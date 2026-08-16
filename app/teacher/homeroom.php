<?php
/* =============== TEACHER: homeroom class overview =============== */
class Ctl_homeroom {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];
        $groups = Database::all("SELECT * FROM student_groups WHERE homeroom_teacher_id = ? ORDER BY name", [$uid]);
        if (!$groups) {
            flash('info', 'You are not assigned as homeroom teacher of any class yet. Ask your director to assign one.');
            redirect('teacher/dashboard');
        }

        $gid = (int)($_GET['group'] ?? $groups[0]['id']);
        $group = null;
        foreach ($groups as $g) if ((int)$g['id'] === $gid) $group = $g;
        if (!$group) { flash('danger', 'Class not found.'); redirect('teacher/homeroom'); }

        $students = Database::all(
            "SELECT us.id, us.student_id, us.enrollment_status, CONCAT(us.first_name,' ',us.last_name) AS name
             FROM users us WHERE us.role = 'student' AND us.group_id = ? AND us.status = 'active'
             ORDER BY us.student_id", [$gid]);

        // per-student semester stats: exam pct, assignment pct, combined average, attendance
        $rows = [];
        $earnedAll = 0; $totalAll = 0; $attPres = 0; $attTotal = 0;
        foreach ($students as &$s) {
            $s['exams_pct'] = null; $s['assign_pct'] = null; $s['avg'] = null;
            $exams = Database::all(
                "SELECT score, total_points FROM exam_attempts
                 WHERE student_id = ? AND status = 'graded' AND score IS NOT NULL AND total_points > 0", [$s['id']]);
            $exPcts = [];
            foreach ($exams as $r) $exPcts[] = (float)$r['score'] / (float)$r['total_points'] * 100;
            if ($exPcts) $s['exams_pct'] = round(array_sum($exPcts) / count($exPcts), 1);

            $assigns = Database::all(
                "SELECT a.score, asg.max_score FROM assignment_submissions a JOIN assignments asg ON asg.id = a.assignment_id
                 WHERE a.student_id = ? AND a.status = 'graded' AND a.score IS NOT NULL AND asg.max_score > 0", [$s['id']]);
            $asPcts = [];
            foreach ($assigns as $r) $asPcts[] = (float)$r['score'] / (float)$r['max_score'] * 100;
            if ($asPcts) $s['assign_pct'] = round(array_sum($asPcts) / count($asPcts), 1);

            $earn = array_sum(array_column($exams, 'score')) + array_sum(array_column($assigns, 'score'));
            $tot = array_sum(array_column($exams, 'total_points')) + array_sum(array_column($assigns, 'max_score'));
            if ($tot > 0) $s['avg'] = round($earn / $tot * 100, 1);
            $earnedAll += $earn; $totalAll += $tot;

            $att = Database::one(
                "SELECT COUNT(*) AS n, COALESCE(SUM(status = 'present'),0) AS pres FROM attendance WHERE student_id = ?", [$s['id']]);
            $s['att_pct'] = ($att && (int)$att['n'] > 0) ? round((int)$att['pres'] / (int)$att['n'] * 100, 1) : null;
            $attPres += (int)($att['pres'] ?? 0); $attTotal += (int)($att['n'] ?? 0);
        }
        unset($s);

        // class rank by combined average (students without results rank after those with)
        usort($students, fn($a, $b) => ($b['avg'] ?? -1) <=> ($a['avg'] ?? -1));
        $rank = 1;
        foreach ($students as $i => &$s) { $s['rank'] = $s['avg'] !== null ? $rank++ : null; }
        unset($s);

        // per-course subject summary for the class
        $subjects = Database::all(
            "SELECT c.id, c.title AS course, c.teacher_id, CONCAT(t.first_name,' ',t.last_name) AS teacher, s.name AS subject,
                    COUNT(DISTINCT ce.user_id) AS enrolled
             FROM course_enrollments ce
             JOIN courses c ON c.id = ce.course_id
             JOIN users t ON t.id = c.teacher_id
             JOIN subjects s ON s.id = c.subject_id
             WHERE ce.user_id IN (SELECT id FROM users WHERE role = 'student' AND group_id = ? AND status = 'active')
             GROUP BY c.id ORDER BY s.name, c.title", [$gid]);
        foreach ($subjects as &$sub) {
            $ex = Database::all(
                "SELECT t.score, t.total_points FROM exam_attempts t
                 JOIN course_enrollments ce ON ce.user_id = t.student_id
                 WHERE ce.course_id = ? AND t.status = 'graded' AND t.score IS NOT NULL AND t.total_points > 0
                 AND t.student_id IN (SELECT id FROM users WHERE role = 'student' AND group_id = ? AND status = 'active')",
                [$sub['id'], $gid]);
            $pct = [];
            foreach ($ex as $r) $pct[] = (float)$r['score'] / (float)$r['total_points'] * 100;
            $sub['exam_pct'] = $pct ? round(array_sum($pct) / count($pct), 1) : null;
            $sub['exam_n'] = count($pct);

            $asg = Database::all(
                "SELECT a.score, asg.max_score FROM assignment_submissions a
                 JOIN assignments asg ON asg.id = a.assignment_id
                 JOIN course_enrollments ce ON ce.user_id = a.student_id
                 WHERE ce.course_id = ? AND a.status = 'graded' AND a.score IS NOT NULL AND asg.max_score > 0
                 AND a.student_id IN (SELECT id FROM users WHERE role = 'student' AND group_id = ? AND status = 'active')",
                [$sub['id'], $gid]);
            $ap = [];
            foreach ($asg as $r) $ap[] = (float)$r['score'] / (float)$r['max_score'] * 100;
            $sub['assign_pct'] = $ap ? round(array_sum($ap) / count($ap), 1) : null;
            $sub['assign_n'] = count($ap);
        }
        unset($sub);

        $classAvg = $totalAll > 0 ? round($earnedAll / $totalAll * 100, 1) : null;
        $classAtt = $attTotal > 0 ? round($attPres / $attTotal * 100, 1) : null;
        $top = null;
        foreach ($students as $s) if ($s['avg'] !== null && ($top === null || $s['avg'] > $top['avg'])) $top = $s;
        $withResults = count(array_filter($students, fn($s) => $s['avg'] !== null));

        Router::render('app/teacher/homeroom', [
            'title' => 'Homeroom — ' . $group['name'],
            'groups' => $groups, 'group' => $group,
            'students' => $students, 'subjects' => $subjects,
            'classAvg' => $classAvg, 'classAtt' => $classAtt, 'top' => $top, 'withResults' => $withResults,
        ]);
    }
}
