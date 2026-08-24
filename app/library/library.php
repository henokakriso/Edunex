<?php
/**
 * Library module: browse, search, item view, favorites, downloads
 */

class Ctl_index {
    public function run(): void {
        $u = require_login();
        if (!module_active((int)$u['school_id'], 'library')) { http_response_code(403); die('The Library module is not installed for your school.'); }
        $q = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? '';
        $df = demo_filter('i');
        $sql = "SELECT i.*, s.name AS school_name,
                    (SELECT COUNT(*) FROM library_favorites f WHERE f.item_id = i.id) AS favs
                FROM library_items i JOIN schools s ON s.id = i.school_id
                WHERE i.status = 'published' $df";
        $args = [];
        // All users only see their own school's library
        if ((int)$u['school_id'] > 0) { $sql .= " AND i.school_id = ?"; $args[] = (int)$u['school_id']; }
        if ($q !== '') { $sql .= " AND (i.title LIKE ? OR i.author LIKE ? OR i.category LIKE ? OR i.description LIKE ?)"; $args[] = "%$q%"; $args[] = "%$q%"; $args[] = "%$q%"; $args[] = "%$q%"; }
        if (in_array($type, ['book','notes','paper','slides','video','past_exam','tutorial'], true)) { $sql .= " AND i.type = ?"; $args[] = $type; }
        $sql .= " ORDER BY i.downloads DESC, i.created_at DESC LIMIT 80";
        $items = Database::all($sql, $args);
        $types = (int)$u['school_id'] > 0
            ? Database::all("SELECT type, COUNT(*) AS n FROM library_items WHERE status = 'published' AND school_id = ? GROUP BY type", [$u['school_id']])
            : Database::all("SELECT type, COUNT(*) AS n FROM library_items WHERE status = 'published' GROUP BY type");
        $myFavs = $u ? array_map(fn($f) => (int)$f['item_id'], Database::all("SELECT item_id FROM library_favorites WHERE user_id = ?", [$u['id']])) : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (($fid = (int)($_POST['favorite'] ?? 0))) {
                Database::run("INSERT IGNORE INTO library_favorites (user_id, item_id) VALUES (?,?)", [$u['id'], $fid]);
                flash('success', 'Added to favorites.');
                redirect('library');
            }
            if (($uf = (int)($_POST['unfavorite'] ?? 0))) {
                Database::delete('library_favorites', 'user_id = ? AND item_id = ?', [$u['id'], $uf]);
                flash('success', 'Removed from favorites.');
                redirect('library');
            }
        }
        Router::render('app/library/index', [
            'title' => 'Library', 'items' => $items, 'types' => $types, 'myFavs' => $myFavs, 'q' => $q, 'type' => $type,
        ]);
    }
}

class Ctl_item {
    public function run(): void {
        $u = require_login();
        $id = (int)($_GET['id'] ?? 0);
        $item = Database::one("SELECT i.*, s.name AS school_name FROM library_items i JOIN schools s ON s.id = i.school_id WHERE i.id = ?", [$id]);
        if (!$item) { flash('danger', 'Item not found.'); redirect('library'); }
        if ((int)$u['school_id'] > 0 && (int)$item['school_id'] !== (int)$u['school_id']) {
            flash('danger', 'Access denied.');
            redirect('library');
        }
        $related = (int)$u['school_id'] > 0
            ? Database::all(
                "SELECT * FROM library_items WHERE status = 'published' AND school_id = ? AND id != ? AND category = ? LIMIT 4",
                [$u['school_id'], $id, $item['category']])
            : Database::all(
                "SELECT * FROM library_items WHERE status = 'published' AND id != ? AND category = ? LIMIT 4", [$id, $item['category']]);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (($fid = (int)($_POST['favorite'] ?? 0))) {
                Database::run("INSERT IGNORE INTO library_favorites (user_id, item_id) VALUES (?,?)", [$u['id'], $fid]);
                flash('success', 'Added to favorites.');
                redirect('library/item&id=' . $id);
            }
        }
        Router::render('app/library/item', ['title' => $item['title'], 'item' => $item, 'related' => $related]);
    }
}
