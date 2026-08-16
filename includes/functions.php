<?php
/**
 * EDUNEX Global helper functions
 */

/** HTML escape */
function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** URL builder */
function url(string $path = ''): string {
    if ($path === '') return APP_URL . '/';
    if ($path[0] === '/') return APP_URL . $path;                                  // absolute
    if (str_starts_with($path, 'index.php') || str_starts_with($path, 'public/')) return APP_URL . '/' . $path; // passthrough
    if (str_contains($path, '?')) {                                               // route with query string
        [$r, $qs] = explode('?', $path, 2);
        return APP_URL . '/index.php?r=' . $r . ($qs !== '' ? '&' . $qs : '');
    }
    return APP_URL . '/index.php?r=' . $path;                                      // route
}

/** Redirect */
function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

/** CSRF token */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

/** Verify CSRF on POST */
function csrf_verify(): void {
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)$sent)) {
        http_response_code(419);
        die('Invalid CSRF token. Please go back and try again.');
    }
}

/** Flash messages */
function flash(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}
function flash_drain(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/** Slug */
function slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

/** Get a site setting (cached) */
function setting(string $key, $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (Database::all("SELECT `key`, `value` FROM settings") as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    return $cache[$key] ?? $default;
}

/** Current user */
function me(): ?array {
    return $_SESSION['user'] ?? null;
}

/** Effective school_id for the current user (superadmins may have NULL) */
function my_school_id(): ?int {
    $u = me();
    if (!empty($u['school_id'])) return (int)$u['school_id'];
    if (($u['role'] ?? '') === 'admin') {
        $first = Database::scalar("SELECT MIN(id) FROM schools", [], null);
        return $first ? (int)$first : null;
    }
    return null;
}
function role(): ?string {
    return $_SESSION['user']['role'] ?? null;
}
function require_login(): array {
    if (empty($_SESSION['user'])) redirect('auth/login');
    return $_SESSION['user'];
}
function require_role(string ...$roles): array {
    $u = require_login();
    if (!in_array($u['role'], $roles, true)) {
        http_response_code(403);
        die('Access denied. Your role does not permit this action.');
    }
    return $u;
}

/** Post-login landing page for the current user's role */
function dashboard_path(): string {
    return match (me()['role'] ?? '') {
        'sysadmin' => 'admin/dashboard', 'admin' => 'regional/dashboard', 'director' => 'director/dashboard', 'teacher' => 'teacher/dashboard',
        'registrar' => 'registrar/dashboard', 'dean' => 'dean/dashboard', 'vice_dean' => 'vice_dean/dashboard', 'dept_head' => 'dept_head/dashboard',
        'parent' => 'parent/dashboard', 'student' => 'student/dashboard',
        default => 'landing'
    };
}

/** Simple logger */
function log_activity(string $action, string $detail = '', ?int $userId = null): void {
    try {
        Database::insert('activity_logs', [
            'user_id' => $userId ?? ($_SESSION['user']['id'] ?? null),
            'action'  => $action,
            'detail'  => mb_substr($detail, 0, 500),
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200),
        ]);
    } catch (Throwable $e) { /* never block main flow */ }
}

/** Send email (mail() or log mode) */
function send_mail(string $to, string $subject, string $body): bool {
    if (MAIL_MODE === 'log') {
        $line = sprintf("[%s] To: %s | Subject: %s\n%s\n%s\n\n",
            date('Y-m-d H:i:s'), $to, $subject, str_repeat('-', 60), $body);
        @file_put_contents(STORAGE_PATH . '/mail.log', $line, FILE_APPEND);
        return true;
    }
    $headers = "From: " . APP_NAME . " <" . MAIL_FROM . ">\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    return @mail($to, $subject, nl2br(e($body)), $headers);
}

/** Generate OTP code */
function make_otp(int $len = 6): string {
    $d = '';
    for ($i = 0; $i < $len; $i++) $d .= random_int(0, 9);
    return $d;
}

/** Rate limiter: true if currently blocked (does not record) */
function rate_limit_blocked(string $key, int $max, int $windowSec): bool {
    $file = STORAGE_PATH . '/rate/' . md5($key) . '.json';
    if (!is_file($file)) return false;
    $data = json_decode(file_get_contents($file), true) ?: [];
    if (($data['blocked_until'] ?? 0) > time()) return true;
    $t = array_values(array_filter($data['t'] ?? [], fn($x) => $x > time() - $windowSec));
    return count($t) >= $max;
}

/** Rate limiter: record one attempt and return false if over limit */
function rate_limit(string $key, int $max, int $windowSec, ?int &$remaining = null): bool {
    $file = STORAGE_PATH . '/rate/' . md5($key) . '.json';
    $data = ['t' => [], 'blocked_until' => 0];
    if (is_file($file)) $data = json_decode(file_get_contents($file), true) ?: $data;
    if ($data['blocked_until'] > time()) {
        $remaining = 0;
        return false;
    }
    $data['t'] = array_values(array_filter($data['t'], fn($x) => $x > time() - $windowSec));
    if (count($data['t']) >= $max) {
        $data['blocked_until'] = time() + 300;
        @file_put_contents($file, json_encode($data));
        $remaining = 0;
        return false;
    }
    $data['t'][] = time();
    $remaining = $max - count($data['t']);
    @file_put_contents($file, json_encode($data));
    return true;
}

/** Safe file upload: returns [ok, path|error] (also with named keys error/path/size) */
function upload_file(array $file, string $dir, array $allowedExt = null): array {
    $fail = function (string $msg): array { return [false, $msg, 'ok' => false, 'error' => $msg, 'path' => null, 'size' => 0]; };
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $why = $file['error'] === UPLOAD_ERR_INI_SIZE ? 'File exceeds the server upload limit'
            : ($file['error'] === UPLOAD_ERR_PARTIAL ? 'Upload was interrupted' : 'No file uploaded');
        return $fail($why);
    }
    if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) {
        return $fail('File exceeds ' . MAX_UPLOAD_MB . 'MB limit');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = $allowedExt ?? explode(',', ALLOWED_EXT);
    if (!in_array($ext, $allowed, true)) {
        return $fail('File type .' . $ext . ' is not allowed');
    }
    // verify real image
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        if (@getimagesize($file['tmp_name']) === false) return $fail('Invalid image file');
    }
    $targetDir = STORAGE_PATH . '/' . $dir;
    if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $name)) {
        return $fail('Failed to save file');
    }
    return [true, $dir . '/' . $name, 'ok' => true, 'error' => null, 'path' => $dir . '/' . $name, 'size' => (int)$file['size']];
}

/** Human readable bytes */
function human_bytes(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < 3) { $bytes /= 1024; $i++; }
    return round($bytes, 1) . ' ' . $units[$i];
}

/** Full user name */
function full_name(?array $u): string {
    return $u ? trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) : '—';
}

/** Avatar initials */
function initials(?array $u): string {
    if (!$u) return '?';
    return mb_strtoupper(mb_substr($u['first_name'] ?? '?', 0, 1)) . mb_strtoupper(mb_substr($u['last_name'] ?? '', 0, 1));
}

/** Role label */
function role_label(string $r): string {
    return match ($r) {
        'admin' => 'Administrator', 'teacher' => 'Teacher', 'registrar' => 'Registrar', 'dean' => 'Dean',
        'vice_dean' => 'Vice Dean', 'dept_head' => 'Department Head',
        'student' => 'Student', 'parent' => 'Parent', 'guest' => 'Guest',
        default => ucfirst($r)
    };
}

/** Time ago */
function time_ago(?string $dt): string {
    if (!$dt) return '—';
    $sec = time() - strtotime($dt);
    if ($sec < 60) return 'just now';
    if ($sec < 3600) return floor($sec / 60) . ' min ago';
    if ($sec < 86400) return floor($sec / 3600) . ' h ago';
    if ($sec < 2592000) return floor($sec / 86400) . ' days ago';
    return date('M j, Y', strtotime($dt));
}

/** Generate a national student identifier (ET-XXXXXXXX) if missing; return it. */
function ensure_national_id(int $userId): string {
    $u = Database::one("SELECT national_id FROM users WHERE id = ?", [$userId]);
    if ($u && !empty($u['national_id'])) return (string)$u['national_id'];
    do {
        $nid = 'ET-' . str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    } while (Database::one("SELECT id FROM users WHERE national_id = ?", [$nid]));
    Database::update('users', ['national_id' => $nid], 'id = ?', [$userId]);
    return $nid;
}

/** Record student entry into a school (lifelong education profile). */
function education_enter(int $studentId, int $schoolId, string $type = 'enrolled', string $notes = ''): void {
    $student = Database::one("SELECT u.first_name, u.last_name, s.name AS school_name, s.education_level FROM users u JOIN schools s ON s.id = u.school_id WHERE u.id = ?", [$studentId]);
    $schoolName = $student['school_name'] ?? '';
    Database::insert('education_history', [
        'student_id' => $studentId, 'school_id' => $schoolId, 'school_name' => $schoolName,
        'education_level' => $student['education_level'] ?? '',
        'entry_type' => $type, 'entered_at' => date('Y-m-d H:i:s'), 'notes' => $notes,
    ]);
}

/** Close a student's open record for a school (lifelong education profile). */
function education_leave(int $studentId, int $schoolId, string $type = 'transferred_out', string $notes = ''): void {
    Database::run(
        "UPDATE education_history SET left_at = ?, entry_type = ?, notes = CONCAT(notes, ?)
         WHERE student_id = ? AND school_id = ? AND left_at IS NULL",
        [date('Y-m-d H:i:s'), $type, $notes !== '' ? ' | ' . $notes : '', $studentId, $schoolId]
    );
}

/** Default module set for a school, by education level. */
function default_modules_for(string $level): array {
    $common = ['core', 'auth', 'user-management', 'security', 'backup', 'api', 'analytics', 'messaging', 'notifications',
               'teacher-portal', 'parent-portal', 'student-portal', 'academic', 'attendance', 'library', 'ai-tutor', 'gamification',
               'hr', 'payroll', 'recruitment', 'projects', 'documents', 'helpdesk', 'assets', 'fleet'];
    return match ($level) {
        'kg' => array_values(array_diff([...$common, 'kindergarten'], ['academic', 'student-portal', 'ai-tutor', 'gamification', 'attendance'])),
        'primary' => [...$common, 'primary', 'examination'],
        'secondary', 'preparatory' => [...$common, 'high-school', 'examination', 'certificate', 'lms', 'online-courses'],
        'university' => [...$common, 'university', 'admissions', 'registrar', 'dean', 'cgpa', 'research', 'thesis', 'examination', 'certificate', 'lms', 'online-courses'],
        default => [...$common, 'examination'],
    };
}

/** Is a module active for a school? If the school has no module rows, everything is active (backward compatible). */
function module_active(int $schoolId, string $key): bool {
    $row = Database::one("SELECT enabled FROM school_modules WHERE school_id = ? AND module_key = ?", [$schoolId, $key]);
    if ($row !== null) return (int)$row['enabled'] === 1;
    return Database::scalar("SELECT COUNT(*) FROM school_modules WHERE school_id = ?", [$schoolId], 0) === 0;
}

/** Install a school's default module set (idempotent). */
function ensure_school_modules(int $schoolId): void {
    $level = (string)Database::scalar("SELECT education_level FROM schools WHERE id = ?", [$schoolId], 'secondary');
    foreach (default_modules_for($level) as $key) {
        Database::run(
            "INSERT IGNORE INTO school_modules (school_id, module_key) VALUES (?, ?)",
            [$schoolId, $key]
        );
    }
}

/** Student capability tier from school level + grade group. */
function student_tier(int $userId): string {
    $row = Database::one(
        "SELECT sc.education_level AS level, g.grade FROM users u
         JOIN schools sc ON sc.id = u.school_id LEFT JOIN student_groups g ON g.id = u.group_id
         WHERE u.id = ?", [$userId]);
    $level = $row['level'] ?? 'secondary';
    if ($level === 'university' || $level === 'college') return $level;
    if ($level === 'kg') return 'kg';
    if ($level === 'primary') return 'primary';
    $g = (int)preg_replace('/\D+/', '', (string)($row['grade'] ?? ''));
    if ($g >= 1 && $g <= 4) return 'primary';
    if ($g >= 5 && $g <= 8) return 'middle';
    if ($level === 'preparatory') return 'secondary';
    return 'secondary';
}

/** Feature gate for the student portal by capability tier. */
function student_can(string $feature): bool {
    $t = student_tier((int)(me()['id'] ?? 0));
    $allowed = [
        'primary' => ['grades', 'homework', 'reading', 'games', 'ai-reader', 'ai-tutor'],
        'middle' => ['grades', 'homework', 'reading', 'games', 'ai-reader', 'assignments', 'ai-tutor', 'attendance', 'messages', 'materials', 'exams-simple', 'leaderboard'],
        'secondary' => ['grades', 'homework', 'reading', 'games', 'ai-reader', 'assignments', 'ai-tutor', 'attendance', 'messages', 'materials', 'exams', 'projects', 'research', 'career', 'certificates', 'leaderboard', 'transfers', 'schedule'],
        'university' => ['grades', 'homework', 'reading', 'games', 'ai-reader', 'assignments', 'ai-tutor', 'attendance', 'messages', 'materials', 'exams', 'projects', 'research', 'career', 'certificates', 'leaderboard', 'transfers', 'thesis', 'schedule'],
    ];
    return in_array($feature, $allowed[$t] ?? ['grades', 'homework', 'reading', 'games'], true);
}

/** 403 unless the logged-in student may use this feature. */
function require_student_feature(string $feature): void {
    if (!student_can($feature)) {
        http_response_code(403);
        die('This feature is not available at your education level.');
    }
}

/** DEMO data badge — marks seeded demo rows in ERP/portal views. */
function demo_badge(array $row): string {
    return !empty($row['is_demo'])
        ? '<span class="badge badge-warning" title="Seeded demo data">DEMO</span>'
        : '';
}

/** JSON response */
function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Notification helper */
function notify(int $userId, string $type, string $title, string $body = '', string $link = ''): void {
    Database::insert('notifications', [
        'user_id' => $userId, 'type' => $type, 'title' => $title,
        'body' => $body, 'link' => $link, 'read_at' => null,
    ]);
}

/** Award XP to user, handle level ups, badge checks, return xp event */
function award_xp(int $userId, int $amount, string $reason): void {
    $u = Database::one("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$u) return;
    $newXp = $u['xp'] + $amount;
    $level = floor(sqrt($newXp / 100)) + 1;
    $prevLevel = $u['level'];
    Database::update('users', ['xp' => $newXp, 'level' => $level, 'streak_last' => date('Y-m-d')], 'id = ?', [$userId]);
    // streak
    $today = date('Y-m-d');
    if ($u['streak_last'] === date('Y-m-d', strtotime('-1 day'))) {
        Database::run("UPDATE users SET streak = streak + 1 WHERE id = ?", [$userId]);
    } elseif ($u['streak_last'] !== $today) {
        Database::run("UPDATE users SET streak = 1 WHERE id = ?", [$userId]);
    }
    // badges
    $badges = Database::all("SELECT * FROM badges WHERE xp_required <= ? AND id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = ?)", [$newXp, $userId]);
    foreach ($badges as $b) {
        Database::insert('user_badges', ['user_id' => $userId, 'badge_id' => $b['id']]);
        notify($userId, 'achievement', 'Achievement unlocked: ' . $b['name'], $b['description'], 'gamification/badges');
    }
    if ($level > $prevLevel) {
        notify($userId, 'achievement', 'Level up! You reached level ' . $level, $reason, 'gamification');
    }
    log_activity('xp', "+$amount XP — $reason", $userId);
}

/** Fetch role permissions */
function permissions_for(string $role): array {
    static $cache = [];
    if (isset($cache[$role])) return $cache[$role];
    $perms = [];
    foreach (Database::all("SELECT permission FROM role_permissions WHERE role = ?", [$role]) as $r) {
        $perms[] = $r['permission'];
    }
    return $cache[$role] = $perms;
}

function can(string $perm): bool {
    $u = me();
    if (!$u) return false;
    if ($u['role'] === 'admin') return true;
    return in_array($perm, permissions_for($u['role']), true);
}

/** Redirect user to dashboard with a flash if they lack a permission */
function require_perm(string $perm): void {
    if (!can($perm)) {
        flash('danger', 'You do not have permission for that action.');
        redirect('dashboard');
    }
}

/** Generate special student ID: EDX-<year>-<6 digits> */
function generate_student_id(int $schoolId, ?int $year = null): string {
    $year = $year ?? date('Y');
    $n = Database::scalar("SELECT COUNT(*)+1 FROM users WHERE role='student' AND school_id = ?", [$schoolId], 1);
    $school = Database::one("SELECT code FROM schools WHERE id = ?", [$schoolId]);
    $prefix = $school ? mb_strtoupper(mb_substr($school['code'], 0, 3)) : 'EDX';
    return sprintf('%s-%d-%06d', $prefix, $year, $n);
}

/** Random password generator */
function random_password(int $len = 12): string {
    $u = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $l = 'abcdefghjkmnpqrstuvwxyz';
    $d = '23456789';
    $s = '!@#$%';
    $p = $u[random_int(0, strlen($u) - 1)] . $l[random_int(0, strlen($l) - 1)] . $d[random_int(0, strlen($d) - 1)];
    $pool = $u . $l . $d . $s;
    for ($i = 0; $i < $len - 3; $i++) $p .= $pool[random_int(0, strlen($pool) - 1)];
    return str_shuffle($p);
}

/** Theme toggle endpoint helper */
function current_theme(): string {
    return $_SESSION['theme'] ?? DEFAULT_THEME;
}

/** Avatar URL */
function avatar_url(?array $u): string {
    if ($u && !empty($u['avatar'])) return url('file?p=' . $u['avatar']);
    return url('public/images/avatar.svg');
}

/** Check file served via api (safe) */
function safe_storage_path(string $rel): ?string {
    $rel = ltrim($rel, '/');
    $abs = realpath(STORAGE_PATH . '/' . $rel);
    $base = realpath(STORAGE_PATH);
    if ($abs === false || $base === false || strpos($abs, $base) !== 0) return null;
    return $abs;
}

/** generate unique token */
function make_token(int $bytes = 24): string {
    return bin2hex(random_bytes($bytes));
}

/** Pagination helper */
function paginate(array &$page, int &$total, int &$perPage): void {
    $perPage = max(1, (int)($_GET['per'] ?? 20));
    $pageNo = max(1, (int)($_GET['pg'] ?? 1));
    $offset = ($pageNo - 1) * $perPage;
    $page = $pageNo;
    $total = max(0, $total);
}
