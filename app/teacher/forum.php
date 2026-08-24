<?php
require_once __DIR__ . "/../forum/forum.php";

/* Teacher discussion hub — topics are created per course, and only for
 * courses in the subjects the director assigned to this teacher. */
class Ctl_teacher_forum {
    public function run(): void {
        $u = require_login();
        if (!in_array($u['role'], ['teacher', 'lecturer'], true)) { flash('danger', 'Teachers only.'); redirect('dashboard'); }
        $uid = (int)$u['id'];

        $courses = SubjectAuth::courses($uid);
        $ids = array_map('intval', array_column($courses, 'id'));
        $courseId = (int)($_GET['course'] ?? ($ids[0] ?? 0));
        if ($courseId && !in_array($courseId, $ids, true)) {
            flash('danger', 'Discussions can only be created for courses in the subjects assigned to you by the director.');
            redirect('teacher/forum');
        }
        $course = $courseId ? Database::one("SELECT c.*, u.first_name, u.last_name FROM courses c JOIN users u ON u.id = c.teacher_id WHERE c.id = ?", [$courseId]) : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['new_topic'])) {
                if (!$course) { flash('danger', 'Choose a course first.'); redirect('teacher/forum'); }
                $title = trim((string)($_POST['title'] ?? ''));
                if ($title === '') { flash('danger', 'Topic title is required.'); redirect('teacher/forum&course=' . $courseId); }
                $tid = (int)Database::insert('forum_topics', [
                    'course_id' => $courseId, 'author_id' => $uid,
                    'title' => $title, 'body' => trim((string)($_POST['body'] ?? '')),
                    'pinned' => !empty($_POST['pinned']) ? 1 : 0,
                ]);
                log_activity('forum_topic', 'New topic: ' . $title, $uid);
                Ledger::append((int)$u['school_id'], $uid, 'forum.topic', 'forum_topic', $tid, ['course_id' => $courseId, 'title' => mb_strimwidth($title, 0, 60, '…')]);
                flash('success', 'Discussion posted — students enrolled in this course can reply.');
                redirect('courses/discuss&course=' . $courseId . '&topic=' . $tid);
            }
        }

        $df = demo_filter('t');
        $topics = Database::all(
            "SELECT t.*, u.first_name, u.last_name, (SELECT COUNT(*) FROM forum_posts p WHERE p.topic_id = t.id) AS posts,
                    (SELECT MAX(p.created_at) FROM forum_posts p WHERE p.topic_id = t.id) AS last_post
             FROM forum_topics t JOIN users u ON u.id = t.author_id
             WHERE t.course_id = ? $df ORDER BY t.pinned DESC, t.created_at DESC", [$courseId]);
        Router::render('app/teacher/forum', [
            'title' => 'Discussion', 'course' => $course, 'topics' => $topics,
            'courses' => $courses, 'courseId' => $courseId,
        ]);
    }
}
