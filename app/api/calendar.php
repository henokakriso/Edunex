<?php
/**
 * Calendar API: upcoming events
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
$days = min(90, max(1, (int)($_GET['days'] ?? 14)));
$events = Database::all(
    "SELECT * FROM calendar_events
     WHERE (user_id = ? OR user_id IS NULL OR school_id = ?)
       AND start_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
     ORDER BY start_at LIMIT 100",
    [$u['id'], $u['school_id'], $days]);
api_out(['ok' => true, 'events' => array_map(function ($e) {
    $e['id'] = (int)$e['id'];
    return $e;
}, $events)]);
