<?php
/** Serve stored files safely (from storage/) */
class Ctl_file {
    public function run(): void {
        $path = $_GET['p'] ?? ($_GET['f'] ?? '');
        $abs = safe_storage_path($path);
        if (!$abs || !is_file($abs)) {
            http_response_code(404);
            exit('File not found.');
        }
        $mime = match (pathinfo($abs, PATHINFO_EXTENSION)) {
            'pdf' => 'application/pdf', 'jpg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'mp4' => 'video/mp4', 'webm' => 'video/webm',
            'mp3' => 'audio/mpeg', 'zip' => 'application/zip', 'svg' => 'image/svg+xml',
            'txt', 'md' => 'text/plain', 'csv' => 'text/csv', 'json' => 'application/json',
            'html' => 'text/html', 'css' => 'text/css', 'js' => 'text/javascript',
            default => 'application/octet-stream'
        };
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($abs));
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
        readfile($abs);
        exit;
    }
}
