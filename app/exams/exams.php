<?php
/**
 * Examination system: student take/result + teacher create/manage + grading
 * Question types: mcq, truefalse, essay, fill, coding, matching, order,
 *                 image, audio, video
 */

/* =============== STUDENT: take exam =============== */
class Ctl_take {
    public function run(): void {
        $u = require_role('student');
        $examId = (int)($_GET['e'] ?? 0);
        $exam = Database::one("SELECT e.*, c.title AS course_title FROM exams e JOIN courses c ON c.id = e.course_id WHERE e.id = ? AND e.status = 'published'", [$examId]);
        if (!$exam) { flash('danger', 'Exam not found.'); redirect('student/exams'); }
        $enrolled = Database::one("SELECT id FROM course_enrollments WHERE course_id = ? AND user_id = ?", [$exam['course_id'], $u['id']]);
        if (!$enrolled && !can('exams.manage')) { flash('danger', 'You are not enrolled in this course.'); redirect('student/exams'); }
        $now = time();
        if (strtotime($exam['start_time']) > $now + 120) { flash('warning', 'This exam has not started yet.'); redirect('student/exams'); }
        if (strtotime($exam['end_time']) < $now) { flash('warning', 'This exam has closed.'); redirect('student/exams'); }

        // existing attempt?
        $attempt = Database::one("SELECT * FROM exam_attempts WHERE exam_id = ? AND student_id = ? ORDER BY id DESC LIMIT 1", [$examId, $u['id']]);
        $questions = $this->questions($examId, $exam['shuffle_questions']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['submit_exam'] ?? '') === '1') {
            csrf_verify();
            $this->submit($attempt, $exam, $questions, $u);
            redirect('exams/result&a=' . $attempt['id']);
        }

        if (!$attempt || $attempt['status'] === 'submitted' || $attempt['status'] === 'graded') {
            // new attempt
            $attemptId = Database::insert('exam_attempts', ['exam_id' => $examId, 'student_id' => $u['id'], 'status' => 'in_progress']);
            $attempt = Database::one("SELECT * FROM exam_attempts WHERE id = ?", [$attemptId]);
        }
        // deadline
        $deadline = strtotime($attempt['started_at']) + $exam['duration_min'] * 60;
        $deadline = min($deadline, strtotime($exam['end_time']));

        Router::render('app/exams/take', [
            'title' => $exam['title'], 'exam' => $exam, 'questions' => $questions,
            'attempt' => $attempt, 'deadline' => $deadline,
        ]);
    }

    private function questions(int $examId, bool $shuffle): array {
        $qs = Database::all("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY sort_order, id", [$examId]);
        foreach ($qs as &$q) {
            $q['options'] = $q['options'] ? json_decode($q['options'], true) : [];
            $q['matching'] = $q['type'] === 'matching' && $q['correct_answer'] ? json_decode($q['correct_answer'], true) : [];
            $q['order_items'] = $q['type'] === 'order' && $q['correct_answer'] ? json_decode($q['correct_answer'], true) : [];
        }
        if ($shuffle) { shuffle($qs); $idx = 1; foreach ($qs as &$q) $q['sort_order'] = $idx++; }
        return $qs;
    }

    /** Grade an answer automatically where possible */
    public static function autoGrade(string $type, ?array $options, ?string $correct, string $answer): array {
        $a = mb_strtolower(trim($answer));
        $c = mb_strtolower(trim((string)$correct));
        switch ($type) {
            case 'mcq':
                $sel = '';
                // answer may be "2" (index) or "3" (value)
                foreach ((array)$options as $i => $opt) {
                    if ($a === mb_strtolower(trim((string)$i)) || $a === mb_strtolower(trim((string)$opt))) { $sel = $opt; break; }
                }
                return [$sel !== '' && mb_strtolower(trim($sel)) === $c, null];
            case 'truefalse':
                $b = in_array($a, ['true', 't', '1', 'yes'], true);
                $cb = in_array($c, ['true', 't', '1', 'yes'], true);
                return [$b === $cb, null];
            case 'fill':
                // fuzzy: normalize spaces
                $na = preg_replace('/\s+/', ' ', $a);
                $nc = preg_replace('/\s+/', ' ', $c);
                return [$na === $nc || str_contains($nc, $na) && strlen($na) > 1, null];
            case 'matching':
                try {
                    $ans = json_decode($answer, true) ?: [];
                    $cor = json_decode($correct, true) ?: [];
                    $score = 0;
                    foreach ($cor as $k => $v) {
                        if (isset($ans[$k]) && mb_strtolower(trim($ans[$k])) === mb_strtolower(trim($v))) $score++;
                    }
                    return [count($cor) > 0 && $score === count($cor), $score];
                } catch (Throwable $e) { return [false, 0]; }
            case 'order':
                try {
                    $ans = json_decode($answer, true) ?: [];
                    $cor = json_decode($correct, true) ?: [];
                    return [count($ans) > 0 && $ans === $cor, null];
                } catch (Throwable $e) { return [false, null]; }
            default:
                return [null, null]; // manual grading
        }
    }

    private function submit(?array $attempt, array $exam, array $questions, array $u): void {
        if (!$attempt || $attempt['status'] !== 'in_progress') { flash('warning', 'Attempt already submitted.'); return; }
        $total = 0;
        $earned = 0;
        Database::transaction(function () use ($attempt, $questions, &$total, &$earned, $u, $exam) {
            foreach ($questions as $q) {
                $raw = $_POST['q_' . $q['id']] ?? null;
                $answer = $raw;
                // matching: rebuild map from m_<qid>[] hidden inputs
                if ($q['type'] === 'matching') {
                    $lefts = $_POST['m_left_' . $q['id']] ?? [];
                    $vals = $_POST['m_' . $q['id']] ?? [];
                    $map = [];
                    foreach ($lefts as $i => $l) $map[$l] = $vals[$i] ?? '';
                    $answer = json_encode($map);
                }
                if (is_array($answer)) $answer = json_encode(array_values($answer));
                $total += (float)$q['points'];
                [$correct, $partial] = self::autoGrade($q['type'], $q['options'], $q['correct_answer'], (string)$answer);
                $pe = 0;
                if ($correct === true) $pe = (float)$q['points'];
                elseif ($correct === null && !$exam['auto_grade']) $pe = null; // manual
                elseif ($correct === null && $exam['auto_grade']) $pe = null;
                elseif ($q['type'] === 'matching' && $partial !== null && $partial > 0) {
                    $n = count(json_decode($q['correct_answer'], true) ?: []);
                    $pe = $n ? round((float)$q['points'] * $partial / $n, 2) : 0;
                }
                $earned += (float)($pe ?? 0);
                Database::insert('exam_answers', [
                    'attempt_id' => $attempt['id'], 'question_id' => $q['id'],
                    'answer' => is_array($raw) ? json_encode($raw) : (string)($answer ?? ''),
                    'is_correct' => $correct, 'points_earned' => $pe ?? 0,
                ]);
            }
            $status = $exam['auto_grade'] ? 'graded' : 'submitted';
            Database::update('exam_attempts', [
                'submitted_at' => date('Y-m-d H:i:s'),
                'score' => $status === 'graded' ? $earned : null,
                'total_points' => $total,
                'status' => $status,
            ], 'id = ?', [$attempt['id']]);
            award_xp((int)$u['id'], 15, 'Completed exam: ' . $exam['title']);
            if ($status === 'graded' && $earned >= $total * 0.8) {
                award_xp((int)$u['id'], 50, 'Excellent score on ' . $exam['title']);
            }
            notify((int)$u['id'], 'exam', 'Exam submitted: ' . $exam['title'],
                $status === 'graded' ? 'You scored ' . $earned . '/' . $total . '.' : 'Your exam is awaiting grading.', 'exams/result&a=' . $attempt['id']);
        });
        flash('success', 'Exam submitted successfully.');
    }
}

/* =============== STUDENT: result =============== */
class Ctl_result {
    public function run(): void {
        $u = require_login();
        $attemptId = (int)($_GET['a'] ?? 0);
        $attempt = Database::one("SELECT * FROM exam_attempts WHERE id = ?", [$attemptId]);
        if (!$attempt) { flash('danger', 'Attempt not found.'); redirect('student/exams'); }
        $owner = (int)$attempt['student_id'] === (int)$u['id'] || in_array($u['role'], ['admin', 'teacher'], true);
        if (!$owner) { http_response_code(403); die('Access denied'); }
        $exam = Database::one("SELECT e.*, c.title AS course_title FROM exams e JOIN courses c ON c.id = e.course_id WHERE e.id = ?", [$attempt['exam_id']]);
        $answers = Database::all("SELECT * FROM exam_answers WHERE attempt_id = ? ORDER BY id", [$attemptId]);
        $byQ = [];
        foreach ($answers as $a) $byQ[$a['question_id']] = $a;
        $questions = Database::all("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY sort_order, id", [$exam['id']]);
        foreach ($questions as &$q) $q['options'] = $q['options'] ? json_decode($q['options'], true) : [];
        Router::render('app/exams/result', [
            'title' => 'Result — ' . $exam['title'], 'exam' => $exam, 'attempt' => $attempt,
            'answers' => $byQ, 'questions' => $questions,
        ]);
    }
}

/* =============== TEACHER: exam list =============== */
class Ctl_exams {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];
        $myCourses = self::authorizedCourses($uid);
        $courseFilter = (int)($_GET['course'] ?? 0);
        $sql = "SELECT e.*, c.title AS course_title, c.subject_id, s.name AS subject_name,
                (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id) AS question_count,
                (SELECT COUNT(*) FROM exam_attempts t WHERE t.exam_id = e.id AND t.status = 'submitted') AS pending_count
                FROM exams e JOIN courses c ON c.id = e.course_id LEFT JOIN subjects s ON s.id = c.subject_id
                WHERE e.teacher_id = ?";
        $args = [$u['id']];
        if ($courseFilter) { $sql .= " AND e.course_id = ?"; $args[] = $courseFilter; }
        $sql .= " ORDER BY e.created_at DESC";
        $exams = Database::all($sql, $args);
        foreach ($exams as &$ex) {
            $ex['is_published'] = $ex['status'] === 'published';
            $ex['ends_at'] = $ex['end_time'];
            $ex['duration_min'] = (int)$ex['duration_min'];
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if ($pid = (int)($_POST['publish_exam'] ?? 0)) {
                $ex = Database::one("SELECT * FROM exams WHERE id = ? AND teacher_id = ?", [$pid, $u['id']]);
                if ($ex) {
                    Database::update('exams', ['status' => 'published'], 'id = ?', [$pid]);
                    $enr = Database::all("SELECT user_id FROM course_enrollments WHERE course_id = ?", [$ex['course_id']]);
                    foreach ($enr as $en) {
                        notify((int)$en['user_id'], 'exam', 'New exam: ' . $ex['title'], 'Your teacher published an exam.', 'exams/take&e=' . $pid);
                    }
                    flash('success', 'Exam published — students have been notified.');
                }
            }
            if ($did = (int)($_POST['delete_exam'] ?? 0)) {
                Database::delete('exams', 'id = ? AND teacher_id = ?', [$did, $u['id']]);
                flash('success', 'Exam deleted.');
            }
            redirect('teacher/exams' . ($courseFilter ? '&course=' . $courseFilter : ''));
        }
        Router::render('app/teacher/exams', [
            'title' => 'Exams', 'exams' => $exams, 'myCourses' => $myCourses, 'courseFilter' => $courseFilter,
        ]);
    }

    /** Courses whose subject is in the teacher's authorised subject list. */
    public static function authorizedCourses(int $uid): array {
        return SubjectAuth::courses($uid);
    }
}

/* =============== TEACHER: create/edit exam + questions =============== */
class Ctl_exam {
    public function run(): void {
        $u = require_role('teacher');
        $id = (int)($_GET['id'] ?? 0);
        $myCourses = Ctl_exams::authorizedCourses((int)$u['id']);
        $exam = null;
        $questions = [];
        if ($id) {
            $exam = Database::one("SELECT e.*, c.title AS course_title FROM exams e JOIN courses c ON c.id = e.course_id WHERE e.id = ? AND e.teacher_id = ?", [$id, $u['id']]);
            if (!$exam) { flash('danger', 'Exam not found.'); redirect('teacher/exams'); }
            $questions = Database::all("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY sort_order, id", [$id]);
            foreach ($questions as &$q) {
                $q['options'] = $q['options'] ? json_decode($q['options'], true) : [];
                $q['matching'] = $q['type'] === 'matching' && $q['correct_answer'] ? json_decode($q['correct_answer'], true) : [];
                $q['order_items'] = $q['type'] === 'order' && $q['correct_answer'] ? json_decode($q['correct_answer'], true) : [];
            }
        }
        $editing = null;
        if (($qid = (int)($_GET['edit'] ?? 0)) && $id) {
            $editing = Database::one("SELECT * FROM exam_questions WHERE id = ? AND exam_id = ?", [$qid, $id]);
            if ($editing) {
                $editing['options'] = $editing['options'] ? json_decode($editing['options'], true) : [];
                $editing['matching'] = $editing['type'] === 'matching' && $editing['correct_answer'] ? json_decode($editing['correct_answer'], true) : [];
                $editing['order_items'] = $editing['type'] === 'order' && $editing['correct_answer'] ? json_decode($editing['correct_answer'], true) : [];
            }
        }
        $preview = !empty($_GET['preview']);

        // question validity follows exam validity: adding questions requires a
        // saved exam whose course subject is inside the teacher's authorised list
        $examAuthorized = false;
        $qBlocked = false;
        $qBlockMsg = '';
        if (!$exam) {
            $qBlocked = true;
            $qBlockMsg = 'Save the exam settings first and choose a subject you are authorised to teach — questions can only be added after the exam is saved.';
        } else {
            $authCourse = Database::one(
                "SELECT c.id FROM courses c JOIN teacher_subjects ts ON ts.subject_id = c.subject_id
                 WHERE c.id = ? AND c.teacher_id = ? AND ts.teacher_id = ?",
                [$exam['course_id'], $u['id'], $u['id']]);
            $examAuthorized = (bool)$authCourse;
            if (!$examAuthorized) {
                $qBlocked = true;
                $qBlockMsg = "You can't add questions to this exam — it belongs to a subject outside the ones assigned to you by the director. Change the course to an authorised subject, or contact your director.";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['save_exam'])) {
                $courseId = (int)($_POST['course_id'] ?? 0);
                // existing exams keep their current course even if legacy/unauthorised;
                // only NEW exams or course changes must stay inside authorised subjects
                $keepCourse = $exam && (int)$exam['course_id'] === $courseId;
                $course = $courseId && !$keepCourse ? Database::one(
                    "SELECT c.id, c.subject_id FROM courses c WHERE c.id = ? AND c.teacher_id = ?
                     AND c.subject_id IN (SELECT subject_id FROM teacher_subjects WHERE teacher_id = ?)",
                    [$courseId, $u['id'], $u['id']]) : null;
                if (!$courseId || (!$course && !$keepCourse)) {
                    flash('danger', 'Exams can only be created for courses in the subjects assigned to you by the director.');
                    redirect('teacher/exam&id=' . $id);
                }
                $data = [
                    'course_id' => $courseId,
                    'title' => trim($_POST['title'] ?? ''),
                    'description' => trim($_POST['instructions'] ?? ''),
                    'duration_min' => max(1, (int)($_POST['duration_min'] ?? 60)),
                    'passing_score' => max(0, (float)($_POST['passing_score'] ?? 50)),
                    'auto_grade' => !empty($_POST['auto_grade']) ? 1 : 0,
                    'shuffle_questions' => !empty($_POST['randomize']) ? 1 : 0,
                    'show_result' => !empty($_POST['show_result']) ? 1 : 0,
                    'start_time' => str_replace('T', ' ', $_POST['starts_at'] ?? ''),
                    'end_time' => str_replace('T', ' ', $_POST['ends_at'] ?? ''),
                ];
                if (!$data['start_time']) $data['start_time'] = date('Y-m-d H:i:s');
                if (!$data['end_time']) $data['end_time'] = date('Y-m-d H:i:s', time() + 86400 * 7);
                if (!$data['title'] || !$data['course_id']) { flash('danger', 'Title and course are required.'); redirect('teacher/exam&id=' . $id); }
                if ($exam) {
                    Database::update('exams', $data, 'id = ?', [$id]);
                    flash('success', 'Exam updated.');
                } else {
                    $data['teacher_id'] = $u['id'];
                    $id = (int)Database::insert('exams', $data);
                    flash('success', 'Exam created. Now add questions.');
                }
                redirect('teacher/exam&id=' . $id);
            }
            if (isset($_POST['add_question']) || isset($_POST['update_question'])) {
                if ($qBlocked) {
                    flash('danger', $qBlockMsg);
                    redirect('teacher/exam&id=' . $id);
                }
                $updating = (int)($_POST['qid'] ?? 0);
                $type = $_POST['qtype'] ?? '';
                if ($updating && !$type) {
                    $type = (string)Database::scalar("SELECT type FROM exam_questions WHERE id = ?", [$updating], 'mcq');
                }
                $q = ['type' => $type, 'question' => trim($_POST['question'] ?? ''), 'points' => (float)($_POST['points'] ?? 1)];
                if (!$q['question']) { flash('danger', 'Question text required.'); redirect('teacher/exam&id=' . $id); }
                // parse options from textarea (one per line)
                if (in_array($type, ['mcq', 'truefalse', 'image', 'audio', 'video'], true)) {
                    $opts = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string)($_POST['options'] ?? ''))), fn($o) => $o !== ''));
                    $q['options'] = json_encode($opts);
                    $q['correct_answer'] = trim((string)($_POST['correct_answer'] ?? ''));
                    if ($type === 'truefalse') $q['correct_answer'] = strtolower($q['correct_answer']) === 'false' ? 'false' : 'true';
                } elseif ($type === 'fill') {
                    $q['correct_answer'] = trim((string)($_POST['correct_answer'] ?? ''));
                } elseif ($type === 'matching') {
                    $map = [];
                    foreach (preg_split('/\r?\n/', (string)($_POST['matching'] ?? '')) as $line) {
                        if (str_contains($line, '|')) {
                            [$l, $r] = array_map('trim', explode('|', $line, 2));
                            if ($l !== '' && $r !== '') $map[$l] = $r;
                        }
                    }
                    $q['correct_answer'] = json_encode($map);
                    $q['options'] = json_encode(array_keys($map));
                } elseif ($type === 'order') {
                    $items = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string)($_POST['order_items'] ?? ''))), fn($o) => $o !== ''));
                    $q['correct_answer'] = json_encode($items);
                    $q['options'] = json_encode($items);
                } else { // essay, coding
                    $q['correct_answer'] = trim((string)($_POST['correct_answer'] ?? ''));
                }
                $q['explanation'] = trim((string)($_POST['explanation'] ?? ''));
                if (in_array($type, ['image', 'audio', 'video'], true) && !empty($_FILES['media']['name'])) {
                    [$ok, $path] = upload_file($_FILES['media'], 'uploads/exams', ['jpg','jpeg','png','webp','gif','mp3','wav','mp4','webm','ogg']);
                    if ($ok) $q['media_path'] = $path;
                }
                $where = 'exam_id = ?'; $args = [$id];
                if ($updating) { $where .= ' AND id = ?'; $args[] = $updating; }
                $exists = Database::one("SELECT id FROM exam_questions WHERE $where", $args);
                if ($exists) {
                    $q['sort_order'] = (int)Database::scalar("SELECT sort_order FROM exam_questions WHERE id = ?", [$exists['id']], 0);
                    Database::update('exam_questions', $q, 'id = ?', [$exists['id']]);
                    flash('success', 'Question updated.');
                } else {
                    $q['exam_id'] = $id;
                    $q['sort_order'] = (int)Database::scalar("SELECT COALESCE(MAX(sort_order),0)+1 FROM exam_questions WHERE exam_id = ?", [$id], 1);
                    Database::insert('exam_questions', $q);
                    flash('success', 'Question added.');
                }
                redirect('teacher/exam&id=' . $id);
            }
            if (($dq = (int)($_POST['delete_question'] ?? 0)) && $id) {
                Database::delete('exam_questions', 'id = ? AND exam_id = ?', [$dq, $id]);
                flash('success', 'Question deleted.');
                redirect('teacher/exam&id=' . $id);
            }
        }
        Router::render('app/teacher/exam', [
            'title' => $exam ? 'Edit: ' . $exam['title'] : 'New exam',
            'exam' => $exam, 'questions' => $questions, 'myCourses' => $myCourses,
            'isNew' => !$exam, 'editing' => $editing, 'preview' => $preview,
            'qBlocked' => $qBlocked, 'qBlockMsg' => $qBlockMsg,
        ]);
    }
}

/* =============== TEACHER: grade manual questions =============== */
class Ctl_grade {
    public function run(): void {
        $u = require_role('teacher');
        $examId = (int)($_GET['exam'] ?? 0);
        $exam = Database::one("SELECT e.*, c.title AS course_title FROM exams e JOIN courses c ON c.id = e.course_id WHERE e.id = ? AND e.teacher_id = ?", [$examId, $u['id']]);
        if (!$exam) { flash('danger', 'Exam not found.'); redirect('teacher/exams'); }

        $attempts = Database::all(
            "SELECT t.*, CONCAT(us.first_name, ' ', us.last_name) AS student_name, us.student_id
             FROM exam_attempts t JOIN users us ON us.id = t.student_id
             WHERE t.exam_id = ? AND t.status = 'submitted'
             ORDER BY t.submitted_at", [$examId]);
        $mode = ['manual' => !$exam['auto_grade']];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (($aid = (int)($_POST['auto_grade'] ?? 0))) {
                $this->autoGradeAttempt($aid, $u);
                flash('success', 'Auto-graded with keyword matching.');
                redirect('teacher/grade&exam=' . $examId);
            }
            if ($aid = (int)($_POST['grade_attempt'] ?? 0)) {
                $answers = Database::all("SELECT * FROM exam_answers WHERE attempt_id = ?", [$aid]);
                $total = 0;
                $oldScore = (float)Database::scalar("SELECT COALESCE(score,0) FROM exam_attempts WHERE id = ?", [$aid], 0);
                foreach ($answers as $ans) {
                    $pts = (float)($_POST['pts_' . $ans['question_id']] ?? $ans['points_earned']);
                    $fb = trim((string)($_POST['fb_' . $ans['question_id']] ?? ''));
                    $isC = $ans['is_correct'];
                    if ($pts > 0) $isC = 1;
                    elseif (isset($_POST['pts_' . $ans['question_id']])) $isC = 0;
                    Database::update('exam_answers', ['points_earned' => $pts, 'feedback' => $fb, 'is_correct' => $isC], 'id = ?', [$ans['id']]);
                    $total += $pts;
                }
                $stud = Database::one("SELECT student_id, status FROM exam_attempts WHERE id = ?", [$aid]);
                Database::update('exam_attempts', ['score' => $total, 'status' => 'graded'], 'id = ?', [$aid]);
                if ($stud) {
                    Ledger::append((int)$u['school_id'], (int)$u['id'], 'grade.set',
                        'exam_attempt', $aid,
                        ['exam_id' => $examId, 'exam' => $exam['title'], 'student_id' => (int)$stud['student_id'],
                         'score' => $total, 'graded_by' => $u['first_name'] . ' ' . $u['last_name']]);
                    grade_audit_log((int)$stud['student_id'], (int)$exam['course_id'], 'exam', $examId,
                        (string)$oldScore, (string)$total, 'Manual grading', (int)$u['id']);
                    notify((int)$stud['student_id'], 'exam', 'Exam graded: ' . $exam['title'], 'Your teacher graded your exam. Score: ' . $total . ' points.', 'exams/result&a=' . $aid);
                    award_xp((int)$stud['student_id'], 20, 'Exam graded');
                }
                flash('success', 'Grades saved.');
                redirect('teacher/grade&exam=' . $examId);
            }
            if (isset($_POST['send_results'])) {
                // finalise any still-pending manual attempts with their stored points
                foreach (Database::all("SELECT id FROM exam_attempts WHERE exam_id = ? AND status = 'submitted'", [$examId]) as $p) {
                    $t = (float)Database::scalar("SELECT COALESCE(SUM(points_earned),0) FROM exam_answers WHERE attempt_id = ?", [$p['id']], 0);
                    Database::update('exam_attempts', ['score' => $t, 'status' => 'graded'], 'id = ?', [$p['id']]);
                }
                $rows = Database::all(
                    "SELECT t.id, t.student_id, t.score, t.total_points, us.group_id, CONCAT(us.first_name,' ',us.last_name) AS sname
                     FROM exam_attempts t JOIN users us ON us.id = t.student_id
                     WHERE t.exam_id = ? AND t.status = 'graded' AND t.score IS NOT NULL", [$examId]);
                $sent = 0; $pcts = []; $byHr = [];
                foreach ($rows as $r) {
                    $pct = $r['total_points'] > 0 ? round((float)$r['score'] / (float)$r['total_points'] * 100, 1) : 0;
                    $pcts[] = $pct;
                    $pass = $pct >= (float)$exam['passing_score'];
                    notify((int)$r['student_id'], 'exam', 'Exam result: ' . $exam['title'],
                        $pass ? 'Congratulations — you passed with ' . $pct . '%.' : 'Your score is ' . $pct . '% (passing is ' . rtrim(rtrim((string)$exam['passing_score'], '0'), '.') . '%).',
                        'exams/result&a=' . (int)$r['id']);
                    award_xp((int)$r['student_id'], 10, 'Exam result received');
                    if ($r['group_id']) {
                        $hr = (int)Database::scalar("SELECT homeroom_teacher_id FROM student_groups WHERE id = ?", [$r['group_id']], 0);
                        if ($hr) $byHr[$hr][] = ['name' => (string)$r['sname'], 'pct' => $pct];
                    }
                    $sent++;
                }
                foreach ($byHr as $hrId => $list) {
                    $avg = round(array_sum(array_column($list, 'pct')) / count($list), 1);
                    $names = implode(', ', array_map(fn($x) => $x['name'] . ' (' . $x['pct'] . '%)', array_slice($list, 0, 5)));
                    if (count($list) > 5) $names .= '…';
                    notify($hrId, 'system', 'Exam results: ' . $exam['title'],
                        count($list) . ' student(s) from your class — average ' . $avg . '%. ' . $names,
                        'teacher/homeroom');
                }
                if ($sent) {
                    Database::update('exams', ['results_sent_at' => date('Y-m-d H:i:s')], 'id = ?', [$examId]);
                    Ledger::append((int)$u['school_id'], (int)$u['id'], 'exam.results.sent', 'exam', $examId,
                        ['exam_id' => $examId, 'students' => $sent, 'avg_pct' => round(array_sum($pcts) / count($pcts), 1)]);
                    flash('success', 'Results sent to ' . $sent . ' student(s) and their homeroom teacher(s).');
                } else {
                    flash('info', 'Nothing to send yet — grade the attempts first.');
                }
                redirect('teacher/grade&exam=' . $examId);
            }
        }

        foreach ($attempts as &$att) {
            $answers = Database::all("SELECT * FROM exam_answers WHERE attempt_id = ?", [$att['id']]);
            $byQ = [];
            foreach ($answers as $a) $byQ[$a['question_id']] = $a;
            $att['answers'] = json_encode(array_map(fn($a) => (string)$a['answer'], $byQ));
            $att['gradable'] = [];
            foreach ($byQ as $qid => $a) {
                if ($a['is_correct'] !== null) continue; // already auto-graded
                $q = Database::one("SELECT * FROM exam_questions WHERE id = ?", [$qid]);
                if ($q) {
                    $q['options'] = $q['options'] ? json_decode($q['options'], true) : [];
                    $att['gradable'][] = $q;
                }
            }
        }
        Router::render('app/teacher/grade', [
            'title' => 'Grade — ' . $exam['title'], 'exam' => $exam, 'attempts' => $attempts, 'mode' => $mode,
        ]);
    }

    private function autoGradeAttempt(int $attemptId, array $teacher): void {
        $answers = Database::all("SELECT * FROM exam_answers WHERE attempt_id = ?", [$attemptId]);
        $oldScore = (float)Database::scalar("SELECT COALESCE(score,0) FROM exam_attempts WHERE id = ?", [$attemptId], 0);
        foreach ($answers as $a) {
            if ($a['is_correct'] !== null) continue;
            $q = Database::one("SELECT * FROM exam_questions WHERE id = ?", [$a['question_id']]);
            if (!$q) continue;
            $score = 0;
            $kw = 0;
            $words = preg_split('/\W+/u', mb_strtolower((string)$a['answer']));
            $hay = implode(' ', $words);
            $correct = mb_strtolower((string)$q['correct_answer']);
            if ($correct !== '') {
                $kws = preg_split('/\W+/u', $correct);
                $need = max(1, intval(count($kws) * 0.5));
                foreach ($kws as $w) if ($w !== '' && str_contains($hay, $w)) $kw++;
                if ($kw >= $need) $score = (float)$q['points'];
                elseif ($kw > 0) $score = round((float)$q['points'] * $kw / max(1, count($kws)), 1);
            }
            Database::update('exam_answers', ['points_earned' => $score, 'is_correct' => $score > 0 ? 1 : 0], 'id = ?', [$a['id']]);
        }
        $total = (float)Database::scalar("SELECT COALESCE(SUM(points_earned),0) FROM exam_answers WHERE attempt_id = ?", [$attemptId], 0);
        Database::update('exam_attempts', ['score' => $total, 'status' => 'graded'], 'id = ?', [$attemptId]);
        $att = Database::one("SELECT student_id, exam_id FROM exam_attempts WHERE id = ?", [$attemptId]);
        if ($att) {
            Ledger::append((int)$teacher['school_id'], (int)$teacher['id'], 'grade.autograd',
                'exam_attempt', $attemptId,
                ['exam_id' => (int)$att['exam_id'], 'student_id' => (int)$att['student_id'], 'score' => $total]);
            $exam = Database::one("SELECT course_id FROM exams WHERE id = ?", [(int)$att['exam_id']]);
            if ($exam) {
                grade_audit_log((int)$att['student_id'], (int)$exam['course_id'], 'exam', (int)$att['exam_id'],
                    (string)$oldScore, (string)$total, 'Auto-grading', (int)$teacher['id']);
            }
        }
    }
}
