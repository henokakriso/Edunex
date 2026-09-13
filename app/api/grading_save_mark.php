<?php
/**
 * API: Save a single grade mark via AJAX
 * POST: assessment_id, student_id, mark
 */
require_once __DIR__ . '/../teacher/grading.php';

$u = require_role('teacher', 'lecturer');
$uid = (int)$u['id'];
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    exit;
}

$sent = $_POST['_csrf'] ?? '';
if (!hash_equals(csrf_token(), (string)$sent)) {
    http_response_code(419);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$assessmentId = (int)($_POST['assessment_id'] ?? 0);
$studentId = (int)($_POST['student_id'] ?? 0);
$markRaw = trim($_POST['mark'] ?? '');

if ($assessmentId <= 0 || $studentId <= 0) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$assessment = Database::one(
    "SELECT a.*, c.teacher_id, c.id AS cid
     FROM assessments a JOIN courses c ON c.id = a.course_id
     WHERE a.id = ?", [$assessmentId]);
if (!$assessment || (int)$assessment['teacher_id'] !== $uid) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if (isset($assessment['result_status']) && $assessment['result_status'] === 'locked') {
    echo json_encode(['error' => 'Assessment is locked']);
    exit;
}

$maxMark = (float)$assessment['max_mark'];

if ($markRaw === '' || $markRaw === '—') {
    $mark = null;
} else {
    $mark = filter_var($markRaw, FILTER_VALIDATE_FLOAT);
    if ($mark === false || $mark < 0 || $mark > $maxMark) {
        echo json_encode(['error' => "Mark must be 0-$maxMark"]);
        exit;
    }
}

$pct = $mark !== null && $maxMark > 0 ? round(($mark / $maxMark) * 100, 2) : null;
$letter = $pct !== null ? grading_letter($pct) : null;

$existing = Database::one(
    "SELECT id, mark, status FROM grades WHERE assessment_id = ? AND student_id = ?",
    [$assessmentId, $studentId]);

if ($existing) {
    if ($existing['status'] === 'locked') {
        echo json_encode(['error' => 'Grade is locked']);
        exit;
    }
    $oldMark = (float)$existing['mark'];
    Database::update('grades', [
        'mark' => $mark,
        'percentage' => $pct,
        'letter_grade' => $letter,
        'entered_by' => $uid,
        'status' => 'draft',
    ], 'id = ?', [(int)$existing['id']]);
    if ($oldMark !== ($mark ?? 0)) {
        grading_audit((int)$existing['id'], $studentId, $assessmentId, 'update', $oldMark, $mark, $existing['status'], 'draft', $uid);
    }
} else {
    if ($mark === null) {
        echo json_encode(['ok' => true, 'mark' => null, 'pct' => null, 'letter' => null, 'msg' => 'Cleared']);
        exit;
    }
    $gid = Database::insert('grades', [
        'assessment_id' => $assessmentId,
        'student_id' => $studentId,
        'mark' => $mark,
        'percentage' => $pct,
        'letter_grade' => $letter,
        'entered_by' => $uid,
        'status' => 'draft',
    ]);
    grading_audit($gid, $studentId, $assessmentId, 'create', null, $mark, null, 'draft', $uid);
}

// Recalculate semester/final for this student
grading_recalc($studentId, (int)$assessment['cid']);

// Get updated final stats
$f = grading_calc_final($studentId, (int)$assessment['cid']);

echo json_encode([
    'ok' => true,
    'mark' => $mark,
    'pct' => $pct,
    'letter' => $letter,
    'total1' => $f['total1'],
    'total2' => $f['total2'],
    'final' => $f['adjusted'],
    'final_letter' => $f['letter'],
    'pass' => $f['pass'],
]);
exit;
