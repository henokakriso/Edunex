<?php
class ZoneScope {
    public static function woredas(int $adminId): array {
        return Database::all("SELECT * FROM woredas WHERE admin_id = ? AND status != 'archived' ORDER BY name", [$adminId]);
    }
    public static function woredaIds(int $adminId): array {
        return array_map('intval', array_column(self::woredas($adminId), 'id'));
    }
    public static function schools(int $adminId): array {
        $ids = self::woredaIds($adminId);
        if (!$ids) return [];
        $in = implode(',', $ids);
        return Database::all("SELECT * FROM schools WHERE woreda_id IN ($in) AND status != 'archived' ORDER BY name");
    }
    public static function ids(int $adminId): array {
        return array_map('intval', array_column(self::schools($adminId), 'id'));
    }
    public static function idList(int $adminId): string {
        $ids = self::ids($adminId);
        return $ids ? implode(',', $ids) : '0';
    }
    public static function requireSchool(int $adminId, int $schoolId): array {
        $s = Database::one("SELECT * FROM schools WHERE id = ? AND woreda_id IN (SELECT id FROM woredas WHERE admin_id = ?) AND status != 'archived'", [$schoolId, $adminId]);
        if (!$s) { http_response_code(403); die('Access denied.'); }
        return $s;
    }
}

class Ctl_zonal {
    public function run(): void {
        $u = require_role('zonal_admin');
        $action = trim($_GET['r'] ?? '', '/');
        $route = str_replace('zonal/', '', $action);
        match ($route) {
            'dashboard' => $this->dashboard($u),
            'woredas' => $this->woredas($u),
            'woreda' => $this->woredaDetail($u),
            'schools' => $this->schools($u),
            'school' => $this->school($u),
            'directors' => $this->directors($u),
            'director' => $this->director($u),
            'analytics' => $this->analytics($u),
            'announcements' => $this->announcements($u),
            'audit' => $this->audit($u),
            default => $this->dashboard($u),
        };
    }

    private function stat(string $sql, array $args = [], int $d = 0): int {
        return (int)Database::scalar($sql, $args, $d);
    }

    private function dashboard(array $u): void {
        $uid = (int)$u['id'];
        $idList = ZoneScope::idList($uid);
        $schools = ZoneScope::schools($uid);
        $woredas = ZoneScope::woredas($uid);
        $s = [
            'schools' => count($schools), 'woredas' => count($woredas),
            'students' => $this->stat("SELECT COUNT(*) FROM users WHERE role='student' AND school_id IN ($idList)"),
            'teachers' => $this->stat("SELECT COUNT(*) FROM users WHERE role='teacher' AND school_id IN ($idList)"),
            'directors' => $this->stat("SELECT COUNT(*) FROM users WHERE role='director' AND school_id IN ($idList)"),
            'courses' => $this->stat("SELECT COUNT(*) FROM courses WHERE school_id IN ($idList)"),
            'enroll30' => $this->stat("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id=ce.course_id WHERE c.school_id IN ($idList) AND ce.enrolled_at>=NOW()-INTERVAL 30 DAY"),
            'pending_transfers' => $this->stat("SELECT COUNT(*) FROM transfer_requests WHERE status='pending' AND to_school_id IN ($idList)"),
        ];
        $schoolStats = [];
        foreach ($schools as $sc) {
            $sid = (int)$sc['id'];
            $schoolStats[] = [
                'school' => $sc,
                'students' => $this->stat("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=?", [$sid]),
                'teachers' => $this->stat("SELECT COUNT(*) FROM users WHERE role='teacher' AND school_id=?", [$sid]),
                'directors' => $this->stat("SELECT COUNT(*) FROM users WHERE role='director' AND school_id=?", [$sid]),
            ];
        }
        Router::render('app/zonal/dashboard', [
            'title' => 'Zone Overview', 'stats' => $s, 'schoolStats' => $schoolStats, 'woredas' => $woredas,
        ]);
    }

    private function woredas(array $u): void {
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_woreda'])) {
                $name = trim((string)($_POST['name'] ?? ''));
                if ($name === '') { flash('danger', 'Name required.'); redirect('zonal/woredas'); }
                $zoneId = (int)Database::scalar("SELECT id FROM zones WHERE admin_id=?", [$uid], 0);
                Database::insert('woredas', ['zone_id' => $zoneId, 'name' => $name, 'admin_id' => null]);
                log_activity('woreda.create', 'Created woreda: '.$name, $uid);
                flash('success', 'Woreda created.'); redirect('zonal/woredas');
            }
            if (isset($_POST['assign_woreda'])) {
                $wid = (int)($_POST['woreda_id'] ?? 0);
                $aid = (int)($_POST['admin_id'] ?? 0);
                Database::update('woredas', ['admin_id' => $aid ?: null], 'id=?', [$wid]);
                log_activity('woreda.assign', "Assigned woreda #$wid to admin #$aid", $uid);
                flash('success', 'Assigned.'); redirect('zonal/woredas');
            }
        }
        $zoneId = (int)Database::scalar("SELECT id FROM zones WHERE admin_id=?", [$uid], 0);
        $woredas = Database::all(
            "SELECT w.*, (SELECT COUNT(*) FROM schools WHERE woreda_id=w.id) AS schools
             FROM woredas w WHERE w.zone_id=? ORDER BY w.name", [$zoneId]);
        $admins = Database::all("SELECT id, first_name, last_name, email FROM users WHERE role='woreda_admin' AND school_id IS NULL ORDER BY first_name");
        Router::render('app/zonal/woredas', ['title' => 'Woredas', 'woredas' => $woredas, 'admins' => $admins]);
    }

    private function woredaDetail(array $u): void {
        $uid = (int)$u['id'];
        $wid = (int)($_GET['id'] ?? 0);
        $zoneId = (int)Database::scalar("SELECT id FROM zones WHERE admin_id=?", [$uid], 0);
        $woreda = Database::one("SELECT * FROM woredas WHERE id=? AND zone_id=?", [$wid, $zoneId]);
        if (!$woreda) { flash('danger', 'Not found.'); redirect('zonal/woredas'); }
        $schools = Database::all(
            "SELECT s.*, (SELECT COUNT(*) FROM users WHERE role='student' AND school_id=s.id) AS students,
                    (SELECT COUNT(*) FROM users WHERE role='teacher' AND school_id=s.id) AS teachers
             FROM schools s WHERE s.woreda_id=? ORDER BY s.name", [$wid]);
        Router::render('app/zonal/woreda', ['title' => $woreda['name'], 'woreda' => $woreda, 'schools' => $schools]);
    }

    private function schools(array $u): void {
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $id = (int)($_POST['school_id'] ?? 0);
            ZoneScope::requireSchool($uid, $id);
            $action = (string)($_POST['action'] ?? '');
            $status = $action === 'suspend' ? 'suspended' : ($action === 'archive' ? 'archived' : 'active');
            Database::update('schools', ['status' => $status], 'id=?', [$id]);
            log_activity('school.'.$status, "Set school #$id to $status", $uid);
            flash('success', 'Updated.'); redirect('zonal/schools');
        }
        $rows = Database::all(
            "SELECT sc.*, w.name AS woreda_name,
                    (SELECT COUNT(*) FROM users WHERE role='director' AND school_id=sc.id) AS directors,
                    (SELECT COUNT(*) FROM users WHERE role='student' AND school_id=sc.id) AS students,
                    (SELECT COUNT(*) FROM users WHERE role='teacher' AND school_id=sc.id) AS teachers
             FROM schools sc LEFT JOIN woredas w ON w.id=sc.woreda_id
             WHERE sc.woreda_id IN (SELECT id FROM woredas WHERE zone_id=(SELECT id FROM zones WHERE admin_id=?))
             ORDER BY sc.status, sc.name", [$uid]);
        Router::render('app/zonal/schools', ['title' => 'Zone Schools', 'rows' => $rows]);
    }

    private function school(array $u): void {
        $uid = (int)$u['id'];
        $id = (int)($_GET['id'] ?? 0);
        $school = ZoneScope::requireSchool($uid, $id);
        $stats = [
            'students' => $this->stat("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=?", [$id]),
            'teachers' => $this->stat("SELECT COUNT(*) FROM users WHERE role='teacher' AND school_id=?", [$id]),
            'directors' => $this->stat("SELECT COUNT(*) FROM users WHERE role='director' AND school_id=?", [$id]),
            'courses' => $this->stat("SELECT COUNT(*) FROM courses WHERE school_id=?", [$id]),
        ];
        $recent = Database::all(
            "SELECT CONCAT(u.first_name,' ',u.last_name) AS name, u.role, u.status, u.last_login
             FROM users u WHERE u.school_id=? AND u.role IN ('teacher','director') ORDER BY u.last_login DESC LIMIT 8", [$id]);
        Router::render('app/zonal/school', ['title' => $school['name'], 'school' => $school, 'stats' => $stats, 'recent' => $recent]);
    }

    private function directors(array $u): void {
        $uid = (int)$u['id'];
        $schools = ZoneScope::schools($uid);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_director'])) {
                $schoolId = (int)($_POST['school_id'] ?? 0);
                ZoneScope::requireSchool($uid, $schoolId);
                $email = trim((string)($_POST['email'] ?? ''));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || Database::one("SELECT id FROM users WHERE email=?", [$email])) {
                    flash('danger', 'Invalid or used email.'); redirect('zonal/directors');
                }
                $pass = ($_POST['password'] ?? '') !== '' ? (string)$_POST['password'] : random_password();
                Database::insert('users', [
                    'school_id' => $schoolId, 'role' => 'director',
                    'first_name' => trim((string)$_POST['first_name']),
                    'last_name' => trim((string)$_POST['last_name']),
                    'email' => $email, 'phone' => trim((string)($_POST['phone'] ?? '')),
                    'password_hash' => password_hash($pass, PASSWORD_BCRYPT), 'status' => 'active',
                ]);
                $nid = Database::insertId();
                notify((int)$nid, 'system', 'Welcome', 'Your account was created.', 'director/dashboard');
                log_activity('director.create', 'Created director '.$email, $uid);
                flash('success', 'Director created — password: '.$pass); redirect('zonal/directors');
            }
            if (isset($_POST['toggle_director'])) {
                $did = (int)($_POST['id'] ?? 0);
                $d = Database::one("SELECT * FROM users WHERE id=? AND role='director'", [$did]);
                if (!$d) { flash('danger', 'Not found.'); redirect('zonal/directors'); }
                ZoneScope::requireSchool($uid, (int)$d['school_id']);
                $ns = ($d['status'] ?? 'active') === 'active' ? 'suspended' : 'active';
                Database::update('users', ['status' => $ns], 'id=?', [$did]);
                log_activity('director.toggle', "Set director #$did to $ns", $uid);
                flash('success', 'Director '.$ns.'.'); redirect('zonal/directors');
            }
        }
        $rows = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.status, u.last_login, s.name AS school_name
             FROM users u JOIN schools s ON s.id=u.school_id
             WHERE u.role='director' AND u.school_id IN (".ZoneScope::idList($uid).")
             ORDER BY s.name", []);
        Router::render('app/zonal/directors', ['title' => 'Directors', 'rows' => $rows, 'schools' => $schools]);
    }

    private function director(array $u): void {
        $uid = (int)$u['id'];
        $id = (int)($_GET['id'] ?? 0);
        $d = Database::one("SELECT u.*, s.name AS school_name FROM users u JOIN schools s ON s.id=u.school_id WHERE u.id=? AND u.role='director'", [$id]);
        if (!$d) { flash('danger', 'Not found.'); redirect('zonal/directors'); }
        ZoneScope::requireSchool($uid, (int)$d['school_id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['reset_password'])) {
                $pass = random_password();
                Database::update('users', ['password_hash' => password_hash($pass, PASSWORD_BCRYPT)], 'id=?', [$id]);
                log_activity('director.password', "Reset director #$id password", $uid);
                flash('success', 'New password: '.$pass); redirect('zonal/director?id='.$id);
            }
        }
        $activity = Database::all("SELECT * FROM activity_log WHERE user_id=? ORDER BY created_at DESC LIMIT 20", [$id]);
        Router::render('app/zonal/director', ['title' => $d['first_name'].' '.$d['last_name'], 'director' => $d, 'activity' => $activity]);
    }

    private function analytics(array $u): void {
        $uid = (int)$u['id'];
        $idList = ZoneScope::idList($uid);
        $schools = ZoneScope::schools($uid);
        $schoolStats = [];
        foreach ($schools as $sc) {
            $sid = (int)$sc['id'];
            $studentCount = $this->stat("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=?", [$sid]);
            $teacherCount = $this->stat("SELECT COUNT(*) FROM users WHERE role='teacher' AND school_id=?", [$sid]);
            $courseCount = $this->stat("SELECT COUNT(*) FROM courses WHERE school_id=?", [$sid]);
            $enrollCount = $this->stat("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id=ce.course_id WHERE c.school_id=?", [$sid]);
            $schoolStats[] = [
                'school' => $sc, 'students' => $studentCount, 'teachers' => $teacherCount,
                'courses' => $courseCount, 'enrollments' => $enrollCount,
                'student_teacher_ratio' => $teacherCount > 0 ? round($studentCount / $teacherCount, 1) : 0,
            ];
        }
        Router::render('app/zonal/analytics', ['title' => 'Zone Analytics', 'schoolStats' => $schoolStats]);
    }

    private function announcements(array $u): void {
        $uid = (int)$u['id'];
        $idList = ZoneScope::idList($uid);
        $schools = ZoneScope::schools($uid);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $title = trim((string)($_POST['title'] ?? ''));
            $body = trim((string)($_POST['body'] ?? ''));
            if ($title === '' || $body === '') { flash('danger', 'Title and body required.'); redirect('zonal/announcements'); }
            $target = $_POST['target'] ?? 'all';
            Database::insert('announcements', [
                'school_id' => null, 'title' => $title, 'body' => $body, 'target_role' => $target,
                'posted_by' => $uid, 'is_global' => 0, 'pinned' => 0,
            ]);
            log_activity('announcement.create', 'Zone announcement: '.$title, $uid);
            flash('success', 'Announcement posted.'); redirect('zonal/announcements');
        }
        $list = Database::all(
            "SELECT a.*, CONCAT(u.first_name,' ',u.last_name) AS posted_by_name
             FROM announcements a LEFT JOIN users u ON u.id=a.posted_by
             WHERE a.posted_by=? ORDER BY a.created_at DESC LIMIT 50", [$uid]);
        Router::render('app/zonal/announcements', ['title' => 'Announcements', 'announcements' => $list, 'schools' => $schools]);
    }

    private function audit(array $u): void {
        $uid = (int)$u['id'];
        $idList = ZoneScope::idList($uid);
        $logs = Database::all(
            "SELECT al.*, CONCAT(u.first_name,' ',u.last_name) AS user_name
             FROM activity_log al LEFT JOIN users u ON u.id=al.user_id
             WHERE (al.user_id IN (SELECT id FROM users WHERE school_id IN ($idList))
                    OR al.user_id=?)
             ORDER BY al.created_at DESC LIMIT 200", [$uid]);
        Router::render('app/zonal/audit', ['title' => 'Audit Log', 'logs' => $logs]);
    }
}
