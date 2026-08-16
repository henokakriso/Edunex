<?php
/**
 * Teacher module: dashboard + course manager (modules/lessons/enrollments)
 */

class Ctl_dashboard {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];
        $stats = [
            'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE teacher_id = ?", [$uid], 0),
            'published' => (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE teacher_id = ? AND status = 'published'", [$uid], 0),
            'students' => (int)Database::scalar(
                "SELECT COUNT(DISTINCT ce.user_id) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE c.teacher_id = ?", [$uid], 0),
            'exams' => (int)Database::scalar("SELECT COUNT(*) FROM exams WHERE teacher_id = ?", [$uid], 0),
            'assignments' => (int)Database::scalar("SELECT COUNT(*) FROM assignments WHERE teacher_id = ?", [$uid], 0),
            'pending_grades' => (int)Database::scalar(
                "SELECT COUNT(*) FROM exam_attempts t JOIN exams e ON e.id = t.exam_id WHERE e.teacher_id = ? AND t.status = 'submitted'", [$uid], 0),
            'pending_submissions' => (int)Database::scalar(
                "SELECT COUNT(*) FROM assignment_submissions s JOIN assignments a ON a.id = s.assignment_id WHERE a.teacher_id = ? AND s.status = 'submitted'", [$uid], 0),
            'pending_verify' => (int)Database::scalar(
                "SELECT COUNT(*) FROM users us WHERE us.role='student' AND us.school_id = ? AND us.status = 'pending' AND (us.group_id IS NULL OR us.group_id IN (SELECT id FROM student_groups WHERE homeroom_teacher_id = ?))", [$u['school_id'], $uid], 0),
            'inactive' => (int)Database::scalar(
                "SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id JOIN users us ON us.id = ce.user_id WHERE c.teacher_id = ? AND us.enrollment_status = 'inactive'", [$uid], 0),
            'lessons' => (int)Database::scalar(
                "SELECT COUNT(DISTINCT l.id) FROM lessons l JOIN course_modules m ON m.id = l.module_id JOIN courses c ON c.id = m.course_id WHERE c.teacher_id = ?", [$uid], 0),
        ];
        $courseStats = Database::all(
            "SELECT c.id, c.title, c.status, c.created_at,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students,
                    (SELECT ROUND(AVG(progress),0) FROM course_enrollments ce WHERE ce.course_id = c.id) AS avg_progress,
                    (SELECT COUNT(*) FROM exams e WHERE e.course_id = c.id) AS exams,
                    (SELECT COUNT(*) FROM assignments a WHERE a.course_id = c.id) AS assignments,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lessons
             FROM courses c WHERE c.teacher_id = ? ORDER BY c.created_at DESC", [$uid]);
        // weekly enrollments (last 8 weeks)
        $weeks = [];
        for ($i = 7; $i >= 0; $i--) {
            $start = date('Y-m-d', strtotime("-$i weeks monday"));
            $end = date('Y-m-d', strtotime("-" . ($i - 1) . " weeks monday") - 1);
            $n = (int)Database::scalar(
                "SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id
                 WHERE c.teacher_id = ? AND ce.enrolled_at BETWEEN ? AND ?", [$uid, $start . ' 00:00:00', $end . ' 23:59:59'], 0);
            $weeks[] = ['label' => date('M j', strtotime($start)), 'count' => $n];
        }
        $recent = Database::all(
            "SELECT ce.enrolled_at, u.first_name, u.last_name, u.avatar, c.title AS course_title, c.id AS course_id
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id JOIN courses c ON c.id = ce.course_id
             WHERE c.teacher_id = ? ORDER BY ce.enrolled_at DESC LIMIT 8", [$uid]);
        $activities = Database::all(
            "SELECT * FROM activity_logs WHERE user_id = ? OR ip = ? ORDER BY created_at DESC LIMIT 8", [$uid, $_SERVER['REMOTE_ADDR'] ?? '']);
        $todo = [];
        if ($stats['pending_verify'] > 0) $todo[] = ['icon' => 'check-circle', 'label' => "Verify {$stats['pending_verify']} new student account(s)", 'link' => 'teacher/verify', 'cls' => 'accent'];
        if ($stats['pending_grades'] > 0) $todo[] = ['icon' => 'note', 'label' => "Grade {$stats['pending_grades']} pending exam attempt(s)", 'link' => 'teacher/grade', 'cls' => 'warn'];
        if ($stats['pending_submissions'] > 0) $todo[] = ['icon' => 'file', 'label' => "Review {$stats['pending_submissions']} assignment submission(s)", 'link' => 'teacher/grade', 'cls' => 'warn'];
        $drafts = (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE teacher_id = ? AND status = 'draft'", [$uid], 0);
        if ($drafts > 0) $todo[] = ['icon' => 'rocket', 'label' => "Publish $drafts draft course(s)", 'link' => 'teacher/courses', 'cls' => 'info'];
        if (!$todo) $todo[] = ['icon' => 'spark', 'label' => 'All caught up — nothing pending', 'link' => '', 'cls' => 'ok'];
        Router::render('app/teacher/dashboard', [
            'title' => 'Teacher Dashboard', 'stats' => $stats, 'recent' => $recent,
            'activities' => $activities, 'weeks' => $weeks, 'courseStats' => $courseStats, 'todo' => $todo,
        ]);
    }
}

class Ctl_courses {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_course'])) {
                $sid2 = (int)($_POST['subject_id'] ?? 0);
                $mine = Database::all(
                    "SELECT s.id FROM teacher_subjects ts JOIN subjects s ON s.id = ts.subject_id
                     WHERE ts.teacher_id = ? AND s.status = 'active'", [$uid]);
                $mineIds = array_map('intval', array_column($mine, 'id'));
                if ($sid2 && !in_array($sid2, $mineIds, true)) {
                    flash('danger', 'You can only create courses in subjects assigned to you by the director.');
                    redirect('teacher/courses');
                }
                $data = [
                    'school_id' => (int)$_POST['school_id'], 'title' => trim($_POST['title']),
                    'code' => trim($_POST['code']), 'description' => trim($_POST['description']),
                    'level' => trim($_POST['level']), 'subject_id' => $sid2 ?: null,
                    'credit_hours' => min(20, max(0, (float)($_POST['credit_hours'] ?? 3))),
                    'price' => max(0, (float)($_POST['price'] ?? 0)), 'teacher_id' => $uid,
                    'status' => 'draft',
                ];
                if ($data['title'] === '') { flash('danger', 'Title is required.'); redirect('teacher/courses'); }
                $cid = Database::insert('courses', $data);
                log_activity('course', "Created course '{$data['title']}'", $uid);
                flash('success', 'Course created. Add modules and lessons next.');
                redirect('teacher/course&id=' . $cid);
            }
            if (($did = (int)($_POST['delete_course'] ?? 0))) {
                Database::delete('courses', 'id = ? AND teacher_id = ?', [$did, $uid]);
                flash('success', 'Course deleted.');
                redirect('teacher/courses');
            }
        }
        $courses = Database::all(
            "SELECT c.*, s.name AS school_name,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lessons,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students,
                    (SELECT ROUND(AVG(progress),0) FROM course_enrollments ce WHERE ce.course_id = c.id) AS avg_progress
             FROM courses c JOIN schools s ON s.id = c.school_id WHERE c.teacher_id = ? ORDER BY c.created_at DESC", [$uid]);
        $schools = Database::all("SELECT id, name FROM schools WHERE status = 'active'");
        $subjects = Database::all(
            "SELECT s.id, s.name FROM teacher_subjects ts JOIN subjects s ON s.id = ts.subject_id
             WHERE ts.teacher_id = ? AND s.status = 'active' ORDER BY s.name", [$uid]);
        Router::render('app/teacher/courses', [
            'title' => 'My Courses', 'courses' => $courses, 'schools' => $schools, 'subjects' => $subjects,
        ]);
    }
}

class Ctl_course {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];
        $id = (int)($_GET['id'] ?? 0);
        $course = Database::one("SELECT * FROM courses WHERE id = ? AND teacher_id = ?", [$id, $uid]);
        if (!$course) { flash('danger', 'Course not found.'); redirect('teacher/courses'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['update_course'])) {
                $data = [
                    'title' => trim($_POST['title']), 'code' => trim($_POST['code']),
                    'description' => trim($_POST['description']), 'level' => trim($_POST['level']),
                    'price' => max(0, (float)$_POST['price']),
                    'status' => in_array($_POST['status'], ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft',
                ];
                if (isset($_FILES['image']) && $_FILES['image']['name']) {
                    [$ok, $path] = upload_file($_FILES['image'], 'uploads/courses', ['jpg','jpeg','png','webp','gif']);
                    if ($ok) $data['image'] = $path;
                }
                Database::update('courses', $data, 'id = ?', [$id]);
                log_activity('course', "Updated course '{$data['title']}'", $uid);
                flash('success', 'Course updated.');
                redirect('teacher/course&id=' . $id);
            }
            if (($mid = (int)($_POST['add_module'] ?? 0)) !== 0) {
                $name = trim($_POST['module_name'] ?? '');
                if ($name !== '') {
                    $sort = (int)Database::scalar("SELECT COALESCE(MAX(sort_order),0)+1 FROM course_modules WHERE course_id = ?", [$id], 1);
                    Database::insert('course_modules', ['course_id' => $id, 'title' => $name, 'sort_order' => $sort]);
                    flash('success', 'Module added.');
                }
                redirect('teacher/course&id=' . $id);
            }
            if (($did = (int)($_POST['delete_module'] ?? 0))) {
                Database::delete('course_modules', 'id = ? AND course_id = ?', [$did, $id]);
                flash('success', 'Module deleted.');
                redirect('teacher/course&id=' . $id);
            }
            if (($lid = (int)($_POST['delete_lesson'] ?? 0))) {
                Database::delete('lessons', 'id = ? AND course_id = ?', [$lid, $id]);
                flash('success', 'Lesson deleted.');
                redirect('teacher/course&id=' . $id);
            }
            if (isset($_POST['toggle_publish'])) {
                $to = $course['status'] === 'published' ? 'draft' : 'published';
                Database::update('courses', ['status' => $to], 'id = ?', [$id]);
                $enr = Database::all("SELECT user_id FROM course_enrollments WHERE course_id = ?", [$id]);
                if ($to === 'published') {
                    foreach ($enr as $en) {
                        notify((int)$en['user_id'], 'course', 'Course published: ' . $course['title'], 'New content is available for you.', 'courses/view&id=' . $id);
                    }
                }
                flash('success', $to === 'published' ? 'Course published — students notified.' : 'Course unpublished.');
                redirect('teacher/course&id=' . $id);
            }
        }

        $modules = Database::all("SELECT * FROM course_modules WHERE course_id = ? ORDER BY sort_order", [$id]);
        foreach ($modules as &$m) {
            $m['lessons'] = Database::all("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order", [$m['id']]);
        }
        $enrollments = Database::all(
            "SELECT ce.*, u.first_name, u.last_name, u.student_id, u.avatar
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY ce.enrolled_at DESC LIMIT 50", [$id]);
        Router::render('app/teacher/course', [
            'title' => $course['title'], 'course' => $course, 'modules' => $modules, 'enrollments' => $enrollments,
        ]);
    }
}

class Ctl_lesson {
    public function run(): void {
        $u = require_role('teacher');
        $uid = (int)$u['id'];
        $courseId = (int)($_GET['course'] ?? 0);
        $course = Database::one("SELECT * FROM courses WHERE id = ? AND teacher_id = ?", [$courseId, $uid]);
        if (!$course) { flash('danger', 'Course not found.'); redirect('teacher/courses'); }
        $id = (int)($_GET['id'] ?? 0);
        $lesson = $id ? Database::one("SELECT * FROM lessons WHERE id = ? AND course_id = ?", [$id, $courseId]) : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $data = [
                'title' => trim($_POST['title']), 'type' => in_array($_POST['type'], ['video','pdf','notes','slides','audio','link'], true) ? $_POST['type'] : 'notes',
                'content' => $_POST['content'] ?? '', 'video_url' => trim($_POST['video_url'] ?? ''),
                'duration_min' => max(0, (int)($_POST['duration_min'] ?? 0)),
            ];
            if (isset($_FILES['file']) && $_FILES['file']['name']) {
                [$ok, $path] = upload_file($_FILES['file'], 'uploads/lessons', ['pdf','ppt','pptx','mp3','wav','ogg','mp4','webm']);
                if ($ok) $data['file_path'] = $path;
            }
            if ($data['title'] === '') { flash('danger', 'Title is required.'); redirect('teacher/lesson&course=' . $courseId); }
            if ($lesson) {
                Database::update('lessons', $data, 'id = ?', [$id]);
                flash('success', 'Lesson updated.');
                redirect('teacher/lesson&course=' . $courseId . '&id=' . $id);
            } else {
                $moduleId = (int)($_POST['module_id'] ?? 0);
                $sort = (int)Database::scalar("SELECT COALESCE(MAX(sort_order),0)+1 FROM lessons WHERE module_id = ?", [$moduleId], 1);
                Database::insert('lessons', array_merge($data, ['course_id' => $courseId, 'module_id' => $moduleId, 'sort_order' => $sort]));
                flash('success', 'Lesson created.');
                redirect('teacher/course&id=' . $courseId);
            }
        }

        $modules = Database::all("SELECT * FROM course_modules WHERE course_id = ? ORDER BY sort_order", [$courseId]);
        Router::render('app/teacher/lesson', [
            'title' => $lesson ? 'Edit: ' . $lesson['title'] : 'New lesson',
            'course' => $course, 'lesson' => $lesson, 'modules' => $modules,
        ]);
    }
}
