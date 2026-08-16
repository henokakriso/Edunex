<?php
/**
 * Vice Dean — faculty-wide academic oversight (courses approval, analytics).
 * Department Head — department-scoped oversight (courses, theses, teachers).
 */

/* =============== VICE DEAN: faculty scope (same as dean) =============== */
class Ctl_vice_dean {
    private function fid(int $sid): int {
        $f = Database::one(
            "SELECT id FROM faculties WHERE school_id = ? AND (vice_dean_id = ? OR dean_id = ?) AND status = 'active'",
            [$sid, (int)me()['id'], (int)me()['id']]);
        return $f ? (int)$f['id'] : 0;
    }

    public function run(): void {
        $u = require_role('vice_dean');
        $sid = (int)$u['school_id'];
        $route = trim($_GET['r'] ?? '', '/');
        $route = str_replace('vice_dean/', '', $route);
        if (!module_active($sid, 'university')) {
            http_response_code(403);
            die('The University module is not installed for this school.');
        }
        match ($route) {
            'dashboard' => $this->dashboard($u, $sid),
            'courses' => $this->courses($u, $sid),
            'analytics' => $this->analytics($u, $sid),
            default => $this->dashboard($u, $sid),
        };
    }

    private function dashboard(array $u, int $sid): void {
        $fid = $this->fid($sid);
        $faculty = Database::one("SELECT * FROM faculties WHERE id = ? AND school_id = ?", [$fid, $sid]);
        $stats = [
            'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id WHERE d.faculty_id = ?", [$fid], 0),
            'pending' => (int)Database::scalar("SELECT COUNT(*) FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id WHERE d.faculty_id = ? AND co.status='draft'", [$fid], 0),
            'teachers' => (int)Database::scalar("SELECT COUNT(*) FROM users u JOIN departments d ON d.id = u.department_id WHERE d.faculty_id = ? AND u.role='teacher'", [$fid], 0),
            'departments' => (int)Database::scalar("SELECT COUNT(*) FROM departments WHERE faculty_id = ? AND status='active'", [$fid], 0),
        ];
        $recent = Database::all(
            "SELECT co.title, co.status, CONCAT(t.first_name,' ',t.last_name) AS teacher, co.approved_at
             FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id
             WHERE d.faculty_id = ? ORDER BY co.created_at DESC LIMIT 8", [$fid]);
        Router::render('app/vice_dean/dashboard', ['title' => 'Vice Dean', 'stats' => $stats, 'recent' => $recent, 'faculty' => $faculty]);
    }

    private function courses(array $u, int $sid): void {
        $fid = $this->fid($sid);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $cid = (int)($_POST['approve_course'] ?? 0);
            $course = Database::one(
                "SELECT co.* FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id
                 WHERE co.id = ? AND d.faculty_id = ?", [$cid, $fid]);
            if (!$course) {
                flash('danger', 'Course not found in your faculty.');
            } else {
                $status = $_POST['status'] === 'published' ? 'published' : 'draft';
                Database::update('courses', ['status' => $status, 'approved_by' => (int)$u['id'], 'approved_at' => date('Y-m-d H:i:s')], 'id = ?', [$cid]);
                log_activity('course.approve', 'Vice dean ' . ($status === 'published' ? 'approved' : 'returned') . ' course #' . $cid, (int)$u['id']);
                flash('success', $status === 'published' ? 'Course approved and published.' : 'Course returned to draft.');
            }
            redirect('vice_dean/courses');
        }
        $rows = Database::all(
            "SELECT co.*, CONCAT(t.first_name,' ',t.last_name) AS teacher, d.name AS dept
             FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id
             WHERE d.faculty_id = ? ORDER BY co.status, co.created_at DESC LIMIT 100", [$fid]);
        Router::render('app/vice_dean/courses', ['title' => 'Course Approval', 'rows' => $rows]);
    }

    private function analytics(array $u, int $sid): void {
        $fid = $this->fid($sid);
        $rows = Database::all(
            "SELECT d.name AS dept, COUNT(DISTINCT co.id) AS courses, COUNT(DISTINCT t.id) AS teachers,
                    (SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id JOIN users t2 ON t2.id = c.teacher_id WHERE t2.department_id = d.id) AS enrollments
             FROM departments d LEFT JOIN users t ON t.department_id = d.id AND t.role='teacher'
             LEFT JOIN courses co ON co.teacher_id = t.id
             WHERE d.faculty_id = ? AND d.status='active' GROUP BY d.id ORDER BY d.name", [$fid]);
        Router::render('app/vice_dean/analytics', ['title' => 'Faculty Analytics', 'rows' => $rows]);
    }
}

/* =============== DEPARTMENT HEAD: department scope =============== */
class Ctl_dept_head {
    private function dept(int $sid): ?array {
        $did = (int)(me()['department_id'] ?? 0);
        return Database::one("SELECT * FROM departments WHERE id = ? AND school_id = ? AND status = 'active'", [$did, $sid]);
    }

    public function run(): void {
        $u = require_role('dept_head');
        $sid = (int)$u['school_id'];
        $route = trim($_GET['r'] ?? '', '/');
        $route = str_replace('dept_head/', '', $route);
        if (!module_active($sid, 'university')) {
            http_response_code(403);
            die('The University module is not installed for this school.');
        }
        match ($route) {
            'dashboard' => $this->dashboard($u, $sid),
            'courses' => $this->courses($u, $sid),
            'theses' => $this->theses($u, $sid),
            'analytics' => $this->analytics($u, $sid),
            default => $this->dashboard($u, $sid),
        };
    }

    private function dashboard(array $u, int $sid): void {
        $dept = $this->dept($sid);
        if (!$dept) { http_response_code(403); die('You are not linked to an active department.'); }
        $stats = [
            'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses c JOIN users t ON t.id = c.teacher_id WHERE t.department_id = ?", [$dept['id']], 0),
            'teachers' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE department_id = ? AND role='teacher'", [$dept['id']], 0),
            'theses' => (int)Database::scalar("SELECT COUNT(*) FROM theses WHERE department_id = ? AND status='submitted'", [$dept['id']], 0),
            'students' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE department_id = ? AND role='student'", [$dept['id']], 0),
        ];
        Router::render('app/dept_head/dashboard', ['title' => 'Department Head', 'stats' => $stats, 'dept' => $dept]);
    }

    private function courses(array $u, int $sid): void {
        $dept = $this->dept($sid);
        $rows = Database::all(
            "SELECT co.*, CONCAT(t.first_name,' ',t.last_name) AS teacher
             FROM courses co JOIN users t ON t.id = co.teacher_id
             WHERE t.department_id = ? ORDER BY co.status, co.created_at DESC LIMIT 100", [$dept['id']]);
        Router::render('app/dept_head/courses', ['title' => 'Department Courses', 'rows' => $rows]);
    }

    private function theses(array $u, int $sid): void {
        $dept = $this->dept($sid);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $tid = (int)($_POST['decide_thesis'] ?? 0);
            $thesis = Database::one("SELECT * FROM theses WHERE id = ? AND department_id = ?", [$tid, $dept['id']]);
            if (!$thesis) {
                flash('danger', 'Thesis not found in your department.');
            } else {
                $newStatus = $_POST['status'] === 'approved' ? 'approved' : 'rejected';
                Database::update('theses', [
                    'status' => $newStatus, 'decided_by' => (int)$u['id'], 'decided_at' => date('Y-m-d H:i:s'),
                    'feedback' => trim((string)($_POST['feedback'] ?? '')),
                ], 'id = ?', [$tid]);
                log_activity('thesis.decide', 'Dept head ' . $newStatus . ' thesis #' . $tid, (int)$u['id']);
                flash('success', 'Thesis ' . $newStatus . '.');
            }
            redirect('dept_head/theses');
        }
        $rows = Database::all(
            "SELECT th.*, CONCAT(st.first_name,' ',st.last_name) AS student, st.student_id
             FROM theses th JOIN users st ON st.id = th.student_id
             WHERE th.department_id = ? ORDER BY th.submitted_at DESC LIMIT 100", [$dept['id']]);
        Router::render('app/dept_head/theses', ['title' => 'Theses', 'rows' => $rows, 'dept' => $dept]);
    }

    private function analytics(array $u, int $sid): void {
        $dept = $this->dept($sid);
        $students = Database::all(
            "SELECT u.id, u.student_id, CONCAT(u.first_name,' ',u.last_name) AS student,
                    COALESCE(SUM(co.credit_hours), 0) AS credits
             FROM users u LEFT JOIN course_enrollments ce ON ce.user_id = u.id
             LEFT JOIN courses co ON co.id = ce.course_id
             WHERE u.department_id = ? AND u.role = 'student'
             GROUP BY u.id ORDER BY u.last_name LIMIT 200", [$dept['id']]);
        Router::render('app/dept_head/analytics', ['title' => 'Department Analytics', 'students' => $students, 'dept' => $dept]);
    }
}
