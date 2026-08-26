<?php
/**
 * Admin module: dashboard, users, schools, departments, subjects,
 * groups, academic years, courses
 */

/* =============== ADMIN: dashboard =============== */
class Ctl_dashboard {
    public function run(): void {
        $u = require_role('ministry');
        $dfCo = demo_filter('c');
        $stats = [
            'schools' => (int)Database::scalar("SELECT COUNT(*) FROM schools", [], 0),
            'students' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student'", [], 0),
            'teachers' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'teacher'", [], 0),
            'parents' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'parent'", [], 0),
            'directors' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'principal'", [], 0),
            'active_users' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE status = 'active'", [], 0),
            'online' => (int)Database::scalar("SELECT COUNT(DISTINCT user_id) FROM login_history WHERE status = 'success' AND created_at > NOW() - INTERVAL 15 MINUTE", [], 0),
            'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses c WHERE 1=1 $dfCo", [], 0),
            'subjects' => (int)Database::scalar("SELECT COUNT(*) FROM subjects", [], 0),
            'departments' => (int)Database::scalar("SELECT COUNT(*) FROM departments", [], 0),
            'library' => (int)Database::scalar("SELECT COUNT(*) FROM library_items", [], 0),
            'exams' => (int)Database::scalar("SELECT COUNT(*) FROM exams", [], 0),
            'announcements' => (int)Database::scalar("SELECT COUNT(*) FROM announcements", [], 0),
            'messages' => (int)Database::scalar("SELECT COUNT(*) FROM messages", [], 0),
            'transfers' => (int)Database::scalar("SELECT COUNT(*) FROM transfer_requests", [], 0),
            'attendance_today' => (int)Database::scalar("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()", [], 0),
            'pending' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE status = 'pending'", [], 0) + (int)Database::scalar("SELECT COUNT(*) FROM transfer_requests WHERE status = 'pending'", [], 0),
            'storage' => (int)Database::scalar("SELECT COALESCE(SUM(size),0) FROM files WHERE deleted_at IS NULL AND is_folder = 0", [], 0),
            'ai_msgs' => (int)Database::scalar("SELECT COUNT(*) FROM ai_messages", [], 0),
            'ai_users' => (int)Database::scalar("SELECT COUNT(DISTINCT ac.user_id) FROM ai_chats ac", [], 0),
            'admins' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'regional'", [], 0),
            'enrollments' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments ce WHERE 1=1", [], 0),
        ];

        // --- Regional admin performance (schools assigned, workload) ---
        $adminPerf = Database::all(
            "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name, u.email, u.last_login,
                    (SELECT COUNT(*) FROM schools s WHERE s.admin_id = u.id) AS schools,
                    (SELECT COUNT(*) FROM users st JOIN schools s ON s.id = st.school_id WHERE s.admin_id = u.id AND st.role='student') AS students,
                    (SELECT COUNT(*) FROM users d JOIN schools s ON s.id = d.school_id WHERE s.admin_id = u.id AND d.role='principal') AS directors
             FROM users u WHERE u.role = 'regional' ORDER BY schools DESC");
        foreach ($adminPerf as &$ap) {
            $ap['over'] = (int)$ap['schools'] > 15;
        }

        // --- Chart datasets (last 12 months / 30 days) ---
        $months = [];
        for ($i = 11; $i >= 0; $i--) $months[] = date('Y-m', strtotime("-$i months"));
        $mIn = implode(',', array_fill(0, 12, '?'));
        $monthly = Database::all(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, role, COUNT(*) AS n FROM users
             WHERE created_at >= ? GROUP BY m, role", [$months[0]]);
        $mmap = [];
        foreach ($monthly as $r) $mmap[$r['m']][$r['role']] = (int)$r['n'];
        $studentsGrowth = []; $teachersGrowth = []; $regs = [];
        foreach ($months as $m) {
            $studentsGrowth[] = $mmap[$m]['student'] ?? 0;
            $teachersGrowth[] = $mmap[$m]['teacher'] ?? 0;
            $regs[] = array_sum($mmap[$m] ?? []);
        }
        $schoolsGrowth = [];
        $schoolRows = Database::all("SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, COUNT(*) AS n FROM schools WHERE created_at >= ? GROUP BY m", [$months[0]]);
        $smap = array_column($schoolRows, 'n', 'm');
        foreach ($months as $m) $schoolsGrowth[] = (int)($smap[$m] ?? 0);

        $days = [];
        for ($i = 29; $i >= 0; $i--) $days[] = date('Y-m-d', strtotime("-$i days"));
        $dIn = implode(',', array_fill(0, 30, '?'));
        $loginRows = Database::all(
            "SELECT DATE(created_at) AS d, COUNT(*) AS n FROM login_history WHERE status = 'success' AND created_at >= ? GROUP BY d", [$days[0]]);
        $lmap = array_column($loginRows, 'n', 'd');
        $dailyLogins = [];
        foreach ($days as $d) $dailyLogins[] = (int)($lmap[$d] ?? 0);

        $attRows = Database::all(
            "SELECT date, COUNT(*) AS n FROM attendance WHERE date >= ? GROUP BY date ORDER BY date", [$days[0]]);
        $amap = array_column($attRows, 'n', 'date');
        $attendanceTrend = [];
        foreach ($days as $d) $attendanceTrend[] = (int)($amap[$d] ?? 0);

        $roleDist = Database::all("SELECT role, COUNT(*) AS n FROM users GROUP BY role ORDER BY n DESC");
        $roleDist = array_filter($roleDist, fn($r) => $r['role'] !== 'guest');

        $grades = Database::all(
            "SELECT CASE
                WHEN a.points_earned >= q.points * 0.9 THEN 'A'
                WHEN a.points_earned >= q.points * 0.8 THEN 'B'
                WHEN a.points_earned >= q.points * 0.7 THEN 'C'
                WHEN a.points_earned >= q.points * 0.6 THEN 'D'
                ELSE 'F' END AS g, COUNT(*) AS n
             FROM exam_answers a JOIN exam_questions q ON q.id = a.question_id
             WHERE a.is_correct IS NOT NULL GROUP BY g");
        $gmap = array_column($grades, 'n', 'g');
        $gradeLabels = ['A', 'B', 'C', 'D', 'F'];
        $gradeDist = [];
        foreach ($gradeLabels as $g) $gradeDist[] = (int)($gmap[$g] ?? 0);

        $dfCc = demo_filter('ce');
        $courseCompletion = Database::all(
            "SELECT ce.course_id, c.title,
                    COUNT(*) AS total,
                    SUM(ce.completed = 1) AS done
             FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id
             WHERE 1=1 $dfCc
             GROUP BY ce.course_id, c.title ORDER BY total DESC LIMIT 8");
        $compLabels = []; $compRates = [];
        foreach ($courseCompletion as $cc) { $compLabels[] = mb_strimwidth($cc['title'], 0, 18, '…'); $compRates[] = (int)$cc['total'] ? round((int)$cc['done'] / (int)$cc['total'] * 100) : 0; }

        $transferStat = Database::all("SELECT status, COUNT(*) AS n FROM transfer_requests GROUP BY status");
        $tmap = array_column($transferStat, 'n', 'status');
        $tLabels = ['pending', 'approved', 'rejected', 'completed'];
        $tDist = [];
        foreach ($tLabels as $t) $tDist[] = (int)($tmap[$t] ?? 0);

        $activityByHour = Database::all(
            "SELECT HOUR(created_at) AS h, COUNT(*) AS n FROM activity_logs WHERE created_at >= ? GROUP BY h ORDER BY h", [date('Y-m-d', strtotime('-7 days'))]);
        $hmap = array_column($activityByHour, 'n', 'h');
        $hours = []; $hourAct = [];
        for ($i = 0; $i < 24; $i++) { $hours[] = sprintf('%02d:00', $i); $hourAct[] = (int)($hmap[$i] ?? 0); }

        $newUsers = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, role, school_id, created_at FROM users ORDER BY created_at DESC LIMIT 8");
        $activity = Database::all(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 10");
        $schoolStats = Database::all(
            "SELECT s.id, s.name, s.code, s.status,
                    (SELECT COUNT(*) FROM users us WHERE us.school_id = s.id) AS users,
                    (SELECT COUNT(*) FROM courses c WHERE c.school_id = s.id) AS courses,
                    (SELECT COUNT(*) FROM course_enrollments ce JOIN courses c2 ON c2.id = ce.course_id WHERE c2.school_id = s.id) AS enrollments
             FROM schools s ORDER BY users DESC");
        $signups = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $signups[] = ['date' => $d, 'n' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?", [$d], 0)];
        }
        $ledger = [];
        foreach (Database::all("SELECT id, name FROM schools") as $s) {
            $st = Ledger::status((int)$s['id']);
            $ledger[] = ['school' => $s['name'], 'id' => (int)$s['id'], 'ok' => $st['ok'], 'entries' => (int)$st['entries'], 'broken_at' => $st['broken_at']];
        }
        Router::render('app/admin/dashboard', [
            'title' => 'Admin Dashboard', 'stats' => $stats, 'newUsers' => $newUsers,
            'activity' => $activity, 'schoolStats' => $schoolStats, 'signups' => $signups,
            'months' => $months, 'studentsGrowth' => $studentsGrowth, 'teachersGrowth' => $teachersGrowth,
            'schoolsGrowth' => $schoolsGrowth, 'regs' => $regs, 'dailyLogins' => $dailyLogins,
            'attendanceTrend' => $attendanceTrend, 'roleDist' => $roleDist,
            'gradeLabels' => $gradeLabels, 'gradeDist' => $gradeDist,
            'compLabels' => $compLabels, 'compRates' => $compRates,
            'tLabels' => $tLabels, 'tDist' => $tDist,
            'hours' => $hours, 'hourAct' => $hourAct, 'ledger' => $ledger, 'adminPerf' => $adminPerf,
        ]);
    }
}

/* =============== ADMIN: users =============== */
class Ctl_users {
    public function run(): void {
        $u = require_role('ministry');
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $q = trim($_GET['q'] ?? '');
        $sort = $_GET['sort'] ?? 'created_at';
        $dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $sortMap = ['name' => 'us.first_name', 'role' => 'us.role', 'number' => 'us.student_id', 'school' => 's.name', 'status' => 'us.status', 'created_at' => 'us.created_at', 'id' => 'us.id'];
        if (!isset($sortMap[$sort])) $sort = 'created_at';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', us.id DESC';

        $where = "1=1";
        $args = [];
        if (in_array($role, ['ministry', 'regional', 'zonal', 'woreda', 'principal', 'teacher', 'student', 'parent', 'guest', 'registrar', 'dean', 'vice_dean', 'hod', 'lecturer', 'bursar', 'student_affairs', 'librarian', 'it_admin'], true)) { $where .= " AND us.role = ?"; $args[] = $role; }
        if (in_array($status, ['active', 'pending', 'suspended', 'banned'], true)) { $where .= " AND us.status = ?"; $args[] = $status; }
        if ($q !== '') { $where .= " AND (us.first_name LIKE ? OR us.last_name LIKE ? OR us.email LIKE ? OR us.student_id LIKE ?)"; $args[] = "%$q%"; $args[] = "%$q%"; $args[] = "%$q%"; $args[] = "%$q%"; }

        $df = demo_filter('us');
        $dfU = demo_filter('');
        $total = (int)Database::scalar("SELECT COUNT(*) FROM users us WHERE $where $df", $args, 0);
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;

        $users = Database::all(
            "SELECT us.*, s.name AS school_name, g.name AS group_name, d.name AS dept_name
             FROM users us
             LEFT JOIN schools s ON s.id = us.school_id
             LEFT JOIN student_groups g ON g.id = us.group_id
             LEFT JOIN departments d ON d.id = us.department_id
             WHERE $where $df ORDER BY $orderBy LIMIT $perPage OFFSET $offset", $args);

        $statRows = Database::all("SELECT status, COUNT(*) c FROM users us WHERE $where $df GROUP BY status", $args);
        $stats = ['total' => $total, 'active' => 0, 'pending' => 0, 'suspended' => 0, 'banned' => 0];
        foreach ($statRows as $sr) $stats[$sr['status']] = (int)$sr['c'];
        $stats['new_month'] = (int)Database::scalar(
            "SELECT COUNT(*) FROM users WHERE created_at >= ? $dfU", [date('Y-m-01')], 0);
        $roleCounts = Database::all("SELECT role, COUNT(*) c FROM users WHERE 1=1 $dfU GROUP BY role");
        $roleCounts = array_column($roleCounts, 'c', 'role');

        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");
        $groups = Database::all("SELECT id, name FROM student_groups");
        $depts = Database::all("SELECT id, name FROM departments");

        $baseQuery = array_filter(['role' => $role, 'status' => $status, 'q' => $q, 'sort' => $sort, 'dir' => $dir], fn($x) => $x !== '');
        $pager = fn(int $p): string => url('admin/users?' . http_build_query(array_merge($baseQuery, ['page' => $p])));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $suffix = $baseQuery ? '&' . http_build_query($baseQuery) : '';
            if (isset($_POST['create_user'])) {
                $role2 = $_POST['role'] ?? 'student';
                $email = trim($_POST['email'] ?? '');
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('danger', 'Valid email required.'); redirect('admin/users' . $suffix); }
                if (Database::one("SELECT id FROM users WHERE email = ?", [$email])) { flash('danger', 'Email already in use.'); redirect('admin/users' . $suffix); }

                $allowed = match($u['role']) {
                    'ministry' => ['regional'],
                    'regional' => array_merge(['zonal','woreda','principal','teacher','student','parent'],
                        Database::scalar("SELECT education_level FROM schools WHERE id = ?", [(int)$u['school_id']]) === 'university'
                            ? ['registrar','dean','vice_dean','hod','lecturer','bursar','student_affairs','librarian'] : []),
                    'zonal'    => ['woreda','principal','teacher','student','parent'],
                    'woreda'   => ['principal','teacher','student','parent'],
                    'principal'=> array_merge(['teacher','student','parent'],
                        Database::scalar("SELECT education_level FROM schools WHERE id = ?", [(int)$u['school_id']]) === 'university'
                            ? ['registrar','dean','vice_dean','hod','lecturer','bursar','student_affairs','librarian'] : []),
                    'registrar'=> ['student'],
                    default    => [],
                };
                if (!in_array($role2, $allowed, true)) {
                    flash('danger', 'You do not have permission to create this role.'); redirect('admin/users' . $suffix);
                }

                $schoolId = (int)($_POST['school_id'] ?? 0);
                if (!$schoolId) $schoolId = (int)$u['school_id'];
                if (!$schoolId && in_array($role2, ['principal', 'teacher', 'student', 'parent'], true)) {
                    flash('danger', 'Select the school for this account.'); redirect('admin/users' . $suffix);
                }

                $data = [
                    'school_id' => $schoolId, 'role' => $role2,
                    'first_name' => trim($_POST['first_name']), 'last_name' => trim($_POST['last_name']),
                    'email' => $email, 'phone' => trim($_POST['phone'] ?? ''),
                    'password_hash' => password_hash($_POST['password'] ?? random_password(), PASSWORD_BCRYPT),
                    'status' => 'active',
                ];
                if ($role2 === 'student') {
                    $data['student_id'] = generate_student_id((int)$schoolId);
                    $data['group_id'] = (int)($_POST['group_id'] ?? 0) ?: null;
                }
                if (in_array($role2, ['teacher', 'lecturer', 'hod'], true)) {
                    $data['department_id'] = (int)($_POST['department_id'] ?? 0) ?: null;
                }
                Database::insert('users', $data);
                $newUserId = Database::insertId();
                if ($role2 === 'student') {
                    ensure_national_id($newUserId);
                    education_enter($newUserId, (int)$schoolId, 'enrolled');
                }
                log_activity('user', "Created user {$data['first_name']} {$data['last_name']} ($role2)", (int)$u['id']);
                flash('success', 'User created.');
                redirect('admin/users' . $suffix);
            }
            if (($del = (int)($_POST['delete_user'] ?? 0))) {
                $target = Database::one("SELECT id, first_name, last_name FROM users WHERE id = ?", [$del]);
                if ($target && (int)$target['id'] !== (int)$u['id'] && (int)$target['id'] !== 1) {
                    Database::delete('users', 'id = ?', [$del]);
                    log_activity('user', "Deleted user {$target['first_name']} {$target['last_name']}", (int)$u['id']);
                    flash('success', 'User deleted.');
                } else {
                    flash('danger', 'This account cannot be deleted.');
                }
                redirect('admin/users' . $suffix);
            }
            if (($sid = (int)($_POST['set_status'] ?? 0))) {
                $st = $_POST['new_status'] ?? 'active';
                if (in_array($st, ['active', 'pending', 'suspended', 'banned'], true) && $sid !== (int)$u['id'] && $sid !== 1) {
                    Database::update('users', ['status' => $st], 'id = ?', [$sid]);
                    flash('success', 'User status updated.');
                } else {
                    flash('danger', 'This account cannot be suspended.');
                }
                redirect('admin/users' . $suffix);
            }
            /* Bulk actions from selection */
            $ids = array_values(array_filter(array_map('intval', explode(',', (string)($_POST['ids'] ?? '')))));
            $ids = array_diff($ids, [(int)$u['id'], 1]);
            if ($ids) {
                if (isset($_POST['bulk_status'])) {
                    $st = $_POST['bulk_status'] === 'active' ? 'active' : 'suspended';
                    if ($st === 'active' || $st === 'suspended') {
                        $in = implode(',', $ids);
                        Database::run("UPDATE users SET status = ? WHERE id IN ($in)", [$st]);
                        log_activity('user', "Bulk $st " . count($ids) . ' users', (int)$u['id']);
                        flash('success', count($ids) . ' user(s) ' . ($st === 'active' ? 'activated' : 'suspended') . '.');
                    }
                } elseif (isset($_POST['bulk_delete'])) {
                    $in = implode(',', $ids);
                    Database::run("DELETE FROM users WHERE id IN ($in)", []);
                    log_activity('user', 'Bulk deleted ' . count($ids) . ' users', (int)$u['id']);
                    flash('success', count($ids) . ' user(s) deleted.');
                }
            } elseif (isset($_POST['bulk_status']) || isset($_POST['bulk_delete'])) {
                flash('warning', 'No valid users selected.');
            }
            redirect('admin/users' . $suffix);
        }
        $isPartial = ($_GET['partial'] ?? '') === '1';
        Router::render($isPartial ? 'app/admin/users_partial' : 'app/admin/users', [
            'title' => 'Users', 'users' => $users, 'role' => $role, 'q' => $q, 'status' => $status,
            'schools' => $schools, 'groups' => $groups, 'depts' => $depts,
            'stats' => $stats, 'roleCounts' => $roleCounts,
            'sort' => $sort, 'dir' => $dir, 'page' => $page, 'pages' => $pages, 'total' => $total, 'pager' => $pager,
            'creatorRole' => $u['role'], 'creatorSchoolId' => $u['school_id'],
        ]);
    }
}

/* =============== ADMIN: user detail =============== */
class Ctl_user {
    public function run(): void {
        $u = require_role('ministry');
        $id = (int)($_GET['id'] ?? 0);
        $target = Database::one("SELECT us.*, s.name AS school_name FROM users us JOIN schools s ON s.id = us.school_id WHERE us.id = ?", [$id]);
        if (!$target) { flash('danger', 'User not found.'); redirect('admin/users'); }
        $children = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name, student_id FROM users WHERE parent_id = ?", [$id]);
        $parent = $target['parent_id'] ? Database::one("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE id = ?", [$target['parent_id']]) : null;
        $enrollments = Database::all(
            "SELECT c.title, ce.progress, ce.completed, ce.enrolled_at FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE ce.user_id = ?", [$id]);
        $attendance = Database::all(
            "SELECT at.status, at.date, c.title AS course_title FROM attendance at JOIN courses c ON c.id = at.course_id WHERE at.student_id = ? ORDER BY at.date DESC LIMIT 20", [$id]);
        $grades = Database::all(
            "SELECT e.title AS exam_title, c.title AS course_title, ea.score, ea.total_points, ea.submitted_at
             FROM exam_attempts ea
             JOIN exams e ON e.id = ea.exam_id
             JOIN courses c ON c.id = e.course_id
             WHERE ea.student_id = ? AND ea.status IN ('submitted','graded')
             ORDER BY ea.submitted_at DESC LIMIT 30", [$id]);
        $badges = Database::all(
            "SELECT b.name, b.icon, b.description, ub.earned_at FROM user_badges ub JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ? ORDER BY ub.earned_at DESC", [$id]);
        $gpa = Database::all(
            "SELECT c.title, AVG(ea.score / NULLIF(ea.total_points,0) * 100) AS avg_percent,
                    COUNT(ea.id) AS attempts
             FROM exam_attempts ea
             JOIN exams e ON e.id = ea.exam_id
             JOIN courses c ON c.id = e.course_id
             WHERE ea.student_id = ? AND ea.status IN ('submitted','graded') AND ea.total_points > 0
             GROUP BY e.course_id, c.title", [$id]);
        $logins = Database::all("SELECT * FROM login_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['update_user'])) {
                $data = [
                    'first_name' => trim($_POST['first_name']), 'last_name' => trim($_POST['last_name']),
                    'email' => trim($_POST['email']), 'phone' => trim($_POST['phone']),
                    'role' => $_POST['role'] ?? $target['role'],
                    'status' => $_POST['status'] ?? $target['status'],
                ];
                if ($target['role'] === 'student') $data['student_id'] = trim($_POST['student_id']);
                if (!empty($_POST['password'])) $data['password_hash'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
                Database::update('users', $data, 'id = ?', [$id]);
                log_activity('user', "Updated user #$id", (int)$u['id']);
                flash('success', 'User updated.');
                redirect('admin/user&id=' . $id);
            }
            if (($linkP = (int)($_POST['link_parent'] ?? 0))) {
                Database::update('users', ['parent_id' => $linkP], 'id = ?', [$id]);
                flash('success', 'Parent linked.');
                redirect('admin/user&id=' . $id);
            }
        }
        Router::render('app/admin/user', [
            'title' => $target['first_name'] . ' ' . $target['last_name'], 'target' => $target,
            'children' => $children, 'parent' => $parent, 'enrollments' => $enrollments,
            'attendance' => $attendance, 'logins' => $logins, 'grades' => $grades, 'badges' => $badges, 'gpa' => $gpa,
        ]);
    }
}

/* =============== ADMIN: schools =============== */
class Ctl_schools {
    public function run(): void {
        $u = require_role('ministry');
        $q = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        $sort = $_GET['sort'] ?? 'name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $sortMap = ['name' => 's.name', 'type' => 's.type', 'status' => 's.status', 'created_at' => 's.created_at', 'users' => 'total_users', 'city' => 's.city'];
        if (!isset($sortMap[$sort])) $sort = 'name';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', s.id DESC';

        $where = "1=1";
        $args = [];
        if ($q !== '') { $where .= " AND (s.name LIKE ? OR s.code LIKE ? OR s.city LIKE ?)"; $args[] = "%$q%"; $args[] = "%$q%"; $args[] = "%$q%"; }
        if (in_array($type, ['school', 'university', 'college', 'training', 'other'], true)) { $where .= " AND s.type = ?"; $args[] = $type; }
        if (in_array($status, ['active', 'suspended'], true)) { $where .= " AND s.status = ?"; $args[] = $status; }

        $countSql = "SELECT COUNT(*) FROM schools s WHERE $where";
        $total = (int)Database::scalar($countSql, $args, 0);
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;

        $sel = "SELECT s.*,
                (SELECT COUNT(*) FROM users us WHERE us.school_id = s.id) AS total_users,
                (SELECT COUNT(*) FROM users us WHERE us.school_id = s.id AND us.role = 'student') AS students,
                (SELECT COUNT(*) FROM users us WHERE us.school_id = s.id AND us.role = 'teacher') AS teachers,
                (SELECT COUNT(*) FROM users us WHERE us.school_id = s.id AND us.role = 'principal') AS directors,
                (SELECT COUNT(*) FROM users us WHERE us.school_id = s.id AND us.role = 'parent') AS parents,
                (SELECT COUNT(*) FROM courses c WHERE c.school_id = s.id) AS courses,
                (SELECT COUNT(*) FROM departments d WHERE d.school_id = s.id) AS departments,
                (SELECT COUNT(*) FROM student_groups g WHERE g.school_id = s.id) AS classes
                FROM schools s WHERE $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
        $schools = Database::all($sel, $args);

        $statRows = Database::all("SELECT s.status, COUNT(*) c FROM schools s WHERE $where GROUP BY s.status", $args);
        $stats = ['total' => $total, 'active' => 0, 'suspended' => 0];
        foreach ($statRows as $sr) $stats[$sr['status']] = (int)$sr['c'];
        $stats['students'] = (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student'", [], 0);
        $typeCounts = Database::all("SELECT s.type, COUNT(*) c FROM schools s GROUP BY s.type");
        $typeCounts = array_column($typeCounts, 'c', 'type');

        $baseQuery = array_filter(['q' => $q, 'type' => $type, 'status' => $status, 'sort' => $sort, 'dir' => $dir], fn($x) => $x !== '');
        $pager = fn(int $p): string => url('admin/schools?' . http_build_query(array_merge($baseQuery, ['page' => $p])));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $suffix = $baseQuery ? '&' . http_build_query($baseQuery) : '';
            if (isset($_POST['create_school'])) {
                $data = [
                    'name' => trim($_POST['name']), 'code' => strtoupper(trim($_POST['code'])),
                    'type' => $_POST['type'] ?? 'school', 'address' => trim($_POST['address'] ?? ''),
                    'city' => trim($_POST['city'] ?? ''), 'phone' => trim($_POST['phone'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'education_level' => in_array($_POST['education_level'] ?? 'secondary', ['kg', 'primary', 'secondary', 'preparatory', 'university', 'college', 'training', 'other'], true) ? $_POST['education_level'] : 'secondary',
                    'zone_id' => (int)($_POST['zone_id'] ?? 0) ?: null,
                    'woreda_id' => (int)($_POST['woreda_id'] ?? 0) ?: null,
                ];
                if (!$data['name'] || !$data['code']) { flash('danger', 'Name and code required.'); redirect('admin/schools' . $suffix); }
                if (Database::one("SELECT id FROM schools WHERE code = ?", [$data['code']])) { flash('danger', 'School code already exists.'); redirect('admin/schools' . $suffix); }
                $newSchoolId = Database::insert('schools', $data);
                ensure_school_modules((int)$newSchoolId);
                log_activity('school', "Created school {$data['name']}", (int)$u['id']);
                flash('success', 'School created. Default modules for its level were installed automatically.');
                redirect('admin/schools' . $suffix);
            }
            if (($sid = (int)($_POST['update_school'] ?? 0))) {
                Database::update('schools', [
                    'name' => trim($_POST['name']), 'type' => $_POST['type'] ?? 'school',
                    'education_level' => in_array($_POST['education_level'] ?? 'secondary', ['kg', 'primary', 'secondary', 'preparatory', 'university', 'college', 'training', 'other'], true) ? $_POST['education_level'] : 'secondary',
                    'address' => trim($_POST['address'] ?? ''), 'city' => trim($_POST['city'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''), 'email' => trim($_POST['email'] ?? ''),
                    'status' => $_POST['status'] ?? 'active',
                    'zone_id' => (int)($_POST['zone_id'] ?? 0) ?: null,
                    'woreda_id' => (int)($_POST['woreda_id'] ?? 0) ?: null,
                ], 'id = ?', [$sid]);
                ensure_school_modules((int)$sid);
                flash('success', 'School updated. Level defaults were applied automatically.');
                redirect('admin/schools' . $suffix);
            }
            if (($did = (int)($_POST['delete_school'] ?? 0))) {
                $target = Database::one("SELECT id, name FROM schools WHERE id = ?", [$did]);
                if ($target) {
                    Database::delete('schools', 'id = ?', [$did]);
                    log_activity('school', "Deleted school {$target['name']}", (int)$u['id']);
                    flash('success', 'School deleted.');
                }
                redirect('admin/schools' . $suffix);
            }
        }
        $isPartial = ($_GET['partial'] ?? '') === '1';
        Router::render($isPartial ? 'app/admin/schools_partial' : 'app/admin/schools', [
            'title' => 'Schools', 'schools' => $schools, 'q' => $q, 'type' => $type, 'status' => $status,
            'stats' => $stats, 'typeCounts' => $typeCounts,
            'sort' => $sort, 'dir' => $dir, 'page' => $page, 'pages' => $pages, 'total' => $total, 'pager' => $pager,
        ]);
    }
}

/* =============== ADMIN: school profile =============== */
class Ctl_school {
    public function run(): void {
        $u = require_role('ministry');
        $id = (int)($_GET['id'] ?? 0);
        $school = Database::one("SELECT * FROM schools WHERE id = ?", [$id]);
        if (!$school) { flash('danger', 'School not found.'); redirect('admin/schools'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $data = [
                'name' => trim($_POST['name']), 'type' => $_POST['type'] ?? 'school',
                'education_level' => in_array($_POST['education_level'] ?? 'secondary', ['kg', 'primary', 'secondary', 'preparatory', 'university', 'college', 'training', 'other'], true) ? $_POST['education_level'] : 'secondary',
                'address' => trim($_POST['address'] ?? ''), 'city' => trim($_POST['city'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''), 'email' => trim($_POST['email'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
            ];
            if (!empty($_FILES['logo']['name'])) {
                $res = upload_file($_FILES['logo'], 'schools', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                if (!$res['error']) $data['logo'] = $res['path'];
            }
            Database::update('schools', $data, 'id = ?', [$id]);
            log_activity('school', "Updated school {$data['name']}", (int)$u['id']);
            flash('success', 'School updated.');
            redirect('admin/school&id=' . $id);
        }

        $directors = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, status, last_login FROM users WHERE role = 'principal' AND school_id = ?", [$id]);
        $vice = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name, email FROM users WHERE role = 'teacher' AND school_id = ? AND department_id IS NOT NULL AND status='active' ORDER BY last_login DESC LIMIT 3", [$id]);
        $teachers = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, status FROM users WHERE role = 'teacher' AND school_id = ? ORDER BY created_at DESC LIMIT 12", [$id]);
        $students = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name, student_id, status, enrollment_status FROM users WHERE role = 'student' AND school_id = ? ORDER BY created_at DESC LIMIT 12", [$id]);
        $parents = (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'parent' AND school_id = ?", [$id], 0);
        $depts = Database::all("SELECT d.*, (SELECT COUNT(*) FROM users us WHERE us.department_id = d.id) AS members FROM departments d WHERE d.school_id = ?", [$id]);
        $subjects = Database::all("SELECT * FROM subjects WHERE school_id = ?", [$id]);
        $courses = Database::all(
            "SELECT c.id, c.title, c.code, c.status, u.first_name AS tfirst, u.last_name AS tlast,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lessons
             FROM courses c LEFT JOIN users u ON u.id = c.teacher_id WHERE c.school_id = ? ORDER BY c.created_at DESC LIMIT 12", [$id]);
        $announcements = Database::all("SELECT * FROM announcements WHERE school_id = ? ORDER BY created_at DESC LIMIT 6", [$id]);
        $transfers = Database::all(
            "SELECT t.*, us.first_name AS sfirst, us.last_name AS slast, s.name AS from_school, s2.name AS to_school
             FROM transfer_requests t
             LEFT JOIN users us ON us.id = t.student_id
             LEFT JOIN schools s ON s.id = t.from_school_id
             LEFT JOIN schools s2 ON s2.id = t.to_school_id
             WHERE t.from_school_id = ? OR t.to_school_id = ? ORDER BY t.created_at DESC LIMIT 10", [$id, $id]);
        $logs = Database::all(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name FROM activity_logs a
             LEFT JOIN users u ON u.id = a.user_id WHERE u.school_id = ? OR a.detail LIKE ? ORDER BY a.created_at DESC LIMIT 15", [$id, '%' . $school['code'] . '%']);

        $gradeDist = Database::all(
            "SELECT CASE WHEN a.points_earned >= q.points * 0.9 THEN 'A' WHEN a.points_earned >= q.points * 0.8 THEN 'B'
             WHEN a.points_earned >= q.points * 0.7 THEN 'C' WHEN a.points_earned >= q.points * 0.6 THEN 'D' ELSE 'F' END AS g, COUNT(*) AS n
             FROM exam_answers a JOIN exam_questions q ON q.id = a.question_id
             JOIN exam_attempts at2 ON at2.id = a.attempt_id JOIN exams e ON e.id = at2.exam_id JOIN courses c ON c.id = e.course_id
             WHERE c.school_id = ? AND a.is_correct IS NOT NULL GROUP BY g", [$id]);
        $gmap = array_column($gradeDist, 'n', 'g');
        $gradeLabels = ['A', 'B', 'C', 'D', 'F'];
        $grades = [];
        foreach ($gradeLabels as $g) $grades[] = (int)($gmap[$g] ?? 0);

        $attSeries = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $attSeries[] = ['date' => $d, 'n' => (int)Database::scalar(
                "SELECT COUNT(*) FROM attendance at JOIN courses c ON c.id = at.course_id WHERE c.school_id = ? AND at.date = ?", [$id, $d], 0)];
        }
        $loginSeries = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $loginSeries[] = ['date' => $d, 'n' => (int)Database::scalar(
                "SELECT COUNT(*) FROM login_history lh JOIN users us ON us.id = lh.user_id WHERE us.school_id = ? AND lh.status='success' AND DATE(lh.created_at) = ?", [$id, $d], 0)];
        }

        $stats = [
            'teachers' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role='teacher' AND school_id=?", [$id], 0),
            'students' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=?", [$id], 0),
            'parents' => $parents,
            'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE school_id=?", [$id], 0),
            'exams' => (int)Database::scalar("SELECT COUNT(*) FROM exams e JOIN courses c ON c.id=e.course_id WHERE c.school_id=?", [$id], 0),
            'enrollments' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id=ce.course_id WHERE c.school_id=?", [$id], 0),
            'attendance' => (int)Database::scalar("SELECT COUNT(*) FROM attendance at JOIN courses c ON c.id=at.course_id WHERE c.school_id=?", [$id], 0),
        ];
        if (($_GET['partial'] ?? '') === '1') {
            Router::render('app/admin/school_drawer', [
                'title' => $school['name'], 'school' => $school, 'stats' => $stats,
                'directors' => $directors, 'vice' => $vice, 'teachers' => array_slice($teachers, 0, 5), 'students' => array_slice($students, 0, 5),
                'depts' => $depts, 'subjects' => $subjects, 'courses' => array_slice($courses, 0, 5),
                'announcements' => array_slice($announcements, 0, 3), 'transfers' => $transfers, 'logs' => $logs,
            ]);
            return;
        }
        Router::render('app/admin/school_profile', [
            'title' => $school['name'], 'school' => $school, 'stats' => $stats,
            'directors' => $directors, 'vice' => $vice, 'teachers' => $teachers, 'students' => $students,
            'depts' => $depts, 'subjects' => $subjects, 'courses' => $courses,
            'announcements' => $announcements, 'transfers' => $transfers, 'logs' => $logs,
            'gradeLabels' => $gradeLabels, 'grades' => $grades, 'attSeries' => $attSeries, 'loginSeries' => $loginSeries,
        ]);
    }
}

/* =============== ADMIN: departments =============== */
class Ctl_departments {
    public function run(): void {
        $u = require_role('ministry');
        $sort = $_GET['sort'] ?? 'name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $sortMap = ['name' => 'd.name', 'school' => 's.name', 'members' => 'members', 'status' => 'd.status'];
        if (!isset($sortMap[$sort])) $sort = 'name';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', d.id DESC';
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");
        $depts = Database::all("SELECT d.*, s.name AS school_name, (SELECT COUNT(*) FROM users us WHERE us.department_id = d.id) AS members FROM departments d JOIN schools s ON s.id = d.school_id ORDER BY $orderBy");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_dept'])) {
                $school_id = (int)$_POST['school_id'];
                $name = trim($_POST['name']);
                if (!$school_id || !$name) { flash('danger', 'School and name required.'); redirect('admin/departments'); }
                Database::insert('departments', [
                    'school_id' => $school_id, 'name' => $name,
                    'head' => trim($_POST['head'] ?? ''),
                ]);
                log_activity('department', "Created department $name", (int)$u['id']);
                flash('success', 'Department created.');
                redirect('admin/departments');
            }
            if (($did = (int)($_POST['update_dept'] ?? 0))) {
                Database::update('departments', [
                    'school_id' => (int)$_POST['school_id'], 'name' => trim($_POST['name']),
                    'head' => trim($_POST['head'] ?? ''),
                ], 'id = ?', [$did]);
                log_activity('department', "Updated department #$did", (int)$u['id']);
                flash('success', 'Department updated.');
                redirect('admin/departments');
            }
            if (($did = (int)($_POST['archive_dept'] ?? 0))) {
                Database::update('departments', ['status' => 'archived'], 'id = ?', [$did]);
                flash('success', 'Department archived.');
                redirect('admin/departments');
            }
            if (($did = (int)($_POST['restore_dept'] ?? 0))) {
                Database::update('departments', ['status' => 'active'], 'id = ?', [$did]);
                flash('success', 'Department restored.');
                redirect('admin/departments');
            }
            if (($did = (int)($_POST['delete_dept'] ?? 0))) {
                Database::delete('departments', 'id = ?', [$did]);
                log_activity('department', "Deleted department #$did", (int)$u['id']);
                flash('success', 'Department deleted.');
                redirect('admin/departments');
            }
        }
        Router::render('app/admin/departments', ['title' => 'Departments', 'depts' => $depts, 'schools' => $schools, 'sort' => $sort, 'dir' => $dir]);
    }
}

/* =============== ADMIN: department detail =============== */
class Ctl_department {
    public function run(): void {
        $u = require_role('ministry');
        $id = (int)($_GET['id'] ?? 0);
        $dept = Database::one("SELECT d.*, s.name AS school_name FROM departments d JOIN schools s ON s.id = d.school_id WHERE d.id = ?", [$id]);
        if (!$dept) { flash('danger', 'Department not found.'); redirect('admin/departments'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['update_dept'])) {
                Database::update('departments', [
                    'school_id' => (int)$_POST['school_id'], 'name' => trim($_POST['name']),
                    'head' => trim($_POST['head'] ?? ''),
                ], 'id = ?', [$id]);
                log_activity('department', "Updated department {$dept['name']}", (int)$u['id']);
                flash('success', 'Department updated.');
                redirect('admin/department&id=' . $id);
            }
        }

        $members = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name, email, role, status FROM users WHERE department_id = ? ORDER BY role, first_name", [$id]);
        $subjects = Database::all("SELECT id, name, code, status FROM subjects WHERE department_id = ?", [$id]);
        $courses = Database::all(
            "SELECT c.id, c.title, c.status, (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
             FROM courses c JOIN subjects sb ON sb.id = c.subject_id WHERE sb.department_id = ? ORDER BY c.created_at DESC", [$id]);
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");

        $activity = Database::all(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name FROM activity_logs a
             LEFT JOIN users u ON u.id = a.user_id WHERE a.action = 'department' AND (a.detail LIKE ? OR a.user_id IN (SELECT id FROM users WHERE department_id = ?)) ORDER BY a.created_at DESC LIMIT 15",
            ['%' . $dept['name'] . '%', $id]);

        Router::render('app/admin/department_profile', [
            'title' => $dept['name'], 'dept' => $dept, 'members' => $members,
            'subjects' => $subjects, 'courses' => $courses, 'schools' => $schools, 'activity' => $activity,
        ]);
    }
}

/* =============== ADMIN: subjects =============== */
class Ctl_subjects {
    public function run(): void {
        $u = require_role('ministry');
        $sort = $_GET['sort'] ?? 'name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $sortMap = ['name' => 's.name', 'code' => 's.code', 'school' => 'sc.name', 'department' => 'd.name', 'status' => 's.status'];
        if (!isset($sortMap[$sort])) $sort = 'name';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', s.id DESC';
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");
        $depts = Database::all("SELECT id, name, school_id FROM departments WHERE status = 'active' ORDER BY name");
        $subjects = Database::all("SELECT s.*, sc.name AS school_name, d.name AS dept_name FROM subjects s JOIN schools sc ON sc.id = s.school_id LEFT JOIN departments d ON d.id = s.department_id ORDER BY $orderBy");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_subject'])) {
                Database::insert('subjects', [
                    'school_id' => (int)$_POST['school_id'], 'name' => trim($_POST['name']),
                    'code' => strtoupper(trim($_POST['code'] ?? '')),
                    'department_id' => (int)($_POST['department_id'] ?? 0) ?: null,
                ]);
                log_activity('subject', "Created subject " . trim($_POST['name']), (int)$u['id']);
                flash('success', 'Subject created.');
                redirect('admin/subjects');
            }
            if (($sid = (int)($_POST['update_subject'] ?? 0))) {
                Database::update('subjects', [
                    'school_id' => (int)$_POST['school_id'], 'name' => trim($_POST['name']),
                    'code' => strtoupper(trim($_POST['code'] ?? '')),
                    'department_id' => (int)($_POST['department_id'] ?? 0) ?: null,
                ], 'id = ?', [$sid]);
                log_activity('subject', "Updated subject #$sid", (int)$u['id']);
                flash('success', 'Subject updated.');
                redirect('admin/subjects');
            }
            if (($sid = (int)($_POST['archive_subject'] ?? 0))) {
                Database::update('subjects', ['status' => 'archived'], 'id = ?', [$sid]);
                flash('success', 'Subject archived.');
                redirect('admin/subjects');
            }
            if (($sid = (int)($_POST['restore_subject'] ?? 0))) {
                Database::update('subjects', ['status' => 'active'], 'id = ?', [$sid]);
                flash('success', 'Subject restored.');
                redirect('admin/subjects');
            }
            if (($sid = (int)($_POST['delete_subject'] ?? 0))) {
                Database::delete('subjects', 'id = ?', [$sid]);
                log_activity('subject', "Deleted subject #$sid", (int)$u['id']);
                flash('success', 'Subject deleted.');
                redirect('admin/subjects');
            }
        }
        Router::render('app/admin/subjects', ['title' => 'Subjects', 'subjects' => $subjects, 'schools' => $schools, 'depts' => $depts, 'sort' => $sort, 'dir' => $dir]);
    }
}

/* =============== ADMIN: student groups =============== */
class Ctl_groups {
    public function run(): void {
        $u = require_role('ministry');
        $sort = $_GET['sort'] ?? 'name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $sortMap = ['name' => 'g.name', 'school' => 's.name', 'grade' => 'g.grade', 'section' => 'g.section', 'students' => 'members'];
        if (!isset($sortMap[$sort])) $sort = 'name';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', g.id DESC';
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");
        $groups = Database::all("SELECT g.*, s.name AS school_name, (SELECT COUNT(*) FROM users us WHERE us.group_id = g.id) AS members FROM student_groups g JOIN schools s ON s.id = g.school_id ORDER BY $orderBy");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_group'])) {
                Database::insert('student_groups', [
                    'school_id' => (int)$_POST['school_id'], 'name' => trim($_POST['name']),
                    'grade' => trim($_POST['grade'] ?? ''), 'section' => trim($_POST['section'] ?? ''),
                ]);
                flash('success', 'Class created.');
                redirect('admin/groups');
            }
            if (($gid = (int)($_POST['delete_group'] ?? 0))) {
                Database::delete('student_groups', 'id = ?', [$gid]);
                flash('success', 'Class deleted.');
                redirect('admin/groups');
            }
        }
        Router::render('app/admin/groups', ['title' => 'Classes', 'groups' => $groups, 'schools' => $schools, 'sort' => $sort, 'dir' => $dir]);
    }
}

/* =============== ADMIN: academic years =============== */
class Ctl_years {
    public function run(): void {
        $u = require_role('ministry');
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");
        $years = Database::all("SELECT y.*, s.name AS school_name FROM academic_years y JOIN schools s ON s.id = y.school_id ORDER BY y.start_date DESC");
        foreach ($years as &$y) {
            $y['semesters'] = Database::all("SELECT * FROM semesters WHERE year_id = ? ORDER BY start_date", [$y['id']]);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_year'])) {
                Database::insert('academic_years', [
                    'school_id' => (int)$_POST['school_id'], 'name' => trim($_POST['name']),
                    'start_date' => $_POST['start_date'] ?: null, 'end_date' => $_POST['end_date'] ?: null,
                ]);
                flash('success', 'Academic year created.');
                redirect('admin/years');
            }
            if (isset($_POST['set_current'])) {
                Database::run("UPDATE academic_years SET is_current = 0");
                Database::update('academic_years', ['is_current' => 1], 'id = ?', [(int)$_POST['set_current']]);
                flash('success', 'Current year updated.');
                redirect('admin/years');
            }
            if (isset($_POST['create_semester'])) {
                Database::insert('semesters', [
                    'year_id' => (int)$_POST['year_id'], 'name' => trim($_POST['name']),
                    'start_date' => $_POST['start_date'] ?: null, 'end_date' => $_POST['end_date'] ?: null,
                ]);
                flash('success', 'Semester added.');
                redirect('admin/years');
            }
        }
        Router::render('app/admin/years', ['title' => 'Academic Years', 'years' => $years, 'schools' => $schools]);
    }
}

/* =============== ADMIN: courses =============== */
class Ctl_courses {
    public function run(): void {
        $u = require_role('ministry');
        $schoolId = (int)($_GET['school'] ?? 0);
        $sort = $_GET['sort'] ?? 'created_at';
        $dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $sortMap = ['name' => 'c.name', 'school' => 's.name', 'teacher' => 'u.last_name', 'students' => 'students', 'lessons' => 'lessons', 'status' => 'c.status', 'created_at' => 'c.created_at'];
        if (!isset($sortMap[$sort])) $sort = 'created_at';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', c.id DESC';
        $courses = Database::all(
            "SELECT c.*, s.name AS school_name, u.first_name AS tfirst, u.last_name AS tlast,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lessons
             FROM courses c JOIN schools s ON s.id = c.school_id JOIN users u ON u.id = c.teacher_id
             WHERE 1=1" . ($schoolId ? " AND c.school_id = ?" : "") . " ORDER BY $orderBy", $schoolId ? [$schoolId] : []);
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (($cid = (int)($_POST['archive_course'] ?? 0))) {
                Database::update('courses', ['status' => 'archived'], 'id = ?', [$cid]);
                flash('success', 'Course archived.');
                redirect('admin/courses');
            }
            if (($cid = (int)($_POST['publish_course'] ?? 0))) {
                Database::update('courses', ['status' => 'published'], 'id = ?', [$cid]);
                log_activity('course', "Published course #$cid", (int)$u['id']);
                flash('success', 'Course published.');
                redirect('admin/courses');
            }
            if (($cid = (int)($_POST['restore_course'] ?? 0))) {
                Database::update('courses', ['status' => 'draft'], 'id = ?', [$cid]);
                log_activity('course', "Restored course #$cid", (int)$u['id']);
                flash('success', 'Course restored to drafts.');
                redirect('admin/courses');
            }
            if (($cid = (int)($_POST['delete_course'] ?? 0))) {
                Database::delete('courses', 'id = ?', [$cid]);
                flash('success', 'Course deleted.');
                redirect('admin/courses');
            }
        }
        Router::render('app/admin/courses', ['title' => 'All Courses', 'courses' => $courses, 'schools' => $schools, 'schoolId' => $schoolId, 'sort' => $sort, 'dir' => $dir]);
    }
}

/** Fallback random password generator (used when admin leaves password blank) */
