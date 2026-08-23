<?php
/**
 * Shared API auth: JSON helpers + token auth for the C desktop app.
 * API endpoints should set header('Content-Type: application/json') first,
 * then require this file.
 */

// API security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

function api_out(array $data, int $code = 200): never {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function api_user(): array {
    // Browser fetches carry the session; the C desktop client carries a Bearer token.
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $u = me();
        if (!$u) api_out(['ok' => false, 'error' => 'auth_required'], 401);
        return $u;
    }
    $token = trim((string)$_SERVER['HTTP_AUTHORIZATION']);
    if (str_starts_with($token, 'Bearer ')) $token = substr($token, 7);
    $payload = api_verify_token($token);
    if (!$payload) api_out(['ok' => false, 'error' => 'invalid_token'], 401);
    $u = Database::one("SELECT * FROM users WHERE id = ? AND status = 'active'", [(int)$payload['uid']]);
    if (!$u) api_out(['ok' => false, 'error' => 'user_not_found'], 401);
    $u['api_token'] = $token;
    return $u;
}

function api_issue_token(int $userId, int $ttlSeconds = 86400): string {
    $exp = time() + $ttlSeconds;
    $payload = $userId . '.' . $exp;
    $sig = hash_hmac('sha256', $payload, API_SECRET);
    return base64url_encode($payload . '.' . $sig);
}

function api_verify_token(string $token): ?array {
    $raw = base64url_decode($token);
    if ($raw === false) return null;
    $parts = explode('.', $raw);
    if (count($parts) !== 3) return null;
    [$uid, $exp, $sig] = $parts;
    if ((int)$exp < time()) return null;
    if (!hash_equals(hash_hmac('sha256', $uid . '.' . $exp, API_SECRET), $sig)) return null;
    return ['uid' => (int)$uid, 'exp' => (int)$exp];
}

function base64url_encode(string $s): string {
    return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
}

function base64url_decode(string $s): string|false {
    $t = strtr($s, '-_', '+/');
    return base64_decode($t . str_repeat('=', (4 - strlen($t) % 4) % 4));
}

/**
 * Rate limit an API endpoint. Call after api_user().
 * $max = max requests per $windowSec seconds. Default: 60 req/min.
 */
function api_rate_limit(array $user, string $endpoint = 'default', int $max = 60, int $windowSec = 60): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = "api:{$endpoint}:{$ip}:{$user['id']}";
    if (rate_limit_blocked($key, $max, $windowSec)) {
        api_out(['ok' => false, 'error' => 'rate_limited', 'retry_after' => $windowSec], 429);
    }
    rate_limit($key, $max, $windowSec);
}
