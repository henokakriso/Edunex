<?php
/** Serve stored files safely (from storage/) with decryption + integrity check */
class Ctl_file {
    public function run(): void {
        $path = $_GET['p'] ?? ($_GET['f'] ?? '');
        $abs = safe_storage_path($path);
        if (!$abs || !is_file($abs)) {
            http_response_code(404);
            exit('File not found.');
        }
        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));

        // Check if this file is encrypted in the DB
        $isEncrypted = false;
        $fileHash = null;
        try {
            $dbFile = Database::one("SELECT is_encrypted, file_hash FROM files WHERE path = ?", [$_GET['p'] ?? $_GET['f'] ?? '']);
            if ($dbFile) {
                $isEncrypted = (bool)$dbFile['is_encrypted'];
                $fileHash = $dbFile['file_hash'];
            }
        } catch (Throwable $e) { /* non-blocking — serve raw for legacy files */ }

        // Decrypt if encrypted
        $content = null;
        if ($isEncrypted) {
            $raw = @file_get_contents($abs);
            if ($raw === false) {
                http_response_code(500);
                exit('Failed to read file.');
            }
            $content = FileSecurity::decrypt($raw);
            if ($content === '') {
                http_response_code(500);
                exit('Failed to decrypt file.');
            }
            // Verify integrity
            if ($fileHash && !FileSecurity::verify($content, $fileHash)) {
                http_response_code(410);
                exit('File integrity check failed — file may have been tampered with.');
            }
        }

        $mime = match ($ext) {
            'pdf' => 'application/pdf', 'jpg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'mp4' => 'video/mp4', 'webm' => 'video/webm',
            'mp3' => 'audio/mpeg', 'zip' => 'application/zip', 'svg' => 'image/svg+xml',
            'txt', 'md' => 'text/plain', 'csv' => 'text/csv', 'json' => 'application/json',
            'html' => 'text/html', 'css' => 'text/css', 'js' => 'text/javascript',
            default => 'application/octet-stream'
        };
        header('Content-Type: ' . $mime);
        if ($content !== null) {
            header('Content-Length: ' . strlen($content));
        } else {
            header('Content-Length: ' . filesize($abs));
        }
        if (!empty($_GET['dl'])) {
            header('Content-Disposition: attachment; filename="' . basename($abs) . '"');
        } else {
            header('Content-Disposition: inline; filename="' . basename($abs) . '"');
        }
        if (!empty($_GET['item']) && $_GET['item'] === 'library') {
            try {
                Database::run("UPDATE library_items SET downloads = downloads + 1 WHERE id = ?", [(int)$_GET['id']]);
            } catch (Throwable $e) { /* non-blocking */ }
        }
        if ($content !== null) {
            echo $content;
        } else {
            readfile($abs);
        }
        exit;
    }
}
