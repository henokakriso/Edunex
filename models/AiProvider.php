<?php
/**
 * AI model provider interface.
 * Plug a new model by implementing this interface and registering it in Model.php.
 */
interface AiProvider {
    /** Provider display name */
    public function name(): string;
    /** Chat completion: system prompt + user message → text reply */
    public function chat(string $system, string $message, array $opts = []): string;
    /** Generate N MCQ questions from source text (best-effort JSON) */
    public function generateQuestions(string $text, int $count, string $topic): array;
    /** Summarize source text into a short lesson description */
    public function summarize(string $text, int $maxWords = 40): string;
}
