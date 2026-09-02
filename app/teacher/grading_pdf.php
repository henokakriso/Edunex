<?php
/**
 * Grading PDF generator — student, class, exam, teacher reports
 */
require_once __DIR__ . '/grading.php';

class Ctl_grading_pdf {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];
        $type = $_GET['type'] ?? 'student';
        $courseId = (int)($_GET['course'] ?? 0);
        $assessmentId = (int)($_GET['id'] ?? 0);

        if ($courseId) {
            $ownCourse = Database::scalar("SELECT id FROM courses WHERE id = ? AND teacher_id = ?", [$courseId, $uid]);
            if (!$ownCourse) { http_response_code(403); exit('Access denied.'); }
        }

        $school = Database::one("SELECT name FROM schools WHERE id = ?", [$u['school_id'] ?? 1]);
        $schoolName = $school['name'] ?? 'Edunex School';
        $teacher = Database::one("SELECT first_name, last_name FROM users WHERE id = ?", [$uid]);
        $teacherName = $teacher['first_name'] . ' ' . $teacher['last_name'];

        $html = '';
        $title = '';

        switch ($type) {
            case 'student':
                $title = 'Student Result Report';
                $html = $this->studentReport($courseId, $uid, $schoolName, $teacherName);
                break;
            case 'class':
                $title = 'Class Result Report';
                $html = $this->classReport($courseId, $uid, $schoolName, $teacherName);
                break;
            case 'exam':
                $title = 'Exam Results Report';
                $html = $this->examReport($assessmentId, $uid, $schoolName, $teacherName);
                break;
            case 'teacher':
                $title = 'Teacher Summary Report';
                $html = $this->teacherReport($uid, $schoolName, $teacherName);
                break;
            default:
                exit('Invalid report type.');
        }

        // Render PDF page
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . e($title) . '</title>';
        echo '<style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family:"Helvetica Neue",Helvetica,Arial,sans-serif; font-size:11px; color:#1d1d1f; line-height:1.5; padding:20px; }
            .header { text-align:center; border-bottom:3px solid #0071e3; padding-bottom:12px; margin-bottom:16px; }
            .header h1 { font-size:20px; font-weight:800; color:#0071e3; letter-spacing:1px; }
            .header h2 { font-size:13px; font-weight:600; color:#1d1d1f; margin-top:4px; }
            .header p { font-size:10px; color:#6e6e73; }
            table { width:100%; border-collapse:collapse; margin:12px 0; font-size:10px; }
            th { background:#f5f5f7; border:1px solid #d2d2d7; padding:6px 8px; text-align:left; font-weight:700; font-size:9px; text-transform:uppercase; letter-spacing:.5px; color:#6e6e73; }
            td { border:1px solid #d2d2d7; padding:5px 8px; }
            tr:nth-child(even) { background:#fafafa; }
            .pass { color:#34c759; font-weight:700; }
            .fail { color:#ff3b30; font-weight:700; }
            .stats { display:flex; gap:12px; margin:12px 0; }
            .stat-box { flex:1; text-align:center; border:1px solid #d2d2d7; border-radius:8px; padding:10px; }
            .stat-val { font-size:18px; font-weight:800; color:#0071e3; }
            .stat-label { font-size:9px; color:#6e6e73; text-transform:uppercase; }
            .footer { margin-top:20px; border-top:1px solid #d2d2d7; padding-top:10px; font-size:9px; color:#6e6e73; display:flex; justify-content:space-between; }
            .sig-line { border-top:1px solid #1d1d1f; width:180px; margin-top:40px; padding-top:4px; font-size:9px; text-align:center; }
            @media print { body { padding:0; } .no-print { display:none; } }
        </style></head><body>';
        echo $html;
        echo '<div class="no-print" style="text-align:center;margin-top:20px">';
        echo '<button onclick="window.print()" style="padding:10px 24px;background:#0071e3;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Print / Save as PDF</button>';
        echo '</div>';
        echo '</body></html>';
        exit;
    }

    private function studentReport(int $courseId, int $uid, string $schoolName, string $teacherName): string {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $html = '<div class="header"><h1>EDUNEX — STUDENT RESULT REPORT</h1><h2>' . e($schoolName) . '</h2><p>Course: ' . e($course['title'] ?? '') . ' (' . e($course['code'] ?? '') . ')</p></div>';

        $html .= '<table><thead><tr><th>#</th><th>Student</th><th>ID</th><th>Round 1</th><th>Round 2</th><th>Bonus</th><th>Final</th><th>Grade</th><th>Status</th></tr></thead><tbody>';

        $rank = 0;
        $finals = [];
        foreach ($students as $s) {
            $f = grading_calc_final((int)$s['id'], $courseId);
            $finals[] = ['name' => $s['last_name'] . ' ' . $s['first_name'], 'sid' => $s['sid'], 'final' => $f];
        }
        usort($finals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        foreach ($finals as $f) {
            $rank++;
            $adj = $f['final']['adjusted'];
            $cls = $f['final']['pass'] ? 'pass' : 'fail';
            $html .= "<tr>
                <td>{$rank}</td>
                <td><b>" . e($f['name']) . "</b></td>
                <td>" . e($f['sid'] ?? '—') . "</td>
                <td>" . ($f['final']['semester1'] ?? '—') . "</td>
                <td>" . ($f['final']['semester2'] ?? '—') . "</td>
                <td>+" . ($f['final']['bonus'] ?? 0) . "</td>
                <td><b>" . ($adj !== null ? e($adj) : '—') . "</b></td>
                <td><span class='{$cls}'>" . e($f['final']['letter'] ?? '—') . "</span></td>
                <td><span class='{$cls}'>" . ($f['final']['pass'] ? 'PASS' : 'FAIL') . "</span></td>
            </tr>";
        }
        $html .= '</tbody></table>';

        // Stats
        $adjScores = array_filter(array_column($finals, 'final'), fn($f) => $f['adjusted'] !== null);
        $adjScores = array_column($adjScores, 'adjusted');
        if ($adjScores) {
            $html .= '<div class="stats">';
            $html .= '<div class="stat-box"><div class="stat-val">' . count($students) . '</div><div class="stat-label">Students</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . round(array_sum($adjScores) / count($adjScores), 1) . '</div><div class="stat-label">Average</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . max($adjScores) . '</div><div class="stat-label">Highest</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . min($adjScores) . '</div><div class="stat-label">Lowest</div></div>';
            $passRate = round(count(array_filter($finals, fn($f) => $f['final']['pass'])) / count($finals) * 100, 1);
            $html .= '<div class="stat-box"><div class="stat-val">' . $passRate . '%</div><div class="stat-label">Pass Rate</div></div>';
            $html .= '</div>';
        }

        $html .= '<div class="footer"><span>Generated: ' . date('M j, Y H:i') . '</span><span>EDUNEX Grading System</span></div>';
        $html .= '<div style="display:flex;justify-content:space-between;margin-top:30px"><div class="sig-line">Teacher</div><div class="sig-line">Director</div></div>';
        return $html;
    }

    private function classReport(int $courseId, int $uid, string $schoolName, string $teacherName): string {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $html = '<div class="header"><h1>EDUNEX — CLASS RESULT REPORT</h1><h2>' . e($schoolName) . '</h2><p>Course: ' . e($course['title'] ?? '') . ' · Teacher: ' . e($teacherName) . '</p></div>';

        // Get all assessments grouped by type
        $assessments = Database::all(
            "SELECT a.id, a.title, a.type_slug, a.max_mark, ats.label AS type_label
             FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
             WHERE a.course_id = ? AND a.status = 'published' ORDER BY ats.sort_order", [$courseId]);

        // Build header
        $html .= '<table><thead><tr><th>Rank</th><th>Student</th><th>ID</th>';
        foreach ($assessments as $a) {
            $html .= '<th style="font-size:8px">' . e($a['type_slug'] !== $a['title'] ? $a['type_slug'] : mb_substr($a['title'], 0, 8)) . '<br>' . (int)$a['max_mark'] . '</th>';
        }
        $html .= '<th>Round 1</th><th>Round 2</th><th>Bonus</th><th>Final</th><th>Grade</th></tr></thead><tbody>';

        $rank = 0;
        $allFinals = [];
        foreach ($students as $s) {
            $f = grading_calc_final((int)$s['id'], $courseId);
            $allFinals[] = ['student' => $s, 'final' => $f];
        }
        usort($allFinals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        foreach ($allFinals as $entry) {
            $rank++;
            $s = $entry['student'];
            $f = $entry['final'];
            $adj = $f['adjusted'];
            $cls = $f['pass'] ? 'pass' : 'fail';

            $html .= "<tr><td>{$rank}</td><td><b>" . e($s['last_name'] . ', ' . $s['first_name']) . "</b></td><td>" . e($s['sid'] ?? '—') . "</td>";

            // Get individual assessment marks
            foreach ($assessments as $a) {
                $grade = Database::one("SELECT mark, percentage FROM grades WHERE assessment_id = ? AND student_id = ?", [$a['id'], $s['id']]);
                $html .= '<td>' . ($grade ? e($grade['mark']) : '—') . '</td>';
            }

            $html .= "<td>" . ($f['semester1'] ?? '—') . "</td>";
            $html .= "<td>" . ($f['semester2'] ?? '—') . "</td>";
            $html .= "<td>+" . ($f['bonus'] ?? 0) . "</td>";
            $html .= "<td><b>" . ($adj !== null ? e($adj) : '—') . "</b></td>";
            $html .= "<td><span class='{$cls}'>" . e($f['letter'] ?? '—') . "</span></td></tr>";
        }
        $html .= '</tbody></table>';

        // Stats
        $adjScores = [];
        foreach ($allFinals as $e) { if ($e['final']['adjusted'] !== null) $adjScores[] = $e['final']['adjusted']; }
        if ($adjScores) {
            $html .= '<div class="stats">';
            $html .= '<div class="stat-box"><div class="stat-val">' . count($students) . '</div><div class="stat-label">Students</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . round(array_sum($adjScores) / count($adjScores), 1) . '</div><div class="stat-label">Average</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . max($adjScores) . '</div><div class="stat-label">Highest</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . min($adjScores) . '</div><div class="stat-label">Lowest</div></div>';
            $passRate = round(count(array_filter($allFinals, fn($e) => $e['final']['pass'])) / count($allFinals) * 100, 1);
            $html .= '<div class="stat-box"><div class="stat-val">' . $passRate . '%</div><div class="stat-label">Pass Rate</div></div>';
            $html .= '</div>';
        }

        $html .= '<div class="footer"><span>Generated: ' . date('M j, Y H:i') . '</span><span>EDUNEX Grading System</span></div>';
        $html .= '<div style="display:flex;justify-content:space-between;margin-top:30px"><div class="sig-line">Teacher</div><div class="sig-line">Director</div></div>';
        return $html;
    }

    private function examReport(int $assessmentId, int $uid, string $schoolName, string $teacherName): string {
        $assessment = Database::one(
            "SELECT a.*, ats.label AS type_label, c.title AS course_title
             FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
             JOIN courses c ON c.id = a.course_id
             WHERE a.id = ? AND a.teacher_id = ?", [$assessmentId, $uid]);
        if (!$assessment) return '<p>Assessment not found.</p>';

        $grades = Database::all(
            "SELECT g.*, u.first_name, u.last_name, u.student_id AS sid
             FROM grades g JOIN users u ON u.id = g.student_id
             WHERE g.assessment_id = ?
             ORDER BY g.mark DESC", [$assessmentId]);

        $html = '<div class="header"><h1>EDUNEX — EXAM RESULTS</h1><h2>' . e($assessment['title']) . '</h2><p>' . e($assessment['type_label'] ?? $assessment['type_slug']) . ' · ' . e($assessment['course_title']) . ' · Max: ' . (int)$assessment['max_mark'] . '</p></div>';

        $html .= '<table><thead><tr><th>Rank</th><th>Student</th><th>ID</th><th>Mark</th><th>Out Of</th><th>Percentage</th><th>Grade</th></tr></thead><tbody>';

        $rank = 0;
        foreach ($grades as $g) {
            $rank++;
            $cls = ($g['percentage'] ?? 0) >= 50 ? 'pass' : 'fail';
            $html .= "<tr>
                <td>{$rank}</td>
                <td><b>" . e($g['last_name'] . ', ' . $g['first_name']) . "</b></td>
                <td>" . e($g['sid'] ?? '—') . "</td>
                <td><b>" . e($g['mark'] ?? '—') . "</b></td>
                <td>" . (int)$assessment['max_mark'] . "</td>
                <td>" . ($g['percentage'] !== null ? e($g['percentage']) . '%' : '—') . "</td>
                <td><span class='{$cls}'>" . e($g['letter_grade'] ?? '—') . "</span></td>
            </tr>";
        }
        $html .= '</tbody></table>';

        $pcts = array_filter(array_column($grades, 'percentage'), fn($p) => $p !== null);
        if ($pcts) {
            $html .= '<div class="stats">';
            $html .= '<div class="stat-box"><div class="stat-val">' . count($grades) . '</div><div class="stat-label">Students</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . round(array_sum($pcts) / count($pcts), 1) . '%</div><div class="stat-label">Average</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . max($pcts) . '%</div><div class="stat-label">Highest</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . min($pcts) . '%</div><div class="stat-label">Lowest</div></div>';
            $passRate = round(count(array_filter($pcts, fn($p) => $p >= 50)) / count($pcts) * 100, 1);
            $html .= '<div class="stat-box"><div class="stat-val">' . $passRate . '%</div><div class="stat-label">Pass Rate</div></div>';
            $html .= '</div>';
        }

        $html .= '<div class="footer"><span>Generated: ' . date('M j, Y H:i') . '</span><span>EDUNEX Grading System</span></div>';
        return $html;
    }

    private function teacherReport(int $uid, string $schoolName, string $teacherName): string {
        $courses = Database::all(
            "SELECT c.id, c.title, c.code,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
             FROM courses c WHERE c.teacher_id = ? AND c.status = 'published' ORDER BY c.title", [$uid]);

        $html = '<div class="header"><h1>EDUNEX — TEACHER SUMMARY</h1><h2>' . e($schoolName) . '</h2><p>Teacher: ' . e($teacherName) . '</p></div>';

        $totalStudents = 0;
        $allAvgs = [];
        $allPassRates = [];

        $html .= '<table><thead><tr><th>Course</th><th>Code</th><th>Students</th><th>Avg Score</th><th>Pass Rate</th><th>Assessments</th></tr></thead><tbody>';

        foreach ($courses as $c) {
            $assessCount = (int)Database::scalar("SELECT COUNT(*) FROM assessments WHERE course_id = ? AND status = 'published'", [$c['id']], 0);
            $finals = grading_calc_final_for_course((int)$c['id']);
            $avg = $finals['avg'] ?? null;
            $passRate = $finals['pass_rate'] ?? null;
            if ($avg !== null) $allAvgs[] = $avg;
            if ($passRate !== null) $allPassRates[] = $passRate;
            $totalStudents += (int)$c['students'];

            $html .= "<tr>
                <td><b>" . e($c['title']) . "</b></td>
                <td>" . e($c['code'] ?? '—') . "</td>
                <td>" . (int)$c['students'] . "</td>
                <td>" . ($avg !== null ? e($avg) . '%' : '—') . "</td>
                <td>" . ($passRate !== null ? e($passRate) . '%' : '—') . "</td>
                <td>" . $assessCount . "</td>
            </tr>";
        }
        $html .= '</tbody></table>';

        if ($allAvgs) {
            $html .= '<div class="stats">';
            $html .= '<div class="stat-box"><div class="stat-val">' . count($courses) . '</div><div class="stat-label">Courses</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . $totalStudents . '</div><div class="stat-label">Total Students</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . round(array_sum($allAvgs) / count($allAvgs), 1) . '%</div><div class="stat-label">Overall Average</div></div>';
            $html .= '<div class="stat-box"><div class="stat-val">' . round(array_sum($allPassRates) / count($allPassRates), 1) . '%</div><div class="stat-label">Avg Pass Rate</div></div>';
            $html .= '</div>';
        }

        $html .= '<div class="footer"><span>Generated: ' . date('M j, Y H:i') . '</span><span>EDUNEX Grading System</span></div>';
        $html .= '<div style="display:flex;justify-content:space-between;margin-top:30px"><div class="sig-line">Teacher</div><div class="sig-line">Director</div></div>';
        return $html;
    }
}
