<?php
/**
 * AI job endpoints: SSE progress streaming (with finalize).
 */

class Ctl_ai_job_progress {
    public function run(): void {
        $u = require_login();
        $id = (string)($_GET['job'] ?? '');
        if (!AiJob::validId($id) || !is_dir(AiJob::dir($id))) {
            http_response_code(404);
            exit('Job not found');
        }
        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        if (ob_get_level()) ob_end_flush();
        set_time_limit(600);
        while (true) {
            $st = AiJob::read($id);
            $state = $st['state'] ?? 'missing';
            echo 'data: ' . json_encode([
                'stage' => $st['stage'] ?? 'starting',
                'cur' => (int)($st['cur'] ?? 0),
                'total' => (int)($st['total'] ?? 0),
                'state' => $state,
            ], JSON_UNESCAPED_UNICODE) . "\n\n";
            flush();
            if ($state === 'done' || $state === 'cancelled') {
                $result = AiJob::finalize($id);
                echo 'data: ' . json_encode([
                    'done' => true,
                    'state' => $result['state'] ?? $state,
                    'result' => $result,
                ], JSON_UNESCAPED_UNICODE) . "\n\n";
                flush();
                exit;
            }
            if ($state === 'error') {
                echo 'data: ' . json_encode(['done' => true, 'state' => 'error', 'result' => ['state' => 'error']], JSON_UNESCAPED_UNICODE) . "\n\n";
                flush();
                exit;
            }
            if (connection_aborted()) exit;
            usleep(500000);
        }
    }
}
