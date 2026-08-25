<?php
require_once INC_PATH . '/Spreadsheet.php';

/**
 * Bulk user import (Excel/CSV).
 * - teacher:  imports PARENTS (optionally linked to a student)
 * - director: imports TEACHERS or STUDENTS for their school
 * - admin:    imports DIRECTORS, TEACHERS or STUDENTS (any school)
 * First row must be headers; unknown columns are ignored.
 */

class Ctl_import_common {
    public static function handle(string $role, ?int $schoolId): array {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return ['', []];
        csrf_verify();
        if (empty($_FILES['file'])) return ['No file uploaded.', []];
        [$rows, $err] = Spreadsheet::parseFile($_FILES['file']);
        if ($err) return [$err, []];
        if (count($rows) < 2) return ['The file has no data rows (header + at least 1 row).', []];
        $headers = array_shift($rows);
        $idx = Spreadsheet::headerIndex($headers);
        $required = ['first_name', 'last_name', 'email'];
        foreach ($required as $k) {
            if (!isset($idx[$k])) return ['Missing required column "' . $k . '" in the header row.', []];
        }

        $created = []; $errors = [];
        $passAll = trim($_POST['password'] ?? '');
        $groupByName = [];
        if (($idx['class'] ?? false) !== false || ($idx['group'] ?? false) !== false) {
            foreach (Database::all("SELECT id, name FROM student_groups WHERE school_id = ?", [$schoolId ?: -1]) as $g) {
                $groupByName[mb_strtolower(trim($g['name']))] = $g['id'];
            }
        }
        $deptByName = [];
        foreach (Database::all("SELECT id, name FROM departments WHERE school_id = ?", [$schoolId ?: -1]) as $d) {
            $deptByName[mb_strtolower(trim($d['name']))] = $d['id'];
        }

        Database::transaction(function () use ($rows, $idx, $role, $schoolId, $passAll, $groupByName, $deptByName, &$created, &$errors) {
            foreach ($rows as $i => $row) {
                $line = $i + 2;
                $get = fn(string $key) => isset($idx[$key]) ? trim((string)($row[$idx[$key]] ?? '')) : '';
                $first = $get('first_name'); $last = $get('last_name');
                $email = mb_strtolower($get('email'));
                $phone = $get('phone');
                if ($first === '' || $last === '' || $email === '') { $errors[] = "Line $line: name and email required."; continue; }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Line $line: '$email' is not a valid email."; continue; }
                if (Database::one("SELECT id FROM users WHERE email = ?", [$email])) { $errors[] = "Line $line: email '$email' already exists."; continue; }
                $pass = $passAll !== '' ? $passAll : random_password();
                $data = [
                    'school_id' => $schoolId, 'role' => $role,
                    'first_name' => $first, 'last_name' => $last,
                    'email' => $email, 'phone' => $phone,
                    'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
                    'status' => 'active', 'verified' => 1,
                ];
                if ($role === 'student') {
                    $gname = mb_strtolower($get('class') ?: $get('group'));
                    if ($gname !== '') {
                        if (!isset($groupByName[$gname])) { $errors[] = "Line $line: class '$gname' not found."; continue; }
                        $data['group_id'] = $groupByName[$gname];
                    }
                    $data['student_id'] = generate_student_id((int)$schoolId);
                }
                if ($role === 'teacher') {
                    $dname = mb_strtolower($get('department'));
                    if ($dname !== '' && isset($deptByName[$dname])) $data['department_id'] = $deptByName[$dname];
                }
                $newId = Database::insert('users', $data);
                $created[] = ['id' => $newId, 'name' => "$first $last", 'email' => $email, 'password' => $pass];
            }
        });
        return ['', ['created' => $created, 'errors' => $errors]];
    }
}

/* ============ TEACHER: import parents ============ */
class Ctl_import {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $sid = (int)$u['school_id'];
        $result = null; $msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$msg, $result] = Ctl_import_common::handle('parent', $sid);
            if ($result && $result['created']) {
                log_activity('user', 'Imported ' . count($result['created']) . ' parent accounts', (int)$u['id']);
            }
        }
        // student lookup for the link step
        $students = [];
        if (isset($_GET['student'])) {
            $students = Database::all(
                "SELECT us.id, CONCAT(us.first_name,' ',us.last_name) AS name, us.student_id FROM users us
                 WHERE us.school_id = ? AND us.role = 'student' AND (us.first_name LIKE ? OR us.last_name LIKE ? OR us.student_id LIKE ?)
                 LIMIT 20", [$sid, "%" . $_GET['student'] . "%", "%" . $_GET['student'] . "%", "%" . $_GET['student'] . "%"]);
        }
        Router::render('app/teacher/import', ['title' => 'Import Parents', 'result' => $result, 'msg' => $msg, 'students' => $students]);
    }
}

/* ============ DIRECTOR: import teachers/students ============ */
class Ctl_director_import {
    public function run(): void {
        $u = require_role('principal');
        $sid = (int)$u['school_id'];
        $result = null; $msg = '';
        $target = $_GET['type'] ?? 'teacher';
        if (!in_array($target, ['teacher', 'student'], true)) $target = 'teacher';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $target = ($_POST['type'] ?? 'teacher') === 'student' ? 'student' : 'teacher';
            [$msg, $result] = Ctl_import_common::handle($target, $sid);
            if ($result && $result['created']) {
                log_activity('user', 'Director imported ' . count($result['created']) . " $target accounts", (int)$u['id']);
            }
        }
        Router::render('app/director/import', ['title' => 'Bulk Import', 'result' => $result, 'msg' => $msg, 'target' => $target]);
    }
}

/* ============ ADMIN (super admin): import directors/teachers/students ============ */
class Ctl_admin_import {
    public function run(): void {
        $u = require_role('ministry');
        $result = null; $msg = '';
        $target = $_GET['type'] ?? 'principal';
        if (!in_array($target, ['principal', 'teacher', 'student'], true)) $target = 'principal';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $target = in_array($_POST['type'] ?? '', ['principal', 'teacher', 'student'], true) ? $_POST['type'] : 'principal';
            $schoolId = (int)($_POST['school_id'] ?? 0);
            if ($target !== 'principal' && !$schoolId) { $msg = 'Select the school for these accounts.'; }
            else {
                $sid = $target === 'principal' ? null : $schoolId;
                [$msg, $result] = Ctl_import_common::handle($target, $sid);
                if ($result && $result['created']) {
                    log_activity('user', 'Super admin imported ' . count($result['created']) . " $target accounts", (int)$u['id']);
                }
            }
        }
        $schools = Database::all("SELECT id, name FROM schools ORDER BY name");
        Router::render('app/admin/import', ['title' => 'Bulk Import', 'result' => $result, 'msg' => $msg, 'target' => $target, 'schools' => $schools]);
    }
}
