<?php
/**
 * Announcement detail — email-style view.
 * Opened from the notifications list. Back returns to notifications.
 * Forward lets the reader send a suggestion/comment to the announcement creator (a DM).
 */

class Ctl_announcement {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { flash('danger', 'Announcement not found.'); redirect('notifications'); }

        $ann = Database::one(
            "SELECT a.*, us.first_name, us.last_name, us.role AS author_role, us.avatar,
                    s.name AS school_name, s.type AS school_type, c.title AS course_title
             FROM announcements a
             JOIN users us ON us.id = a.author_id JOIN schools s ON s.id = a.school_id
             LEFT JOIN courses c ON c.id = a.course_id
             WHERE a.id = ?", [$id]);
        if (!$ann) { flash('danger', 'Announcement not found.'); redirect('notifications'); }

        // Access: author, standard visibility, or a notification was delivered to this user
        $link = 'communication/announcement&id=' . $id;
        $hasNotif = (int)Database::scalar("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND link = ?", [$uid, $link], 0) > 0
            || (int)Database::scalar(
                "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = 'announcement' AND title = ?",
                [$uid, $ann['title']], 0) > 0; // legacy links carried no id
        $roleOk = $ann['audience'] === 'all'
            || ($ann['audience'] === 'students' && $u['role'] === 'student')
            || ($ann['audience'] === 'teachers' && $u['role'] === 'teacher')
            || ($ann['audience'] === 'parents' && $u['role'] === 'parent');
        $enrolled = (int)$ann['course_id'] > 0 && Database::scalar("SELECT COUNT(*) FROM course_enrollments WHERE user_id = ? AND course_id = ?", [$uid, (int)$ann['course_id']], 0) > 0;
        $teaches = (int)$ann['course_id'] > 0 && Database::scalar("SELECT COUNT(*) FROM courses WHERE id = ? AND teacher_id = ?", [(int)$ann['course_id'], $uid], 0) > 0;
        $visible = $roleOk || ($ann['audience'] === 'course' && ($enrolled || $teaches))
            || (int)$ann['school_id'] === (int)my_school_id() && in_array($ann['audience'], ['all', 'students', 'teachers', 'parents'], true);
        if (!$hasNotif && !$visible && (int)$ann['author_id'] !== $uid) {
            flash('danger', 'Access denied.');
            redirect('notifications');
        }

        // Opening the announcement marks the notification as read (new + legacy links)
        $marked = Database::update('notifications', ['read_at' => date('Y-m-d H:i:s')], "user_id = ? AND link = 'communication/announcement&id=" . $id . "'", [$uid]);
        if (!$marked) {
            Database::update('notifications', ['read_at' => date('Y-m-d H:i:s')], "user_id = ? AND type = 'announcement' AND title = ?", [$uid, $ann['title']]);
        }

        $author = $ann['first_name'] . ' ' . $ann['last_name'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['reply_announcement'])) {
                $body = trim($_POST['body'] ?? '');
                $to = (int)$ann['author_id'];
                if ($body === '') { flash('danger', 'Write a message first.'); redirect('communication/announcement&id=' . $id); }
                if ($to === $uid) { flash('danger', 'This is your own announcement.'); redirect('communication/announcement&id=' . $id); }
                $existing = Database::scalar(
                    "SELECT c.id FROM conversations c
                     JOIN conversation_members x ON x.conversation_id = c.id AND x.user_id = ? AND c.is_group = 0
                     JOIN conversation_members y ON y.conversation_id = c.id AND y.user_id = ? AND c.is_group = 0
                     WHERE (SELECT COUNT(*) FROM conversation_members cm WHERE cm.conversation_id = c.id) = 2 LIMIT 1",
                    [$uid, $to], 0);
                $cid = $existing ? (int)$existing : null;
                if (!$cid) {
                    $cid = Database::insert('conversations', [
                        'school_id' => $u['school_id'] ?? my_school_id(), 'is_group' => 0, 'title' => '',
                        'conv_key' => Ctl_messages::makeConvKey($uid, $to),
                    ]);
                    Database::insert('conversation_members', ['conversation_id' => $cid, 'user_id' => $uid]);
                    Database::insert('conversation_members', ['conversation_id' => $cid, 'user_id' => $to]);
                }
                $convKey = (string)Database::scalar("SELECT conv_key FROM conversations WHERE id = ?", [$cid], '');
                $stored = $body; $hmac = '';
                if ($convKey !== '') {
                    $stored = CWorker::chatEncrypt($convKey, $body);
                    $hmac = CWorker::chatHmac($convKey, $stored);
                    if ($hmac === '') $stored = $body;
                }
                Database::insert('messages', ['conversation_id' => $cid, 'sender_id' => $uid, 'body' => $stored, 'hmac' => $hmac]);
                notify($to, 'message', 'Suggestion about "' . mb_strimwidth($ann['title'], 0, 40, '…') . '"', mb_strimwidth($body, 0, 100, '…'), 'messages&conv=' . $cid);
                flash('success', 'Suggestion sent to ' . $author . '.');
                redirect('communication/announcement&id=' . $id);
            }
        }

        Router::render('app/communication/announcement', [
            'title' => $ann['title'], 'ann' => $ann, 'author' => $author, 'back' => $_GET['back'] ?? 'notifications',
        ]);
    }
}
