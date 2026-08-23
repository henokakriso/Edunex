<?php
/**
 * API login (desktop app): POST identifier + password (+ otp) -> api token
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_out(['ok' => false, 'error' => 'method'], 405);
$in = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$identifier = trim((string)($in['identifier'] ?? ''));

// Rate limit login attempts
$loginKey = 'api:login:' . ($_SERVER['REMOTE_ADDR'] ?? '');
if (rate_limit_blocked($loginKey, 10, 900)) {
    api_out(['ok' => false, 'error' => 'Too many failed attempts. Please wait 5 minutes.'], 429);
}
$password = (string)($in['password'] ?? '');

if ($identifier === '' || $password === '') api_out(['ok' => false, 'error' => 'missing_fields'], 400);

$u = Database::one("SELECT * FROM users WHERE (email = ? OR phone = ? OR student_id = ?) LIMIT 1", [$identifier, $identifier, $identifier]);
if (!$u || !password_verify($password, $u['password_hash'] ?? '')) {
    rate_limit($loginKey, 10, 900); // record failed attempt
    if ($u) Database::insert('login_history', ['user_id' => $u['id'], 'status' => 'failed', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'user_agent' => 'desktop-api']);
    api_out(['ok' => false, 'error' => 'invalid_credentials'], 401);
}
if ($u['status'] !== 'active') api_out(['ok' => false, 'error' => 'account_inactive'], 403);
if ($u['role'] === 'student') {
    $lvl = (string)Database::scalar(
        "SELECT sc.education_level FROM users us JOIN schools sc ON sc.id = us.school_id WHERE us.id = ?",
        [(int)$u['id']], 'secondary');
    if ($lvl === 'kg') api_out(['ok' => false, 'error' => 'kg_portal_only'], 403);
}

$recentFails = (int)Database::scalar("SELECT COUNT(*) FROM login_history WHERE user_id = ? AND status = 'failed' AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)", [$u['id']], 0);
if ($recentFails >= MAX_LOGIN_ATTEMPTS) api_out(['ok' => false, 'error' => 'locked', 'retry_after' => 900], 423);

// 2FA checkpoint
if (!empty($u['twofa_enabled']) && $u['twofa_enabled']) {
    $isHena = null !== Auth::henaDecode($u['twofa_secret'] ?? '');
    if ($isHena) {
        $file = $in['hena'] ?? '';
        if ($file === '') api_out(['ok' => false, 'error' => 'hena_required', 'hena' => true], 401);
        [$ok, $msg] = Auth::henaVerifyAndRotate((int)$u['id'], (string)$file);
        if (!$ok) api_out(['ok' => false, 'error' => 'invalid_hena', 'hena' => true], 401);
    } else {
        if (empty($in['otp']) || !Auth::totpVerify($u['twofa_secret'], (string)$in['otp'])) {
            api_out(['ok' => false, 'error' => 'otp_required', 'totp' => true], 401);
        }
    }
}

Database::update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$u['id']]);
Database::insert('login_history', ['user_id' => $u['id'], 'status' => 'success', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'user_agent' => 'desktop-api']);
$token = api_issue_token((int)$u['id']);
log_activity('api_login', 'Desktop app login: ' . $u['email'], (int)$u['id']);
api_out(['ok' => true, 'token' => $token, 'user' => [
    'id' => (int)$u['id'], 'first_name' => $u['first_name'], 'last_name' => $u['last_name'],
    'email' => $u['email'], 'role' => $u['role'], 'xp' => (int)$u['xp'], 'level' => (int)$u['level'],
    'student_id' => $u['student_id'] ?? '', 'school_id' => (int)$u['school_id'],
    'language' => $u['language'] ?? 'en',
]]);
