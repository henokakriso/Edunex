<?php
/**
 * Student module: courses, exams, grades, attendance, schedule, leaderboard
 */

/** Re-exam track: inactive students get courses + exams only */
function inactive_student(?array $u): bool {
    return ($u['role'] ?? '') === 'student' && ($u['enrollment_status'] ?? 'active') === 'inactive';
}

/* =============== STUDENT: enrolled courses =============== */
class Ctl_courses {
    public function run(): void {
        $u = require_role('student');
        $uid = (int)$u['id'];
        $df = demo_filter('c');
        $courses = Database::all(
            "SELECT c.*, u.first_name AS tfirst, u.last_name AS tlast, ce.progress, ce.completed,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS total_lessons,
                    (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.lesson_id IN (SELECT id FROM lessons l2 WHERE l2.course_id = c.id) AND lp.user_id = ? AND lp.completed = 1) AS done_lessons
             FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id JOIN users u ON u.id = c.teacher_id
             WHERE ce.user_id = ? AND c.status = 'published' $df
             ORDER BY ce.enrolled_at DESC", [$uid, $uid]);
        Router::render('app/student/courses', ['title' => 'My Courses', 'courses' => $courses]);
    }
}

/* =============== STUDENT: exam list =============== */
class Ctl_exams {
    public function run(): void {
        $u = require_role('student');
        require_student_feature('exams');
        $uid = (int)$u['id'];
        $df = demo_filter('e');
        $exams = Database::all(
            "SELECT e.*, c.title AS course_title,
                    a.id AS attempt_id, a.status AS attempt_status, a.score, a.total_points, a.started_at, a.submitted_at
             FROM exams e JOIN courses c ON c.id = e.course_id
             JOIN course_enrollments ce ON ce.course_id = e.course_id AND ce.user_id = ?
             LEFT JOIN exam_attempts a ON a.exam_id = e.id AND a.student_id = ? AND a.id = (SELECT MAX(a2.id) FROM exam_attempts a2 WHERE a2.exam_id = e.id AND a2.student_id = ?)
             WHERE e.status = 'published' AND e.end_time > NOW() $df
             ORDER BY e.start_time", [$uid, $uid, $uid]);
        foreach ($exams as &$ex) {
            $ex['open'] = strtotime($ex['start_time']) <= time() + 120;
        }
        Router::render('app/student/exams', ['title' => 'My Exams', 'exams' => $exams]);
    }
}

/* =============== STUDENT: grades =============== */
class Ctl_grades {
    public function run(): void {
        $u = require_role('student');
        require_student_feature('grades');
        if (inactive_student($u)) { flash('info', 'Inactive (re-exam) accounts have access to courses and exams only.'); redirect('student/dashboard'); }
        $uid = (int)$u['id'];
        $dfExam = demo_filter('e');
        $dfAssign = demo_filter('a');
        $semesters = Database::all(
            "SELECT s.*, y.name AS year_name FROM semesters s
             JOIN academic_years y ON y.id = s.year_id
             WHERE s.name <> '' ORDER BY s.start_date");
        $semesterOf = function (?string $ts) use ($semesters): string {
            if (!$ts) return 'Other';
            $t = strtotime($ts);
            foreach ($semesters as $s) {
                $st = $s['start_date'] ? strtotime($s['start_date']) : null;
                $en = $s['end_date'] ? strtotime($s['end_date']) : null;
                if ($st && $t >= $st && (!$en || $t <= $en)) return trim($s['name'] . ' · ' . $s['year_name']);
            }
            return 'Other';
        };
        $exams = Database::all(
            "SELECT e.id, e.title, c.title AS course_title, c.level AS course_level, c.id AS course_id,
                    a.score, a.total_points, a.submitted_at, a.status,
                    e.passing_score
             FROM exam_attempts a JOIN exams e ON e.id = a.exam_id JOIN courses c ON c.id = e.course_id
             WHERE a.student_id = ? AND a.status = 'graded' $dfExam ORDER BY a.submitted_at DESC", [$uid]);
        foreach ($exams as &$x) { $x['semester'] = $semesterOf($x['submitted_at']); }
        $assigns = Database::all(
            "SELECT a.title, c.title AS course_title, c.level AS course_level, c.id AS course_id,
                    s.score, a.max_score, s.graded_at, s.feedback
             FROM assignment_submissions s JOIN assignments a ON a.id = s.assignment_id
             JOIN courses c ON c.id = a.course_id
             WHERE s.student_id = ? AND s.status = 'graded' $dfAssign ORDER BY s.graded_at DESC", [$uid]);
        foreach ($assigns as &$a) { $a['semester'] = $semesterOf($a['graded_at']); }
        $courses = Database::all(
            "SELECT c.title, ce.progress,
                    (SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON l.id = lp.lesson_id WHERE lp.user_id = ? AND l.course_id = c.id AND lp.completed = 1) AS done,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS total
             FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE ce.user_id = ?", [$uid, $uid]);
        Router::render('app/student/grades', [
            'title' => 'My Grades', 'exams' => $exams, 'assigns' => $assigns, 'courses' => $courses,
        ]);
    }
}

/* =============== STUDENT: attendance =============== */
class Ctl_attendance {
    public function run(): void {
        $u = require_role('student');
        require_student_feature('attendance');
        if (inactive_student($u)) { flash('info', 'Inactive (re-exam) accounts have access to courses and exams only.'); redirect('student/dashboard'); }
        $uid = (int)$u['id'];
        $df = demo_filter('at');
        $rows = Database::all(
            "SELECT at.*, c.title AS course_title, us.first_name AS tfirst, us.last_name AS tlast
             FROM attendance at JOIN courses c ON c.id = at.course_id JOIN users us ON us.id = at.recorded_by
             WHERE at.student_id = ? $df ORDER BY at.date DESC, at.id DESC LIMIT 120", [$uid]);
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
        foreach ($rows as $r) {
            $summary[$r['status']] = ($summary[$r['status']] ?? 0) + 1;
        }
        $rate = ($summary['present'] + $summary['late'] + $summary['excused']) > 0
            ? round(($summary['present'] + $summary['late'] + $summary['excused']) / max(1, count($rows)) * 100)
            : 0;
        Router::render('app/student/attendance', [
            'title' => 'My Attendance', 'rows' => $rows, 'summary' => $summary, 'rate' => $rate,
        ]);
    }
}

/* =============== STUDENT: schedule =============== */
class Ctl_schedule {
    public function run(): void {
        $u = require_role('student');
        require_student_feature('schedule');
        if (inactive_student($u)) { flash('info', 'Inactive (re-exam) accounts have access to courses and exams only.'); redirect('student/dashboard'); }
        $uid = (int)$u['id'];
        $events = Database::all(
            "SELECT ce.id, ce.title, ce.type AS event_type, ce.start_at AS event_date, ce.location, ce.description, '' AS course_title
             FROM calendar_events ce
             WHERE (ce.user_id = ? OR ce.user_id IS NULL OR ce.school_id = ?)
               AND ce.start_at >= NOW() - INTERVAL 1 DAY
             ORDER BY ce.start_at LIMIT 60", [$uid, $u['school_id']]);
        foreach ($events as &$ev) {
            $ev['start_time'] = date('H:i', strtotime($ev['event_date']));
        }
        $dfExam = demo_filter('e');
        $exams = Database::all(
            "SELECT e.title, e.start_time AS event_date, e.duration_min, c.title AS course_title, 'exam' AS event_type
             FROM exams e JOIN courses c ON c.id = e.course_id
             JOIN course_enrollments en ON en.course_id = e.course_id AND en.user_id = ?
             WHERE e.status = 'published' AND e.end_time > NOW() $dfExam", [$uid]);
        $dfAssign = demo_filter('a');
        $assigns = Database::all(
            "SELECT a.title, a.due_date AS event_date, 0 AS duration_min, c.title AS course_title, 'assignment' AS event_type
             FROM assignments a JOIN courses c ON c.id = a.course_id
             JOIN course_enrollments en ON en.course_id = a.course_id AND en.user_id = ?
             WHERE 1=1 $dfAssign", [$uid]);
        $all = array_merge($events, $exams, $assigns);
        usort($all, fn($a, $b) => strtotime($a['event_date']) <=> strtotime($b['event_date']));
        Router::render('app/student/schedule', ['title' => 'My Schedule', 'all' => $all]);
    }
}

/* =============== STUDENT: leaderboard =============== */
class Ctl_leaderboard {
    public function run(): void {
        $u = require_role('student');
        require_student_feature('leaderboard');
        if (inactive_student($u)) { flash('info', 'Inactive (re-exam) accounts have access to courses and exams only.'); redirect('student/dashboard'); }
        $uid = (int)$u['id'];
        $board = Database::all(
            "SELECT us.id, CONCAT(us.first_name, ' ', us.last_name) AS name, us.avatar, us.student_id, us.xp
             FROM users us
             WHERE us.role = 'student' AND us.school_id = ?
             ORDER BY us.xp DESC LIMIT 10", [(int)$u['school_id']]);
        $rank = 1;
        foreach ($board as &$b) { $b['rank'] = $rank++; }
        $me = null;
        foreach ($board as $b) if ((int)$b['id'] === $uid) { $me = $b; break; }
        Router::render('app/student/leaderboard', ['title' => 'Leaderboard', 'board' => $board, 'me' => $me]);
    }
}

/* =============== STUDENT: grade detail (one subject, all semesters) =============== */
class Ctl_grades_subject {
    public function run(): void {
        $u = require_role('student');
        if (inactive_student($u)) { flash('info', 'Inactive (re-exam) accounts have access to courses and exams only.'); redirect('student/dashboard'); }
        $uid = (int)$u['id'];
        $id = (int)($_GET['id'] ?? 0);
        $course = Database::one(
            "SELECT c.*, u.first_name AS tfirst, u.last_name AS tlast
             FROM courses c JOIN users u ON u.id = c.teacher_id WHERE c.id = ?", [$id]);
        if (!$course) { flash('danger', 'Subject not found.'); redirect('student/grades'); }
        $semesters = Database::all(
            "SELECT s.*, y.name AS year_name FROM semesters s
             JOIN academic_years y ON y.id = s.year_id
             WHERE s.name <> '' ORDER BY s.start_date");
        $semesterOf = function (?string $ts) use ($semesters): array {
            if (!$ts) return ['name' => 'Other', 'order' => 0];
            $t = strtotime($ts);
            foreach ($semesters as $s) {
                $st = $s['start_date'] ? strtotime($s['start_date']) : null;
                $en = $s['end_date'] ? strtotime($s['end_date']) : null;
                if ($st && $t >= $st && (!$en || $t <= $en)) return ['name' => trim($s['name'] . ' · ' . $s['year_name']), 'order' => (int)$s['year_id']];
            }
            return ['name' => 'Other', 'order' => 0];
        };
        $dfEa = demo_filter('a');
        $exams = Database::all(
            "SELECT e.id, e.title, a.score, a.total_points, a.submitted_at, e.passing_score
             FROM exam_attempts a JOIN exams e ON e.id = a.exam_id
             WHERE a.student_id = ? AND a.status = 'graded' AND e.course_id = ? $dfEa
             ORDER BY a.submitted_at DESC", [$uid, $id]);
        $dfAs = demo_filter('s');
        $assigns = Database::all(
            "SELECT a.title, s.score, a.max_score, s.graded_at, s.feedback
             FROM assignment_submissions s JOIN assignments a ON a.id = s.assignment_id
             WHERE s.student_id = ? AND s.status = 'graded' AND a.course_id = ? $dfAs
             ORDER BY s.graded_at DESC", [$uid, $id]);
        $groups = [];
        foreach ($exams as &$x) {
            $sem = $semesterOf($x['submitted_at']);
            $groups[$sem['name']][] = ['kind' => 'exam', 'title' => $x['title'], 'score' => $x['score'],
                'max' => $x['total_points'], 'pass' => (float)$x['passing_score'], 'at' => $x['submitted_at']];
        }
        foreach ($assigns as &$a) {
            $sem = $semesterOf($a['graded_at']);
            $groups[$sem['name']][] = ['kind' => 'assignment', 'title' => $a['title'], 'score' => $a['score'],
                'max' => $a['max_score'], 'pass' => 0, 'at' => $a['graded_at'], 'feedback' => $a['feedback']];
        }
        foreach ($groups as $name => &$rows) {
            usort($rows, fn($a2, $b2) => strtotime($b2['at']) - strtotime($a2['at']));
        }
        Router::render('app/student/grades_subject', [
            'title' => $course['title'] . ' — Grades', 'course' => $course,
            'groups' => $groups, 'exams' => $exams, 'assigns' => $assigns,
        ]);
    }
}
