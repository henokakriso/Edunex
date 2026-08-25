<?php
/**
 * Profile API: public profile data for the profile drawer
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) api_out(['ok' => false, 'error' => 'missing id'], 400);

$p = Database::one(
    "SELECT id, first_name, last_name, role, student_id, school_id, status,
            xp, level, bio, created_at, last_login
     FROM users WHERE id = ? AND status = 'active'", [$id]);
if (!$p) api_out(['ok' => false, 'error' => 'not found'], 404);

// Non-admins may only view profiles within their own school (or their own)
if ($u['role'] !== 'ministry' && (int)$u['id'] !== $id
    && (int)($u['school_id'] ?? 0) !== (int)($p['school_id'] ?? -1)) {
    api_out(['ok' => false, 'error' => 'access denied'], 403);
}

$school = null;
if (!empty($p['school_id'])) {
    $s = Database::one("SELECT name, type, city, address FROM schools WHERE id = ? AND status = 'active'", [$p['school_id']]);
    if ($s) $school = ['name' => $s['name'], 'type' => $s['type'], 'city' => $s['city'], 'address' => $s['address']];
}

$courses = [];
if ($p['role'] === 'teacher') {
    $courses = Database::all(
        "SELECT c.title, c.level, s.name AS subject
         FROM courses c LEFT JOIN subjects s ON s.id = c.subject_id
         WHERE c.teacher_id = ? AND c.status = 'published' AND c.school_id = ?
         ORDER BY c.title LIMIT 40", [$id, $p['school_id']]);
} elseif ($p['role'] === 'student') {
    $courses = Database::all(
        "SELECT c.title, c.level, s.name AS subject
         FROM courses c LEFT JOIN subjects s ON s.id = c.subject_id
         JOIN course_enrollments ce ON ce.course_id = c.id
         WHERE ce.user_id = ? AND c.status = 'published'
         ORDER BY c.title LIMIT 40", [$id]);
}

api_out(['ok' => true, 'profile' => [
    'id' => (int)$p['id'],
    'name' => $p['first_name'] . ' ' . $p['last_name'],
    'role' => ucfirst($p['role']),
    'role_key' => $p['role'],
    'student_id' => $p['role'] === 'student' ? $p['student_id'] : null,
    'avatar' => avatar_url($p),
    'initials' => initials($p),
    'bio' => $p['bio'] ?? '',
    'xp' => (int)$p['xp'],
    'level' => (int)$p['level'],
    'member_since' => date('M Y', strtotime($p['created_at'])),
    'last_login' => $p['last_login'] ?? null,
    'school' => $school,
    'courses' => $courses,
]]);
