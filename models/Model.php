<?php
/**
 * Model factory — pick the active AI provider from settings.
 *   ai_provider: 'local' (default) | 'openai'
 *   ai_api_url, ai_api_key, ai_model: OpenAI-compatible endpoint config
 */

class Model {

    public static function provider(): AiProvider {
        static $provider = null;
        if ($provider !== null) return $provider;
        $which = setting('ai_provider', 'local');
        if ($which === 'openai') {
            $provider = new OpenAIProvider();
        } elseif ($which === 'ollama' || $which === 'local') {
            $provider = new OllamaProvider();
        } else {
            $provider = new LocalProvider();
        }
        return $provider;
    }

    public static function chat(string $system, string $message, array $opts = []): string {
        return self::provider()->chat($system, $message, $opts);
    }

    public static function generateQuestions(string $text, int $count, string $topic = ''): array {
        return self::provider()->generateQuestions($text, $count, $topic);
    }

    public static function summarize(string $text, int $maxWords = 40): string {
        return self::provider()->summarize($text, $maxWords);
    }

    /** Human list of available providers for the settings page */
    public static function providersInfo(): array {
        $ollama = OllamaProvider::available() ? ' (C backend ready — qwen2.5-3b)' : ' (install & run "ollama serve" with the qwen2.5-3b model)';
        return [
            'local' => 'Local (offline, no key needed)',
            'ollama' => 'Ollama C backend' . $ollama,
            'openai' => 'OpenAI-compatible API (OpenAI, DeepSeek, Ollama…)',
        ];
    }
}
