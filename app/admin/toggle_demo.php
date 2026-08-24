<?php
/**
 * Lightweight demo mode toggle — called from profile dropdown.
 * Flips demo_mode between 0 and 1, then redirects back.
 */
$u = require_login();
csrf_verify();

$current = (setting('demo_mode') ?? '0') === '1' ? '1' : '0';
$new = $current === '1' ? '0' : '1';

Database::run("INSERT INTO settings (`key`, `value`) VALUES ('demo_mode', ?) ON DUPLICATE KEY UPDATE `value` = ?", [$new, $new]);
log_activity('settings', 'Toggled demo mode: ' . ($new === '1' ? 'ON (Demo)' : 'OFF (Normal)'), (int)$u['id']);

flash('success', $new === '1' ? 'Demo mode ON — showing sample data.' : 'Normal mode ON — showing only user-created data.');

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$path = parse_url($referer, PHP_URL_PATH) ?: '/dashboard';
$query = parse_url($referer, PHP_URL_QUERY) ?: '';
$internal = $query ? ltrim($path, '/') . '?' . $query : ltrim($path, '/');

// Strip APP_URL prefix if present
$appPath = parse_url(url(''), PHP_URL_PATH) ?: '';
if ($appPath && str_starts_with($internal, ltrim($appPath, '/'))) {
    $internal = substr($internal, strlen(ltrim($appPath, '/')));
}
$internal = ltrim($internal, '/');

// Only redirect to internal routes
if ($internal && !str_starts_with($internal, 'http')) {
    redirect($internal);
} else {
    redirect('dashboard');
}
