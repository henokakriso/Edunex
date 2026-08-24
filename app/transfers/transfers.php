<?php
/**
 * School transfers: student requests, referral codes, admin redeem
 */

class Ctl_index {
    public function run(): void {
        $u = require_login();
        if (($u['role'] ?? '') === 'student') require_student_feature('transfers');
        $uid = (int)$u['id'];
        if ($u['role'] === 'parent') {
            $kids = Database::all("SELECT id FROM users WHERE parent_id = ?", [$uid]);
            $uid = (int)($kids[0]['id'] ?? 0);
            if (!$uid) { flash('info', 'No linked student.'); redirect('parent/dashboard'); }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['cancel_transfer'])) {
                $rid = (int)($_POST['cancel_transfer']);
                $r = Database::one("SELECT * FROM transfer_requests WHERE id = ? AND student_id = ? AND status = 'pending'", [$rid, $uid]);
                if ($r) {
                    Database::update('transfer_requests', ['status' => 'cancelled', 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$rid]);
                    flash('success', 'Transfer request cancelled.');
                    log_activity('transfer', "Student cancelled transfer request #$rid", (int)$u['id']);
                } else {
                    flash('danger', 'Transfer request not found or already decided.');
                }
                redirect('transfers');
            }
        }
        $requests = Database::all(
            "SELECT t.*, s.name AS to_school, fs.name AS from_school
             FROM transfer_requests t JOIN schools s ON s.id = t.to_school_id JOIN schools fs ON fs.id = t.from_school_id
             WHERE t.student_id = ? ORDER BY t.created_at DESC", [$uid]);
        $student = Database::one("SELECT * FROM users WHERE id = ?", [$uid]);
        Router::render('app/transfers/index', ['title' => 'Transfers', 'requests' => $requests, 'student' => $student]);
    }
}

class Ctl_new {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($u['role'] === 'parent') {
            $kids = Database::all("SELECT id FROM users WHERE parent_id = ?", [$uid]);
            $uid = (int)($kids[0]['id'] ?? 0);
            if (!$uid) { flash('info', 'No linked student.'); redirect('parent/dashboard'); }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $toId = (int)($_POST['to_school'] ?? 0);
            $reason = trim($_POST['reason'] ?? '');
            $code = trim($_POST['referral_code'] ?? '');
            $school = $toId ? Database::one("SELECT * FROM schools WHERE id = ?", [$toId]) : null;
            if (!$school) { flash('danger', 'Please pick a target school.'); redirect('transfers/new'); }
            $ref = null;
            if ($code !== '') {
                $ref = Database::one("SELECT * FROM transfer_codes WHERE code = ? AND used = 0 AND (expires_at IS NULL OR expires_at > NOW())", [$code]);
                if (!$ref) { flash('danger', 'Invalid or expired referral code.'); redirect('transfers/new'); }
            }
            Database::insert('transfer_requests', [
                'student_id' => $uid, 'from_school_id' => $u['school_id'], 'to_school_id' => $toId,
                'referral_code' => $code, 'reason' => $reason, 'status' => 'pending',
            ]);
            $rid = Database::insertId();
            if ($ref) {
                Database::update('transfer_codes', ['used' => 1, 'student_id' => $uid], 'id = ?', [$ref['id']]);
                Database::query("UPDATE transfer_requests SET status = 'approved' WHERE id = ?", [$rid]);
                // move student immediately
                education_leave($uid, (int)$u['school_id'], 'transferred_out');
                Database::update('users', ['school_id' => $toId], 'id = ?', [$uid]);
                ensure_national_id($uid);
                education_enter($uid, $toId, 'transferred_in');
                notify($uid, 'system', 'Transfer approved', 'Your referral code was valid — welcome to ' . $school['name'] . '!', 'dashboard');
                flash('success', 'Transfer approved instantly via referral code!');
                redirect('transfers');
            }
            $admins = Database::all("SELECT id FROM users WHERE role = 'sysadmin'");
            foreach ($admins as $a) notify((int)$a['id'], 'system', 'New transfer request', 'Student ' . ($u['student_id'] ?? '') . ' wants to transfer to ' . $school['name'], 'admin/transfers');
            flash('success', 'Transfer request submitted. An administrator will review it.');
            redirect('transfers');
        }
        $schools = Database::all(
            "SELECT * FROM schools WHERE id != ? AND education_level = (SELECT education_level FROM schools WHERE id = ?) AND status = 'active' ORDER BY name",
            [$u['school_id'], $u['school_id']]);
        Router::render('app/transfers/new', ['title' => 'New Transfer', 'schools' => $schools]);
    }
}

class Ctl_redeem {
    public function run(): void {
        $u = require_role('sysadmin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_code'])) {
                $code = strtoupper('TRF-' . implode('-', array_map(fn() => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4), [1, 1])));
                Database::insert('transfer_codes', [
                    'code' => $code, 'school_id' => $u['school_id'],
                    'purpose' => 'referral',
                    'expires_at' => ($_POST['expires'] ?? '') ?: null,
                ]);
                flash('success', 'Referral code created: ' . $code);
                redirect('transfers/redeem');
            }
            if (isset($_POST['approve'])) {
                $rid = (int)($_POST['approve']);
                $r = Database::one("SELECT * FROM transfer_requests WHERE id = ?", [$rid]);
                if ($r) {
                    $fromSchool = (int)Database::scalar("SELECT school_id FROM users WHERE id = ?", [(int)$r['student_id']], 0);
                    if ($fromSchool > 0) education_leave((int)$r['student_id'], $fromSchool, 'transferred_out');
                    Database::update('users', ['school_id' => (int)$r['to_school_id']], 'id = ?', [$r['student_id']]);
                    ensure_national_id((int)$r['student_id']);
                    education_enter((int)$r['student_id'], (int)$r['to_school_id'], 'transferred_in');
                    Database::update('transfer_requests', ['status' => 'approved', 'approved_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$rid]);
                    notify((int)$r['student_id'], 'system', 'Transfer approved', 'Welcome to your new school!', 'dashboard');
                    flash('success', 'Transfer approved; student moved.');
                }
                redirect('transfers/redeem');
            }
            if (isset($_POST['reject'])) {
                $rid = (int)($_POST['reject']);
                Database::update('transfer_requests', ['status' => 'rejected', 'approved_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$rid]);
                $r = Database::one("SELECT * FROM transfer_requests WHERE id = ?", [$rid]);
                if ($r) notify((int)$r['student_id'], 'system', 'Transfer rejected', 'Your transfer request was declined.', 'transfers');
                flash('success', 'Transfer rejected.');
                redirect('transfers/redeem');
            }
        }
        $pending = Database::all(
            "SELECT t.*, s.name AS to_school, fs.name AS from_school, st.first_name, st.last_name, st.student_id
             FROM transfer_requests t JOIN schools s ON s.id = t.to_school_id JOIN schools fs ON fs.id = t.from_school_id
             JOIN users st ON st.id = t.student_id WHERE t.status = 'pending' ORDER BY t.created_at DESC", []);
        $codes = Database::all(
            "SELECT c.*, s.name AS school_name, CONCAT(st.first_name, ' ', st.last_name) AS used_by
             FROM transfer_codes c JOIN schools s ON s.id = c.school_id LEFT JOIN users st ON st.id = c.student_id
             ORDER BY c.created_at DESC LIMIT 50", []);
        Router::render('app/transfers/redeem', ['title' => 'Transfers Admin', 'pending' => $pending, 'codes' => $codes]);
    }
}
