<?php
/**
 * Settings API: get/update site settings (admin)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
if ($u['role'] !== 'sysadmin') api_out(['ok' => false, 'error' => 'forbidden'], 403);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $updates = (int)0;
    foreach (['site_name', 'site_tagline', 'maintenance_mode', 'session_lifetime', 'default_theme'] as $k) {
        if (array_key_exists($k, $in)) {
            Database::query("INSERT INTO settings (`key`, `value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [$k, (string)$in[$k]]);
            $updates++;
        }
    }
    api_out(['ok' => true, 'updated' => $updates]);
}
$settings = [];
foreach (Database::all("SELECT * FROM settings") as $s) $settings[$s['key']] = $s['value'];
api_out(['ok' => true, 'settings' => $settings]);
