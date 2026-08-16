<?php
/**
 * Regional Admin (role 'admin') — supervises only the schools assigned to them.
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
        $u = require_role('admin');
        $uid = (int)$u['id'];
        $action = trim($_GET['r'] ?? '', '/');
        $route = str_replace('regional/', '', $action);

        match ($route) {
            'dashboard' => $this->dashboard($u),
            'schools' => $this->schools($u),
            'school' => $this->school($u),
            'directors' => $this->directors($u),
            'director' => $this->director($u),
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
            'directors' => $this->stat("SELECT COUNT(*) FROM users WHERE role = 'director' AND school_id IN ($idList)"),
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
                'directors' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'director' AND school_id = ?", [$sc['id']], 0),
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
                    (SELECT COUNT(*) FROM users WHERE role = 'director' AND school_id = sc.id) AS directors,
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
            'directors' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'director' AND school_id = ?", [$id], 0),
            'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE school_id = ?", [$id], 0),
            'enrollments' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE c.school_id = ?", [$id], 0),
            'exams' => (int)Database::scalar("SELECT COUNT(*) FROM exams e JOIN courses c ON c.id = e.course_id WHERE c.school_id = ?", [$id], 0),
            'attendance' => (int)Database::scalar("SELECT COUNT(*) FROM attendance a JOIN users st ON st.id = a.student_id WHERE st.school_id = ?", [$id], 0),
            'ai' => (int)Database::scalar("SELECT COUNT(*) FROM ai_messages m JOIN ai_chats ac ON ac.id = m.chat_id JOIN users us ON us.id = ac.user_id WHERE us.school_id = ?", [$id], 0),
        ];
        $recent = Database::all(
            "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name, u.role, u.status, u.last_login
             FROM users u WHERE u.school_id = ? AND u.role IN ('teacher', 'director')
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
                    'school_id' => $schoolId, 'role' => 'director', 'first_name' => trim((string)$_POST['first_name']),
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
                $d = Database::one("SELECT * FROM users WHERE id = ? AND role = 'director'", [$did]);
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
             WHERE u.role = 'director' AND u.school_id IN ($idList) ORDER BY sc.name, u.first_name", []);
        Router::render('app/regional/directors', ['title' => 'Directors', 'rows' => $rows, 'schools' => $schools]);
    }

    private function director(array $u): void {
        $uid = (int)$u['id'];
        $id = (int)($_GET['id'] ?? 0);
        $d = Database::one("SELECT u.*, sc.name AS school_name FROM users u JOIN schools sc ON sc.id = u.school_id WHERE u.id = ? AND u.role = 'director'", [$id]);
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
                'directors' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'director' AND school_id = ?", [$scId], 0),
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
            Database::insert('announcements', [
                'school_id' => $schoolId, 'author_id' => $uid,
                'title' => trim((string)$_POST['title']), 'content' => trim((string)$_POST['content']),
                'audience' => in_array($_POST['audience'] ?? 'all', ['all', 'students', 'teachers', 'parents'], true) ? $_POST['audience'] : 'all',
            ]);
            log_activity('announcement.create', 'Regional admin announced to school #' . $schoolId, $uid);
            flash('success', 'Announcement published.');
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
        Router::render('app/regional/backups', ['title' => 'Backups', 'backups' => $backups]);
    }

    private function audit(array $u): void {
        $uid = (int)$u['id'];
        $idList = RegionalScope::idList($uid);
        $rows = Database::all(
            "SELECT al.*, u.first_name, u.last_name, u.email, sc.name AS school_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             LEFT JOIN schools sc ON sc.id = u.school_id
             WHERE u.school_id IN ($idList)
             ORDER BY al.created_at DESC LIMIT 100", []);
        Router::render('app/regional/audit', ['title' => 'Audit Log', 'rows' => $rows]);
    }
}
