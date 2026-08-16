<?php
/**
 * Gamification: badges, challenges, goals, leaderboard
 */

class Ctl_badges {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $me = Database::one("SELECT xp, level, streak FROM users WHERE id = ?", [$uid]);
        $mine = Database::all(
            "SELECT b.*, ub.earned_at FROM user_badges ub JOIN badges b ON b.id = ub.badge_id
             WHERE ub.user_id = ? ORDER BY ub.earned_at DESC", [$uid]);
        $mineIds = array_column($mine, 'id');
        $all = Database::all("SELECT * FROM badges ORDER BY xp_required");
        foreach ($all as &$b) $b['earned'] = in_array($b['id'], $mineIds);
        unset($b);
        Router::render('app/gamification/badges', [
            'title' => 'Badges', 'me' => $me, 'mine' => $mine, 'all' => $all,
        ]);
    }
}

class Ctl_index {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $isPlayer = (bool)($u['role'] === 'student');
        $me = Database::one("SELECT xp, level, streak FROM users WHERE id = ?", [$uid]);
        $goals = $isPlayer ? Database::all("SELECT * FROM goals WHERE user_id = ? AND completed = 0 ORDER BY due_date", [$uid]) : [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (!$isPlayer) { flash('danger', 'Only students can create or manage personal goals.'); redirect('gamification'); }
            if (isset($_POST['goal'])) {
                Database::insert('goals', [
                    'user_id' => $uid, 'title' => trim($_POST['goal']),
                    'target' => (int)($_POST['target'] ?? 100), 'unit' => $_POST['unit'] ?? 'lessons',
                    'due_date' => $_POST['due_date'] ?: null,
                ]);
                flash('success', 'Goal created.');
                redirect('gamification');
            }
            if (($gid = (int)($_POST['complete_goal'] ?? 0))) {
                Database::run("UPDATE goals SET completed = 1, current = target WHERE id = ? AND user_id = ?", [$gid, $uid]);
                award_xp($uid, 15, 'Goal completed');
                flash('success', 'Goal completed! +15 XP');
                redirect('gamification');
            }
            if (($gid = (int)($_POST['delete_goal'] ?? 0))) {
                Database::run("DELETE FROM goals WHERE id = ? AND user_id = ?", [$gid, $uid]);
                flash('success', 'Goal removed.');
                redirect('gamification');
            }
        }
        $challenges = Database::all(
            "SELECT c.*, uc.progress, uc.completed AS done
             FROM challenges c LEFT JOIN user_challenges uc ON uc.challenge_id = c.id AND uc.user_id = ?
             WHERE c.school_id = ? AND (c.ends_at IS NULL OR c.ends_at >= CURDATE())
             ORDER BY c.id DESC LIMIT 6", [$uid, (int)my_school_id()]);
        foreach ($challenges as &$ch) {
            $ch['earned'] = $ch['done'] ? $ch['reward_xp'] : 0;
            $ch['x'] = 0;
            if (mb_stripos((string)$ch['title'], 'lesson') !== false || mb_stripos((string)$ch['description'], 'lesson') !== false) {
                $ch['x'] = Database::scalar("SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND completed = 1", [$uid], 0);
            } elseif (mb_stripos((string)$ch['title'], 'xp') !== false || mb_stripos((string)$ch['description'], 'xp') !== false) {
                $ch['x'] = (int)$me['xp'];
            } elseif (mb_stripos((string)$ch['title'], 'streak') !== false || mb_stripos((string)$ch['description'], 'streak') !== false) {
                $ch['x'] = (int)$me['streak'];
            } elseif (mb_stripos((string)$ch['title'], 'quiz') !== false || mb_stripos((string)$ch['description'], 'quiz') !== false) {
                $ch['x'] = Database::scalar("SELECT COUNT(*) FROM exam_attempts WHERE student_id = ?", [$uid], 0);
            } else {
                $ch['x'] = Database::scalar("SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND completed = 1", [$uid], 0);
            }
            if (!$ch['done'] && $ch['x'] >= $ch['reward_xp']) {
                Database::run("INSERT INTO user_challenges (user_id, challenge_id, progress, completed) VALUES (?,?,?,1) ON DUPLICATE KEY UPDATE completed = 1, progress = VALUES(progress)", [$uid, $ch['id'], $ch['x']]);
                if ($isPlayer) award_xp($uid, (int)$ch['reward_xp'], 'Challenge: ' . $ch['title']);
                $ch['done'] = 1; $ch['earned'] = (int)$ch['reward_xp'];
            } elseif (!$ch['done']) {
                Database::run("INSERT INTO user_challenges (user_id, challenge_id, progress, completed) VALUES (?,?,?,0) ON DUPLICATE KEY UPDATE progress = GREATEST(progress, VALUES(progress))", [$uid, $ch['id'], $ch['x']]);
            }
            $ch['x'] = (int)$ch['x'];
        }
        unset($ch);
        $top = Database::all("SELECT id, first_name, last_name, xp, level, school_id FROM users WHERE role = 'student' ORDER BY xp DESC LIMIT 10");
        $myRank = 1 + (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student' AND xp > ?", [$me['xp']], 0);
        Router::render('app/gamification/index', [
            'title' => 'Gamification', 'me' => $me, 'goals' => $goals,
            'challenges' => $challenges, 'top' => $top, 'myRank' => $myRank, 'isPlayer' => $isPlayer,
        ]);
    }
}

class Ctl_leaderboard {
    public function run(): void {
        $u = require_login();
        $sid = (int)my_school_id();
        $scope = $_GET['scope'] ?? 'school';
        $role = in_array($_GET['role'] ?? 'student', ['student', 'teacher', 'parent'], true) ? $_GET['role'] : 'student';
        $roleWhere = $scope === 'school' ? " AND school_id = ?" : '';
        $args = $scope === 'school' ? [$sid] : [];
        $list = Database::all(
            "SELECT id, first_name, last_name, xp, level, streak, role FROM users
             WHERE role = ? $roleWhere ORDER BY xp DESC LIMIT 100", array_merge([$role], $args));
        Router::render('app/gamification/leaderboard', ['title' => 'Leaderboard', 'list' => $list, 'scope' => $scope, 'role' => $role]);
    }
}