<?php
/**
 * Regional Admin (role 'regional') — supervises only the schools assigned to them.
 * Every query is scoped by schools.admin_id = current admin. No cross-school access.
 */

class RegionalScope {
    public static function schools(int $adminId): array {
        return Database::all("SELECT * FROM schools WHERE admin_id = ? AND status != 'archived' ORDER BY name", [$adminId]);
    }
    public static function ids(int $adminId): array {
        return array_map('intval', array_column(self::schools($adminId), 'id'));
    }
    public static function idList(int $adminId): string {
        $ids = self::ids($adminId);
        return $ids ? implode(',', $ids) : '0';
    }
    public static function requireSchool(int $adminId, int $schoolId): array {
        $s = Database::one("SELECT * FROM schools WHERE id = ? AND admin_id = ? AND status != 'archived'", [$schoolId, $adminId]);
        if (!$s) {
            http_response_code(403);
            die('Access denied. This school is not assigned to you.');
        }
        return $s;
    }
}

class Ctl_regional {
    public function run(): void {
        $u = require_role('regional');
        $uid = (int)$u['id'];
        $action = trim($_GET['r'] ?? '', '/');
        $route = str_replace('regional/', '', $action);

        match ($route) {
            'dashboard' => $this->dashboard($u),
            'schools' => $this->schools($u),
            'school' => $this->school($u),
            'directors' => $this->directors($u),
            'principal' => $this->director($u),
            'analytics' => $this->analytics($u),
            'announcements' => $this->announcements($u),
            'backups' => $this->backups($u),
            'audit' => $this->audit($u),
            default => $this->dashboard($u),
        };
    }

    private function stat(string $sql, array $args = [], int $default = 0): int {
        return (int)Database::scalar($sql, $args, $default);
    }

    private function dashboard(array $u): void {
        $uid = (int)$u['id'];
        $idList = RegionalScope::idList($uid);
        $schools = RegionalScope::schools($uid);
        $schoolsArr = implode(',', $schools ? array_map(fn($s) => (int)$s['id'], $schools) : [0]);

        $s = [
            'schools' => count($schools),
            'students' => $this->stat("SELECT COUNT(*) FROM users WHERE role = 'student' AND school_id IN ($idList)"),
            'teachers' => $this->stat("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND school_id IN ($idList)"),
            'directors' => $this->stat("SELECT COUNT(*) FROM users WHERE role = 'principal' AND school_id IN ($idList)"),
            'courses' => $this->stat("SELECT COUNT(*) FROM courses WHERE school_id IN ($idList)"),
            'enroll30' => $this->stat("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE c.school_id IN ($idList) AND ce.enrolled_at >= NOW() - INTERVAL 30 DAY"),
            'pending_transfers' => $this->stat("SELECT COUNT(*) FROM transfer_requests WHERE status = 'pending' AND to_school_id IN ($idList)"),
        ];
        $s['load'] = round($s['schools'] / 15 * 100, 0);
        $s['ratio'] = $s['schools'] ? round($s['directors'] / $s['schools'], 1) : 0;

        $schoolStats = [];
        foreach ($schools as $sc) {
            $schoolStats[] = [
                'school' => $sc,
                'students' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student' AND school_id = ?", [$sc['id']], 0),
                'teachers' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND school_id = ?", [$sc['id']], 0),
                'directors' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'principal' AND school_id = ?", [$sc['id']], 0),
                'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE school_id = ?", [$sc['id']], 0),
            ];
        }
        Router::render('app/regional/dashboard', [
            'title' => 'Regional Overview', 'stats' => $s, 'schoolStats' => $schoolStats,
        ]);
    }

    private function schools(array $u): void {
        $uid = (int)$u['id'];
        $idList = RegionalScope::idList($uid);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $id = (int)($_POST['school_id'] ?? 0);
            RegionalScope::requireSchool($uid, $id);
            $action = (string)($_POST['action'] ?? '');
            $status = $action === 'suspend' ? 'suspended' : ($action === 'archive' ? 'archived' : 'active');
            Database::update('schools', ['status' => $status], 'id = ?', [$id]);
            log_activity('school.' . $status, 'Regional admin ' . $u['email'] . ' set school #' . $id . ' to ' . $status, $uid);
            flash('success', 'School ' . ($status === 'archived' ? 'archived' : ($status === 'suspended' ? 'suspended' : 'activated')) . '.');
            redirect('regional/schools');
        }
        $rows = Database::all(
            "SELECT sc.*,
                    (SELECT COUNT(*) FROM users WHERE role = 'principal' AND school_id = sc.id) AS directors,
                    (SELECT COUNT(*) FROM users WHERE role = 'student' AND school_id = sc.id) AS students,
                    (SELECT COUNT(*) FROM users WHERE role = 'teacher' AND school_id = sc.id) AS teachers
             FROM schools sc WHERE sc.admin_id = ? ORDER BY sc.status, sc.name", [$uid]);
        Router::render('app/regional/schools', ['title' => 'My Schools', 'rows' => $rows]);
    }

    private function school(array $u): void {
        $uid = (int)$u['id'];
        $id = (int)($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $id = (int)($_POST['school_id'] ?? 0);
            $s = RegionalScope::requireSchool($uid, $id);
            $action = (string)($_POST['action'] ?? '');
            $status = $action === 'suspend' ? 'suspended' : ($action === 'archive' ? 'archived' : 'active');
            Database::update('schools', ['status' => $status], 'id = ?', [$id]);
            log_activity('school.' . $status, 'Regional admin set school #' . $id . ' to ' . $status, $uid);
            flash('success', 'School updated.');
            redirect('regional/school&id=' . $id);
        }
        $school = RegionalScope::requireSchool($uid, $id);
        $stats = [
            'students' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student' AND school_id = ?", [$id], 0),
            'teachers' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND school_id = ?", [$id], 0),
            'directors' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'principal' AND school_id = ?", [$id], 0),
            'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE school_id = ?", [$id], 0),
            'enrollments' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE c.school_id = ?", [$id], 0),
            'exams' => (int)Database::scalar("SELECT COUNT(*) FROM exams e JOIN courses c ON c.id = e.course_id WHERE c.school_id = ?", [$id], 0),
            'attendance' => (int)Database::scalar("SELECT COUNT(*) FROM attendance a JOIN users st ON st.id = a.student_id WHERE st.school_id = ?", [$id], 0),
            'ai' => (int)Database::scalar("SELECT COUNT(*) FROM ai_messages m JOIN ai_chats ac ON ac.id = m.chat_id JOIN users us ON us.id = ac.user_id WHERE us.school_id = ?", [$id], 0),
        ];
        $recent = Database::all(
            "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name, u.role, u.status, u.last_login
             FROM users u WHERE u.school_id = ? AND u.role IN ('teacher', 'principal')
             ORDER BY u.last_login DESC LIMIT 8", [$id]);
        Router::render('app/regional/school', ['title' => $school['name'], 'school' => $school, 'stats' => $stats, 'recent' => $recent]);
    }

    private function directors(array $u): void {
        $uid = (int)$u['id'];
        $idList = RegionalScope::idList($uid);
        $schools = RegionalScope::schools($uid);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_director'])) {
                $schoolId = (int)($_POST['school_id'] ?? 0);
                RegionalScope::requireSchool($uid, $schoolId);
                $email = trim((string)($_POST['email'] ?? ''));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || Database::one("SELECT id FROM users WHERE email = ?", [$email])) {
                    flash('danger', 'Invalid or already-used email address.');
                    redirect('regional/directors');
                }
                $pass = $_POST['password'] !== '' ? (string)$_POST['password'] : random_password();
                Database::insert('users', [
                    'school_id' => $schoolId, 'role' => 'principal', 'first_name' => trim((string)$_POST['first_name']),
                    'last_name' => trim((string)$_POST['last_name']), 'email' => $email, 'phone' => trim((string)$_POST['phone'] ?? ''),
                    'password_hash' => password_hash($pass, PASSWORD_BCRYPT), 'status' => 'active',
                ]);
                $newId = Database::insertId();
                notify((int)$newId, 'system', 'Welcome, Director', 'Your account was created by your regional admin. Email: ' . $email, 'director/dashboard');
                log_activity('director.create', 'Regional admin ' . $u['email'] . ' created director ' . $email, $uid);
                flash('success', 'Director created — temporary password: ' . $pass);
                redirect('regional/directors');
            }
            if (isset($_POST['toggle_director'])) {
                $did = (int)($_POST['id'] ?? 0);
                $d = Database::one("SELECT * FROM users WHERE id = ? AND role = 'principal'", [$did]);
                if (!$d) { flash('danger', 'Director not found.'); redirect('regional/directors'); }
                RegionalScope::requireSchool($uid, (int)$d['school_id']);
                $newStatus = ($d['status'] ?? 'active') === 'active' ? 'suspended' : 'active';
                Database::update('users', ['status' => $newStatus], 'id = ?', [$did]);
                log_activity('director.toggle', 'Regional admin set director #' . $did . ' to ' . $newStatus, $uid);
                flash('success', 'Director ' . ($newStatus === 'suspended' ? 'suspended' : 'reactivated') . '.');
                redirect('regional/directors');
            }
        }
        $rows = Database::all(
            "SELECT u.*, sc.name AS school_name
             FROM users u JOIN schools sc ON sc.id = u.school_id
             WHERE u.role = 'principal' AND u.school_id IN ($idList) ORDER BY sc.name, u.first_name", []);
        Router::render('app/regional/directors', ['title' => 'Directors', 'rows' => $rows, 'schools' => $schools]);
    }

    private function director(array $u): void {
        $uid = (int)$u['id'];
        $id = (int)($_GET['id'] ?? 0);
        $d = Database::one("SELECT u.*, sc.name AS school_name FROM users u JOIN schools sc ON sc.id = u.school_id WHERE u.id = ? AND u.role = 'principal'", [$id]);
        if (!$d) { flash('danger', 'Director not found.'); redirect('regional/directors'); }
        RegionalScope::requireSchool($uid, (int)$d['school_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['reset_password'])) {
                $pass = random_password();
                Database::update('users', ['password_hash' => password_hash($pass, PASSWORD_BCRYPT)], 'id = ?', [$id]);
                log_activity('director.reset', 'Regional admin reset password for director #' . $id, $uid);
                flash('success', 'Password reset — temporary password: ' . $pass);
                redirect('regional/director&id=' . $id);
            }
            if (isset($_POST['transfer_director'])) {
                $toId = (int)($_POST['to_school'] ?? 0);
                $target = RegionalScope::requireSchool($uid, $toId);
                $owned = (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE teacher_id = ? AND school_id = ?", [$id, $d['school_id']], 0);
                if ($owned > 0) {
                    flash('danger', 'Transfer blocked — this director still owns ' . $owned . ' course(s) in their current school.');
                    redirect('regional/director&id=' . $id);
                }
                Database::update('users', ['school_id' => $toId], 'id = ?', [$id]);
                log_activity('director.transfer', 'Regional admin moved director #' . $id . ' to ' . $target['name'], $uid);
                flash('success', 'Director transferred to ' . $target['name'] . '.');
                redirect('regional/director&id=' . $id);
            }
        }
        $schools = RegionalScope::schools($uid);
        $log = Database::all(
            "SELECT action, detail, created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);
        Router::render('app/regional/director', ['title' => $d['first_name'] . ' ' . $d['last_name'], 'd' => $d, 'schools' => $schools, 'log' => $log]);
    }

    private function analytics(array $u): void {
        $uid = (int)$u['id'];
        $idList = RegionalScope::idList($uid);
        $schools = RegionalScope::schools($uid);
        $schoolIds = $schools ? array_map(fn($s) => (int)$s['id'], $schools) : [0];

        $perSchool = [];
        foreach ($schoolIds as $scId) {
            $perSchool[] = [
                'id' => $scId,
                'name' => (string)Database::scalar("SELECT name FROM schools WHERE id = ?", [$scId], ''),
                'students' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student' AND school_id = ?", [$scId], 0),
                'teachers' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND school_id = ?", [$scId], 0),
                'directors' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'principal' AND school_id = ?", [$scId], 0),
                'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE school_id = ?", [$scId], 0),
                'enrollments' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE c.school_id = ?", [$scId], 0),
                'exams' => (int)Database::scalar("SELECT COUNT(*) FROM exams e JOIN courses c ON c.id = e.course_id WHERE c.school_id = ?", [$scId], 0),
                'attendance' => (int)Database::scalar("SELECT COUNT(*) FROM attendance a JOIN users st ON st.id = a.student_id WHERE st.school_id = ?", [$scId], 0),
                'ai' => (int)Database::scalar("SELECT COUNT(*) FROM ai_messages m JOIN ai_chats ac ON ac.id = m.chat_id JOIN users us ON us.id = ac.user_id WHERE us.school_id = ?", [$scId], 0),
            ];
        }
        $totals = [
            'students' => array_sum(array_column($perSchool, 'students')),
            'teachers' => array_sum(array_column($perSchool, 'teachers')),
            'directors' => array_sum(array_column($perSchool, 'directors')),
            'courses' => array_sum(array_column($perSchool, 'courses')),
            'enrollments' => array_sum(array_column($perSchool, 'enrollments')),
            'exams' => array_sum(array_column($perSchool, 'exams')),
            'attendance' => array_sum(array_column($perSchool, 'attendance')),
            'ai' => array_sum(array_column($perSchool, 'ai')),
            'schools' => count($schools),
        ];
        $workload = [
            'schools' => $totals['schools'],
            'schoolCap' => 15,
            'schoolPct' => (int)round($totals['schools'] / 15 * 100),
            'directors' => $totals['directors'],
            'dirCap' => $totals['schools'] * 2,
            'dirPct' => $totals['schools'] ? (int)round($totals['directors'] / ($totals['schools'] * 2) * 100) : 0,
            'recommended' => max((int)ceil($totals['schools'] / 15), $totals['schools'] ? (int)ceil($totals['directors'] / ($totals['schools'] * 2)) : 1, 1),
        ];
        Router::render('app/regional/analytics', [
            'title' => 'Regional Analytics', 'perSchool' => $perSchool, 'totals' => $totals, 'workload' => $workload,
        ]);
    }

    private function announcements(array $u): void {
        $uid = (int)$u['id'];
        $idList = RegionalScope::idList($uid);
        $schools = RegionalScope::schools($uid);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $schoolId = (int)($_POST['school_id'] ?? 0);
            RegionalScope::requireSchool($uid, $schoolId);
            $title = trim((string)$_POST['title']);
            $content = trim((string)$_POST['content']);
            $audience = in_array($_POST['audience'] ?? 'all', ['all', 'students', 'teachers', 'parents'], true) ? $_POST['audience'] : 'all';
            $aid = Database::insert('announcements', [
                'school_id' => $schoolId, 'author_id' => $uid,
                'title' => $title, 'content' => $content, 'audience' => $audience,
            ]);
            $roleMap = ['all' => null, 'students' => 'student', 'teachers' => 'teacher', 'parents' => 'parent'];
            $role = $roleMap[$audience] ?? null;
            $targets = $role
                ? Database::all("SELECT id FROM users WHERE role = ? AND school_id = ?", [$role, $schoolId])
                : Database::all("SELECT id FROM users WHERE role != 'guest' AND school_id = ?", [$schoolId]);
            foreach ($targets as $t) {
                notify((int)$t['id'], 'announcement', $title, mb_strimwidth($content, 0, 120, '…'), 'communication/announcement&id=' . $aid);
            }
            log_activity('announcement.create', 'Regional admin announced to school #' . $schoolId, $uid);
            flash('success', 'Announcement published to ' . count($targets) . ' users.');
            redirect('regional/announcements');
        }
        $rows = Database::all(
            "SELECT a.*, sc.name AS school_name FROM announcements a JOIN schools sc ON sc.id = a.school_id
             WHERE a.school_id IN ($idList) ORDER BY a.created_at DESC LIMIT 30", []);
        Router::render('app/regional/announcements', ['title' => 'Announcements', 'rows' => $rows, 'schools' => $schools]);
    }

    private function backups(array $u): void {
        $backups = [];
        $dir = STORAGE_PATH . '/backups';
        if (is_dir($dir)) {
            foreach (glob($dir . '/*.sql') ?: [] as $f) {
                $backups[] = ['file' => basename($f), 'size' => filesize($f), 'time' => filemtime($f)];
            }
        }
        usort($backups, fn($a, $b) => $b['time'] <=> $a['time']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_backup'])) {
                if (!is_dir($dir)) @mkdir($dir, 0775, true);
                $file = $dir . '/edunex_' . date('Ymd_His') . '.sql';
                $db = DB_NAME;
                // Use temp config file to avoid password in process list
                $mycnf = tempnam(sys_get_temp_dir(), 'mycnf');
                file_put_contents($mycnf, "[client]\nhost=" . DB_HOST . "\nuser=" . DB_USER . "\npassword=" . DB_PASS . "\n");
                chmod($mycnf, 0600);
                $cmd = sprintf('mysqldump --no-defaults --defaults-extra-file=%s %s > %s 2>&1',
                    escapeshellarg($mycnf), escapeshellarg($db), escapeshellarg($file));
                exec($cmd, $out, $code);
                @unlink($mycnf);
                if ($code === 0 && is_file($file) && filesize($file) > 0) {
                    flash('success', 'Backup created: ' . basename($file));
                } else {
                    @unlink($file);
                    $err = implode("\n", $out);
                    flash('danger', 'Backup failed (exit ' . $code . '): ' . ($err ?: 'Check mysqldump.'));
                }
                redirect('regional/backups');
            }
            if (isset($_POST['download_backup'])) {
                $name = basename($_POST['file'] ?? '');
                $path = $dir . '/' . $name;
                if (preg_match('/^edunex_.*\.sql$/', $name) && is_file($path)) {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $name . '"');
                    header('Content-Length: ' . filesize($path));
                    readfile($path);
                    exit;
                }
                flash('danger', 'File not found.');
                redirect('regional/backups');
            }
            if (isset($_POST['rename_backup'])) {
                $old = basename($_POST['old_name'] ?? '');
                $new = trim($_POST['new_name'] ?? '');
                if ($old && $new && preg_match('/^edunex_.*\.sql$/', $old) && preg_match('/^[\w\-]+\.sql$/', $new)) {
                    $oldPath = $dir . '/' . $old;
                    $newPath = $dir . '/' . $new;
                    if (is_file($oldPath) && !is_file($newPath)) {
                        rename($oldPath, $newPath);
                        flash('success', 'Renamed to ' . $new);
                    } else { flash('danger', 'Cannot rename.'); }
                } else { flash('danger', 'Invalid name.'); }
                redirect('regional/backups');
            }
            if (($del = $_POST['delete_backup'] ?? '')) {
                $delFile = basename($del);
                if (preg_match('/^edunex_.*\.sql$/', $delFile) && is_file($dir . '/' . $delFile)) {
                    @unlink($dir . '/' . $delFile);
                    flash('success', 'Backup deleted.');
                } else {
                    flash('danger', 'Invalid backup file.');
                }
                redirect('regional/backups');
            }
        }

        Router::render('app/regional/backups', ['title' => 'Backups', 'backups' => $backups]);
    }

    private function audit(array $u): void {
        $uid = (int)$u['id'];
        $idList = RegionalScope::idList($uid);
        $export = $_GET['export'] ?? '';
        $rows = Database::all(
            "SELECT al.*, u.first_name, u.last_name, u.email, sc.name AS school_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             LEFT JOIN schools sc ON sc.id = u.school_id
             WHERE u.school_id IN ($idList)
             ORDER BY al.created_at DESC LIMIT 100", []);

        if ($export === 'md') {
            header('Content-Type: text/markdown');
            header('Content-Disposition: attachment; filename="edunex_audit_' . date('Ymd_His') . '.md"');
            echo "# Edunex Audit Log — Regional Admin\n\n";
            echo "**Generated:** " . date('F j, Y \a\t g:i A') . " · " . count($rows) . " records\n\n";
            echo "| # | User | School | Action | Detail | When |\n";
            echo "|---|------|--------|--------|--------|------|\n";
            $rn = 0;
            foreach ($rows as $a) {
                $rn++;
                $name = e(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
                echo "| $rn | $name | " . e($a['school_name'] ?? '—') . " | " . e($a['action']) . " | " . e($a['detail']) . " | " . e(time_ago($a['created_at'])) . " |\n";
            }
            echo "\n---\n**Henok Akriso** · henokakriso.com\n\nAll system is opensourced under [ARWE-PL License](https://github.com/henokakriso/Edunex)\n";
            exit;
        }

        if ($export === 'pdf') {
            $stamp = date('F j, Y \a\t g:i A');
            $title = 'Edunex Audit Log — Regional Admin';
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . e($title) . '</title>';
            echo '<style>';
            echo '*{box-sizing:border-box;margin:0;padding:0}body{font-family:system-ui,-apple-system,sans-serif;background:#f5f5f5;color:#222}';
            echo '.viewer-bar{position:sticky;top:0;z-index:100;background:#1a1a2e;color:#fff;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,.2)}';
            echo '.viewer-bar h1{font-size:15px;font-weight:600}.viewer-bar .btns{display:flex;gap:10px}';
            echo '.viewer-bar a,.viewer-bar button{background:#4361ee;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px}';
            echo '.viewer-bar a:hover,.viewer-bar button:hover{background:#3a56d4}.viewer-bar .btn-secondary{background:#555}.viewer-bar .btn-secondary:hover{background:#444}';
            echo '.report{max-width:1100px;margin:24px auto;background:#fff;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden}';
            echo '.report-header{padding:28px 32px 20px;border-bottom:2px solid #eee}';
            echo '.report-header h2{font-size:20px;margin-bottom:4px}.report-header .meta{color:#666;font-size:12px}';
            echo '.table-wrap{overflow-x:auto}';
            echo 'table{width:100%;border-collapse:collapse;min-width:700px}th,td{padding:8px 10px;text-align:left;border-bottom:1px solid #eee;font-size:12px;white-space:nowrap;max-width:220px;overflow:hidden;text-overflow:ellipsis}';
            echo 'th{background:#f8f9fa;font-weight:600;color:#444;position:sticky;top:0;z-index:2}';
            echo '.row-num{color:#999;width:36px;text-align:center;max-width:36px}';
            echo '.badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;background:#e8e8e8}';
            echo '.footer{padding:16px 32px;border-top:2px solid #eee;text-align:center;color:#888;font-size:11px;line-height:1.6}';
            echo '.footer a{color:#4361ee;text-decoration:none}';
            echo '@media print{.viewer-bar{display:none!important}.report{box-shadow:none;margin:0}body{background:#fff}.table-wrap{overflow:visible}table{min-width:0}th,td{white-space:normal;word-break:break-word}}';
            echo '</style></head><body>';
            echo '<div class="viewer-bar"><h1>' . e($title) . '</h1>';
            echo '<div class="btns"><button class="btn-secondary" onclick="history.back()">← Back</button>';
            echo '<a href="javascript:window.print()">🖨 Print</a>';
            echo '<a href="javascript:downloadPDF()">⬇ Download PDF</a>';
            echo '<a href="' . e(url('regional/audit&export=md')) . '">📄 Markdown</a></div></div>';
            echo '<div class="report"><div class="report-header"><h2>' . e($title) . '</h2>';
            echo '<p class="meta">Generated: ' . e($stamp) . ' · ' . count($rows) . ' records</p></div>';
            echo '<div class="table-wrap"><table><thead><tr><th class="row-num">#</th><th>User</th><th>School</th><th>Action</th><th>Detail</th><th>When</th></tr></thead><tbody>';
            $rn = 0;
            foreach ($rows as $a) {
                $rn++;
                $name = e(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
                echo '<tr><td class="row-num">' . $rn . '</td>';
                echo '<td>' . $name . '<br><span class="tiny faint">' . e($a['email'] ?? '') . '</span></td>';
                echo '<td>' . e($a['school_name'] ?? '—') . '</td>';
                echo '<td><span class="badge">' . e($a['action']) . '</span></td>';
                echo '<td>' . e($a['detail']) . '</td>';
                echo '<td>' . e(time_ago($a['created_at'])) . '</td></tr>';
            }
            echo '</tbody></table></div>';
            echo '<div class="footer"><p><b>Henok Akriso</b> · henokakriso.com</p>';
            echo '<p>All system is opensourced under <a href="https://github.com/henokakriso/Edunex" target="_blank">ARWE-PL License</a></p></div></div>';
            echo '<script>function downloadPDF(){var opt={margin:[10,10],filename:"edunex_audit_' . date('Ymd_His') . '.pdf",html2canvas:{scale:2},jsPDF:{unit:"mm",format:"a4",orientation:"landscape"}};if(typeof html2pdf!=="undefined"){html2pdf().set(opt).from(document.querySelector(".report")).save();}else{var s=document.createElement("script");s.src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js";s.onload=function(){html2pdf().set(opt).from(document.querySelector(".report")).save();};document.head.appendChild(s);}}</script>';
            echo '</body></html>';
            exit;
        }

        Router::render('app/regional/audit', ['title' => 'Audit Log', 'rows' => $rows]);
    }
}
