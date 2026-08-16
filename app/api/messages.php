<?php
/**
 * Messages API: send a direct message
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_out(['ok' => false, 'error' => 'method'], 405);
$in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$toId = (int)($in['to_id'] ?? 0);
$body = trim((string)($in['body'] ?? ''));
if (!$toId || $body === '') api_out(['ok' => false, 'error' => 'missing_fields'], 400);
if ($toId === $u['id']) api_out(['ok' => false, 'error' => 'self_message'], 400);

$other = Database::one("SELECT id FROM users WHERE id = ? AND status = 'active'", [$toId]);
if (!$other) api_out(['ok' => false, 'error' => 'recipient_not_found'], 404);

$conv = Database::one(
    "SELECT c.id FROM conversations c
     JOIN conversation_members m ON m.conversation_id = c.id AND m.user_id = ? AND c.is_group = 0
     JOIN conversation_members m2 ON m2.conversation_id = c.id AND m2.user_id = ? AND c.is_group = 0
     WHERE (SELECT COUNT(*) FROM conversation_members cm WHERE cm.conversation_id = c.id) = 2
     LIMIT 1", [$u['id'], $toId]);
if (!$conv) {
    $cKey = substr(CWorker::hmac(API_SECRET, 'conv:' . min($u['id'], $toId) . ':' . max($u['id'], $toId)), 0, 64);
    $cid = Database::insert('conversations', ['school_id' => (int)($u['school_id'] ?? my_school_id()), 'is_group' => 0, 'title' => '', 'conv_key' => $cKey]);
    Database::insert('conversation_members', ['conversation_id' => $cid, 'user_id' => $u['id']]);
    Database::insert('conversation_members', ['conversation_id' => $cid, 'user_id' => $toId]);
} else {
    $cid = (int)$conv['id'];
}
$convKey = (string)Database::scalar("SELECT conv_key FROM conversations WHERE id = ?", [$cid], '');
$stored = $body;
$hmac = '';
if ($convKey !== '') {
    $stored = CWorker::chatEncrypt($convKey, $body);
    $hmac = CWorker::chatHmac($convKey, $stored);
    if ($hmac === '') $stored = $body;
}
Database::insert('messages', ['conversation_id' => $cid, 'sender_id' => $u['id'], 'body' => $stored, 'hmac' => $hmac]);
notify($toId, 'message', $u['first_name'] . ' messaged you', mb_strimwidth($body, 0, 80, '…'), 'messages');
api_out(['ok' => true, 'conversation_id' => $cid]);
