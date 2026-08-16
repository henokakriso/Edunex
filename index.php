<?php
/**
 * EDUNEX Front controller
 * Apache: rewrite all requests to index.php
 * Dev:    php -S localhost:8080 (path info routing works)
 *
 * With `php -S ... index.php` the router script runs for EVERY request;
 * returning false lets the built-in server serve real files (css/js/img)
 * directly instead of as HTML.
 */

$__path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__file = __DIR__ . $__path;
if ($__path !== '/' && !preg_match('/\.php$/i', $__path) && is_file($__file)) {
    return false;
}

require __DIR__ . '/includes/bootstrap.php';
