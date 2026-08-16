<?php
/**
 * Exam API: autosave drafts + flag questions
 * Auth: any logged-in user; only the owner of the attempt may save.
 */
header('Content-Type: application/json');
try {
    $u = require_login(); // may exit; we catch below
} catch (Throwable $e) {
    http_response_code(401);
    exit(json_encode(['ok' => false, 'error' => 'auth']));
}

$route = $_GET['r'] ?? '';
$body = json_decode(file_get_contents('php://input'), true) ?: [];
$aid = (int)($body['attempt_id'] ?? 0);
$attempt = Database::one("SELECT * FROM exam_attempts WHERE id = ?", [$aid]);
if (!$attempt || (int)$attempt['student_id'] !== (int)$u['id']) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'error' => 'forbidden']));
}
if ($attempt['status'] !== 'in_progress') {
    exit(json_encode(['ok' => false, 'error' => 'submitted']));
}

if (str_contains($route, 'flag')) {
    $qid = (int)($body['question_id'] ?? 0);
    $flagged = json_decode($attempt['flagged'] ?: '[]', true) ?: [];
    $flagged = array_values(array_map('intval', $flagged));
    if (in_array($qid, $flagged, true)) {
        $flagged = array_values(array_diff($flagged, [$qid]));
        $isFlagged = false;
    } else {
        $flagged[] = $qid;
        $isFlagged = true;
    }
    Database::update('exam_attempts', ['flagged' => json_encode($flagged)], 'id = ?', [$aid]);
    exit(json_encode(['ok' => true, 'flagged' => $isFlagged]));
}

// autosave: keep only fields for questions in this exam
$qids = Database::all("SELECT id FROM exam_questions WHERE exam_id = ?", [$attempt['exam_id']]);
$allowed = [];
foreach ($qids as $q) $allowed[] = 'q_' . $q['id'];
$clean = [];
foreach ($body as $k => $v) {
    if (str_starts_with((string)$k, 'm_left_') || str_starts_with((string)$k, 'm_')) $clean[$k] = $v;
    elseif (in_array($k, $allowed, true)) $clean[$k] = $v;
}
Database::update('exam_attempts', ['auto_save' => json_encode($clean)], 'id = ?', [$aid]);
exit(json_encode(['ok' => true]));
