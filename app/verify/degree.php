<?php
/**
 * Public degree verification — enter an EDG- code to verify a degree.
 */

class Ctl_degree {
    public function run(): void {
        $code = strtoupper(trim((string)($_GET['code'] ?? '')));
        $result = null;
        if ($code !== '') {
            $result = Database::one(
                "SELECT d.degree_code, d.degree_name, d.issued_at, sc.name AS school_name,
                        CONCAT(u.first_name,' ',u.last_name) AS student, u.student_id
                 FROM degrees d
                 JOIN schools sc ON sc.id = d.school_id
                 JOIN users u ON u.id = d.student_id
                 WHERE d.degree_code = ?", [$code]);
        }
        Router::render('public/verify_degree', ['title' => 'Degree Verification', 'result' => $result, 'code' => $code]);
    }
}
