<?php
/**
 * FileSecurity — AES-256-CTR encryption, SHA-256 integrity, malicious content scanning
 *
 * Every uploaded file is scanned, hashed, and encrypted at rest.
 * On serve, files are decrypted and integrity verified.
 */
class FileSecurity {

    /** AES-256 key (32 bytes) — derived from APP_KEY */
    private static function key(): string {
        static $k = null;
        if ($k === null) {
            $raw = hash('sha256', APP_KEY . '_file_encryption', true);
            $k = $raw;
        }
        return $k;
    }

    /** Encrypt raw file content with AES-256-CTR */
    public static function encrypt(string $plain): string {
        $iv = random_bytes(16);
        $tag = '';
        $encrypted = openssl_encrypt($plain, 'aes-256-ctr', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($encrypted === false) return '';
        return $iv . $encrypted;
    }

    /** Decrypt AES-256-CTR encrypted content */
    public static function decrypt(string $blob): string {
        if (strlen($blob) < 17) return '';
        $iv = substr($blob, 0, 16);
        $encrypted = substr($blob, 16);
        $tag = '';
        $plain = openssl_decrypt($encrypted, 'aes-256-ctr', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) return '';
        return $plain;
    }

    /** Compute SHA-256 hash of content */
    public static function hash(string $content): string {
        return hash('sha256', $content);
    }

    /** Verify SHA-256 hash matches content */
    public static function verify(string $content, string $expectedHash): bool {
        return hash_equals($expectedHash, self::hash($content));
    }

    /** Scan file content for malicious scripts/code injection. Returns [safe, reason] */
    public static function scanForMalicious(string $tmpPath, string $ext): array {
        $content = @file_get_contents($tmpPath);
        if ($content === false) return [true, ''];

        // 1. Block dangerous extensions entirely
        $blockedExts = ['php','phtml','php3','php4','php5','php7','php8','phar','inc',
            'sh','bash','zsh','csh','ksh','bat','cmd','com','ps1','psm1',
            'exe','msi','dll','scr','pif','vbs','vbe','wsf','wsh','js','jse',
            'jar','class','py','rb','pl','cgi','lua','asp','aspx','jsp','jspx',
            'htaccess','htpasswd','ini','conf','config','env'];
        if (in_array(strtolower($ext), $blockedExts, true)) {
            return [false, 'File type .'.$ext.' is not allowed for security reasons'];
        }

        $lower = strtolower($content);

        // 2. Detect PHP tags (even in non-PHP files)
        if (preg_match('/<\?php|<\?=|<\?[^x]|<\?=/', $lower)) {
            return [false, 'File contains PHP code — potential script injection'];
        }

        // 3. Detect JavaScript in HTML/text files
        if (in_array($ext, ['html','htm','svg','xml','txt','md'])) {
            if (preg_match('/<script[\s>]/i', $content) || preg_match('/javascript\s*:/i', $lower)) {
                return [false, 'File contains JavaScript — potential XSS injection'];
            }
            if (preg_match('/on(mouse|key|load|error|focus|blur|submit|change|input)\s*=/i', $content)) {
                return [false, 'File contains inline event handlers — potential XSS'];
            }
        }

        // 4. Detect shell/exec commands in any file
        if (preg_match('/\b(system|exec|passthru|shell_exec|popen|proc_open|eval|assert|create_function)\s*\(/i', $lower)) {
            return [false, 'File contains dangerous function calls — potential code injection'];
        }

        // 5. Detect base64 encoded payloads (common in web shells)
        if (preg_match('/eval\s*\(\s*base64_decode\s*\(/i', $lower)) {
            return [false, 'File contains obfuscated code — potential web shell'];
        }

        // 6. Detect file upload backdoors
        if (preg_match('/move_uploaded_file|_FILES\s*\[|file_put_contents\s*\(.*\$_(GET|POST|REQUEST)/i', $lower)) {
            return [false, 'File contains upload backdoor patterns'];
        }

        // 7. Detect SQL injection patterns in non-text files
        if (preg_match('/(UNION\s+SELECT|DROP\s+TABLE|INSERT\s+INTO.*VALUES|--\s*$)/i', $lower)) {
            return [false, 'File contains SQL injection patterns'];
        }

        // 8. Detect iframes and redirects in documents
        if (in_array($ext, ['html','htm','svg','xml'])) {
            if (preg_match('/<iframe[\s>]/i', $content)) {
                return [false, 'File contains hidden iframe — potential malicious redirect'];
            }
            if (preg_match('/meta\s+http-equiv\s*=\s*["\']refresh/i', $lower)) {
                return [false, 'File contains auto-refresh redirect'];
            }
        }

        // 9. Detect null bytes (common exploit technique)
        if (strpos($content, "\x00") !== false) {
            return [false, 'File contains null bytes — potential path traversal exploit'];
        }

        // 10. Detect PE/ELF headers (executable disguised as other file)
        $header = substr($content, 0, 4);
        if ($header === "MZ\x90\x00" || $header === "\x7fELF") {
            return [false, 'File is an executable — potential malware'];
        }

        // 11. For images: verify actual image data
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            if (@getimagesize($tmpPath) === false) {
                return [false, 'File claims to be an image but has invalid image data — potential disguised script'];
            }
        }

        // 12. For PDFs: check for embedded JavaScript
        if ($ext === 'pdf') {
            if (preg_match('/\/JavaScript|\/JS\s|\/OpenAction/i', $content)) {
                return [false, 'PDF contains embedded JavaScript — potential malicious PDF'];
            }
        }

        // 13. For Office docs (OOXML): check for macros
        if (in_array($ext, ['docx','xlsx','pptx'])) {
            if (preg_match('/vbaProject|macros|macroEnabled/i', $content)) {
                return [false, 'Office document contains macros — potential macro virus'];
            }
        }

        return [true, ''];
    }

    /**
     * Full upload security pipeline: scan → hash → encrypt → save
     * Returns [ok, message, hash, encrypted]
     */
    public static function processUpload(string $tmpPath, string $ext): array {
        // 1. Malicious content scan
        [$safe, $reason] = self::scanForMalicious($tmpPath, $ext);
        if (!$safe) {
            return [false, $reason, null, false];
        }

        // 2. Read raw content
        $content = @file_get_contents($tmpPath);
        if ($content === false) {
            return [false, 'Failed to read uploaded file', null, false];
        }

        // 3. Compute integrity hash (of original content)
        $hash = self::hash($content);

        // 4. Encrypt the content
        $encrypted = self::encrypt($content);
        if ($encrypted === '') {
            return [false, 'Failed to encrypt file', null, false];
        }

        // 5. Overwrite temp file with encrypted content for move_uploaded_file
        file_put_contents($tmpPath, $encrypted);

        return [true, '', $hash, true];
    }

    /**
     * Read a stored file: decrypt if encrypted, verify integrity
     * Returns [ok, content]
     */
    public static function secureRead(string $absPath, ?string $expectedHash, bool $encrypted): array {
        if (!is_file($absPath)) return [false, ''];

        $blob = @file_get_contents($absPath);
        if ($blob === false) return [false, ''];

        // Decrypt if needed
        if ($encrypted) {
            $plain = self::decrypt($blob);
            if ($plain === '') return [false, 'Decryption failed'];
        } else {
            $plain = $blob;
        }

        // Verify integrity
        if ($expectedHash && !self::verify($plain, $expectedHash)) {
            return [false, ''];
        }

        return [true, $plain];
    }
}
