<?php
/** POST a review/comment on an assignment submission (teacher review ↔ student reply). */

class Ctl_assignments_review_post {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST only'); }
        csrf_verify();
        $subId = (int)($_POST['sub'] ?? 0);
        $message = trim((string)($_POST['message'] ?? ''));
        if ($subId <= 0 || $message === '') { http_response_code(400); exit('Missing sub or message'); }
        if (mb_strlen($message) > 2000) { http_response_code(400); exit('Message too long (max 2000 chars)'); }

        $sub = Database::one(
            "SELECT s.*, a.teacher_id, a.title AS assign_title, c.title AS course_title
             FROM assignment_submissions s JOIN assignments a ON a.id = s.assignment_id
             JOIN courses c ON c.id = a.course_id WHERE s.id = ?", [$subId]);
        if (!$sub) { http_response_code(404); exit('Submission not found'); }

        $role = '';
        if ((int)$sub['teacher_id'] === $uid) {
            $role = 'teacher';
        } elseif ((int)$sub['student_id'] === $uid) {
            $role = 'student';
        } else {
            http_response_code(403); exit('Not allowed');
        }

        Database::insert('assignment_reviews', [
            'submission_id' => $subId, 'user_id' => $uid, 'role' => $role, 'message' => $message,
        ]);

        // ping the other side
        if ($role === 'teacher') {
            notify((int)$sub['student_id'], 'assignment', 'Teacher review: ' . $sub['assign_title'],
                mb_substr($message, 0, 120), 'assignments/view&id=' . $sub['assignment_id']);
        } else {
            notify((int)$sub['teacher_id'], 'assignment', 'Student reply: ' . $sub['assign_title'],
                mb_substr($message, 0, 120), 'teacher/assignment&id=' . $sub['assignment_id']);
        }

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'role' => $role]);
        exit;
    }
}
