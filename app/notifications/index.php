<?php
/**
 * Notifications center: list, mark read, mark all read
 */

class Ctl_index {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $filter = $_GET['filter'] ?? 'all';
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $args = [$uid];
        if ($filter === 'unread') { $sql .= " AND read_at IS NULL"; }
        elseif ($filter === 'achievements') { $sql .= " AND type = 'achievement'"; }
        elseif ($filter === 'academic') { $sql .= " AND type IN ('assignment','exam','feedback','announcement')"; }
        $sql .= " ORDER BY created_at DESC LIMIT 100";
        $notifs = Database::all($sql, $args);
        foreach ($notifs as &$n) {
            $n['ann_id'] = null;
            $n['author_name'] = null;
            if ($n['type'] === 'announcement') {
                if (preg_match('~[?&]id=(\d+)~', (string)$n['link'], $m)) {
                    $n['ann_id'] = (int)$m[1];
                } else {
                    // Legacy links (communication/announcements, no id) — resolve by title
                    $n['ann_id'] = (int)Database::scalar(
                        "SELECT id FROM announcements WHERE title = ? ORDER BY created_at DESC, id DESC LIMIT 1",
                        [$n['title']], 0) ?: null;
                }
                if ($n['ann_id']) {
                    $a = Database::one(
                        "SELECT us.first_name, us.last_name, us.avatar
                         FROM announcements a JOIN users us ON us.id = a.author_id WHERE a.id = ?",
                        [$n['ann_id']]);
                    if ($a) {
                        $n['author_first'] = $a['first_name'];
                        $n['author_last'] = $a['last_name'];
                        $n['author_avatar'] = $a['avatar'];
                    }
                }
            }
        }
        unset($n);
        $unread = (int)Database::scalar("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL", [$uid], 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['mark_all'])) {
                $total = (int)Database::scalar("SELECT COUNT(*) FROM notifications WHERE user_id = ?", [$uid], 0);
                if ($total === 0) {
                    flash('info', 'You have no notifications yet.');
                } elseif (!Database::update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'user_id = ? AND read_at IS NULL', [$uid])) {
                    flash('info', 'No unread notifications to mark as read.');
                } else {
                    flash('success', 'All notifications marked as read.');
                }
                redirect('notifications');
            }
            if (($nid = (int)($_POST['mark_one'] ?? 0))) {
                Database::update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'id = ? AND user_id = ?', [$nid, $uid]);
                redirect('notifications');
            }
            if (($nid = (int)($_POST['delete_one'] ?? 0))) {
                Database::delete('notifications', 'id = ? AND user_id = ?', [$nid, $uid]);
                redirect('notifications');
            }
            if (isset($_POST['delete_all'])) {
                $deleted = Database::delete('notifications', 'user_id = ?', [$uid]);
                if (!$deleted) {
                    flash('info', 'You have no notifications to clear.');
                } else {
                    flash('success', 'Notifications cleared.');
                }
                redirect('notifications');
            }
        }
        Router::render('app/notifications/index', [
            'title' => 'Notifications', 'notifs' => $notifs, 'unread' => $unread, 'filter' => $filter,
        ]);
    }
}
