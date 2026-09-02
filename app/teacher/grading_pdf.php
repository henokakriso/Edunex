<?php
/**
 * Grading PDF generator — server-side PDF using Pdf.php
 * Direct download, no new tab, no CDN dependency
 */
require_once __DIR__ . '/grading.php';
require_once BASE_PATH . '/includes/Pdf.php';

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
        $director = Database::one("SELECT first_name, last_name FROM users WHERE school_id = ? AND role = 'principal' LIMIT 1", [(int)($u['school_id'] ?? 1)]);
        $directorName = $director ? ($director['first_name'] . ' ' . $director['last_name']) : 'School Director';

        $pdf = new Pdf('landscape', 'A4', true);

        switch ($type) {
            case 'student':
                $this->studentReport($pdf, $courseId, $uid, $schoolName, $teacherName, $directorName);
                $filename = 'student_result_' . date('Ymd_His') . '.pdf';
                break;
            case 'class':
                $this->classReport($pdf, $courseId, $uid, $schoolName, $teacherName, $directorName);
                $filename = 'class_result_' . date('Ymd_His') . '.pdf';
                break;
            case 'exam':
                $this->examReport($pdf, $assessmentId, $uid, $schoolName, $teacherName, $directorName);
                $filename = 'exam_result_' . date('Ymd_His') . '.pdf';
                break;
            case 'teacher':
                $this->teacherReport($pdf, $uid, $schoolName, $teacherName, $directorName);
                $filename = 'teacher_summary_' . date('Ymd_His') . '.pdf';
                break;
            default:
                exit('Invalid report type.');
        }

        $pdf->output($filename, false);
    }

    private function studentReport(Pdf $pdf, int $courseId, int $uid, string $schoolName, string $teacherName, string $directorName): void {
        $course = Database::one("SELECT id, title, code, level FROM courses WHERE id = ?", [$courseId]);
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $pdf->setTitle('STUDENT RESULT REPORT');
        $pdf->setSubtitle($schoolName . ' — ' . ($course['title'] ?? '') . ' (' . ($course['code'] ?? '') . ')');
        $pdf->infoBlock([
            ['Course', $course['title'] ?? '—'],
            ['Teacher', $teacherName],
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
            $rows[] = [
                $rank,
                $f['name'],
                $f['sid'] ?? '—',
                $f['final']['semester1'] ?? '—',
                $f['final']['semester2'] ?? '—',
                '+' . ($f['final']['bonus'] ?? 0),
                $adj !== null ? (string)$adj : '—',
                $f['final']['letter'] ?? '—',
                $f['final']['pass'] ? 'PASS' : 'FAIL',
            ];
        }

        $pdf->table(['#', 'Student', 'ID', 'Round 1', 'Round 2', 'Bonus', 'Final', 'Grade', 'Status'], $rows);

        // Stats
        $adjScores = array_filter(array_column($finals, 'final'), fn($f) => $f['adjusted'] !== null);
        $adjScores = array_column($adjScores, 'adjusted');
        if ($adjScores) {
            $pdf->spacer(8);
            $pdf->summaryBox([
                ['Students', count($students)],
                ['Average', round(array_sum($adjScores) / count($adjScores), 1)],
                ['Highest', max($adjScores)],
                ['Lowest', min($adjScores)],
                ['Pass Rate', round(count(array_filter($finals, fn($f) => $f['final']['pass'])) / count($finals) * 100, 1) . '%'],
            ]);
        }

        $this->addSignatures($pdf, $teacherName, $directorName);
    }

    private function classReport(Pdf $pdf, int $courseId, int $uid, string $schoolName, string $teacherName, string $directorName): void {
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
        $pdf->setSubtitle($schoolName . ' — ' . ($course['title'] ?? '') . ' · Teacher: ' . $teacherName);
        $pdf->infoBlock([
            ['Course', $course['title'] ?? '—'],
            ['Teacher', $teacherName],
            ['Date', date('F j, Y')],
            ['Students', count($students)],
        ]);
        $pdf->spacer(6);

        // Build headers: Rank, Student, ID, then each assessment, then Round 1, Round 2, Bonus, Final, Grade
        $headers = ['Rank', 'Student', 'ID'];
        foreach ($assessments as $a) {
            $headers[] = mb_substr($a['type_slug'], 0, 6) . '/' . (int)$a['max_mark'];
        }
        $headers = array_merge($headers, ['R1', 'R2', 'Bonus', 'Final', 'Grade']);

        $allFinals = [];
        foreach ($students as $s) {
            $f = grading_calc_final((int)$s['id'], $courseId);
            $allFinals[] = ['student' => $s, 'final' => $f];
        }
        usort($allFinals, fn($a, $b) => ($b['final']['adjusted'] ?? 0) <=> ($a['final']['adjusted'] ?? 0));

        $rows = [];
        $rank = 0;
        foreach ($allFinals as $entry) {
            $rank++;
            $s = $entry['student'];
            $f = $entry['final'];
            $row = [$rank, $s['last_name'] . ', ' . $s['first_name'], $s['sid'] ?? '—'];
            foreach ($assessments as $a) {
                $grade = Database::one("SELECT mark FROM grades WHERE assessment_id = ? AND student_id = ?", [$a['id'], $s['id']]);
                $row[] = $grade ? (string)$grade['mark'] : '—';
            }
            $row[] = $f['semester1'] ?? '—';
            $row[] = $f['semester2'] ?? '—';
            $row[] = '+' . ($f['bonus'] ?? 0);
            $row[] = $f['adjusted'] !== null ? (string)$f['adjusted'] : '—';
            $row[] = $f['letter'] ?? '—';
            $rows[] = $row;
        }

        $pdf->table($headers, $rows);

        $adjScores = [];
        foreach ($allFinals as $e) { if ($e['final']['adjusted'] !== null) $adjScores[] = $e['final']['adjusted']; }
        if ($adjScores) {
            $pdf->spacer(8);
            $pdf->summaryBox([
                ['Students', count($students)],
                ['Average', round(array_sum($adjScores) / count($adjScores), 1)],
                ['Highest', max($adjScores)],
                ['Lowest', min($adjScores)],
                ['Pass Rate', round(count(array_filter($allFinals, fn($e) => $e['final']['pass'])) / count($allFinals) * 100, 1) . '%'],
            ]);
        }

        $this->addSignatures($pdf, $teacherName, $directorName);
    }

    private function examReport(Pdf $pdf, int $assessmentId, int $uid, string $schoolName, string $teacherName, string $directorName): void {
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
            ['Assessment', $assessment['title'] ?? '—'],
            ['Type', $assessment['type_label'] ?? $assessment['type_slug']],
            ['Course', $assessment['course_title'] ?? '—'],
            ['Max Mark', (int)$assessment['max_mark']],
            ['Date', date('F j, Y')],
        ]);
        $pdf->spacer(6);

        $rows = [];
        $rank = 0;
        foreach ($grades as $g) {
            $rank++;
            $pct = $g['percentage'] !== null ? e($g['percentage']) . '%' : '—';
            $rows[] = [
                $rank,
                $g['last_name'] . ', ' . $g['first_name'],
                $g['sid'] ?? '—',
                $g['mark'] ?? '—',
                (int)$assessment['max_mark'],
                $pct,
                $g['letter_grade'] ?? '—',
            ];
        }

        $pdf->table(['Rank', 'Student', 'ID', 'Mark', 'Out Of', 'Percentage', 'Grade'], $rows);

        $pcts = array_filter(array_column($grades, 'percentage'), fn($p) => $p !== null);
        if ($pcts) {
            $pdf->spacer(8);
            $pdf->summaryBox([
                ['Students', count($grades)],
                ['Average', round(array_sum($pcts) / count($pcts), 1) . '%'],
                ['Highest', max($pcts) . '%'],
                ['Lowest', min($pcts) . '%'],
                ['Pass Rate', round(count(array_filter($pcts, fn($p) => $p >= 50)) / count($pcts) * 100, 1) . '%'],
            ]);
        }

        $this->addSignatures($pdf, $teacherName, $directorName);
    }

    private function teacherReport(Pdf $pdf, int $uid, string $schoolName, string $teacherName, string $directorName): void {
        $courses = Database::all(
            "SELECT c.id, c.title, c.code,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
             FROM courses c WHERE c.teacher_id = ? AND c.status = 'published' ORDER BY c.title", [$uid]);

        $pdf->setTitle('TEACHER SUMMARY');
        $pdf->setSubtitle($schoolName . ' — ' . $teacherName);
        $pdf->infoBlock([
            ['Teacher', $teacherName],
            ['School', $schoolName],
            ['Date', date('F j, Y')],
            ['Courses', count($courses)],
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
            $passRate = $finals['pass_rate'] ?? null;
            if ($avg !== null) $allAvgs[] = $avg;
            if ($passRate !== null) $allPassRates[] = $passRate;
            $totalStudents += (int)$c['students'];

            $rows[] = [
                $c['title'],
                $c['code'] ?? '—',
                (int)$c['students'],
                $avg !== null ? e($avg) . '%' : '—',
                $passRate !== null ? e($passRate) . '%' : '—',
                $assessCount,
            ];
        }

        $pdf->table(['Course', 'Code', 'Students', 'Avg Score', 'Pass Rate', 'Assessments'], $rows);

        if ($allAvgs) {
            $pdf->spacer(8);
            $pdf->summaryBox([
                ['Courses', count($courses)],
                ['Total Students', $totalStudents],
                ['Overall Average', round(array_sum($allAvgs) / count($allAvgs), 1) . '%'],
                ['Avg Pass Rate', round(array_sum($allPassRates) / count($allPassRates), 1) . '%'],
            ]);
        }

        $this->addSignatures($pdf, $teacherName, $directorName);
    }

    private function addSignatures(Pdf $pdf, string $teacherName, string $directorName): void {
        $pdf->spacer(30);
        $pdf->rule();
        $pdf->bold('Teacher: ' . $teacherName, 9);
        $pdf->spacer(20);
        $pdf->rule();
        $pdf->bold('Director: ' . $directorName, 9);
    }
}
