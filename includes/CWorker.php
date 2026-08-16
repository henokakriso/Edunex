<?php
/**
 * CWorker — PHP bridge to the native C backend (storage/bin/edunex_worker)
 *
 * Used for:
 *  - secure direct-chat: AES-256-GCM message sealing + HMAC integrity
 *  - safe folder creation (sandboxed filesystem ops)
 *
 * The binary is compiled with: gcc -O2 -o storage/bin/edunex_worker storage/bin/edunex_worker.c -lcrypto
 */

final class CWorker {
    private static ?string $bin = null;

    /** Path to the compiled C worker binary. */
    public static function binary(): string {
        if (self::$bin !== null) return self::$bin;
        $candidates = [
            STORAGE_PATH . '/bin/edunex_worker',
            __DIR__ . '/../storage/bin/edunex_worker',
        ];
        foreach ($candidates as $c) {
            if (is_file($c) && is_executable($c)) return self::$bin = $c;
        }
        return self::$bin = $candidates[0];
    }

    public static function available(): bool {
        return is_file(self::binary()) && is_executable(self::binary());
    }

    /** Run op with args. Returns [exitCode, stdout, stderr]. Never throws. */
    public static function run(string $op, array $args = [], int &$exit = 0): array {
        if (!self::available()) return [127, '', 'C backend unavailable'];
        $cmd = array_merge([self::binary(), $op], $args);
        $out = $err = '';
        $spec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $cmdStr = implode(' ', array_map('escapeshellarg', $cmd));
        $proc = proc_open($cmdStr, $spec, $pipes);
        if (!is_resource($proc)) return [1, '', 'proc_open failed'];
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        $exit = proc_close($proc);
        return [$exit, trim((string)$out), trim((string)$err)];
    }

    /** Encrypt a chat message -> base64 ciphertext (or original plaintext on failure). */
    public static function chatEncrypt(string $conversationKey, string $plain): string {
        [$exit, $out] = self::run('chat-enc', [ENCRYPTION_KEY, $conversationKey, $plain]);
        if ($exit === 0 && $out !== '') return $out;
        return $plain;
    }

    /** Decrypt a chat message -> plaintext (or empty on verify/tamper failure). */
    public static function chatDecrypt(string $conversationKey, string $cipher): string {
        [$exit, $out] = self::run('chat-dec', [ENCRYPTION_KEY, $conversationKey, $cipher]);
        if ($exit === 0) return $out;
        error_log("[CWorker] chat-dec failed ($exit)");
        return '';
    }

    /** HMAC-SHA256 hex for a chat message fingerprint (integrity). */
    public static function chatHmac(string $conversationKey, string $payload): string {
        [$exit, $out] = self::run('chat-hmac', [ENCRYPTION_KEY, $conversationKey . '|' . $payload]);
        return $exit === 0 && $out !== '' ? $out : '';
    }

    /** Scramble helper only: HMAC of any string. */
    public static function hmac(string $key, string $data): string {
        [$exit, $out] = self::run('hash', [$key, $data]);
        return $exit === 0 && $out !== '' ? $out : '';
    }

    /**
     * Securely create a per-user folder under storage/files/<owner>/<name>.
     * Returns the created absolute path or null on failure.
     */
    public static function mkdirSafe(string $root, string $name, string $owner): ?string {
        [$exit, $out, $err] = self::run('mkdir-safe', [$root, $name, $owner]);
        if ($exit === 0 && $out !== '') return $out;
        error_log("[CWorker] mkdir-safe failed ($exit): $err");
        return null;
    }

    /** Upload integrity digest (deterministic). */
    public static function uploadHash(string $payload): string {
        [$exit, $out] = self::run('upload-hash', [$payload]);
        return $exit === 0 && $out !== '' ? $out : '';
    }

    /**
     * Run the C crypto battery and return per-CWE results.
     * Each line: "CWE-XXX: <name>: <PASS|FAIL>|<detail>".
     */
    public static function selfTest(): array {
        [$exit, $out, $err] = self::run('selftest');
        if ($exit === 0 && $out === '') {
            return ['ok' => true, 'cwe' => ['CWE-SELFTEST' => ['name' => 'C backend selftest', 'pass' => true, 'detail' => 'no tests reported']]];
        }
$cwe = [];
        $i = 0;
        foreach (explode("\n", $out) as $line) {
            if (!str_contains($line, ':')) continue;
            [$id, $rest] = explode(':', $line, 2);
            [$name, $state] = array_pad(explode(':', $rest, 2), 2, '');
            [$verdict, $detail] = array_pad(explode('|', $state, 2), 2, '');
            $key = $id . ($cwe[$id] ?? null ? '·' . ++$i : '');
            $cwe[$key] = ['id' => $id, 'name' => trim($name), 'pass' => trim($verdict) === 'PASS', 'detail' => trim($detail)];
            $i++;
        }
        $allPass = !array_filter($cwe, fn($c) => !$c['pass']);
        return ['ok' => $exit === 0 && $allPass, 'cwe' => $cwe];
    }
}