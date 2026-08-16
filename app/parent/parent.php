<?php
/**
 * Parent module: dashboard, children overview, reports
 */

function parent_children(array $u): array {
    $kids = Database::all("SELECT * FROM users WHERE parent_id = ? AND role = 'student'", [$u['id']]);
    if (!$kids && $u['role'] === 'parent') {
        // fall back: any student whose account shares the parent's email domain is NOT automatic; keep explicit only
    }
    return $kids;
}

class Ctl_dashboard {
    public function run(): void {
        $u = require_role('parent');
        $children = parent_children($u);
        $summaries = [];
        foreach ($children as $c) {
            $summaries[] = child_summary($c);
        }
        $anns = Database::all("SELECT * FROM announcements WHERE audience IN ('all','parents') ORDER BY pinned DESC, created_at DESC LIMIT 5");
        Router::render('app/parent/dashboard', [
            'title' => 'Parent Dashboard', 'summaries' => $summaries, 'anns' => $anns,
        ]);
    }
}

function child_summary(array $c): array {
    $uid = (int)$c['id'];
    return [
        'child' => $c,
        'courses' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments WHERE user_id = ?", [$uid], 0),
        'avg_progress' => (float)Database::scalar("SELECT COALESCE(AVG(progress),0) FROM course_enrollments WHERE user_id = ?", [$uid], 0),
        'completed_courses' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments WHERE user_id = ? AND completed = 1", [$uid], 0),
        'xp' => (int)$c['xp'], 'level' => (int)$c['level'], 'streak' => (int)$c['streak'],
        'attendance' => attendance_rate($uid),
        'gpa' => round(avg_gpa($uid), 2),
        'upcoming' => Database::all(
            "SELECT e.title, e.start_time, c.title AS course_title FROM exams e
             JOIN courses c ON c.id = e.course_id JOIN course_enrollments ce ON ce.course_id = e.course_id
             WHERE ce.user_id = ? AND e.status = 'published' AND e.end_time > NOW() ORDER BY e.start_time LIMIT 3", [$uid]),
        'assignments' => Database::all(
            "SELECT a.title, a.due_date, s.status AS sub_status, s.score FROM assignments a
             JOIN courses c ON c.id = a.course_id JOIN course_enrollments ce ON ce.course_id = a.course_id
             LEFT JOIN assignment_submissions s ON s.assignment_id = a.id AND s.student_id = ?
             WHERE ce.user_id = ? ORDER BY a.due_date LIMIT 4", [$uid, $uid]),
    ];
}

function attendance_rate(int $uid): array {
    $rows = Database::all("SELECT status, COUNT(*) AS n FROM attendance WHERE student_id = ? GROUP BY status", [$uid]);
    $total = 0; $present = 0;
    foreach ($rows as $r) { $total += (int)$r['n']; if ($r['status'] !== 'absent') $present += (int)$r['n']; }
    return ['total' => $total, 'rate' => $total ? round($present / $total * 100) : 0, 'breakdown' => $rows];
}

function avg_gpa(int $uid): float {
    $rows = Database::all(
        "SELECT t.score, t.total_points FROM exam_attempts t WHERE t.student_id = ? AND t.status = 'graded'", [$uid]);
    if (!$rows) return 0;
    $pct = 0;
    foreach ($rows as $r) $pct += $r['total_points'] > 0 ? $r['score'] / $r['total_points'] * 100 : 0;
    $pct /= count($rows);
    // Ethiopian 4.0 scale approximation
    if ($pct >= 90) return 4.0; if ($pct >= 80) return 3.5; if ($pct >= 70) return 3.0;
    if ($pct >= 60) return 2.5; if ($pct >= 50) return 2.0; if ($pct >= 40) return 1.0;
    return 0;
}

class Ctl_children {
    public function run(): void {
        $u = require_role('parent');
        $children = parent_children($u);
        $summaries = [];
        foreach ($children as $c) $summaries[] = child_summary($c);
        Router::render('app/parent/children', ['title' => 'My Children', 'summaries' => $summaries]);
    }
}

class Ctl_reports {
    public function run(): void {
        $u = require_role('parent');
        $cid = (int)($_GET['child'] ?? 0);
        $child = null;
        foreach (parent_children($u) as $c) if ((int)$c['id'] === $cid) $child = $c;
        $summaries = [];
        foreach (parent_children($u) as $c) $summaries[] = child_summary($c);
        $detail = $child ? child_summary($child) : null;
        if ($detail) {
            $detail['grades'] = Database::all(
                "SELECT e.title, c.title AS course_title, t.score, t.total_points, t.submitted_at, t.status
                 FROM exam_attempts t JOIN exams e ON e.id = t.exam_id JOIN courses c ON c.id = e.course_id
                 WHERE t.student_id = ? ORDER BY t.submitted_at DESC", [$child['id']]);
            $detail['attendance_rows'] = Database::all(
                "SELECT at.*, c.title AS course_title FROM attendance at JOIN courses c ON c.id = at.course_id
                 WHERE at.student_id = ? ORDER BY at.date DESC LIMIT 100", [$child['id']]);
            $detail['certificates'] = Database::all(
                "SELECT cert.*, c.title AS course_title FROM certificates cert JOIN courses c ON c.id = cert.course_id
                 WHERE cert.student_id = ?", [$child['id']]);
        }
        Router::render('app/parent/reports', [
            'title' => 'Reports', 'summaries' => $summaries, 'child' => $child, 'detail' => $detail, 'cid' => $cid,
        ]);
    }
}
