<?php
/**
 * Gamification API: profile, badges, leaderboard
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
$me = Database::one("SELECT xp, level, streak, streak_last FROM users WHERE id = ?", [$u['id']]);
$badges = Database::all(
    "SELECT b.id, b.name, b.icon, b.description, b.category, ub.earned_at
     FROM user_badges ub JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ?", [$u['id']]);
$rank = 1 + (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student' AND xp > ?", [$me['xp']], 0);
api_out(['ok' => true, 'profile' => $me, 'rank' => $rank, 'badges' => $badges]);
