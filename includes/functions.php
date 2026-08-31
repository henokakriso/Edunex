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

/** Redirect back to previous page */
function back(): void {
    $ref = $_SERVER['HTTP_REFERER'] ?? url('dashboard');
    header('Location: ' . $ref);
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
    if (($u['role'] ?? '') === 'regional') {
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
        // Render a proper 403 page with layout so user can navigate away
        $title = 'Access Denied';
        $error_msg = 'Your role (' . e($u['role']) . ') does not have permission to access this page.';
        require BASE_PATH . '/app/views/errors/403.php';
        exit;
    }
    return $u;
}

/** Post-login landing page for the current user's role */
function dashboard_path(): string {
    return match (me()['role'] ?? '') {
        'ministry' => 'admin/dashboard', 'regional' => 'regional/dashboard', 'zonal' => 'zonal/dashboard', 'woreda' => 'woreda/dashboard', 'principal' => 'director/dashboard', 'teacher' => 'teacher/dashboard',
        'registrar' => 'registrar/dashboard', 'dean' => 'dean/dashboard', 'vice_dean' => 'dean/dashboard', 'hod' => 'dean/dashboard',
        'lecturer' => 'teacher/dashboard', 'bursar' => 'university/fees/manage', 'student_affairs' => 'university/clearance/manage',
        'librarian' => 'library', 'it_admin' => 'it_admin/dashboard',
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
    $file = STORAGE_PATH . '/rate/' . hash_hmac('sha256', $key, 'edunex_rate') . '.json';
    if (!is_file($file)) return false;
    $data = json_decode(file_get_contents($file), true) ?: [];
    if (($data['blocked_until'] ?? 0) > time()) return true;
    $t = array_values(array_filter($data['t'] ?? [], fn($x) => $x > time() - $windowSec));
    return count($t) >= $max;
}

/** Rate limiter: record one attempt and return false if over limit */
function rate_limit(string $key, int $max, int $windowSec, ?int &$remaining = null): bool {
    $file = STORAGE_PATH . '/rate/' . hash_hmac('sha256', $key, 'edunex_rate') . '.json';
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
    // Programmatic size validation
    if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) {
        return $fail('File exceeds ' . MAX_UPLOAD_MB . 'MB limit');
    }
    if ($file['size'] === 0) {
        return $fail('Empty file not allowed');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = $allowedExt ?? explode(',', ALLOWED_EXT);
    if (!in_array($ext, $allowed, true)) {
        return $fail('File type .' . $ext . ' is not allowed');
    }
    // Verify real image via getimagesize
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        if (@getimagesize($file['tmp_name']) === false) return $fail('Invalid image file');
    }
    // MIME type validation via finfo
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $validMimes = [
        'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp',
        'pdf'=>'application/pdf','doc'=>'application/msword',
        'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'=>'application/vnd.ms-excel',
        'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'=>'application/vnd.ms-powerpoint',
        'pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt'=>'text/plain','csv'=>'text/csv','md'=>'text/markdown',
        'zip'=>'application/zip','rar'=>'application/x-rar-compressed','7z'=>'application/x-7z-compressed',
        'mp3'=>'audio/mpeg','wav'=>'audio/wav','ogg'=>'audio/ogg',
        'mp4'=>'video/mp4','webm'=>'video/webm',
        'json'=>'application/json',
    ];
    if (isset($validMimes[$ext]) && $mimeType !== $validMimes[$ext]) {
        return $fail('File content does not match extension');
    }
    // FileSecurity: scan for malicious content, hash, encrypt
    [$safe, $reason] = FileSecurity::scanForMalicious($file['tmp_name'], $ext);
    if (!$safe) {
        return $fail($reason);
    }
    $rawContent = @file_get_contents($file['tmp_name']);
    if ($rawContent === false) {
        return $fail('Failed to read uploaded file');
    }
    $fileHash = FileSecurity::hash($rawContent);
    $encrypted = FileSecurity::encrypt($rawContent);
    if ($encrypted === '') {
        return $fail('Failed to encrypt file');
    }
    // Write encrypted content to target
    $targetDir = STORAGE_PATH . '/' . $dir;
    if (!is_dir($targetDir)) @mkdir($targetDir, 0750, true);
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = $targetDir . '/' . $name;
    if (file_put_contents($dest, $encrypted) === false) {
        return $fail('Failed to save file');
    }
    return [true, $dir . '/' . $name, 'ok' => true, 'error' => null, 'path' => $dir . '/' . $name, 'size' => (int)$file['size'], 'hash' => $fileHash, 'encrypted' => true];
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
        'regional' => 'Administrator', 'teacher' => 'Teacher', 'registrar' => 'Registrar', 'dean' => 'Dean',
        'vice_dean' => 'Vice Dean', 'hod' => 'Department Head',
        'lecturer' => 'Lecturer', 'bursar' => 'Bursar',
        'student_affairs' => 'Student Affairs', 'librarian' => 'Librarian',
        'student' => 'Student', 'parent' => 'Parent', 'guest' => 'Guest',
        default => ucfirst(str_replace('_', ' ', $r))
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
               'teacher-portal', 'parent-portal', 'student-portal', 'academic', 'attendance', 'library', 'ai-tutor', 'gamification'];
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
        'secondary' => ['grades', 'homework', 'reading', 'games', 'ai-reader', 'assignments', 'ai-tutor', 'attendance', 'messages', 'materials', 'exams', 'projects', 'research', 'career', 'certificates', 'leaderboard', 'schedule'],
        'university' => ['grades', 'homework', 'reading', 'games', 'ai-reader', 'assignments', 'ai-tutor', 'attendance', 'messages', 'materials', 'exams', 'projects', 'research', 'career', 'certificates', 'leaderboard', 'transfers', 'thesis', 'schedule', 'registration', 'clearance', 'transcript', 'payments'],
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

/** Check if demo mode is active (settings-based). */
function is_demo_mode(): bool {
    return (setting('demo_mode') ?? '0') === '1';
}

/** Returns SQL fragment to filter out demo data when in normal mode. */
function demo_filter(string $tableAlias = ''): string {
    if (is_demo_mode()) return '';
    $col = ($tableAlias ? $tableAlias . '.' : '') . 'is_demo';
    return " AND $col = 0";
}

/** DEMO data badge — marks seeded demo rows in ERP/portal views. */
function demo_badge(array $row): string {
    return !empty($row['is_demo'])
        ? '<span class="badge badge-warning" title="Seeded demo data">DEMO</span>'
        : '';
}

/** Return demo-only data or empty array depending on demo_mode setting. */
function demo_or_empty(array $demoData): array {
    return is_demo_mode() ? $demoData : [];
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
    if (in_array($u['role'], ['ministry', 'regional'], true)) return true;
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

/* ================= ETHIOPIAN CALENDAR (Ge'ez) ================= */

/** Ethiopian month names */
function ethiopian_months(): array {
    return [
        1 => 'Meskerem', 2 => 'Tikimt', 3 => 'Hidar', 4 => 'Tahsas',
        5 => 'Tir', 6 => 'Yekatit', 7 => 'Megabit', 8 => 'Miyazya',
        9 => 'Genna', 10 => 'Tebet', 11 => 'Yekur', 12 => 'Negus',
        13 => 'Pagume',
    ];
}

/** Short Ethiopian month names */
function ethiopian_months_short(): array {
    return [
        1 => 'Mes', 2 => 'Tik', 3 => 'Hid', 4 => 'Tah',
        5 => 'Tir', 6 => 'Yek', 7 => 'Meg', 8 => 'Miy',
        9 => 'Gen', 10 => 'Teb', 11 => 'Yek', 12 => 'Neg',
        13 => 'Pag',
    ];
}

/** Is an Ethiopian year a leap year? (every 4 years, with exception every 100 but not every 400 — same rule as Gregorian offset) */
function ethiopian_leap_year(int $year): bool {
    return $year % 4 === 0;
}

/** Days in an Ethiopian month */
function ethiopian_month_days(int $month, int $year): int {
    if ($month >= 1 && $month <= 12) return 30;
    if ($month === 13) return ethiopian_leap_year($year) ? 6 : 5;
    return 0;
}

/**
 * Convert Gregorian date to Ethiopian date.
 * Returns [year, month, day].
 *
 * The Ethiopian calendar is approximately 7-8 years behind the Gregorian.
 * Ethiopian New Year (Meskerem 1) falls on September 11 (or September 12 in Gregorian leap years).
 */
function gregorian_to_ethiopian(int $year, int $month, int $day): array {
    $jd = (int)floor(365.25 * ($year + 4800 + (int)(($month - 14) / 12)))
        + (int)floor(30.6001 * ($month - 2 - 12 * (int)(($month - 14) / 12)))
        + $day - 32075;
    $r = ($jd - 1723857) % 1461;
    $n = (int)floor($r / 365) - (int)floor($r / 1461);
    $ju = $jd - ($n * 365 + (int)floor($n / 4));
    $n2 = (int)floor($ju / 366);
    $n3 = $ju - $n2 * 366 + 365;
    $year2 = $n + $n2 + 38;
    if ((int)floor(($year2 + 3) / 4) < $n3) $year2++;
    $daysInYear = ethiopian_leap_year($year2) ? 366 : 365;
    $dayOfYear = $n3 - (int)floor(($year2 + 3) / 4);
    $month = (int)floor(($dayOfYear - 1) / 30) + 1;
    $day = $dayOfYear - ($month - 1) * 30;
    if ($month > 13) $month = 13;
    if ($day > ethiopian_month_days($month, $year2)) $day = ethiopian_month_days($month, $year2);
    return [$year2, $month, $day];
}

/**
 * Convert Ethiopian date to Gregorian date.
 * Returns [year, month, day].
 */
function ethiopian_to_gregorian(int $year, int $month, int $day): array {
    $year += 7;
    $jd = 1723856 + 365 * $year + (int)floor($year / 4) + 30 * ($month - 1) + $day - 1;
    $l = $jd - 1924420 + 1364;
    $n = (int)floor(($l - 1) / 1461);
    $l2 = $l - 1461 * $n;
    $l3 = (int)floor(($l2 - 1) / 365) - (int)floor($l2 / 1461);
    $i = $l2 - 365 * $l3 + 30;
    $j = (int)floor($i / 30);
    $i2 = $i - $j * 30;
    $j2 = (int)floor(($j + 1) / 11);
    $j3 = $j - $j2 * 11 + 2;
    $year2 = 4 * $n + $l3 + $j2 - 4716;
    $month = $j3 - $j2 * 2 + 3;
    $day = $i2 + 1;
    if ($month > 12) { $month -= 12; $year2++; }
    $daysInMonth = [31, ($year2 % 4 === 0 ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    if ($day > ($daysInMonth[$month - 1] ?? 31)) $day = $daysInMonth[$month - 1] ?? 31;
    return [$year2, $month, $day];
}

/** Format a Gregorian date as Ethiopian: "Meskerem 1, 2016" */
function ethiopian_date(string $gregorianDate): string {
    $dt = new DateTime($gregorianDate);
    [$y, $m, $d] = gregorian_to_ethiopian((int)$dt->format('Y'), (int)$dt->format('m'), (int)$dt->format('d'));
    $months = ethiopian_months();
    return ($months[$m] ?? '???') . ' ' . $d . ', ' . $y;
}

/** Format a Gregorian date as short Ethiopian: "Mes 1, 2016" */
function ethiopian_date_short(string $gregorianDate): string {
    $dt = new DateTime($gregorianDate);
    [$y, $m, $d] = gregorian_to_ethiopian((int)$dt->format('Y'), (int)$dt->format('m'), (int)$dt->format('d'));
    $months = ethiopian_months_short();
    return ($months[$m] ?? '???') . ' ' . $d . ', ' . $y;
}

/** Current Ethiopian date as array [year, month, day] */
function ethiopian_now(): array {
    return gregorian_to_ethiopian((int)date('Y'), (int)date('m'), (int)date('d'));
}

/** Current Ethiopian year */
function ethiopian_year(): int {
    [$y] = ethiopian_now();
    return $y;
}

/** Academic year label in Ethiopian calendar (e.g. "2017 E.C.") */
function academic_year_ethiopian(): string {
    return ethiopian_year() . ' E.C.';
}

/* ================= GRADE AUDIT TRAIL ================= */

/**
 * Record a grade change in the audit trail.
 * Call this whenever a grade is created, updated, or deleted.
 */
function grade_audit_log(
    int $studentId,
    int $courseId,
    string $assessmentType,
    int $assessmentId,
    ?string $oldScore,
    ?string $newScore,
    string $reason,
    int $actorId,
    string $action = 'update'
): void {
    $schoolId = (int)Database::scalar(
        "SELECT c.school_id FROM courses c WHERE c.id = ?", [$courseId], 0
    );
    Database::insert('grade_audit', [
        'student_id'     => $studentId,
        'course_id'      => $courseId,
        'school_id'      => $schoolId,
        'assessment_type' => $assessmentType,
        'assessment_id'  => $assessmentId,
        'old_score'      => $oldScore,
        'new_score'      => $newScore,
        'action'         => $action,
        'reason'         => $reason,
        'actor_id'       => $actorId,
    ]);
    log_activity('grade_change', "$action $assessmentType #$assessmentId: $oldScore -> $newScore ($reason)", $actorId);
}

/** Fetch audit trail for a student's grades */
function grade_audit_for_student(int $studentId, int $limit = 50): array {
    return Database::all(
        "SELECT ga.*, CONCAT(u.first_name, ' ', u.last_name) AS actor_name,
                c.title AS course_title
         FROM grade_audit ga
         JOIN users u ON u.id = ga.actor_id
         JOIN courses c ON c.id = ga.course_id
         WHERE ga.student_id = ?
         ORDER BY ga.created_at DESC LIMIT ?",
        [$studentId, $limit]
    );
}

/* ================= UNIVERSITY SYSTEM ================= */

/** Grade to grade-point mapping */
function grade_to_points(string $grade): float {
    $map = [
        'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D+' => 1.3, 'D' => 1.0,
        'F' => 0.0,
    ];
    return $map[strtoupper($grade)] ?? 0.0;
}

/** Compute SGPA for a student in a given semester */
function compute_sgpa(int $studentId, int $semesterId): array {
    $rows = Database::all(
        "SELECT ar.credit_hours, ar.grade_points
         FROM academic_records ar
         WHERE ar.student_id = ? AND ar.semester_id = ?",
        [$studentId, $semesterId]
    );
    $totalQp = 0.0;
    $totalCr = 0;
    foreach ($rows as $r) {
        $totalQp += (float)$r['credit_hours'] * (float)$r['grade_points'];
        $totalCr += (int)$r['credit_hours'];
    }
    return [
        'sgpa'          => $totalCr > 0 ? round($totalQp / $totalCr, 2) : 0.0,
        'quality_points' => $totalQp,
        'credit_hours'  => $totalCr,
    ];
}

/** Compute CGPA for a student (all semesters) */
function compute_cgpa(int $studentId): array {
    $rows = Database::all(
        "SELECT ar.credit_hours, ar.grade_points
         FROM academic_records ar
         WHERE ar.student_id = ?",
        [$studentId]
    );
    $totalQp = 0.0;
    $totalCr = 0;
    foreach ($rows as $r) {
        $totalQp += (float)$r['credit_hours'] * (float)$r['grade_points'];
        $totalCr += (int)$r['credit_hours'];
    }
    return [
        'cgpa'          => $totalCr > 0 ? round($totalQp / $totalCr, 2) : 0.0,
        'quality_points' => $totalQp,
        'credit_hours'  => $totalCr,
    ];
}

/** Determine academic standing from CGPA */
function academic_standing(float $cgpa): string {
    if ($cgpa >= 3.5) return 'deans_list';
    if ($cgpa >= 2.0) return 'good';
    if ($cgpa >= 1.5) return 'probation';
    return 'suspension';
}

/** Human-readable standing label */
function standing_label(string $standing): string {
    return match ($standing) {
        'deans_list' => "Dean's List",
        'good'       => 'Good Standing',
        'probation'  => 'Academic Probation',
        'suspension' => 'Academic Suspension',
        default      => ucfirst($standing),
    };
}

/** Check if student has passed a course (grade >= C or >= given min) */
function has_passed_course(int $studentId, int $courseId, string $minGrade = 'D'): bool {
    $min = grade_to_points($minGrade);
    $row = Database::one(
        "SELECT ar.grade_points FROM academic_records ar
         JOIN course_offerings co ON co.id = ar.course_offering_id
         WHERE ar.student_id = ? AND co.course_id = ? AND ar.grade_points >= ?
         ORDER BY ar.grade_points DESC LIMIT 1",
        [$studentId, $courseId, $min]
    );
    return $row !== null;
}

/** Check if student meets all prerequisites for a course */
function meets_prerequisites(int $studentId, int $courseId): array {
    $prereqs = Database::all(
        "SELECT p.required_course_id, p.min_grade, c.title, c.code
         FROM prerequisites p
         JOIN courses c ON c.id = p.required_course_id
         WHERE p.course_id = ?",
        [$courseId]
    );
    $missing = [];
    foreach ($prereqs as $p) {
        if (!has_passed_course($studentId, $p['required_course_id'], $p['min_grade'])) {
            $missing[] = $p;
        }
    }
    return ['met' => empty($missing), 'missing' => $missing];
}

/** Check schedule conflicts for a student against a course offering */
function schedule_conflicts(int $studentId, int $courseOfferingId): array {
    $new = Database::one("SELECT schedule_json FROM course_offerings WHERE id = ?", [$courseOfferingId]);
    if (!$new || empty($new['schedule_json'])) return [];
    $newSched = json_decode($new['schedule_json'], true) ?: [];

    $existing = Database::all(
        "SELECT co.id, co.schedule_json, co.course_id, c.title
         FROM registrations r
         JOIN course_offerings co ON co.id = r.course_offering_id
         JOIN courses c ON c.id = co.course_id
         WHERE r.student_id = ? AND r.status = 'registered' AND co.id != ?",
        [$studentId, $courseOfferingId]
    );
    $conflicts = [];
    foreach ($existing as $ex) {
        if (empty($ex['schedule_json'])) continue;
        $oldSched = json_decode($ex['schedule_json'], true) ?: [];
        foreach ($newSched as $day => $newTimes) {
            if (!isset($oldSched[$day])) continue;
            $nt = $newSched[$day];
            $ot = $oldSched[$day];
            // simple overlap: same day AND overlapping time range
            if ($nt['start'] < $ot['end'] && $nt['end'] > $ot['start']) {
                $conflicts[] = ['offering_id' => $ex['id'], 'course' => $ex['title'], 'day' => $day];
            }
        }
    }
    return $conflicts;
}

/** Get current/max credit hours for a student */
function student_credit_load(int $studentId, int $semesterId): array {
    $row = Database::one(
        "SELECT COALESCE(SUM(c.credits), 0) AS enrolled
         FROM registrations r
         JOIN course_offerings co ON co.id = r.course_offering_id
         JOIN courses c ON c.id = co.course_id
         WHERE r.student_id = ? AND r.status = 'registered' AND co.semester_id = ?",
        [$studentId, $semesterId]
    );
    $enrolled = (int)($row['enrolled'] ?? 0);
    $max = (int)Database::scalar(
        "SELECT COALESCE(s.value, '18') FROM settings s WHERE s.key = 'max_credit_hours'",
        [], 18
    );
    return ['enrolled' => $enrolled, 'max' => $max, 'remaining' => max(0, $max - $enrolled)];
}

/** Register a student for a course (with all validations) */
function register_course(int $studentId, int $courseOfferingId): array {
    $offering = Database::one("SELECT * FROM course_offerings WHERE id = ?", [$courseOfferingId]);
    if (!$offering) return ['ok' => false, 'error' => 'Course offering not found.'];
    if ($offering['status'] !== 'open') return ['ok' => false, 'error' => 'Course is not open for registration.'];
    if ($offering['current_students'] >= $offering['max_students']) return ['ok' => false, 'error' => 'Course is full.'];

    // Already registered?
    $exists = Database::one(
        "SELECT id FROM registrations WHERE student_id = ? AND course_offering_id = ? AND status = 'registered'",
        [$studentId, $courseOfferingId]
    );
    if ($exists) return ['ok' => false, 'error' => 'Already registered for this course.'];

    // Prerequisites
    $pre = meets_prerequisites($studentId, $offering['course_id']);
    if (!$pre['met']) {
        $names = array_map(fn($p) => $p['code'] . ' ' . $p['title'], $pre['missing']);
        return ['ok' => false, 'error' => 'Missing prerequisites: ' . implode(', ', $names)];
    }

    // Schedule conflict
    $conflicts = schedule_conflicts($studentId, $courseOfferingId);
    if (!empty($conflicts)) {
        return ['ok' => false, 'error' => 'Schedule conflict with: ' . $conflicts[0]['course']];
    }

    // Credit limit
    $load = student_credit_load($studentId, $offering['semester_id']);
    $cr = Database::one("SELECT credits FROM courses WHERE id = ?", [$offering['course_id']]);
    $crHours = (int)($cr['credits'] ?? 3);
    if ($load['enrolled'] + $crHours > $load['max']) {
        return ['ok' => false, 'error' => "Credit limit reached ({$load['max']}). Currently enrolled: {$load['enrolled']} cr."];
    }

    // Register
    Database::insert('registrations', [
        'student_id'         => $studentId,
        'course_offering_id' => $courseOfferingId,
        'status'             => 'registered',
    ]);
    Database::run("UPDATE course_offerings SET current_students = current_students + 1 WHERE id = ?", [$courseOfferingId]);
    return ['ok' => true];
}

/** Drop a student from a course */
function drop_course(int $studentId, int $courseOfferingId): array {
    $reg = Database::one(
        "SELECT id FROM registrations WHERE student_id = ? AND course_offering_id = ? AND status = 'registered'",
        [$studentId, $courseOfferingId]
    );
    if (!$reg) return ['ok' => false, 'error' => 'Registration not found.'];
    Database::update('registrations', ['status' => 'dropped', 'dropped_at' => date('Y-m-d H:i:s')], 'id = ?', [$reg['id']]);
    Database::run("UPDATE course_offerings SET current_students = GREATEST(current_students - 1, 0) WHERE id = ?", [$courseOfferingId]);
    return ['ok' => true];
}

/** Create a clearance request with tracking code */
function create_clearance_request(int $studentId, string $type = 'graduation'): array {
    $school = Database::one(
        "SELECT s.code FROM users u JOIN schools s ON s.id = u.school_id WHERE u.id = ?",
        [$studentId]
    );
    $prefix = $school ? strtoupper(mb_substr($school['code'], 0, 4)) : 'UNIV';
    $seq = (int)Database::scalar(
        "SELECT COUNT(*) + 1 FROM clearance_requests WHERE YEAR(requested_at) = YEAR(NOW())",
        [], 1
    );
    $year = date('Y');
    $trackingCode = sprintf('CLR-%s-%s-%05d', $year, $prefix, $seq);

    Database::insert('clearance_requests', [
        'student_id'    => $studentId,
        'type'          => $type,
        'status'        => 'pending',
        'tracking_code' => $trackingCode,
    ]);

    $requestId = (int)Database::scalar("SELECT LAST_INSERT_ID()", [], 0);

    // Create clearance items for each department
    $departments = ['library', 'finance', 'dormitory', 'lab', 'academic', 'disciplinary', 'department'];
    foreach ($departments as $dept) {
        Database::insert('clearance_items', [
            'request_id'  => $requestId,
            'department'  => $dept,
            'status'      => 'pending',
        ]);
    }

    return ['ok' => true, 'request_id' => $requestId, 'tracking_code' => $trackingCode];
}

/** Check or update a clearance item */
function check_clearance_item(int $itemId, int $checkerId, string $status, string $notes = ''): array {
    $item = Database::one("SELECT * FROM clearance_items WHERE id = ?", [$itemId]);
    if (!$item) return ['ok' => false, 'error' => 'Item not found.'];
    $sig = hash_hmac('sha256', "$itemId-$checkerId-$status-" . date('Y-m-d H:i:s'), 'edunex_clearance');
    Database::update('clearance_items', [
        'checker_id'     => $checkerId,
        'status'         => $status,
        'notes'          => $notes,
        'checked_at'     => date('Y-m-d H:i:s'),
        'signature_hash' => $sig,
    ], 'id = ?', [$itemId]);

    // Update parent request status
    $requestId = $item['request_id'];
    $all = Database::all("SELECT status FROM clearance_items WHERE request_id = ?", [$requestId]);
    $allPassed = true;
    $anyFailed = false;
    foreach ($all as $ai) {
        if ($ai['status'] !== 'passed' && $ai['id'] != $itemId) { $allPassed = false; }
        if ($ai['status'] === 'failed') $anyFailed = true;
    }
    // Also consider the current update
    if ($status === 'failed') $anyFailed = true;
    if ($status !== 'passed') $allPassed = false;

    $reqStatus = 'in_progress';
    if ($anyFailed) $reqStatus = 'rejected';
    elseif ($allPassed && $status === 'passed') $reqStatus = 'cleared';

    Database::update('clearance_requests', [
        'status'       => $reqStatus,
        'completed_at' => $reqStatus === 'cleared' ? date('Y-m-d H:i:s') : null,
    ], 'id = ?', [$requestId]);

    return ['ok' => true, 'request_status' => $reqStatus];
}

/** Generate a clearance verification hash */
function clearance_verify_url(string $trackingCode): string {
    return url('verify/clearance?code=' . urlencode($trackingCode));
}

/** Generate a transcript hash */
function transcript_hash(int $requestId): string {
    $rows = Database::all(
        "SELECT ar.grade, ar.credit_hours, ar.grade_points, c.title, c.code, sem.name AS semester_name
         FROM academic_records ar
         JOIN course_offerings co ON co.id = ar.course_offering_id
         JOIN courses c ON c.id = co.course_id
         JOIN semesters sem ON sem.id = ar.semester_id
         WHERE ar.student_id = (SELECT student_id FROM transcript_requests WHERE id = ?)
         ORDER BY sem.start_date, c.code",
        [$requestId]
    );
    return hash('sha256', json_encode($rows));
}

/** Create a transcript request */
function create_transcript_request(int $studentId, string $type = 'unofficial'): array {
    Database::insert('transcript_requests', [
        'student_id' => $studentId,
        'type'       => $type,
        'status'     => 'pending',
    ]);
    $id = (int)Database::scalar("SELECT LAST_INSERT_ID()", [], 0);
    return ['ok' => true, 'request_id' => $id];
}

/** Create invoice for a student for a given semester */
function create_student_invoice(int $studentId, int $semesterId): array {
    $schoolId = (int)Database::scalar(
        "SELECT u.school_id FROM users u WHERE u.id = ?", [$studentId], 0
    );
    $fees = Database::all(
        "SELECT * FROM fee_structures WHERE school_id = ? AND (semester_id = ? OR semester_id IS NULL) AND status = 'active'",
        [$schoolId, $semesterId]
    );
    if (empty($fees)) return ['ok' => false, 'error' => 'No fee structures defined.'];

    // Check existing invoice
    $existing = Database::one(
        "SELECT id FROM invoices WHERE student_id = ? AND semester_id = ?",
        [$studentId, $semesterId]
    );
    if ($existing) return ['ok' => false, 'error' => 'Invoice already exists.', 'invoice_id' => $existing['id']];

    $total = 0.0;
    $items = [];
    foreach ($fees as $f) {
        $amt = (float)$f['amount'];
        if ($f['fee_type'] === 'per_credit') {
            $cr = Database::one(
                "SELECT COALESCE(SUM(c.credits), 0) AS total
                 FROM registrations r
                 JOIN course_offerings co ON co.id = r.course_offering_id
                 JOIN courses c ON c.id = co.course_id
                 WHERE r.student_id = ? AND co.semester_id = ? AND r.status = 'registered'",
                [$studentId, $semesterId]
            );
            $amt *= (float)($cr['total'] ?? 0);
        }
        if ($amt <= 0) continue;
        $total += $amt;
        $items[] = ['fee_structure_id' => $f['id'], 'description' => $f['name'], 'amount' => $amt];
    }

    Database::insert('invoices', [
        'student_id'    => $studentId,
        'semester_id'   => $semesterId,
        'total_amount'  => $total,
        'paid_amount'   => 0,
        'status'        => 'pending',
        'due_date'      => date('Y-m-d', strtotime('+30 days')),
    ]);
    $invId = (int)Database::scalar("SELECT LAST_INSERT_ID()", [], 0);

    foreach ($items as $it) {
        Database::insert('invoice_items', [
            'invoice_id'       => $invId,
            'fee_structure_id' => $it['fee_structure_id'],
            'description'      => $it['description'],
            'amount'           => $it['amount'],
        ]);
    }

    return ['ok' => true, 'invoice_id' => $invId, 'total' => $total];
}

/** Record a payment against an invoice */
function record_payment(int $invoiceId, float $amount, string $method, string $ref = '', string $notes = '', ?int $recordedBy = null): array {
    $inv = Database::one("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
    if (!$inv) return ['ok' => false, 'error' => 'Invoice not found.'];
    if ($amount <= 0) return ['ok' => false, 'error' => 'Invalid amount.'];

    Database::insert('payments', [
        'invoice_id'      => $invoiceId,
        'student_id'      => $inv['student_id'],
        'amount'          => $amount,
        'payment_method'  => $method,
        'reference_number'=> $ref,
        'notes'           => $notes,
        'recorded_by'     => $recordedBy,
    ]);

    $newPaid = (float)$inv['paid_amount'] + $amount;
    $status = 'partial';
    if ($newPaid >= (float)$inv['total_amount']) $status = 'paid';
    elseif (strtotime($inv['due_date'] ?? '') < time() && $newPaid < (float)$inv['total_amount']) $status = 'overdue';

    Database::update('invoices', [
        'paid_amount' => $newPaid,
        'status'      => $status,
    ], 'id = ?', [$invoiceId]);

    return ['ok' => true, 'status' => $status, 'balance' => (float)$inv['total_amount'] - $newPaid];
}

/** Create a thesis record */
function create_thesis(int $studentId, int $programId, string $title, string $abstract = ''): array {
    Database::insert('theses', [
        'student_id' => $studentId,
        'program_id' => $programId,
        'title'      => $title,
        'abstract'   => $abstract,
        'status'     => 'proposal',
    ]);
    $id = (int)Database::scalar("SELECT LAST_INSERT_ID()", [], 0);
    // Create default chapters
    $chapters = ['Introduction', 'Literature Review', 'Methodology', 'Results', 'Conclusion'];
    foreach ($chapters as $i => $ch) {
        Database::insert('thesis_chapters', [
            'thesis_id'      => $id,
            'chapter_number' => $i + 1,
            'title'          => $ch,
            'status'         => 'draft',
        ]);
    }
    return ['ok' => true, 'thesis_id' => $id];
}

/** Assign thesis advisor */
function assign_thesis_advisor(int $thesisId, int $advisorId): array {
    Database::update('theses', ['advisor_id' => $advisorId], 'id = ?', [$thesisId]);
    Database::insert('thesis_committee', [
        'thesis_id' => $thesisId,
        'member_id' => $advisorId,
        'role'      => 'advisor',
    ]);
    return ['ok' => true];
}

/** Submit thesis chapter for review */
function submit_thesis_chapter(int $chapterId, int $studentId): array {
    $ch = Database::one(
        "SELECT tc.*, t.student_id FROM thesis_chapters tc JOIN theses t ON t.id = tc.thesis_id WHERE tc.id = ?",
        [$chapterId]
    );
    if (!$ch) return ['ok' => false, 'error' => 'Chapter not found.'];
    if ((int)$ch['student_id'] !== $studentId) return ['ok' => false, 'error' => 'Not your thesis.'];
    Database::update('thesis_chapters', [
        'status'      => 'submitted',
        'submitted_at' => date('Y-m-d H:i:s'),
    ], 'id = ?', [$chapterId]);
    return ['ok' => true];
}

/** Schedule thesis defense */
function schedule_defense(int $thesisId, string $date): array {
    Database::update('theses', [
        'defense_date' => $date,
        'status'       => 'defense',
    ], 'id = ?', [$thesisId]);
    return ['ok' => true];
}

/** Record thesis defense result */
function thesis_defense_result(int $thesisId, string $result, string $notes = ''): array {
    Database::update('theses', [
        'defense_result' => $result,
        'defense_notes'  => $notes,
        'status'         => $result === 'pass' ? 'completed' : ($result === 'revise' ? 'revision' : 'in_progress'),
    ], 'id = ?', [$thesisId]);
    return ['ok' => true];
}

/** Check schedule availability for a room */
function room_available(int $roomId, string $day, string $startTime, string $endTime, ?int $excludeOfferingId = null): bool {
    $where = "s.room_id = ? AND s.day = ?";
    $args = [$roomId, $day];
    if ($excludeOfferingId) {
        $where .= " AND s.course_offering_id != ?";
        $args[] = $excludeOfferingId;
    }
    $rows = Database::all(
        "SELECT s.start_time, s.end_time FROM schedules s WHERE $where",
        $args
    );
    foreach ($rows as $r) {
        if ($startTime < $r['end_time'] && $endTime > $r['start_time']) return false;
    }
    return true;
}

/** Generate student ID card data */
function generate_student_card(int $studentId): array {
    $u = Database::one(
        "SELECT u.*, s.name AS school_name, s.code AS school_code
         FROM users u JOIN schools s ON s.id = u.school_id WHERE u.id = ?",
        [$studentId]
    );
    if (!$u) return ['ok' => false, 'error' => 'Student not found.'];

    $existing = Database::one(
        "SELECT * FROM student_cards WHERE student_id = ? AND status = 'active'",
        [$studentId]
    );
    if ($existing) return ['ok' => true, 'card' => $existing];

    $seq = (int)Database::scalar("SELECT COUNT(*) + 1 FROM student_cards", [], 1);
    $cardNumber = sprintf('%s-%s-%05d', strtoupper(mb_substr($u['school_code'] ?? 'EDX', 0, 3)), date('Y'), $seq);
    $qrData = url('verify/card?id=' . $cardNumber);

    Database::insert('student_cards', [
        'student_id'    => $studentId,
        'card_number'   => $cardNumber,
        'barcode_data'  => $u['student_id'] ?? $cardNumber,
        'qr_data'       => $qrData,
        'expires_at'    => date('Y-m-d', strtotime('+4 years')),
    ]);

    $card = Database::one("SELECT * FROM student_cards WHERE student_id = ? AND status = 'active'", [$studentId]);
    return ['ok' => true, 'card' => $card];
}

/* ═══════════════ LICENSE TIER SYSTEM ═══════════════ */

/** Get the active license for a school (or platform-wide if school_id=0). */
function license_for_school(int $schoolId): ?array {
    static $cache = [];
    $key = 'lic_' . $schoolId;
    if (isset($cache[$key])) return $cache[$key];
    $now = date('Y-m-d');
    $lic = Database::one(
        "SELECT * FROM licenses WHERE (school_id = ? OR school_id IS NULL) AND status = 'active'
         AND (expires_at IS NULL OR expires_at >= ?) ORDER BY school_id DESC LIMIT 1",
        [$schoolId, $now]
    );
    $cache[$key] = $lic;
    return $lic;
}

/** Check if a school's license allows a specific module. */
function license_can(int $schoolId, string $moduleKey): bool {
    $lic = license_for_school($schoolId);
    if (!$lic) return true; // No license = allow all (backward compat for ministry users)
    $tier = $lic['type'] ?? 'standard';
    $allowed = Database::scalar(
        "SELECT COUNT(*) FROM license_tier_features WHERE tier = ? AND module_key = ?",
        [$tier, $moduleKey], 0
    );
    return $allowed > 0;
}

/** Get all modules allowed by a license tier. */
function license_modules(string $tier): array {
    return Database::all("SELECT module_key, max_seats, max_schools FROM license_tier_features WHERE tier = ?", [$tier]);
}

/** Get seat limit for a tier (0=unlimited). */
function license_seat_limit(string $tier): int {
    $row = Database::one("SELECT max_seats FROM license_tier_features WHERE tier = ? LIMIT 1", [$tier]);
    return $row ? (int)$row['max_seats'] : 0;
}

/** Get school limit for a tier (0=unlimited). */
function license_school_limit(string $tier): int {
    $row = Database::one("SELECT max_schools FROM license_tier_features WHERE tier = ? LIMIT 1", [$tier]);
    return $row ? (int)$row['max_schools'] : 0;
}

/** Check if a school can add more users (seat limit not exceeded). */
function license_seats_available(int $schoolId): bool {
    $lic = license_for_school($schoolId);
    if (!$lic) return true;
    $limit = (int)$lic['seats'];
    if ($limit <= 0) return true; // 0 = unlimited
    $used = (int)Database::scalar(
        "SELECT COUNT(*) FROM users WHERE school_id = ? AND status = 'active'", [$schoolId], 0
    );
    return $used < $limit;
}

/** Get seat usage for a license. */
function license_seat_usage(int $schoolId): array {
    $lic = license_for_school($schoolId);
    if (!$lic) return ['limit' => 0, 'used' => 0, 'pct' => 0];
    $limit = (int)$lic['seats'];
    $used = (int)Database::scalar(
        "SELECT COUNT(*) FROM users WHERE school_id = ? AND status = 'active'", [$schoolId], 0
    );
    $pct = $limit > 0 ? round($used / $limit * 100) : 0;
    return ['limit' => $limit, 'used' => $used, 'pct' => $pct];
}

/** Check if a license is expired or expiring soon. Returns: 'ok', 'expiring', 'expired'. */
function license_status(int $schoolId): string {
    $lic = license_for_school($schoolId);
    if (!$lic) return 'ok';
    if ($lic['status'] !== 'active') return 'expired';
    if (!$lic['expires_at']) return 'ok';
    $days = (strtotime($lic['expires_at']) - time()) / 86400;
    if ($days < 0) return 'expired';
    if ($days <= 30) return 'expiring';
    return 'ok';
}

/** Auto-expire licenses past their expiration date. */
function license_auto_expire(): int {
    $stmt = Database::run(
        "UPDATE licenses SET status = 'expired' WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at < CURDATE()"
    );
    return $stmt->rowCount();
}

/** Check if the current user's school has a valid license for a module. Returns true if allowed. */
function require_license(string $moduleKey): void {
    $u = me();
    if (!$u) return;
    // Ministry/regional admins bypass license checks
    if (in_array($u['role'] ?? '', ['ministry', 'regional', 'zonal', 'woreda'])) return;
    $schoolId = (int)($u['school_id'] ?? 0);
    if ($schoolId <= 0) return;
    if (!license_can($schoolId, $moduleKey)) {
        $lic = license_for_school($schoolId);
        $tier = $lic['type'] ?? 'none';
        http_response_code(403);
        echo '<div style="text-align:center;padding:60px;font-family:system-ui"><h2>Module Not Available</h2><p>This feature requires a <b>' . ucfirst($tier) . '</b> license or higher.</p><p>Contact your administrator to upgrade.</p></div>';
        exit;
    }
}
