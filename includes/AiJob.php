<?php
/**
 * AiJob — background AI generation jobs driven by the native C engine
 * (storage/bin/ai_fast, compiled from ai_fast.c, libcurl + json-c).
 *
 * Job layout (STORAGE_PATH/ai_jobs/<id>/):
 *   text.txt          book text extracted from the PDF
 *   lessons.ndjson    lesson chunks prepared by PHP (title, content, duration)
 *   meta.json         type + context (course title, level, counts, user ids)
 *   progress.log      written by the C engine (P stage=..., D ok/cancelled, E msg)
 *   summary.txt       generated course description (C engine)
 *   questions.ndjson  generated MCQs (C engine, one JSON object per line)
 *   cancel            presence cancels the C engine
 *   finalized         JSON result once the PHP finalizer has run
 *
 * Modes supported by ai_fast:
 *   ai_fast course <textfile> <nquestions> <title> <outdir> [host] [model]
 */

final class AiJob {

    private const BIN = 'ai_fast';

    public static function dir(string $id): string {
        return STORAGE_PATH . '/ai_jobs/' . $id;
    }

    public static function validId(string $id): bool {
        return is_string($id) && preg_match('/^[a-f0-9]{16}$/', $id) === 1;
    }

    /** Create a job dir, write inputs, spawn the C engine detached. Returns job id. */
    public static function start(string $type, array $meta, string $text, array $lessons, int $count, string $title): string {
        $id = bin2hex(random_bytes(8));
        $dir = self::dir($id);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create AI job directory');
        }
        file_put_contents($dir . '/text.txt', $text);
        file_put_contents($dir . '/lessons.ndjson', json_encode($lessons, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        file_put_contents($dir . '/meta.json', json_encode([
            'type' => $type,
            'meta' => $meta,
            'count' => max(1, (int)$count),
            'title' => (string)$title,
            'created' => date('c'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $bin = STORAGE_PATH . '/bin/' . self::BIN;
        if (!is_file($bin) || !is_executable($bin)) {
            throw new RuntimeException('C AI engine missing: ' . $bin);
        }
        $host = (string)setting('ai_api_url');
        if (!str_starts_with($host, 'http')) $host = 'http://127.0.0.1:11434';
        $model = (string)setting('ai_model');
        if ($model === '') $model = 'edunex-tutor';
        $cmd = sprintf(
            'nohup %s course %s %d %s %s %s %s > /dev/null 2>&1 &',
            escapeshellarg($bin),
            escapeshellarg($dir . '/text.txt'),
            max(1, (int)$count),
            escapeshellarg($title),
            escapeshellarg($dir),
            escapeshellarg($host),
            escapeshellarg($model)
        );
        $meta = json_decode((string)file_get_contents($dir . '/meta.json'), true);
        $meta['cmd'] = $cmd;
        file_put_contents($dir . '/meta.json', json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        exec($cmd, $o, $rc);
        if ($rc !== 0) {
            throw new RuntimeException('Failed to launch C AI engine (exec rc=' . $rc . ')');
        }
        return $id;
    }

    /** Read the current job snapshot from disk. */
    public static function read(string $id): array {
        $dir = self::dir($id);
        if (!is_dir($dir)) return ['state' => 'missing'];
        $out = ['stage' => 'starting', 'cur' => 0, 'total' => 0, 'state' => 'running',
                'summary' => '', 'questions' => [], 'lessons' => 0];
        $log = @file_get_contents($dir . '/progress.log');
        if ($log !== false && $log !== '') {
            foreach (explode("\n", trim($log)) as $line) {
                $line = trim($line);
                if (str_starts_with($line, 'P ')) {
                    foreach (preg_split('/\s+/', trim(substr($line, 2))) as $pair) {
                        if (!str_contains($pair, '=')) continue;
                        [$k, $v] = explode('=', $pair, 2);
                        if ($k === 'stage') $out['stage'] = (string)$v;
                        elseif ($k === 'cur') $out['cur'] = (int)$v;
                        elseif ($k === 'total') $out['total'] = (int)$v;
                    }
                } elseif ($line === 'D ok') {
                    $out['state'] = 'done';
                } elseif ($line === 'D cancelled') {
                    $out['state'] = 'cancelled';
                } elseif (str_starts_with($line, 'E ')) {
                    $out['state'] = 'error';
                    $out['error'] = trim(substr($line, 2));
                }
            }
        }
        if (is_file($dir . '/summary.txt')) $out['summary'] = (string)file_get_contents($dir . '/summary.txt');
        $qfile = $dir . '/questions.ndjson';
        if (is_file($qfile)) {
            foreach (file($qfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $q = json_decode((string)$line, true);
                if (is_array($q)) $out['questions'][] = $q;
            }
        }
        if (is_file($dir . '/finalized')) {
            $out['finalized'] = json_decode((string)file_get_contents($dir . '/finalized'), true);
        }
        return $out;
    }

    /** Ask the C engine to stop (checked between and during generations). */
    public static function cancel(string $id): bool {
        $dir = self::dir($id);
        if (!is_dir($dir)) return false;
        return @file_put_contents($dir . '/cancel', '1') !== false;
    }

    /**
     * Persist job results (course, lessons, exam) exactly once.
     * Returns the result array (also cached in the finalized marker).
     */
    public static function finalize(string $id): array {
        $dir = self::dir($id);
        if (!is_dir($dir)) return ['state' => 'missing'];
        $lock = fopen($dir . '/.lock', 'c');
        flock($lock, LOCK_EX);
        $marker = $dir . '/finalized';
        if (is_file($marker)) {
            $r = json_decode((string)file_get_contents($marker), true);
            flock($lock, LOCK_UN);
            return is_array($r) ? $r : ['state' => 'error'];
        }
        $meta = json_decode((string)@file_get_contents($dir . '/meta.json'), true);
        $meta = is_array($meta) ? $meta : [];
        $st = self::read($id);
        $m = $meta['meta'] ?? [];
        $result = [
            'state' => $st['state'] === 'running' ? 'running' : $st['state'],
            'title' => $meta['title'] ?? '',
            'course_id' => 0,
            'lessons' => 0,
            'questions' => 0,
            'summary' => $st['summary'],
            'generated' => count($st['questions']),
        ];
        if ($st['state'] !== 'done' && $st['state'] !== 'cancelled') {
            $result['state'] = $st['state'];
            file_put_contents($marker, json_encode($result, JSON_UNESCAPED_UNICODE));
            flock($lock, LOCK_UN);
            return $result;
        }
        try {
            $uid = (int)($m['user_id'] ?? 0);
            $sid = (int)($m['school_id'] ?? 0);
            $lessons = [];
            $lf = $dir . '/lessons.ndjson';
            if (is_file($lf)) {
                foreach (file($lf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    $l = json_decode((string)$line, true);
                    if (is_array($l)) $lessons[] = $l;
                }
            }
            $summary = $st['summary'];
            $questions = $st['questions'];
            if ($st['state'] === 'done' && ($questions === [] || $summary === '')) {
                // Engine could not produce content — fall back to the PHP heuristics
                $text = (string)@file_get_contents($dir . '/text.txt');
                if ($questions === []) {
                    $questions = (new LocalProvider())->generateQuestions($text, (int)($meta['count'] ?? 5), $result['title']);
                }
                if ($summary === '') {
                    $summary = (new LocalProvider())->summarize($text, 60);
                }
            }
            $courseId = Database::insert('courses', [
                'school_id' => $sid,
                'teacher_id' => $uid,
                'title' => $result['title'],
                'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $result['title']), 0, 4)) . '-' . random_int(100, 999),
                'description' => mb_substr($summary, 0, 1000),
                'status' => 'published',
                'level' => (string)($m['level'] ?? ''),
                'subject_id' => (int)($m['subject_id'] ?? 0) ?: null,
            ]);
            $result['course_id'] = (int)$courseId;
            if ($lessons) {
                $moduleId = Database::insert('course_modules', ['course_id' => $courseId, 'title' => 'Unit 1 — From your book', 'sort_order' => 1]);
                foreach ($lessons as $i => $l) {
                    Database::insert('lessons', [
                        'course_id' => $courseId, 'module_id' => (int)$moduleId,
                        'title' => (string)($l['title'] ?? ('Lesson ' . ($i + 1))),
                        'content' => (string)($l['content'] ?? ''),
                        'duration_min' => max(5, (int)($l['duration_min'] ?? 10)),
                        'sort_order' => $i + 1,
                    ]);
                    $result['lessons']++;
                }
            }
            if ($questions) {
                $examId = Database::insert('exams', [
                    'course_id' => $courseId, 'teacher_id' => $uid,
                    'title' => $result['title'] . ' — Chapter Test',
                    'type' => 'midterm', 'duration_min' => max(10, count($questions) * 2),
                    'passing_score' => 50, 'auto_grade' => 1, 'shuffle_questions' => 1, 'show_result' => 1,
                    'status' => 'published',
                ]);
                foreach ($questions as $q) {
                    $opt = array_values(array_map('strval', $q['options'] ?? []));
                    $ans = (string)($q['answer'] ?? '');
                    if (!in_array($ans, $opt, true)) $ans = $opt[0] ?? '';
                    if ($ans === '' || !($q['question'] ?? '')) continue;
                    Database::insert('exam_questions', [
                        'exam_id' => (int)$examId, 'type' => 'mcq', 'question' => (string)$q['question'],
                        'options' => json_encode($opt), 'correct_answer' => $ans,
                        'points' => 1, 'explanation' => (string)($q['explanation'] ?? ''),
                    ]);
                    Database::insert('ai_question_bank', [
                        'school_id' => $sid, 'topic' => $result['title'], 'type' => 'mcq',
                        'question' => (string)$q['question'],
                        'options' => json_encode($opt), 'answer' => $ans,
                        'explanation' => (string)($q['explanation'] ?? ''),
                    ]);
                    $result['questions']++;
                }
            }
            $result['state'] = $st['state'];
            file_put_contents($marker, json_encode($result, JSON_UNESCAPED_UNICODE));
            flock($lock, LOCK_UN);
            return $result;
        } catch (Throwable $e) {
            error_log('[AiJob] finalize failed: ' . $e->getMessage());
            flock($lock, LOCK_UN);
            $result['state'] = 'error';
            $result['error'] = $e->getMessage();
            return $result;
        }
    }
}
