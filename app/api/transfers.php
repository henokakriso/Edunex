<?php
/**
 * Transfers API: request transfer (student), list/approve (admin)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();

if ($u['role'] === 'student' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $toSchoolId = (int)($in['to_school_id'] ?? 0);
    $reason = trim((string)($in['reason'] ?? ''));
    $code = trim((string)($in['referral_code'] ?? ''));
    $school = $toSchoolId ? Database::one("SELECT * FROM schools WHERE id = ?", [$toSchoolId]) : null;
    if (!$school) api_out(['ok' => false, 'error' => 'school_not_found'], 404);
    $ref = null;
    if ($code !== '') {
        $ref = Database::one("SELECT * FROM transfer_codes WHERE code = ? AND used = 0 AND (expires_at IS NULL OR expires_at > NOW())", [$code]);
        if (!$ref) api_out(['ok' => false, 'error' => 'invalid_referral'], 400);
    }
    Database::insert('transfer_requests', [
        'student_id' => $u['id'], 'from_school_id' => $u['school_id'], 'to_school_id' => $toSchoolId,
        'referral_code' => $code, 'reason' => $reason, 'status' => 'pending',
    ]);
    $rid = Database::insertId();
    if ($ref) {
        Database::update('transfer_codes', ['used' => 1, 'student_id' => $u['id']], 'id = ?', [$ref['id']]);
        Database::query("UPDATE transfer_requests SET status = 'approved' WHERE id = ?", [$rid]);
        api_out(['ok' => true, 'request_id' => $rid, 'auto_approved' => true]);
    }
    $admins = Database::all("SELECT id FROM users WHERE role = 'ministry'");
    foreach ($admins as $a) notify((int)$a['id'], 'system', 'New transfer request', $u['student_id'] . ' wants to transfer', 'admin/transfers');
    api_out(['ok' => true, 'request_id' => $rid, 'auto_approved' => false]);
}

if ($u['role'] === 'student') {
    $reqs = Database::all(
        "SELECT t.*, s.name AS to_school FROM transfer_requests t JOIN schools s ON s.id = t.to_school_id
         WHERE t.student_id = ? ORDER BY t.created_at DESC", [$u['id']]);
    api_out(['ok' => true, 'requests' => $reqs]);
}

if ($u['role'] === 'ministry' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $rid = (int)($in['request_id'] ?? 0);
    $action = (string)($in['action'] ?? 'approve');
    $r = Database::one("SELECT * FROM transfer_requests WHERE id = ?", [$rid]);
    if (!$r) api_out(['ok' => false, 'error' => 'not_found'], 404);
    if ($action === 'approve') {
        // Move the student to the new school, keep student_id (transferable special ID)
        Database::update('users', ['school_id' => (int)$r['to_school_id']], 'id = ?', [$r['student_id']]);
        Database::update('transfer_requests', ['status' => 'approved', 'approved_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$rid]);
        notify((int)$r['student_id'], 'system', 'Transfer approved', 'You have been moved to the new school.', 'dashboard');
    } else {
        Database::update('transfer_requests', ['status' => 'rejected', 'approved_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$rid]);
        notify((int)$r['student_id'], 'system', 'Transfer rejected', 'Your transfer request was declined.', 'dashboard');
    }
    api_out(['ok' => true]);
}

if ($u['role'] === 'ministry') {
    $reqs = Database::all(
        "SELECT t.*, s.name AS to_school, fs.name AS from_school, st.first_name, st.last_name, st.student_id
         FROM transfer_requests t JOIN schools s ON s.id = t.to_school_id JOIN schools fs ON fs.id = t.from_school_id
         JOIN users st ON st.id = t.student_id WHERE t.status = 'pending' ORDER BY t.created_at DESC", []);
    api_out(['ok' => true, 'requests' => $reqs]);
}

api_out(['ok' => false, 'error' => 'forbidden'], 403);
