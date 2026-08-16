<?php
/** JSON list of review messages for one submission (polled live by teacher + student). */

class Ctl_assignments_review_list {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $subId = (int)($_GET['sub'] ?? 0);
        if ($subId <= 0) { http_response_code(400); exit('Missing sub'); }

        $sub = Database::one(
            "SELECT s.*, a.teacher_id, us.first_name, us.last_name, CONCAT(us.first_name,' ',us.last_name) AS student_name
             FROM assignment_submissions s JOIN assignments a ON a.id = s.assignment_id
             JOIN users us ON us.id = s.student_id WHERE s.id = ?", [$subId]);
        if (!$sub) { http_response_code(404); exit('Submission not found'); }
        $mine = $uid === (int)$sub['teacher_id'] || $uid === (int)$sub['student_id'];
        if (!$mine) { http_response_code(403); exit('Not allowed'); }

        $msgs = Database::all(
            "SELECT r.*, us.first_name, us.last_name
             FROM assignment_reviews r JOIN users us ON us.id = r.user_id
             WHERE r.submission_id = ? ORDER BY r.id ASC", [$subId]);

        header('Content-Type: application/json');
        echo json_encode([
            'sub' => $subId,
            'status' => $sub['status'],
            'score' => $sub['score'],
            'student' => $sub['student_name'],
            'msgs' => array_map(fn($m) => [
                'id' => (int)$m['id'],
                'role' => $m['role'],
                'name' => $m['first_name'] . ' ' . $m['last_name'],
                'message' => $m['message'],
                'created' => $m['created_at'],
            ], $msgs),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
