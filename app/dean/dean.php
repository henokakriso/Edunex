<?php
/**
 * Dean — manages one faculty (departments, teachers, course approvals).
 * Faculty = faculties.dean_id = me. Everything scoped through that faculty.
 */

class Ctl_dean {
    private ?array $faculty = null;

    public function run(): void {
        $u = require_role('dean');
        $this->faculty = Database::one(
            "SELECT f.*, sc.name AS school_name FROM faculties f JOIN schools sc ON sc.id = f.school_id
             WHERE f.dean_id = ? AND f.status = 'active'", [(int)$u['id']]);
        if (!$this->faculty) {
            Router::render('app/dean/dashboard', ['title' => 'Dean', 'faculty' => null]);
            return;
        }
        $route = trim($_GET['r'] ?? '', '/');
        $route = str_replace('dean/', '', $route);
        match ($route) {
            'dashboard' => $this->dashboard($u),
            'departments' => $this->departments($u),
            'courses' => $this->courses($u),
            'teachers' => $this->teachers($u),
            'analytics' => $this->analytics($u),
            default => $this->dashboard($u),
        };
    }

    private function fid(): int { return (int)$this->faculty['id']; }

    private function dashboard(array $u): void {
        $fid = $this->fid();
        $stats = [
            'departments' => (int)Database::scalar("SELECT COUNT(*) FROM departments WHERE faculty_id = ? AND status='active'", [$fid], 0),
            'teachers' => (int)Database::scalar("SELECT COUNT(*) FROM users t JOIN departments d ON d.id = t.department_id WHERE d.faculty_id = ? AND t.role='teacher'", [$fid], 0),
            'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id WHERE d.faculty_id = ?", [$fid], 0),
            'pending' => (int)Database::scalar("SELECT COUNT(*) FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id WHERE d.faculty_id = ? AND co.status='draft'", [$fid], 0),
            'students' => (int)Database::scalar(
                "SELECT COUNT(DISTINCT ce.user_id) FROM course_enrollments ce
                 JOIN courses co ON co.id = ce.course_id
                 JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id
                 WHERE d.faculty_id = ?", [$fid], 0),
            'exams' => (int)Database::scalar(
                "SELECT COUNT(*) FROM exams e JOIN courses co ON co.id = e.course_id
                 JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id
                 WHERE d.faculty_id = ?", [$fid], 0),
        ];
        $recent = Database::all(
            "SELECT co.id, co.title, co.status, co.created_at, CONCAT(t.first_name,' ',t.last_name) AS teacher
             FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id
             WHERE d.faculty_id = ? ORDER BY co.created_at DESC LIMIT 8", [$fid]);
        Router::render('app/dean/dashboard', ['title' => 'Dean Dashboard', 'faculty' => $this->faculty, 'stats' => $stats, 'recent' => $recent]);
    }

    private function departments(array $u): void {
        $fid = $this->fid();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_dept'])) {
                $name = trim((string)($_POST['name'] ?? ''));
                if ($name === '') {
                    flash('danger', 'Department name is required.');
                } else {
                    Database::insert('departments', ['school_id' => (int)$this->faculty['school_id'], 'faculty_id' => $fid, 'name' => $name, 'head' => trim((string)($_POST['head'] ?? '')) ?: null, 'required_credits' => max(0, (int)($_POST['required_credits'] ?? 120))]);
                    log_activity('department.create', 'Dean created department ' . $name, (int)$u['id']);
                    flash('success', 'Department created.');
                }
                redirect('dean/departments');
            }
            if (isset($_POST['archive_dept'])) {
                $did = (int)($_POST['archive_dept'] ?? 0);
                Database::update('departments', ['status' => 'archived'], 'id = ? AND faculty_id = ?', [$did, $fid]);
                flash('success', 'Department archived.');
                redirect('dean/departments');
            }
        }
        $rows = Database::all(
            "SELECT d.*, (SELECT COUNT(*) FROM users t WHERE t.department_id = d.id AND t.role='teacher') AS teachers,
                    (SELECT COUNT(*) FROM courses co JOIN users t ON t.id = co.teacher_id WHERE t.department_id = d.id) AS courses
             FROM departments d WHERE d.faculty_id = ? ORDER BY d.status, d.name", [$fid]);
        Router::render('app/dean/departments', ['title' => 'Departments', 'faculty' => $this->faculty, 'rows' => $rows]);
    }

    private function courses(array $u): void {
        $fid = $this->fid();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $cid = (int)($_POST['course_id'] ?? 0);
            $action = (string)($_POST['action'] ?? '');
            $course = Database::one(
                "SELECT co.* FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id
                 WHERE co.id = ? AND d.faculty_id = ?", [$cid, $fid]);
            if ($course) {
                if ($action === 'approve') {
                    Database::update('courses', ['status' => 'published', 'approved_by' => (int)$u['id'], 'approved_at' => date('Y-m-d H:i:s')], 'id = ?', [$cid]);
                    log_activity('course.approve', 'Dean approved course #' . $cid . ' (' . $course['title'] . ')', (int)$u['id']);
                    flash('success', 'Course approved and published.');
                } elseif ($action === 'reject') {
                    Database::update('courses', ['status' => 'draft', 'approved_by' => null, 'approved_at' => null], 'id = ?', [$cid]);
                    log_activity('course.reject', 'Dean returned course #' . $cid . ' for revision', (int)$u['id']);
                    flash('success', 'Course returned for revision.');
                }
            } else {
                flash('danger', 'Course not found in your faculty.');
            }
            redirect('dean/courses');
        }
        $status = (string)($_GET['status'] ?? 'draft');
        $rows = Database::all(
            "SELECT co.*, CONCAT(t.first_name,' ',t.last_name) AS teacher, d.name AS department
             FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id
             WHERE d.faculty_id = ? AND co.status = ? ORDER BY co.created_at DESC LIMIT 100", [$fid, in_array($status, ['draft', 'published', 'archived'], true) ? $status : 'draft']);
        $counts = [
            'draft' => (int)Database::scalar("SELECT COUNT(*) FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id WHERE d.faculty_id = ? AND co.status='draft'", [$fid], 0),
            'published' => (int)Database::scalar("SELECT COUNT(*) FROM courses co JOIN users t ON t.id = co.teacher_id JOIN departments d ON d.id = t.department_id WHERE d.faculty_id = ? AND co.status='published'", [$fid], 0),
        ];
        Router::render('app/dean/courses', ['title' => 'Course Approval', 'faculty' => $this->faculty, 'rows' => $rows, 'status' => $status, 'counts' => $counts]);
    }

    private function teachers(array $u): void {
        $fid = $this->fid();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $tid = (int)($_POST['teacher_id'] ?? 0);
            $did = (int)($_POST['department_id'] ?? 0);
            $teacher = Database::one(
                "SELECT t.id FROM users t JOIN departments d ON d.id = t.department_id
                 WHERE t.id = ? AND t.role='teacher' AND d.faculty_id = ?", [$tid, $fid]);
            $dept = Database::one("SELECT id FROM departments WHERE id = ? AND faculty_id = ?", [$did, $fid]);
            if ($teacher && $dept) {
                Database::update('users', ['department_id' => $did], 'id = ?', [$tid]);
                log_activity('teacher.department', 'Dean moved teacher #' . $tid . ' to department #' . $did, (int)$u['id']);
                flash('success', 'Teacher moved.');
            } else {
                flash('danger', 'Teacher or department not found in your faculty.');
            }
            redirect('dean/teachers');
        }
        $depts = Database::all("SELECT id, name FROM departments WHERE faculty_id = ? AND status='active' ORDER BY name", [$fid]);
        $rows = Database::all(
            "SELECT t.id, CONCAT(t.first_name,' ',t.last_name) AS name, t.email, t.phone, t.status, d.name AS department,
                    (SELECT COUNT(*) FROM courses co WHERE co.teacher_id = t.id) AS courses
             FROM users t JOIN departments d ON d.id = t.department_id
             WHERE d.faculty_id = ? AND t.role='teacher' ORDER BY d.name, t.last_name", [$fid]);
        Router::render('app/dean/teachers', ['title' => 'Teachers', 'faculty' => $this->faculty, 'rows' => $rows, 'depts' => $depts]);
    }

    private function analytics(array $u): void {
        $fid = $this->fid();
        $rows = Database::all(
            "SELECT d.id, d.name,
                    (SELECT COUNT(*) FROM users t WHERE t.department_id = d.id AND t.role='teacher') AS teachers,
                    (SELECT COUNT(*) FROM courses co JOIN users t ON t.id = co.teacher_id WHERE t.department_id = d.id) AS courses,
                    (SELECT COUNT(DISTINCT ce.user_id) FROM course_enrollments ce JOIN courses co ON co.id = ce.course_id JOIN users t ON t.id = co.teacher_id WHERE t.department_id = d.id) AS students,
                    (SELECT ROUND(AVG(ea.score),1) FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id JOIN courses co ON co.id = e.course_id JOIN users t ON t.id = co.teacher_id WHERE t.department_id = d.id AND ea.status='graded') AS avg_score
             FROM departments d WHERE d.faculty_id = ? AND d.status='active' ORDER BY d.name", [$fid]);
        $totals = [
            'teachers' => array_sum(array_column($rows, 'teachers')),
            'courses' => array_sum(array_column($rows, 'courses')),
            'students' => array_sum(array_column($rows, 'students')),
        ];
        Router::render('app/dean/analytics', ['title' => 'Analytics', 'faculty' => $this->faculty, 'rows' => $rows, 'totals' => $totals]);
    }
}
