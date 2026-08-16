<?php
/**
 * Director module — head of a single school.
 * Directors are created by the Super Admin. They manage teachers and students
 * of their school, run bulk imports, and handle school-to-school transfers.
 */

/* ============ DIRECTOR: dashboard ============ */
class Ctl_dashboard {
    public function run(): void {
        $u = require_role('director');
        $sid = (int)$u['school_id'];
        $stats = [
            'teachers' => Database::scalar("SELECT COUNT(*) FROM users WHERE role='teacher' AND school_id=?", [$sid], 0),
            'students' => Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=?", [$sid], 0),
            'active' => Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=? AND status='active' AND enrollment_status='active'", [$sid], 0),
            'inactive' => Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=? AND enrollment_status='inactive'", [$sid], 0),
            'pending' => Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=? AND status='pending'", [$sid], 0),
            'courses' => Database::scalar("SELECT COUNT(*) FROM courses WHERE school_id=?", [$sid], 0),
            'exams' => Database::scalar("SELECT COUNT(*) FROM exams e JOIN courses c ON c.id=e.course_id WHERE c.school_id=?", [$sid], 0),
            'transfers' => Database::scalar("SELECT COUNT(*) FROM transfer_requests tr JOIN users us ON us.id=tr.student_id WHERE us.school_id=? AND tr.status='pending'", [$sid], 0),
        ];
        $recent = Database::all(
            "SELECT us.id, CONCAT(us.first_name,' ',us.last_name) AS name, us.email, us.role, us.enrollment_status, us.status, us.created_at
             FROM users us WHERE us.school_id=? AND us.role IN ('teacher','student') ORDER BY us.created_at DESC LIMIT 10", [$sid]);
        $pending = Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=? AND status='pending' AND created_at < NOW() - INTERVAL 24 HOUR", [$sid], 0);

        // 8-week signup trend (teachers + students)
        $days = Database::all(
            "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM users
             WHERE school_id=? AND role IN ('teacher','student') AND created_at >= NOW() - INTERVAL 8 WEEK
             GROUP BY DATE(created_at)", [$sid]);
        $dayMap = [];
        foreach ($days as $d) { $dayMap[$d['d']] = (int)$d['c']; }
        $weeks = [];
        $weekStart = strtotime('monday this week');
        for ($i = 7; $i >= 0; $i--) {
            $start = $weekStart - $i * 604800;
            $count = 0;
            for ($j = 0; $j < 7; $j++) {
                $count += $dayMap[date('Y-m-d', $start + $j * 86400)] ?? 0;
            }
            $weeks[] = ['label' => date('M j', $start), 'count' => $count];
        }

        $activities = Database::all(
            "SELECT a.action, a.detail, a.created_at, u.first_name, u.last_name
             FROM activity_logs a JOIN users u ON u.id = a.user_id
             WHERE u.school_id = ? ORDER BY a.created_at DESC LIMIT 8", [$sid]);

        $todo = [];
        if ($stats['pending'] > 0) {
            $todo[] = ['label' => $stats['pending'] . ' student' . ($stats['pending'] === 1 ? '' : 's') . ' waiting for verification', 'link' => 'director/students&filter=pending', 'icon' => 'clock', 'cls' => 'warn'];
        }
        if ($stats['transfers'] > 0) {
            $todo[] = ['label' => $stats['transfers'] . ' pending transfer' . ($stats['transfers'] === 1 ? '' : 's'), 'link' => 'director/transfers', 'icon' => 'refresh', 'cls' => 'accent'];
        }
        if ($stats['inactive'] > 0) {
            $todo[] = ['label' => $stats['inactive'] . ' student' . ($stats['inactive'] === 1 ? '' : 's') . ' in re-exam (inactive)', 'link' => 'director/students&filter=inactive', 'icon' => 'pause', 'cls' => 'info'];
        }
        if ($stats['teachers'] === 0) {
            $todo[] = ['label' => 'No teachers yet — create your first teacher', 'link' => 'director/teachers', 'icon' => 'users', 'cls' => 'warn'];
        }

        Router::render('app/director/dashboard', [
            'title' => 'Director Dashboard', 'stats' => $stats, 'recent' => $recent, 'pending' => $pending,
            'weeks' => $weeks, 'activities' => $activities, 'todo' => $todo,
        ]);
    }
}

/* ============ DIRECTOR: manage teachers ============ */
class Ctl_teachers {
    public function run(): void {
        $u = require_role('director');
        $sid = (int)$u['school_id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_teacher'])) {
                $first = trim($_POST['first_name'] ?? '');
                $last = trim($_POST['last_name'] ?? '');
                $email = strtolower(trim($_POST['email'] ?? ''));
                $phone = trim($_POST['phone'] ?? '');
                $pass = trim($_POST['password'] ?? '') ?: random_password();
                if ($first === '' || $last === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    flash('danger', 'Name and valid email are required.');
                } elseif (Database::one("SELECT id FROM users WHERE email = ?", [$email])) {
                    flash('danger', 'Email already in use.');
                } else {
                    Database::insert('users', [
                        'school_id' => $sid, 'role' => 'teacher',
                        'first_name' => $first, 'last_name' => $last,
                        'email' => $email, 'phone' => $phone,
                        'department_id' => (int)($_POST['department_id'] ?? 0) ?: null,
                        'group_id' => null,
                        'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
                        'status' => 'active', 'verified' => 1,
                    ]);
                    log_activity('user', "Director created teacher $first $last", (int)$u['id']);
                    flash('success', 'Teacher account created. Initial password: ' . $pass);
                }
                redirect('director/teachers');
            }
            if (isset($_POST['delete_teacher'])) {
                $tid = (int)$_POST['delete_teacher'];
                Database::delete('users', 'id = ? AND school_id = ? AND role = ?', [$tid, $sid, 'teacher']);
                flash('success', 'Teacher removed.');
                redirect('director/teachers');
            }
            if (isset($_POST['edit_teacher'])) {
                $tid = (int)$_POST['edit_teacher'];
                $teacher = Database::one("SELECT id FROM users WHERE id = ? AND school_id = ? AND role = 'teacher'", [$tid, $sid]);
                if (!$teacher) {
                    flash('danger', 'Teacher not found.');
                } else {
                    $first = trim($_POST['first_name'] ?? '');
                    $last = trim($_POST['last_name'] ?? '');
                    $email = strtolower(trim($_POST['email'] ?? ''));
                    $phone = trim($_POST['phone'] ?? '');
                    $dept = (int)($_POST['department_id'] ?? 0) ?: null;
                    $pass = trim($_POST['password'] ?? '');
                    if ($first === '' || $last === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        flash('danger', 'Name and valid email are required.');
                    } elseif (Database::one("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $tid])) {
                        flash('danger', 'Email already in use.');
                    } else {
                        $data = ['first_name' => $first, 'last_name' => $last, 'email' => $email, 'phone' => $phone, 'department_id' => $dept];
                        if ($pass !== '') $data['password_hash'] = password_hash($pass, PASSWORD_DEFAULT);
                        Database::update('users', $data, 'id = ?', [$tid]);
                        log_activity('user', "Director updated teacher #$tid", (int)$u['id']);
                        flash('success', $pass !== '' ? "Teacher updated. New password: $pass" : 'Teacher updated.');
                    }
                }
                redirect('director/teachers');
            }
            if (isset($_POST['set_homeroom'])) {
                $gid = (int)$_POST['set_homeroom'];
                $tid = (int)($_POST['homeroom_teacher_id'] ?? 0) ?: null;
                Database::update('student_groups', ['homeroom_teacher_id' => $tid], 'id = ? AND school_id = ?', [$gid, $sid]);
                flash('success', 'Homeroom teacher updated.');
                redirect('director/teachers');
            }
            if (isset($_POST['set_subjects'])) {
                $tid = (int)$_POST['set_subjects'];
                $teacher = Database::one("SELECT id FROM users WHERE id = ? AND school_id = ? AND role = 'teacher'", [$tid, $sid]);
                if (!$teacher) {
                    flash('danger', 'Teacher not found.');
                } else {
                    // keep only subjects that belong to this school
                    $wanted = array_map('intval', (array)($_POST['subjects'] ?? []));
                    $valid = [];
                    if ($wanted) {
                        $qs = implode(',', array_fill(0, count($wanted), '?'));
                        $rows = Database::all("SELECT id FROM subjects WHERE id IN ($qs) AND school_id = ? AND status = 'active'", [...$wanted, $sid]);
                        $valid = array_map('intval', array_column($rows, 'id'));
                    }
                    Database::query("DELETE FROM teacher_subjects WHERE teacher_id = ?", [$tid]);
                    foreach ($valid as $sid2) {
                        Database::insert('teacher_subjects', ['teacher_id' => $tid, 'subject_id' => $sid2]);
                    }
                    log_activity('user', "Director set subjects for teacher #$tid: " . implode(',', $valid), (int)$u['id']);
                    flash('success', 'Teacher subjects updated — the teacher can now create courses only in these subjects.');
                }
                redirect('director/teachers');
            }
        }
        $q = trim($_GET['q'] ?? '');
        $f = trim($_GET['f'] ?? '');
        $where = "us.school_id = ? AND us.role = 'teacher'";
        $args = [$sid];
        if ($q) { $where .= " AND (us.first_name LIKE ? OR us.last_name LIKE ? OR us.email LIKE ? OR us.phone LIKE ?)"; array_push($args, "%$q%", "%$q%", "%$q%", "%$q%"); }
        $teachers = Database::all(
            "SELECT us.id, CONCAT(us.first_name,' ',us.last_name) AS name, us.email, us.phone, us.last_login, us.department_id AS dept_id, d.name AS dept,
                    (SELECT COUNT(*) FROM student_groups g WHERE g.homeroom_teacher_id = us.id) AS homeroom_count,
                    (SELECT g.id FROM student_groups g WHERE g.homeroom_teacher_id = us.id LIMIT 1) AS homeroom_gid,
                    (SELECT COUNT(*) FROM courses c WHERE c.teacher_id = us.id) AS course_count,
                    COALESCE((SELECT GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ')
                              FROM teacher_subjects ts JOIN subjects s ON s.id = ts.subject_id
                              WHERE ts.teacher_id = us.id), '') AS subjects
             FROM users us LEFT JOIN departments d ON d.id = us.department_id
             WHERE $where ORDER BY us.created_at DESC LIMIT 200", $args);
        $groups = Database::all("SELECT id, name, grade, section, homeroom_teacher_id FROM student_groups WHERE school_id = ?", [$sid]);
        $depts = Database::all("SELECT id, name FROM departments WHERE school_id = ?", [$sid]);
        $subjects = Database::all("SELECT id, name FROM subjects WHERE school_id = ? AND status = 'active' ORDER BY name", [$sid]);
        $assigned = Database::all("SELECT ts.teacher_id, ts.subject_id FROM teacher_subjects ts JOIN users us ON us.id = ts.teacher_id WHERE us.school_id = ?", [$sid]);
        $assignMap = [];
        foreach ($assigned as $a) $assignMap[(int)$a['teacher_id']][] = (int)$a['subject_id'];
        $stats = [
            'total' => count($teachers),
            'with_courses' => count(array_filter($teachers, fn($t) => (int)$t['course_count'] > 0)),
            'homeroom' => count(array_filter($teachers, fn($t) => (int)$t['homeroom_count'] > 0)),
            'no_subjects' => count(array_filter($teachers, fn($t) => empty($assignMap[(int)$t['id']]))),
        ];
        $filter = in_array($f, ['with_courses', 'homeroom', 'no_subjects'], true) ? $f : '';
        if ($filter === 'with_courses') $teachers = array_values(array_filter($teachers, fn($t) => (int)$t['course_count'] > 0));
        elseif ($filter === 'homeroom') $teachers = array_values(array_filter($teachers, fn($t) => (int)$t['homeroom_count'] > 0));
        elseif ($filter === 'no_subjects') $teachers = array_values(array_filter($teachers, fn($t) => empty($assignMap[(int)$t['id']])));
        Router::render('app/director/teachers', [
            'title' => 'Teachers', 'teachers' => $teachers, 'groups' => $groups,
            'depts' => $depts, 'subjects' => $subjects, 'assignMap' => $assignMap,
            'stats' => $stats, 'q' => $q, 'filter' => $filter,
        ]);
    }
}

/* ============ DIRECTOR: students + active/inactive ============ */
class Ctl_students {
    public function run(): void {
        $u = require_role('director');
        $sid = (int)$u['school_id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $stid = (int)($_POST['student_id'] ?? 0);
            $st = Database::one("SELECT id FROM users WHERE id = ? AND role='student' AND school_id = ?", [$stid, $sid]);
            if ($st) {
                if (isset($_POST['set_enrollment'])) {
                    $val = $_POST['set_enrollment'] === 'inactive' ? 'inactive' : 'active';
                    Database::update('users', ['enrollment_status' => $val], 'id = ?', [$stid]);
                    flash('success', $val === 'active' ? 'Student is active.' : 'Student marked INACTIVE — courses & re-exams only.');
                }
                if (isset($_POST['activate'])) {
                    Database::update('users', ['status' => 'active', 'verified' => 1, 'verified_at' => date('Y-m-d H:i:s')], 'id = ?', [$stid]);
                    flash('success', 'Student account activated.');
                }
            }
            redirect('director/students');
        }
        $q = trim($_GET['q'] ?? '');
        $filter = $_GET['filter'] ?? 'all';
        $where = "us.school_id = ? AND us.role = 'student'";
        $args = [$sid];
        if ($q) { $where .= " AND (us.first_name LIKE ? OR us.last_name LIKE ? OR us.email LIKE ? OR us.student_id LIKE ?)"; array_push($args, "%$q%", "%$q%", "%$q%", "%$q%"); }
        if ($filter === 'active') $where .= " AND us.enrollment_status = 'active'";
        elseif ($filter === 'inactive') $where .= " AND us.enrollment_status = 'inactive'";
        elseif ($filter === 'pending') $where .= " AND us.status = 'pending'";
        $students = Database::all(
            "SELECT us.id, CONCAT(us.first_name,' ',us.last_name) AS name, us.student_id, us.email, us.phone,
                    us.enrollment_status, us.status, us.verified_at, us.created_at,
                    g.name AS group_name,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.user_id = us.id) AS courses
             FROM users us LEFT JOIN student_groups g ON g.id = us.group_id
             WHERE $where ORDER BY us.created_at DESC LIMIT 200", $args);
        $stats = [
            'total' => Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=?", [$sid], 0),
            'active' => Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=? AND enrollment_status='active'", [$sid], 0),
            'inactive' => Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=? AND enrollment_status='inactive'", [$sid], 0),
            'pending' => Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=? AND status='pending'", [$sid], 0),
        ];
        Router::render('app/director/students', ['title' => 'Students', 'students' => $students, 'stats' => $stats, 'q' => $q, 'filter' => $filter]);
    }
}
