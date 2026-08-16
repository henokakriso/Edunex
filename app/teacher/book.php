<?php
/**
 * Teacher: PDF book → course + exam generation pipeline.
 * The heavy AI work (summary + questions) runs in the background through the
 * native C engine (storage/bin/ai_fast). Course creation happens the moment
 * the PDF is parsed; the AI job streams progress and inserts lessons + exam
 * when done. A stop button cancels the job at any time.
 */

class Ctl_book {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];
        $sid = (int)$u['school_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $title = trim($_POST['title'] ?? '');
            if ($title === '') { flash('danger', 'Course title is required.'); redirect('teacher/book'); }
            $subjId = (int)($_POST['subject_id'] ?? 0);
            $mine = Database::all(
                "SELECT s.id FROM teacher_subjects ts JOIN subjects s ON s.id = ts.subject_id
                 WHERE ts.teacher_id = ? AND s.status = 'active'", [$uid]);
            $mineIds = array_map('intval', array_column($mine, 'id'));
            if (!$subjId || !in_array($subjId, $mineIds, true)) {
                flash('danger', 'Choose a subject you are authorised to teach (assigned by the director).');
                redirect('teacher/book');
            }
            $file = $_FILES['pdf'] ?? null;
            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                flash('danger', 'Please choose a PDF file to upload.');
                redirect('teacher/book');
            }
            if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
                flash('danger', 'Only PDF files are supported.');
                redirect('teacher/book');
            }
            try {
                $jobId = self::build($u, $title, $file, (int)($_POST['lesson_count'] ?? 10), (int)($_POST['question_count'] ?? 10), (string)($_POST['level'] ?? ''), $subjId);
                redirect('teacher/book/job&job=' . $jobId);
            } catch (Throwable $e) {
                flash('danger', $e->getMessage());
                redirect('teacher/book');
            }
        }

        $subjects = Database::all(
            "SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON s.id = ts.subject_id
             WHERE ts.teacher_id = ? AND s.status = 'active' ORDER BY s.name", [$uid]);
        Router::render('app/teacher/book', ['title' => 'Book → Course Generator', 'subjects' => $subjects]);
    }

    /** Fast path: extract + chunk, then hand the AI work to the C engine. */
    public static function build(array $u, string $title, array $file, int $lessonCount, int $questionCount, string $level, int $subjectId = 0): string {
        $tmp = tempnam(sys_get_temp_dir(), 'edunex_pdf_');
        move_uploaded_file($file['tmp_name'], $tmp);
        $text = self::extractText($tmp);
        @unlink($tmp);

        if (trim($text) === '') {
            flash('danger', 'Could not extract text from this PDF (scanned images are not supported).');
            redirect('teacher/book');
        }

        // Prepare lesson chunks (pure PHP, instant)
        $chunks = self::chunkText($text, max(3, min(25, $lessonCount)));
        $lessons = [];
        foreach ($chunks as $i => $chunk) {
            $lessons[] = [
                'title' => 'Lesson ' . ($i + 1) . ' — ' . self::headingFor($chunk),
                'content' => $chunk,
                'duration_min' => max(5, (int)round(mb_strlen($chunk) / 1000)),
            ];
        }

        return AiJob::start('book', [
            'user_id' => (int)$u['id'],
            'school_id' => (int)$u['school_id'],
            'level' => $level ?: 'All levels',
            'subject_id' => $subjectId,
        ], $text, $lessons, max(3, min(20, $questionCount)), $title);
    }

    private static function extractText(string $pdfPath): string {
        $out = '';
        foreach (['pdftotext -layout', 'mutool draw -F txt', 'gs -sDEVICE=txtwrite'] as $cmd) {
            $parts = explode(' ', $cmd);
            if (self::hasBin($parts[0])) {
                $out = shell_exec(escapeshellcmd($parts[0]) . ' ' . implode(' ', array_map('escapeshellarg', array_slice($parts, 1))) . ' ' . escapeshellarg($pdfPath) . ' - 2>/dev/null');
                if (is_string($out) && trim($out) !== '') break;
            }
        }
        return (string)$out;
    }

    private static function hasBin(string $bin): bool {
        return !(shell_exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null') === null || shell_exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null') === '');
    }

    private static function chunkText(string $text, int $count): array {
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/u', $text))));
        $chunks = [];
        $buffer = '';
        foreach ($paragraphs as $p) {
            $buffer .= $p . "\n\n";
            if (mb_strlen($buffer) >= 1800) { $chunks[] = trim($buffer); $buffer = ''; }
        }
        if (trim($buffer) !== '') $chunks[] = trim($buffer);
        if (count($chunks) >= 2 && count($chunks) > $count * 1.5) {
            $merged = [];
            $per = (int)ceil(count($chunks) / $count);
            foreach (array_chunk($chunks, $per) as $group) $merged[] = implode("\n\n", $group);
            $chunks = $merged;
        }
        return array_slice($chunks, 0, $count);
    }

    private static function headingFor(string $chunk): string {
        if (preg_match('/^(chapter|unit)\s+\d+[.:]?\s*([^\n]{2,60})/im', $chunk, $m)) {
            return 'Chapter ' . trim($m[2]);
        }
        if (preg_match('/^[A-Z][A-Za-z ,\'&-]{3,60}$/m', $chunk, $m)) {
            return trim($m[0]);
        }
        $words = preg_split('/\s+/u', trim($chunk));
        return mb_substr(implode(' ', array_slice($words, 0, 8)), 0, 60) . '…';
    }
}
