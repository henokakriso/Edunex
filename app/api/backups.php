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
    // Use temp config file to avoid password in process list
    $mycnf = tempnam(sys_get_temp_dir(), 'mycnf');
    file_put_contents($mycnf, "[client]\nhost=" . DB_HOST . "\nport=" . DB_PORT . "\nuser=" . DB_USER . "\npassword=" . DB_PASS . "\n");
    chmod($mycnf, 0600);
    $cmd = sprintf('mysqldump --no-defaults --defaults-extra-file=%s --routines --single-transaction %s > %s 2>/dev/null',
        escapeshellarg($mycnf), escapeshellarg(DB_NAME), escapeshellarg($path));
    exec($cmd, $o, $rc);
    @unlink($mycnf);
    if ($rc !== 0 || !is_file($path)) api_out(['ok' => false, 'error' => 'dump_failed'], 500);
    api_out(['ok' => true, 'file' => $file, 'size' => filesize($path)]);
}

$list = [];
foreach (glob($dir . '/*.sql') ?: [] as $f) {
    $list[] = ['file' => basename($f), 'size' => filesize($f), 'created' => date('Y-m-d H:i:s', filemtime($f))];
}
usort($list, fn($a, $b) => strcmp($b['created'], $a['created']));
api_out(['ok' => true, 'count' => count($list), 'backups' => $list]);
