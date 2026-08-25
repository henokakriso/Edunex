<?php
/**
 * Reports API: list/download generated reports (admin/teacher)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
if (!in_array($u['role'], ['regional', 'teacher'], true)) api_out(['ok' => false, 'error' => 'forbidden'], 403);
$reports = Database::all(
    "SELECT r.id, r.type, r.title, r.format, r.file_path, r.filters, r.created_at FROM reports r
     WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 100", [$u['id']]);
api_out(['ok' => true, 'count' => count($reports), 'reports' => $reports]);
