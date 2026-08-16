<?php
/**
 * Courses API: list courses, enroll (student)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($u['role'] !== 'student') api_out(['ok' => false, 'error' => 'forbidden'], 403);
    $in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $cid = (int)($in['course_id'] ?? 0);
    $exists = Database::one("SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?", [$u['id'], $cid]);
    if ($exists) api_out(['ok' => true, 'already' => true]);
    Database::insert('course_enrollments', ['user_id' => $u['id'], 'course_id' => $cid, 'progress' => 0]);
    award_xp((int)$u['id'], 20, 'Enrolled in course #' . $cid);
    api_out(['ok' => true]);
}

$where = "c.school_id = ?";
$args = [$u['school_id']];
if ($u['role'] === 'student') $where .= " AND c.status = 'published'";
$courses = Database::all(
    "SELECT c.id, c.title, c.code, c.description, c.image, c.status,
            u.first_name AS teacher_first, u.last_name AS teacher_last,
            (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students,
            (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lessons
     FROM courses c JOIN users u ON u.id = c.teacher_id
     WHERE $where ORDER BY c.created_at DESC LIMIT 200", $args);
api_out(['ok' => true, 'count' => count($courses), 'courses' => $courses]);
