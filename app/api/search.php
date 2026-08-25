<?php
/**
 * Search API: students, teachers, schools, courses, subjects, departments,
 * files, messages, announcements. Returns route+icon for the live dropdown.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) < 2) api_out(['ok' => true, 'q' => $q, 'results' => []]);
$like = '%' . $q . '%';
$isAdmin = in_array($u['role'] ?? '', ['ministry'], true);
$sid = $isAdmin ? null : ($u['school_id'] ?? null);

$out = [];

// Students, teachers & parents (users). Non-admins search their own school first,
// but can also find users at other schools so cross-school messaging works.
$like = '%' . $q . '%';
$sid = $u['school_id'] ?? null;
if ($sid !== null) {
    $users = Database::all(
        "SELECT id, first_name, last_name, role, student_id,
                (CASE WHEN school_id = ? THEN 1 ELSE 0 END) AS same_school
         FROM users
         WHERE role IN ('student','teacher','parent') AND status='active'
           AND (first_name LIKE ? OR last_name LIKE ? OR student_id LIKE ?)
         ORDER BY same_school DESC, last_name LIMIT 12",
        [$sid, $like, $like, $like]);
} else {
    $users = Database::all(
        "SELECT id, first_name, last_name, role, student_id, 1 AS same_school FROM users
         WHERE role IN ('student','teacher','parent') AND status='active'
           AND (first_name LIKE ? OR last_name LIKE ? OR student_id LIKE ?) LIMIT 12",
        [$like, $like, $like]);
}
foreach ($users as $usr) {
    $sub = $usr['role'] . ($usr['student_id'] ? ' · ' . $usr['student_id'] : '');
    if (!$usr['same_school']) $sub .= ' · other school';
    $route = ($u['role'] ?? '') === 'ministry' && $usr['role'] === 'student'
        ? 'admin/user&id=' . $usr['id']
        : 'messages&to=' . $usr['id'];
    $out[] = [
        'type' => $usr['role'], 'id' => (int)$usr['id'],
        'title' => $usr['first_name'] . ' ' . $usr['last_name'],
        'subtitle' => $sub,
        'route' => $route, 'icon' => $usr['role'] === 'student' ? 'users-card' : 'user',
    ];
}

// Schools (admin only)
if ($isAdmin) {
    $schools = Database::all("SELECT id, name, code, city FROM schools WHERE name LIKE ? OR code LIKE ? LIMIT 6", [$like, $like]);
    foreach ($schools as $s) $out[] = ['type' => 'school', 'id' => (int)$s['id'], 'title' => $s['name'], 'subtitle' => $s['code'] . ($s['city'] ? ' · ' . $s['city'] : ''), 'route' => 'admin/school&id=' . $s['id'], 'icon' => 'school'];
}

// Courses
$courses = $sid !== null
    ? Database::all("SELECT id, title, code FROM courses WHERE school_id = ? AND (title LIKE ? OR code LIKE ?) AND status = 'published' LIMIT 8", [$sid, $like, $like])
    : Database::all("SELECT id, title, code FROM courses WHERE (title LIKE ? OR code LIKE ?) AND status = 'published' LIMIT 8", [$like, $like]);
foreach ($courses as $c) $out[] = ['type' => 'course', 'id' => (int)$c['id'], 'title' => $c['title'], 'subtitle' => $c['code'] ?? '', 'route' => 'courses/view&id=' . $c['id'], 'icon' => 'books'];

// Subjects
$subjects = $sid !== null
    ? Database::all("SELECT id, name, code FROM subjects WHERE school_id = ? AND (name LIKE ? OR code LIKE ?) LIMIT 6", [$sid, $like, $like])
    : Database::all("SELECT id, name, code FROM subjects WHERE name LIKE ? OR code LIKE ? LIMIT 6", [$like, $like]);
foreach ($subjects as $s) $out[] = ['type' => 'subject', 'id' => (int)$s['id'], 'title' => $s['name'], 'subtitle' => $s['code'] ?? '', 'route' => 'admin/subjects', 'icon' => 'tag'];

// Departments
$depts = $sid !== null
    ? Database::all("SELECT id, name FROM departments WHERE school_id = ? AND name LIKE ? LIMIT 6", [$sid, $like])
    : Database::all("SELECT id, name FROM departments WHERE name LIKE ? LIMIT 6", [$like]);
foreach ($depts as $d) $out[] = ['type' => 'department', 'id' => (int)$d['id'], 'title' => $d['name'], 'subtitle' => 'Department', 'route' => 'admin/departments', 'icon' => 'folder'];

// Files (own + school shared, non-deleted)
$files = $sid !== null
    ? Database::all("SELECT id, name, original_name, is_folder FROM files WHERE school_id = ? AND deleted_at IS NULL AND name LIKE ? LIMIT 6", [$sid, $like])
    : Database::all("SELECT id, name, original_name, is_folder FROM files WHERE deleted_at IS NULL AND name LIKE ? LIMIT 6", [$like]);
foreach ($files as $f) $out[] = ['type' => 'file', 'id' => (int)$f['id'], 'title' => $f['original_name'] ?: $f['name'], 'subtitle' => $f['is_folder'] ? 'Folder' : 'File', 'route' => $f['is_folder'] ? 'files&folder=' . $f['id'] : 'files/view&id=' . $f['id'], 'icon' => $f['is_folder'] ? 'folder' : 'file'];

// Messages: matching conversation titles / message bodies
$cids = Database::all(
    "SELECT DISTINCT cm.conversation_id FROM conversation_members cm WHERE cm.user_id = ? LIMIT 200", [$u['id']]);
if ($cids) {
    $ids = array_column($cids, 'conversation_id');
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $msgRows = Database::all(
        "SELECT DISTINCT c.id, c.title FROM conversations c JOIN messages m ON m.conversation_id = c.id
         WHERE c.id IN ($ph) AND m.body LIKE ? LIMIT 6", array_merge($ids, [$like]));
    foreach ($msgRows as $m) $out[] = ['type' => 'message', 'id' => (int)$m['id'], 'title' => $m['title'] ?: 'Conversation', 'subtitle' => 'Message', 'route' => 'messages&conv=' . $m['id'], 'icon' => 'chat'];
}

// Announcements
$anns = $sid !== null
    ? Database::all("SELECT id, title, created_at FROM announcements WHERE school_id = ? AND title LIKE ? LIMIT 6", [$sid, $like])
    : Database::all("SELECT id, title, created_at FROM announcements WHERE title LIKE ? LIMIT 6", [$like]);
foreach ($anns as $a) $out[] = ['type' => 'announcement', 'id' => (int)$a['id'], 'title' => $a['title'], 'subtitle' => 'Announcement · ' . date('M j', strtotime($a['created_at'])), 'route' => 'communication/announcements', 'icon' => 'megaphone'];

api_out(['ok' => true, 'q' => $q, 'results' => array_slice($out, 0, 20)]);
