<?php
/**
 * Grading PDF — viewer page with embedded PDF + download
 * ?type=student&course=15 → HTML viewer with toolbar + inline report
 * ?type=student&course=15&download=1 → raw PDF download
 */
require_once __DIR__ . '/grading.php';
require_once BASE_PATH . '/includes/Pdf.php';

class Ctl_grading_pdf {
    private string $schoolName = '';
    private string $teacherName = '';
    private string $directorName = '';

    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];
        $type = $_GET['type'] ?? 'student';
        $courseId = (int)($_GET['course'] ?? 0);
        $assessmentId = (int)($_GET['id'] ?? 0);
        $download = isset($_GET['download']);

        if ($courseId) {
            $ownCourse = Database::scalar("SELECT id FROM courses WHERE id = ? AND teacher_id = ?", [$courseId, $uid]);
            if (!$ownCourse) { http_response_code(403); exit('Access denied.'); }
        }

        $school = Database::one("SELECT name FROM schools WHERE id = ?", [$u['school_id'] ?? 1]);
        $this->schoolName = $school['name'] ?? 'Edunex School';
        $teacher = Database::one("SELECT first_name, last_name FROM users WHERE id = ?", [$uid]);
        $this->teacherName = $teacher['first_name'] . ' ' . $teacher['last_name'];
        $director = Database::one("SELECT first_name, last_name FROM users WHERE school_id = ? AND role = 'principal' LIMIT 1", [(int)($u['school_id'] ?? 1)]);
        $this->directorName = $director ? ($director['first_name'] . ' ' . $director['last_name']) : 'School Director';

        $pdf = new Pdf('landscape', 'A4', true);

        switch ($type) {
            case 'student':
                $this->studentReport($pdf, $courseId, $uid);
                $filename = 'student_result_' . date('Ymd_His') . '.pdf';
                $title = 'Student Result Report';
                $htmlBody = $this->studentReportHTML($courseId, $uid);
                break;
            case 'class':
                $this->classReport($pdf, $courseId, $uid);
                $filename = 'class_result_' . date('Ymd_His') . '.pdf';
                $title = 'Class Result Report';
                $htmlBody = $this->classReportHTML($courseId, $uid);
                break;
            case 'exam':
                $this->examReport($pdf, $assessmentId, $uid);
                $filename = 'exam_result_' . date('Ymd_His') . '.pdf';
                $title = 'Exam Results Report';
                $htmlBody = $this->examReportHTML($assessmentId, $uid);
                break;
            case 'teacher':
                $this->teacherReport($pdf, $uid);
                $filename = 'teacher_summary_' . date('Ymd_His') . '.pdf';
                $title = 'Teacher Summary';
                $htmlBody = $this->teacherReportHTML($uid);
                break;
            default:
                exit('Invalid report type.');
        }

        if ($download) {
            $pdf->output($filename, false);
            return;
        }

        $dlUrl = url('teacher/grading/pdf') . '&type=' . e($type);
        if ($type === 'exam') $dlUrl .= '&id=' . $assessmentId;
        else if ($type !== 'teacher') $dlUrl .= '&course=' . $courseId;
        $dlUrl .= '&download=1&_t=' . time();

        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f5f5f7;color:#1d1d1f}
    .bar{position:sticky;top:0;z-index:100;display:flex;align-items:center;gap:12px;padding:10px 24px;background:rgba(255,255,255,.92);backdrop-filter:blur(20px);border-bottom:1px solid rgba(0,0,0,.06)}
    .bar-title{flex:1;font-size:14px;font-weight:700}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s;text-decoration:none;line-height:1}
    .btn-back{background:rgba(0,0,0,.05);color:#1d1d1f}.btn-back:hover{background:rgba(0,0,0,.1)}
    .btn-dl{background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;box-shadow:0 2px 8px rgba(99,102,241,.3)}.btn-dl:hover{box-shadow:0 4px 16px rgba(99,102,241,.4)}
    .btn-print{background:rgba(0,0,0,.05);color:#1d1d1f}.btn-print:hover{background:rgba(0,0,0,.1)}
    .doc{max-width:900px;margin:24px auto;background:#fff;border-radius:12px;box-shadow:0 2px 20px rgba(0,0,0,.06);padding:40px 48px}
    .doc-header{text-align:center;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #e5e7eb}
    .doc-header h1{font-size:18px;font-weight:800;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px}
    .doc-header p{font-size:12px;color:#6b7280}
    .doc-meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;margin-bottom:20px;padding:12px 16px;background:#f9fafb;border-radius:8px;font-size:11px}
    .doc-meta b{color:#374151}
    .doc-meta span{color:#6b7280}
    table{width:100%;border-collapse:collapse;font-size:12px}
    th{background:#f3f4f6;padding:8px 10px;text-align:left;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#374151;border-bottom:2px solid #e5e7eb}
    td{padding:7px 10px;border-bottom:1px solid #f3f4f6}
    tr:hover td{background:#f9fafb}
    .grade-pass{color:#059669;font-weight:600}
    .grade-fail{color:#dc2626;font-weight:600}
    .signatures{margin-top:40px;padding-top:16px;border-top:1px solid #e5e7eb;display:grid;grid-template-columns:1fr 1fr;gap:40px}
    .sig-line{border-top:1px solid #9ca3af;padding-top:6px;font-size:11px;color:#6b7280}
    @media print{.bar{display:none!important}.doc{box-shadow:none;margin:0;border-radius:0;max-width:100%;padding:20px}}
  </style>
</head>
<body>
  <div class="bar">
    <button class="btn btn-back" onclick="history.back()">← Back</button>
    <div class="bar-title"><?= e($title) ?></div>
    <button class="btn btn-dl" onclick="window.location.href='<?= e($dlUrl) ?>'">⬇ Download PDF</button>
    <button class="btn btn-print" onclick="window.print()">🖨 Print</button>
  </div>
  <div class="doc">
    <?= $htmlBody ?>
  </div>
</body>
</html>
<?php
        exit;
    }

    // ──────────── HTML REPORTS ────────────

    private function signaturesHTML(): string {
        return '<div class="signatures">'
            . '<div><div class="sig-line">Teacher: ' . e($this->teacherName) . '</div></div>'
            . '<div><div class="sig-line">Director: ' . e($this->directorName) . '</div></div>'
            . '</div>';
    }

    private function studentReportHTML(int $courseId, int $uid): string {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $h = '<div class="doc-header"><h1>Student Result Report</h1><p>' . e($this->schoolName) . '</p></div>';
        $h .= '<div class="doc-meta"><div><b>Course:</b> <span>' . e($course['title'] ?? '') . '</span></div>'
            . '<div><b>Teacher:</b> <span>' . e($this->teacherName) . '</span></div>'
            . '<div><b>Date:</b> <span>' . date('F j, Y') . '</span></div>'
            . '<div><b>Students:</b> <span>' . count($students) . '</span></div></div>';

        $finals = [];
        foreach ($students as $s) {
            $f = grading_calc_final((int)$s['id'], $courseId);
            $finals[] = ['name' => $s['last_name'] . ', ' . $s['first_name'], 'sid' => $s['sid'], 'final' => $f];
        }
        usort($finals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        $h .= '<table><thead><tr><th>#</th><th>Student</th><th>ID</th><th>Round 1</th><th>Round 2</th><th>Bonus</th><th>Final</th><th>Grade</th><th>Status</th></tr></thead><tbody>';
        $rank = 0;
        foreach ($finals as $f) {
            $rank++;
            $adj = $f['final']['adjusted'];
            $pass = $f['final']['pass'];
            $h .= '<tr><td>' . $rank . '</td><td>' . e($f['name']) . '</td><td>' . e($f['sid'] ?? '—') . '</td>'
                . '<td>' . e($f['final']['semester1'] ?? '—') . '</td>'
                . '<td>' . e($f['final']['semester2'] ?? '—') . '</td>'
                . '<td>+' . (int)($f['final']['bonus'] ?? 0) . '</td>'
                . '<td><b>' . ($adj !== null ? e($adj) : '—') . '</b></td>'
                . '<td><b>' . e($f['final']['letter'] ?? '—') . '</b></td>'
                . '<td class="' . ($pass ? 'grade-pass' : 'grade-fail') . '">' . ($pass ? 'PASS' : 'FAIL') . '</td></tr>';
        }
        $h .= '</tbody></table>';

        $adjScores = array_filter(array_column($finals, 'final'), fn($f) => $f['adjusted'] !== null);
        $adjScores = array_column($adjScores, 'adjusted');
        if ($adjScores) {
            $passCount = count(array_filter($finals, fn($f) => $f['final']['pass']));
            $h .= '<div style="margin-top:16px;display:grid;grid-template-columns:repeat(5,1fr);gap:8px;font-size:12px">'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . count($students) . '</b><br><span style="color:#6b7280;font-size:10px">Students</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . round(array_sum($adjScores) / count($adjScores), 1) . '%</b><br><span style="color:#6b7280;font-size:10px">Average</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . max($adjScores) . '%</b><br><span style="color:#6b7280;font-size:10px">Highest</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . min($adjScores) . '%</b><br><span style="color:#6b7280;font-size:10px">Lowest</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . round($passCount / count($finals) * 100, 1) . '%</b><br><span style="color:#6b7280;font-size:10px">Pass Rate</span></div>'
                . '</div>';
        }

        $h .= $this->signaturesHTML();
        return $h;
    }

    private function classReportHTML(int $courseId, int $uid): string {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);
        $assessments = Database::all(
            "SELECT a.id, a.title, a.type_slug, a.max_mark, ats.label AS type_label
             FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
             WHERE a.course_id = ? AND a.status = 'published' ORDER BY ats.sort_order", [$courseId]);

        $h = '<div class="doc-header"><h1>Class Result Report</h1><p>' . e($this->schoolName) . '</p></div>';
        $h .= '<div class="doc-meta"><div><b>Course:</b> <span>' . e($course['title'] ?? '') . '</span></div>'
            . '<div><b>Teacher:</b> <span>' . e($this->teacherName) . '</span></div>'
            . '<div><b>Date:</b> <span>' . date('F j, Y') . '</span></div>'
            . '<div><b>Students:</b> <span>' . count($students) . '</span></div></div>';

        $allFinals = [];
        foreach ($students as $s) {
            $f = grading_calc_final((int)$s['id'], $courseId);
            $allFinals[] = ['student' => $s, 'final' => $f];
        }
        usort($allFinals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        $h .= '<div style="overflow-x:auto"><table><thead><tr><th>Rank</th><th>Student</th><th>ID</th>';
        foreach ($assessments as $a) $h .= '<th>' . e(mb_substr($a['type_slug'], 0, 6)) . '/' . (int)$a['max_mark'] . '</th>';
        $h .= '<th>R1</th><th>R2</th><th>Bonus</th><th>Final</th><th>Grade</th></tr></thead><tbody>';

        $rank = 0;
        foreach ($allFinals as $entry) {
            $rank++;
            $s = $entry['student'];
            $f = $entry['final'];
            $h .= '<tr><td>' . $rank . '</td><td>' . e($s['last_name'] . ', ' . $s['first_name']) . '</td><td>' . e($s['sid'] ?? '—') . '</td>';
            foreach ($assessments as $a) {
                $grade = Database::one("SELECT mark FROM grades WHERE assessment_id = ? AND student_id = ?", [$a['id'], $s['id']]);
                $h .= '<td>' . ($grade ? e($grade['mark']) : '—') . '</td>';
            }
            $h .= '<td>' . e($f['semester1'] ?? '—') . '</td><td>' . e($f['semester2'] ?? '—') . '</td>'
                . '<td>+' . (int)($f['bonus'] ?? 0) . '</td>'
                . '<td><b>' . ($f['adjusted'] !== null ? e($f['adjusted']) : '—') . '</b></td>'
                . '<td><b>' . e($f['letter'] ?? '—') . '</b></td></tr>';
        }
        $h .= '</tbody></table></div>';

        $adjScores = array_filter(array_column(array_column($allFinals, 'final'), 'adjusted'), fn($v) => $v !== null);
        if ($adjScores) {
            $passCount = count(array_filter($allFinals, fn($e) => $e['final']['pass']));
            $h .= '<div style="margin-top:16px;display:grid;grid-template-columns:repeat(5,1fr);gap:8px;font-size:12px">'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . count($students) . '</b><br><span style="color:#6b7280;font-size:10px">Students</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . round(array_sum($adjScores) / count($adjScores), 1) . '%</b><br><span style="color:#6b7280;font-size:10px">Average</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . max($adjScores) . '%</b><br><span style="color:#6b7280;font-size:10px">Highest</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . min($adjScores) . '%</b><br><span style="color:#6b7280;font-size:10px">Lowest</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . round($passCount / count($allFinals) * 100, 1) . '%</b><br><span style="color:#6b7280;font-size:10px">Pass Rate</span></div>'
                . '</div>';
        }

        $h .= $this->signaturesHTML();
        return $h;
    }

    private function examReportHTML(int $assessmentId, int $uid): string {
        $assessment = Database::one(
            "SELECT a.*, ats.label AS type_label, c.title AS course_title
             FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
             JOIN courses c ON c.id = a.course_id
             WHERE a.id = ? AND a.teacher_id = ?", [$assessmentId, $uid]);
        if (!$assessment) return '<p>Assessment not found.</p>';

        $grades = Database::all(
            "SELECT g.*, u.first_name, u.last_name, u.student_id AS sid
             FROM grades g JOIN users u ON u.id = g.student_id
             WHERE g.assessment_id = ? ORDER BY g.mark DESC", [$assessmentId]);

        $h = '<div class="doc-header"><h1>Exam Results</h1><p>' . e($this->schoolName) . '</p></div>';
        $h .= '<div class="doc-meta"><div><b>Assessment:</b> <span>' . e($assessment['title'] ?? '') . '</span></div>'
            . '<div><b>Type:</b> <span>' . e($assessment['type_label'] ?? $assessment['type_slug']) . '</span></div>'
            . '<div><b>Course:</b> <span>' . e($assessment['course_title'] ?? '') . '</span></div>'
            . '<div><b>Max Mark:</b> <span>' . (int)$assessment['max_mark'] . '</span></div>'
            . '<div><b>Date:</b> <span>' . date('F j, Y') . '</span></div></div>';

        $h .= '<table><thead><tr><th>Rank</th><th>Student</th><th>ID</th><th>Mark</th><th>Out Of</th><th>Percentage</th><th>Grade</th></tr></thead><tbody>';
        $rank = 0;
        foreach ($grades as $g) {
            $rank++;
            $pct = $g['percentage'] !== null ? e($g['percentage']) . '%' : '—';
            $h .= '<tr><td>' . $rank . '</td><td>' . e($g['last_name'] . ', ' . $g['first_name']) . '</td><td>' . e($g['sid'] ?? '—') . '</td>'
                . '<td><b>' . e($g['mark'] ?? '—') . '</b></td><td>' . (int)$assessment['max_mark'] . '</td>'
                . '<td>' . $pct . '</td><td><b>' . e($g['letter_grade'] ?? '—') . '</b></td></tr>';
        }
        $h .= '</tbody></table>';

        $pcts = array_filter(array_column($grades, 'percentage'), fn($p) => $p !== null);
        if ($pcts) {
            $passCount = count(array_filter($pcts, fn($p) => $p >= 50));
            $h .= '<div style="margin-top:16px;display:grid;grid-template-columns:repeat(5,1fr);gap:8px;font-size:12px">'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . count($grades) . '</b><br><span style="color:#6b7280;font-size:10px">Students</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . round(array_sum($pcts) / count($pcts), 1) . '%</b><br><span style="color:#6b7280;font-size:10px">Average</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . max($pcts) . '%</b><br><span style="color:#6b7280;font-size:10px">Highest</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . min($pcts) . '%</b><br><span style="color:#6b7280;font-size:10px">Lowest</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . round($passCount / count($pcts) * 100, 1) . '%</b><br><span style="color:#6b7280;font-size:10px">Pass Rate</span></div>'
                . '</div>';
        }

        $h .= $this->signaturesHTML();
        return $h;
    }

    private function teacherReportHTML(int $uid): string {
        $courses = Database::all(
            "SELECT c.id, c.title, c.code,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
             FROM courses c WHERE c.teacher_id = ? AND c.status = 'published' ORDER BY c.title", [$uid]);

        $h = '<div class="doc-header"><h1>Teacher Summary</h1><p>' . e($this->schoolName) . '</p></div>';
        $h .= '<div class="doc-meta"><div><b>Teacher:</b> <span>' . e($this->teacherName) . '</span></div>'
            . '<div><b>School:</b> <span>' . e($this->schoolName) . '</span></div>'
            . '<div><b>Date:</b> <span>' . date('F j, Y') . '</span></div>'
            . '<div><b>Courses:</b> <span>' . count($courses) . '</span></div></div>';

        $h .= '<table><thead><tr><th>Course</th><th>Code</th><th>Students</th><th>Avg Score</th><th>Pass Rate</th><th>Assessments</th></tr></thead><tbody>';
        $allAvgs = [];
        $allPassRates = [];
        foreach ($courses as $c) {
            $assessCount = (int)Database::scalar("SELECT COUNT(*) FROM assessments WHERE course_id = ? AND status = 'published'", [$c['id']], 0);
            $finals = grading_calc_final_for_course((int)$c['id']);
            $avg = $finals['avg'] ?? null;
            $pr = $finals['pass_rate'] ?? null;
            if ($avg !== null) $allAvgs[] = $avg;
            if ($pr !== null) $allPassRates[] = $pr;
            $h .= '<tr><td>' . e($c['title']) . '</td><td>' . e($c['code'] ?? '—') . '</td><td>' . (int)$c['students'] . '</td>'
                . '<td>' . ($avg !== null ? e($avg) . '%' : '—') . '</td>'
                . '<td>' . ($pr !== null ? e($pr) . '%' : '—') . '</td>'
                . '<td>' . $assessCount . '</td></tr>';
        }
        $h .= '</tbody></table>';

        if ($allAvgs) {
            $totalStudents = array_sum(array_column($courses, 'students'));
            $h .= '<div style="margin-top:16px;display:grid;grid-template-columns:repeat(4,1fr);gap:8px;font-size:12px">'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . count($courses) . '</b><br><span style="color:#6b7280;font-size:10px">Courses</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . $totalStudents . '</b><br><span style="color:#6b7280;font-size:10px">Total Students</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . round(array_sum($allAvgs) / count($allAvgs), 1) . '%</b><br><span style="color:#6b7280;font-size:10px">Overall Average</span></div>'
                . '<div style="background:#f3f4f6;padding:10px;border-radius:8px;text-align:center"><b>' . round(array_sum($allPassRates) / count($allPassRates), 1) . '%</b><br><span style="color:#6b7280;font-size:10px">Avg Pass Rate</span></div>'
                . '</div>';
        }

        $h .= $this->signaturesHTML();
        return $h;
    }

    // ──────────── PDF GENERATORS (unchanged) ────────────

    private function studentReport(Pdf $pdf, int $courseId, int $uid): void {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $pdf->setTitle('STUDENT RESULT REPORT');
        $pdf->setSubtitle($this->schoolName . ' — ' . ($course['title'] ?? '') . ' (' . ($course['code'] ?? '') . ')');
        $pdf->infoBlock([
            ['Course', $course['title'] ?? '—'],
            ['Teacher', $this->teacherName],
            ['Date', date('F j, Y')],
            ['Students', count($students)],
        ]);
        $pdf->spacer(6);

        $finals = [];
        foreach ($students as $s) {
            $f = grading_calc_final((int)$s['id'], $courseId);
            $finals[] = ['name' => $s['last_name'] . ', ' . $s['first_name'], 'sid' => $s['sid'], 'final' => $f];
        }
        usort($finals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        $rows = [];
        $rank = 0;
        foreach ($finals as $f) {
            $rank++;
            $adj = $f['final']['adjusted'];
            $rows[] = [$rank, $f['name'], $f['sid'] ?? '—', $f['final']['semester1'] ?? '—', $f['final']['semester2'] ?? '—', '+' . ($f['final']['bonus'] ?? 0), $adj !== null ? (string)$adj : '—', $f['final']['letter'] ?? '—', $f['final']['pass'] ? 'PASS' : 'FAIL'];
        }
        $pdf->table(['#', 'Student', 'ID', 'Round 1', 'Round 2', 'Bonus', 'Final', 'Grade', 'Status'], $rows);

        $adjScores = array_filter(array_column($finals, 'final'), fn($f) => $f['adjusted'] !== null);
        $adjScores = array_column($adjScores, 'adjusted');
        if ($adjScores) {
            $pdf->spacer(8);
            $pdf->summaryBox([
                ['Students', count($students)], ['Average', round(array_sum($adjScores) / count($adjScores), 1)],
                ['Highest', max($adjScores)], ['Lowest', min($adjScores)],
                ['Pass Rate', round(count(array_filter($finals, fn($f) => $f['final']['pass'])) / count($finals) * 100, 1) . '%'],
            ]);
        }
        $this->addSignatures($pdf);
    }

    private function classReport(Pdf $pdf, int $courseId, int $uid): void {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);
        $assessments = Database::all(
            "SELECT a.id, a.title, a.type_slug, a.max_mark, ats.label AS type_label
             FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
             WHERE a.course_id = ? AND a.status = 'published' ORDER BY ats.sort_order", [$courseId]);

        $pdf->setTitle('CLASS RESULT REPORT');
        $pdf->setSubtitle($this->schoolName . ' — ' . ($course['title'] ?? '') . ' · Teacher: ' . $this->teacherName);
        $pdf->infoBlock([
            ['Course', $course['title'] ?? '—'], ['Teacher', $this->teacherName],
            ['Date', date('F j, Y')], ['Students', count($students)],
        ]);
        $pdf->spacer(6);

        $headers = ['Rank', 'Student', 'ID'];
        foreach ($assessments as $a) $headers[] = mb_substr($a['type_slug'], 0, 6) . '/' . (int)$a['max_mark'];
        $headers = array_merge($headers, ['R1', 'R2', 'Bonus', 'Final', 'Grade']);

        $allFinals = [];
        foreach ($students as $s) { $f = grading_calc_final((int)$s['id'], $courseId); $allFinals[] = ['student' => $s, 'final' => $f]; }
        usort($allFinals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        $rows = [];
        $rank = 0;
        foreach ($allFinals as $entry) {
            $rank++; $s = $entry['student']; $f = $entry['final'];
            $row = [$rank, $s['last_name'] . ', ' . $s['first_name'], $s['sid'] ?? '—'];
            foreach ($assessments as $a) { $grade = Database::one("SELECT mark FROM grades WHERE assessment_id = ? AND student_id = ?", [$a['id'], $s['id']]); $row[] = $grade ? (string)$grade['mark'] : '—'; }
            $row = array_merge($row, [$f['semester1'] ?? '—', $f['semester2'] ?? '—', '+' . ($f['bonus'] ?? 0), $f['adjusted'] !== null ? (string)$f['adjusted'] : '—', $f['letter'] ?? '—']);
            $rows[] = $row;
        }
        $pdf->table($headers, $rows);

        $adjScores = array_filter(array_column(array_column($allFinals, 'final'), 'adjusted'), fn($v) => $v !== null);
        if ($adjScores) {
            $pdf->spacer(8);
            $pdf->summaryBox([
                ['Students', count($students)], ['Average', round(array_sum($adjScores) / count($adjScores), 1)],
                ['Highest', max($adjScores)], ['Lowest', min($adjScores)],
                ['Pass Rate', round(count(array_filter($allFinals, fn($e) => $e['final']['pass'])) / count($allFinals) * 100, 1) . '%'],
            ]);
        }
        $this->addSignatures($pdf);
    }

    private function examReport(Pdf $pdf, int $assessmentId, int $uid): void {
        $assessment = Database::one(
            "SELECT a.*, ats.label AS type_label, c.title AS course_title
             FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
             JOIN courses c ON c.id = a.course_id
             WHERE a.id = ? AND a.teacher_id = ?", [$assessmentId, $uid]);
        if (!$assessment) { $pdf->setTitle('ASSESSMENT NOT FOUND'); return; }

        $grades = Database::all(
            "SELECT g.*, u.first_name, u.last_name, u.student_id AS sid
             FROM grades g JOIN users u ON u.id = g.student_id
             WHERE g.assessment_id = ? ORDER BY g.mark DESC", [$assessmentId]);

        $pdf->setTitle('EXAM RESULTS');
        $pdf->setSubtitle(($assessment['title'] ?? '') . ' — ' . ($assessment['course_title'] ?? ''));
        $pdf->infoBlock([
            ['Assessment', $assessment['title'] ?? '—'], ['Type', $assessment['type_label'] ?? $assessment['type_slug']],
            ['Course', $assessment['course_title'] ?? '—'], ['Max Mark', (int)$assessment['max_mark']],
            ['Date', date('F j, Y')],
        ]);
        $pdf->spacer(6);

        $rows = [];
        $rank = 0;
        foreach ($grades as $g) {
            $rank++;
            $pct = $g['percentage'] !== null ? e($g['percentage']) . '%' : '—';
            $rows[] = [$rank, $g['last_name'] . ', ' . $g['first_name'], $g['sid'] ?? '—', $g['mark'] ?? '—', (int)$assessment['max_mark'], $pct, $g['letter_grade'] ?? '—'];
        }
        $pdf->table(['Rank', 'Student', 'ID', 'Mark', 'Out Of', 'Percentage', 'Grade'], $rows);

        $pcts = array_filter(array_column($grades, 'percentage'), fn($p) => $p !== null);
        if ($pcts) {
            $pdf->spacer(8);
            $pdf->summaryBox([
                ['Students', count($grades)], ['Average', round(array_sum($pcts) / count($pcts), 1) . '%'],
                ['Highest', max($pcts) . '%'], ['Lowest', min($pcts) . '%'],
                ['Pass Rate', round(count(array_filter($pcts, fn($p) => $p >= 50)) / count($pcts) * 100, 1) . '%'],
            ]);
        }
        $this->addSignatures($pdf);
    }

    private function teacherReport(Pdf $pdf, int $uid): void {
        $courses = Database::all(
            "SELECT c.id, c.title, c.code,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
             FROM courses c WHERE c.teacher_id = ? AND c.status = 'published' ORDER BY c.title", [$uid]);

        $pdf->setTitle('TEACHER SUMMARY');
        $pdf->setSubtitle($this->schoolName . ' — ' . $this->teacherName);
        $pdf->infoBlock([
            ['Teacher', $this->teacherName], ['School', $this->schoolName],
            ['Date', date('F j, Y')], ['Courses', count($courses)],
        ]);
        $pdf->spacer(6);

        $rows = [];
        $totalStudents = 0;
        $allAvgs = [];
        $allPassRates = [];
        foreach ($courses as $c) {
            $assessCount = (int)Database::scalar("SELECT COUNT(*) FROM assessments WHERE course_id = ? AND status = 'published'", [$c['id']], 0);
            $finals = grading_calc_final_for_course((int)$c['id']);
            $avg = $finals['avg'] ?? null;
            $pr = $finals['pass_rate'] ?? null;
            if ($avg !== null) $allAvgs[] = $avg;
            if ($pr !== null) $allPassRates[] = $pr;
            $totalStudents += (int)$c['students'];
            $rows[] = [$c['title'], $c['code'] ?? '—', (int)$c['students'], $avg !== null ? e($avg) . '%' : '—', $pr !== null ? e($pr) . '%' : '—', $assessCount];
        }
        $pdf->table(['Course', 'Code', 'Students', 'Avg Score', 'Pass Rate', 'Assessments'], $rows);

        if ($allAvgs) {
            $pdf->spacer(8);
            $pdf->summaryBox([
                ['Courses', count($courses)], ['Total Students', $totalStudents],
                ['Overall Average', round(array_sum($allAvgs) / count($allAvgs), 1) . '%'],
                ['Avg Pass Rate', round(array_sum($allPassRates) / count($allPassRates), 1) . '%'],
            ]);
        }
        $this->addSignatures($pdf);
    }

    private function addSignatures(Pdf $pdf): void {
        $pdf->spacer(30);
        $pdf->rule();
        $pdf->bold('Teacher: ' . $this->teacherName, 9);
        $pdf->spacer(20);
        $pdf->rule();
        $pdf->bold('Director: ' . $this->directorName, 9);
    }
}
