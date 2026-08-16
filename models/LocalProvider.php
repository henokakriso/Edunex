<?php
/**
 * Local rule-based provider — works fully offline, no API key needed.
 * Chat uses the built-in AiTutor engine; questions are extracted heuristically
 * from lesson text (facts, definitions, numbers) into MCQs.
 */

class LocalProvider implements AiProvider {

    public function name(): string {
        return 'Local (offline rule engine)';
    }

    public function chat(string $system, string $message, array $opts = []): string {
        $user = $opts['user'] ?? ['id' => 0, 'school_id' => null];
        return AiTutor::respond($message, $user);
    }

    /** Extract candidate fact sentences: definitions, numeric facts, "X is Y" */
    public function generateQuestions(string $text, int $count, string $topic): array {
        $count = max(3, min(20, $count));
        $text = preg_replace('/\s+/u', ' ', $text);
        $sentences = preg_split('/(?<=[.!?])\s+/u', trim($text));
        $facts = [];
        foreach ($sentences as $s) {
            $s = trim($s, " \t\n\r\u{00A0}");
            if (mb_strlen($s) < 30 || mb_strlen($s) > 320) continue;
            $low = mb_strtolower($s);
            if (preg_match('/chapter \d|exercise|page \d|^references|^table of contents/i', $s)) continue;
            if (preg_match('/\b(is|are|was|were|called|means|equals|has|have|contains|consists|divided into|measured in)\b/i', $s)) {
                $facts[] = $s;
            }
            if (count($facts) >= $count * 2) break;
        }
        if (count($facts) < $count) {
            foreach ($sentences as $s) {
                $s = trim($s, " \t\n\r\u{00A0}");
                if (mb_strlen($s) >= 30 && mb_strlen($s) <= 320 && !in_array($s, $facts, true)) $facts[] = $s;
                if (count($facts) >= $count) break;
            }
        }
        $questions = [];
        $idx = 0;
        foreach (array_slice($facts, 0, $count) as $fact) {
            $q = self::makeQuestion($fact);
            if ($q) $questions[] = $q + ['topic' => $topic ?: 'generated', 'index' => $idx++];
        }
        return $questions;
    }

    /** Convert one fact sentence into a fill-the-blank MCQ with distractors from other sentences */
    private static function makeQuestion(string $fact): ?array {
        $words = preg_split('/\s+/u', $fact);
        $candidates = [];
        foreach ($words as $i => $w) {
            $w = trim($w, '.,;:()');
            if (mb_strlen($w) >= 5 && preg_match('/^[A-Za-z][A-Za-z-]{4,}$/', $w)) $candidates[] = $w;
        }
        $candidates = array_values(array_unique($candidates));
        if (count($candidates) < 2) return null;
        shuffle($candidates);
        $answer = $candidates[0];
        $rest = array_slice($candidates, 1);
        $distractors = [];
        foreach ($rest as $w) {
            if (strcasecmp($w, $answer) !== 0 && count($distractors) < 3) $distractors[] = $w;
        }
        $options = array_merge([$answer], $distractors);
        if (count($options) < 2) return null;
        shuffle($options);
        $question = preg_replace('/\b' . preg_quote($answer, '/') . '\b/u', '______', $fact, 1);
        return [
            'question' => $question,
            'options' => $options,
            'answer' => $answer,
            'explanation' => 'Complete sentence: ' . $fact,
        ];
    }

    public function summarize(string $text, int $maxWords = 40): string {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $words = preg_split('/\s+/u', trim($text));
        $short = implode(' ', array_slice($words, 0, $maxWords));
        if (count($words) > $maxWords) $short .= '…';
        return $short;
    }
}
