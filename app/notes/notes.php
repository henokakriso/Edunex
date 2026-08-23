<?php
/**
 * Student Notes: personal note-taking on courses/lessons
 */
class Ctl_index {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $courseId = (int)($_GET['course'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $body = trim($_POST['body'] ?? '');
            $lid = (int)($_POST['lesson_id'] ?? 0) ?: null;
            $cid = (int)($_POST['course_id'] ?? 0) ?: $courseId ?: null;
            if ($body !== '' || $title !== '') {
                Database::insert('student_notes', [
                    'user_id' => $uid, 'lesson_id' => $lid, 'course_id' => $cid,
                    'title' => $title ?: mb_substr($body, 0, 60), 'body' => $body,
                ]);
                flash('success', 'Note saved.');
            }
            redirect('notes' . ($cid ? '&course=' . $cid : ''));
        }

        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['note_id'] ?? 0);
            $body = trim($_POST['body'] ?? '');
            $title = trim($_POST['title'] ?? '');
            Database::update('student_notes', [
                'title' => $title, 'body' => $body,
            ], 'id = ? AND user_id = ?', [$id, $uid]);
            flash('success', 'Note updated.');
            redirect('notes' . ($courseId ? '&course=' . $courseId : ''));
        }

        if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['note_id'] ?? 0);
            Database::delete('student_notes', 'id = ? AND user_id = ?', [$id, $uid]);
            flash('success', 'Note deleted.');
            redirect('notes' . ($courseId ? '&course=' . $courseId : ''));
        }

        if ($action === 'pin' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['note_id'] ?? 0);
            $cur = Database::scalar("SELECT pinned FROM student_notes WHERE id = ? AND user_id = ?", [$id, $uid], 0);
            Database::update('student_notes', ['pinned' => $cur ? 0 : 1], 'id = ? AND user_id = ?', [$id, $uid]);
            redirect('notes' . ($courseId ? '&course=' . $courseId : ''));
        }

        $where = 'n.user_id = ?';
        $params = [$uid];
        if ($courseId) { $where .= ' AND n.course_id = ?'; $params[] = $courseId; }

        $notes = Database::all(
            "SELECT n.*, c.title AS course_title, l.title AS lesson_title
             FROM student_notes n
             LEFT JOIN courses c ON c.id = n.course_id
             LEFT JOIN lessons l ON l.id = n.lesson_id
             WHERE $where ORDER BY n.pinned DESC, n.updated_at DESC", $params
        );

        $courses = Database::all(
            "SELECT DISTINCT c.id, c.title FROM student_notes n JOIN courses c ON c.id = n.course_id WHERE n.user_id = ? ORDER BY c.title", [$uid]
        );

        Router::render('app/notes/index', [
            'title' => 'My Notes', 'notes' => $notes, 'courses' => $courses,
            'filterCourse' => $courseId,
        ]);
    }
}
