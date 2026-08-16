<?php
/**
 * Notify API: create a notification for a user (admin/system tools)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
if (!in_array($u['role'], ['admin', 'teacher'], true)) api_out(['ok' => false, 'error' => 'forbidden'], 403);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_out(['ok' => false, 'error' => 'method'], 405);
$in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$toId = (int)($in['to_id'] ?? 0);
$title = trim((string)($in['title'] ?? ''));
$body = trim((string)($in['body'] ?? ''));
$link = trim((string)($in['link'] ?? ''));
if (!$toId || $title === '') api_out(['ok' => false, 'error' => 'missing_fields'], 400);
notify($toId, $in['type'] ?? 'system', $title, $body, $link);
api_out(['ok' => true]);
