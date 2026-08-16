<?php
/* Homeroom teacher: verify newly registered students within 24 hours */

class Ctl_verify {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $sid = (int)($_POST['student_id'] ?? 0);
            $action = $_POST['action'] ?? '';
            $st = Database::one("SELECT * FROM users WHERE id = ? AND role = 'student' AND school_id = ?", [$sid, $u['school_id']]);
            if ($st) {
                if ($action === 'approve') {
                    Database::update('users', ['status' => 'active', 'verified' => 1, 'verified_by' => $uid, 'verified_at' => date('Y-m-d H:i:s')], 'id = ?', [$sid]);
                    log_activity('user', 'Verified student #' . $sid . ' (' . $st['first_name'] . ' ' . $st['last_name'] . ')', $uid);
                    flash('success', 'Student account verified — they can log in now.');
                    Database::insert('notifications', [
                        'user_id' => $sid, 'type' => 'achievement', 'title' => 'Account verified',
                        'body' => 'Your homeroom teacher approved your account. Welcome to Edunex!', 'link' => 'student/dashboard',
                    ]);
                } elseif ($action === 'reject') {
                    Database::update('users', ['status' => 'suspended'], 'id = ?', [$sid]);
                    log_activity('user', 'Rejected student #' . $sid . ' (' . $st['first_name'] . ' ' . $st['last_name'] . ')', $uid);
                    flash('danger', 'Student account rejected.');
                }
            } else {
                flash('danger', 'Student not found in your school.');
            }
            redirect('teacher/verify');
        }

        // Homeroom groups for this teacher + any pending student in the school
        $myGroups = Database::all("SELECT id FROM student_groups WHERE homeroom_teacher_id = ?", [$uid]);
        $groupIds = array_column($myGroups, 'id');
        if ($groupIds) {
            $pending = Database::all(
                "SELECT us.id, us.first_name, us.last_name, us.email, us.phone, us.student_id, us.created_at,
                        g.name AS group_name, g.grade, g.section,
                        (SELECT COUNT(*) FROM transfer_requests tr WHERE tr.student_id = us.id) AS transfers
                 FROM users us LEFT JOIN student_groups g ON g.id = us.group_id
                 WHERE us.role = 'student' AND us.school_id = ? AND us.status = 'pending' AND us.group_id IN (" . implode(',', $groupIds) . ")
                 ORDER BY us.created_at ASC", [$u['school_id']]);
        } else {
            $pending = [];
        }
        // School-wide pending (not in my homeroom) shown separately
        $other = Database::all(
            "SELECT us.id, us.first_name, us.last_name, us.email, us.student_id, us.created_at,
                    g.name AS group_name, g.grade, g.section, g.homeroom_teacher_id
             FROM users us LEFT JOIN student_groups g ON g.id = us.group_id
             WHERE us.role = 'student' AND us.school_id = ? AND us.status = 'pending' AND (us.group_id IS NULL OR g.homeroom_teacher_id IS NULL OR g.homeroom_teacher_id != ?)
             ORDER BY us.created_at ASC", [$u['school_id'], $uid]);

        $overdue = Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student' AND status = 'pending' AND created_at < NOW() - INTERVAL 24 HOUR AND school_id = ?", [$u['school_id']], 0);

        Router::render('app/teacher/verify', [
            'title' => 'Verify Students', 'pending' => $pending, 'other' => $other, 'overdue' => $overdue,
        ]);
    }
}
