<?php
/**
 * Backups API: list/create/delete database backups (admin)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
if ($u['role'] !== 'sysadmin') api_out(['ok' => false, 'error' => 'forbidden'], 403);
$dir = STORAGE_PATH . '/backups';
if (!is_dir($dir)) @mkdir($dir, 0775, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = 'edunex_backup_' . date('Ymd_His') . '.sql';
    $path = $dir . '/' . $file;
    $cmd = sprintf('mysqldump --host=%s --port=%s --user=%s --password=%s --routines --single-transaction %s > %s 2>/dev/null',
        escapeshellarg(DB_HOST), escapeshellarg(DB_PORT), escapeshellarg(DB_USER), escapeshellarg(DB_PASS), escapeshellarg(DB_NAME), escapeshellarg($path));
    exec($cmd, $o, $rc);
    if ($rc !== 0 || !is_file($path)) api_out(['ok' => false, 'error' => 'dump_failed'], 500);
    api_out(['ok' => true, 'file' => $file, 'size' => filesize($path)]);
}

$list = [];
foreach (glob($dir . '/*.sql') ?: [] as $f) {
    $list[] = ['file' => basename($f), 'size' => filesize($f), 'created' => date('Y-m-d H:i:s', filemtime($f))];
}
usort($list, fn($a, $b) => strcmp($b['created'], $a['created']));
api_out(['ok' => true, 'count' => count($list), 'backups' => $list]);
