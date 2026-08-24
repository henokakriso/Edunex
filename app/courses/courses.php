<?php
class Ctl_student_courses {
    public function run(): void {
        $u = require_role('student');
        $uid = (int)$u['id'];
        $df = demo_filter('ce');
        $courses = Database::all(
            "SELECT c.*, ce.progress, ce.completed, ce.enrolled_at,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS total_lessons,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id AND l.id IN (SELECT lesson_id FROM lesson_progress WHERE user_id = ? AND completed = 1)) AS done_lessons,
                    u.first_name AS tfirst, u.last_name AS tlast
             FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id JOIN users u ON u.id = c.teacher_id
             WHERE ce.user_id = ? $df ORDER BY ce.enrolled_at DESC", [$uid, $uid]);
        Router::render('app/student/courses', ['title' => 'My Courses', 'courses' => $courses]);
    }
}

class Ctl_courses_browse {
    public function run(): void {
        $u = require_login();
        $role = $u['role'];
        $where = "c.status = 'published'";
        $params = [];
        // Directors manage and see ALL their school's courses (any status)
        if ($role === 'director') {
            $where = "c.school_id = ?";
            $params[] = (int)$u['school_id'];
        }
        // Students only see courses matching their group's grade/level.
        if ($u['role'] === 'student') {
            $grade = (string)Database::scalar(
                "SELECT g.grade FROM users u LEFT JOIN student_groups g ON g.id = u.group_id WHERE u.id = ?", [$u['id']], '');
            $grade = trim($grade);
            if ($grade !== '') {
                $norm = strtolower(preg_replace('/[^a-z0-9]/i', '', $grade));
                $where .= " AND c.level <> '' AND (LOWER(c.level) = LOWER(?) OR LOWER(REPLACE(REPLACE(LOWER(c.level),' ',''),'grade','')) = LOWER(?))";
                $params[] = $grade;
                $params[] = $norm;
            } else {
                $where .= " AND c.level = ''"; // ungraded students only see unleveled courses
            }
        }
        // Director CRUD handlers
        if ($role === 'director' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $sid = (int)$u['school_id'];
            $teacherIds = Database::all("SELECT id FROM users WHERE school_id = ? AND role = 'teacher'", [$sid]);
            $validT = array_map('intval', array_column($teacherIds, 'id'));
            $subjectIds = Database::all("SELECT id FROM subjects WHERE school_id = ? AND status = 'active'", [$sid]);
            $validS = array_map('intval', array_column($subjectIds, 'id'));
            if (isset($_POST['create_course'])) {
                $data = [
                    'school_id' => $sid,
                    'title' => trim($_POST['title'] ?? ''),
                    'code' => trim($_POST['code'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'level' => trim($_POST['level'] ?? ''),
                    'subject_id' => in_array((int)($_POST['subject_id'] ?? 0), $validS, true) ? (int)$_POST['subject_id'] : null,
                    'teacher_id' => in_array((int)($_POST['teacher_id'] ?? 0), $validT, true) ? (int)$_POST['teacher_id'] : 0,
                    'price' => max(0, (float)($_POST['price'] ?? 0)),
                    'status' => 'draft',
                ];
                if ($data['title'] === '') { flash('danger', 'Title is required.'); redirect('courses'); }
                if (!$data['teacher_id']) { flash('danger', 'Choose a teacher for this course.'); redirect('courses'); }
                $cid = Database::insert('courses', $data);
                log_activity('course', "Director created course '{$data['title']}'", (int)$u['id']);
                flash('success', 'Course created — the teacher can now add modules and lessons.');
                redirect('courses');
            }
            if (isset($_POST['edit_course'])) {
                $cid = (int)($_POST['edit_course'] ?? 0);
                $owned = Database::one("SELECT id FROM courses WHERE id = ? AND school_id = ?", [$cid, $sid]);
                if (!$owned) { flash('danger', 'Access denied.'); redirect('courses'); }
                $data = [
                    'title' => trim($_POST['title'] ?? ''),
                    'code' => trim($_POST['code'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'level' => trim($_POST['level'] ?? ''),
                    'subject_id' => in_array((int)($_POST['subject_id'] ?? 0), $validS, true) ? (int)$_POST['subject_id'] : null,
                    'teacher_id' => in_array((int)($_POST['teacher_id'] ?? 0), $validT, true) ? (int)$_POST['teacher_id'] : 0,
                    'price' => max(0, (float)($_POST['price'] ?? 0)),
                ];
                if ($data['title'] === '') { flash('danger', 'Title is required.'); redirect('courses'); }
                if (!$data['teacher_id']) { flash('danger', 'Choose a teacher for this course.'); redirect('courses'); }
                Database::update('courses', $data, 'id = ?', [$cid]);
                log_activity('course', "Director edited course #$cid", (int)$u['id']);
                flash('success', 'Course updated.');
                redirect('courses');
            }
            if (isset($_POST['delete_course'])) {
                $cid = (int)($_POST['delete_course'] ?? 0);
                $owned = Database::one("SELECT id FROM courses WHERE id = ? AND school_id = ?", [$cid, $sid]);
                if (!$owned) { flash('danger', 'Access denied.'); redirect('courses'); }
                Database::delete('courses', 'id = ?', [$cid]);
                log_activity('course', "Director deleted course #$cid", (int)$u['id']);
                flash('success', 'Course deleted.');
                redirect('courses');
            }
            if (isset($_POST['publish_course'])) {
                $cid = (int)($_POST['publish_course'] ?? 0);
                $owned = Database::one("SELECT id FROM courses WHERE id = ? AND school_id = ?", [$cid, $sid]);
                if ($owned) Database::update('courses', ['status' => 'published'], 'id = ?', [$cid]);
                log_activity('course', "Director published course #$cid", (int)$u['id']);
                flash('success', 'Course published.');
                redirect('courses');
            }
            if (isset($_POST['unpublish_course'])) {
                $cid = (int)($_POST['unpublish_course'] ?? 0);
                $owned = Database::one("SELECT id FROM courses WHERE id = ? AND school_id = ?", [$cid, $sid]);
                if ($owned) Database::update('courses', ['status' => 'draft'], 'id = ?', [$cid]);
                log_activity('course', "Director unpublished course #$cid", (int)$u['id']);
                flash('success', 'Course moved back to drafts.');
                redirect('courses');
            }
        }
        $catalog = Database::all(
            "SELECT c.*, u.first_name AS tfirst, u.last_name AS tlast, s.name AS school_name,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS total_lessons,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
             FROM courses c JOIN users u ON u.id = c.teacher_id JOIN schools s ON s.id = c.school_id
             WHERE $where
             ORDER BY c.created_at DESC", $params);
        $extra = [];
        if ($role === 'director') {
            $extra = [
                'teachers' => Database::all(
                    "SELECT id, first_name, last_name FROM users WHERE school_id = ? AND role = 'teacher' ORDER BY first_name, last_name", [$u['school_id']]),
                'subjects' => Database::all(
                    "SELECT id, name FROM subjects WHERE school_id = ? AND status = 'active' ORDER BY name", [$u['school_id']]),
            ];
        }
        Router::render('app/courses/browse', array_merge(
            ['title' => 'Course Catalog', 'catalog' => $catalog, 'role' => $role], $extra));
    }
}

class Ctl_courses_view {
    public function run(): void {
        $u = require_login();
        $id = (int)($_GET['id'] ?? 0);
        $course = Database::one(
            "SELECT c.*, u.first_name AS tfirst, u.last_name AS tlast FROM courses c JOIN users u ON u.id = c.teacher_id WHERE c.id = ?", [$id]);
        if (!$course) { flash('danger', 'Course not found.'); redirect('courses'); }
        if ($u['role'] === 'director' && (int)$course['school_id'] !== (int)$u['school_id']) {
            flash('danger', 'Access denied.');
            redirect('courses');
        }
        $modules = Database::all("SELECT * FROM course_modules WHERE course_id = ? ORDER BY sort_order", [$id]);
        foreach ($modules as &$m) {
            $m['lessons'] = Database::all("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order", [$m['id']]);
        }
        $enrolled = null;
        $readonly = false;
        if ($u['role'] === 'student') {
            $enrolled = Database::one("SELECT * FROM course_enrollments WHERE course_id = ? AND user_id = ?", [$id, $u['id']]);
        } elseif ($u['role'] === 'director') {
            // Directors preview course detail + contents read-only — no enrollment
            $readonly = true;
        }
        $dfFt = demo_filter('ft');
        $topics = Database::all("SELECT * FROM forum_topics ft WHERE ft.course_id = ? $dfFt ORDER BY ft.pinned DESC, ft.created_at DESC LIMIT 6", [$id]);
        $dfAn = demo_filter('an');
        $anns = Database::all("SELECT * FROM announcements an WHERE an.course_id = ? $dfAn ORDER BY an.created_at DESC LIMIT 5", [$id]);
        $teacherCourses = Database::all("SELECT id, title FROM courses WHERE teacher_id = ? AND id != ?", [$course['teacher_id'], $id]);
        Router::render('app/courses/view', [
            'title' => $course['title'], 'course' => $course, 'modules' => $modules,
            'enrolled' => $enrolled, 'topics' => $topics, 'anns' => $anns, 'teacherCourses' => $teacherCourses,
            'readonly' => $readonly,
        ]);
    }
}

class Ctl_courses_learn {
    public function run(): void {
        $u = require_login();
        $courseId = (int)($_GET['id'] ?? 0);
        $lessonId = (int)($_GET['lesson'] ?? 0);
        $course = Database::one("SELECT * FROM courses WHERE id = ? AND status = 'published'", [$courseId]);
        if (!$course) { flash('danger', 'Course not found.'); redirect('courses'); }
        $readonly = false;
        if ($u['role'] === 'director') {
            // Directors preview lesson contents read-only — never enroll, take or complete
            if ((int)$course['school_id'] !== (int)$u['school_id']) { flash('danger', 'Access denied.'); redirect('courses'); }
            $isEnrolled = true;
            $readonly = true;
        } else {
            $isEnrolled = $u['role'] === 'teacher' ? (int)$u['id'] === (int)$course['teacher_id']
                : (bool)Database::one("SELECT id FROM course_enrollments WHERE course_id = ? AND user_id = ?", [$courseId, $u['id']]);
        }
        if (!$isEnrolled) {
            if ($u['role'] === 'student') {
                Database::insert('course_enrollments', ['course_id' => $courseId, 'user_id' => $u['id']]);
                award_xp((int)$u['id'], 20, 'Enrolled in ' . $course['title']);
                flash('success', 'You are enrolled. Happy learning!');
            } elseif ($u['role'] !== 'sysadmin') {
                flash('info', 'Enroll in this course to start learning.');
                redirect('courses/view&id=' . $courseId);
            }
            redirect('courses/learn&id=' . $courseId);
        }
        $modules = Database::all("SELECT * FROM course_modules WHERE course_id = ? ORDER BY sort_order", [$courseId]);
        $lesson = null;
        if ($lessonId) {
            $lesson = Database::one("SELECT * FROM lessons WHERE id = ? AND course_id = ?", [$lessonId, $courseId]);
        }
        if (!$lesson) {
            // resume where they stopped
            $lesson = Database::one(
                "SELECT l.* FROM lessons l LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = ?
                 WHERE l.course_id = ? AND (lp.completed = 0 OR lp.id IS NULL) ORDER BY l.module_id, l.sort_order LIMIT 1",
                [$u['id'], $courseId]) ?: Database::one("SELECT * FROM lessons WHERE course_id = ? ORDER BY module_id, sort_order LIMIT 1", [$courseId]);
        }
        $allLessons = [];
        foreach ($modules as &$m) {
            $m['lessons'] = Database::all("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order", [$m['id']]);
            foreach ($m['lessons'] as $l) $allLessons[$l['id']] = $l;
        }
        $progress = Database::one("SELECT * FROM lesson_progress WHERE user_id = ? AND lesson_id = ?", [$u['id'], $lesson['id']]);
        $total = count($allLessons);
        $done = (int)Database::scalar(
            "SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND course_id = ? AND completed = 1", [$u['id'], $courseId], 0);
        $bookmarked = (bool)Database::one("SELECT id FROM bookmarks WHERE user_id = ? AND lesson_id = ?", [$u['id'], $lesson['id']]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if ($readonly) { redirect('courses/learn&id=' . $courseId . '&lesson=' . $lesson['id']); }
            $action = $_POST['action'] ?? '';
            if ($action === 'complete') {
                Database::run(
                    "INSERT INTO lesson_progress (user_id, lesson_id, course_id, completed, last_accessed) VALUES (?,?,?,1,NOW())
                     ON DUPLICATE KEY UPDATE completed = 1, last_accessed = NOW()", [$u['id'], $lesson['id'], $courseId]);
                if (!Database::scalar("SELECT 1 FROM lesson_progress WHERE user_id = ? AND lesson_id = ? AND completed = 1", [$u['id'], $lesson['id']])) {
                    award_xp((int)$u['id'], 10, 'Completed lesson: ' . $lesson['title']);
                } else {
                    award_xp((int)$u['id'], 10, 'Completed lesson: ' . $lesson['title']);
                }
                $newDone = $done + 1;
                $newPct = $total ? round($newDone / $total * 100, 1) : 0;
                Database::run("UPDATE course_enrollments SET progress = ?, completed = ?, completed_at = IF(?, NOW(), NULL) WHERE course_id = ? AND user_id = ?",
                    [$newPct, $newPct >= 100 ? 1 : 0, $newPct >= 100 ? 1 : 0, $courseId, $u['id']]);
                if ($newPct >= 100) {
                    $enr = Database::one("SELECT * FROM course_enrollments WHERE course_id = ? AND user_id = ?", [$courseId, $u['id']]);
                    // certificate
                    if (!Database::one("SELECT id FROM certificates WHERE student_id = ? AND course_id = ?", [$u['id'], $courseId])) {
                        $certCode = 'CERT-' . strtoupper(bin2hex(random_bytes(4)));
                        $cid = Database::insert('certificates', [
                            'student_id' => $u['id'], 'course_id' => $courseId,
                            'cert_code' => $certCode, 'qr_hash' => hash('sha256', $certCode . $u['id']),
                            'grade' => 'Passed',
                        ]);
                        Ledger::append((int)$u['school_id'], (int)$u['id'], 'certificate.issued', 'certificate', $cid,
                            ['cert_code' => $certCode, 'course_id' => $courseId, 'course' => $course['title']]);
                        notify((int)$u['id'], 'achievement', 'Course completed!', 'You earned a certificate for ' . $course['title'] . '.', 'certificates');
                        flash('success', 'Course completed! Your certificate is ready.');
                    }
                }
                flash('success', 'Lesson completed +10 XP');
                redirect('courses/learn&id=' . $courseId . '&lesson=' . $lesson['id']);
            }
            if ($action === 'bookmark') {
                if ($bookmarked) {
                    Database::delete('bookmarks', 'user_id = ? AND lesson_id = ?', [$u['id'], $lesson['id']]);
                } else {
                    Database::insert('bookmarks', ['user_id' => $u['id'], 'course_id' => $courseId, 'lesson_id' => $lesson['id']]);
                }
                redirect('courses/learn&id=' . $courseId . '&lesson=' . $lesson['id']);
            }
            if ($action === 'progress') {
                // AJAX video/audio position save
                json_out(['ok' => true]);
            }
        }
        Router::render('app/courses/learn', [
            'title' => $lesson['title'], 'course' => $course, 'modules' => $modules, 'lesson' => $lesson,
            'progress' => $progress, 'total' => $total, 'done' => $done, 'bookmarked' => $bookmarked, 'allLessons' => $allLessons,
            'readonly' => $readonly,
        ]);
    }
}
