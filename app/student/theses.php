<?php
/**
 * Student theses — submit a thesis proposal (university module).
 */

class Ctl_theses {
    public function run(): void {
        $u = require_role('student');
        require_student_feature('thesis');
        $sid = (int)$u['school_id'];
        if (!module_active($sid, 'thesis')) { http_response_code(403); die('The Thesis module is not installed for your school.'); }
        $uid = (int)$u['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $title = trim((string)($_POST['title'] ?? ''));
            if ($title === '') {
                flash('danger', 'Thesis title is required.');
            } else {
                Database::insert('theses', [
                    'school_id' => $sid,
                    'department_id' => (int)$u['department_id'] ?: null,
                    'student_id' => $uid,
                    'title' => $title,
                    'abstract' => trim((string)($_POST['abstract'] ?? '')),
                    'advisor' => trim((string)($_POST['advisor'] ?? '')),
                    'status' => 'submitted',
                ]);
                log_activity('thesis.submit', 'Student submitted thesis: ' . $title, $uid);
                flash('success', 'Thesis submitted for department review.');
            }
            redirect('student/theses');
        }

        $rows = Database::all(
            "SELECT th.*, d.name AS dept_name FROM theses th
             LEFT JOIN departments d ON d.id = th.department_id
             WHERE th.student_id = ? ORDER BY th.submitted_at DESC LIMIT 20", [$uid]);
        Router::render('app/student/theses', ['title' => 'My Theses', 'rows' => $rows]);
    }
}
