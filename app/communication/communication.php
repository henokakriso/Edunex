<?php
/**
 * Communication: direct & group messages, announcements
 */

class Ctl_messages {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $convId = (int)($_GET['conv'] ?? 0);
        $to = (int)($_GET['to'] ?? 0);

        self::backfillKeys($uid);

        $convs = Database::all(
            "SELECT c.id, c.is_group, c.title, c.conv_key, cm.last_read_at,
                    (SELECT cm2.user_id FROM conversation_members cm2 WHERE cm2.conversation_id = c.id AND cm2.user_id != ? LIMIT 1) AS other_id,
                    (SELECT m.body FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_body,
                    (SELECT m.created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_at,
                    (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.sender_id != ? AND m.created_at > COALESCE(cm.last_read_at, '1970-01-01')) AS unread
             FROM conversations c
             JOIN conversation_members cm ON cm.conversation_id = c.id AND cm.user_id = ?
             WHERE 1=1 GROUP BY c.id, c.is_group, c.title, c.conv_key, cm.last_read_at ORDER BY last_at DESC LIMIT 60", [$uid, $uid, $uid]);
        foreach ($convs as &$cv) {
            if ($cv['is_group']) {
                $cv['title'] = $cv['title'] ?: 'Group';
                $cv['icon'] = '';
            } else {
                $other = $cv['other_id'] ? Database::one("SELECT id, first_name, last_name, email, school_id, avatar FROM users WHERE id = ?", [(int)$cv['other_id']]) : null;
                $cv['other'] = $other;
                $cv['title'] = $other ? $other['first_name'] . ' ' . $other['last_name'] : 'Unknown';
                $cv['icon'] = '';
            }
            if ($cv['last_body'] !== null && self::isSealed($cv['last_body']) && $cv['conv_key'] !== '') {
                $cv['last_body'] = '[ Encrypted by C backend ]';
            }
        }
        unset($cv);

        // Support ?to=<user_id> from people search: open (or start) that DM
        if ($to && !$convId && $to !== $uid) {
            $cid = self::startDm($u, $uid, $to, false);
            if ($cid) redirect('messages&conv=' . $cid);
            redirect('messages');
        }

        $open = null;
        $messages = [];
        if ($convId) {
            // Authorize: can only open a conversation you belong to
            $open = Database::one(
                "SELECT c.id, c.is_group, c.title, c.conv_key FROM conversations c
                 JOIN conversation_members me ON me.conversation_id = c.id AND me.user_id = ?
                 WHERE c.id = ?", [$uid, $convId]);
            if (!$open) { flash('danger', 'Conversation not found.'); redirect('messages'); }
            $open['other'] = null;
            if (!$open['is_group']) {
                $oid = Database::scalar("SELECT user_id FROM conversation_members WHERE conversation_id = ? AND user_id != ? LIMIT 1", [$convId, $uid], 0);
                if ($oid) $open['other'] = Database::one("SELECT id, first_name, last_name, school_id FROM users WHERE id = ?", [(int)$oid]);
                $open['title'] = $open['other'] ? $open['other']['first_name'] . ' ' . $open['other']['last_name'] : 'Unknown';
            } else {
                $open['title'] = $open['title'] ?: 'Group';
            }
            $messages = Database::all(
                "SELECT m.id, m.body, m.hmac, m.created_at, m.sender_id, us.first_name, us.last_name, us.avatar
                 FROM messages m JOIN users us ON us.id = m.sender_id
                 WHERE m.conversation_id = ? ORDER BY m.id LIMIT 200", [$convId]);
            $convKey = (string)($open['conv_key'] ?? '');
            foreach ($messages as &$mm) {
                $mm['sealed'] = false;
                if ($convKey !== '' && self::isSealed($mm['body'])) {
                    $mm['sealed'] = true;
                    $mm['body'] = self::unseal($convKey, $mm['body'], (string)($mm['hmac'] ?? ''));
                }
            }
            unset($mm);
            Database::run("UPDATE conversation_members SET last_read_at = NOW() WHERE conversation_id = ? AND user_id = ?", [$convId, $uid]);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['start_dm'])) {
                $otherId = (int)$_POST['other_id'];
                if ($otherId === $uid) { flash('danger', 'You cannot message yourself.'); redirect('messages'); }
                $cid = self::startDm($u, $uid, $otherId);
                if ($cid) redirect('messages&conv=' . $cid);
                redirect('messages');
            }
            if (isset($_POST['send_message'])) {
                $body = trim($_POST['body'] ?? '');
                $convId2 = (int)($_POST['conv_id'] ?? 0);
                if ($body !== '' && $convId2) {
                    $member = Database::one("SELECT 1 FROM conversation_members WHERE conversation_id = ? AND user_id = ?", [$convId2, $uid]);
                    if ($member) {
                        $convKey = (string)Database::scalar("SELECT conv_key FROM conversations WHERE id = ?", [$convId2], '');
                        $hmac = '';
                        $stored = $body;
                        if ($convKey !== '') {
                            $stored = CWorker::chatEncrypt($convKey, $body);
                            $hmac = CWorker::chatHmac($convKey, $stored);
                            if ($hmac === '') $stored = $body; // C backend unavailable -> store plain
                        }
                        $mid = Database::insert('messages', ['conversation_id' => $convId2, 'sender_id' => $uid, 'body' => $stored, 'hmac' => $hmac]);
                        $others = Database::all("SELECT user_id FROM conversation_members WHERE conversation_id = ? AND user_id != ?", [$convId2, $uid]);
                        foreach ($others as $o) {
                            notify((int)$o['user_id'], 'message', 'New message', mb_strimwidth($body, 0, 100, '…'), 'messages&conv=' . $convId2);
                        }
                        Database::run("UPDATE conversation_members SET last_read_at = NOW() WHERE conversation_id = ? AND user_id = ?", [$convId2, $uid]);
                    }
                }
                redirect('messages&conv=' . $convId2);
            }
        }
        Router::render('app/communication/messages', [
            'title' => 'Messages', 'convs' => $convs, 'open' => $open, 'messages' => $messages,
        ]);
    }

    /** Backfill conv_key for legacy conversations missing one. */
    private static function backfillKeys(int $uid): void {
        if (!CWorker::available()) return;
        $rows = Database::all(
            "SELECT c.id, c.is_group,
                    (SELECT user_id FROM conversation_members WHERE conversation_id = c.id ORDER BY user_id LIMIT 1) AS a,
                    (SELECT user_id FROM conversation_members WHERE conversation_id = c.id ORDER BY user_id DESC LIMIT 1) AS b
             FROM conversations c WHERE c.conv_key = '' LIMIT 25");
        foreach ($rows as $r) {
            $a = (int)($r['a'] ?? 0);
            $b = (int)($r['b'] ?? 0);
            if (!$a || !$b) continue;
            $k = $r['is_group'] ? self::makeConvKey($a, $a) : self::makeConvKey($a, $b);
            Database::run("UPDATE conversations SET conv_key = ? WHERE id = ? AND conv_key = ''", [$k, (int)$r['id']]);
        }
    }

    /** Static helper: does the body look like it was sealed by the C backend? */
    public static function isSealed(?string $body): bool {
        return strlen($body) >= 44 && preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $body) === 1 && CWorker::available();
    }

    /** Seal (encrypt) a message body via the C backend; returns original on failure. */
    public static function seal(string $convKey, string $body): string {
        return CWorker::chatEncrypt($convKey, $body);
    }

    /** Unseal + verify integrity via the C backend. On verify failure returns a warning fallback. */
    public static function unseal(string $convKey, string $cipher, string $hmac): string {
        $expected = CWorker::chatHmac($convKey, $cipher);
        $plain = CWorker::chatDecrypt($convKey, $cipher);
        if ($expected !== '' && $hmac !== '' && $expected !== $hmac) {
            return '[ Message integrity check failed — possible tampering ]';
        }
        return $plain;
    }

    /** Deterministic per-participant conversation key derived from user ids. */
    public static function makeConvKey(int $a, int $b): string {
        $lo = min($a, $b);
        $hi = max($a, $b);
        return substr(CWorker::hmac(API_SECRET, "conv:$lo:$hi"), 0, 64);
    }

    /** Find an existing 1:1 conversation between two users (any school) */
    private static function findDm(int $a, int $b): ?int {
        $row = Database::one(
            "SELECT c.id FROM conversations c
             JOIN conversation_members x ON x.conversation_id = c.id AND x.user_id = ? AND c.is_group = 0
             JOIN conversation_members y ON y.conversation_id = c.id AND y.user_id = ? AND c.is_group = 0
             WHERE (SELECT COUNT(*) FROM conversation_members cm WHERE cm.conversation_id = c.id) = 2
             LIMIT 1", [$a, $b]);
        return $row ? (int)$row['id'] : null;
    }

    /** Ensure the target user exists, then create/open a DM (cross-school allowed) */
    private static function startDm(?array $u, int $uid, int $otherId, bool $flashMsg = true): ?int {
        if ($otherId === $uid) {
            if ($flashMsg) flash('danger', 'You cannot message yourself.');
            return null;
        }
        $other = Database::one("SELECT id FROM users WHERE id = ? AND status = 'active'", [$otherId]);
        if (!$other) {
            if ($flashMsg) flash('danger', 'User not found.');
            return null;
        }
        $existing = self::findDm($uid, $otherId);
        if ($existing) return $existing;
        $cid = Database::insert('conversations', ['school_id' => $u['school_id'] ?? my_school_id(), 'is_group' => 0, 'title' => '', 'conv_key' => self::makeConvKey($uid, $otherId)]);
        Database::insert('conversation_members', ['conversation_id' => $cid, 'user_id' => $uid]);
        Database::insert('conversation_members', ['conversation_id' => $cid, 'user_id' => $otherId]);
        if ($flashMsg) flash('success', 'Conversation started.');
        return $cid;
    }
}

class Ctl_groups {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_group'])) {
                $title = trim($_POST['title'] ?? '');
                $members = array_values(array_filter(array_map('intval', (array)($_POST['members'] ?? []))));
                $members[] = $uid;
                $members = array_values(array_unique($members));
                $gid = Database::insert('conversations', ['school_id' => $u['school_id'] ?? my_school_id(), 'is_group' => 1, 'title' => $title, 'conv_key' => Ctl_messages::makeConvKey($uid, $uid)]);
                foreach ($members as $m) Database::insert('conversation_members', ['conversation_id' => $gid, 'user_id' => $m]);
                flash('success', 'Group created with ' . count($members) . ' members.');
                redirect('messages&conv=' . $gid);
            }
        }
        $myGroups = Database::all(
            "SELECT c.*, (SELECT COUNT(*) FROM conversation_members cm WHERE cm.conversation_id = c.id) AS members
             FROM conversations c JOIN conversation_members cm ON cm.conversation_id = c.id AND cm.user_id = ?
             WHERE c.is_group = 1 ORDER BY c.created_at DESC", [$uid]);
        $candidates = Database::all(
            "SELECT us.id, CONCAT(us.first_name, ' ', us.last_name) AS name, us.role
             FROM users us WHERE us.school_id = ? AND us.id != ? AND us.status = 'active' ORDER BY us.role, us.last_name LIMIT 300",
            [my_school_id(), $uid]);
        Router::render('app/communication/groups', ['title' => 'Groups', 'myGroups' => $myGroups, 'candidates' => $candidates]);
    }
}

class Ctl_announcements {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $anns = Database::all(
            "SELECT a.*, CONCAT(us.first_name, ' ', us.last_name) AS author_name, s.name AS school_name, c.title AS course_title
             FROM announcements a JOIN users us ON us.id = a.author_id JOIN schools s ON s.id = a.school_id
             LEFT JOIN courses c ON c.id = a.course_id
             WHERE (a.audience = 'all' OR a.audience = ?)
               AND (a.school_id = ? OR a.course_id IN (SELECT course_id FROM course_enrollments WHERE user_id = ?))
             ORDER BY a.pinned DESC, a.created_at DESC LIMIT 60",
            [$u['role'], my_school_id(), $uid]);
        Router::render('app/communication/announcements', ['title' => 'Announcements', 'anns' => $anns]);
    }
}
