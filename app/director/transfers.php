<?php
/**
 * Director school-transfer center:
 *  - issue a transfer code for a leaving student (full data copy)
 *  - approve/reject incoming transfers → copies the entire academic record
 *  - view outgoing transfers + portable record snapshots
 */

class Ctl_transfers {
    public function run(): void {
        $u = require_role('director');
        $sid = (int)$u['school_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();

            if (isset($_POST['issue_code'])) {
                $st = (int)($_POST['student'] ?? 0);
                $student = Database::one("SELECT id, first_name, last_name FROM users WHERE id = ? AND role = 'student' AND school_id = ?", [$st, $sid]);
                if (!$student) {
                    flash('danger', 'Please select a student from your school.');
                    redirect('director/transfers');
                }
                $code = 'TRF-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4)) . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
                Database::insert('transfer_codes', [
                    'code' => $code, 'school_id' => $sid, 'student_id' => $st,
                    'purpose' => 'transfer',
                    'expires_at' => date('Y-m-d H:i:s', time() + 86400 * 90),
                ]);
                log_activity('transfer', "Transfer code $code issued for {$student['first_name']} {$student['last_name']}", $u['id']);
                flash('success', 'Transfer code created for ' . $student['first_name'] . ' ' . $student['last_name'] . ': <b>' . $code . '</b><br>Share it with the student — they register at the new school with this code, and you approve the copy here.');
                redirect('director/transfers');
            }

            if (($rid = (int)($_POST['approve'] ?? 0))) {
                $req = Database::one(
                    "SELECT t.* FROM transfer_requests t WHERE t.id = ? AND t.to_school_id = ? AND t.status IN ('pending','approved')",
                    [$rid, $sid]);
                if ($req) {
                    $res = transfer_copy_record($req, $u['id']);
                    if ($res['ok']) {
                        notify((int)$req['student_id'], 'system', 'School transfer complete!',
                            'Your previous school data (grades, badges, certificates, streak) has been copied to your new account.', 'student/dashboard');
                        flash('success', 'Transfer approved — the student\'s full record was copied to your school.');
                    } else {
                        flash('danger', $res['error']);
                    }
                } else {
                    flash('danger', 'Request not found or not addressed to your school.');
                }
                redirect('director/transfers');
            }

            if (($rid = (int)($_POST['reject'] ?? 0))) {
                $req = Database::one("SELECT id FROM transfer_requests WHERE id = ? AND to_school_id = ? AND status = 'pending'", [$rid, $sid]);
                if ($req) {
                    Database::update('transfer_requests', ['status' => 'rejected', 'approved_by' => $u['id'], 'decided_at' => date('Y-m-d H:i:s')], 'id = ?', [$rid]);
                    flash('success', 'Transfer request rejected.');
                }
                redirect('director/transfers');
            }

            if (($cid = (int)($_POST['revoke_code'] ?? 0))) {
                Database::query("DELETE FROM transfer_codes WHERE id = ? AND school_id = ?", [$cid, $sid]);
                flash('success', 'Transfer code revoked.');
                redirect('director/transfers');
            }
        }

        $students = Database::all(
            "SELECT id, first_name, last_name, student_id, enrollment_status
             FROM users WHERE role = 'student' AND school_id = ? ORDER BY first_name", [$sid]);
        $incoming = Database::all(
            "SELECT t.*, fs.name AS from_school, st.first_name, st.last_name, st.student_id, st.xp, st.level
             FROM transfer_requests t JOIN schools fs ON fs.id = t.from_school_id JOIN users st ON st.id = t.student_id
             WHERE t.to_school_id = ? ORDER BY t.created_at DESC LIMIT 50", [$sid]);
        $outgoing = Database::all(
            "SELECT t.*, ts.name AS to_school, st.first_name, st.last_name, st.student_id,
                    CASE WHEN t.record_snapshot IS NOT NULL THEN 1 ELSE 0 END AS has_snapshot
             FROM transfer_requests t JOIN schools ts ON ts.id = t.to_school_id JOIN users st ON st.id = t.student_id
             WHERE t.from_school_id = ? ORDER BY t.created_at DESC LIMIT 50", [$sid]);
        $codes = Database::all(
            "SELECT c.*, CONCAT(st.first_name, ' ', st.last_name) AS for_student
             FROM transfer_codes c LEFT JOIN users st ON st.id = c.student_id
             WHERE c.school_id = ? AND c.purpose = 'transfer' ORDER BY c.created_at DESC LIMIT 50", [$sid]);

        Router::render('app/director/transfers', [
            'title' => 'Transfers & Data Copy', 'students' => $students,
            'incoming' => $incoming, 'outgoing' => $outgoing, 'codes' => $codes,
        ]);
    }
}
