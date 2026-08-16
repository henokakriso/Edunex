<?php
/**
 * Assignments: teacher create/grade, student submit/view
 */

class Ctl_teacher_assignments {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];
        $assignments = Database::all(
            "SELECT a.*, c.title AS course_title, s.name AS subject_name,
                    (SELECT COUNT(*) FROM assignment_submissions s2 WHERE s2.assignment_id = a.id) AS subs,
                    (SELECT COUNT(*) FROM assignment_submissions s2 WHERE s2.assignment_id = a.id AND s2.status = 'submitted') AS pending
             FROM assignments a JOIN courses c ON c.id = a.course_id LEFT JOIN subjects s ON s.id = c.subject_id
             WHERE a.teacher_id = ? ORDER BY a.due_date DESC", [$uid]);
        $courses = SubjectAuth::courses((int)$u['id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_assign'])) {
                $courseId = (int)($_POST['course_id'] ?? 0);
                if (!$courseId || !in_array($courseId, array_column($courses, 'id'), true)) {
                    flash('danger', 'Assignments can only be created for courses in the subjects assigned to you by the director.');
                    redirect('teacher/assignments');
                }
                $rubric = Ctl_teacher_assignment::parseRubric($_POST);
                $data = [
                    'course_id' => $courseId, 'title' => trim($_POST['title']),
                    'description' => trim($_POST['description'] ?? ''),
                    'max_score' => max(1, (float)($_POST['max_score'] ?? 100)),
                    'due_date' => str_replace('T', ' ', $_POST['due_date'] ?? ''),
                    'allow_late' => !empty($_POST['allow_late']) ? 1 : 0,
                    'late_penalty' => max(0, (float)($_POST['late_penalty'] ?? 0)),
                    'rubric' => $rubric,
                    'teacher_id' => $uid,
                ];
                if (!$data['due_date']) $data['due_date'] = date('Y-m-d H:i:s', time() + 86400 * 7);
                if ($data['title'] === '') { flash('danger', 'Title required.'); redirect('teacher/assignments'); }
                $aid = Database::insert('assignments', $data);
                $enr = Database::all("SELECT user_id FROM course_enrollments WHERE course_id = ?", [$data['course_id']]);
                foreach ($enr as $en) {
                    notify((int)$en['user_id'], 'assignment', 'New assignment: ' . $data['title'], 'Due ' . date('M j, H:i', strtotime($data['due_date'])), 'assignments/view&id=' . $aid);
                }
                flash('success', 'Assignment created — ' . count($enr) . ' students notified.');
                redirect('teacher/assignments');
            }
            if (isset($_POST['delete_assign'])) {
                $delId = (int)($_POST['assignment_id'] ?? 0);
                Database::delete('assignments', 'id = ? AND teacher_id = ?', [$delId, $uid]);
                log_activity('assignment', "Deleted assignment #$delId", $uid);
                flash('success', 'Assignment deleted.');
                redirect('teacher/assignments');
            }
        }
        Router::render('app/teacher/assignments', [
            'title' => 'Assignments', 'assignments' => $assignments, 'courses' => $courses,
        ]);
    }
}

class Ctl_teacher_assignment {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];
        $courses = SubjectAuth::courses($uid);
        $id = (int)($_GET['id'] ?? 0);
        $assign = Database::one("SELECT a.*, c.title AS course_title FROM assignments a JOIN courses c ON c.id = a.course_id WHERE a.id = ? AND a.teacher_id = ?", [$id, $uid]);
        if (!$assign) { flash('danger', 'Assignment not found.'); redirect('teacher/assignments'); }
        $subs = Database::all(
            "SELECT s.*, us.first_name, us.last_name, us.student_id, us.avatar
             FROM assignment_submissions s JOIN users us ON us.id = s.student_id
             WHERE s.assignment_id = ? ORDER BY s.submitted_at", [$id]);

        $reviews = [];
        if ($subs) {
            $ids = array_map(fn($s) => (int)$s['id'], $subs);
            $qs = implode(',', array_fill(0, count($ids), '?'));
            $msgs = Database::all(
                "SELECT r.*, us.first_name, us.last_name FROM assignment_reviews r JOIN users us ON us.id = r.user_id
                 WHERE r.submission_id IN ($qs) ORDER BY r.id ASC", $ids);
            foreach ($msgs as $m) $reviews[(int)$m['submission_id']][] = $m;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (($sid = (int)($_POST['grade_sub'] ?? 0))) {
                $score = max(0, (float)($_POST['score'] ?? 0));
                $fb = trim((string)($_POST['feedback'] ?? ''));
                Database::update('assignment_submissions', [
                    'score' => $score, 'feedback' => $fb, 'status' => 'graded', 'graded_by' => $uid, 'graded_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$sid]);
                $st = Database::one("SELECT student_id FROM assignment_submissions WHERE id = ?", [$sid]);
                if ($st) {
                    notify((int)$st['student_id'], 'assignment', 'Assignment graded: ' . $assign['title'], 'Score: ' . $score . '/' . $assign['max_score'] . '. ' . $fb, 'assignments/view&id=' . $id);
                    award_xp((int)$st['student_id'], 15, 'Assignment graded');
                }
                flash('success', 'Submission graded.');
                redirect('teacher/assignment&id=' . $id);
            }
            if (isset($_POST['ai_feedback_all'])) {
                foreach ($subs as $s) {
                    $ai = $this->aiFeedback($s['content'] . ($s['file_path'] ? ' (file: ' . $s['file_path'] . ')' : ''), $assign);
                    Database::update('assignment_submissions', ['ai_feedback' => $ai], 'id = ?', [$s['id']]);
                }
                flash('success', 'AI feedback generated for all submissions.');
                redirect('teacher/assignment&id=' . $id);
            }
            if (isset($_POST['update_assign'])) {
                $courseId = (int)($_POST['course_id'] ?? 0);
                if ($courseId && !in_array($courseId, array_column($courses, 'id'), true)) {
                    flash('danger', 'Assignments can only be moved to courses in the subjects assigned to you by the director.');
                    redirect('teacher/assignment&id=' . $id);
                }
                $data = [
                    'course_id' => $courseId ?: $assign['course_id'],
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'max_score' => max(1, (float)($_POST['max_score'] ?? 100)),
                    'due_date' => str_replace('T', ' ', $_POST['due_date'] ?? ''),
                    'allow_late' => !empty($_POST['allow_late']) ? 1 : 0,
                    'late_penalty' => max(0, (float)($_POST['late_penalty'] ?? 0)),
                    'rubric' => self::parseRubric($_POST),
                ];
                if (!$data['due_date']) $data['due_date'] = date('Y-m-d H:i:s', time() + 86400 * 7);
                if ($data['title'] === '') { flash('danger', 'Title required.'); redirect('teacher/assignment&id=' . $id); }
                Database::update('assignments', $data, 'id = ?', [$id]);
                log_activity('assignment', "Updated assignment '{$data['title']}'", $uid);
                flash('success', 'Assignment updated.');
                redirect('teacher/assignment&id=' . $id);
            }
            if (isset($_POST['delete_assign'])) {
                Database::delete('assignments', 'id = ? AND teacher_id = ?', [$id, $uid]);
                log_activity('assignment', "Deleted assignment #$id", $uid);
                flash('success', 'Assignment deleted.');
                redirect('teacher/assignments');
            }
        }

        $assign['rubric'] = $assign['rubric'] ? json_decode($assign['rubric'], true) : [];
        Router::render('app/teacher/assignment', [
            'title' => $assign['title'], 'assign' => $assign, 'subs' => $subs, 'reviews' => $reviews, 'courses' => $courses,
        ]);
    }

    /** Build rubric JSON from the interactive editor fields (r_criterion[]/r_max[]/r_weight[]). */
    public static function parseRubric(array $post): ?string {
        $rubric = [];
        foreach ((array)($post['r_criterion'] ?? []) as $i => $crit) {
            if (trim((string)$crit) !== '') {
                $rubric[] = [
                    'criterion' => trim($crit),
                    'max' => (float)($post['r_max'][$i] ?? 0),
                    'weight' => (float)($post['r_weight'][$i] ?? 0),
                ];
            }
        }
        return $rubric ? json_encode($rubric) : null;
    }

    private function aiFeedback(string $content, array $assign): string {
        $len = mb_strlen($content);
        $words = str_word_count($content);
        $lines = substr_count($content, "\n");
        if ($len < 50) return 'Your submission is quite short. Expand on your ideas and add more detail to cover the topic thoroughly.';
        if ($words < 40) return 'Good start, but your answer needs more depth. Try to explain the concepts with examples.';
        $tips = [];
        if ($lines < 2) $tips[] = 'Break your answer into paragraphs for clarity.';
        if (!preg_match('/[.!?]$/', trim($content))) $tips[] = 'Finish your answer with a clear concluding sentence.';
        $pos = 0; $n = count($tips);
        $perf = ['Excellent work! Your answer is detailed and well structured.', 'Very good! Clear and well organized. Keep it up.', 'Good effort with solid points. Consider adding examples.'];
        $r = $perf[array_rand($perf)] . ($n ? ' Suggestions: ' . implode(' ', $tips) : '');
        return $r;
    }
}

class Ctl_student_assignments {
    public function run(): void {
        $u = require_role('student');
        require_student_feature('assignments');
        $uid = (int)$u['id'];
        $assigns = Database::all(
            "SELECT a.*, c.title AS course_title,
                    s.status AS sub_status, s.score, s.submitted_at AS my_submitted_at, s.id AS sub_id
             FROM assignments a
             JOIN courses c ON c.id = a.course_id
             JOIN course_enrollments ce ON ce.course_id = a.course_id AND ce.user_id = ?
             LEFT JOIN assignment_submissions s ON s.assignment_id = a.id AND s.student_id = ?
             ORDER BY a.due_date", [$uid, $uid]);
        foreach ($assigns as &$a) {
            $a['submitted'] = (bool)$a['sub_id'];
            $a['overdue'] = !$a['submitted'] && strtotime($a['due_date']) < time();
        }
        Router::render('app/student/assignments', ['title' => 'My Assignments', 'assigns' => $assigns]);
    }
}

class Ctl_assignments_view {
    public function run(): void {
        $u = require_login();
        $id = (int)($_GET['id'] ?? 0);
        $assign = Database::one("SELECT a.*, c.title AS course_title, c.teacher_id FROM assignments a JOIN courses c ON c.id = a.course_id WHERE a.id = ?", [$id]);
        if (!$assign) { flash('danger', 'Assignment not found.'); redirect('courses'); }
        $assign['rubric'] = $assign['rubric'] ? json_decode($assign['rubric'], true) : [];
        $sub = null;
        if ($u['role'] === 'student') {
            $sub = Database::one("SELECT * FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?", [$id, $u['id']]);
        }
        $reviews = [];
        if ($sub) {
            $reviews = Database::all(
                "SELECT r.*, us.first_name, us.last_name FROM assignment_reviews r JOIN users us ON us.id = r.user_id
                 WHERE r.submission_id = ? ORDER BY r.id ASC", [$sub['id']]);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $u['role'] === 'student') {
            csrf_verify();
            $enrolled = Database::one("SELECT id FROM course_enrollments WHERE course_id = ? AND user_id = ?", [$assign['course_id'], $u['id']]);
            if (!$enrolled) { flash('danger', 'You must be enrolled to submit.'); redirect('courses/view&id=' . $assign['course_id']); }
            $data = [
                'content' => trim($_POST['content'] ?? ''),
                'submitted_at' => date('Y-m-d H:i:s'),
                'is_late' => strtotime($assign['due_date']) < time() ? 1 : 0,
            ];
            if ($data['content'] === '' && empty($_FILES['file']['name'])) { flash('danger', 'Add content or attach a file.'); redirect('assignments/view&id=' . $id); }
            if (!empty($_FILES['file']['name'])) {
                [$ok, $path] = upload_file($_FILES['file'], 'uploads/assignments', ['pdf','doc','docx','txt','md','ppt','pptx','zip','jpg','png']);
                if ($ok) $data['file_path'] = $path;
            }
            if ($sub) {
                Database::update('assignment_submissions', $data, 'id = ?', [$sub['id']]);
                flash('success', 'Submission updated.');
            } else {
                $data['assignment_id'] = $id; $data['student_id'] = $u['id'];
                Database::insert('assignment_submissions', $data);
                award_xp((int)$u['id'], 10, 'Submitted assignment: ' . $assign['title']);
                notify((int)$assign['teacher_id'], 'assignment', 'New submission: ' . $assign['title'], ($u['first_name'] ?? 'A student') . ' submitted an assignment.', 'teacher/assignment&id=' . $id);
                flash('success', 'Assignment submitted!');
            }
            redirect('assignments/view&id=' . $id);
        }
        $subs = null;
        if ($u['role'] === 'teacher' || $u['role'] === 'sysadmin' || (int)$u['id'] === (int)$assign['teacher_id']) {
            $subs = Database::all(
                "SELECT s.*, us.first_name, us.last_name FROM assignment_submissions s JOIN users us ON us.id = s.student_id WHERE s.assignment_id = ? ORDER BY s.submitted_at", [$id]);
        }
        Router::render('app/assignments/view', [
            'title' => $assign['title'], 'assign' => $assign, 'sub' => $sub, 'subs' => $subs, 'reviews' => $reviews,
        ]);
    }
}
