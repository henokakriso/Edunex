<?php
/**
 * Users API: list users (role-filterable)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
$role = (string)($_GET['role'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));
$limit = min(200, max(1, (int)($_GET['limit'] ?? 50)));

$where = "role != 'guest'";
$args = [];
if ($u['role'] !== 'sysadmin') {
    $where .= " AND school_id = ?";
    $args[] = $u['school_id'];
}
if ($role !== '' && in_array($role, ['admin', 'teacher', 'student', 'parent'], true)) {
    $where .= " AND role = ?";
    $args[] = $role;
}
if ($q !== '') {
    $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_id LIKE ?)";
    $like = '%' . $q . '%';
    array_push($args, $like, $like, $like, $like);
}
$users = Database::all("SELECT id, first_name, last_name, email, phone, role, student_id, status, xp, level, last_login FROM users WHERE $where ORDER BY last_name LIMIT $limit", $args);
api_out(['ok' => true, 'count' => count($users), 'users' => array_map(function ($x) {
    $x['id'] = (int)$x['id'];
    $x['xp'] = (int)$x['xp'];
    $x['level'] = (int)$x['level'];
    return $x;
}, $users)]);
