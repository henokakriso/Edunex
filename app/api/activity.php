<?php
/**
 * Activity API: recent activity (admin dashboard, desktop app)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
$limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
$rows = Database::all(
    "SELECT a.id, a.action, a.detail, a.user_id, a.created_at, u.first_name, u.last_name, u.role
     FROM activity_logs a JOIN users u ON u.id = a.user_id
     ORDER BY a.created_at DESC LIMIT $limit");
api_out(['ok' => true, 'count' => count($rows), 'activity' => $rows]);
