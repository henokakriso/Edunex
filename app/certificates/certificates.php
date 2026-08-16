<?php
/**
 * Certificates: list, view (printable), public verify
 */

class Ctl_index {
    public function run(): void {
        $u = require_login();
        if (in_array($u['role'], ['student'], true) && !module_active((int)$u['school_id'], 'certificate')) { http_response_code(403); die('The Certificate module is not installed for your school.'); }
        $uid = (int)$u['id'];
        if ($u['role'] === 'parent') {
            $uid = (int)($_GET['student'] ?? 0);
            if (!$uid) { $k = Database::all("SELECT id FROM users WHERE parent_id = ?", [$u['id']]); $uid = (int)($k[0]['id'] ?? 0); }
            if (!$uid) { flash('info', 'No linked student.'); redirect('parent/dashboard'); }
        }
        $certs = Database::all(
            "SELECT c.*, co.title AS course_title, co.code AS course_code, u.first_name, u.last_name, u.student_id,
                    (SELECT ROUND(AVG(t.score/t.total_points*100)) FROM exam_attempts t WHERE t.student_id = c.student_id AND t.status = 'graded' AND t.total_points > 0) AS avg_score
             FROM certificates c JOIN courses co ON co.id = c.course_id JOIN users u ON u.id = c.student_id
             WHERE c.student_id = ? ORDER BY c.issued_at DESC", [$uid]);
        Router::render('app/certificates/index', ['title' => 'Certificates', 'certs' => $certs, 'isParent' => $u['role'] === 'parent']);
    }
}

class Ctl_view {
    public function run(): void {
        $code = $_GET['code'] ?? '';
        if (!$code) { flash('danger', 'No certificate code.'); redirect('certificates'); }
        $cert = Database::one(
            "SELECT c.*, co.title AS course_title, co.code AS course_code, co.description, s.name AS school_name,
                    u.first_name, u.last_name, u.student_id, u.school_id
             FROM certificates c
             JOIN courses co ON co.id = c.course_id JOIN users u ON u.id = c.student_id
             JOIN schools s ON s.id = u.school_id
             WHERE c.cert_code = ?", [$code]);
        if (!$cert) { flash('danger', 'Certificate not found.'); redirect('certificates'); }
        Router::render('app/certificates/view', ['title' => 'Certificate', 'cert' => $cert]);
    }
}

class Ctl_verify {
    public function run(): void {
        $result = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim($_POST['code'] ?? '');
            $result = Database::one(
                "SELECT c.*, co.title AS course_title, u.first_name, u.last_name, s.name AS school_name, u.student_id
                 FROM certificates c JOIN courses co ON co.id = c.course_id JOIN users u ON u.id = c.student_id
                 JOIN schools s ON s.id = u.school_id WHERE c.cert_code = ?", [$code]);
        }
        Router::render('app/certificates/verify', ['title' => 'Verify Certificate', 'result' => $result]);
    }
}
