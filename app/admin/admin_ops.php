<?php
/**
 * Admin module part 2: settings, roles/permissions, logs, analytics,
 * reports, backups, announcements, library, transfers, system
 */

/* =============== ADMIN: settings =============== */
class Ctl_settings {
    public function run(): void {
        $u = require_role('ministry');
        $settings = [];
        foreach (Database::all("SELECT * FROM settings") as $s) $settings[$s['key']] = $s['value'];
        $defaults = [
            'site_name' => 'Edunex', 'tagline' => 'Learn without limits',
            'contact_email' => '', 'contact_phone' => '', 'address' => '',
            'support_phone' => '', 'currency' => 'ETB',
            'ai_enabled' => '1', 'ai_language' => 'am',
            'registration_enabled' => '1', 'transfer_enabled' => '1',
            'exam_grace' => '0', 'max_upload_mb' => '20',
        ];
        foreach ($defaults as $k => $v) if (!isset($settings[$k])) $settings[$k] = $v;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['save_settings']) || isset($_POST['setting_demo_mode'])) {
                // Handle checkbox (unchecked = not in POST)
                if (!isset($_POST['setting_demo_mode']) && !isset($_POST['save_settings'])) {
                    Database::run("INSERT INTO settings (`key`, `value`) VALUES ('demo_mode','0') ON DUPLICATE KEY UPDATE `value` = '0'");
                }
                foreach ($_POST as $k => $v) {
                    if (str_starts_with($k, 'setting_')) {
                        $key = substr($k, 8);
                        Database::run("INSERT INTO settings (`key`, `value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [$key, (string)$v]);
                    }
                }
                log_activity('settings', 'Updated system settings', (int)$u['id']);
                // Reset cached demo_mode
                if (isset($_POST['setting_demo_mode']) || !isset($_POST['save_settings'])) {
                    // Clear is_demo_mode() static cache by forcing reload
                }
                flash('success', isset($_POST['save_settings']) ? 'Settings saved.' : 'Demo mode updated.');
                redirect('admin/settings');
            }
            if (isset($_POST['clear_cache'])) {
                flash('success', 'Cache cleared (no persistent cache in use).');
                redirect('admin/settings');
            }
        }
        Router::render('app/admin/settings', ['title' => 'Settings', 'settings' => $settings]);
    }
}

/* =============== ADMIN: roles & permissions =============== */
class Ctl_roles {
    public function run(): void {
        $u = require_role('ministry');
        $perms = [];
        foreach (Database::all("SELECT * FROM role_permissions ORDER BY permission") as $r) {
            $perms[$r['role']][] = $r['permission'];
        }
        $all = $this->catalog();
        if (isset($_POST['seed_defaults'])) {
            csrf_verify();
            $this->seedDefaults();
            log_activity('roles', 'Restored default role permissions', (int)$u['id']);
            flash('success', 'Default permissions restored for all roles.');
            redirect('admin/roles');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            Database::run("DELETE FROM role_permissions");
            foreach ($_POST['perm'] ?? [] as $role => $list) {
                foreach ($list as $p) Database::insert('role_permissions', ['role' => $role, 'permission' => $p]);
            }
            log_activity('roles', 'Updated role permissions', (int)$u['id']);
            flash('success', 'Permissions saved.');
            redirect('admin/roles');
        }
        $roles = ['regional', 'principal', 'teacher', 'student', 'parent'];
        Router::render('app/admin/roles', ['title' => 'Roles & Permissions', 'perms' => $perms, 'all' => $all, 'roles' => $roles]);
    }

    /** Full permission catalog grouped by module. */
    private function catalog(): array {
        return [
            'Dashboard & Core' => ['dashboard', 'profile.view', 'notifications.view', 'search.global'],
            'Courses' => ['courses.manage', 'courses.view', 'courses.enroll', 'courses.create', 'lessons.manage', 'announcements.manage'],
            'Exams' => ['exams.manage', 'exams.create', 'exams.take', 'exams.grade', 'exams.view'],
            'Assignments' => ['assignments.manage', 'assignments.create', 'assignments.submit', 'assignments.grade'],
            'Attendance' => ['attendance.record', 'attendance.view', 'attendance.manage', 'attendance.export'],
            'Grades' => ['grades.view', 'grades.manage', 'grades.export'],
            'Library' => ['library.manage', 'library.view', 'library.upload', 'library.borrow'],
            'Community' => ['forum.post', 'forum.moderate', 'messages.send', 'messages.view', 'announcements.manage', 'comments.view'],
            'Gamification' => ['gamification.view', 'badges.manage', 'goals.create', 'leaderboard.view'],
            'Files' => ['files.view', 'files.upload', 'files.manage'],
            'Calendar' => ['calendar.view', 'calendar.manage'],
            'AI' => ['ai.tutor', 'ai.assistant', 'ai.flashcards'],
            'Transfers' => ['transfers.view', 'transfers.apply', 'transfers.approve', 'transfers.manage'],
            'Management' => ['users.view', 'users.manage', 'users.create', 'reports.view', 'reports.export', 'analytics.view', 'settings.manage', 'backups.manage', 'logs.view', 'ledger.verify'],
        ];
    }

    /** Sensible default matrix applied on 'Restore defaults'. */
    private function seedDefaults(): void {
        Database::run("DELETE FROM role_permissions");
        $defaults = [
            'regional' => ['dashboard', 'profile.view', 'notifications.view', 'courses.manage', 'courses.view', 'courses.create', 'lessons.manage', 'exams.manage', 'exams.create', 'exams.take', 'exams.grade', 'exams.view', 'assignments.manage', 'assignments.create', 'assignments.grade', 'attendance.record', 'attendance.view', 'attendance.manage', 'attendance.export', 'grades.manage', 'grades.view', 'grades.export', 'library.manage', 'library.view', 'library.upload', 'forum.post', 'forum.moderate', 'messages.send', 'messages.view', 'announcements.manage', 'gamification.view', 'badges.manage', 'goals.award', 'leaderboard.view', 'files.view', 'files.upload', 'files.manage', 'calendar.view', 'calendar.create', 'ai.tutor', 'ai.assistant', 'ai.flashcards', 'transfers.manage', 'transfers.approve', 'users.manage', 'users.view', 'users.create', 'reports.view', 'reports.export', 'analytics.view', 'settings.manage', 'backups.manage', 'logs.view', 'ledger.verify'],
            'principal' => ['dashboard', 'courses.view', 'exams.view', 'attendance.view', 'attendance.manage', 'grades.view', 'library.view', 'messages.view', 'messages.send', 'announcements.manage', 'reports.view', 'reports.export', 'analytics.view', 'transfers.view', 'transfers.approve', 'users.view', 'gamification.view', 'leaderboard.view', 'calendar.view', 'ai.tutor', 'ai.assistant', 'ai.flashcards'],
            'teacher' => ['dashboard', 'courses.view', 'courses.manage', 'lessons.manage', 'exams.manage', 'exams.create', 'exams.take', 'exams.grade', 'exams.view', 'assignments.manage', 'assignments.create', 'assignments.grade', 'attendance.record', 'attendance.view', 'grades.view', 'grades.manage', 'library.view', 'library.upload', 'forum.post', 'forum.reply', 'messages.send', 'messages.view', 'announcements.create', 'gamification.view', 'goals.award', 'leaderboard.view', 'files.view', 'files.upload', 'calendar.view', 'calendar.create', 'ai.tutor', 'ai.assistant', 'ai.flashcards', 'reports.view', 'analytics.view'],
            'student' => ['dashboard', 'courses.view', 'courses.enroll', 'exams.take', 'exams.view', 'assignments.submit', 'attendance.view', 'grades.view', 'library.view', 'library.borrow', 'forum.post', 'forum.reply', 'messages.send', 'messages.view', 'gamification.view', 'leaderboard.view', 'goals.view', 'files.view', 'files.upload', 'calendar.view', 'ai.tutor', 'ai.assistant', 'ai.flashcards', 'transfers.view', 'transfers.apply'],
            'parent' => ['dashboard', 'courses.view', 'grades.view', 'attendance.view', 'assignments.view', 'messages.send', 'messages.view', 'reports.view', 'calendar.view'],
        ];
        foreach ($defaults as $role => $list) {
            foreach ($list as $p) Database::insert('role_permissions', ['role' => $role, 'permission' => $p]);
        }
    }
}

/* =============== ADMIN: permissions (alias page) =============== */
class Ctl_permissions {
    public function run(): void {
        require_role('ministry');
        redirect('admin/roles');
    }
}

/* =============== ADMIN: logs =============== */
class Ctl_logs {
    public function run(): void {
        $u = require_role('ministry', 'regional');
        $action = trim($_GET['action'] ?? '');
        $q = trim($_GET['q'] ?? '');
        $days = (int)($_GET['days'] ?? 0);
        $sort = $_GET['sort'] ?? 'created_at';
        $dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $sortMap = ['created_at' => 'l.created_at', 'user' => 'us.last_name', 'school' => 'sc.name', 'action' => 'l.action'];
        if (!isset($sortMap[$sort])) $sort = 'created_at';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', l.id DESC';
        $export = $_GET['export'] ?? '';
        $sql = "SELECT l.*, CONCAT(us.first_name, ' ', us.last_name) AS user_name, us.email, sc.name AS school_name
                 FROM activity_logs l
                 LEFT JOIN users us ON us.id = l.user_id
                 LEFT JOIN schools sc ON sc.id = us.school_id
                 WHERE 1=1";
        $args = [];
        if ($action) { $sql .= " AND l.action = ?"; $args[] = $action; }
        if ($q !== '') { $sql .= " AND (l.detail LIKE ? OR l.user_agent LIKE ?)"; $args[] = "%$q%"; $args[] = "%$q%"; }
        if ($days > 0) { $sql .= " AND l.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)"; $args[] = $days; }
        $sql .= " ORDER BY $orderBy LIMIT 500";
        $logs = Database::all($sql, $args);
        $actions = Database::all("SELECT action, COUNT(*) AS n FROM activity_logs GROUP BY action ORDER BY n DESC LIMIT 25");

        if ($export === 'pdf' || $export === 'md') {
            $stamp = date('F j, Y \a\t g:i A');
            $filters = [];
            if ($action) $filters[] = "Action: $action";
            if ($q) $filters[] = "Search: \"$q\"";
            if ($days) $filters[] = "Last $days days";
            $filterStr = $filters ? implode(' · ', $filters) : 'No filters';

            if ($export === 'md') {
                header('Content-Type: text/markdown');
                header('Content-Disposition: attachment; filename="edunex_logs_' . date('Ymd_His') . '.md"');
                echo "# Edunex Activity Logs\n\n";
                echo "**Generated:** $stamp\n**Filters:** $filterStr\n**Records:** " . count($logs) . "\n\n";
                echo "| # | Time | User | School | Action | Detail |\n";
                echo "|---|------|------|--------|--------|--------|\n";
                $rn = 0;
                foreach ($logs as $l) {
                    $rn++;
                    echo "| $rn | " . e(date('M j, H:i:s', strtotime($l['created_at']))) . " | " . e($l['user_name'] ?? '—') . " | " . e($l['school_name'] ?? '—') . " | " . e($l['action']) . " | " . e($l['detail']) . " |\n";
                }
                echo "\n---\n**Henok Akriso** · henokakriso.com\n\nAll system is opensourced under [ARWE-PL License](https://github.com/henokakriso/Edunex)\n";
                exit;
            }

            // PDF viewer
            $title = 'Edunex Activity Logs';
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
            echo '.table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}';
            echo 'table{width:100%;border-collapse:collapse;table-layout:auto;min-width:600px}';
            echo 'th,td{padding:8px 10px;text-align:left;border-bottom:1px solid #eee;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px}';
            echo 'th{background:#f8f9fa;font-weight:600;color:#444;position:sticky;top:0;z-index:2;white-space:nowrap}';
            echo '.row-num{color:#999;width:36px;text-align:center;max-width:36px}';
            echo '.badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;background:#e8e8e8}';
            echo '.footer{padding:16px 32px;border-top:2px solid #eee;text-align:center;color:#888;font-size:11px;line-height:1.6}';
            echo '.footer a{color:#4361ee;text-decoration:none}';
            echo '@media print{.viewer-bar{display:none!important}.report{box-shadow:none;margin:0;border-radius:0}body{background:#fff}.table-wrap{overflow:visible}table{min-width:0}th,td{white-space:normal;word-break:break-word}}';
            echo '</style></head><body>';
            echo '<div class="viewer-bar"><h1>' . e($title) . '</h1>';
            echo '<div class="btns"><button class="btn-secondary" onclick="history.back()">← Back</button>';
            echo '<a href="javascript:window.print()">🖨 Print</a>';
            echo '<a href="javascript:downloadPDF()">⬇ Download PDF</a>';
            echo '<a href="' . e(url('admin/logs&' . http_build_query(array_filter(['action'=>$action,'q'=>$q,'days'=>$days,'export'=>'md'])))) . '">📄 Markdown</a>';
            echo '</div></div>';
            echo '<div class="report"><div class="report-header"><h2>' . e($title) . '</h2>';
            echo '<p class="meta">Generated: ' . e($stamp) . ' · Filters: ' . e($filterStr) . ' · ' . count($logs) . ' records</p></div>';
            echo '<div class="table-wrap"><table><thead><tr><th class="row-num">#</th><th>Time</th><th>User</th><th>School</th><th>Action</th><th>Detail</th></tr></thead><tbody>';
            $rn = 0;
            foreach ($logs as $l) {
                $rn++;
                echo '<tr><td class="row-num">' . $rn . '</td>';
                echo '<td>' . e(date('M j, H:i:s', strtotime($l['created_at']))) . '</td>';
                echo '<td><b>' . e($l['user_name'] ?? '—') . '</b><br><small style="color:#888">' . e($l['email'] ?? '') . '</small></td>';
                echo '<td>' . e($l['school_name'] ?? '—') . '</td>';
                echo '<td><span class="badge">' . e($l['action']) . '</span></td>';
                echo '<td>' . e($l['detail']) . '</td></tr>';
            }
            echo '</tbody></table></div>';
            echo '<div class="footer"><p><b>Henok Akriso</b> · henokakriso.com</p>';
            echo '<p>All system is opensourced under <a href="https://github.com/henokakriso/Edunex" target="_blank">ARWE-PL License</a></p></div></div>';
            echo '<script>function downloadPDF(){var opt={margin:[10,10],filename:"edunex_logs_' . date('Ymd_His') . '.pdf",html2canvas:{scale:2},jsPDF:{unit:"mm",format:"a4",orientation:"landscape"}};if(typeof html2pdf!=="undefined"){html2pdf().set(opt).from(document.querySelector(".report")).save();}else{var s=document.createElement("script");s.src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js";s.onload=function(){html2pdf().set(opt).from(document.querySelector(".report")).save();};document.head.appendChild(s);}}</script>';
            echo '</body></html>';
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['rotate'])) {
                $keep = (int)($_POST['keep_days'] ?? 90);
                $del = Database::run("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)", [$keep]);
                log_activity('log', "Rotated activity logs (kept $keep days)", (int)$u['id']);
                flash('success', 'Logs rotated — deleted records older than ' . $keep . ' days.');
                redirect('admin/logs');
            }
        }
        Router::render('app/admin/logs', ['title' => 'Activity Logs', 'logs' => $logs, 'actions' => $actions, 'action' => $action, 'q' => $q, 'days' => $days, 'sort' => $sort, 'dir' => $dir]);
    }
}

/* =============== ADMIN: analytics =============== */
class Ctl_analytics {
    public function run(): void {
        $u = require_role('ministry');
        $range = $_GET['range'] ?? '30';
        $days = (int)$range;
        $loginSeries = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $loginSeries[] = [
                'date' => $d,
                'logins' => (int)Database::scalar("SELECT COUNT(*) FROM login_history WHERE status = 'success' AND DATE(created_at) = ?", [$d], 0),
                'signups' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?", [$d], 0),
            ];
        }
        $byRole = Database::all("SELECT role, COUNT(*) AS n FROM users GROUP BY role");
        $topCourses = Database::all(
            "SELECT c.title, COUNT(ce.id) AS students, c.status FROM courses c
             LEFT JOIN course_enrollments ce ON ce.course_id = c.id
             GROUP BY c.id ORDER BY students DESC LIMIT 10");
        $activityByDay = Database::all("SELECT DATE(created_at) AS d, COUNT(*) AS n FROM activity_logs GROUP BY DATE(created_at) ORDER BY d DESC LIMIT 7");
        $attSeries = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $attSeries[] = ['date' => $d, 'present' => (int)Database::scalar("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'present'", [$d], 0)];
        }
        $examStats = Database::all(
            "SELECT c.title, COUNT(DISTINCT at2.id) AS attempts,
                    ROUND(AVG(at2.score / NULLIF(at2.total_points, 0)) * 100, 1) AS avg_pct
             FROM exam_attempts at2 JOIN exams e ON e.id = at2.exam_id JOIN courses c ON c.id = e.course_id
             WHERE at2.status IN ('submitted','graded') GROUP BY c.id ORDER BY attempts DESC LIMIT 10");
        $bySchool = Database::all(
            "SELECT s.name, COUNT(us.id) AS users FROM schools s LEFT JOIN users us ON us.school_id = s.id GROUP BY s.id ORDER BY users DESC LIMIT 8");
        Router::render('app/admin/analytics', [
            'title' => 'Analytics', 'days' => $days, 'loginSeries' => $loginSeries,
            'byRole' => $byRole, 'topCourses' => $topCourses, 'activityByDay' => $activityByDay,
            'attSeries' => $attSeries, 'examStats' => $examStats, 'bySchool' => $bySchool,
        ]);
    }
}

/* =============== ADMIN: reports =============== */
class Ctl_reports {
    public function run(): void {
        $u = require_role('ministry');
        if (isset($_GET['action']) && $_GET['action'] === 'view') { $this->view(); return; }
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");
        $regions = Database::all("SELECT DISTINCT region FROM schools WHERE region IS NOT NULL AND region != '' ORDER BY region");
        $zones = Database::all("SELECT DISTINCT z.name AS zone_name, r.name AS region_name FROM zones z JOIN regions r ON r.id = z.region_id ORDER BY r.name, z.name");
        $semesters = Database::all("SELECT DISTINCT name, sort_order FROM semesters ORDER BY sort_order");
        $reportType = $_POST['report_type'] ?? '';
        $reportTypes = $_POST['report_types'] ?? [];
        $reports = Database::all(
            "SELECT r.*, CONCAT(us.first_name, ' ', us.last_name) AS user_name
             FROM reports r JOIN users us ON us.id = r.user_id
             ORDER BY r.created_at DESC LIMIT 50");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['generate']) && !empty($reportTypes)) {
                $filters = [
                    'region' => trim($_POST['region'] ?? ''),
                    'zone' => trim($_POST['zone'] ?? ''),
                    'school_id' => (int)($_POST['school_id'] ?? 0),
                    'education_level' => trim($_POST['education_level'] ?? ''),
                    'year' => trim($_POST['year'] ?? ''),
                    'semester' => trim($_POST['semester'] ?? ''),
                    'date_from' => $_POST['date_from'] ?? '',
                    'date_to' => $_POST['date_to'] ?? '',
                ];
                $format = 'pdf';
                $generated = 0;
                foreach ($reportTypes as $reportType) {
                    $reportType = trim($reportType);
                    if (!$reportType) continue;
                    [$headers, $rows] = $this->buildReport($reportType, $filters);
                    $title = trim($_POST['title'] ?: '');
                    $typeNames = [
                        'education_performance' => 'Education Performance', 'enrollment_stats' => 'Enrollment Statistics',
                        'academic_performance' => 'Academic Performance', 'attendance_participation' => 'Attendance & Participation',
                        'national_exam' => 'National Exam Performance', 'school_performance' => 'School Performance',
                        'teacher_workforce' => 'Teacher Workforce Statistics', 'course_curriculum' => 'Course & Curriculum Analytics',
                        'learning_activity' => 'Learning Activity', 'student_progress' => 'Student Progress',
                        'regional_education' => 'Regional Education', 'institution_stats' => 'Institution Statistics',
                        'digital_platform' => 'Digital Platform Usage', 'compliance' => 'Compliance',
                        'annual_education' => 'Annual Education', 'system_activity' => 'System Activity',
                    ];
                    $label = $typeNames[$reportType] ?? ucfirst(str_replace('_', ' ', $reportType));
                    $reportTitle = $title ?: ($label . ' Report — ' . date('M j, Y'));
                    if (count($reportTypes) > 1) $reportTitle = $label . ' — ' . ($title ?: date('M j, Y'));
                    $file = $this->renderReport($reportTitle, $headers, $rows, $reportType, $format);
                    Database::insert('reports', [
                        'school_id' => $filters['school_id'] ?: Database::scalar("SELECT id FROM schools ORDER BY id LIMIT 1", [], 1),
                        'user_id' => $u['id'], 'type' => $reportType, 'title' => $reportTitle,
                        'format' => $format, 'file_path' => $file, 'filters' => json_encode($filters),
                        'data_json' => json_encode(['headers' => $headers, 'rows' => array_values($rows)]),
                    ]);
                    log_activity('report', "Generated: $reportTitle", (int)$u['id']);
                    $generated++;
                }
                flash('success', "$generated report(s) generated successfully.");
                redirect('admin/reports');
            }
        }
        Router::render('app/admin/reports', [
            'title' => 'Reports', 'reports' => $reports, 'schools' => $schools,
            'regions' => $regions, 'zones' => $zones, 'semesters' => $semesters,
            'reportType' => $reportType,
        ]);
    }

    private function buildReport(string $type, array $f): array {
        $schoolWhere = $f['school_id'] > 0 ? " AND s.id = {$f['school_id']}" : '';
        $regionWhere = $f['region'] !== '' ? " AND s.region = '" . addslashes($f['region']) . "'" : '';
        $zoneWhere = $f['zone'] !== '' ? " AND z.name = '" . addslashes($f['zone']) . "'" : '';
        $levelWhere = ($f['education_level'] ?? '') !== '' ? " AND s.education_level = '" . addslashes($f['education_level']) . "'" : '';
        $dateFrom = $f['date_from'] !== '' ? " AND u.created_at >= '{$f['date_from']}'" : '';
        $dateTo = $f['date_to'] !== '' ? " AND u.created_at <= '{$f['date_to']} 23:59:59'" : '';

        return match ($type) {
            'enrollment_stats' => [
                ['Region', 'Zone', 'Institution', 'Total Students', 'Male', 'Female', 'Active', 'Inactive', 'New Enrollment'],
                Database::all("SELECT s.region AS Region, COALESCE(z.name,'—') AS Zone, s.name AS Institution,
                    COUNT(*) AS `Total Students`, SUM(u.gender='m') AS Male, SUM(u.gender='f') AS Female,
                    SUM(u.enrollment_status='active') AS Active, SUM(u.enrollment_status='inactive') AS Inactive,
                    SUM(CASE WHEN u.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) AS `New Enrollment`
                    FROM users u JOIN schools s ON s.id = u.school_id LEFT JOIN zones z ON z.id = s.zone_id
                    WHERE u.role = 'student' $schoolWhere $regionWhere $zoneWhere $levelWhere $dateFrom $dateTo
                    GROUP BY s.id ORDER BY s.region, s.name"),
            ],
            'teacher_workforce' => [
                ['Region', 'Institution', 'Teachers', 'Active', 'On Leave', 'Avg Experience', 'Student/Teacher Ratio'],
                Database::all("SELECT s.region AS Region, s.name AS Institution,
                    COUNT(*) AS Teachers, SUM(u.status='active') AS Active,
                    SUM(u.status='suspended') AS `On Leave`,
                    ROUND(AVG(u.experience_years),1) AS `Avg Experience`,
                    ROUND(COUNT(*) / GREATEST((SELECT COUNT(*) FROM users u2 WHERE u2.role='student' AND u2.school_id=s.id),1),1) AS `Student/Teacher Ratio`
                    FROM users u JOIN schools s ON s.id = u.school_id LEFT JOIN zones z ON z.id = s.zone_id
                    WHERE u.role IN ('teacher','lecturer') $schoolWhere $regionWhere $zoneWhere $levelWhere $dateFrom $dateTo
                    GROUP BY s.id ORDER BY s.region, s.name"),
            ],
            'academic_performance' => [
                ['Region', 'Institution', 'Course', 'Enrolled', 'Avg Progress', 'Completion Rate', 'Completed', 'In Progress'],
                Database::all("SELECT s.region AS Region, s.name AS Institution, c.title AS Course,
                    COUNT(ce.id) AS Enrolled, ROUND(AVG(ce.progress),1) AS `Avg Progress`,
                    ROUND(SUM(CASE WHEN ce.completed = 1 THEN 1 ELSE 0 END)/GREATEST(COUNT(ce.id),1)*100,1) AS `Completion Rate`,
                    SUM(ce.completed = 1) AS Completed, SUM(ce.completed = 0) AS `In Progress`
                    FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id JOIN schools s ON s.id = c.school_id LEFT JOIN zones z ON z.id = s.zone_id
                    WHERE 1=1 $schoolWhere $regionWhere $zoneWhere $levelWhere
                    GROUP BY c.id ORDER BY s.region, s.name"),
            ],
            'school_performance' => [
                ['Region', 'Zone', 'Institution', 'Type', 'Students', 'Teachers', 'Courses', 'Avg Progress'],
                Database::all("SELECT s.region AS Region, COALESCE(z.name,'—') AS Zone, s.name AS Institution, s.type AS Type,
                    (SELECT COUNT(*) FROM users u WHERE u.role='student' AND u.school_id=s.id) AS Students,
                    (SELECT COUNT(*) FROM users u WHERE u.role IN ('teacher','lecturer') AND u.school_id=s.id) AS Teachers,
                    (SELECT COUNT(*) FROM courses c WHERE c.school_id=s.id) AS Courses,
                    (SELECT ROUND(AVG(ce.progress),1) FROM course_enrollments ce JOIN courses c ON c.id=ce.course_id WHERE c.school_id=s.id) AS `Avg Progress`
                    FROM schools s LEFT JOIN zones z ON z.id = s.zone_id
                    WHERE s.status = 'active' $schoolWhere $regionWhere $zoneWhere $levelWhere
                    ORDER BY s.region, s.name"),
            ],
            'course_curriculum' => [
                ['Region', 'Institution', 'Course', 'Status', 'Enrolled', 'Completed', 'Completion Rate'],
                Database::all("SELECT s.region AS Region, s.name AS Institution, c.title AS Course, c.status AS Status,
                    COUNT(ce.id) AS Enrolled,
                    SUM(CASE WHEN ce.completed = 1 THEN 1 ELSE 0 END) AS Completed,
                    ROUND(SUM(CASE WHEN ce.completed = 1 THEN 1 ELSE 0 END)/GREATEST(COUNT(ce.id),1)*100,1) AS `Completion Rate`
                    FROM courses c JOIN schools s ON s.id = c.school_id LEFT JOIN course_enrollments ce ON ce.course_id = c.id LEFT JOIN zones z ON z.id = s.zone_id
                    WHERE 1=1 $schoolWhere $regionWhere $zoneWhere $levelWhere
                    GROUP BY c.id ORDER BY s.region, s.name"),
            ],
            'student_progress' => [
                ['Region', 'Institution', 'Total Students', 'Active', 'Completed', 'Transferred', 'Withdrawn', 'Retention Rate'],
                Database::all("SELECT s.region AS Region, s.name AS Institution,
                    COUNT(*) AS `Total Students`, SUM(enrollment_status='active') AS Active,
                    SUM(enrollment_status='active') AS Completed,
                    0 AS Transferred, 0 AS Withdrawn,
                    ROUND(SUM(enrollment_status='active')/GREATEST(COUNT(*),1)*100,1) AS `Retention Rate`
                    FROM users u JOIN schools s ON s.id = u.school_id LEFT JOIN zones z ON z.id = s.zone_id
                    WHERE u.role = 'student' $schoolWhere $regionWhere $zoneWhere $levelWhere $dateFrom $dateTo
                    GROUP BY s.id ORDER BY s.region, s.name"),
            ],
            'regional_education' => [
                ['Region', 'Schools', 'Students', 'Teachers', 'Courses', 'Avg Progress'],
                Database::all("SELECT s.region AS Region,
                    COUNT(DISTINCT s.id) AS Schools,
                    SUM(CASE WHEN u.role='student' THEN 1 ELSE 0 END) AS Students,
                    SUM(CASE WHEN u.role IN ('teacher','lecturer') THEN 1 ELSE 0 END) AS Teachers,
                    (SELECT COUNT(*) FROM courses c WHERE c.school_id IN (SELECT s2.id FROM schools s2 WHERE s2.region=s.region)) AS Courses,
                    (SELECT ROUND(AVG(ce.progress),1) FROM course_enrollments ce JOIN courses c ON c.id=ce.course_id JOIN schools s3 ON s3.id=c.school_id WHERE s3.region=s.region) AS `Avg Progress`
                    FROM schools s LEFT JOIN users u ON u.school_id = s.id
                    WHERE s.status = 'active' AND s.region IS NOT NULL
                    GROUP BY s.region ORDER BY s.region"),
            ],
            'institution_stats' => [
                ['Metric', 'Value'],
                [
                    ['Total Institutions', Database::scalar("SELECT COUNT(*) FROM schools WHERE status='active'")],
                    ['Universities', Database::scalar("SELECT COUNT(*) FROM schools WHERE type='university' AND status='active'")],
                    ['Schools (K-12)', Database::scalar("SELECT COUNT(*) FROM schools WHERE type='school' AND status='active'")],
                    ['Total Students', Database::scalar("SELECT COUNT(*) FROM users WHERE role='student'")],
                    ['Total Teachers', Database::scalar("SELECT COUNT(*) FROM users WHERE role IN ('teacher','lecturer')")],
                    ['Total Courses', Database::scalar("SELECT COUNT(*) FROM courses")],
                    ['Active Regions', Database::scalar("SELECT COUNT(DISTINCT region) FROM schools WHERE region IS NOT NULL")],
                ],
            ],
            'digital_platform' => [
                ['Metric', 'Value'],
                [
                    ['Total Users', Database::scalar("SELECT COUNT(*) FROM users WHERE role != 'guest'")],
                    ['Active Users (30d)', Database::scalar("SELECT COUNT(*) FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)")],
                    ['Total Logins (30d)', Database::scalar("SELECT COUNT(*) FROM activity_logs WHERE action='auth.login' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")],
                    ['Messages Sent', Database::scalar("SELECT COUNT(*) FROM messages")],
                    ['Assignments Created', Database::scalar("SELECT COUNT(*) FROM assignments")],
                    ['Exams Taken', Database::scalar("SELECT COUNT(*) FROM exam_attempts")],
                    ['Library Downloads', Database::scalar("SELECT COALESCE(SUM(downloads),0) FROM library_items")],
                ],
            ],
            'system_activity' => [
                ['Action', 'Detail', 'User', 'Date'],
                Database::all("SELECT al.action AS Action, al.detail AS Detail,
                    CONCAT(u.first_name,' ',u.last_name) AS User, al.created_at AS Date
                    FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id
                    ORDER BY al.created_at DESC LIMIT 500"),
            ],
            default => [
                ['Region', 'Institution', 'Students', 'Teachers', 'Status'],
                Database::all("SELECT s.region AS Region, s.name AS Institution,
                    (SELECT COUNT(*) FROM users u WHERE u.role='student' AND u.school_id=s.id) AS Students,
                    (SELECT COUNT(*) FROM users u WHERE u.role IN ('teacher','lecturer') AND u.school_id=s.id) AS Teachers,
                    s.status AS Status
                    FROM schools s WHERE s.status='active' $schoolWhere $regionWhere $levelWhere ORDER BY s.region, s.name"),
            ],
        };
    }

    private function renderReport(string $title, array $headers, array $rows, string $type, string $format): string {
        $dir = STORAGE_PATH . '/reports';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $file = 'reports/' . $type . '_' . date('Ymd_His') . '.csv';
        $absPath = STORAGE_PATH . '/' . $file;
        $fp = fopen($absPath, 'w');
        // Header info block
        fputcsv($fp, ['EDUNEX LMS — ' . $title]);
        fputcsv($fp, ['Document ID', 'EDU-' . date('Y') . '-' . str_pad(Database::scalar("SELECT id FROM reports ORDER BY id DESC LIMIT 1", [], 0) + 1, 6, '0', STR_PAD_LEFT)]);
        fputcsv($fp, ['Generated', date('M j, Y g:i A')]);
        fputcsv($fp, ['Report Type', ucfirst(str_replace('_', ' ', $type))]);
        fputcsv($fp, ['Format', strtoupper($format)]);
        fputcsv($fp, ['Website', 'www.henockakriso.com']);
        fputcsv($fp, ['License', 'ARWE-PL Licensed ' . date('Y')]);
        fputcsv($fp, []); // blank line separator
        // Column headers
        fputcsv($fp, $headers);
        // Data rows
        foreach ($rows as $r) {
            if (is_array($r) && count($r) === count($headers)) fputcsv($fp, array_values($r));
        }
        fclose($fp);
        return $file;
    }

    public function view(): void {
        $u = require_role('ministry');
        $id = (int)($_GET['id'] ?? 0);
        $report = Database::one("SELECT r.*, CONCAT(us.first_name,' ',us.last_name) AS user_name FROM reports r JOIN users us ON us.id=r.user_id WHERE r.id=?", [$id]);
        if (!$report) { flash('danger', 'Report not found'); redirect('admin/reports'); }
        $data = json_decode($report['data_json'] ?? '{}', true);
        $headers = $data['headers'] ?? [];
        $rows = $data['rows'] ?? [];
        include __DIR__ . '/../views/app/admin/report_view.php';
    }
}

/* =============== ADMIN: backups =============== */
class Ctl_backups {
    public function run(): void {
        $u = require_role('ministry', 'regional');
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
                    flash('success', 'Backup created: ' . basename($file) . ' (' . round(filesize($file)/1024, 1) . ' KB)');
                } else {
                    @unlink($file);
                    $err = implode("\n", $out);
                    flash('danger', 'Backup failed (exit ' . $code . '): ' . ($err ?: 'Check mysqldump is installed and DB credentials are correct.'));
                }
                redirect('admin/backups');
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
                    } else {
                        flash('danger', 'Cannot rename: source missing or target exists.');
                    }
                } else {
                    flash('danger', 'Invalid backup name. Use only letters, numbers, hyphens and .sql extension.');
                }
                redirect('admin/backups');
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
                flash('danger', 'Backup file not found.');
                redirect('admin/backups');
            }
            if (($del = $_POST['delete_backup'] ?? '')) {
                $delFile = basename($del);
                if (preg_match('/^edunex_.*\.sql$/', $delFile) && is_file($dir . '/' . $delFile)) {
                    @unlink($dir . '/' . $delFile);
                    flash('success', 'Backup deleted.');
                } else {
                    flash('danger', 'Invalid backup file.');
                }
                redirect('admin/backups');
            }
        }
        Router::render('app/admin/backups', ['title' => 'Backups', 'backups' => $backups]);
    }
}

/* =============== ADMIN: announcements =============== */
class Ctl_announcements {
    public function run(): void {
        $u = require_role('ministry');
        $df = demo_filter('a');
        $anns = Database::all(
            "SELECT a.*, CONCAT(us.first_name, ' ', us.last_name) AS author_name, s.name AS school_name, c.title AS course_title
             FROM announcements a
             LEFT JOIN schools s ON s.id = a.school_id JOIN users us ON us.id = a.author_id
             LEFT JOIN courses c ON c.id = a.course_id
             WHERE 1=1 $df
             ORDER BY a.pinned DESC, a.created_at DESC LIMIT 100");
        $courses = Database::all("SELECT id, title FROM courses WHERE status = 'published'");
        $regions = Database::all("SELECT DISTINCT region FROM schools WHERE region IS NOT NULL AND region != '' ORDER BY region");
        $zones = Database::all("SELECT DISTINCT z.name AS zone_name, r.name AS region_name FROM zones z JOIN regions r ON r.id = z.region_id ORDER BY r.name, z.name");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_ann'])) {
                $targetRegion = trim($_POST['target_region'] ?? '');
                $targetZone = trim($_POST['target_zone'] ?? '');
                $hasScope = $targetRegion !== '' || $targetZone !== '';
                $data = [
                    'school_id' => 1, 'author_id' => $u['id'],
                    'title' => trim($_POST['title']), 'content' => trim($_POST['content']),
                    'audience' => $_POST['audience'] ?? 'all', 'pinned' => !empty($_POST['pinned']) ? 1 : 0,
                    'course_id' => ((int)($_POST['course_id'] ?? 0)) ?: null,
                    'target_region' => $targetRegion ?: null,
                    'target_zone' => $targetZone ?: null,
                    'approval_status' => $hasScope ? 'pending' : 'none',
                ];
                if ($data['title'] === '' || $data['content'] === '') { flash('danger', 'Title and content required.'); redirect('admin/announcements'); }
                $aid = Database::insert('announcements', $data);
                $roleMap = ['all' => null, 'students' => 'student', 'teachers' => 'teacher', 'parents' => 'parent', 'course' => null];
                $role = $roleMap[$data['audience']] ?? null;
                if ($hasScope) {
                    if ($targetZone) {
                        $targets = Database::all("SELECT u.id FROM users u JOIN schools s ON s.id = u.school_id JOIN zones z ON z.id = s.zone_id WHERE z.name = ? AND u.role != 'guest'" . ($role ? " AND u.role = '$role'" : ''), [$targetZone]);
                    } elseif ($targetRegion) {
                        $targets = Database::all("SELECT u.id FROM users u JOIN schools s ON s.id = u.school_id WHERE s.region = ? AND u.role != 'guest'" . ($role ? " AND u.role = '$role'" : ''), [$targetRegion]);
                    } else {
                        $targets = [];
                    }
                } elseif ($data['audience'] === 'course' && $data['course_id']) {
                    $targets = Database::all("SELECT user_id AS id FROM course_enrollments WHERE course_id = ?", [$data['course_id']]);
                } else {
                    $targets = $role ? Database::all("SELECT id FROM users WHERE role = ?", [$role]) : Database::all("SELECT id FROM users WHERE role != 'guest'");
                }
                if (!$hasScope) {
                    foreach ($targets as $t) {
                        notify((int)$t['id'], 'announcement', $data['title'], mb_strimwidth($data['content'], 0, 120, '…'), 'communication/announcement&id=' . $aid);
                    }
                }
                log_activity('announcement', 'Posted: ' . $data['title'] . ($hasScope ? " (pending regional approval for " . ($targetZone ?: $targetRegion) . ")" : ''), (int)$u['id']);
                flash('success', $hasScope ? 'Announcement sent for regional approval — will be delivered once approved.' : 'Announcement posted to ' . count($targets) . ' users.');
                redirect('admin/announcements');
            }
            if (($del = (int)($_POST['delete_ann'] ?? 0))) {
                Database::delete('announcements', 'id = ?', [$del]);
                flash('success', 'Announcement deleted.');
                redirect('admin/announcements');
            }
        }
        Router::render('app/admin/announcements', ['title' => 'Announcements', 'anns' => $anns, 'courses' => $courses, 'regions' => $regions, 'zones' => $zones]);
    }
}

/* =============== ADMIN: library =============== */
class Ctl_library {
    public function run(): void {
        $u = require_role('ministry');
        $sort = $_GET['sort'] ?? 'created_at';
        $dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $sortMap = ['title' => 'i.title', 'type' => 'i.type', 'school' => 's.name', 'downloads' => 'i.downloads', 'status' => 'i.status', 'created_at' => 'i.created_at'];
        if (!isset($sortMap[$sort])) $sort = 'created_at';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', i.id DESC';
        $df = demo_filter('i');
        $items = Database::all("SELECT i.*, s.name AS school_name FROM library_items i JOIN schools s ON s.id = i.school_id WHERE 1=1 $df ORDER BY $orderBy LIMIT 150");
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_item'])) {
                $data = [
                    'school_id' => (int)$_POST['school_id'], 'title' => trim($_POST['title']),
                    'type' => $_POST['type'] ?? 'book', 'author' => trim($_POST['author'] ?? ''),
                    'category' => trim($_POST['category'] ?? ''), 'description' => trim($_POST['description'] ?? ''),
                    'status' => $_POST['status'] ?? 'published',
                ];
                if (isset($_FILES['file']) && $_FILES['file']['name']) {
                    [$ok, $path] = upload_file($_FILES['file'], 'uploads/library', ['pdf','doc','docx','ppt','pptx','mp4','webm','mp3']);
                    if ($ok) $data['file_path'] = $path;
                }
                if ($data['title'] === '') { flash('danger', 'Title required.'); redirect('admin/library'); }
                Database::insert('library_items', $data);
                flash('success', 'Library item added.');
                redirect('admin/library');
            }
            if (($del = (int)($_POST['delete_item'] ?? 0))) {
                Database::delete('library_items', 'id = ?', [$del]);
                flash('success', 'Item deleted.');
                redirect('admin/library');
            }
        }
        Router::render('app/admin/library', ['title' => 'Library', 'items' => $items, 'schools' => $schools, 'sort' => $sort, 'dir' => $dir]);
    }
}

/* =============== ADMIN: transfers =============== */
class Ctl_transfers {
    public function run(): void {
        $u = require_role('ministry');
        $requests = Database::all(
            "SELECT t.*, st.first_name AS sf, st.last_name AS sl, st.student_id,
                    fs.name AS from_school, ts.name AS to_school
             FROM transfer_requests t
             JOIN users st ON st.id = t.student_id
             JOIN schools fs ON fs.id = t.from_school_id
             JOIN schools ts ON ts.id = t.to_school_id
             ORDER BY t.created_at DESC LIMIT 100");
        $codes = Database::all(
            "SELECT c.*, s.name AS school_name, CONCAT(us.first_name, ' ', us.last_name) AS used_by
             FROM transfer_codes c LEFT JOIN schools s ON s.id = c.school_id
             LEFT JOIN users us ON us.id = c.student_id
             ORDER BY c.created_at DESC LIMIT 100");
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active' AND type IN ('university','college')");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_code'])) {
                $code = 'TRF-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4)) . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
                Database::insert('transfer_codes', [
                    'code' => $code, 'school_id' => (int)$_POST['school_id'],
                    'purpose' => $_POST['purpose'] ?? 'referral',
                    'expires_at' => $_POST['expires_at'] ?: date('Y-m-d H:i:s', time() + 86400 * 90),
                ]);
                flash('success', 'Code created: ' . $code);
                redirect('admin/transfers');
            }
            if (($rid = (int)($_POST['approve'] ?? 0))) {
                $req = Database::one("SELECT * FROM transfer_requests WHERE id = ?", [$rid]);
                if ($req) {
                    Database::update('users', ['school_id' => $req['to_school_id']], 'id = ?', [$req['student_id']]);
                    if (!empty($req['source_student_id'])) {
                        transfer_copy_record($req, $u['id']);
                    } else {
                        Database::update('transfer_requests', ['status' => 'completed', 'approved_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$rid]);
                    }
                    notify((int)$req['student_id'], 'announcement', 'Transfer approved!', 'Welcome to your new school.', 'dashboard');
                    flash('success', 'Transfer approved — student moved' . (!empty($req['source_student_id']) ? ' and their full record copied.' : '.'));
                }
                redirect('admin/transfers');
            }
            if (($rid = (int)($_POST['reject'] ?? 0))) {
                Database::update('transfer_requests', ['status' => 'rejected', 'approved_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$rid]);
                flash('success', 'Transfer rejected.');
                redirect('admin/transfers');
            }
            if (($del = (int)($_POST['delete_code'] ?? 0))) {
                Database::delete('transfer_codes', 'id = ?', [$del]);
                flash('success', 'Code deleted.');
                redirect('admin/transfers');
            }
        }
        Router::render('app/admin/transfers', ['title' => 'Transfers', 'requests' => $requests, 'codes' => $codes, 'schools' => $schools]);
    }
}

/* =============== ADMIN: transfer detail =============== */
class Ctl_transfer {
    public function run(): void {
        $u = require_role('ministry');
        $id = (int)($_GET['id'] ?? 0);
        $req = Database::one(
            "SELECT t.*, st.first_name AS sf, st.last_name AS sl, st.email AS semail, st.student_id AS sstid, st.status AS sstatus,
                    src.first_name AS ocf, src.last_name AS ocl,
                    fs.name AS from_school, ts.name AS to_school, ap.first_name AS af, ap.last_name AS al
             FROM transfer_requests t
             JOIN users st ON st.id = t.student_id
             LEFT JOIN users src ON src.id = t.source_student_id
             JOIN schools fs ON fs.id = t.from_school_id
             JOIN schools ts ON ts.id = t.to_school_id
             LEFT JOIN users ap ON ap.id = t.approved_by
             WHERE t.id = ?", [$id]);
        if (!$req) { flash('danger', 'Transfer request not found.'); redirect('admin/transfers'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['approve'])) {
                if (!empty($req['source_student_id'])) {
                    transfer_copy_record($req, $u['id']);
                } else {
                    Database::update('users', ['school_id' => $req['to_school_id']], 'id = ?', [$req['student_id']]);
                    Database::update('transfer_requests', ['status' => 'completed', 'approved_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s'), 'completed_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
                }
                notify((int)$req['student_id'], 'announcement', 'Transfer approved!', 'Welcome to your new school.', 'dashboard');
                log_activity('transfer', "Approved transfer #$id for {$req['sf']} {$req['sl']}", (int)$u['id']);
                flash('success', 'Transfer approved.');
                redirect('admin/transfer&id=' . $id);
            }
            if (isset($_POST['reject'])) {
                Database::update('transfer_requests', ['status' => 'rejected', 'approved_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
                log_activity('transfer', "Rejected transfer #$id for {$req['sf']} {$req['sl']}", (int)$u['id']);
                flash('success', 'Transfer rejected.');
                redirect('admin/transfer&id=' . $id);
            }
        }

        $history = Database::all("SELECT * FROM transfer_requests WHERE student_id = ? OR source_student_id = ? ORDER BY created_at DESC", [$req['student_id'], $req['student_id']]);
        $snapshot = json_decode((string)$req['record_snapshot'], true);
        $attendance = Database::all("SELECT at.status, at.date, c.title AS course FROM attendance at JOIN courses c ON c.id = at.course_id WHERE at.student_id = ? ORDER BY at.date DESC LIMIT 10", [$req['student_id']]);
        $grades = Database::all(
            "SELECT c.title, ROUND(AVG(a.points_earned / NULLIF(q.points, 0)) * 100, 1) AS pct FROM exam_answers a
             JOIN exam_questions q ON q.id = a.question_id
             JOIN exam_attempts at2 ON at2.id = a.attempt_id
             JOIN exams e ON e.id = at2.exam_id
             JOIN courses c ON c.id = e.course_id
             WHERE at2.student_id = ? GROUP BY c.id LIMIT 10", [$req['student_id']]);

        Router::render('app/admin/transfer_profile', [
            'title' => 'Transfer #' . $id, 'req' => $req, 'history' => $history,
            'snapshot' => $snapshot, 'attendance' => $attendance, 'grades' => $grades,
        ]);
    }
}

/* =============== ADMIN: integrity ledger =============== */
class Ctl_ledger {
    public function run(): void {
        $u = require_role('ministry', 'principal');
        $schoolId = (int)($u['role'] === 'principal' ? $u['school_id'] : ($_GET['school'] ?? $u['school_id'] ?? 0));
        $schoolId = (int)$schoolId;
        if ($schoolId <= 0) {
            $schools = Database::all("SELECT id, name FROM schools ORDER BY name");
            Router::render('app/admin/ledger', ['title' => 'Integrity Ledger', 'schoolId' => 0, 'schools' => $schools, 'status' => null, 'entries' => [], 'school' => null]);
            return;
        }
        $school = Database::one("SELECT id, name FROM schools WHERE id = ?", [$schoolId]);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['enable_2fa'])) {
                $staffId = (int)($_POST['staff_id'] ?? 0);
                $staff = Database::one("SELECT id, first_name, last_name, email, twofa_enabled FROM users WHERE id = ? AND school_id = ? AND role IN ('regional','principal','teacher')", [$staffId, $schoolId]);
                if (!$staff) { flash('danger', 'Staff member not found.'); redirect('admin/ledger&school=' . $schoolId); }
                // Re-issuing rotates the key, so refuse to clobber a key that's already enabled
                // but whose file the recipient may not have downloaded yet.
                if ((int)$staff['twofa_enabled'] === 1) {
                    flash('danger', '2FA is already enabled for ' . $staff['first_name'] . '. Use the "Download key" link to fetch the activation file again.');
                    redirect('admin/ledger&school=' . $schoolId);
                }
                $file = Auth::henaIssue($staffId);
                // Persist the activation file so it can be re-downloaded without re-issuing.
                $keysDir = STORAGE_PATH . '/keys';
                if (!is_dir($keysDir)) @mkdir($keysDir, 0770, true);
                $path = $keysDir . '/hena_' . $staffId . '.hena';
                if (file_put_contents($path, $file) !== false) @chmod($path, 0600);
                log_activity('ledger', "2FA enabled for {$staff['first_name']} {$staff['last_name']}", (int)$u['id']);
                flash('success', 'USB 2FA activated for ' . $staff['first_name'] . ' ' . $staff['last_name'] . '. Download the one-time key below and hand it to them over a secure channel.');
                redirect('admin/ledger&school=' . $schoolId . '&key=' . $staffId);
            }
            if (isset($_POST['download_2fa_key'])) {
                $staffId = (int)($_POST['staff_id'] ?? 0);
                $staff = Database::one("SELECT id, first_name, last_name, twofa_enabled FROM users WHERE id = ? AND school_id = ? AND role IN ('regional','principal','teacher')", [$staffId, $schoolId]);
                if (!$staff || (int)$staff['twofa_enabled'] !== 1) { flash('danger', 'Staff member has no active 2FA key.'); redirect('admin/ledger&school=' . $schoolId); }
                $path = STORAGE_PATH . '/keys/hena_' . $staffId . '.hena';
                if (!is_file($path)) {
                    // File was never persisted (e.g. older version) — re-issue once.
                    $file = Auth::henaIssue($staffId);
                    $dir = STORAGE_PATH . '/keys';
                    if (!is_dir($dir)) @mkdir($dir, 0770, true);
                    if (file_put_contents($path, $file) !== false) @chmod($path, 0600);
                }
                $bytes = (string)file_get_contents($path);
                header('Content-Type: text/plain');
                header('Content-Disposition: attachment; filename="hena_' . $staff['first_name'] . '_' . $staffId . '.hena"');
                header('Content-Length: ' . strlen($bytes));
                echo $bytes;
                exit;
            }
            if (isset($_POST['disable_2fa'])) {
                $staffId = (int)($_POST['staff_id'] ?? 0);
                $staff = Database::one("SELECT id, first_name, last_name, email FROM users WHERE id = ? AND school_id = ? AND role IN ('regional','principal','teacher')", [$staffId, $schoolId]);
                if ($staff) {
                    Auth::henaReset($staffId);
                    @unlink(STORAGE_PATH . '/keys/hena_' . $staffId . '.hena');
                    log_activity('ledger', "2FA disabled for {$staff['first_name']} {$staff['last_name']}", (int)$u['id']);
                    flash('success', '2FA disabled for ' . $staff['first_name'] . ' ' . $staff['last_name'] . '.');
                } else flash('danger', 'Staff member not found.');
                redirect('admin/ledger&school=' . $schoolId);
            }
            if (isset($_POST['reverify'])) {
                $res = Ledger::verify($schoolId);
                log_activity('ledger', "Re-verified ledger for {$school['name']}: " . ($res['ok'] ? 'INTACT' : 'BROKEN at #' . $res['broken_at']), (int)$u['id']);
                flash($res['ok'] ? 'success' : 'danger', 'Chain verification: ' . ($res['ok'] ? 'INTACT — all ' . $res['checked'] . ' entries valid.' : 'BROKEN at entry #' . $res['broken_at'] . '.'));
                redirect('admin/ledger&school=' . $schoolId);
            }
            if (isset($_POST['export_ledger'])) {
                $rows = Database::all(
                    "SELECT l.id, l.event_type, l.entity_type, l.entity_id, CONCAT(us.first_name, ' ', us.last_name) AS actor, l.payload, l.prev_hash, l.record_hash, l.created_at
                     FROM ledger l LEFT JOIN users us ON us.id = l.actor_id
                     WHERE l.school_id = ? ORDER BY l.id ASC", [$schoolId]);
                $dir = STORAGE_PATH . '/reports';
                if (!is_dir($dir)) @mkdir($dir, 0775, true);
                $file = 'reports/ledger_' . $schoolId . '_' . date('Ymd_His') . '.csv';
                $fp = fopen(STORAGE_PATH . '/' . $file, 'w');
                if ($rows) {
                    fputcsv($fp, array_keys($rows[0]));
                    foreach ($rows as $r) fputcsv($fp, array_values($r));
                }
                fclose($fp);
                flash('success', 'Ledger exported.');
                redirect('file?p=' . $file . '&dl=1');
            }
        }
        $status = Ledger::status($schoolId);
        $entries = Database::all(
            "SELECT l.*, CONCAT(us.first_name, ' ', us.last_name) AS actor FROM ledger l
             LEFT JOIN users us ON us.id = l.actor_id
             WHERE l.school_id = ? ORDER BY l.id DESC LIMIT 100", [$schoolId]);
        $schools = Database::all("SELECT id, name FROM schools ORDER BY name");

        // 2FA coverage for the ledger's staff (who can sign records)
        $staff = Database::all(
            "SELECT us.id, us.first_name, us.last_name, us.email, us.role, us.twofa_enabled, us.last_login
             FROM users us WHERE us.school_id = ? AND us.role IN ('regional','principal','teacher')
             ORDER BY us.role, us.last_name", [$schoolId]);
        $staffTwofa = ['ok' => 0, 'total' => count($staff)];
        foreach ($staff as $s) if ((int)$s['twofa_enabled'] === 1) $staffTwofa['ok']++;
        $authEvents = Database::all(
            "SELECT lh.*, CONCAT(us.first_name, ' ', us.last_name) AS user_name, us.email
             FROM login_history lh JOIN users us ON us.id = lh.user_id
             WHERE us.school_id = ? ORDER BY lh.id DESC LIMIT 12", [$schoolId]);

        $crypto = ['binary' => CWorker::available(), 'chain_verified' => (bool)$status['ok']];

        Router::render('app/admin/ledger', [
            'title' => 'Integrity Ledger', 'schoolId' => $schoolId, 'schools' => $schools,
            'status' => $status, 'entries' => $entries, 'school' => $school,
            'staff' => $staff, 'staffTwofa' => $staffTwofa, 'authEvents' => $authEvents,
            'crypto' => $crypto, 'keyFileHint' => (int)($_GET['key'] ?? 0),
        ]);
    }
}

/* =============== ADMIN: security console (ledger-scoped) =============== */
class Ctl_security {
    public function run(): void {
        $u = require_role('ministry');
        $schoolId = (int)($_GET['school'] ?? $u['school_id'] ?? 0);
        if ($schoolId <= 0) {
            $schools = Database::all("SELECT id, name FROM schools ORDER BY name");
            Router::render('app/admin/security', ['title' => 'Security Console', 'schoolId' => 0, 'schools' => $schools, 'status' => null, 'entries' => [], 'school' => null]);
            return;
        }
        $school = Database::one("SELECT id, name FROM schools WHERE id = ?", [$schoolId]);
        if (!$school) { flash('danger', 'School not found.'); redirect('admin/security'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['note'])) {
                $note = trim($_POST['note'] ?? '');
                if ($note !== '') {
                    Ledger::append($schoolId, (int)$u['id'], 'audit.note', 'security.console', 0, ['note' => $note]);
                    log_activity('ledger', "Audit note on {$school['name']}: " . $note, (int)$u['id']);
                    flash('success', 'Audit note appended to the chain.');
                }
                redirect('admin/security&school=' . $schoolId);
            }
            if (isset($_POST['reverify'])) {
                $res = Ledger::verify($schoolId);
                log_activity('ledger', "Re-verified ledger for {$school['name']}: " . ($res['ok'] ? 'INTACT' : 'BROKEN at #' . $res['broken_at']), (int)$u['id']);
                flash($res['ok'] ? 'success' : 'danger', 'Chain verification: ' . ($res['ok'] ? 'INTACT — all ' . $res['checked'] . ' entries valid.' : 'BROKEN at entry #' . $res['broken_at'] . '.'));
                redirect('admin/security&school=' . $schoolId);
            }
            if (isset($_POST['export_ledger'])) {
                $rows = Database::all(
                    "SELECT l.id, l.event_type, l.entity_type, l.entity_id, CONCAT(us.first_name, ' ', us.last_name) AS actor, l.payload, l.prev_hash, l.record_hash, l.created_at
                     FROM ledger l LEFT JOIN users us ON us.id = l.actor_id
                     WHERE l.school_id = ? ORDER BY l.id ASC", [$schoolId]);
                $dir = STORAGE_PATH . '/reports';
                if (!is_dir($dir)) @mkdir($dir, 0775, true);
                $file = 'reports/ledger_' . $schoolId . '_' . date('Ymd_His') . '.csv';
                $fp = fopen(STORAGE_PATH . '/' . $file, 'w');
                if ($rows) fputcsv($fp, array_keys($rows[0]));
                foreach ($rows as $r) fputcsv($fp, array_values($r));
                fclose($fp);
                flash('success', 'Ledger exported.');
                redirect('file?p=' . $file . '&dl=1');
            }
            if (isset($_POST['enable_2fa'])) {
                $staffId = (int)($_POST['staff_id'] ?? 0);
                $staff = Database::one("SELECT id, first_name, last_name FROM users WHERE id = ? AND school_id = ? AND role IN ('regional','principal','teacher')", [$staffId, $schoolId]);
                if (!$staff) { flash('danger', 'Staff member not found.'); redirect('admin/security&school=' . $schoolId); }
                if ((int)Database::scalar("SELECT twofa_enabled FROM users WHERE id = ?", [$staffId], 0) === 1) { flash('danger', '2FA already enabled for ' . $staff['first_name'] . '.'); redirect('admin/security&school=' . $schoolId); }
                $file = Auth::henaIssue($staffId);
                $keysDir = STORAGE_PATH . '/keys';
                if (!is_dir($keysDir)) @mkdir($keysDir, 0770, true);
                if (file_put_contents($keysDir . '/hena_' . $staffId . '.hena', $file) !== false) @chmod($keysDir . '/hena_' . $staffId . '.hena', 0600);
                log_activity('ledger', "2FA enabled for {$staff['first_name']} {$staff['last_name']}", (int)$u['id']);
                flash('success', 'USB 2FA activated for ' . $staff['first_name'] . ' ' . $staff['last_name'] . '.');
                redirect('admin/security&school=' . $schoolId . '&key=' . $staffId);
            }
            if (isset($_POST['disable_2fa'])) {
                $staffId = (int)($_POST['staff_id'] ?? 0);
                $staff = Database::one("SELECT id, first_name, last_name FROM users WHERE id = ? AND school_id = ? AND role IN ('regional','principal','teacher')", [$staffId, $schoolId]);
                if ($staff) {
                    Auth::henaReset($staffId);
                    @unlink(STORAGE_PATH . '/keys/hena_' . $staffId . '.hena');
                    log_activity('ledger', "2FA disabled for {$staff['first_name']} {$staff['last_name']}", (int)$u['id']);
                    flash('success', '2FA disabled.');
                }
                redirect('admin/security&school=' . $schoolId);
            }
        }

        $status = Ledger::status($schoolId);
        $entries = Database::all(
            "SELECT l.*, CONCAT(us.first_name, ' ', us.last_name) AS actor FROM ledger l
             LEFT JOIN users us ON us.id = l.actor_id
             WHERE l.school_id = ? ORDER BY l.id DESC LIMIT 60", [$schoolId]);
        $schools = Database::all("SELECT id, name FROM schools ORDER BY name");
        $authEvents = Database::all(
            "SELECT lh.*, CONCAT(us.first_name, ' ', us.last_name) AS user_name, us.email
             FROM login_history lh JOIN users us ON us.id = lh.user_id
             WHERE us.school_id = ? ORDER BY lh.id DESC LIMIT 10", [$schoolId]);
        try {
            $crypto = CWorker::selfTest();
        } catch (Throwable $e) {
            $crypto = ['ok' => false, 'cwe' => [], 'error' => $e->getMessage()];
        }
        $crypto['binary'] = CWorker::available();

        Router::render('app/admin/security', [
            'title' => 'Security Console', 'schoolId' => $schoolId, 'schools' => $schools,
            'status' => $status, 'entries' => $entries, 'school' => $school,
            'authEvents' => $authEvents, 'crypto' => $crypto,
            'chainIntact' => (bool)($status['ok'] ?? false), 'keyFileHint' => (int)($_GET['key'] ?? 0),
        ]);
    }
}

/* =============== ADMIN: system =============== */
class Ctl_system {
    public function run(): void {
        $u = require_role('ministry');
        $info = [
            'PHP version' => PHP_VERSION,
            'PHP SAPI' => php_sapi_name(),
            'MySQL' => Database::scalar("SELECT VERSION()", [], '—'),
            'Server' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            'Platform' => php_uname('s') . ' ' . php_uname('r'),
            'Upload max' => ini_get('upload_max_filesize'),
            'Memory limit' => ini_get('memory_limit'),
            'App version' => '1.0.0',
            'Storage' => is_writable(STORAGE_PATH) ? 'writable ✓' : 'NOT WRITABLE ✗',
        ];
        $tables = [];
        foreach (Database::all("SHOW TABLES") as $row) {
            $t = array_values($row)[0];
            $tables[$t] = (int)Database::scalar("SELECT COUNT(*) FROM `$t`", [], 0);
        }
        Router::render('app/admin/system', ['title' => 'System', 'info' => $info, 'tables' => $tables]);
    }
}

/* =============== ADMIN: badge & achievement manager =============== */
class Ctl_admin_badges {
    public function run(): void {
        $u = require_role('ministry');
        $cats = ['learning' => 'Learning', 'streak' => 'Streaks', 'quiz' => 'Quizzes', 'attendance' => 'Attendance', 'community' => 'Community', 'level' => 'Levels'];
        $icons = ['medal', 'medal-gold', 'medal-silver', 'medal-bronze', 'trophy', 'crown', 'star', 'flame', 'bolt', 'leaf', 'brain', 'books', 'graduation', 'target', 'handshake', 'run', 'heart', 'rocket', 'smile', 'thumbs-up'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['save_badge'])) {
                $id = (int)($_POST['badge_id'] ?? 0);
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'icon' => trim($_POST['icon'] ?? 'medal'),
                    'description' => trim($_POST['description'] ?? ''),
                    'category' => in_array($_POST['category'] ?? '', array_keys($cats), true) ? $_POST['category'] : 'learning',
                    'xp_required' => max(0, (int)($_POST['xp_required'] ?? 0)),
                ];
                if ($data['name'] === '') { flash('danger', 'Badge name required.'); redirect('admin/badges'); }
                if ($id) Database::update('badges', $data, 'id = ?', [$id]);
                else Database::insert('badges', $data);
                log_activity('badges', ($id ? 'Updated' : 'Created') . ' badge: ' . $data['name'], (int)$u['id']);
                flash('success', 'Badge saved.');
                redirect('admin/badges');
            }
            if (($bid = (int)($_POST['delete_badge'] ?? 0))) {
                Database::delete('user_badges', 'badge_id = ?', [$bid]);
                Database::delete('badges', 'id = ?', [$bid]);
                flash('success', 'Badge deleted.');
                redirect('admin/badges');
            }
            if (isset($_POST['award_badge'])) {
                $bid = (int)($_POST['badge_id'] ?? 0);
                $sn = trim($_POST['student_id'] ?? '');
                $user = $sn !== ''
                    ? Database::one("SELECT id FROM users WHERE (student_id = ? OR email = ?) AND role = 'student' LIMIT 1", [$sn, $sn])
                    : null;
                if (!$bid || !$user) { flash('danger', 'Badge or student not found.'); redirect('admin/badges'); }
                Database::run("INSERT IGNORE INTO user_badges (user_id, badge_id, earned_at) VALUES (?,?,NOW())", [$user['id'], $bid]);
                notify((int)$user['id'], 'achievement', 'You earned a badge!', (string)Database::scalar("SELECT name FROM badges WHERE id = ?", [$bid], 'Badge'), 'gamification/badges');
                flash('success', 'Badge awarded.');
                redirect('admin/badges');
            }
        }
        $all = Database::all("SELECT * FROM badges ORDER BY xp_required, id");
        $students = Database::all("SELECT id, student_id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE role = 'student' AND status = 'active' ORDER BY last_name LIMIT 500");
        $earnedCount = [];
        foreach (Database::all("SELECT badge_id, COUNT(*) c FROM user_badges GROUP BY badge_id") as $r) $earnedCount[$r['badge_id']] = (int)$r['c'];
        Router::render('app/admin/badges', [
            'title' => 'Badges & Achievements', 'all' => $all, 'cats' => $cats, 'icons' => $icons,
            'students' => $students, 'earnedCount' => $earnedCount,
        ]);
    }
}


/* =============== ADMIN: module registry =============== */
class Ctl_modules {
    public function run(): void {
        require_role('ministry');
        $mode = $_GET['view'] ?? '';
        $q = trim($_GET['q'] ?? '');
        $cat = $_GET['cat'] ?? '';
        $only = $_GET['only'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $key = trim($_POST['module_key'] ?? '');
            $mod = Database::one("SELECT id, module_key, is_core FROM modules WHERE module_key = ?", [$key]);
            if (!$mod) { flash('danger', 'Module not found.'); redirect('admin/modules'); }
            if (isset($_POST['toggle'])) {
                $new = ((int)$_POST['toggle'] === 1) ? 1 : 0;
                if ($new === 0 && (int)$mod['is_core'] === 1) {
                    flash('danger', 'Core modules cannot be disabled.');
                } else {
                    Database::update('modules', ['enabled' => $new], 'id = ?', [(int)$mod['id']]);
                    log_activity('module', ($new ? 'Enabled' : 'Disabled') . ' module: ' . $mod['module_key'], (int)me()['id']);
                    flash('success', 'Module ' . ($new ? 'enabled' : 'disabled') . '.');
                }
                redirect('admin/modules' . ($_GET['view'] ?? '') ? '?view=' . $_GET['view'] : '');
            }
            if (isset($_POST['install'])) {
                Database::update('modules', ['enabled' => 1, 'installed_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$mod['id']]);
                log_activity('module', 'Installed module: ' . $mod['module_key'], (int)me()['id']);
                flash('success', 'Module installed.');
                redirect('admin/modules');
            }
        }

        $where = "1=1"; $args = [];
        if ($q !== '') { $where .= " AND (module_key LIKE ? OR name LIKE ? OR description LIKE ?)"; array_push($args, "%$q%", "%$q%", "%$q%"); }
        if (in_array($cat, ['core', 'education', 'portal', 'service'], true)) { $where .= " AND category = ?"; $args[] = $cat; }
        if (in_array($only, ['on', 'off'], true)) { $where .= $only === 'on' ? " AND enabled = 1" : " AND enabled = 0"; }
        $sort = $_GET['sort'] ?? 'name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $sortMap = ['name' => 'name', 'category' => 'category', 'level' => 'education_type', 'installed_at' => 'installed_at', 'enabled' => 'enabled'];
        if (!isset($sortMap[$sort])) $sort = 'name';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', id ASC';
        $modules = Database::all("SELECT * FROM modules WHERE $where ORDER BY is_core DESC, $orderBy", $args);
        $cats = Database::all("SELECT category, COUNT(*) c FROM modules GROUP BY category");
        $counts = [
            'all' => (int)Database::scalar("SELECT COUNT(*) FROM modules", [], 0),
            'on' => (int)Database::scalar("SELECT COUNT(*) FROM modules WHERE enabled = 1", [], 0),
            'off' => (int)Database::scalar("SELECT COUNT(*) FROM modules WHERE enabled = 0", [], 0),
        ];
        $mode = $_GET['view'] ?? '';
        if ($mode === 'levels') {
            $rows = Database::all(
                "SELECT m.education_type, m.name, m.module_key, m.enabled FROM modules m
                 WHERE m.education_type != 'all' AND m.education_type != '' ORDER BY m.education_type, m.name");
            Router::render('app/admin/modules_levels', ['title' => 'Modules by Level', 'rows' => $rows, 'counts' => $counts]);
            return;
        }
        Router::render('app/admin/modules', ['title' => 'Modules', 'modules' => $modules, 'cats' => $cats, 'counts' => $counts, 'q' => $q, 'cat' => $cat, 'only' => $only, 'sort' => $sort, 'dir' => $dir]);
    }
}

/* =============== ADMIN: per-school module installer =============== */
class Ctl_school_modules {
    public function run(): void {
        require_role('ministry');
        $sid = (int)($_GET['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $sid = (int)($_POST['school_id'] ?? 0);
            $school = Database::one("SELECT id, name FROM schools WHERE id = ?", [$sid]);
            if (!$school) { flash('danger', 'School not found.'); redirect('admin/school-modules'); }
            $key = trim((string)($_POST['module_key'] ?? ''));
            $mod = Database::one("SELECT module_key, is_core FROM modules WHERE module_key = ?", [$key]);
            if (!$mod) { flash('danger', 'Module not found.'); redirect('admin/school-modules&id=' . $sid); }
            if (isset($_POST['set_module'])) {
                $enabled = ((int)$_POST['set_module'] === 1) ? 1 : 0;
                Database::run(
                    "INSERT INTO school_modules (school_id, module_key, enabled) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE enabled = VALUES(enabled)",
                    [$sid, $key, $enabled]
                );
                log_activity('module.school', ($enabled ? 'Installed' : 'Uninstalled') . " module {$key} at school #{$sid}", (int)me()['id']);
                flash('success', "Module " . ($enabled ? 'installed' : 'uninstalled') . " for {$school['name']}.");
            }
            if (isset($_POST['reset_defaults'])) {
                Database::delete('school_modules', 'school_id = ?', [$sid]);
                ensure_school_modules($sid);
                flash('success', "Module set reset to {$school['name']}'s education level defaults.");
            }
            redirect('admin/school-modules&id=' . $sid);
        }
        $schools = Database::all("SELECT id, name, education_level, code FROM schools ORDER BY name");
        $school = $sid ? Database::one("SELECT id, name, education_level FROM schools WHERE id = ?", [$sid]) : null;
        $modules = Database::all("SELECT * FROM modules ORDER BY is_core DESC, category, name");
        $installed = $sid ? Database::all("SELECT module_key, enabled FROM school_modules WHERE school_id = ?", [$sid]) : [];
        $map = [];
        foreach ($installed as $m) $map[$m['module_key']] = (int)$m['enabled'];
        Router::render('app/admin/school_modules', [
            'title' => 'School Modules', 'schools' => $schools, 'school' => $school,
            'modules' => $modules, 'map' => $map,
        ]);
    }
}

/* =============== ADMIN: national AI usage report =============== */
class Ctl_ai_reports {
    public function run(): void {
        require_role('ministry');
        $perSchool = Database::all(
            "SELECT sc.id, sc.name, sc.education_level,
                    (SELECT COUNT(*) FROM ai_chats ac JOIN users u2 ON u2.id = ac.user_id WHERE u2.school_id = sc.id) AS chats,
                    (SELECT COUNT(*) FROM ai_messages am JOIN ai_chats ac2 ON ac2.id = am.chat_id JOIN users u3 ON u3.id = ac2.user_id WHERE u3.school_id = sc.id) AS msgs,
                    (SELECT COUNT(DISTINCT ac3.user_id) FROM ai_chats ac3 JOIN users u4 ON u4.id = ac3.user_id WHERE u4.school_id = sc.id) AS users
             FROM schools sc ORDER BY msgs DESC");
        $perLevel = Database::all(
            "SELECT sc.education_level AS level, COUNT(DISTINCT ac.id) AS chats, COUNT(am.id) AS msgs
             FROM ai_chats ac JOIN users u ON u.id = ac.user_id JOIN schools sc ON sc.id = u.school_id
             LEFT JOIN ai_messages am ON am.chat_id = ac.id
             GROUP BY sc.education_level ORDER BY msgs DESC");
        $perRole = Database::all(
            "SELECT u.role, COUNT(DISTINCT ac.id) AS chats, COUNT(am.id) AS msgs
             FROM ai_chats ac JOIN users u ON u.id = ac.user_id
             LEFT JOIN ai_messages am ON am.chat_id = ac.id
             GROUP BY u.role ORDER BY msgs DESC");
        $totals = [
            'chats' => (int)Database::scalar("SELECT COUNT(*) FROM ai_chats", [], 0),
            'msgs' => (int)Database::scalar("SELECT COUNT(*) FROM ai_messages", [], 0),
            'users' => (int)Database::scalar("SELECT COUNT(DISTINCT user_id) FROM ai_chats", [], 0),
            'active7' => (int)Database::scalar("SELECT COUNT(DISTINCT user_id) FROM ai_chats WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)", [], 0),
        ];
        $topSchool = $perSchool[0] ?? null;
        $topLevel = $perLevel[0] ?? null;
        $narrative = 'Nationwide, ' . $totals['msgs'] . ' AI messages were exchanged across ' . $totals['chats'] . ' chats by ' . $totals['users'] . ' users, ' . $totals['active7'] . ' of them active in the last 7 days. '
            . ($topSchool ? 'The most active institution is ' . $topSchool['name'] . ' (' . $topSchool['msgs'] . ' messages). ' : '')
            . ($topLevel ? 'AI usage is concentrated at the ' . $topLevel['level'] . ' level (' . $topLevel['msgs'] . ' messages). ' : '')
            . 'Recommendation: prioritize AI tutor onboarding for low-usage schools and review prompt quality at high-usage institutions.';
        Router::render('app/admin/ai_reports', [
            'title' => 'National AI Report', 'perSchool' => $perSchool, 'perLevel' => $perLevel,
            'perRole' => $perRole, 'totals' => $totals, 'narrative' => $narrative,
        ]);
    }
}

/* =============== ADMIN: emergency override panel =============== */
class Ctl_override {
    public function run(): void {
        if (trim($_GET['r'] ?? '', '/') === 'admin/override-exit') {
            if (empty($_SESSION['impersonated_by'])) { redirect('dashboard'); }
            $u = me();
            $real = Database::one("SELECT * FROM users WHERE id = ?", [(int)$_SESSION['impersonated_by']]);
            $_SESSION['user'] = $real;
            unset($_SESSION['user']['password_hash'], $_SESSION['user']['twofa_secret']);
            $_SESSION['sv'] = (int)$real['session_version'];
            unset($_SESSION['impersonated_by']);
            log_activity('override.exit', 'Exited emergency impersonation', (int)$u['id']);
            flash('success', 'Override exited. Back to your own account.');
            redirect('dashboard');
        }
        $u = require_role('ministry');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $target = Database::one("SELECT id, role, first_name, last_name, email, status FROM users WHERE id = ?", [(int)($_POST['user_id'] ?? 0)]);
            if (!$target) { flash('danger', 'User not found.'); redirect('admin/override'); }
            if (isset($_POST['unlock'])) {
                Database::update('users', ['status' => 'active'], 'id = ?', [(int)$target['id']]);
                Database::delete('login_history', "user_id = ? AND status = 'failed'", [(int)$target['id']]);
                log_activity('override.unlock', 'Emergency unlock of ' . $target['email'], (int)$u['id']);
                flash('success', 'Account unlocked and failed attempts cleared.');
            }
            if (isset($_POST['reset_password'])) {
                $newPass = trim((string)($_POST['password'] ?? '')) ?: random_password();
                Database::update('users', ['password_hash' => password_hash($newPass, PASSWORD_DEFAULT)], 'id = ?', [(int)$target['id']]);
                Auth::bumpSessionVersion((int)$target['id']);
                Database::delete('sessions', 'user_id = ?', [(int)$target['id']]);
                log_activity('override.password', 'Emergency password reset for ' . $target['email'], (int)$u['id']);
                flash('success', 'Password reset. New password: ' . $newPass);
            }
            if (isset($_POST['revoke_sessions'])) {
                Database::run("UPDATE users SET session_version = session_version + 1 WHERE id = ?", [(int)$target['id']]);
                Database::delete('sessions', 'user_id = ?', [(int)$target['id']]);
                Database::run("DELETE FROM login_history WHERE user_id = ? AND status = 'success'", [(int)$target['id']]);
                log_activity('override.sessions', 'Revoked all sessions for ' . $target['email'], (int)$u['id']);
                flash('success', 'All sessions revoked for ' . $target['email'] . '.');
            }
            if (isset($_POST['impersonate'])) {
                $targetRow = Database::one("SELECT * FROM users WHERE id = ?", [(int)$target['id']]);
                $_SESSION['user'] = $targetRow;
                unset($_SESSION['user']['password_hash'], $_SESSION['user']['twofa_secret']);
                $_SESSION['sv'] = (int)$targetRow['session_version'];
                $_SESSION['impersonated_by'] = (int)$u['id'];
                log_activity('override.impersonate', 'Emergency impersonation of ' . $target['email'] . ' by ministry', (int)$u['id']);
                flash('warning', 'You are now acting as ' . $target['email'] . ' (emergency override). Exit via the banner.');
                redirect('dashboard');
            }
            redirect('admin/override');
        }
        $q = trim((string)($_GET['q'] ?? ''));
        $sort = $_GET['sort'] ?? 'last_name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $sortMap = ['user' => 'u.last_name', 'role' => 'u.role', 'school' => 'sc.name', 'status' => 'u.status', 'last_name' => 'u.last_name'];
        if (!isset($sortMap[$sort])) $sort = 'last_name';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', u.id ASC';
        $rows = [];
        if ($q !== '') {
            $like = '%' . $q . '%';
            $rows = Database::all(
                "SELECT u.id, u.role, u.first_name, u.last_name, u.email, u.status,
                        sc.name AS school_name
                 FROM users u LEFT JOIN schools sc ON sc.id = u.school_id
                 WHERE u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?
                 ORDER BY $orderBy LIMIT 30", [$like, $like, $like]);
        } else {
            $rows = Database::all(
                "SELECT u.id, u.role, u.first_name, u.last_name, u.email, u.status, sc.name AS school_name
                 FROM users u LEFT JOIN schools sc ON sc.id = u.school_id
                 WHERE u.role IN ('regional','principal','registrar','dean','teacher','student')
                 ORDER BY $orderBy LIMIT 30");
        }
        Router::render('app/admin/override', ['title' => 'Emergency Override', 'rows' => $rows, 'q' => $q, 'sort' => $sort, 'dir' => $dir]);
    }
}

/* =============== ADMIN: financial summaries (finance module) =============== */
class Ctl_finance {
    public function run(): void {
        require_role('ministry');
        $sort = $_GET['sort'] ?? 'name';
        $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $sortMap = ['name' => 'name', 'level' => 'level', 'paid_courses' => 'paid_courses', 'revenue' => 'revenue'];
        if (!isset($sortMap[$sort])) $sort = 'name';
        $rows = [];
        $schools = Database::all("SELECT id, name, education_level FROM schools ORDER BY name");
        foreach ($schools as $sc) {
            if (!module_active((int)$sc['id'], 'finance')) continue;
            $revenue = Database::scalar(
                "SELECT COALESCE(SUM(c.price * c.cnt), 0) FROM (
                    SELECT co.id, co.price, COUNT(ce.id) AS cnt FROM courses co
                    LEFT JOIN course_enrollments ce ON ce.course_id = co.id
                    WHERE co.school_id = ? AND co.price > 0 GROUP BY co.id) c", [(int)$sc['id']], 0);
            $paidCourses = (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE school_id = ? AND price > 0", [(int)$sc['id']], 0);
            $rows[] = [
                'name' => $sc['name'], 'level' => $sc['education_level'],
                'paid_courses' => $paidCourses, 'revenue' => round((float)$revenue, 2),
            ];
        }
        $sortCol = $sortMap[$sort];
        usort($rows, fn($a, $b) => $dir === 'asc' ? ($a[$sortCol] <=> $b[$sortCol]) : ($b[$sortCol] <=> $a[$sortCol]));
        $notEnabled = array_filter($schools, fn($s) => !module_active((int)$s['id'], 'finance'));
        Router::render('app/admin/finance', ['title' => 'Financial Summary', 'rows' => $rows, 'notEnabled' => $notEnabled, 'sort' => $sort, 'dir' => $dir]);
    }
}

/* =============== ADMIN: license management =============== */
class Ctl_licenses {
    public function run(): void {
        require_role('ministry');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_license'])) {
                $key = strtoupper('EDX-' . implode('-', array_map(fn() => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5), [1, 1, 1, 1])));
                $data = [
                    'school_id' => (int)($_POST['school_id'] ?? 0) ?: null,
                    'license_key' => $key,
                    'institution' => trim($_POST['institution'] ?? ''),
                    'type' => in_array($_POST['type'] ?? '', ['trial', 'standard', 'premium', 'enterprise'], true) ? $_POST['type'] : 'standard',
                    'seats' => max(0, (int)($_POST['seats'] ?? 0)),
                    'issued_at' => ($_POST['issued_at'] ?? '') ?: date('Y-m-d'),
                    'expires_at' => ($_POST['expires_at'] ?? '') ?: null,
                    'status' => 'active',
                ];
                Database::insert('licenses', $data);
                log_activity('license', 'Issued license ' . $key . ' to ' . ($data['institution'] ?: '—'), (int)me()['id']);
                flash('success', 'License issued: <b>' . $key . '</b>');
                redirect('admin/licenses');
            }
            if (($lid = (int)($_POST['toggle_license'] ?? 0))) {
                $lic = Database::one("SELECT id, status, license_key FROM licenses WHERE id = ?", [$lid]);
                if ($lic) {
                    $new = $lic['status'] === 'active' ? 'suspended' : 'active';
                    Database::update('licenses', ['status' => $new], 'id = ?', [$lid]);
                    log_activity('license', ($new === 'active' ? 'Reactivated' : 'Suspended') . ' license ' . $lic['license_key'], (int)me()['id']);
                    flash('success', 'License ' . $new . '.');
                }
                redirect('admin/licenses');
            }
            if (($lid = (int)($_POST['delete_license'] ?? 0))) {
                Database::delete('licenses', 'id = ?', [$lid]);
                flash('success', 'License deleted.');
                redirect('admin/licenses');
            }
        }
        $sort = $_GET['sort'] ?? 'created_at';
        $dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $sortMap = ['institution' => 'l.institution', 'type' => 'l.type', 'seats' => 'l.seats', 'issued' => 'l.issued_at', 'expires' => 'l.expires_at', 'status' => 'l.status', 'created_at' => 'l.created_at'];
        if (!isset($sortMap[$sort])) $sort = 'created_at';
        $orderBy = $sortMap[$sort] . ' ' . $dir . ', l.id DESC';
        $rows = Database::all(
            "SELECT l.*, sc.name AS school_name FROM licenses l LEFT JOIN schools sc ON sc.id = l.school_id
             ORDER BY $orderBy LIMIT 200");
        $schools = Database::all("SELECT id, name FROM schools ORDER BY name");
        Router::render('app/admin/licenses', ['title' => 'Licenses', 'rows' => $rows, 'schools' => $schools, 'sort' => $sort, 'dir' => $dir]);
    }
}
