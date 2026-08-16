<?php
/**
 * FcardTool — helper for the C flashcard image editor (storage/bin/fcard_edit).
 * Stamps question/answer text onto picture flashcards via the native worker.
 */

final class FcardTool {
    private static ?string $bin = null;

    public static function binary(): string {
        if (self::$bin !== null) return self::$bin;
        $candidates = [
            STORAGE_PATH . '/bin/fcard_edit',
            __DIR__ . '/../storage/bin/fcard_edit',
        ];
        foreach ($candidates as $c) {
            if (is_file($c) && is_executable($c)) return self::$bin = $c;
        }
        return self::$bin = $candidates[0];
    }

    public static function available(): bool {
        return is_file(self::binary()) && is_executable(self::binary());
    }
}
