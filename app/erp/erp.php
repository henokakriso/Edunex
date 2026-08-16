<?php
/**
 * EDUNEX ERP — HR, Payroll, Recruitment, Projects & Services, Documents,
 * Help Desk, Fixed Assets, Fleet Management.
 * Accessed by Director (own school) and Sysadmin (school picker).
 */

/** Resolve the school this ERP session operates on. */
function erp_school(): int {
    $u = me();
    if (!empty($u['school_id'])) return (int)$u['school_id'];
    $sid = (int)($_GET['school_id'] ?? 0);
    if ($sid && Database::one("SELECT id FROM schools WHERE id = ?", [$sid])) {
        $_SESSION['erp_school_id'] = $sid;
        return $sid;
    }
    $sid = (int)($_SESSION['erp_school_id'] ?? 0);
    return $sid ?: (int)Database::scalar("SELECT MIN(id) FROM schools", [], 0);
}

/** 403 unless the ERP module is installed for the school. */
function erp_require_module(string $key): void {
    $sid = erp_school();
    if (!module_active($sid, $key)) {
        http_response_code(403);
        die('This ERP module is not installed for the selected school.');
    }
}

/** Shared ERP boot: role check + school + module gate. */
function erp_boot(string $key): int {
    require_role(['director', 'sysadmin']);
    erp_require_module($key);
    return erp_school();
}

/* ===================== ERP: dashboard ===================== */
class Ctl_erp_dashboard {
    public function run(): void {
        require_role(['director', 'sysadmin']);
        $sid = erp_school();
        $stats = [];
        foreach (['hr', 'payroll', 'recruitment', 'projects', 'documents', 'helpdesk', 'assets', 'fleet'] as $key) {
            $stats[$key] = module_active($sid, $key);
        }
        $data = [
            'hr' => Database::scalar("SELECT COUNT(*) FROM hr_staff WHERE school_id = ? AND status='active'", [$sid], 0),
            'leave_pending' => Database::scalar("SELECT COUNT(*) FROM hr_leave WHERE school_id = ? AND status='pending'", [$sid], 0),
            'payroll_draft' => Database::scalar("SELECT COUNT(*) FROM payroll_runs WHERE school_id = ? AND status='draft'", [$sid], 0),
            'openings' => Database::scalar("SELECT COUNT(*) FROM job_openings WHERE school_id = ? AND status='open'", [$sid], 0),
            'applications' => Database::scalar("SELECT COUNT(*) FROM job_applications ja JOIN job_openings jo ON jo.id = ja.opening_id WHERE jo.school_id = ? AND ja.stage NOT IN ('hired','rejected')", [$sid], 0),
            'projects' => Database::scalar("SELECT COUNT(*) FROM projects WHERE school_id = ? AND status IN ('planning','active')", [$sid], 0),
            'over_budget' => Database::scalar("SELECT COUNT(*) FROM projects WHERE school_id = ? AND status IN ('planning','active') AND spent > budget", [$sid], 0),
            'docs' => Database::scalar("SELECT COUNT(*) FROM documents WHERE school_id = ?", [$sid], 0),
            'tickets_open' => Database::scalar("SELECT COUNT(*) FROM helpdesk_tickets WHERE school_id = ? AND status IN ('open','in_progress')", [$sid], 0),
            'tickets_urgent' => Database::scalar("SELECT COUNT(*) FROM helpdesk_tickets WHERE school_id = ? AND priority='urgent' AND status IN ('open','in_progress')", [$sid], 0),
            'assets' => Database::scalar("SELECT COUNT(*) FROM assets WHERE school_id = ? AND status != 'retired'", [$sid], 0),
            'assets_maint' => Database::scalar("SELECT COUNT(*) FROM assets WHERE school_id = ? AND status='under_maintenance'", [$sid], 0),
            'vehicles' => Database::scalar("SELECT COUNT(*) FROM fleet_vehicles WHERE school_id = ? AND status='active'", [$sid], 0),
            'vehicles_maint' => Database::scalar("SELECT COUNT(*) FROM fleet_vehicles WHERE school_id = ? AND status='maintenance'", [$sid], 0),
            'total_km' => Database::scalar("SELECT COALESCE(SUM(end_km - start_km),0) FROM fleet_trips WHERE school_id = ?", [$sid], 0),
            'payroll_month' => Database::scalar("SELECT COALESCE(SUM(net),0) FROM payroll_entries pe JOIN payroll_runs pr ON pr.id = pe.run_id WHERE pr.school_id = ? AND pr.period = DATE_FORMAT(NOW(), '%Y-%m')", [$sid], 0),
            'fleet_cost' => Database::scalar("SELECT COALESCE(SUM(fuel_cost),0) FROM fleet_trips WHERE school_id = ? AND trip_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", [$sid], 0),
            'asset_value' => Database::scalar("SELECT COALESCE(SUM(purchase_cost),0) FROM assets WHERE school_id = ? AND status != 'retired'", [$sid], 0),
        ];
        $school = Database::one("SELECT * FROM schools WHERE id = ?", [$sid]);
        $schools = (me()['role'] ?? '') === 'sysadmin' ? Database::all("SELECT id, name FROM schools ORDER BY name") : [];
        Router::render('app/erp/dashboard', [
            'title' => 'ERP Dashboard', 'school' => $school, 'schools' => $schools,
            'stats' => $stats, 'data' => $data,
        ]);
    }
}

/* ===================== ERP: HR ===================== */
class Ctl_erp_hr {
    public function run(): void {
        $sid = erp_boot('hr');
        $u = me();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['add_position'])) {
                Database::insert('hr_positions', [
                    'school_id' => $sid, 'title' => trim($_POST['title'] ?? ''),
                    'department_id' => (int)($_POST['department_id'] ?? 0) ?: null,
                    'level' => $_POST['level'] ?? 'staff',
                    'salary_scale' => max(0, (float)($_POST['salary_scale'] ?? 0)),
                ]);
                flash('success', 'Position created.');
            }
            if (isset($_POST['add_staff'])) {
                $uid = (int)($_POST['user_id'] ?? 0);
                if (Database::one("SELECT id FROM users WHERE id = ? AND role NOT IN ('student','parent')", [$uid])) {
                    Database::run("INSERT INTO hr_staff (school_id, user_id, position_id, hire_date, employment_type, supervisor_id, status) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE position_id = VALUES(position_id)", [
                        $sid, $uid, (int)($_POST['position_id'] ?? 0) ?: null,
                        $_POST['hire_date'] ?: null, $_POST['employment_type'] ?? 'full',
                        (int)($_POST['supervisor_id'] ?? 0) ?: null, 'active',
                    ]);
                    flash('success', 'Staff record saved.');
                } else {
                    flash('danger', 'Select a valid staff member.');
                }
            }
            if (isset($_POST['clock_in']) || isset($_POST['clock_out'])) {
                $today = date('Y-m-d');
                if (isset($_POST['clock_in'])) {
                    Database::run("INSERT INTO hr_attendance (school_id, user_id, work_date, check_in, status) VALUES (?,?,?,CURTIME(),'present') ON DUPLICATE KEY UPDATE check_in = COALESCE(check_in, CURTIME())", [$sid, $u['id'], $today]);
                    flash('success', 'Checked in.');
                } else {
                    Database::run("UPDATE hr_attendance SET check_out = CURTIME() WHERE user_id = ? AND work_date = ? AND check_out IS NULL", [$u['id'], $today]);
                    flash('success', 'Checked out.');
                }
            }
            if (isset($_POST['mark_attendance'])) {
                $date = $_POST['work_date'] ?? date('Y-m-d');
                $rows = Database::all("SELECT h.user_id, h.user_id AS uid FROM hr_staff h WHERE h.school_id = ? AND h.status = 'active'", [$sid]);
                foreach ($rows as $r) {
                    $st = $_POST['att_' . $r['uid']] ?? null;
                    if ($st) {
                        Database::run("INSERT INTO hr_attendance (school_id, user_id, work_date, status) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE status = VALUES(status)", [$sid, $r['uid'], $date, $st]);
                    }
                }
                flash('success', 'Attendance saved for ' . e($date));
            }
            if (isset($_POST['add_leave'])) {
                Database::insert('hr_leave', [
                    'school_id' => $sid, 'user_id' => (int)($_POST['user_id'] ?? 0),
                    'type' => $_POST['type'] ?? 'annual',
                    'start_date' => $_POST['start_date'] ?: null, 'end_date' => $_POST['end_date'] ?: null,
                    'days' => max(1, (int)($_POST['days'] ?? 1)), 'reason' => $_POST['reason'] ?? null,
                ]);
                flash('success', 'Leave request created.');
            }
            if (isset($_POST['decide_leave'])) {
                $status = ($_POST['decide_leave'] === 'approved') ? 'approved' : 'rejected';
                Database::update('hr_leave', ['status' => $status, 'decided_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$_POST['leave_id']]);
                flash('success', 'Leave ' . $status . '.');
            }
            redirect('erp/hr');
        }
        $positions = Database::all("SELECT p.*, d.name AS dept FROM hr_positions p LEFT JOIN departments d ON d.id = p.department_id WHERE p.school_id = ? ORDER BY p.level, p.title", [$sid]);
        $staff = Database::all(
            "SELECT h.*, u.first_name, u.last_name, u.email, u.role, p.title AS position, d.name AS dept,
                    s.first_name AS sup_first, s.last_name AS sup_last
             FROM hr_staff h JOIN users u ON u.id = h.user_id
             LEFT JOIN hr_positions p ON p.id = h.position_id
             LEFT JOIN departments d ON d.id = p.department_id
             LEFT JOIN users s ON s.id = h.supervisor_id
             WHERE h.school_id = ? ORDER BY h.status, u.last_name", [$sid]);
        $leave = Database::all(
            "SELECT l.*, u.first_name, u.last_name, u.email, p.title AS position
             FROM hr_leave l JOIN users u ON u.id = l.user_id
             LEFT JOIN hr_staff h ON h.user_id = u.id AND h.school_id = l.school_id
             LEFT JOIN hr_positions p ON p.id = h.position_id
             WHERE l.school_id = ? ORDER BY FIELD(l.status,'pending','approved','rejected'), l.start_date DESC", [$sid]);
        $attendance = Database::all(
            "SELECT a.*, u.first_name, u.last_name FROM hr_attendance a JOIN users u ON u.id = a.user_id
             WHERE a.school_id = ? ORDER BY a.work_date DESC, a.id DESC LIMIT 30", [$sid]);
        $candidates = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE school_id = ? AND role NOT IN ('student','parent') ORDER BY last_name", [$sid]);
        $staffToday = Database::one("SELECT check_in, check_out FROM hr_attendance WHERE user_id = ? AND work_date = CURDATE()", [$u['id']]);
        $depts = Database::all("SELECT id, name FROM departments WHERE school_id = ? ORDER BY name", [$sid]);
        Router::render('app/erp/hr', [
            'title' => 'HR Management', 'positions' => $positions, 'staff' => $staff, 'leave' => $leave,
            'attendance' => $attendance, 'candidates' => $candidates, 'staffToday' => $staffToday, 'depts' => $depts,
        ]);
    }
}

/* ===================== ERP: Payroll ===================== */
class Ctl_erp_payroll {
    public function run(): void {
        $sid = erp_boot('payroll');
        $u = me();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_run'])) {
                $period = preg_replace('/[^0-9-]/', '', (string)($_POST['period'] ?? ''));
                if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                    Database::run("INSERT INTO payroll_runs (school_id, period, status, created_by) VALUES (?,?,'draft',?) ON DUPLICATE KEY UPDATE status = 'draft'", [$sid, $period, $u['id']]);
                    $run = Database::one("SELECT id FROM payroll_runs WHERE school_id = ? AND period = ?", [$sid, $period]);
                    Database::run(
                        "INSERT IGNORE INTO payroll_entries (run_id, user_id, basic, allowance, deduction, net, bank)
                         SELECT ?, h.user_id, p.salary_scale, ROUND(p.salary_scale*0.15), ROUND(p.salary_scale*0.10),
                                ROUND(p.salary_scale*1.05), 'CBE'
                         FROM hr_staff h JOIN hr_positions p ON p.id = h.position_id
                         WHERE h.school_id = ? AND h.status = 'active'",
                        [$run['id'], $sid]);
                    flash('success', 'Payroll run ' . $period . ' created from active staff.');
                } else {
                    flash('danger', 'Period must be YYYY-MM.');
                }
            }
            if (isset($_POST['save_entry'])) {
                Database::update('payroll_entries', [
                    'basic' => max(0, (float)($_POST['basic'] ?? 0)),
                    'allowance' => max(0, (float)($_POST['allowance'] ?? 0)),
                    'deduction' => max(0, (float)($_POST['deduction'] ?? 0)),
                    'bank' => trim($_POST['bank'] ?? ''),
                ], 'id = ?', [(int)$_POST['entry_id']]);
                Database::run("UPDATE payroll_entries SET net = ROUND(basic + allowance - deduction, 2) WHERE id = ?", [(int)$_POST['entry_id']]);
                flash('success', 'Entry updated.');
            }
            if (isset($_POST['approve_run'])) {
                Database::update('payroll_runs', ['status' => 'approved'], 'id = ?', [(int)$_POST['run_id']]);
                flash('success', 'Payroll run approved.');
            }
            if (isset($_POST['delete_run'])) {
                Database::delete('payroll_entries', 'run_id = ?', [(int)$_POST['run_id']]);
                Database::delete('payroll_runs', 'id = ? AND status = \'draft\'', [(int)$_POST['run_id']]);
                flash('success', 'Draft run deleted.');
            }
            redirect('erp/payroll');
        }
        $runs = Database::all(
            "SELECT pr.*, u.first_name AS by_first, u.last_name AS by_last,
                    (SELECT COUNT(*) FROM payroll_entries pe WHERE pe.run_id = pr.id) AS entries,
                    (SELECT COALESCE(SUM(pe.net),0) FROM payroll_entries pe WHERE pe.run_id = pr.id) AS total
             FROM payroll_runs pr LEFT JOIN users u ON u.id = pr.created_by
             WHERE pr.school_id = ? ORDER BY pr.period DESC", [$sid]);
        $entries = Database::all(
            "SELECT pe.*, CONCAT(u.first_name, ' ', u.last_name) AS name, u.email, p.title AS position
             FROM payroll_entries pe JOIN payroll_runs pr ON pr.id = pe.run_id
             JOIN users u ON u.id = pe.user_id
             LEFT JOIN hr_staff h ON h.user_id = u.id AND h.school_id = pr.school_id
             LEFT JOIN hr_positions p ON p.id = h.position_id
             WHERE pr.school_id = ? ORDER BY pr.period DESC, u.last_name", [$sid]);
        $trend = Database::all(
            "SELECT pr.period, COALESCE(SUM(pe.net),0) AS total FROM payroll_runs pr
             LEFT JOIN payroll_entries pe ON pe.run_id = pr.id
             WHERE pr.school_id = ? GROUP BY pr.period ORDER BY pr.period LIMIT 12", [$sid]);
        Router::render('app/erp/payroll', ['title' => 'Payroll', 'runs' => $runs, 'entries' => $entries, 'trend' => $trend]);
    }
}

/* ===================== ERP: Recruitment ===================== */
class Ctl_erp_recruitment {
    public function run(): void {
        $sid = erp_boot('recruitment');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['add_opening'])) {
                Database::insert('job_openings', [
                    'school_id' => $sid, 'title' => trim($_POST['title'] ?? ''),
                    'department_id' => (int)($_POST['department_id'] ?? 0) ?: null,
                    'job_type' => $_POST['job_type'] ?? 'full',
                    'salary_range' => $_POST['salary_range'] ?? null,
                    'description' => $_POST['description'] ?? null,
                    'deadline' => $_POST['deadline'] ?: null,
                ]);
                flash('success', 'Job opening posted.');
            }
            if (isset($_POST['close_opening'])) {
                Database::update('job_openings', ['status' => 'closed'], 'id = ?', [(int)$_POST['opening_id']]);
                flash('success', 'Opening closed.');
            }
            if (isset($_POST['add_application'])) {
                Database::insert('job_applications', [
                    'opening_id' => (int)$_POST['opening_id'],
                    'candidate_name' => trim($_POST['candidate_name'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''), 'phone' => $_POST['phone'] ?? null,
                    'summary' => $_POST['summary'] ?? null,
                ]);
                flash('success', 'Application logged.');
            }
            if (isset($_POST['stage'])) {
                $next = $_POST['stage'];
                $fields = ['applied' => ['screened'], 'screened' => ['interview'], 'interview' => ['offered'], 'offered' => ['hired']];
                $cur = Database::one("SELECT stage FROM job_applications WHERE id = ?", [(int)$_POST['app_id']]);
                if ($cur && in_array($next, $fields[$cur['stage']] ?? [], true)) {
                    Database::update('job_applications', ['stage' => $next, 'decided_at' => $next === 'hired' ? date('Y-m-d H:i:s') : null], 'id = ?', [(int)$_POST['app_id']]);
                    flash('success', 'Application moved to ' . $next . '.');
                } else {
                    flash('danger', 'Invalid stage transition.');
                }
            }
            if (isset($_POST['reject_app'])) {
                Database::update('job_applications', ['stage' => 'rejected', 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$_POST['app_id']]);
                flash('success', 'Application rejected.');
            }
            redirect('erp/recruitment');
        }
        $openings = Database::all(
            "SELECT o.*, d.name AS dept,
                    (SELECT COUNT(*) FROM job_applications a WHERE a.opening_id = o.id) AS apps
             FROM job_openings o LEFT JOIN departments d ON d.id = o.department_id
             WHERE o.school_id = ? ORDER BY FIELD(o.status,'open','closed'), o.deadline", [$sid]);
        $applications = Database::all(
            "SELECT a.*, o.title AS opening, o.school_id FROM job_applications a JOIN job_openings o ON o.id = a.opening_id
             WHERE o.school_id = ? ORDER BY a.id DESC", [$sid]);
        $depts = Database::all("SELECT id, name FROM departments WHERE school_id = ? ORDER BY name", [$sid]);
        $pipeline = Database::one(
            "SELECT SUM(stage='applied') AS applied, SUM(stage='screened') AS screened, SUM(stage='interview') AS interview,
                    SUM(stage='offered') AS offered, SUM(stage='hired') AS hired, SUM(stage='rejected') AS rejected
             FROM job_applications a JOIN job_openings o ON o.id = a.opening_id WHERE o.school_id = ?", [$sid]);
        Router::render('app/erp/recruitment', [
            'title' => 'Recruitment', 'openings' => $openings, 'applications' => $applications,
            'depts' => $depts, 'pipeline' => $pipeline,
        ]);
    }
}

/* ===================== ERP: Projects & Services ===================== */
class Ctl_erp_projects {
    public function run(): void {
        $sid = erp_boot('projects');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['add_project'])) {
                Database::insert('projects', [
                    'school_id' => $sid, 'name' => trim($_POST['name'] ?? ''),
                    'category' => $_POST['category'] ?? 'project',
                    'description' => $_POST['description'] ?? null,
                    'status' => $_POST['status'] ?? 'planning',
                    'budget' => max(0, (float)($_POST['budget'] ?? 0)), 'spent' => max(0, (float)($_POST['spent'] ?? 0)),
                    'progress' => min(100, max(0, (int)($_POST['progress'] ?? 0))),
                    'start_date' => $_POST['start_date'] ?: null, 'end_date' => $_POST['end_date'] ?: null,
                    'manager_id' => (int)($_POST['manager_id'] ?? 0) ?: null,
                ]);
                flash('success', 'Project created.');
            }
            if (isset($_POST['update_project'])) {
                Database::update('projects', [
                    'status' => $_POST['status'] ?? 'planning',
                    'progress' => min(100, max(0, (int)($_POST['progress'] ?? 0))),
                    'spent' => max(0, (float)($_POST['spent'] ?? 0)),
                ], 'id = ?', [(int)$_POST['project_id']]);
                flash('success', 'Project updated.');
            }
            if (isset($_POST['add_task'])) {
                Database::insert('project_tasks', [
                    'project_id' => (int)$_POST['project_id'], 'title' => trim($_POST['title'] ?? ''),
                    'assignee_id' => (int)($_POST['assignee_id'] ?? 0) ?: null,
                    'due_date' => $_POST['due_date'] ?: null, 'priority' => $_POST['priority'] ?? 'medium',
                ]);
                flash('success', 'Task added.');
            }
            if (isset($_POST['task_status'])) {
                Database::update('project_tasks', ['status' => $_POST['task_status']], 'id = ?', [(int)$_POST['task_id']]);
                flash('success', 'Task updated.');
            }
            redirect('erp/projects');
        }
        $projects = Database::all(
            "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) AS manager
             FROM projects p LEFT JOIN users u ON u.id = p.manager_id
             WHERE p.school_id = ? ORDER BY FIELD(p.status,'active','planning','completed','cancelled'), p.id DESC", [$sid]);
        $tasks = Database::all(
            "SELECT t.*, p.name AS project, CONCAT(u.first_name, ' ', u.last_name) AS assignee
             FROM project_tasks t JOIN projects p ON p.id = t.project_id
             LEFT JOIN users u ON u.id = t.assignee_id
             WHERE p.school_id = ? ORDER BY FIELD(t.status,'todo','in_progress','done'), t.due_date", [$sid]);
        $candidates = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE school_id = ? AND role NOT IN ('student','parent') ORDER BY last_name", [$sid]);
        Router::render('app/erp/projects', ['title' => 'Projects & Services', 'projects' => $projects, 'tasks' => $tasks, 'candidates' => $candidates]);
    }
}

/* ===================== ERP: Document Management ===================== */
class Ctl_erp_documents {
    public function run(): void {
        $sid = erp_boot('documents');
        $u = me();
        if (isset($_GET['download'])) {
            $doc = Database::one("SELECT * FROM documents WHERE id = ? AND school_id = ?", [(int)$_GET['download'], $sid]);
            if (!$doc || !$doc['file_path']) { http_response_code(404); die('Not found'); }
            $path = BASE_PATH . '/' . $doc['file_path'];
            if (!is_file($path)) { http_response_code(404); die('File missing on disk'); }
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($doc['file_name']) . '"');
            readfile($path);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['add_document'])) {
                $title = trim($_POST['title'] ?? '');
                $cat = $_POST['category'] ?? 'other';
                $conf = (int)!empty($_POST['confidential']);
                $fname = null; $fpath = null; $fsize = 0;
                if (!empty($_FILES['file']['name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                    $fname = basename($_FILES['file']['name']);
                    $fpath = 'storage/erp/' . date('Ymd_His') . '_' . $fname;
                    mkdir(dirname(BASE_PATH . '/' . $fpath), 0777, true);
                    move_uploaded_file($_FILES['file']['tmp_name'], BASE_PATH . '/' . $fpath);
                    $fsize = (int)$_FILES['file']['size'];
                }
                $existing = Database::one("SELECT id, version, file_path FROM documents WHERE school_id = ? AND title = ?", [$sid, $title]);
                if ($existing) {
                    $newVersion = (int)$existing['version'] + 1;
                    Database::insert('document_versions', [
                        'document_id' => $existing['id'], 'version' => $newVersion,
                        'file_name' => $existing['file_name'], 'file_path' => $existing['file_path'],
                        'note' => 'Auto-archived before update', 'uploaded_by' => $u['id'],
                    ]);
                    Database::update('documents', [
                        'category' => $cat, 'confidential' => $conf, 'version' => $newVersion,
                        'file_name' => $fname ?: $existing['file_name'], 'file_path' => $fpath ?: $existing['file_path'],
                        'size' => $fsize ?: $existing['size'], 'uploaded_by' => $u['id'],
                    ], 'id = ?', [$existing['id']]);
                    flash('success', 'Document updated to version ' . $newVersion . '. Previous version archived.');
                } else {
                    Database::insert('documents', [
                        'school_id' => $sid, 'title' => $title, 'category' => $cat, 'confidential' => $conf,
                        'file_name' => $fname, 'file_path' => $fpath, 'size' => $fsize, 'uploaded_by' => $u['id'],
                    ]);
                    flash('success', 'Document stored.');
                }
            }
            if (isset($_POST['toggle_confidential'])) {
                Database::run("UPDATE documents SET confidential = 1 - confidential WHERE id = ? AND school_id = ?", [(int)$_POST['doc_id'], $sid]);
                flash('success', 'Confidential flag toggled.');
            }
            if (isset($_POST['delete_document'])) {
                $doc = Database::one("SELECT * FROM documents WHERE id = ? AND school_id = ?", [(int)$_POST['doc_id'], $sid]);
                if ($doc) {
                    Database::delete('document_versions', 'document_id = ?', [$doc['id']]);
                    Database::delete('documents', 'id = ?', [$doc['id']]);
                    if ($doc['file_path'] && is_file(BASE_PATH . '/' . $doc['file_path'])) @unlink(BASE_PATH . '/' . $doc['file_path']);
                    flash('success', 'Document deleted.');
                }
            }
            redirect('erp/documents');
        }
        $docs = Database::all(
            "SELECT d.*, CONCAT(u.first_name, ' ', u.last_name) AS uploader
             FROM documents d LEFT JOIN users u ON u.id = d.uploaded_by
             WHERE d.school_id = ? ORDER BY d.created_at DESC", [$sid]);
        $versions = Database::all(
            "SELECT v.*, d.title, CONCAT(u.first_name, ' ', u.last_name) AS uploader
             FROM document_versions v JOIN documents d ON d.id = v.document_id
             LEFT JOIN users u ON u.id = v.uploaded_by
             WHERE d.school_id = ? ORDER BY v.document_id, v.version DESC LIMIT 40", [$sid]);
        Router::render('app/erp/documents', ['title' => 'Document Management', 'docs' => $docs, 'versions' => $versions]);
    }
}

/* ===================== ERP: Help Desk ===================== */
class Ctl_erp_helpdesk {
    public function run(): void {
        $sid = erp_boot('helpdesk');
        $u = me();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['add_ticket'])) {
                Database::insert('helpdesk_tickets', [
                    'school_id' => $sid, 'subject' => trim($_POST['subject'] ?? ''),
                    'description' => $_POST['description'] ?? null,
                    'category' => $_POST['category'] ?? 'other', 'priority' => $_POST['priority'] ?? 'medium',
                    'requester_id' => $u['id'],
                ]);
                flash('success', 'Ticket #' . Database::insertId() . ' opened.');
            }
            if (isset($_POST['add_comment'])) {
                Database::insert('ticket_comments', ['ticket_id' => (int)$_POST['ticket_id'], 'user_id' => $u['id'], 'body' => trim($_POST['body'] ?? '')]);
                flash('success', 'Comment added.');
            }
            if (isset($_POST['ticket_status'])) {
                $st = $_POST['ticket_status'];
                Database::update('helpdesk_tickets', [
                    'status' => $st,
                    'resolved_at' => in_array($st, ['resolved', 'closed'], true) ? date('Y-m-d H:i:s') : null,
                ], 'id = ?', [(int)$_POST['ticket_id']]);
                flash('success', 'Ticket status → ' . $st . '.');
            }
            if (isset($_POST['assign_ticket'])) {
                Database::update('helpdesk_tickets', ['assignee_id' => (int)($_POST['assignee_id'] ?? 0) ?: null], 'id = ?', [(int)$_POST['ticket_id']]);
                flash('success', 'Ticket assigned.');
            }
            redirect('erp/helpdesk');
        }
        $tickets = Database::all(
            "SELECT t.*, CONCAT(r.first_name, ' ', r.last_name) AS requester, CONCAT(a.first_name, ' ', a.last_name) AS assignee
             FROM helpdesk_tickets t LEFT JOIN users r ON r.id = t.requester_id LEFT JOIN users a ON a.id = t.assignee_id
             WHERE t.school_id = ?
             ORDER BY FIELD(t.status,'open','in_progress','resolved','closed'), FIELD(t.priority,'urgent','high','medium','low'), t.created_at DESC", [$sid]);
        $comments = Database::all(
            "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) AS author
             FROM ticket_comments c JOIN helpdesk_tickets t ON t.id = c.ticket_id
             LEFT JOIN users u ON u.id = c.user_id WHERE t.school_id = ? ORDER BY c.id", [$sid]);
        $assignees = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE school_id = ? AND role NOT IN ('student','parent') ORDER BY last_name", [$sid]);
        Router::render('app/erp/helpdesk', ['title' => 'Help Desk', 'tickets' => $tickets, 'comments' => $comments, 'assignees' => $assignees]);
    }
}

/* ===================== ERP: Fixed Assets ===================== */
class Ctl_erp_assets {
    public function run(): void {
        $sid = erp_boot('assets');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['add_asset'])) {
                Database::insert('assets', [
                    'school_id' => $sid, 'name' => trim($_POST['name'] ?? ''),
                    'category' => $_POST['category'] ?? 'office',
                    'asset_code' => trim($_POST['asset_code'] ?? ''),
                    'purchase_date' => $_POST['purchase_date'] ?: null,
                    'purchase_cost' => max(0, (float)($_POST['purchase_cost'] ?? 0)),
                    'useful_life_years' => max(1, (int)($_POST['useful_life_years'] ?? 5)),
                    'asset_condition' => $_POST['asset_condition'] ?? 'good',
                    'status' => $_POST['status'] ?? 'in_use',
                    'location' => $_POST['location'] ?? null,
                    'assigned_to' => (int)($_POST['assigned_to'] ?? 0) ?: null,
                    'warranty_until' => $_POST['warranty_until'] ?: null,
                ]);
                flash('success', 'Asset registered.');
            }
            if (isset($_POST['update_asset'])) {
                Database::update('assets', [
                    'asset_condition' => $_POST['asset_condition'] ?? 'good',
                    'status' => $_POST['status'] ?? 'in_use',
                    'location' => $_POST['location'] ?? null,
                ], 'id = ?', [(int)$_POST['asset_id']]);
                flash('success', 'Asset updated.');
            }
            if (isset($_POST['add_maintenance'])) {
                Database::insert('asset_maintenance', [
                    'asset_id' => (int)$_POST['asset_id'], 'work_date' => $_POST['work_date'] ?: date('Y-m-d'),
                    'maint_type' => $_POST['maint_type'] ?? 'routine',
                    'cost' => max(0, (float)($_POST['cost'] ?? 0)), 'note' => $_POST['note'] ?? null,
                ]);
                Database::update('assets', ['status' => 'under_maintenance'], 'id = ?', [(int)$_POST['asset_id']]);
                flash('success', 'Maintenance logged; asset marked under maintenance.');
            }
            if (isset($_POST['set_active'])) {
                Database::update('assets', ['status' => 'in_use'], 'id = ?', [(int)$_POST['asset_id']]);
                flash('success', 'Asset returned to service.');
            }
            redirect('erp/assets');
        }
        $assets = Database::all(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS assignee,
                    ROUND(a.purchase_cost / a.useful_life_years) AS annual_dep,
                    GREATEST(0, a.purchase_cost - ROUND(a.purchase_cost / a.useful_life_years *
                        (DATEDIFF(COALESCE(NULLIF(a.purchase_date, '0000-00-00'), CURDATE()), CURDATE()) / -365))) AS book_value
             FROM assets a LEFT JOIN users u ON u.id = a.assigned_to
             WHERE a.school_id = ? ORDER BY FIELD(a.status,'in_use','under_maintenance','stored','retired'), a.category", [$sid]);
        $maint = Database::all(
            "SELECT m.*, a.name AS asset_name FROM asset_maintenance m JOIN assets a ON a.id = m.asset_id
             WHERE a.school_id = ? ORDER BY m.work_date DESC LIMIT 30", [$sid]);
        $assignees = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE school_id = ? AND role NOT IN ('student','parent') ORDER BY last_name", [$sid]);
        Router::render('app/erp/assets', ['title' => 'Fixed Assets', 'assets' => $assets, 'maint' => $maint, 'assignees' => $assignees]);
    }
}

/* ===================== ERP: Fleet Management ===================== */
class Ctl_erp_fleet {
    public function run(): void {
        $sid = erp_boot('fleet');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['add_vehicle'])) {
                Database::insert('fleet_vehicles', [
                    'school_id' => $sid, 'name' => trim($_POST['name'] ?? ''),
                    'plate_number' => trim($_POST['plate_number'] ?? ''),
                    'model' => $_POST['model'] ?? null, 'model_year' => (int)($_POST['model_year'] ?? 0) ?: null,
                    'fuel_type' => $_POST['fuel_type'] ?? 'diesel',
                    'capacity' => max(1, (int)($_POST['capacity'] ?? 12)),
                    'odometer_km' => max(0, (int)($_POST['odometer_km'] ?? 0)),
                    'insurance_until' => $_POST['insurance_until'] ?: null,
                ]);
                flash('success', 'Vehicle registered.');
            }
            if (isset($_POST['vehicle_status'])) {
                Database::update('fleet_vehicles', ['status' => $_POST['vehicle_status']], 'id = ?', [(int)$_POST['vehicle_id']]);
                flash('success', 'Vehicle status updated.');
            }
            if (isset($_POST['add_trip'])) {
                Database::insert('fleet_trips', [
                    'school_id' => $sid, 'vehicle_id' => (int)$_POST['vehicle_id'],
                    'driver_id' => (int)($_POST['driver_id'] ?? 0) ?: null,
                    'trip_date' => $_POST['trip_date'] ?: date('Y-m-d'),
                    'purpose' => $_POST['purpose'] ?? null,
                    'origin' => $_POST['origin'] ?? null, 'destination' => $_POST['destination'] ?? null,
                    'start_km' => max(0, (int)($_POST['start_km'] ?? 0)),
                    'end_km' => max(0, (int)($_POST['end_km'] ?? 0)),
                    'fuel_cost' => max(0, (float)($_POST['fuel_cost'] ?? 0)),
                ]);
                Database::run("UPDATE fleet_vehicles SET odometer_km = GREATEST(odometer_km, ?) WHERE id = ?", [max(0, (int)($_POST['end_km'] ?? 0)), (int)$_POST['vehicle_id']]);
                flash('success', 'Trip logged.');
            }
            if (isset($_POST['add_fuel'])) {
                Database::insert('fleet_fuel', [
                    'vehicle_id' => (int)$_POST['vehicle_id'], 'refuel_date' => $_POST['refuel_date'] ?: date('Y-m-d'),
                    'liters' => max(0, (float)($_POST['liters'] ?? 0)),
                    'cost' => max(0, (float)($_POST['cost'] ?? 0)),
                    'odometer' => max(0, (int)($_POST['odometer'] ?? 0)),
                ]);
                flash('success', 'Fuel log added.');
            }
            redirect('erp/fleet');
        }
        $vehicles = Database::all(
            "SELECT v.*, (SELECT COALESCE(SUM(t.fuel_cost),0) FROM fleet_trips t WHERE t.vehicle_id = v.id) AS fuel_total,
                    (SELECT COALESCE(SUM(t.end_km - t.start_km),0) FROM fleet_trips t WHERE t.vehicle_id = v.id) AS km
             FROM fleet_vehicles v WHERE v.school_id = ? ORDER BY FIELD(v.status,'active','maintenance','out','retired'), v.name", [$sid]);
        $trips = Database::all(
            "SELECT t.*, v.name AS vehicle, v.plate_number, CONCAT(u.first_name, ' ', u.last_name) AS driver
             FROM fleet_trips t JOIN fleet_vehicles v ON v.id = t.vehicle_id LEFT JOIN users u ON u.id = t.driver_id
             WHERE t.school_id = ? ORDER BY t.trip_date DESC LIMIT 40", [$sid]);
        $fuel = Database::all(
            "SELECT f.*, v.name AS vehicle FROM fleet_fuel f JOIN fleet_vehicles v ON v.id = f.vehicle_id
             WHERE v.school_id = ? ORDER BY f.refuel_date DESC LIMIT 30", [$sid]);
        $drivers = Database::all("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE school_id = ? AND role NOT IN ('student','parent') ORDER BY last_name", [$sid]);
        Router::render('app/erp/fleet', ['title' => 'Fleet Management', 'vehicles' => $vehicles, 'trips' => $trips, 'fuel' => $fuel, 'drivers' => $drivers]);
    }
}
