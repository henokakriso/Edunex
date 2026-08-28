<?php
/**
 * Teacher library: browse + upload (no delete)
 */
require_once __DIR__ . "/../library/library.php";

class Ctl_teacher_library extends Ctl_index {
    public function run(): void {
        $u = require_role(['teacher', 'lecturer', 'librarian', 'dean', 'hod']);
        if (!module_active((int)$u['school_id'], 'library')) { http_response_code(403); die('The Library module is not installed for your school.'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['upload_item'])) {
                $title = trim($_POST['title'] ?? '');
                $type = $_POST['type'] ?? 'book';
                if ($title === '') { flash('danger', 'Title required.'); redirect('teacher/library'); }
                [$ok, $path] = upload_file($_FILES['file'] ?? null, 'uploads/library', ['pdf','doc','docx','ppt','pptx','mp4','webm','mp3']);
                Database::insert('library_items', [
                    'school_id' => (int)$u['school_id'], 'title' => $title,
                    'type' => in_array($type, ['book','notes','paper','slides','video','past_exam','tutorial'], true) ? $type : 'book',
                    'author' => trim($_POST['author'] ?? ''), 'category' => trim($_POST['category'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'file_path' => $ok ? $path : null, 'status' => 'published',
                ]);
                flash('success', 'Item uploaded to library.');
                redirect('teacher/library');
            }
            if (($fid = (int)($_POST['favorite'] ?? 0))) {
                Database::run("INSERT IGNORE INTO library_favorites (user_id, item_id) VALUES (?,?)", [$u['id'], $fid]);
                flash('success', 'Added to favorites.');
                redirect('teacher/library');
            }
            if (($uf = (int)($_POST['unfavorite'] ?? 0))) {
                Database::delete('library_favorites', 'user_id = ? AND item_id = ?', [$u['id'], $uf]);
                flash('success', 'Removed from favorites.');
                redirect('teacher/library');
            }
        }

        $q = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? '';
        $df = demo_filter('i');
        $sql = "SELECT i.*, s.name AS school_name,
                    (SELECT COUNT(*) FROM library_favorites f WHERE f.item_id = i.id) AS favs
                FROM library_items i JOIN schools s ON s.id = i.school_id
                WHERE i.status = 'published' $df AND i.school_id = ?";
        $args = [(int)$u['school_id']];
        if ($q !== '') { $sql .= " AND (i.title LIKE ? OR i.author LIKE ? OR i.category LIKE ? OR i.description LIKE ?)"; $args[] = "%$q%"; $args[] = "%$q%"; $args[] = "%$q%"; $args[] = "%$q%"; }
        if (in_array($type, ['book','notes','paper','slides','video','past_exam','tutorial'], true)) { $sql .= " AND i.type = ?"; $args[] = $type; }
        $sql .= " ORDER BY i.downloads DESC, i.created_at DESC LIMIT 80";
        $items = Database::all($sql, $args);
        $types = Database::all("SELECT type, COUNT(*) AS n FROM library_items WHERE status = 'published' AND school_id = ? GROUP BY type", [$u['school_id']]);
        $myFavs = array_map(fn($f) => (int)$f['item_id'], Database::all("SELECT item_id FROM library_favorites WHERE user_id = ?", [$u['id']]));
        Router::render('app/library/index', [
            'title' => 'Library', 'items' => $items, 'types' => $types, 'myFavs' => $myFavs, 'q' => $q, 'type' => $type, 'canUpload' => true,
        ]);
    }
}
