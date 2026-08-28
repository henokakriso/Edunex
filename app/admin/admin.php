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
        $sortMap = ['name' => 'us.first_name', 'role' => 'us.role', 'school' => 's.name', 'status' => 'us.status', 'created_at' => 'us.created_at', 'id' => 'us.id'];
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

                // Strict role hierarchy enforcement
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
                    default    => [], // teacher, parent, student, dean, etc. cannot create users
                };
                if (!in_array($role2, $allowed, true)) {
                    flash('danger', 'You do not have permission to create this role.'); redirect('admin/users' . $suffix);
                }

                $schoolId = (int)($_POST['school_id'] ?? 0);
                if (!$schoolId) $schoolId = (int)$u['school_id'];
                if (!$schoolId && in_array($role2, ['principal', 'teacher', 'student', 'parent'], true)) {
                    flash('danger', 'Select the school for this account.'); redirect('admin/users' . $suffix);
                }

                // Scope enforcement: regional/zonal/woreda admins can only create in their scope
                if (in_array($u['role'], ['regional','zonal','woreda']) && $schoolId) {
                    $school = Database::one("SELECT zone_id, woreda_id FROM schools WHERE id = ?", [$schoolId]);
                    if ($school) {
                        if ($u['role'] === 'woreda' && (int)$school['woreda_id'] !== (int)$u['woreda_id']) {
                            flash('danger', 'You can only create users in schools within your woreda.'); redirect('admin/users' . $suffix);
                        }
                        if ($u['role'] === 'zonal') {
                            $myWoredas = array_column(Database::all("SELECT id FROM woredas WHERE zone_id = ?", [(int)$u['zone_id']]), 'id');
                            if (!in_array((int)$school['woreda_id'], $myWoredas)) {
                                flash('danger', 'You can only create users in schools within your zone.'); redirect('admin/users' . $suffix);
                            }
                        }
                        if ($u['role'] === 'regional') {
                            $myZones = array_column(Database::all("SELECT id FROM zones WHERE region_admin_id = ?", [(int)$u['id']]), 'id');
                            if (!empty($myZones) && !in_array((int)$school['zone_id'], $myZones)) {
                                flash('danger', 'You can only create users in schools within your region.'); redirect('admin/users' . $suffix);
                            }
                        }
                    }
                }

                $data = [
                    'school_id' => $schoolId, 'role' => $role2,
                    'first_name' => trim($_POST['first_name']),
                    'middle_name' => trim($_POST['middle_name'] ?? ''),
                    'last_name' => trim($_POST['last_name']),
                    'gender' => trim($_POST['gender'] ?? ''),
                    'birth_date' => $_POST['birth_date'] ?: null,
                    'email' => $email,
                    'phone' => trim($_POST['phone'] ?? ''),
                    'alt_phone' => trim($_POST['alt_phone'] ?? ''),
                    'national_id' => trim($_POST['national_id'] ?? ''),
                    'fayda_id' => trim($_POST['national_id'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'kebele' => trim($_POST['kebele'] ?? ''),
                    'region' => trim($_POST['region'] ?? ''),
                    'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
                    'password_hash' => password_hash($_POST['password'] ?? random_password(), PASSWORD_BCRYPT),
                    'status' => trim($_POST['status'] ?? 'active'),
                    'username' => trim($_POST['username'] ?? ''),
                ];

                // Admin-specific fields
                if ($role2 === 'regional') {
                    $data['admin_type'] = trim($_POST['admin_type'] ?? '');
                    $data['assigned_region'] = trim($_POST['assigned_region'] ?? '');
                    $data['assigned_zone'] = trim($_POST['assigned_zone'] ?? '');
                    $data['assigned_woreda'] = trim($_POST['assigned_woreda'] ?? '');
                    $data['start_date'] = $_POST['start_date'] ?: null;
                    $data['end_date'] = $_POST['end_date'] ?: null;
                    $data['twofa_required'] = (int)($_POST['twofa_required'] ?? 0);
                }

                // Student-specific fields
                if ($role2 === 'student') {
                    $data['student_id'] = generate_student_id((int)$schoolId);
                    $data['group_id'] = (int)($_POST['group_id'] ?? 0) ?: null;
                    $data['birth_cert_number'] = trim($_POST['birth_cert_number'] ?? '');
                    $data['student_type'] = trim($_POST['student_type'] ?? 'regular');
                    $data['previous_school'] = trim($_POST['previous_school'] ?? '');
                    $data['previous_grade'] = trim($_POST['previous_grade'] ?? '');
                    $data['enrollment_date'] = $_POST['enrollment_date'] ?: date('Y-m-d');
                    $data['disability_support'] = (int)($_POST['disability_support'] ?? 0);
                    $data['special_needs'] = trim($_POST['special_needs'] ?? '');
                    $data['language'] = trim($_POST['language'] ?? 'amharic');
                    // Store parent info
                    $data['parent_id'] = null; // Will be linked later
                }

                // Parent-specific fields
                if ($role2 === 'parent') {
                    $data['relationship'] = trim($_POST['relationship'] ?? '');
                    $data['linked_student_ids'] = trim($_POST['linked_student_ids'] ?? '');
                }

                // Teacher/Staff fields
                if (in_array($role2, ['teacher','lecturer','hod','principal','registrar','dean','vice_dean','bursar','student_affairs','librarian'], true)) {
                    $data['department_id'] = (int)($_POST['department_id'] ?? 0) ?: null;
                    $data['qualification'] = trim($_POST['qualification'] ?? '');
                    $data['specialization'] = trim($_POST['specialization'] ?? '');
                    $data['certification'] = trim($_POST['certification'] ?? '');
                    $data['experience_years'] = (int)($_POST['experience_years'] ?? 0) ?: null;
                    $data['employment_type'] = trim($_POST['employment_type'] ?? '');
                    $data['hire_date'] = $_POST['hire_date'] ?: null;
                    $data['position'] = trim($_POST['position'] ?? '');
                    $data['grade_levels'] = trim($_POST['grade_levels'] ?? '');
                    $data['sections'] = trim($_POST['sections'] ?? '');
                    $data['employee_id'] = trim($_POST['employee_id'] ?? '');
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
            'sort' => $sort, 'dir' => $dir, 'page' => $page, 'pages' => $pages, 'total' => $total, 'pager' => $pager, 'perPage' => $perPage,
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
        $approval = $_GET['approval'] ?? '';
        if (in_array($approval, ['pending', 'regional_approved', 'ministry_approved', 'rejected'], true)) { $where .= " AND s.approval_status = ?"; $args[] = $approval; }

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
                $name = trim($_POST['name'] ?? '');
                $code = strtoupper(trim($_POST['code'] ?? ''));
                if (!$name) { flash('danger', 'School name required.'); redirect('admin/schools' . $suffix); }

                // Scope enforcement: non-ministry admins can only create in their scope
                if (in_array($u['role'], ['zonal','woreda'])) {
                    $schoolZone = (int)($_POST['zone_id'] ?? 0);
                    $schoolWoreda = (int)($_POST['woreda_id'] ?? 0);
                    if ($u['role'] === 'woreda' && $schoolWoreda && $schoolWoreda !== (int)$u['woreda_id']) {
                        flash('danger', 'You can only create schools in your woreda.'); redirect('admin/schools' . $suffix);
                    }
                    if ($u['role'] === 'zonal') {
                        $myWoredas = array_column(Database::all("SELECT id FROM woredas WHERE zone_id = ?", [(int)$u['zone_id']]), 'id');
                        if ($schoolWoreda && !in_array($schoolWoreda, $myWoredas)) {
                            flash('danger', 'You can only create schools in your zone.'); redirect('admin/schools' . $suffix);
                        }
                    }
                }

                // Auto-generate code if blank
                if (!$code) {
                    $words = preg_split('/\s+/', $name);
                    $code = '';
                    foreach ($words as $w) { $code .= mb_strtoupper(mb_substr($w, 0, 1)); if (mb_strlen($code) >= 3) break; }
                    if (mb_strlen($code) < 3) $code = mb_strtoupper(mb_substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
                    if (mb_strlen($code) < 3) $code = 'SCH';
                    // Ensure unique
                    $base = $code; $n = 1;
                    while (Database::one("SELECT id FROM schools WHERE code = ?", [$code])) { $code = $base . ($n++); }
                } elseif (Database::one("SELECT id FROM schools WHERE code = ?", [$code])) {
                    flash('danger', 'School code already exists.'); redirect('admin/schools' . $suffix);
                }

                // Auto-generate School ID number
                $yr = date('Y');
                $seq = (int)Database::scalar("SELECT COUNT(*)+1 FROM schools WHERE YEAR(created_at) = ?", [$yr], 1);
                $schoolIdNum = sprintf('SCH-%s-%06d', $yr, $seq);

                // Auto-generate Tenant ID
                $tenantId = 'TNT-' . strtoupper(bin2hex(random_bytes(4)));

                $data = [
                    'name' => $name, 'code' => $code,
                    'type' => 'university',
                    'school_type' => $_POST['school_type'] ?? 'public',
                    'education_level' => $_POST['education_level'] ?? 'university',
                    'school_description' => trim($_POST['school_description'] ?? ''),
                    'established_year' => (int)($_POST['established_year'] ?? 0) ?: null,
                    'region' => trim($_POST['region'] ?? ''),
                    'zone_id' => (int)($_POST['zone_id'] ?? 0) ?: null,
                    'woreda_id' => (int)($_POST['woreda_id'] ?? 0) ?: null,
                    'kebele' => trim($_POST['kebele'] ?? ''),
                    'city' => trim($_POST['city'] ?? ''),
                    'street_address' => trim($_POST['street_address'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'gps_lat' => $_POST['gps_lat'] ?: null,
                    'gps_lng' => $_POST['gps_lng'] ?: null,
                    'phone' => trim($_POST['phone'] ?? ''),
                    'alt_phone' => trim($_POST['alt_phone'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'website' => trim($_POST['website'] ?? ''),
                    'emergency_contact' => trim($_POST['emergency_contact'] ?? ''),
                    'postal_address' => trim($_POST['postal_address'] ?? ''),
                    'director_name' => trim($_POST['director_name'] ?? ''),
                    'director_phone' => trim($_POST['director_phone'] ?? ''),
                    'director_email' => trim($_POST['director_email'] ?? ''),
                    'admin_name' => trim($_POST['admin_name'] ?? ''),
                    'admin_phone' => trim($_POST['admin_phone'] ?? ''),
                    'academic_year' => trim($_POST['academic_year'] ?? ''),
                    'grade_levels' => trim($_POST['grade_levels'] ?? ''),
                    'sections' => trim($_POST['sections'] ?? ''),
                    'max_capacity' => (int)($_POST['max_capacity'] ?? 0) ?: null,
                    'teaching_language' => trim($_POST['teaching_language'] ?? 'Amharic'),
                    'second_language' => trim($_POST['second_language'] ?? ''),
                    'grading_system' => trim($_POST['grading_system'] ?? 'percentage'),
                    'attendance_system' => trim($_POST['attendance_system'] ?? 'daily'),
                    'school_calendar' => trim($_POST['school_calendar'] ?? 'ethiopian'),
                    'tenant_id' => $tenantId,
                    'school_id_number' => $schoolIdNum,
                    'subscription_plan' => 'free',
                    'enabled_modules' => json_encode($_POST['modules'] ?? []),
                    'status' => $u['role'] === 'ministry' ? 'active' : 'pending',
                    'approval_status' => $u['role'] === 'ministry' ? 'ministry_approved' : 'pending',
                    'approved_by' => $u['role'] === 'ministry' ? (int)$u['id'] : null,
                    'approved_at' => $u['role'] === 'ministry' ? date('Y-m-d H:i:s') : null,
                ];
                $newSchoolId = Database::insert('schools', $data);
                if ($u['role'] === 'ministry') {
                    ensure_school_modules((int)$newSchoolId);
                    log_activity('school', "Created university: {$name} ({$schoolIdNum})", (int)$u['id']);
                    flash('success', "University created and activated! School ID: {$schoolIdNum} · Tenant: {$tenantId}.");
                } else {
                    log_activity('school', "Requested new university: {$name} ({$schoolIdNum})", (int)$u['id']);
                    flash('success', "University request submitted! School ID: {$schoolIdNum} · Tenant: {$tenantId}. Awaiting regional approval.");
                }
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
            if (($sid = (int)($_POST['approve_school_regional'] ?? 0))) {
                $target = Database::one("SELECT id, name, approval_status FROM schools WHERE id = ?", [$sid]);
                if ($target && $target['approval_status'] === 'pending') {
                    Database::update('schools', [
                        'approval_status' => 'regional_approved',
                        'approved_by' => (int)$u['id'],
                        'approved_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$sid]);
                    log_activity('school', "Regionally approved: {$target['name']}", (int)$u['id']);
                    flash('success', "Approved {$target['name']} at regional level. Awaiting ministry approval.");
                }
                redirect('admin/schools' . $suffix);
            }
            if (($sid = (int)($_POST['approve_school_ministry'] ?? 0))) {
                $target = Database::one("SELECT id, name, approval_status FROM schools WHERE id = ?", [$sid]);
                if ($target && $target['approval_status'] === 'regional_approved') {
                    Database::update('schools', [
                        'approval_status' => 'ministry_approved',
                        'status' => 'active',
                        'approved_by' => (int)$u['id'],
                        'approved_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$sid]);
                    ensure_school_modules($sid);
                    log_activity('school', "Ministry approved & activated: {$target['name']}", (int)$u['id']);
                    flash('success', "{$target['name']} approved and activated.");
                }
                redirect('admin/schools' . $suffix);
            }
            if (($sid = (int)($_POST['reject_school'] ?? 0))) {
                $target = Database::one("SELECT id, name FROM schools WHERE id = ?", [$sid]);
                if ($target) {
                    Database::update('schools', [
                        'approval_status' => 'rejected',
                        'status' => 'suspended',
                        'approved_by' => (int)$u['id'],
                        'approved_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$sid]);
                    log_activity('school', "Rejected: {$target['name']}", (int)$u['id']);
                    flash('danger', "{$target['name']} rejected.");
                }
                redirect('admin/schools' . $suffix);
            }
        }
        $isPartial = ($_GET['partial'] ?? '') === '1';
        Router::render($isPartial ? 'app/admin/schools_partial' : 'app/admin/schools', [
            'title' => 'Schools', 'schools' => $schools, 'q' => $q, 'type' => $type, 'status' => $status, 'approval' => $approval,
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
        $schoolId = (int)($_GET['school'] ?? 0);
        $region = trim($_GET['region'] ?? '');
        $zone = trim($_GET['zone'] ?? '');
        $type = trim($_GET['type'] ?? '');
        $sort = $_GET['sort'] ?? 'name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $sortMap = ['name' => 'd.name', 'members' => 'members', 'status' => 'd.status'];
        if (!isset($sortMap[$sort])) $sort = 'name';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', d.id DESC';
        $schools = Database::all("SELECT id, name, type, region, zone_id FROM schools WHERE status = 'active' ORDER BY type, region, name");

        // Filter schools by region/zone/type
        $filteredSchools = $schools;
        if ($region) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => ($s['region'] ?: 'Other') === $region));
        if ($zone) {
            // Match by zone name via zones table join
            $zoneIds = array_column(Database::all("SELECT id FROM zones WHERE name = ?", [$zone]), 'id');
            if ($zoneIds) {
                $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => in_array((int)($s['zone_id'] ?? 0), $zoneIds)));
            } else {
                $filteredSchools = [];
            }
        }
        if ($type) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => ($s['type'] ?: 'school') === $type));

        // Build school IDs for query
        $schoolIds = array_column($filteredSchools, 'id');
        $hasFilter = $schoolId || $region || $zone || $type;

        $where = "1=1";
        $args = [];
        if (!$hasFilter) {
            // No filter selected — show empty with prompt message
            $where .= " AND 1=0";
        } elseif ($schoolId) {
            $where .= " AND d.school_id = ?";
            $args[] = $schoolId;
        } elseif ($schoolIds) {
            $placeholders = implode(',', array_fill(0, count($schoolIds), '?'));
            $where .= " AND d.school_id IN ($placeholders)";
            $args = array_merge($args, $schoolIds);
        } else {
            $where .= " AND 1=0";
        }

        $depts = Database::all("SELECT d.*, s.name AS school_name, s.type AS school_type, s.region AS school_region, (SELECT COUNT(*) FROM users us WHERE us.department_id = d.id) AS members FROM departments d JOIN schools s ON s.id = d.school_id WHERE $where ORDER BY $orderBy", $args);

        // Unique regions
        $regions = array_values(array_unique(array_map(fn($s) => $s['region'] ?: 'Other', $schools)));
        sort($regions);

        // Zones for selected region
        $allZones = [];
        if ($region) {
            $regionObj = Database::one("SELECT id FROM regions WHERE name = ?", [$region]);
            if ($regionObj) {
                $allZones = array_column(Database::all("SELECT name FROM zones WHERE region_id = ? ORDER BY name", [(int)$regionObj['id']]), 'name');
            }
        }

        // Types for selected region/zone
        $typesForDropdown = $types;
        if ($region || $zone) {
            $typesForDropdown = array_values(array_unique(array_map(fn($s) => $s['type'] ?: 'school', $filteredSchools)));
            usort($typesForDropdown, fn($a, $b) => array_search($a, ['university','college','school','training','other']) - array_search($b, ['university','college','school','training','other']));
        }

        $pager = fn(int $p): string => url('admin/departments?' . http_build_query(array_filter(['region' => $region, 'zone' => $zone, 'type' => $type, 'school' => $schoolId ?: '', 'sort' => $sort, 'dir' => $dir, 'page' => $p], fn($x) => $x !== '')));

        Router::render('app/admin/departments', [
            'title' => 'Departments', 'depts' => $depts, 'schools' => $schools,
            'schoolId' => $schoolId, 'region' => $region, 'zone' => $zone, 'type' => $type,
            'regions' => $regions, 'allZones' => $allZones, 'types' => $typesForDropdown,
            'sort' => $sort, 'dir' => $dir, 'pager' => $pager,
        ]);
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
        $region = trim($_GET['region'] ?? '');
        $zone = trim($_GET['zone'] ?? '');
        $type = trim($_GET['type'] ?? '');
        $schoolId = (int)($_GET['school'] ?? 0);
        $deptId = (int)($_GET['dept'] ?? 0);
        $sort = $_GET['sort'] ?? 'name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $sortMap = ['name' => 's.name', 'code' => 's.code', 'school' => 'sc.name', 'department' => 'd.name', 'status' => 's.status'];
        if (!isset($sortMap[$sort])) $sort = 'name';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', s.id DESC';

        $schools = Database::all("SELECT id, name, type, region, zone_id FROM schools WHERE status = 'active' ORDER BY type, region, name");

        // Cascade filters
        $filteredSchools = $schools;
        if ($region) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => ($s['region'] ?: 'Other') === $region));
        if ($zone) {
            $zoneIds = array_column(Database::all("SELECT id FROM zones WHERE name = ?", [$zone]), 'id');
            $filteredSchools = $zoneIds ? array_values(array_filter($filteredSchools, fn($s) => in_array((int)($s['zone_id'] ?? 0), $zoneIds))) : [];
        }
        if ($type) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => ($s['type'] ?: 'school') === $type));
        if ($schoolId) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => (int)$s['id'] === $schoolId));

        $schoolIds = array_column($filteredSchools, 'id');
        $hasFilter = $region || $zone || $type || $schoolId || $deptId;

        // Departments for selected school(s)
        $filteredDepts = Database::all("SELECT id, name, school_id FROM departments WHERE status = 'active'" . ($schoolIds ? " AND school_id IN (" . implode(',', array_fill(0, count($schoolIds), '?')) . ")" : "") . " ORDER BY name", $schoolIds);
        if ($deptId) $filteredDepts = array_values(array_filter($filteredDepts, fn($d) => (int)$d['id'] === $deptId));

        // Build query
        $where = "1=1";
        $args = [];
        if (!$hasFilter) {
            $where .= " AND 1=0";
        } elseif ($deptId) {
            $where .= " AND s.department_id = ?";
            $args[] = $deptId;
        } elseif ($schoolIds) {
            $placeholders = implode(',', array_fill(0, count($schoolIds), '?'));
            $where .= " AND s.school_id IN ($placeholders)";
            $args = array_merge($args, $schoolIds);
        } else {
            $where .= " AND 1=0";
        }

        $subjects = Database::all("SELECT s.*, sc.name AS school_name, d.name AS dept_name FROM subjects s JOIN schools sc ON sc.id = s.school_id LEFT JOIN departments d ON d.id = s.department_id WHERE $where ORDER BY $orderBy", $args);

        // Filter dropdowns
        $regions = array_values(array_unique(array_map(fn($s) => $s['region'] ?: 'Other', $schools)));
        sort($regions);
        $allZones = [];
        if ($region) {
            $regionObj = Database::one("SELECT id FROM regions WHERE name = ?", [$region]);
            if ($regionObj) $allZones = array_column(Database::all("SELECT name FROM zones WHERE region_id = ? ORDER BY name", [(int)$regionObj['id']]), 'name');
        }
        $types = array_values(array_unique(array_map(fn($s) => $s['type'] ?: 'school', $filteredSchools)));
        usort($types, fn($a, $b) => array_search($a, ['university','college','school','training','other']) - array_search($b, ['university','college','school','training','other']));

        $pager = fn(int $p): string => url('admin/subjects?' . http_build_query(array_filter(['region'=>$region,'zone'=>$zone,'type'=>$type,'school'=>$schoolId,'dept'=>$deptId,'sort'=>$sort,'dir'=>$dir,'page'=>$p], fn($x) => $x !== '')));

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

        Router::render('app/admin/subjects', [
            'title' => 'Subjects', 'subjects' => $subjects, 'schools' => $schools,
            'depts' => $filteredDepts, 'allDepts' => $filteredDepts,
            'region' => $region, 'zone' => $zone, 'type' => $type,
            'schoolId' => $schoolId, 'deptId' => $deptId,
            'regions' => $regions, 'allZones' => $allZones, 'types' => $types,
            'sort' => $sort, 'dir' => $dir, 'pager' => $pager,
        ]);
    }
}

/* =============== ADMIN: student groups =============== */
class Ctl_groups {
    public function run(): void {
        $u = require_role('ministry');
        $region = trim($_GET['region'] ?? '');
        $zone = trim($_GET['zone'] ?? '');
        $type = trim($_GET['type'] ?? '');
        $schoolId = (int)($_GET['school'] ?? 0);
        $sort = $_GET['sort'] ?? 'name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $sortMap = ['name' => 'g.name', 'school' => 's.name', 'grade' => 'g.grade', 'section' => 'g.section', 'students' => 'members'];
        if (!isset($sortMap[$sort])) $sort = 'name';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', g.id DESC';

        $schools = Database::all("SELECT id, name, type, region, zone_id FROM schools WHERE status = 'active' ORDER BY type, region, name");
        $filteredSchools = $schools;
        if ($region) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => ($s['region'] ?: 'Other') === $region));
        if ($zone) {
            $zoneIds = array_column(Database::all("SELECT id FROM zones WHERE name = ?", [$zone]), 'id');
            $filteredSchools = $zoneIds ? array_values(array_filter($filteredSchools, fn($s) => in_array((int)($s['zone_id'] ?? 0), $zoneIds))) : [];
        }
        if ($type) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => ($s['type'] ?: 'school') === $type));
        if ($schoolId) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => (int)$s['id'] === $schoolId));

        $schoolIds = array_column($filteredSchools, 'id');
        $hasFilter = $region || $zone || $type || $schoolId;

        $where = "1=1";
        $args = [];
        if (!$hasFilter) {
            $where .= " AND 1=0";
        } elseif ($schoolIds) {
            $placeholders = implode(',', array_fill(0, count($schoolIds), '?'));
            $where .= " AND g.school_id IN ($placeholders)";
            $args = array_merge($args, $schoolIds);
        } else {
            $where .= " AND 1=0";
        }

        $groups = Database::all("SELECT g.*, s.name AS school_name, (SELECT COUNT(*) FROM users us WHERE us.group_id = g.id) AS members FROM student_groups g JOIN schools s ON s.id = g.school_id WHERE $where ORDER BY $orderBy", $args);

        $regions = array_values(array_unique(array_map(fn($s) => $s['region'] ?: 'Other', $schools)));
        sort($regions);
        $allZones = [];
        if ($region) {
            $regionObj = Database::one("SELECT id FROM regions WHERE name = ?", [$region]);
            if ($regionObj) $allZones = array_column(Database::all("SELECT name FROM zones WHERE region_id = ? ORDER BY name", [(int)$regionObj['id']]), 'name');
        }
        $types = array_values(array_unique(array_map(fn($s) => $s['type'] ?: 'school', $filteredSchools)));
        usort($types, fn($a, $b) => array_search($a, ['university','college','school','training','other']) - array_search($b, ['university','college','school','training','other']));

        $pager = fn(int $p): string => url('admin/groups?' . http_build_query(array_filter(['region'=>$region,'zone'=>$zone,'type'=>$type,'school'=>$schoolId,'sort'=>$sort,'dir'=>$dir,'page'=>$p], fn($x) => $x !== '')));

        Router::render('app/admin/groups', [
            'title' => 'Classes', 'groups' => $groups, 'schools' => $schools,
            'region' => $region, 'zone' => $zone, 'type' => $type, 'schoolId' => $schoolId,
            'regions' => $regions, 'allZones' => $allZones, 'types' => $types,
            'sort' => $sort, 'dir' => $dir, 'pager' => $pager,
        ]);
    }
}

/* =============== ADMIN: academic years =============== */
class Ctl_years {
    public function run(): void {
        $u = require_role('ministry');
        $schools = Database::all("SELECT id, name, type FROM schools WHERE status = 'active' ORDER BY type, name");

        // Shared calendars
        $sharedYears = Database::all("SELECT y.*, 'Shared' AS school_name FROM academic_years y WHERE y.is_shared = 1 ORDER BY y.education_level, y.start_date DESC");
        foreach ($sharedYears as &$y) {
            $y['semesters'] = Database::all("SELECT * FROM semesters WHERE year_id = ? ORDER BY sort_order, start_date", [$y['id']]);
            $y['applied_count'] = Database::one("SELECT COUNT(*) AS c FROM academic_years WHERE education_level = ? AND is_shared = 0 AND name = ?", [$y['education_level'], $y['name']])['c'] ?? 0;
        }

        // Individual calendars
        $individualYears = Database::all("SELECT y.*, s.name AS school_name FROM academic_years y JOIN schools s ON s.id = y.school_id WHERE y.is_shared = 0 ORDER BY y.start_date DESC");
        foreach ($individualYears as &$y) {
            $y['semesters'] = Database::all("SELECT * FROM semesters WHERE year_id = ? ORDER BY sort_order, start_date", [$y['id']]);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            if (isset($_POST['create_year'])) {
                $isShared = isset($_POST['is_shared']) ? 1 : 0;
                $eduLevel = $_POST['education_level'] ?? 'school';
                $yearId = Database::insert('academic_years', [
                    'school_id' => null,
                    'name' => trim($_POST['name']),
                    'education_level' => $eduLevel, 'is_shared' => $isShared,
                    'ethiopian_year' => trim($_POST['ethiopian_year'] ?? ''),
                    'start_date' => $_POST['start_date'] ?: null, 'end_date' => $_POST['end_date'] ?: null,
                    'ethiopian_start' => trim($_POST['ethiopian_start'] ?? ''),
                    'ethiopian_end' => trim($_POST['ethiopian_end'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'status' => $_POST['status'] ?? 'active',
                    'num_semesters' => (int)($_POST['num_semesters'] ?? 2),
                    'primary_calendar' => $_POST['primary_calendar'] ?? 'ethiopian',
                    'weekend_days' => trim($_POST['weekend_days'] ?? 'fri,sat'),
                    'working_days_per_week' => (int)($_POST['working_days_per_week'] ?? 5),
                    'school_days_target' => (int)($_POST['school_days_target'] ?: 0) ?: null,
                ]);
                for ($i = 1; $i <= (int)($_POST['num_semesters'] ?? 2); $i++) {
                    Database::insert('semesters', [
                        'year_id' => $yearId, 'name' => trim($_POST["semester_{$i}_name"] ?? '') ?: "Semester $i",
                        'start_date' => $_POST["semester_{$i}_start"] ?: null,
                        'end_date' => $_POST["semester_{$i}_end"] ?: null,
                        'sort_order' => $i,
                    ]);
                }
                log_activity('academic_year', "Created " . ($isShared ? "shared $eduLevel" : "individual") . " calendar: " . trim($_POST['name']), (int)$u['id']);
                flash('success', $isShared ? "Shared calendar created for all {$eduLevel}s." : 'Academic year created.');
                redirect('admin/years');
            }

            if (isset($_POST['apply_shared'])) {
                $shared = Database::one("SELECT * FROM academic_years WHERE id = ?", [(int)$_POST['apply_shared']]);
                if ($shared) {
                    $levelSchools = $shared['education_level'] === 'university'
                        ? Database::all("SELECT id, name FROM schools WHERE type = 'university' AND status = 'active'")
                        : Database::all("SELECT id, name FROM schools WHERE type != 'university' AND status = 'active'");
                    $applied = 0;
                    foreach ($levelSchools as $s) {
                        $exists = Database::one("SELECT id FROM academic_years WHERE school_id = ? AND name = ? AND is_shared = 0", [(int)$s['id'], $shared['name']]);
                        if (!$exists) {
                            $newId = Database::insert('academic_years', [
                                'school_id' => (int)$s['id'], 'name' => $shared['name'],
                                'education_level' => $shared['education_level'], 'is_shared' => 0,
                                'ethiopian_year' => $shared['ethiopian_year'],
                                'start_date' => $shared['start_date'], 'end_date' => $shared['end_date'],
                                'ethiopian_start' => $shared['ethiopian_start'], 'ethiopian_end' => $shared['ethiopian_end'],
                                'status' => $shared['status'], 'num_semesters' => $shared['num_semesters'],
                                'primary_calendar' => $shared['primary_calendar'],
                                'weekend_days' => $shared['weekend_days'],
                                'working_days_per_week' => $shared['working_days_per_week'],
                                'school_days_target' => $shared['school_days_target'],
                            ]);
                            $sems = Database::all("SELECT * FROM semesters WHERE year_id = ?", [$shared['id']]);
                            foreach ($sems as $sem) {
                                Database::insert('semesters', [
                                    'year_id' => $newId, 'name' => $sem['name'],
                                    'start_date' => $sem['start_date'], 'end_date' => $sem['end_date'],
                                    'sort_order' => $sem['sort_order'],
                                ]);
                            }
                            $applied++;
                        }
                    }
                    log_activity('academic_year', "Applied shared calendar to $applied " . $shared['education_level'] . "s", (int)$u['id']);
                    flash('success', "Applied to $applied schools.");
                }
                redirect('admin/years');
            }

            if (isset($_POST['set_current'])) {
                $yr = Database::one("SELECT school_id, education_level, is_shared FROM academic_years WHERE id = ?", [(int)$_POST['set_current']]);
                if ($yr) {
                    if ($yr['is_shared']) {
                        Database::run("UPDATE academic_years SET is_current = 0 WHERE education_level = ? AND is_shared = 1", [$yr['education_level']]);
                    } else {
                        Database::run("UPDATE academic_years SET is_current = 0 WHERE school_id = ?", [(int)$yr['school_id']]);
                    }
                    Database::update('academic_years', ['is_current' => 1], 'id = ?', [(int)$_POST['set_current']]);
                }
                flash('success', 'Current year updated.');
                redirect('admin/years');
            }

            if (isset($_POST['set_status'])) {
                Database::update('academic_years', ['status' => $_POST['status']], 'id = ?', [(int)$_POST['set_status']]);
                flash('success', 'Status updated.');
                redirect('admin/years');
            }

            if (isset($_POST['update_year'])) {
                $uid = (int)$_POST['update_year'];
                Database::update('academic_years', [
                    'school_id' => (int)$_POST['school_id'], 'name' => trim($_POST['name']),
                    'ethiopian_year' => trim($_POST['ethiopian_year'] ?? ''),
                    'start_date' => $_POST['start_date'] ?: null, 'end_date' => $_POST['end_date'] ?: null,
                    'ethiopian_start' => trim($_POST['ethiopian_start'] ?? ''),
                    'ethiopian_end' => trim($_POST['ethiopian_end'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'status' => $_POST['status'] ?? 'draft',
                    'num_semesters' => (int)($_POST['num_semesters'] ?? 2),
                    'primary_calendar' => $_POST['primary_calendar'] ?? 'ethiopian',
                    'weekend_days' => trim($_POST['weekend_days'] ?? 'fri,sat'),
                    'working_days_per_week' => (int)($_POST['working_days_per_week'] ?? 5),
                ], 'id = ?', [$uid]);
                flash('success', 'Academic year updated.');
                redirect('admin/years');
            }

            if (($uid = (int)($_POST['delete_year'] ?? 0))) {
                Database::run("DELETE FROM semesters WHERE year_id = ?", [$uid]);
                Database::delete('academic_years', 'id = ?', [$uid]);
                flash('success', 'Academic year deleted.');
                redirect('admin/years');
            }

            if (isset($_POST['create_semester'])) {
                Database::insert('semesters', [
                    'year_id' => (int)$_POST['year_id'], 'name' => trim($_POST['name']),
                    'start_date' => $_POST['start_date'] ?: null, 'end_date' => $_POST['end_date'] ?: null,
                    'sort_order' => (int)($_POST['sort_order'] ?? 0),
                ]);
                flash('success', 'Semester added.');
                redirect('admin/years');
            }

            if (isset($_POST['update_semester'])) {
                Database::update('semesters', [
                    'name' => trim($_POST['name']),
                    'start_date' => $_POST['start_date'] ?: null,
                    'end_date' => $_POST['end_date'] ?: null,
                ], 'id = ?', [(int)$_POST['update_semester']]);
                flash('success', 'Semester updated.');
                redirect('admin/years');
            }

            if (($sid = (int)($_POST['delete_semester'] ?? 0))) {
                Database::delete('semesters', 'id = ?', [$sid]);
                flash('success', 'Semester deleted.');
                redirect('admin/years');
            }
        }

        Router::render('app/admin/years', [
            'title' => 'Academic Years', 'schools' => $schools,
            'sharedYears' => $sharedYears, 'individualYears' => $individualYears,
        ]);
    }
}

/* =============== ADMIN: courses =============== */
class Ctl_courses {
    public function run(): void {
        $u = require_role('ministry');
        $region = trim($_GET['region'] ?? '');
        $zone = trim($_GET['zone'] ?? '');
        $type = trim($_GET['type'] ?? '');
        $schoolId = (int)($_GET['school'] ?? 0);
        $sort = $_GET['sort'] ?? 'created_at';
        $dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $sortMap = ['name' => 'c.title', 'school' => 's.name', 'teacher' => 'u.last_name', 'students' => 'students', 'lessons' => 'lessons', 'status' => 'c.status', 'created_at' => 'c.created_at'];
        if (!isset($sortMap[$sort])) $sort = 'created_at';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', c.id DESC';

        $schools = Database::all("SELECT id, name, type, region, zone_id FROM schools WHERE status = 'active' ORDER BY type, region, name");
        $filteredSchools = $schools;
        if ($region) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => ($s['region'] ?: 'Other') === $region));
        if ($zone) {
            $zoneIds = array_column(Database::all("SELECT id FROM zones WHERE name = ?", [$zone]), 'id');
            $filteredSchools = $zoneIds ? array_values(array_filter($filteredSchools, fn($s) => in_array((int)($s['zone_id'] ?? 0), $zoneIds))) : [];
        }
        if ($type) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => ($s['type'] ?: 'school') === $type));
        if ($schoolId) $filteredSchools = array_values(array_filter($filteredSchools, fn($s) => (int)$s['id'] === $schoolId));

        $schoolIds = array_column($filteredSchools, 'id');
        $hasFilter = $region || $zone || $type || $schoolId;

        $where = "1=1";
        $args = [];
        if (!$hasFilter) {
            $where .= " AND 1=0";
        } elseif ($schoolIds) {
            $placeholders = implode(',', array_fill(0, count($schoolIds), '?'));
            $where .= " AND c.school_id IN ($placeholders)";
            $args = array_merge($args, $schoolIds);
        } else {
            $where .= " AND 1=0";
        }

        $courses = Database::all(
            "SELECT c.*, s.name AS school_name, u.first_name AS tfirst, u.last_name AS tlast,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lessons
             FROM courses c JOIN schools s ON s.id = c.school_id JOIN users u ON u.id = c.teacher_id
             WHERE $where ORDER BY $orderBy", $args);

        $regions = array_values(array_unique(array_map(fn($s) => $s['region'] ?: 'Other', $schools)));
        sort($regions);
        $allZones = [];
        if ($region) {
            $regionObj = Database::one("SELECT id FROM regions WHERE name = ?", [$region]);
            if ($regionObj) $allZones = array_column(Database::all("SELECT name FROM zones WHERE region_id = ? ORDER BY name", [(int)$regionObj['id']]), 'name');
        }
        $types = array_values(array_unique(array_map(fn($s) => $s['type'] ?: 'school', $filteredSchools)));
        usort($types, fn($a, $b) => array_search($a, ['university','college','school','training','other']) - array_search($b, ['university','college','school','training','other']));

        $pager = fn(int $p): string => url('admin/courses?' . http_build_query(array_filter(['region'=>$region,'zone'=>$zone,'type'=>$type,'school'=>$schoolId,'sort'=>$sort,'dir'=>$dir,'page'=>$p], fn($x) => $x !== '')));

        Router::render('app/admin/courses', [
            'title' => 'All Courses', 'courses' => $courses, 'schools' => $schools,
            'region' => $region, 'zone' => $zone, 'type' => $type, 'schoolId' => $schoolId,
            'regions' => $regions, 'allZones' => $allZones, 'types' => $types,
            'sort' => $sort, 'dir' => $dir, 'pager' => $pager,
        ]);
    }
}

/* =============== ADMIN: calendar events =============== */
class Ctl_calendar {
    public function run(): void {
        $u = require_role('ministry');
        $region = trim($_GET['region'] ?? '');
        $schoolId = (int)($_GET['school'] ?? 0);
        $yearId = (int)($_GET['year'] ?? 0);
        $type = trim($_GET['type'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $month = (int)($_GET['month'] ?? date('n'));
        $year = (int)($_GET['year_filter'] ?? date('Y'));

        $schools = Database::all("SELECT id, name, type, region FROM schools WHERE status = 'active' ORDER BY name");
        $years = Database::all("SELECT id, name, school_id, status FROM academic_years ORDER BY start_date DESC");
        $events = Database::all("SELECT e.*, s.name AS school_name FROM calendar_events e LEFT JOIN schools s ON s.id = e.school_id ORDER BY e.gregorian_start DESC LIMIT 200");

        $eventTypeLabels = [
            'academic'=>'Academic','examination'=>'Examination','registration'=>'Registration',
            'holiday'=>'Holiday','national_celebration'=>'National Celebration','memorial_day'=>'Memorial Day',
            'religious'=>'Religious Holiday','ministry'=>'Ministry Event','regional'=>'Regional Event',
            'school'=>'School Event','training'=>'Training','competition'=>'Competition',
            'cultural'=>'Cultural Event','sports'=>'Sports Event','parent'=>'Parent Event',
            'teacher'=>'Teacher Event','other'=>'Other',
        ];
        $statusColors = ['draft'=>'badge-muted','pending_approval'=>'badge-warning','approved'=>'badge-accent','published'=>'badge-success','cancelled'=>'badge-danger'];
        $scopeIcons = ['national'=>'&#127987;','regional'=>'&#127963;','zonal'=>'&#127970;','woreda'=>'&#127966;','school'=>'&#127979;'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_event'])) {
                $evId = Database::insert('calendar_events', [
                    'academic_year_id' => (int)($_POST['academic_year_id'] ?: 0) ?: null,
                    'semester_id' => (int)($_POST['semester_id'] ?: 0) ?: null,
                    'school_id' => (int)($_POST['school_id'] ?: 0) ?: null,
                    'title' => trim($_POST['title']),
                    'title_am' => trim($_POST['title_am'] ?? ''),
                    'title_om' => trim($_POST['title_om'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'event_type' => $_POST['event_type'] ?? 'other',
                    'category' => $_POST['category'] ?? 'school',
                    'priority' => $_POST['priority'] ?? 'normal',
                    'ethiopian_date' => trim($_POST['ethiopian_date'] ?? ''),
                    'gregorian_start' => $_POST['gregorian_start'],
                    'gregorian_end' => $_POST['gregorian_end'] ?: null,
                    'start_time' => $_POST['start_time'] ?: null,
                    'end_time' => $_POST['end_time'] ?: null,
                    'all_day' => isset($_POST['all_day']) ? 1 : 0,
                    'scope_type' => $_POST['scope_type'] ?? 'national',
                    'scope_id' => (int)($_POST['scope_id'] ?: 0) ?: null,
                    'issuing_authority' => $_POST['issuing_authority'] ?? 'school',
                    'authority_name' => trim($_POST['authority_name'] ?? ''),
                    'directive_number' => trim($_POST['directive_number'] ?? ''),
                    'school_closed' => isset($_POST['school_closed']) ? 1 : 0,
                    'teaching_suspended' => isset($_POST['teaching_suspended']) ? 1 : 0,
                    'examination_suspended' => isset($_POST['examination_suspended']) ? 1 : 0,
                    'attendance_required' => isset($_POST['attendance_required']) ? 1 : 0,
                    'is_academic_day' => isset($_POST['is_academic_day']) ? 1 : 0,
                    'makeup_day_required' => isset($_POST['makeup_day_required']) ? 1 : 0,
                    'affects_academic_days' => isset($_POST['affects_academic_days']) ? 1 : 0,
                    'affects_semester' => isset($_POST['affects_semester']) ? 1 : 0,
                    'status' => $_POST['event_status'] ?? 'draft',
                    'created_by' => (int)$u['id'],
                ]);
                log_activity('calendar_event', "Created event " . trim($_POST['title']), (int)$u['id']);
                flash('success', 'Calendar event created.');
                redirect('admin/calendar');
            }
            if (isset($_POST['approve_event'])) {
                Database::update('calendar_events', [
                    'status' => 'approved', 'approved_by' => (int)$u['id'], 'approved_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [(int)$_POST['approve_event']]);
                flash('success', 'Event approved.');
                redirect('admin/calendar');
            }
            if (isset($_POST['publish_event'])) {
                Database::update('calendar_events', [
                    'status' => 'published', 'published_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [(int)$_POST['publish_event']]);
                flash('success', 'Event published.');
                redirect('admin/calendar');
            }
            if (isset($_POST['cancel_event'])) {
                Database::update('calendar_events', ['status' => 'cancelled'], 'id = ?', [(int)$_POST['cancel_event']]);
                flash('success', 'Event cancelled.');
                redirect('admin/calendar');
            }
            if (($eid = (int)($_POST['delete_event'] ?? 0))) {
                Database::delete('calendar_events', 'id = ?', [$eid]);
                flash('success', 'Event deleted.');
                redirect('admin/calendar');
            }
        }

        Router::render('app/admin/calendar', [
            'title' => 'Academic Calendar', 'events' => $events, 'schools' => $schools,
            'years' => $years, 'eventTypeLabels' => $eventTypeLabels,
            'statusColors' => $statusColors, 'scopeIcons' => $scopeIcons,
            'region' => $region, 'schoolId' => $schoolId, 'yearId' => $yearId,
            'type' => $type, 'status' => $status, 'month' => $month, 'year' => $year,
        ]);
    }
}

/* =============== ADMIN: holidays =============== */
class Ctl_holidays {
    public function run(): void {
        $u = require_role('ministry');
        $holidays = Database::all("SELECT e.*, s.name AS school_name FROM calendar_events e LEFT JOIN schools s ON s.id = e.school_id WHERE e.event_type IN ('holiday','national_celebration','memorial_day','religious') ORDER BY e.gregorian_start DESC");
        $years = Database::all("SELECT id, name FROM academic_years ORDER BY start_date DESC");
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active' ORDER BY name");

        $statusColors = ['draft'=>'badge-muted','pending_approval'=>'badge-warning','approved'=>'badge-accent','published'=>'badge-success','cancelled'=>'badge-danger'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_holiday'])) {
                Database::insert('calendar_events', [
                    'academic_year_id' => (int)($_POST['academic_year_id'] ?: 0) ?: null,
                    'school_id' => (int)($_POST['school_id'] ?: 0) ?: null,
                    'title' => trim($_POST['title']),
                    'title_am' => trim($_POST['title_am'] ?? ''),
                    'title_om' => trim($_POST['title_om'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'event_type' => $_POST['event_type'] ?? 'holiday',
                    'category' => $_POST['category'] ?? 'national',
                    'priority' => 'high',
                    'ethiopian_date' => trim($_POST['ethiopian_date'] ?? ''),
                    'gregorian_start' => $_POST['gregorian_start'],
                    'gregorian_end' => $_POST['gregorian_end'] ?: null,
                    'all_day' => 1,
                    'scope_type' => $_POST['scope_type'] ?? 'national',
                    'issuing_authority' => $_POST['issuing_authority'] ?? 'federal',
                    'authority_name' => trim($_POST['authority_name'] ?? ''),
                    'directive_number' => trim($_POST['directive_number'] ?? ''),
                    'school_closed' => 1,
                    'teaching_suspended' => 1,
                    'is_academic_day' => 0,
                    'status' => $_POST['event_status'] ?? 'draft',
                    'created_by' => (int)$u['id'],
                ]);
                log_activity('holiday', "Created holiday " . trim($_POST['title']), (int)$u['id']);
                flash('success', 'Holiday created.');
                redirect('admin/holidays');
            }
            if (isset($_POST['approve_event'])) {
                Database::update('calendar_events', [
                    'status' => 'approved', 'approved_by' => (int)$u['id'], 'approved_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [(int)$_POST['approve_event']]);
                flash('success', 'Holiday approved.');
                redirect('admin/holidays');
            }
            if (isset($_POST['publish_event'])) {
                Database::update('calendar_events', [
                    'status' => 'published', 'published_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [(int)$_POST['publish_event']]);
                flash('success', 'Holiday published.');
                redirect('admin/holidays');
            }
        }

        Router::render('app/admin/holidays', [
            'title' => 'Holidays & Observances', 'holidays' => $holidays,
            'years' => $years, 'schools' => $schools, 'statusColors' => $statusColors,
        ]);
    }
}

/** Fallback random password generator (used when admin leaves password blank) */
