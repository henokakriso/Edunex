<?php
/**
 * Analytics: per-role dashboards with charts
 */

class Ctl_student {
    public function run(): void {
        $u = require_login();
        $uid = (int)($u['role'] === 'parent' ? ($_GET['student'] ?? 0) : $u['id']);
        if (!$uid && $u['role'] === 'parent') {
            $kids = Database::all("SELECT id FROM users WHERE parent_id = ?", [$u['id']]);
            $uid = (int)($kids[0]['id'] ?? 0);
        }
        if (!$uid) { flash('info', 'No student linked.'); redirect('dashboard'); }
        if ($u['role'] !== 'parent' && (int)$u['id'] !== $uid) { flash('danger', 'Access denied.'); redirect('dashboard'); }

        $series = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $series[] = [
                'date' => $d,
                'xp' => (int)Database::scalar("SELECT COALESCE(SUM(xp),0) FROM (SELECT COUNT(*)*10 AS xp FROM lesson_progress WHERE user_id = ? AND DATE(last_accessed) = ?) t", [$uid, $d], 0),
                'lessons' => (int)Database::scalar("SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND completed = 1 AND DATE(last_accessed) = ?", [$uid, $d], 0),
            ];
        }
        $dfCe = demo_filter('ce');
        $perCourse = Database::all(
            "SELECT c.title, ce.progress, ce.completed,
                    (SELECT AVG(t.score/t.total_points*100) FROM exam_attempts t JOIN exams e ON e.id = t.exam_id WHERE t.student_id = ? AND e.course_id = c.id AND t.status='graded' AND t.total_points > 0) AS avg_score
             FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE ce.user_id = ? $dfCe", [$uid, $uid]);
        $dfAt = demo_filter('at');
        $attendance = Database::all("SELECT status, COUNT(*) AS n FROM attendance at WHERE student_id = ? $dfAt GROUP BY status", [$uid]);
        $dfEa = demo_filter('t');
        $exams = Database::all(
            "SELECT e.title, c.title AS course_title, t.score, t.total_points, t.status, t.submitted_at FROM exam_attempts t
             JOIN exams e ON e.id = t.exam_id JOIN courses c ON c.id = e.course_id
             WHERE t.student_id = ? AND t.status = 'graded' $dfEa ORDER BY t.submitted_at DESC LIMIT 12", [$uid]);
        $student = Database::one("SELECT * FROM users WHERE id = ?", [$uid]);

        /* === Performance trend data === */

        // Subject performance: average exam score per subject
        $dfEa2 = demo_filter('t');
        $subjectPerf = Database::all(
            "SELECT s.name AS subject, ROUND(AVG(t.score / NULLIF(t.total_points, 0) * 100), 1) AS avg_pct,
                    COUNT(t.id) AS exam_count
             FROM exam_attempts t
             JOIN exams e ON e.id = t.exam_id
             JOIN courses c ON c.id = e.course_id
             JOIN subjects s ON s.id = c.subject_id
             WHERE t.student_id = ? AND t.status = 'graded' AND t.total_points > 0 $dfEa2
             GROUP BY s.id ORDER BY avg_pct DESC", [$uid]);

        // Assignment completion rate
        $totalAssign = (int)Database::scalar(
            "SELECT COUNT(DISTINCT a.id) FROM assignments a
             JOIN courses c ON c.id = a.course_id
             JOIN course_enrollments ce ON ce.course_id = c.id AND ce.user_id = ?
             WHERE a.course_id IN (SELECT course_id FROM course_enrollments WHERE user_id = ?)", [$uid, $uid], 0);
        $submittedAssign = (int)Database::scalar(
            "SELECT COUNT(DISTINCT sub.assignment_id) FROM assignment_submissions sub
             WHERE sub.student_id = ?", [$uid], 0);
        $assignCompletion = $totalAssign > 0 ? round($submittedAssign / $totalAssign * 100, 1) : 0;

        // Exam score trend: scores over time for the performance chart
        $dfEa3 = demo_filter('t');
        $examTrend = Database::all(
            "SELECT DATE(t.submitted_at) AS dt, ROUND(t.score / NULLIF(t.total_points, 0) * 100, 1) AS pct
             FROM exam_attempts t
             WHERE t.student_id = ? AND t.status = 'graded' AND t.total_points > 0 $dfEa3
             ORDER BY t.submitted_at ASC", [$uid]);
        $trendLabels = array_column($examTrend, 'dt');
        $trendValues = array_map('floatval', array_column($examTrend, 'pct'));

        // Moving average (3-point) for measured trend
        $trendMA = [];
        $n = count($trendValues);
        for ($i = 0; $i < $n; $i++) {
            $w = array_slice($trendValues, max(0, $i - 2), 3);
            $trendMA[] = $w ? round(array_sum($w) / count($w), 1) : 0;
        }

        // Simple linear regression prediction (next 3 exams)
        $prediction = null;
        if ($n >= 3) {
            $xVals = range(1, $n);
            $yVals = $trendValues;
            $sumX = array_sum($xVals);
            $sumY = array_sum($yVals);
            $sumXY = 0; $sumX2 = 0;
            for ($i = 0; $i < $n; $i++) {
                $sumXY += $xVals[$i] * $yVals[$i];
                $sumX2 += $xVals[$i] * $xVals[$i];
            }
            $denom = ($n * $sumX2 - $sumX * $sumX);
            if ($denom != 0) {
                $slope = ($n * $sumXY - $sumX * $sumY) / $denom;
                $intercept = ($sumY - $slope * $sumX) / $n;
                $prediction = [
                    'next3' => [
                        round(max(0, min(100, $intercept + $slope * ($n + 1))), 1),
                        round(max(0, min(100, $intercept + $slope * ($n + 2))), 1),
                        round(max(0, min(100, $intercept + $slope * ($n + 3))), 1),
                    ],
                    'slope' => round($slope, 2),
                    'trend' => $slope > 0.5 ? 'improving' : ($slope < -0.5 ? 'declining' : 'stable'),
                ];
            }
        }

        // Overall GPA estimate
        $overallAvg = $exams
            ? round(array_sum(array_map(fn($e) => $e['total_points'] > 0 ? ($e['score'] / $e['total_points'] * 100) : 0, $exams)) / count($exams), 1)
            : 0;

        $attTotal = array_sum(array_column($attendance, 'n'));
        $attPresent = 0;
        foreach ($attendance as $a) { if ($a['status'] === 'present') $attPresent = (int)$a['n']; }
        $attRate = $attTotal > 0 ? round($attPresent / $attTotal * 100, 1) : 0;

        Router::render('app/analytics/student', [
            'title' => 'Student Analytics', 'series' => $series, 'perCourse' => $perCourse,
            'attendance' => $attendance, 'exams' => $exams, 'student' => $student, 'u_role' => $u['role'],
            'subjectPerf' => $subjectPerf,
            'assignCompletion' => $assignCompletion, 'totalAssign' => $totalAssign, 'submittedAssign' => $submittedAssign,
            'trendLabels' => $trendLabels, 'trendValues' => $trendValues, 'trendMA' => $trendMA,
            'prediction' => $prediction,
            'overallAvg' => $overallAvg, 'attRate' => $attRate,
        ]);
    }
}

class Ctl_teacher {
    /** Runs the native analytics_c helper on the raw data; falls back to PHP math. */
    private static function analyze(array $courses, array $series): array {
        $csv = "#series\n";
        foreach ($series as $p) {
            $csv .= (string)$p['date'] . ',' . (int)$p['enrollments'] . "\n";
        }
        $csv .= "#items\n";
        foreach ($courses as $c) {
            $csv .= str_replace(["\r", "\n"], ' ', (string)$c['title']) . ',' . (int)$c['students'] . "\n";
        }
        $csv .= "#scores\n";
        foreach ($courses as $c) {
            $csv .= (float)($c['avg_progress'] ?? 0) . "\n";
        }

        $bin = STORAGE_PATH . '/bin/analytics_c';
        if (is_file($bin) && is_executable($bin)) {
            $spec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $proc = @proc_open([$bin], $spec, $pipes, STORAGE_PATH . '/bin');
            if (is_resource($proc)) {
                fwrite($pipes[0], $csv);
                fclose($pipes[0]);
                stream_set_blocking($pipes[1], false);
                $out = '';
                $start = microtime(true);
                while (($chunk = fread($pipes[1], 8192)) !== '' && $chunk !== false) {
                    $out .= $chunk;
                    if (microtime(true) - $start > 3) break;
                }
                fclose($pipes[1]);
                fclose($pipes[2]);
                $rc = proc_close($proc);
                if ($rc === 0 && $out) {
                    $j = json_decode($out, true);
                    if (is_array($j) && isset($j['series'], $j['items'])) {
                        return $j;
                    }
                }
            }
        }

        /* PHP fallback — identical shape */
        $vals = array_map('intval', array_column($series, 'enrollments'));
        $n = count($vals);
        $half = intdiv($n, 2);
        $first = array_sum(array_slice($vals, 0, $half));
        $second = array_sum(array_slice($vals, $half));
        $ma = [];
        for ($i = 0; $i < $n; $i++) {
            $w = array_slice($vals, max(0, $i - 1), 3);
            $ma[] = $w ? round(array_sum($w) / count($w), 1) : 0;
        }
        $totalStudents = (int)array_sum(array_map('intval', array_column($courses, 'students')));
        $sorted = $courses;
        usort($sorted, fn($a, $b) => (int)$b['students'] <=> (int)$a['students']);
        $items = [];
        foreach ($sorted as $i => $c) {
            $v = (int)$c['students'];
            $items[] = ['label' => (string)$c['title'], 'value' => $v, 'pct' => $totalStudents ? round($v / $totalStudents * 100, 1) : 0, 'rank' => $i + 1];
        }
        $avgs = array_values(array_map('floatval', array_column($courses, 'avg_progress')));
        $sa = $avgs;
        sort($sa);
        $median = $sa ? ($n % 2 ? $sa[intdiv($n, 2)] : ($sa[$n / 2 - 1] + $sa[$n / 2]) / 2) : 0;
        return [
            'series' => [
                'total' => array_sum($vals), 'mean' => $n ? round(array_sum($vals) / $n, 1) : 0,
                'max' => $vals ? max($vals) : 0, 'growth' => $first ? round(($second - $first) / $first * 100, 1) : 0.0, 'ma' => $ma,
            ],
            'items' => $items,
            'scores' => ['mean' => $avgs ? round(array_sum($avgs) / count($avgs), 1) : 0, 'min' => $sa[0] ?? 0, 'max' => $sa[count($sa) - 1] ?? 0, 'median' => $median],
        ];
    }

    public function run(): void {
        $u = require_role('teacher', 'principal', 'lecturer');
        $uid = (int)$u['id'];
        $isDirector = $u['role'] === 'principal';
        if ($isDirector) {
            $courses = Database::all(
                "SELECT c.id, c.title, s.name AS subject_name,
                        (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students,
                        (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lessons,
                        (SELECT COUNT(*) FROM exam_attempts t JOIN exams e ON e.id = t.exam_id WHERE e.course_id = c.id AND t.status = 'graded') AS graded,
                        (SELECT ROUND(AVG(ce2.progress)) FROM course_enrollments ce2 WHERE ce2.course_id = c.id) AS avg_progress
                 FROM courses c JOIN subjects s ON s.id = c.subject_id WHERE c.school_id = ? ORDER BY c.created_at", [$u['school_id']]);
            $series = [];
            for ($i = 29; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days"));
                $series[] = ['date' => $d, 'enrollments' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE c.school_id = ? AND DATE(ce.enrolled_at) = ?", [$u['school_id'], $d], 0)];
            }
            $topStudents = Database::all(
                "SELECT us.id, CONCAT(us.first_name, ' ', us.last_name) AS name, us.student_id, us.xp,
                        (SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON l.id = lp.lesson_id JOIN courses c2 ON c2.id = l.course_id WHERE lp.user_id = us.id AND lp.completed = 1 AND c2.school_id = ?) AS lessons_done,
                        (SELECT ROUND(AVG(t.score / t.total_points * 100)) FROM exam_attempts t JOIN exams e ON e.id = t.exam_id WHERE t.student_id = us.id AND t.status = 'graded' AND t.total_points > 0 AND e.course_id IN (SELECT id FROM courses WHERE school_id = ?)) AS exam_avg
                 FROM users us WHERE us.role = 'student' AND us.school_id = ? AND (
                     EXISTS (SELECT 1 FROM lesson_progress lp2 JOIN lessons l2 ON l2.id = lp2.lesson_id JOIN courses c3 ON c3.id = l2.course_id WHERE lp2.user_id = us.id AND lp2.completed = 1 AND c3.school_id = ?)
                     OR EXISTS (SELECT 1 FROM exam_attempts t3 JOIN exams e3 ON e3.id = t3.exam_id WHERE t3.student_id = us.id AND t3.status = 'graded' AND e3.course_id IN (SELECT id FROM courses WHERE school_id = ?))
                 )
                 ORDER BY lessons_done DESC, exam_avg DESC LIMIT 10", [$u['school_id'], $u['school_id'], $u['school_id'], $u['school_id'], $u['school_id']]);
            $extra = [
                'school' => [
                    'students' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student' AND school_id = ?", [$u['school_id']], 0),
                    'teachers' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND school_id = ?", [$u['school_id']], 0),
                    'courses' => count($courses),
                    'enroll30' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE c.school_id = ? AND ce.enrolled_at >= NOW() - INTERVAL 30 DAY", [$u['school_id']], 0),
                    'avg_progress' => (float)(Database::scalar("SELECT ROUND(AVG(ce.progress)) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE c.school_id = ?", [$u['school_id']], 0) ?? 0),
                ],
                'recent' => Database::all(
                    "SELECT u.first_name, u.last_name, c.title AS course, ce.progress,
                            DATE_FORMAT(ce.enrolled_at, '%b %d, %Y') AS date
                     FROM course_enrollments ce
                     JOIN users u ON u.id = ce.user_id
                     JOIN courses c ON c.id = ce.course_id
                     WHERE c.school_id = ? ORDER BY ce.enrolled_at DESC LIMIT 6", [$u['school_id']]),
            ];
        } else {
            // Teacher: ONLY courses in the subjects assigned by the director
            $courses = SubjectAuth::courses($uid);
            $ids = array_map('intval', array_column($courses, 'id'));
            $placeholders = array_fill(0, count($ids), '?');
            $courseWhere = $ids ? "c.id IN (" . implode(',', $placeholders) . ")" : '1 = 0';
            if ($ids) {
                $stats = Database::all(
                    "SELECT c.id,
                            (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students,
                            (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lessons,
                            (SELECT COUNT(*) FROM exam_attempts t JOIN exams e ON e.id = t.exam_id WHERE e.course_id = c.id AND t.status = 'graded') AS graded,
                            (SELECT ROUND(AVG(ce2.progress)) FROM course_enrollments ce2 WHERE ce2.course_id = c.id) AS avg_progress
                     FROM courses c WHERE $courseWhere", $ids);
                $stats = array_column($stats, null, 'id');
                foreach ($courses as &$c) {
                    $s = $stats[(int)$c['id']] ?? [];
                    $c['students'] = (int)($s['students'] ?? 0);
                    $c['lessons'] = (int)($s['lessons'] ?? 0);
                    $c['graded'] = (int)($s['graded'] ?? 0);
                    $c['avg_progress'] = (float)($s['avg_progress'] ?? 0);
                }
                unset($c);
            } else {
                $courses = [];
            }
            $series = [];
            if ($ids) {
                for ($i = 29; $i >= 0; $i--) {
                    $d = date('Y-m-d', strtotime("-$i days"));
                    $args = array_merge($ids, [$d]);
                    $series[] = ['date' => $d, 'enrollments' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE c.id IN (" . implode(',', $placeholders) . ") AND DATE(ce.enrolled_at) = ?", $args, 0)];
                }
            }
            $topStudents = $ids ? Database::all(
                "SELECT us.id, CONCAT(us.first_name, ' ', us.last_name) AS name, us.student_id, us.xp,
                        (SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON l.id = lp.lesson_id WHERE lp.user_id = us.id AND lp.completed = 1 AND l.course_id IN (" . implode(',', $placeholders) . ")) AS lessons_done,
                        (SELECT ROUND(AVG(t.score / t.total_points * 100)) FROM exam_attempts t JOIN exams e ON e.id = t.exam_id WHERE t.student_id = us.id AND t.status = 'graded' AND t.total_points > 0 AND e.course_id IN (" . implode(',', $placeholders) . ")) AS exam_avg
                 FROM users us WHERE us.role = 'student' AND us.school_id = ? AND (
                     EXISTS (SELECT 1 FROM lesson_progress lp2 JOIN lessons l2 ON l2.id = lp2.lesson_id WHERE lp2.user_id = us.id AND lp2.completed = 1 AND l2.course_id IN (" . implode(',', $placeholders) . "))
                     OR EXISTS (SELECT 1 FROM exam_attempts t3 JOIN exams e3 ON e3.id = t3.exam_id WHERE t3.student_id = us.id AND t3.status = 'graded' AND e3.course_id IN (" . implode(',', $placeholders) . "))
                 )
                 ORDER BY lessons_done DESC, exam_avg DESC LIMIT 10", array_merge($ids, [$u['school_id']], $ids, $ids)) : [];
        }
        $analysis = self::analyze($courses, $series);
        Router::render('app/analytics/teacher', [
            'title' => $isDirector ? 'School Analytics' : 'Teacher Analytics',
            'courses' => $courses, 'series' => $series, 'topStudents' => $topStudents,
            'analysis' => $analysis, 'is_director' => $isDirector,
            'extra' => $extra ?? null,
        ]);
    }
}

class Ctl_admin {
    public function run(): void {
        require_role('ministry');
        redirect('admin/analytics');
    }
}
