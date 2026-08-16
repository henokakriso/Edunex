<?php
/**
 * AI chat API: rule-based tutor endpoint (used by web + desktop)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_out(['ok' => false, 'error' => 'method'], 405);
$in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$message = trim((string)($in['message'] ?? ''));
$courseId = (int)($in['course_id'] ?? 0);
if ($message === '') api_out(['ok' => false, 'error' => 'empty'], 400);

$tutor = Model::chat('You are Edunex AI, a friendly Ethiopian school tutor.', $message, ['user' => $u]);
api_out(['ok' => true, 'reply' => $tutor]);
