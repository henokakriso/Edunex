<?php
/**
 * EDUNEX Authentication & Session management
 */
class Auth {

    /** Start session with secure settings */
    public static function start(): void {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name(SESSION_NAME);
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => $isSecure,
        ]);
        session_start();
        if (empty($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > SESSION_LIFETIME) {
            self::destroy();
            session_start();
            $_SESSION['created'] = time();
        }
        self::refreshUser();
        self::handleRememberMe();
    }

    /** Reload user from DB into session */
    public static function refreshUser(): void {
        if (empty($_SESSION['user']['id'])) return;
        $u = Database::one("SELECT * FROM users WHERE id = ? AND status = 'active'", [$_SESSION['user']['id']]);
        if (!$u) { self::destroy(); return; }
        unset($u['password_hash'], $u['twofa_secret']);
        if ((int)($_SESSION['sv'] ?? 0) !== (int)$u['session_version']) {
            self::destroy();
            return;
        }
        $_SESSION['user'] = $u;
        $_SESSION['sv'] = (int)$u['session_version'];
    }

    /** Authenticate with identifier (student id / email / phone) + password */
    public static function attempt(string $identifier, string $password, bool $remember): array {
        $identifier = trim($identifier);
        $u = Database::one(
            "SELECT * FROM users WHERE (email = ? OR phone = ? OR student_id = ?) AND status != 'banned' LIMIT 1",
            [$identifier, $identifier, $identifier]
        );
        if (!$u) return [false, 'No account found with that student ID, email or phone.'];
        if ($u['status'] === 'suspended') return [false, 'This account has been suspended. Contact your administrator.'];
        if ($u['status'] === 'pending') return [false, 'Your account is awaiting verification by your homeroom teacher (within 24 hours).'];
        if (!password_verify($password, $u['password_hash'])) {
            Database::insert('login_history', ['user_id' => $u['id'], 'status' => 'failed', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
            return [false, 'Incorrect password.'];
        }
        if ($u['role'] === 'student') {
            $lvl = (string)Database::scalar(
                "SELECT sc.education_level FROM users us JOIN schools sc ON sc.id = us.school_id WHERE us.id = ?",
                [(int)$u['id']], 'secondary');
            if ($lvl === 'kg') {
                return [false, 'Kindergarten students use the parent or teacher portal. Ask your teacher or parent to sign in.'];
            }
        }
        if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
            Database::update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [$u['id']]);
        }
        Database::insert('login_history', ['user_id' => $u['id'], 'status' => 'success', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '']);

        // 2FA checkpoint
        if (!empty($u['twofa_enabled']) && $u['twofa_enabled']) {
            $_SESSION['twofa_pending'] = $u['id'];
            return [true, '2FA'];
        }
        self::login($u, $remember);
        return [true, 'OK'];
    }

    /** Complete session */
    public static function login(array $u, bool $remember): void {
        session_regenerate_id(true);
        unset($u['password_hash'], $u['twofa_secret']);
        $_SESSION['user'] = $u;
        $_SESSION['sv'] = (int)($u['session_version'] ?? 0);
        unset($_SESSION['twofa_pending']);
        Database::update('users', ['last_login' => date('Y-m-d H:i:s'), 'status' => 'active'], 'id = ?', [$u['id']]);
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $selector = bin2hex(random_bytes(12));
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
            Database::insert('sessions', [
                'user_id' => $u['id'], 'selector' => $selector,
                'token_hash' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + REMEMBER_ME_DAYS * 86400),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);
            setcookie('remember', $selector . ':' . $token, time() + REMEMBER_ME_DAYS * 86400, '/', '', $isSecure, true);
        }
        log_activity('login', 'Signed in as ' . $u['role'], $u['id']);
    }

    /** Verify 2FA code (checkpoint) */
    public static function verify_twofa(string $code): array {
        $uid = $_SESSION['twofa_pending'] ?? null;
        if (!$uid) return [false, 'Session expired. Please log in again.'];
        $u = Database::one("SELECT * FROM users WHERE id = ?", [$uid]);
        if (!$u || empty($u['twofa_secret'])) return [false, 'Invalid 2FA configuration.'];
        if (!self::totpVerify($u['twofa_secret'], trim($code))) return [false, 'Invalid verification code.'];
        self::login($u, false);
        return [true, 'OK'];
    }

    /**
     * Verify 2FA USB key at the checkpoint. On success rotates the key,
     * completes the login and returns the replacement .hena file bytes.
     */
    public static function verify_twofa_hena(string $uploaded): array {
        $uid = $_SESSION['twofa_pending'] ?? null;
        if (!$uid) return [false, 'Session expired. Please log in again.', ''];
        $u = Database::one("SELECT * FROM users WHERE id = ?", [$uid]);
        if (!$u || empty($u['twofa_secret'])) return [false, 'Invalid 2FA configuration.', ''];
        [$ok, $msg, $file] = self::henaVerifyAndRotate($uid, $uploaded);
        if (!$ok) return [false, $msg, ''];
        self::login($u, false);
        return [true, $msg, $file];
    }

    /** TOTP (RFC 6238) — generate secret, verify code */
    public static function totpSecret(): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $s = '';
        for ($i = 0; $i < 32; $i++) $s .= $chars[random_int(0, 31)];
        return $s;
    }
    public static function totpVerify(string $secret, string $code): bool {
        if (!is_numeric($code) || strlen($code) !== 6) return false;
        $base32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split(strtoupper($secret)) as $c) {
            $v = strpos($base32, $c);
            if ($v === false) return false;
            $bits .= str_pad(decbin($v), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($bits, 8) as $b) {
            if (strlen($b) === 8) $bytes .= chr(bindec($b));
        }
        $key = $bytes;
        for ($i = -1; $i <= 1; $i++) {
            $time = floor(time() / 30) + $i;
            $packed = pack('N*', 0) . pack('N*', $time);
            $hash = hash_hmac('sha1', $packed, $key, true);
            $offset = ord($hash[19]) & 0x0F;
            $val = ((ord($hash[$offset]) & 0x7F) << 24) | ((ord($hash[$offset+1]) & 0xFF) << 16) | ((ord($hash[$offset+2]) & 0xFF) << 8) | (ord($hash[$offset+3]) & 0xFF);
            $t = str_pad((string)($val % 1000000), 6, '0', STR_PAD_LEFT);
            if (hash_equals($t, $code)) return true;
        }
        return false;
    }

    /** Remember me cookie login */
    public static function handleRememberMe(): void {
        if (!empty($_SESSION['user'])) return;
        if (empty($_COOKIE['remember'])) return;
        [$selector, $token] = array_pad(explode(':', $_COOKIE['remember'], 2), 2, '');
        $row = Database::one("SELECT s.*, u.* FROM sessions s JOIN users u ON u.id = s.user_id WHERE s.selector = ? AND s.expires_at > NOW() AND u.status = 'active'", [$selector]);
        if ($row && hash_equals($row['token_hash'], hash('sha256', $token))) {
            // Verify IP and User-Agent match (if stored)
            $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
            $currentUa = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if (!empty($row['ip']) && $row['ip'] !== $currentIp) {
                setcookie('remember', '', time() - 3600, '/');
                return;
            }
            if (!empty($row['user_agent']) && $row['user_agent'] !== $currentUa) {
                setcookie('remember', '', time() - 3600, '/');
                return;
            }
            self::login($row, true);
        } else {
            setcookie('remember', '', time() - 3600, '/');
        }
    }

    /** Logout (current + optionally all sessions) */
    public static function logout(bool $allDevices = false): void {
        $uid = $_SESSION['user']['id'] ?? null;
        if ($uid) {
            if ($allDevices) {
                Database::delete('sessions', 'user_id = ?', [$uid]);
            } else {
                $selector = $_COOKIE['remember'] ? explode(':', $_COOKIE['remember'])[0] : '';
                if ($selector) Database::delete('sessions', 'user_id = ? AND selector = ?', [$uid, $selector]);
            }
        }
        self::destroy();
        setcookie('remember', '', time() - 3600, '/');
    }

    public static function destroy(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** Invalidate all sessions for a user (call on password change) */
    public static function invalidateSessions(int $userId): void {
        Database::update('users', ['session_version' => new \PDOStatement('session_version + 1')], 'id = ?', [$userId]);
        Database::delete('sessions', 'user_id = ?', [$userId]);
    }

    /** Increment session version (forces re-login) */
    public static function bumpSessionVersion(int $userId): void {
        Database::run("UPDATE users SET session_version = session_version + 1 WHERE id = ?", [$userId]);
    }

    /** Password policy check */
    public static function password_ok(string $p): array {
        if (mb_strlen($p) < PASSWORD_MIN_LEN) return [false, 'Password must be at least ' . PASSWORD_MIN_LEN . ' characters.'];
        if (!preg_match('/[A-Z]/', $p)) return [false, 'Password needs at least one uppercase letter.'];
        if (!preg_match('/[a-z]/', $p)) return [false, 'Password needs at least one lowercase letter.'];
        if (!preg_match('/[0-9]/', $p)) return [false, 'Password needs at least one number.'];
        if (!preg_match('/[^A-Za-z0-9]/', $p)) return [false, 'Password needs at least one special character (!@#$%^&* etc).'];
        return [true, ''];
    }

    /* ============================================================
       USB .hena two-factor keys
       An encrypted single-use file (.hena) stored on a USB stick.
       On each successful login the token rotates and a new file is
       downloaded to replace the one on the USB.
       ============================================================ */

    private const HENA_MAGIC = 'EDUNEXHENA';

    /** Encryption key for .hena payloads (AES-256-GCM, openssl). */
    private static function henaKey(): string { return hash('sha256', HENA_SECRET, true); }

    /** Build a fresh rotation token (unique random hash). */
    private static function henaNew(int $uid, int $counter): array {
        return ['u' => $uid, 'c' => $counter, 't' => bin2hex(random_bytes(32)), 'i' => time()];
    }

    /** Encrypt payload -> store-able blob (base64 iv.tag.ct). */
    private static function henaEncode(array $payload): string {
        $iv = random_bytes(12);
        $tag = '';
        $ct = openssl_encrypt(json_encode($payload), 'aes-256-gcm', self::henaKey(), OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        return base64_encode($iv) . '.' . base64_encode($tag) . '.' . base64_encode($ct);
    }

    /** Decrypt a .hena blob -> payload or null on any failure */
    public static function henaDecode(string $blob): ?array {
        $parts = explode('.', $blob);
        if (count($parts) !== 3) return null;
        list($biv, $btag, $bct) = $parts;
        $iv = base64_decode($biv); $tag = base64_decode($btag); $ct = base64_decode($bct);
        if ($iv === false || $tag === false || $ct === false) return null;
        $pt = openssl_decrypt($ct, 'aes-256-gcm', self::henaKey(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($pt === false) return null;
        $d = json_decode($pt, true);
        return is_array($d) ? $d : null;
    }

    /** Serialise a .hena file for the user to store on USB. */
    private static function henaFile(array $payload): string {
        return self::HENA_MAGIC . "\n" . self::henaEncode($payload) . "\n";
    }

    /** Parse & decrypt an uploaded .hena file -> payload or null */
    private static function henaParseFile(string $content): ?array {
        $content = trim(str_replace(["\r\n", "\r"], "\n", $content));
        $lines = explode("\n", $content);
        if (($lines[0] ?? '') !== self::HENA_MAGIC) return null;
        return self::henaDecode(trim($lines[1] ?? ''));
    }

    /**
     * Enable USB 2FA: rotate a fresh key, persist it, return the .hena file.
     */
    public static function henaIssue(int $uid): string {
        $payload = self::henaNew($uid, 1);
        $blob = self::henaEncode($payload);
        Database::update('users', ['twofa_secret' => $blob, 'twofa_enabled' => 1, 'hena_counter' => 1], 'id = ?', [$uid]);
        self::refreshUser(); // drop secret from active session
        return self::henaFile($payload);
    }

    /**
     * Verify an uploaded .a file, rotate the key, persist the new blob,
     * and return the replacement .a file bytes for the user to re-save.
     */
    public static function henaVerifyAndRotate(int $uid, string $uploaded): array {
        $u = Database::one("SELECT twofa_secret, hena_counter FROM users WHERE id = ?", [$uid]);
        if (!$u || empty($u['twofa_secret'])) return [false, '2FA is not enabled for this account.'];
        $in = self::henaParseFile($uploaded);
        if (!$in) return [false, 'This file is not a valid Edunex .hena key.', ''];
        $stored = self::henaDecode($u['twofa_secret']);
        if (!$stored) { self::henaReset($uid); return [false, 'Stored key is corrupt — re-enable 2FA.', '']; }
        if ((int)$stored['u'] !== (int)$uid || (int)$in['u'] !== (int)$uid) return [false, 'This key does not belong to your account.', ''];
        if ((int)$in['c'] !== (int)$stored['c'] || !hash_equals((string)$stored['t'], (string)$in['t'])) {
            return [false, 'Stale key file — rotate it by logging in from a current session, or re-enable 2FA.', ''];
        }
        // Rotate: new counter + new token
        $next = (int)$stored['c'] + 1;
        $payload = self::henaNew($uid, $next);
        Database::update('users', ['twofa_secret' => self::henaEncode($payload), 'hena_counter' => $next], 'id = ?', [$uid]);
        return [true, 'Identity verified.', self::henaFile($payload)];
    }

    /** Decide whether the stored key is a USB .hena or legacy TOTP secret. */
    public static function henaEnabled(int $uid): bool {
        $u = Database::one("SELECT twofa_secret, twofa_enabled FROM users WHERE id = ?", [$uid]);
        if (!$u || !(int)$u['twofa_enabled']) return false;
        $d = self::henaDecode($u['twofa_secret'] ?? '');
        return $d !== null && (int)($d['u'] ?? 0) === $uid;
    }

    /** Reset 2FA to disabled (e.g. DB state error or user recovery). */
    public static function henaReset(int $uid): void {
        Database::update('users', ['twofa_secret' => '', 'twofa_enabled' => 0, 'hena_counter' => 0], 'id = ?', [$uid]);
    }
}
