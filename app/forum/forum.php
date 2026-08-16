<?php
/**
 * Course forum: topics, posts, replies, reactions
 */

function forum_reaction_counts(int $targetType, int $targetId): array {
    $rows = Database::all("SELECT reaction, COUNT(*) AS n FROM reactions WHERE target_type = ? AND target_id = ? GROUP BY reaction", [$targetType, $targetId]);
    $out = [];
    foreach ($rows as $r) $out[$r['reaction']] = (int)$r['n'];
    return $out;
}

class Ctl_course_forum {
    public function run(): void {
        $u = require_login();
        $courseId = (int)($_GET['course'] ?? 0);
        $course = $courseId ? Database::one("SELECT c.*, u.first_name, u.last_name FROM courses c JOIN users u ON u.id = c.teacher_id WHERE c.id = ?", [$courseId]) : null;
        if ($course && $_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $canPost = (int)$course['teacher_id'] === (int)$u['id']
                || (bool)Database::one("SELECT 1 FROM course_enrollments WHERE course_id = ? AND user_id = ?", [$courseId, $u['id']]);
            if (!$canPost) {
                flash('danger', 'Enroll in this course to join its discussion.');
                redirect('courses/discuss&course=' . $courseId);
            }
            if (isset($_POST['new_topic'])) {
                Database::insert('forum_topics', [
                    'course_id' => $courseId, 'author_id' => (int)$u['id'],
                    'title' => trim($_POST['title']), 'body' => trim($_POST['body']),
                    'pinned' => !empty($_POST['pinned']) && $u['role'] === 'teacher' ? 1 : 0,
                ]);
                $tid = Database::insertId();
                log_activity('forum_topic', 'New topic: ' . $_POST['title'], (int)$u['id']);
                flash('success', 'Topic posted.');
                redirect('courses/discuss&course=' . $courseId . '&topic=' . $tid);
            }
        }
        $topics = Database::all(
            "SELECT t.*, u.first_name, u.last_name, (SELECT COUNT(*) FROM forum_posts p WHERE p.topic_id = t.id) AS posts,
                    (SELECT MAX(p.created_at) FROM forum_posts p WHERE p.topic_id = t.id) AS last_post
             FROM forum_topics t JOIN users u ON u.id = t.author_id
             WHERE t.course_id = ? ORDER BY t.pinned DESC, t.created_at DESC", [$courseId]);
        Router::render('app/forum/forum', ['title' => 'Forum', 'course' => $course, 'topics' => $topics]);
    }
}

class Ctl_topic {
    public function run(): void {
        $u = require_login();
        $tid = (int)($_GET['topic'] ?? 0);
        $topic = Database::one(
            "SELECT t.*, u.first_name, u.last_name, u.role AS author_role, c.title AS course_title, c.id AS course_id
             FROM forum_topics t JOIN users u ON u.id = t.author_id JOIN courses c ON c.id = t.course_id
             WHERE t.id = ?", [$tid]);
        if (!$topic) { flash('danger', 'Topic not found.'); redirect('dashboard'); }
        Database::query("UPDATE forum_topics SET views = views + 1 WHERE id = ?", [$tid]);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['reply'])) {
                $body = trim($_POST['reply']);
                if ($body !== '') {
                    Database::insert('forum_posts', ['topic_id' => $tid, 'author_id' => (int)$u['id'], 'body' => $body, 'is_answer' => !empty($_POST['is_answer']) ? 1 : 0]);
                    $subs = Database::all("SELECT DISTINCT author_id FROM forum_posts WHERE topic_id = ? AND author_id != ?", [$tid, $u['id']]);
                    foreach ($subs as $s) notify((int)$s['author_id'], 'message', 'New reply in: ' . $topic['title'], mb_strimwidth($body, 0, 80, '…'), 'courses/discuss&course=' . $topic['course_id'] . '&topic=' . $tid);
                    flash('success', 'Reply posted.');
                }
                redirect('courses/discuss&course=' . $topic['course_id'] . '&topic=' . $tid);
            }
        }
        $posts = Database::all(
            "SELECT p.*, u.first_name, u.last_name, u.role AS author_role
             FROM forum_posts p JOIN users u ON u.id = p.author_id
             WHERE p.topic_id = ? ORDER BY p.is_answer DESC, p.created_at ASC", [$tid]);
        $myReacts = [];
        foreach (Database::all("SELECT target_id, reaction FROM reactions WHERE target_type = 'forum' AND user_id = ?", [$u['id']]) as $r) $myReacts[(int)$r['target_id']] = $r['reaction'];
        Router::render('app/forum/topic', ['title' => $topic['title'], 'topic' => $topic, 'posts' => $posts, 'myReacts' => $myReacts]);
    }
}
