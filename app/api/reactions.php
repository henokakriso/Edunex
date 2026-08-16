<?php
/**
 * Reactions API: toggle a reaction (like/love/laugh/wow/sad/help)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_out(['ok' => false, 'error' => 'method'], 405);
$in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$type = (string)($in['target_type'] ?? '');
$tid = (int)($in['target_id'] ?? 0);
$reaction = (string)($in['reaction'] ?? 'like');
if (!in_array($type, ['post', 'forum', 'message', 'announcement'], true)) api_out(['ok' => false, 'error' => 'bad_type'], 400);
if (!in_array($reaction, ['like', 'love', 'laugh', 'wow', 'sad', 'help'], true)) $reaction = 'like';
if (!$tid) api_out(['ok' => false, 'error' => 'bad_target'], 400);

$existing = Database::one("SELECT * FROM reactions WHERE user_id = ? AND target_type = ? AND target_id = ?", [$u['id'], $type, $tid]);
if ($existing) {
    if ($existing['reaction'] === $reaction) {
        Database::delete('reactions', 'id = ?', [$existing['id']]);
        api_out(['ok' => true, 'active' => false]);
    }
    Database::update('reactions', ['reaction' => $reaction], 'id = ?', [$existing['id']]);
} else {
    Database::insert('reactions', ['user_id' => $u['id'], 'target_type' => $type, 'target_id' => $tid, 'reaction' => $reaction]);
}
$counts = [];
foreach (Database::all("SELECT reaction, COUNT(*) AS n FROM reactions WHERE target_type = ? AND target_id = ? GROUP BY reaction", [$type, $tid]) as $r) $counts[$r['reaction']] = (int)$r['n'];
api_out(['ok' => true, 'active' => true, 'counts' => $counts]);
