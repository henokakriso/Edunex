<?php
/**
 * Attendance mobile/desktop API: bulk mark + list by course/date
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();

if ($u['role'] === 'student') {
    $rows = Database::all(
        "SELECT a.id, a.date, a.status, a.note, c.title AS course_title
         FROM attendance a JOIN courses c ON c.id = a.course_id
         WHERE a.student_id = ? ORDER BY a.date DESC LIMIT 60", [$u['id']]);
    api_out(['ok' => true, 'count' => count($rows), 'records' => $rows]);
}

if ($u['role'] === 'teacher') {
    $courseId = (int)($_GET['course_id'] ?? 0);
    $date = (string)($_GET['date'] ?? date('Y-m-d'));
    if ($courseId) {
        $rows = Database::all(
            "SELECT a.*, u.first_name, u.last_name, u.student_id
             FROM attendance a JOIN users u ON u.id = a.student_id
             WHERE a.course_id = ? AND a.date = ?", [$courseId, $date]);
        api_out(['ok' => true, 'date' => $date, 'count' => count($rows), 'records' => $rows]);
    }
    $courses = Database::all("SELECT id, title FROM courses WHERE teacher_id = ?", [$u['id']]);
    api_out(['ok' => true, 'courses' => $courses]);
}
api_out(['ok' => false, 'error' => 'forbidden'], 403);
