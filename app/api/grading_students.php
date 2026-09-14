<?php
/**
 * API: Course student management
 * POST: action=enroll|remove, course_id, student_id
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

$action = $_POST['action'] ?? '';
$courseId = (int)($_POST['course_id'] ?? 0);
$studentId = (int)($_POST['student_id'] ?? 0);

if ($courseId <= 0 || $studentId <= 0 || $action !== 'enroll') {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$course = Database::one("SELECT id, teacher_id, title FROM courses WHERE id = ?", [$courseId]);
if (!$course || (int)$course['teacher_id'] !== $uid) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$student = Database::one("SELECT id, first_name, last_name, role FROM users WHERE id = ? AND role = 'student'", [$studentId]);
if (!$student) {
    echo json_encode(['error' => 'Student not found']);
    exit;
}

if ($action === 'enroll') {
    $existing = Database::one(
        "SELECT id FROM course_enrollments WHERE course_id = ? AND user_id = ?",
        [$courseId, $studentId]);
    if ($existing) {
        echo json_encode(['error' => 'Already enrolled']);
        exit;
    }
    // Store class_id from course
    $classId = (int)($course['class_id'] ?? 0);
    Database::insert('course_enrollments', [
        'course_id' => $courseId,
        'user_id' => $studentId,
        'class_id' => $classId ?: null,
        'enrolled_at' => date('Y-m-d H:i:s')
    ]);
    echo json_encode(['ok' => true, 'message' => $student['first_name'] . ' enrolled']);
}
exit;
