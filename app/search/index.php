<?php
/**
 * Search: unified search across the platform
 */

class Ctl_index {
    public function run(): void {
        $u = require_login();
        $q = trim((string)($_GET['q'] ?? ''));
        $results = ['courses' => [], 'lessons' => [], 'library' => [], 'users' => [], 'topics' => [], 'exams' => [], 'announcements' => [], 'files' => []];
        if (mb_strlen($q) >= 2) {
            $like = '%' . $q . '%';
            $sid = (int)$u['school_id'];
            $results['courses'] = Database::all("SELECT id, title, code, description, status FROM courses WHERE school_id = ? AND (title LIKE ? OR code LIKE ?) AND status = 'published' LIMIT 10", [$sid, $like, $like]);
            $results['lessons'] = Database::all("SELECT l.id, l.title, l.course_id, c.title AS course_title FROM lessons l JOIN courses c ON c.id = l.course_id WHERE c.school_id = ? AND l.title LIKE ? LIMIT 10", [$sid, $like]);
            $results['library'] = Database::all("SELECT id, title, type, author FROM library_items WHERE school_id = ? AND title LIKE ? LIMIT 10", [$sid, $like]);
            $results['users'] = Database::all("SELECT id, first_name, last_name, role, student_id FROM users WHERE school_id = ? AND (first_name LIKE ? OR last_name LIKE ? OR student_id LIKE ?) AND role != 'guest' LIMIT 10", [$sid, $like, $like, $like]);
            $results['topics'] = Database::all("SELECT t.id, t.title, t.course_id, c.title AS course_title FROM forum_topics t JOIN courses c ON c.id = t.course_id WHERE c.school_id = ? AND t.title LIKE ? LIMIT 6", [$sid, $like]);
            $results['exams'] = Database::all("SELECT e.id, e.title, e.type, e.status, c.title AS course_title FROM exams e JOIN courses c ON c.id = e.course_id WHERE c.school_id = ? AND e.title LIKE ? LIMIT 8", [$sid, $like]);
            $results['announcements'] = Database::all(
                "SELECT a.id, a.title, a.created_at FROM announcements a WHERE a.school_id = ? AND a.title LIKE ? LIMIT 5", [$sid, $like]);
            if ($u['role'] !== 'student') {
                $results['files'] = Database::all("SELECT id, name, original_name, is_folder FROM files WHERE school_id = ? AND deleted_at IS NULL AND name LIKE ? LIMIT 10", [$sid, $like]);
            }
        }
        Router::render('app/search/index', ['title' => 'Search', 'q' => $q, 'results' => $results]);
    }
}
