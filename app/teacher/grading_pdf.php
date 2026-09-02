<?php
/**
 * Grading PDF generator — student, class, exam, teacher reports
 * Uses formal PDF template with logo, watermark, header, footer, per-page stamps
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

        $school = Database::one("SELECT name, id FROM schools WHERE id = ?", [$u['school_id'] ?? 1]);
        $schoolName = $school['name'] ?? 'Edunex School';
        $schoolId = (int)($school['id'] ?? 1);
        $teacher = Database::one("SELECT first_name, last_name FROM users WHERE id = ?", [$uid]);
        $teacherName = $teacher['first_name'] . ' ' . $teacher['last_name'];

        // Get school director/principal
        $director = Database::one("SELECT first_name, last_name FROM users WHERE school_id = ? AND role = 'principal' LIMIT 1", [$schoolId]);
        $directorName = $director ? ($director['first_name'] . ' ' . $director['last_name']) : 'School Director';

        $html = '';
        $title = '';
        $recordCount = 0;

        switch ($type) {
            case 'student':
                $title = 'Student Result Report';
                $html = $this->studentReport($courseId, $uid, $schoolName, $teacherName, $directorName, $recordCount);
                break;
            case 'class':
                $title = 'Class Result Report';
                $html = $this->classReport($courseId, $uid, $schoolName, $teacherName, $directorName, $recordCount);
                break;
            case 'exam':
                $title = 'Exam Results Report';
                $html = $this->examReport($assessmentId, $uid, $schoolName, $teacherName, $directorName, $recordCount);
                break;
            case 'teacher':
                $title = 'Teacher Summary Report';
                $html = $this->teacherReport($uid, $schoolName, $teacherName, $directorName, $recordCount);
                break;
            default:
                exit('Invalid report type.');
        }

        // Set PDF template variables
        $pdf_title = $title;
        $pdf_subtitle = $schoolName . ' — Grading System';
        $pdf_doc_id = 'EDU-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $pdf_stamp = date('F j, Y \a\t g:i A');
        $pdf_filename = 'grading_' . $type . '_' . date('Ymd_His') . '.pdf';
        $pdf_record_count = $recordCount;
        $pdf_user_name = $teacherName;
        $pdf_orientation = 'landscape';

        // Render formal PDF page
        header('Content-Type: text/html; charset=utf-8');
        require_once BASE_PATH . '/includes/pdf_template.php';
        ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pdf_title) ?></title>
</head>
<body>
  <div class="pdf-viewer">
    <!-- Toolbar -->
    <div class="pdf-toolbar">
      <button class="pdf-toolbar-btn pdf-toolbar-btn--back" onclick="history.back()">← Back</button>
      <button class="pdf-toolbar-btn pdf-toolbar-btn--dl" onclick="downloadPDF()">⬇ Download PDF</button>
      <button class="pdf-toolbar-btn pdf-toolbar-btn--print" onclick="window.print()">🖨 Print</button>
      <span style="margin-left:auto;font-size:12px;color:var(--text-secondary)"><?= e($pdf_doc_id) ?></span>
    </div>

    <!-- Paper -->
    <div class="pdf-paper" id="pdf-content">
      <!-- Watermark -->
      <div class="pdf-watermark">
        <img class="wm-logo" src="<?= $pdf_logo_black ?>" alt="EDUNEX">
        <div class="wm-edunex">EDUNEX</div>
        <div class="wm-url">www.henokakriso.com</div>
      </div>

      <!-- Header -->
      <div class="pdf-header">
        <div class="logos-row">
          <div class="flag-wrap">
            <img class="logo-img flag-img" src="<?= $pdf_ethiopian_flag ?>" alt="Ethiopian Flag">
          </div>
          <div class="text-center">
            <h2>Federal Democratic Republic of Ethiopia</h2>
            <div>Ministry of Education</div>
          </div>
          <div class="ministry-wrap">
            <img class="logo-img" src="<?= $pdf_ministry_logo ?>" alt="Ministry Logo">
          </div>
        </div>
        <div class="pdf-sub"><?= e($pdf_subtitle) ?> — <?= e($title) ?></div>
      </div>

      <!-- Meta row -->
      <div class="pdf-meta">
        <span>Document: <strong><?= e($pdf_doc_id) ?></strong></span>
        <span class="meta-dot"></span>
        <span>Generated: <strong><?= e($pdf_stamp) ?></strong></span>
        <span class="meta-dot"></span>
        <span>Records: <strong><?= (int)$pdf_record_count ?></strong></span>
        <span class="meta-dot"></span>
        <span>Generated by: <strong><?= e($pdf_user_name) ?></strong></span>
      </div>

      <!-- Report Content -->
      <?= $html ?>

      <!-- Footer -->
      <div class="pdf-footer">
        <span>EDUNEX LMS · henockakriso.com · ARWE-PL Licensed [<?= date('Y') ?>]</span>
        <span><?= e($pdf_doc_id) ?></span>
      </div>
    </div>
  </div>
</body>
</html>
<?php
        exit;
    }

    private function studentReport(int $courseId, int $uid, string $schoolName, string $teacherName, string $directorName, int &$recordCount): string {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $recordCount = count($students);
        $html = '<table><thead><tr><th>#</th><th>Student</th><th>ID</th><th>Round 1</th><th>Round 2</th><th>Bonus</th><th>Final</th><th>Grade</th><th>Status</th></tr></thead><tbody>';

        $rank = 0;
        $finals = [];
        foreach ($students as $s) {
            $f = grading_calc_final((int)$s['id'], $courseId);
            $finals[] = ['name' => $s['last_name'] . ', ' . $s['first_name'], 'sid' => $s['sid'], 'final' => $f];
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
            $html .= '<div class="pdf-stats" style="display:flex;gap:12px;margin:16px 0">';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . count($students) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Students</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . round(array_sum($adjScores) / count($adjScores), 1) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Average</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:#34c759">' . max($adjScores) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Highest</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:#ff3b30">' . min($adjScores) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Lowest</div></div>';
            $passRate = round(count(array_filter($finals, fn($f) => $f['final']['pass'])) / count($finals) * 100, 1);
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . $passRate . '%</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Pass Rate</div></div>';
            $html .= '</div>';
        }

        // Signature lines
        $html = '<div style="display:flex;justify-content:space-between;margin-top:40px">';
        $html .= '<div style="border-top:1px solid #1d1d1f;width:180px;padding-top:4px;font-size:9px;text-align:center"><b>' . e($teacherName) . '</b><br>Teacher</div>';
        $html .= '<div style="border-top:1px solid #1d1d1f;width:180px;padding-top:4px;font-size:9px;text-align:center"><b>' . e($directorName) . '</b><br>Director</div>';
        $html .= '</div>';

        return $html;
    }

    private function classReport(int $courseId, int $uid, string $schoolName, string $teacherName, string $directorName, int &$recordCount): string {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $recordCount = count($students);

        // Get all assessments grouped by type
        $assessments = Database::all(
            "SELECT a.id, a.title, a.type_slug, a.max_mark, ats.label AS type_label
             FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
             WHERE a.course_id = ? AND a.status = 'published' ORDER BY ats.sort_order", [$courseId]);

        // Build header
        $html = '<table><thead><tr><th>Rank</th><th>Student</th><th>ID</th>';
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
            $html .= '<div style="display:flex;gap:12px;margin:16px 0">';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . count($students) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Students</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . round(array_sum($adjScores) / count($adjScores), 1) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Average</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:#34c759">' . max($adjScores) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Highest</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:#ff3b30">' . min($adjScores) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Lowest</div></div>';
            $passRate = round(count(array_filter($allFinals, fn($e) => $e['final']['pass'])) / count($allFinals) * 100, 1);
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . $passRate . '%</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Pass Rate</div></div>';
            $html .= '</div>';
        }

        // Signature lines
        $html .= '<div style="display:flex;justify-content:space-between;margin-top:40px">';
        $html .= '<div style="border-top:1px solid #1d1d1f;width:180px;padding-top:4px;font-size:9px;text-align:center"><b>' . e($teacherName) . '</b><br>Teacher</div>';
        $html .= '<div style="border-top:1px solid #1d1d1f;width:180px;padding-top:4px;font-size:9px;text-align:center"><b>' . e($directorName) . '</b><br>Director</div>';
        $html .= '</div>';

        return $html;
    }

    private function examReport(int $assessmentId, int $uid, string $schoolName, string $teacherName, string $directorName, int &$recordCount): string {
        $assessment = Database::one(
            "SELECT a.*, ats.label AS type_label, c.title AS course_title
             FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
             JOIN courses c ON c.id = a.course_id
             WHERE a.id = ? AND a.teacher_id = ?", [$assessmentId, $uid]);
        if (!$assessment) { $recordCount = 0; return '<p>Assessment not found.</p>'; }

        $grades = Database::all(
            "SELECT g.*, u.first_name, u.last_name, u.student_id AS sid
             FROM grades g JOIN users u ON u.id = g.student_id
             WHERE g.assessment_id = ?
             ORDER BY g.mark DESC", [$assessmentId]);

        $recordCount = count($grades);

        $html = '<table><thead><tr><th>Rank</th><th>Student</th><th>ID</th><th>Mark</th><th>Out Of</th><th>Percentage</th><th>Grade</th></tr></thead><tbody>';

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
            $html .= '<div style="display:flex;gap:12px;margin:16px 0">';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . count($grades) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Students</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . round(array_sum($pcts) / count($pcts), 1) . '%</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Average</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:#34c759">' . max($pcts) . '%</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Highest</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:#ff3b30">' . min($pcts) . '%</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Lowest</div></div>';
            $passRate = round(count(array_filter($pcts, fn($p) => $p >= 50)) / count($pcts) * 100, 1);
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . $passRate . '%</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Pass Rate</div></div>';
            $html .= '</div>';
        }

        return $html;
    }

    private function teacherReport(int $uid, string $schoolName, string $teacherName, string $directorName, int &$recordCount): string {
        $courses = Database::all(
            "SELECT c.id, c.title, c.code,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
             FROM courses c WHERE c.teacher_id = ? AND c.status = 'published' ORDER BY c.title", [$uid]);

        $recordCount = count($courses);
        $totalStudents = 0;
        $allAvgs = [];
        $allPassRates = [];

        $html = '<table><thead><tr><th>Course</th><th>Code</th><th>Students</th><th>Avg Score</th><th>Pass Rate</th><th>Assessments</th></tr></thead><tbody>';

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
            $html .= '<div style="display:flex;gap:12px;margin:16px 0">';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . count($courses) . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Courses</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . $totalStudents . '</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Total Students</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . round(array_sum($allAvgs) / count($allAvgs), 1) . '%</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Overall Average</div></div>';
            $html .= '<div style="flex:1;text-align:center;border:1px solid rgba(99,102,241,.15);border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:800;color:var(--accent)">' . round(array_sum($allPassRates) / count($allPassRates), 1) . '%</div><div style="font-size:9px;color:var(--text-secondary);text-transform:uppercase">Avg Pass Rate</div></div>';
            $html .= '</div>';
        }

        // Signature lines
        $html .= '<div style="display:flex;justify-content:space-between;margin-top:40px">';
        $html .= '<div style="border-top:1px solid #1d1d1f;width:180px;padding-top:4px;font-size:9px;text-align:center"><b>' . e($teacherName) . '</b><br>Teacher</div>';
        $html .= '<div style="border-top:1px solid #1d1d1f;width:180px;padding-top:4px;font-size:9px;text-align:center"><b>' . e($directorName) . '</b><br>Director</div>';
        $html .= '</div>';

        return $html;
    }
}
