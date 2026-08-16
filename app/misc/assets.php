<?php
/** Serve public assets with cache headers */
class Ctl_assets {
    public function run(): void {
        $path = $_GET['p'] ?? '';
        $abs = realpath(PUBLIC_PATH . '/' . ltrim($path, '/'));
        if ($abs === false || strpos($abs, PUBLIC_PATH) !== 0 || !is_file($abs)) {
            http_response_code(404);
            exit('Not found');
        }
        $mime = match (pathinfo($abs, PATHINFO_EXTENSION)) {
            'css' => 'text/css', 'js' => 'text/javascript', 'svg' => 'image/svg+xml',
            'png' => 'image/png', 'jpg', 'jpeg' => 'image/jpeg', 'woff2' => 'font/woff2',
            'webp' => 'image/webp', default => 'application/octet-stream'
        };
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        readfile($abs);
        exit;
    }
}
