<?php
/**
 * Notifications API: poll + actions for the C desktop app / JS navbar
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $in['action'] ?? ($_POST['action'] ?? '');
    if ($action === 'mark' && !empty($in['id'])) {
        Database::update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'id = ? AND user_id = ?', [(int)$in['id'], $u['id']]);
        api_out(['ok' => true]);
    }
    if ($action === 'markall') {
        Database::update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'user_id = ? AND read_at IS NULL', [$u['id']]);
        api_out(['ok' => true]);
    }
    if ($action === 'delete' && !empty($in['id'])) {
        Database::delete('notifications', 'id = ? AND user_id = ?', [(int)$in['id'], $u['id']]);
        api_out(['ok' => true]);
    }
    api_out(['ok' => false, 'error' => 'unknown action'], 400);
}

$unread = (int)Database::scalar("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL", [$u['id']], 0);
$recent = Database::all(
    "SELECT id, type, title, body, link, read_at, created_at FROM notifications
     WHERE user_id = ? ORDER BY created_at DESC LIMIT 15", [$u['id']]);
api_out(['ok' => true, 'unread' => $unread, 'items' => array_map(function ($n) {
    $n['id'] = (int)$n['id'];
    $n['read'] = !empty($n['read_at']);
    return $n;
}, $recent)]);
