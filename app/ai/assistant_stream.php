<?php
require_once __DIR__ . '/ai.php';
/** SSE streaming endpoint for the AI assistant quick Q&A. */

class Ctl_ai_assistant_stream {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST only'); }
        csrf_verify();
        $q = trim($_POST['question'] ?? '');
        if ($q === '') exit;

        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        if (ob_get_level()) ob_end_flush();
        set_time_limit(180);
        ignore_user_abort(true);

        if (!OllamaProvider::isUp()) {
            $reply = Model::chat('You are Edunex AI, a helpful study assistant. Answer questions from Ethiopian students concisely and accurately.', $q, ['user' => $u]);
            echo "data: " . json_encode(['delta' => $reply], JSON_UNESCAPED_UNICODE) . "\n\n";
            echo "data: " . json_encode(['done' => true]) . "\n\n";
            flush();
            exit;
        }

        $full = '';
        $json = json_encode([
            'model' => setting('ai_model') ?: 'edunex-tutor',
            'messages' => [
                ['role' => 'system', 'content' => 'You are Edunex AI, a helpful study assistant for Ethiopian students. Answer concisely: at most 120 words unless asked for detail.'],
                ['role' => 'user', 'content' => $q],
            ],
            'stream' => true,
            'temperature' => 0.5,
            'num_predict' => 180,
            'options' => ['temperature' => 0.5, 'num_predict' => 180, 'num_ctx' => 1024, 'num_threads' => 6, 'num_batch' => 512],
            'keep_alive' => '30m',
        ]);
        $host = rtrim(setting('ai_api_url') ?: 'http://127.0.0.1:11434', '/');
        $ch = curl_init($host . '/api/chat');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 170,
            CURLOPT_NOSIGNAL => true,
            CURLOPT_TCP_KEEPALIVE => true,
            CURLOPT_WRITEFUNCTION => function ($ch2, $chunk) use (&$full): int {
                if (connection_aborted()) return 0;
                foreach (explode("\n", $chunk) as $line) {
                    $line = trim($line);
                    if ($line === '') continue;
                    $obj = json_decode($line, true);
                    if (!is_array($obj)) continue;
                    $delta = (string)($obj['message']['content'] ?? '');
                    if ($delta !== '') {
                        $full .= $delta;
                        echo "data: " . json_encode(['delta' => $delta], JSON_UNESCAPED_UNICODE) . "\n\n";
                        flush();
                    }
                    if (!empty($obj['done'])) {
                        echo "data: " . json_encode(['done' => true]) . "\n\n";
                        flush();
                    }
                }
                return strlen($chunk);
            },
        ]);
        $ok = curl_exec($ch);
        curl_close($ch);
        if ($ok === false && $full === '') {
            // engine failed before any token — fallback (offline rule-based)
            $full = Model::chat('You are Edunex AI, a helpful study assistant. Answer questions from Ethiopian students concisely and accurately.', $q, ['user' => $u]);
            echo "data: " . json_encode(['delta' => $full], JSON_UNESCAPED_UNICODE) . "\n\n";
            echo "data: " . json_encode(['done' => true]) . "\n\n";
            flush();
        }
        $chatId = Database::insert('ai_chats', ['user_id' => $uid, 'title' => 'Assistant Q&A']);
        if ($chatId) ai_save_chat($u, (int)$chatId, $q, $full);
        exit;
    }
}
