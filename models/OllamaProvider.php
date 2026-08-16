<?php
/**
 * OllamaProvider — local C-backed LLM via llama.cpp (ollama serve).
 * Uses the two downloaded models:
 *   - qwen2.5-3b-instruct-q4_k_m.gguf (text model, registered as "edunex-tutor")
 *   - deepseek-vl2-tiny (vision model, registered as "edunex-vision" when available)
 * Falls back to the rule-based AiTutor engine when the server is unreachable.
 */

class OllamaProvider implements AiProvider {

    private string $host;
    private string $model;

    public function __construct(string $host = '', string $model = '') {
        $this->host = rtrim($host ?: (setting('ai_api_url') ?: 'http://127.0.0.1:11434'), '/');
        $this->model = $model ?: (setting('ai_model') ?: 'edunex-tutor');
    }

    public function name(): string {
        return 'Ollama (C/llama.cpp): ' . $this->model;
    }

    public static function available(): bool {
        return self::isOllamaUp();
    }

    private static function isOllamaUp(): bool {
        static $up = null;
        if ($up !== null) return $up;
        $up = false;
        $ch = curl_init('http://127.0.0.1:11434/api/tags');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 2]);
        $r = curl_exec($ch);
        if ($r !== false) $up = str_contains((string)$r, 'models');
        curl_close($ch);
        return $up;
    }

    public static function isUp(): bool { return self::isOllamaUp(); }

    private function call(array $messages, float $temperature = 0.7, int $maxTokens = 1200, bool $json = false): string {
        $body = json_encode([
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'temperature' => $temperature,
            'num_predict' => $maxTokens,
            'options' => ['temperature' => $temperature, 'num_predict' => $maxTokens],
        ]);
        $ch = curl_init($this->host . '/api/chat');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 90,
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($resp === false) throw new RuntimeException('Ollama request failed: ' . $err);
        $json2 = json_decode($resp, true);
        $out = trim($json2['message']['content'] ?? '');
        if ($out === '') throw new RuntimeException('Ollama returned empty response: ' . mb_substr((string)$resp, 0, 200));
        return $out;
    }

    public function chat(string $system, string $message, array $opts = []): string {
        if (!self::isOllamaUp()) {
            return AiTutor::respond($message, $opts['user'] ?? ['id' => 0, 'school_id' => null]);
        }
        try {
            return $this->call([
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $message],
            ]);
        } catch (Throwable $e) {
            error_log('[OllamaProvider] chat failed: ' . $e->getMessage());
            return AiTutor::respond($message, $opts['user'] ?? ['id' => 0, 'school_id' => null]);
        }
    }

    /** Ask the model for JSON questions, fall back to LocalProvider heuristics */
    public function generateQuestions(string $text, int $count, string $topic): array {
        if (self::isOllamaUp()) {
            try {
                $sample = mb_substr($text, 0, 12000);
                $out = $this->call([
                    ['role' => 'system', 'content' => 'You are a teacher creating multiple-choice questions. '
                        . 'Return ONLY a JSON array, no markdown, each item: {"question": "...", "options": ["a","b","c","d"], "answer": "exact correct option", "explanation": "..."}'],
                    ['role' => 'user', 'content' => "Topic: $topic\nCreate $count MCQs from this text:\n$sample"],
                ], 0.4, 2000);
                $out = trim($out);
                $out = preg_replace('/^```(json)?/i', '', $out);
                $out = preg_replace('/```$/', '', $out);
                $arr = json_decode($out, true);
                if (!is_array($arr)) {
                    preg_match('/\[[\s\S]*\]/', $out, $m);
                    $arr = $m ? json_decode($m[0], true) : null;
                }
                $qs = [];
                $i = 0;
                foreach ((array)$arr as $item) {
                    if (!isset($item['question'], $item['options'], $item['answer'])) continue;
                    if (count($qs) >= $count) break;
                    $qs[] = [
                        'question' => (string)$item['question'],
                        'options' => array_values(array_map('strval', $item['options'])),
                        'answer' => (string)$item['answer'],
                        'explanation' => (string)($item['explanation'] ?? ''),
                        'topic' => $topic ?: 'generated',
                        'index' => $i++,
                    ];
                }
                if ($qs) return $qs;
            } catch (Throwable $e) {
                error_log('[OllamaProvider] generateQuestions failed: ' . $e->getMessage());
            }
        }
        return (new LocalProvider())->generateQuestions($text, $count, $topic);
    }

    public function summarize(string $text, int $maxWords = 40): string {
        if (self::isOllamaUp()) {
            try {
                return $this->call([
                    ['role' => 'system', 'content' => 'Summarize the following lesson text in at most ' . $maxWords . ' words. Plain text only.'],
                    ['role' => 'user', 'content' => mb_substr(strip_tags($text), 0, 12000)],
                ], 0.3, 200);
            } catch (Throwable $e) {
                error_log('[OllamaProvider] summarize failed: ' . $e->getMessage());
            }
        }
        return (new LocalProvider())->summarize($text, $maxWords);
    }
}
