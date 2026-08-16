<?php
/**
 * Attendance API: mark attendance (desktop/mobile)
 * POST: student_id + course_id + date(optional) + status
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_out(['ok' => false, 'error' => 'method'], 405);
$in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$courseId = (int)($in['course_id'] ?? 0);
$status = (string)($in['status'] ?? 'present');
if (!in_array($status, ['present', 'absent', 'late', 'excused'], true)) $status = 'present';

if ($u['role'] === 'teacher') {
    $sid = (int)($in['student_id'] ?? 0);
    if (!$courseId || !$sid) api_out(['ok' => false, 'error' => 'missing_fields'], 400);
    $owns = Database::one("SELECT id FROM courses WHERE id = ? AND teacher_id = ?", [$courseId, $u['id']]);
    if (!$owns) api_out(['ok' => false, 'error' => 'forbidden'], 403);
    $date = (string)($in['date'] ?? date('Y-m-d'));
    Database::query(
        "INSERT INTO attendance (school_id, course_id, student_id, date, status, recorded_by)
         VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE status = VALUES(status), recorded_by = VALUES(recorded_by)",
        [$u['school_id'], $courseId, $sid, $date, $status, $u['id']]);
    api_out(['ok' => true]);
}
if ($u['role'] === 'student') {
    // Student self-checkin with teacher code
    $code = (string)($in['code'] ?? '');
    $room = Database::one("SELECT * FROM attendance_codes WHERE code = ? AND expires_at > NOW()", [$code]);
    if (!$room) api_out(['ok' => false, 'error' => 'invalid_code'], 404);
    $date = date('Y-m-d');
    Database::query(
        "INSERT INTO attendance (school_id, course_id, student_id, date, status, recorded_by)
         VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE status = VALUES(status)",
        [$u['school_id'], $room['course_id'], $u['id'], $date, 'present', (int)$room['created_by']]);
    api_out(['ok' => true, 'message' => 'Attendance recorded.']);
}
api_out(['ok' => false, 'error' => 'forbidden'], 403);
