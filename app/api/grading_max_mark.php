<?php
/**
 * API: Update assessment max_mark (out of)
 * POST: assessment_id, max_mark
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

// Manual CSRF check (return JSON error)
$sent = $_POST['_csrf'] ?? '';
if (!hash_equals(csrf_token(), (string)$sent)) {
    http_response_code(419);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}
$assessmentId = (int)($_POST['assessment_id'] ?? 0);
$newMax = (float)($_POST['max_mark'] ?? 0);

if ($assessmentId <= 0 || $newMax <= 0 || $newMax > 100) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$assessment = Database::one(
    "SELECT a.*, c.teacher_id FROM assessments a JOIN courses c ON c.id = a.course_id WHERE a.id = ?",
    [$assessmentId]);
if (!$assessment || (int)$assessment['teacher_id'] !== $uid) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if ($assessment['result_status'] === 'locked') {
    echo json_encode(['error' => 'Cannot edit locked assessment']);
    exit;
}

// Check semester total won't exceed 100
$semester = (int)($assessment['semester'] ?? 0);
if ($semester >= 1 && $semester <= 2) {
    $typeSlugs = $semester === 1 ? "('r1','r2')" : "('r3','r4')";
    $usedRows = Database::all(
        "SELECT a.id, a.type_slug, MAX(a.max_mark) AS max_mark
         FROM assessments a WHERE a.course_id = ? AND a.type_slug IN $typeSlugs AND a.status = 'published' AND a.id != ?
         GROUP BY a.type_slug, a.id",
        [$assessment['course_id'], $assessmentId]);
    $otherUsed = 0;
    foreach ($usedRows as $r) $otherUsed += (float)$r['max_mark'];

    if ($otherUsed + $newMax > 100) {
        $remaining = max(0, 100 - $otherUsed);
        echo json_encode(['error' => "Round $semester would exceed 100. Only $remaining remaining (other assessments use $otherUsed/100)."]);
        exit;
    }
}

// Update max_mark
$oldMax = (float)$assessment['max_mark'];
Database::update('assessments', ['max_mark' => $newMax], 'id = ?', [$assessmentId]);

// Recalculate all grades for this assessment
$grades = Database::all("SELECT id, mark FROM grades WHERE assessment_id = ?", [$assessmentId]);
$warning = null;
foreach ($grades as $g) {
    $mark = (float)$g['mark'];
    $pct = $newMax > 0 ? round(($mark / $newMax) * 100, 2) : 0;
    $letter = grading_letter($pct);
    Database::update('grades', ['percentage' => $pct, 'letter_grade' => $letter], 'id = ?', [(int)$g['id']]);
    if ($mark > $newMax) {
        $warning = "Warning: Some students have marks above the new max ($newMax). Their marks were not reduced.";
    }
}

// Recalculate semester/final for all enrolled students
$enrolled = Database::all("SELECT user_id FROM course_enrollments WHERE course_id = ?", [$assessment['course_id']]);
foreach ($enrolled as $e) {
    grading_recalc((int)$e['user_id'], (int)$assessment['course_id']);
}

echo json_encode(['ok' => true, 'warning' => $warning]);
exit;
