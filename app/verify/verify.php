<?php
/**
 * Public verification endpoints — clearance and transcript verification.
 * No login required.
 */

class Ctl_verify {
    public function run(): void {
        $route = trim($_GET['r'] ?? '', '/');
        match ($route) {
            'clearance' => $this->clearance(),
            'transcript' => $this->transcript(),
            default => $this->not_found(),
        };
    }

    private function clearance(): void {
        $code = trim((string)($_GET['code'] ?? ''));
        if ($code === '') {
            $this->not_found();
            return;
        }
        $request = Database::one(
            "SELECT cr.*, CONCAT(u.first_name, ' ', u.last_name) AS student_name,
                    u.student_id AS sid_no, s.name AS school_name
             FROM clearance_requests cr
             JOIN users u ON u.id = cr.student_id
             JOIN schools s ON s.id = u.school_id
             WHERE cr.tracking_code = ?", [$code]);
        if (!$request) {
            $this->not_found();
            return;
        }
        $items = Database::all(
            "SELECT ci.*, CONCAT(u.first_name, ' ', u.last_name) AS checker_name
             FROM clearance_items ci
             LEFT JOIN users u ON u.id = ci.checker_id
             WHERE ci.request_id = ?
             ORDER BY FIELD(ci.department, 'library','finance','dormitory','lab','academic','disciplinary','department')",
            [$request['id']]);
        require BASE_PATH . '/app/views/public/verify_clearance.php';
    }

    private function transcript(): void {
        $hash = trim((string)($_GET['hash'] ?? ''));
        $requestId = (int)($_GET['id'] ?? 0);
        if ($hash === '' && $requestId === 0) {
            $this->not_found();
            return;
        }
        $request = null;
        if ($requestId) {
            $request = Database::one(
                "SELECT tr.*, CONCAT(u.first_name, ' ', u.last_name) AS student_name,
                        u.student_id AS sid_no, s.name AS school_name
                 FROM transcript_requests tr
                 JOIN users u ON u.id = tr.student_id
                 JOIN schools s ON s.id = u.school_id
                 WHERE tr.id = ? AND tr.status = 'ready'", [$requestId]);
        }
        if (!$request && $hash) {
            $request = Database::one(
                "SELECT tr.*, CONCAT(u.first_name, ' ', u.last_name) AS student_name,
                        u.student_id AS sid_no, s.name AS school_name
                 FROM transcript_requests tr
                 JOIN users u ON u.id = tr.student_id
                 JOIN schools s ON s.id = u.school_id
                 WHERE tr.hash = ? AND tr.status = 'ready'", [$hash]);
        }
        if (!$request) {
            $this->not_found();
            return;
        }
        $records = Database::all(
            "SELECT ar.grade, ar.credit_hours, ar.grade_points, ar.quality_points,
                    c.title, c.code, sem.name AS semester_name
             FROM academic_records ar
             JOIN course_offerings co ON co.id = ar.course_offering_id
             JOIN courses c ON c.id = co.course_id
             JOIN semesters sem ON sem.id = ar.semester_id
             WHERE ar.student_id = ?
             ORDER BY sem.start_date DESC, c.code", [$request['student_id']]);
        $cgpa = compute_cgpa($request['student_id']);
        require BASE_PATH . '/app/views/public/verify_transcript.php';
    }

    private function not_found(): void {
        http_response_code(404);
        require BASE_PATH . '/app/views/errors/404.php';
        exit;
    }
}
