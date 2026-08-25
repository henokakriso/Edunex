<?php
/**
 * Director — Faculties & academic staff (registrar / dean accounts).
 * University structure: Faculty → Department → Courses.
 */

class Ctl_faculties {
    public function run(): void {
        $u = require_role('principal');
        $sid = (int)$u['school_id'];
        $schoolType = (string)Database::scalar("SELECT type FROM schools WHERE id = ?", [$sid], 'school');
        if (!in_array($schoolType, ['university', 'college'], true)) {
            flash('info', 'Faculties are only available for universities and colleges.');
            redirect('director/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_faculty'])) {
                $name = trim((string)($_POST['name'] ?? ''));
                $code = trim((string)($_POST['code'] ?? ''));
                if ($name === '') {
                    flash('danger', 'Faculty name is required.');
                } else {
                    Database::insert('faculties', ['school_id' => $sid, 'name' => $name, 'code' => $code ?: null]);
                    log_activity('faculty.create', "Director created faculty $name", (int)$u['id']);
                    flash('success', 'Faculty created.');
                }
                redirect('director/faculties');
            }
            if (isset($_POST['assign_dean'])) {
                $fid = (int)($_POST['faculty_id'] ?? 0);
                $did = (int)($_POST['dean_id'] ?? 0) ?: null;
                $fac = Database::one("SELECT id FROM faculties WHERE id = ? AND school_id = ?", [$fid, $sid]);
                if (!$fac) { flash('danger', 'Faculty not found.'); redirect('director/faculties'); }
                if ($did) {
                    $dean = Database::one("SELECT id, role FROM users WHERE id = ? AND school_id = ?", [$did, $sid]);
                    if (!$dean) { flash('danger', 'User not found in your school.'); redirect('director/faculties'); }
                    if ($dean['role'] !== 'dean') {
                        Database::update('users', ['role' => 'dean'], 'id = ?', [$did]);
                    }
                    Database::update('users', ['school_id' => $sid], 'id = ?', [$did]);
                }
                Database::update('faculties', ['dean_id' => $did], 'id = ?', [$fid]);
                log_activity('faculty.dean', "Director assigned dean #" . ($did ?: 0) . " to faculty #$fid", (int)$u['id']);
                flash('success', 'Dean assignment updated.');
                redirect('director/faculties');
            }
            if (isset($_POST['create_staff'])) {
                $role = (string)($_POST['role'] ?? '');
                if (!in_array($role, ['registrar', 'dean', 'vice_dean', 'hod'], true)) { flash('danger', 'Invalid role.'); redirect('director/faculties'); }
                $first = trim((string)($_POST['first_name'] ?? ''));
                $last = trim((string)($_POST['last_name'] ?? ''));
                $email = strtolower(trim((string)($_POST['email'] ?? '')));
                $pass = trim((string)($_POST['password'] ?? '')) ?: random_password();
                if ($first === '' || $last === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    flash('danger', 'Name and a valid email are required.');
                } elseif (Database::one("SELECT id FROM users WHERE email = ?", [$email])) {
                    flash('danger', 'Email already in use.');
                } else {
                    Database::insert('users', [
                        'school_id' => $sid, 'role' => $role,
                        'first_name' => $first, 'last_name' => $last,
                        'email' => $email, 'phone' => trim((string)($_POST['phone'] ?? '')),
                        'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
                        'status' => 'active', 'verified' => 1,
                    ]);
                    $newId = Database::insertId();
                    if (in_array($role, ['dean', 'vice_dean'], true) && (int)($_POST['faculty_id'] ?? 0)) {
                        $fid = (int)$_POST['faculty_id'];
                        if (Database::one("SELECT id FROM faculties WHERE id = ? AND school_id = ?", [$fid, $sid])) {
                            Database::update('faculties', [$role === 'dean' ? 'dean_id' : 'vice_dean_id' => $newId], 'id = ?', [$fid]);
                        }
                    }
                    if ($role === 'hod' && (int)($_POST['department_id'] ?? 0)) {
                        $did = (int)$_POST['department_id'];
                        if (Database::one("SELECT id FROM departments WHERE id = ? AND school_id = ?", [$did, $sid])) {
                            Database::update('users', ['department_id' => $did], 'id = ?', [$newId]);
                        }
                    }
                    log_activity('user', "Director created $role $first $last", (int)$u['id']);
                    flash('success', ucfirst($role) . ' account created. Initial password: ' . $pass);
                }
                redirect('director/faculties');
            }
        }

        $faculties = Database::all(
            "SELECT f.*, CONCAT(du.first_name,' ',du.last_name) AS dean_name, du.email AS dean_email,
                    (SELECT COUNT(*) FROM departments d WHERE d.faculty_id = f.id AND d.status='active') AS departments,
                    (SELECT COUNT(*) FROM users t WHERE t.department_id IN (SELECT id FROM departments WHERE faculty_id = f.id) AND t.role='teacher') AS teachers
             FROM faculties f LEFT JOIN users du ON du.id = f.dean_id
             WHERE f.school_id = ? ORDER BY f.name", [$sid]);
        $deanCandidates = Database::all("SELECT id, first_name, last_name, email FROM users WHERE school_id = ? AND role = 'dean' ORDER BY last_name", [$sid]);
        $unassignedDeans = Database::all(
            "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name, u.email FROM users u
             LEFT JOIN faculties f ON f.dean_id = u.id AND f.school_id = u.school_id
             WHERE u.school_id = ? AND u.role = 'dean' AND f.id IS NULL ORDER BY u.last_name", [$sid]);
        $departments = Database::all("SELECT id, name, faculty_id FROM departments WHERE school_id = ? AND status = 'active' ORDER BY name", [$sid]);
        Router::render('app/director/faculties', [
            'title' => 'Faculties', 'faculties' => $faculties,
            'deanCandidates' => $deanCandidates, 'unassignedDeans' => $unassignedDeans,
            'departments' => $departments,
        ]);
    }
}
