<?php
/**
 * Teacher: attendance recording + reports, student rosters
 */

class Ctl_attendance {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];
        $sid = (int)$u['school_id'];
        $isHomeroom = (bool)Database::one(
            "SELECT 1 FROM student_groups WHERE school_id = ? AND homeroom_teacher_id = ? LIMIT 1", [$sid, $uid]);

        $df = demo_filter('c');
        if ($isHomeroom) {
            $courses = Database::all(
                "SELECT c.id, c.title, c.subject_id, c.teacher_id, s.name AS subject_name,
                        CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
                 FROM courses c JOIN subjects s ON s.id = c.subject_id LEFT JOIN users t ON t.id = c.teacher_id
                 WHERE c.school_id = ? $df ORDER BY s.name, c.title", [$sid]);
        } else {
            $courses = array_map(fn($c) => $c + ['teacher_id' => $uid, 'teacher_name' => null], SubjectAuth::courses($uid));
        }

        $date = $_GET['date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$date)) $date = date('Y-m-d');

        $courseId = (int)($_GET['course'] ?? 0);
        $course = null;
        foreach ($courses as $c) if ((int)$c['id'] === $courseId) { $course = $c; break; }
        if (!$course && $courses) $course = $courses[0];
        $courseId = $course ? (int)$course['id'] : 0;
        $mine = $course && (int)$course['teacher_id'] === $uid;
        $canEdit = $mine && ($isHomeroom || (int)$course['subject_id'] === 0 || in_array((int)$course['subject_id'], SubjectAuth::ids($uid), true));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $targetId = (int)($_POST['course_id'] ?? 0);
            $target = null;
            foreach ($courses as $c) if ((int)$c['id'] === $targetId) { $target = $c; break; }
            $tMine = $target && (int)$target['teacher_id'] === $uid;
            $tEdit = $tMine && ($isHomeroom || (int)$target['subject_id'] === 0 || in_array((int)$target['subject_id'], SubjectAuth::ids($uid), true));
            if (!$target || !$tEdit) {
                flash('danger', 'You can only record attendance for courses you teach.');
                redirect('teacher/attendance');
            }
            if (isset($_POST['clear_attendance'])) {
                Database::run("DELETE FROM attendance WHERE course_id = ? AND date = ?", [$targetId, $date]);
                log_activity('attendance', "Cleared attendance for course #$targetId on $date", $uid);
                flash('success', 'Attendance cleared for ' . $date . ' — you can record it again.');
                redirect('teacher/attendance&course=' . $targetId . '&date=' . $date);
            }
            if (isset($_POST['save_attendance'])) {
                $enrolled = array_fill_keys(array_column(Database::all(
                    "SELECT user_id FROM course_enrollments WHERE course_id = ?", [$targetId]), 'user_id'), true);
                $school = Database::scalar("SELECT school_id FROM courses WHERE id = ?", [$targetId], 0);
                $marked = [];
                foreach ($_POST['status'] ?? [] as $sidRaw => $st) {
                    $stid = (int)$sidRaw;
                    if (!isset($enrolled[$stid])) continue;
                    if (!in_array($st, ['present', 'absent', 'late', 'excused'], true)) $st = 'present';
                    $note = trim((string)($_POST['note'][$sidRaw] ?? ''));
                    Database::run(
                        "INSERT INTO attendance (school_id, course_id, student_id, date, status, recorded_by, note) VALUES (?,?,?,?,?,?,?)
                         ON DUPLICATE KEY UPDATE status = VALUES(status), note = VALUES(note), recorded_by = VALUES(recorded_by)",
                        [$school, $targetId, $stid, $date, $st, $uid, $note]);
                    $marked[] = ['student_id' => $stid, 'status' => $st];
                }
                if ($marked) {
                    Ledger::append((int)$school, $uid, 'attendance.marked', 'attendance', $targetId,
                        ['course_id' => $targetId, 'date' => $date, 'records' => $marked]);
                }
                log_activity('attendance', "Recorded attendance for course #$targetId on $date", $uid);
                flash('success', 'Attendance saved for ' . date('M j, Y', strtotime($date)) . ' — it is now locked. Use Edit if you need to change it.');
                redirect('teacher/attendance&course=' . $targetId . '&date=' . $date);
            }
        }

        $students = [];
        $existing = [];
        if ($courseId) {
            $dfSt = demo_filter('ce');
            $students = Database::all(
                "SELECT us.id, CONCAT(us.first_name, ' ', us.last_name) AS name, us.student_id, us.avatar
                 FROM course_enrollments ce JOIN users us ON us.id = ce.user_id
                 WHERE ce.course_id = ? $dfSt ORDER BY us.last_name", [$courseId]);
            foreach (Database::all("SELECT * FROM attendance WHERE course_id = ? AND date = ?", [$courseId, $date]) as $r) {
                $existing[$r['student_id']] = $r;
            }
        }
        $saved = $existing ? true : false;
        $editMode = ($_GET['mode'] ?? '') === 'edit';

        Router::render('app/teacher/attendance', [
            'title' => 'Attendance', 'courses' => $courses, 'courseId' => $courseId,
            'date' => $date, 'students' => $students, 'existing' => $existing,
            'saved' => $saved, 'editMode' => $editMode, 'canEdit' => $canEdit,
            'isHomeroom' => $isHomeroom, 'course' => $course, 'uid' => $uid,
        ]);
    }
}

class Ctl_students {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];
        $courses = SubjectAuth::courses($uid);
        $authIds = array_column($courses, 'id');
        $courseId = (int)($_GET['course'] ?? 0);
        if ($courseId && !in_array($courseId, $authIds, true)) $courseId = 0;
        $inAuth = $authIds ? implode(',', array_map('intval', $authIds)) : '0';

        // POST: create parent account + link to student / link existing parent / unlink
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $stid = (int)($_POST['student_id'] ?? 0);
            $st = Database::one(
                "SELECT DISTINCT us.id, us.first_name, us.last_name FROM users us
                 JOIN course_enrollments ce ON ce.user_id = us.id
                 WHERE us.id = ? AND us.role = 'student' AND ce.course_id IN ($inAuth)",
                [$stid]);
            if (!$st) { flash('danger', 'Student not found in your authorised courses.'); redirect('teacher/students'); }
            if (isset($_POST['link_parent']) && (int)$_POST['link_parent'] > 0) {
                $pid = (int)$_POST['link_parent'];
                $pr = Database::one("SELECT id FROM users WHERE id = ? AND role = 'parent' AND school_id = ?", [$pid, $u['school_id']]);
                if (!$pr) { flash('danger', 'Parent not found in your school.'); redirect('teacher/students'); }
                Database::update('users', ['parent_id' => $pid], 'id = ?', [$stid]);
                Database::update('users', ['parent_id' => null], 'id = ?', [$pid]);
                notify((int)$pid, 'system', 'Linked to ' . $st['first_name'] . ' ' . $st['last_name'], 'You can now follow their learning activity.', 'dashboard');
                log_activity('user', "Teacher linked parent #$pid to student #$stid", $uid);
                flash('success', 'Parent linked to ' . $st['first_name'] . ' ' . $st['last_name'] . '.');
            } elseif (isset($_POST['create_parent'])) {
                $first = trim($_POST['p_first_name'] ?? '');
                $last = trim($_POST['p_last_name'] ?? '');
                $email = strtolower(trim($_POST['p_email'] ?? ''));
                $phone = trim($_POST['p_phone'] ?? '');
                $pass = trim($_POST['p_password'] ?? '') ?: random_password();
                if ($first === '' || $last === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    flash('danger', 'Parent name and valid email required.');
                } elseif (Database::one("SELECT id FROM users WHERE email = ?", [$email])) {
                    flash('danger', 'Email already in use.');
                } else {
                    $pid = Database::insert('users', [
                        'school_id' => $u['school_id'], 'role' => 'parent',
                        'first_name' => $first, 'last_name' => $last,
                        'email' => $email, 'phone' => $phone,
                        'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
                        'status' => 'active', 'verified' => 1,
                    ]);
                    Database::update('users', ['parent_id' => $pid], 'id = ?', [$stid]);
                    notify((int)$pid, 'system', 'Account created — linked to ' . $st['first_name'] . ' ' . $st['last_name'], 'Password: ' . $pass, 'dashboard');
                    log_activity('user', "Teacher created parent $first $last for student #$stid", $uid);
                    flash('success', 'Parent account created and linked. Password: ' . $pass);
                }
            } elseif (isset($_POST['unlink_parent'])) {
                $pid = Database::scalar("SELECT parent_id FROM users WHERE id = ?", [$stid], 0);
                Database::update('users', ['parent_id' => null], 'id = ?', [$stid]);
                log_activity('user', "Teacher unlinked parent #$pid from student #$stid", $uid);
                flash('success', 'Parent unlinked.');
            }
            redirect('teacher/students' . ($courseId ? '&course=' . $courseId : ''));
        }

        $dfSt2 = demo_filter('c');
        $students = Database::all(
            "SELECT us.id, CONCAT(us.first_name, ' ', us.last_name) AS name, us.student_id, us.email, us.phone, us.avatar,
                    c.title AS course_title, s.name AS subject_name, ce.course_id, ce.progress, ce.completed, ce.enrolled_at,
                    us.enrollment_status, us.parent_id,
                    CONCAT(p.first_name, ' ', p.last_name) AS parent_name, p.email AS parent_email,
                    (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.user_id = us.id AND lp.course_id = ce.course_id AND lp.completed = 1) AS done_lessons,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = ce.course_id) AS total_lessons
             FROM course_enrollments ce JOIN users us ON us.id = ce.user_id JOIN courses c ON c.id = ce.course_id
             JOIN subjects s ON s.id = c.subject_id
             LEFT JOIN users p ON p.id = us.parent_id
             WHERE c.id IN ($inAuth) $dfSt2" . ($courseId ? " AND ce.course_id = ?" : "") . "
             ORDER BY us.last_name, us.first_name",
            $courseId ? [$courseId] : []);
        $parents = Database::all("SELECT id, first_name, last_name, email FROM users WHERE role = 'parent' AND school_id = ? ORDER BY first_name LIMIT 200", [$u['school_id']]);
        Router::render('app/teacher/students', ['title' => 'Students', 'courses' => $courses, 'courseId' => $courseId, 'students' => $students, 'parents' => $parents]);
    }
}
