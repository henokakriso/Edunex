<?php
/**
 * Grading PDF — viewer page using pdf_template.php + download
 * ?type=student&course=15 → HTML viewer with glassmorphism template
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

        $pdf_filename = $filename;
        $pdf_doc_id = 'EDU-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $pdf_stamp = date('F j, Y \a\t g:i A');
        $pdf_title = $title;
        $pdf_subtitle = $this->schoolName;
        $pdf_record_count = 0;
        $pdf_orientation = 'landscape';
        $backUrl = url('teacher/grading/reports');
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <link rel="stylesheet" href="<?= url('public/css/app.css') ?>?v=<?= time() ?>">
</head>
<body style="background:var(--bg);padding:20px">
  <div class="pdf-viewer">
    <div class="pdf-toolbar">
      <a href="<?= e($backUrl) ?>" class="pdf-toolbar-btn pdf-toolbar-btn--back">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back
      </a>
      <div style="flex:1"></div>
      <button class="pdf-toolbar-btn pdf-toolbar-btn--dl" onclick="downloadPDF()">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        Download PDF
      </button>
      <button class="pdf-toolbar-btn pdf-toolbar-btn--print" onclick="window.print()">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print
      </button>
    </div>

    <div id="pdf-content" class="pdf-paper">
      <!-- Watermark -->
      <div class="pdf-watermark">
        <img src="<?= url('public/images/logo-black.jpeg') ?>" class="wm-logo" alt="">
        <div class="wm-edunex">EDUNEX</div>
        <div class="wm-url">www.henokakriso.com</div>
      </div>

      <!-- Header -->
      <div class="pdf-header">
        <div class="logos-row">
          <div class="flag-wrap">
            <img src="<?= url('public/images/ethiopian-flag.jpeg') ?>" class="flag-img" alt="Ethiopia">
          </div>
          <div class="text-center">
            <h2>Federal Democratic Republic of Ethiopia</h2>
            <div class="pdf-sub">Ministry of Education</div>
          </div>
          <div class="ministry-wrap">
            <img src="<?= url('public/images/ministry-logo.png') ?>" class="logo-img" alt="Ministry">
          </div>
        </div>
        <div class="pdf-sub"><?= e($pdf_subtitle) ?> — <?= e($title) ?></div>
      </div>

      <!-- Meta -->
      <div class="pdf-meta">
        <span><span class="meta-dot"></span> <b>Doc ID:</b> <?= e($pdf_doc_id) ?></span>
        <span><span class="meta-dot"></span> <b>Generated:</b> <?= e($pdf_stamp) ?></span>
      </div>

      <!-- Content -->
      <div class="pdf-section">
        <?= $htmlBody ?>
      </div>

      <!-- Footer -->
      <div class="pdf-footer">
        <span>EDUNEX LMS · henockakriso.com · ARWE-PL Licensed <?= date('Y') ?></span>
      </div>
    </div>
  </div>

  <?php include BASE_PATH . '/includes/pdf_template.php'; ?>
  <script>
    document.querySelector('.pdf-toolbar-btn--dl').addEventListener('click', function(e) { e.preventDefault(); downloadPDF(); });
  </script>
</body>
</html>
<?php
        exit;
    }

    // ──────────── HTML REPORTS ────────────

    private function signaturesHTML(): string {
        return '<div style="margin-top:32px;padding-top:14px;border-top:1px solid rgba(0,0,0,.08);display:grid;grid-template-columns:1fr 1fr;gap:40px">'
            . '<div><div style="border-top:1px solid #9ca3af;padding-top:6px;font-size:11px;color:var(--text-secondary)">Teacher: <b>' . e($this->teacherName) . '</b></div></div>'
            . '<div><div style="border-top:1px solid #9ca3af;padding-top:6px;font-size:11px;color:var(--text-secondary)">Director: <b>' . e($this->directorName) . '</b></div></div>'
            . '</div>';
    }

    private function summaryBoxHTML(array $items): string {
        $h = '<div style="display:grid;grid-template-columns:repeat(' . count($items) . ',1fr);gap:8px;margin:16px 0;font-size:12px">';
        foreach ($items as [$label, $value]) {
            $h .= '<div style="text-align:center;padding:10px;background:rgba(99,102,241,.04);border-radius:8px"><b style="font-size:16px">' . e($value) . '</b><br><span style="color:var(--text-secondary);font-size:10px">' . e($label) . '</span></div>';
        }
        return $h . '</div>';
    }

    private function studentReportHTML(int $courseId, int $uid): string {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $h = '<h3 style="color:var(--accent);margin-bottom:12px">Student Results — ' . e($course['title'] ?? '') . ' (' . e($course['code'] ?? '') . ')</h3>';

        $finals = [];
        foreach ($students as $s) {
            $f = grading_calc_final((int)$s['id'], $courseId);
            $finals[] = ['name' => $s['last_name'] . ', ' . $s['first_name'], 'sid' => $s['sid'], 'final' => $f];
        }
        usort($finals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        $h .= '<table><thead><tr><th>#</th><th>Student</th><th>ID</th><th>Midterm</th><th>S1 Total</th><th>Bonus</th><th>Final</th><th>Grade</th><th>Status</th></tr></thead><tbody>';
        $rank = 0;
        foreach ($finals as $f) {
            $rank++;
            $adj = $f['final']['adjusted'];
            $pass = $f['final']['pass'];
            $statusColor = $pass ? '#059669' : '#dc2626';
            $h .= '<tr><td>' . $rank . '</td><td>' . e($f['name']) . '</td><td>' . e($f['sid'] ?? '—') . '</td>'
                . '<td>' . e($f['final']['midterm1'] ?? '—') . '</td>'
                . '<td>' . e($f['final']['total1'] ?? '—') . '</td>'
                . '<td>+' . (int)($f['final']['bonus'] ?? 0) . '</td>'
                . '<td><b>' . ($adj !== null ? e($adj) : '—') . '</b></td>'
                . '<td><b>' . e($f['final']['letter'] ?? '—') . '</b></td>'
                . '<td style="color:' . $statusColor . ';font-weight:600">' . ($pass ? 'PASS' : 'FAIL') . '</td></tr>';
        }
        $h .= '</tbody></table>';

        $adjScores = array_filter(array_column($finals, 'final'), fn($f) => $f['adjusted'] !== null);
        $adjScores = array_column($adjScores, 'adjusted');
        if ($adjScores) {
            $passCount = count(array_filter($finals, fn($f) => $f['final']['pass']));
            $h .= $this->summaryBoxHTML([
                ['Students', count($students)],
                ['Average', round(array_sum($adjScores) / count($adjScores), 1) . '%'],
                ['Highest', max($adjScores) . '%'],
                ['Lowest', min($adjScores) . '%'],
                ['Pass Rate', round($passCount / count($finals) * 100, 1) . '%'],
            ]);
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

        $h = '<h3 style="color:var(--accent);margin-bottom:12px">Class Results — ' . e($course['title'] ?? '') . '</h3>';

        $allFinals = [];
        foreach ($students as $s) { $f = grading_calc_final((int)$s['id'], $courseId); $allFinals[] = ['student' => $s, 'final' => $f]; }
        usort($allFinals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        $h .= '<div style="overflow-x:auto"><table><thead><tr><th>Rank</th><th>Student</th><th>ID</th>';
        foreach ($assessments as $a) $h .= '<th>' . e(mb_substr($a['type_slug'], 0, 6)) . '/' . (int)$a['max_mark'] . '</th>';
        $h .= '<th>Mid</th><th>S1</th><th>Bonus</th><th>Final</th><th>Grade</th></tr></thead><tbody>';

        $rank = 0;
        foreach ($allFinals as $entry) {
            $rank++; $s = $entry['student']; $f = $entry['final'];
            $h .= '<tr><td>' . $rank . '</td><td>' . e($s['last_name'] . ', ' . $s['first_name']) . '</td><td>' . e($s['sid'] ?? '—') . '</td>';
            foreach ($assessments as $a) {
                $grade = Database::one("SELECT mark FROM grades WHERE assessment_id = ? AND student_id = ?", [$a['id'], $s['id']]);
                $h .= '<td>' . ($grade ? e($grade['mark']) : '—') . '</td>';
            }
            $h .= '<td>' . e($f['midterm1'] ?? '—') . '</td><td>' . e($f['total1'] ?? '—') . '</td>'
                . '<td>+' . (int)($f['bonus'] ?? 0) . '</td>'
                . '<td><b>' . ($f['adjusted'] !== null ? e($f['adjusted']) : '—') . '</b></td>'
                . '<td><b>' . e($f['letter'] ?? '—') . '</b></td></tr>';
        }
        $h .= '</tbody></table></div>';

        $adjScores = array_filter(array_column(array_column($allFinals, 'final'), 'adjusted'), fn($v) => $v !== null);
        if ($adjScores) {
            $passCount = count(array_filter($allFinals, fn($e) => $e['final']['pass']));
            $h .= $this->summaryBoxHTML([
                ['Students', count($students)],
                ['Average', round(array_sum($adjScores) / count($adjScores), 1) . '%'],
                ['Highest', max($adjScores) . '%'],
                ['Lowest', min($adjScores) . '%'],
                ['Pass Rate', round($passCount / count($allFinals) * 100, 1) . '%'],
            ]);
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

        $h = '<h3 style="color:var(--accent);margin-bottom:12px">Exam Results — ' . e($assessment['title'] ?? '') . ' (' . e($assessment['type_label'] ?? $assessment['type_slug']) . ')</h3>';

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
            $h .= $this->summaryBoxHTML([
                ['Students', count($grades)],
                ['Average', round(array_sum($pcts) / count($pcts), 1) . '%'],
                ['Highest', max($pcts) . '%'],
                ['Lowest', min($pcts) . '%'],
                ['Pass Rate', round($passCount / count($pcts) * 100, 1) . '%'],
            ]);
        }

        $h .= $this->signaturesHTML();
        return $h;
    }

    private function teacherReportHTML(int $uid): string {
        $courses = Database::all(
            "SELECT c.id, c.title, c.code,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
             FROM courses c WHERE c.teacher_id = ? AND c.status = 'published' ORDER BY c.title", [$uid]);

        $h = '<h3 style="color:var(--accent);margin-bottom:12px">Teaching Summary — ' . e($this->teacherName) . '</h3>';

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
            $h .= $this->summaryBoxHTML([
                ['Courses', count($courses)],
                ['Total Students', $totalStudents],
                ['Overall Average', round(array_sum($allAvgs) / count($allAvgs), 1) . '%'],
                ['Avg Pass Rate', round(array_sum($allPassRates) / count($allPassRates), 1) . '%'],
            ]);
        }

        $h .= $this->signaturesHTML();
        return $h;
    }

    // ──────────── PDF GENERATORS ────────────

    private function studentReport(Pdf $pdf, int $courseId, int $uid): void {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $pdf->setTitle('STUDENT RESULT REPORT');
        $pdf->setSubtitle($this->schoolName . ' — ' . ($course['title'] ?? '') . ' (' . ($course['code'] ?? '') . ')');
        $pdf->infoBlock([['Course', $course['title'] ?? '—'], ['Teacher', $this->teacherName], ['Date', date('F j, Y')], ['Students', count($students)]]);
        $pdf->spacer(6);

        $finals = [];
        foreach ($students as $s) { $f = grading_calc_final((int)$s['id'], $courseId); $finals[] = ['name' => $s['last_name'] . ', ' . $s['first_name'], 'sid' => $s['sid'], 'final' => $f]; }
        usort($finals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        $rows = []; $rank = 0;
        foreach ($finals as $f) { $rank++; $adj = $f['final']['adjusted']; $rows[] = [$rank, $f['name'], $f['sid'] ?? '—', $f['final']['midterm1'] ?? '—', $f['final']['total1'] ?? '—', '+' . ($f['final']['bonus'] ?? 0), $adj !== null ? (string)$adj : '—', $f['final']['letter'] ?? '—', $f['final']['pass'] ? 'PASS' : 'FAIL']; }
        $pdf->table(['#', 'Student', 'ID', 'Midterm', 'S1 Total', 'Bonus', 'Final', 'Grade', 'Status'], $rows);

        $adjScores = array_filter(array_column($finals, 'final'), fn($f) => $f['adjusted'] !== null);
        $adjScores = array_column($adjScores, 'adjusted');
        if ($adjScores) { $pdf->spacer(8); $pdf->summaryBox([['Students', count($students)], ['Average', round(array_sum($adjScores) / count($adjScores), 1)], ['Highest', max($adjScores)], ['Lowest', min($adjScores)], ['Pass Rate', round(count(array_filter($finals, fn($f) => $f['final']['pass'])) / count($finals) * 100, 1) . '%']]); }
        $this->addSignatures($pdf);
    }

    private function classReport(Pdf $pdf, int $courseId, int $uid): void {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all("SELECT u.id, u.first_name, u.last_name, u.student_id AS sid FROM course_enrollments ce JOIN users u ON u.id = ce.user_id WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);
        $assessments = Database::all("SELECT a.id, a.title, a.type_slug, a.max_mark, ats.label AS type_label FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug WHERE a.course_id = ? AND a.status = 'published' ORDER BY ats.sort_order", [$courseId]);

        $pdf->setTitle('CLASS RESULT REPORT');
        $pdf->setSubtitle($this->schoolName . ' — ' . ($course['title'] ?? '') . ' · Teacher: ' . $this->teacherName);
        $pdf->infoBlock([['Course', $course['title'] ?? '—'], ['Teacher', $this->teacherName], ['Date', date('F j, Y')], ['Students', count($students)]]);
        $pdf->spacer(6);

        $headers = ['Rank', 'Student', 'ID'];
        foreach ($assessments as $a) $headers[] = mb_substr($a['type_slug'], 0, 6) . '/' . (int)$a['max_mark'];
        $headers = array_merge($headers, ['Mid', 'S1', 'Bonus', 'Final', 'Grade']);

        $allFinals = [];
        foreach ($students as $s) { $f = grading_calc_final((int)$s['id'], $courseId); $allFinals[] = ['student' => $s, 'final' => $f]; }
        usort($allFinals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        $rows = []; $rank = 0;
        foreach ($allFinals as $entry) { $rank++; $s = $entry['student']; $f = $entry['final']; $row = [$rank, $s['last_name'] . ', ' . $s['first_name'], $s['sid'] ?? '—']; foreach ($assessments as $a) { $grade = Database::one("SELECT mark FROM grades WHERE assessment_id = ? AND student_id = ?", [$a['id'], $s['id']]); $row[] = $grade ? (string)$grade['mark'] : '—'; } $row = array_merge($row, [$f['midterm1'] ?? '—', $f['total1'] ?? '—', '+' . ($f['bonus'] ?? 0), $f['adjusted'] !== null ? (string)$f['adjusted'] : '—', $f['letter'] ?? '—']); $rows[] = $row; }
        $pdf->table($headers, $rows);

        $adjScores = array_filter(array_column(array_column($allFinals, 'final'), 'adjusted'), fn($v) => $v !== null);
        if ($adjScores) { $pdf->spacer(8); $pdf->summaryBox([['Students', count($students)], ['Average', round(array_sum($adjScores) / count($adjScores), 1)], ['Highest', max($adjScores)], ['Lowest', min($adjScores)], ['Pass Rate', round(count(array_filter($allFinals, fn($e) => $e['final']['pass'])) / count($allFinals) * 100, 1) . '%']]); }
        $this->addSignatures($pdf);
    }

    private function examReport(Pdf $pdf, int $assessmentId, int $uid): void {
        $assessment = Database::one("SELECT a.*, ats.label AS type_label, c.title AS course_title FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug JOIN courses c ON c.id = a.course_id WHERE a.id = ? AND a.teacher_id = ?", [$assessmentId, $uid]);
        if (!$assessment) { $pdf->setTitle('ASSESSMENT NOT FOUND'); return; }
        $grades = Database::all("SELECT g.*, u.first_name, u.last_name, u.student_id AS sid FROM grades g JOIN users u ON u.id = g.student_id WHERE g.assessment_id = ? ORDER BY g.mark DESC", [$assessmentId]);

        $pdf->setTitle('EXAM RESULTS');
        $pdf->setSubtitle(($assessment['title'] ?? '') . ' — ' . ($assessment['course_title'] ?? ''));
        $pdf->infoBlock([['Assessment', $assessment['title'] ?? '—'], ['Type', $assessment['type_label'] ?? $assessment['type_slug']], ['Course', $assessment['course_title'] ?? '—'], ['Max Mark', (int)$assessment['max_mark']], ['Date', date('F j, Y')]]);
        $pdf->spacer(6);

        $rows = []; $rank = 0;
        foreach ($grades as $g) { $rank++; $pct = $g['percentage'] !== null ? e($g['percentage']) . '%' : '—'; $rows[] = [$rank, $g['last_name'] . ', ' . $g['first_name'], $g['sid'] ?? '—', $g['mark'] ?? '—', (int)$assessment['max_mark'], $pct, $g['letter_grade'] ?? '—']; }
        $pdf->table(['Rank', 'Student', 'ID', 'Mark', 'Out Of', 'Percentage', 'Grade'], $rows);

        $pcts = array_filter(array_column($grades, 'percentage'), fn($p) => $p !== null);
        if ($pcts) { $pdf->spacer(8); $pdf->summaryBox([['Students', count($grades)], ['Average', round(array_sum($pcts) / count($pcts), 1) . '%'], ['Highest', max($pcts) . '%'], ['Lowest', min($pcts) . '%'], ['Pass Rate', round(count(array_filter($pcts, fn($p) => $p >= 50)) / count($pcts) * 100, 1) . '%']]); }
        $this->addSignatures($pdf);
    }

    private function teacherReport(Pdf $pdf, int $uid): void {
        $courses = Database::all("SELECT c.id, c.title, c.code, (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students FROM courses c WHERE c.teacher_id = ? AND c.status = 'published' ORDER BY c.title", [$uid]);

        $pdf->setTitle('TEACHER SUMMARY');
        $pdf->setSubtitle($this->schoolName . ' — ' . $this->teacherName);
        $pdf->infoBlock([['Teacher', $this->teacherName], ['School', $this->schoolName], ['Date', date('F j, Y')], ['Courses', count($courses)]]);
        $pdf->spacer(6);

        $rows = []; $totalStudents = 0; $allAvgs = []; $allPassRates = [];
        foreach ($courses as $c) { $assessCount = (int)Database::scalar("SELECT COUNT(*) FROM assessments WHERE course_id = ? AND status = 'published'", [$c['id']], 0); $finals = grading_calc_final_for_course((int)$c['id']); $avg = $finals['avg'] ?? null; $pr = $finals['pass_rate'] ?? null; if ($avg !== null) $allAvgs[] = $avg; if ($pr !== null) $allPassRates[] = $pr; $totalStudents += (int)$c['students']; $rows[] = [$c['title'], $c['code'] ?? '—', (int)$c['students'], $avg !== null ? e($avg) . '%' : '—', $pr !== null ? e($pr) . '%' : '—', $assessCount]; }
        $pdf->table(['Course', 'Code', 'Students', 'Avg Score', 'Pass Rate', 'Assessments'], $rows);

        if ($allAvgs) { $pdf->spacer(8); $pdf->summaryBox([['Courses', count($courses)], ['Total Students', $totalStudents], ['Overall Average', round(array_sum($allAvgs) / count($allAvgs), 1) . '%'], ['Avg Pass Rate', round(array_sum($allPassRates) / count($allPassRates), 1) . '%']]); }
        $this->addSignatures($pdf);
    }

    private function addSignatures(Pdf $pdf): void {
        $pdf->spacer(30); $pdf->rule(); $pdf->bold('Teacher: ' . $this->teacherName, 9);
        $pdf->spacer(20); $pdf->rule(); $pdf->bold('Director: ' . $this->directorName, 9);
    }
}
