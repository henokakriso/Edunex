<?php
/**
 * OpenAI-compatible HTTP provider (works with OpenAI, DeepSeek, Ollama, LM Studio…).
 * Config via settings: ai_provider = openai, ai_api_url, ai_api_key, ai_model.
 * Falls back to the local engine when the API is unreachable.
 */

class OpenAIProvider implements AiProvider {

    private string $url;
    private string $key;
    private string $model;

    public function __construct(string $url = '', string $key = '', string $model = '') {
        $this->url = rtrim($url ?: (setting('ai_api_url') ?: 'https://api.openai.com/v1'), '/');
        $this->key = $key ?: setting('ai_api_key');
        $this->model = $model ?: (setting('ai_model') ?: 'gpt-4o-mini');
    }

    public function name(): string {
        return 'OpenAI-compatible: ' . $this->model;
    }

    private function call(array $messages, float $temperature = 0.7, int $maxTokens = 1200): string {
        if ($this->key === '') {
            throw new RuntimeException('OpenAI provider selected but ai_api_key is not set.');
        }
        $body = json_encode([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);
        $ch = curl_init($this->url . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->key,
            ],
            CURLOPT_TIMEOUT => 60,
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($resp === false) throw new RuntimeException('OpenAI request failed: ' . $err);
        $json = json_decode($resp, true);
        return trim($json['choices'][0]['message']['content'] ?? '');
    }

    public function chat(string $system, string $message, array $opts = []): string {
        try {
            return $this->call([
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $message],
            ]);
        } catch (Throwable $e) {
            return 'Warning: ' . $e->getMessage() . " — using local engine:\n\n" . AiTutor::respond($message, $opts['user'] ?? ['id' => 0]);
        }
    }

    /** Ask the model for JSON questions, fall back to LocalProvider heuristics */
    public function generateQuestions(string $text, int $count, string $topic): array {
        try {
            $sample = mb_substr($text, 0, 14000);
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
            // fall through to local
        }
        return (new LocalProvider())->generateQuestions($text, $count, $topic);
    }

    public function summarize(string $text, int $maxWords = 40): string {
        try {
            return $this->call([
                ['role' => 'system', 'content' => 'Summarize the following lesson text in at most ' . $maxWords . ' words. Plain text only.'],
                ['role' => 'user', 'content' => mb_substr(strip_tags($text), 0, 14000)],
            ], 0.3, 200);
        } catch (Throwable $e) {
            return (new LocalProvider())->summarize($text, $maxWords);
        }
    }
}
