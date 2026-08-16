<?php
/**
 * Library API: browse catalog
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
$q = trim((string)($_GET['q'] ?? ''));
$type = (string)($_GET['type'] ?? '');
$where = "l.status = 'published' AND l.school_id = ?";
$args = [$u['school_id']];
if ($q !== '') { $where .= " AND l.title LIKE ?"; $args[] = '%' . $q . '%'; }
if ($type !== '') { $where .= " AND l.type = ?"; $args[] = $type; }
$items = Database::all("SELECT l.*, (SELECT COUNT(*) FROM library_favorites f WHERE f.item_id = l.id AND f.user_id = ?) AS favorited FROM library_items l WHERE $where ORDER BY l.title LIMIT 100", array_merge([$u['id']], $args));
api_out(['ok' => true, 'count' => count($items), 'items' => $items]);
