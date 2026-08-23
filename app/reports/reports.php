<?php
/**
 * Reports center: list generated reports + CSV export of ad-hoc queries
 */

class Ctl_index {
    public function run(): void {
        $u = require_role('sysadmin', 'teacher', 'director');
        $uid = (int)$u['id'];
        if (isset($_GET['download'])) {
            $rid = (int)$_GET['download'];
            $r = Database::one("SELECT * FROM reports WHERE id = ? AND user_id = ?", [$rid, $uid]);
            if ($r && $r['file_path']) {
                $abs = safe_storage_path($r['file_path']);
                if ($abs && is_file($abs)) {
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="' . basename($abs) . '"');
                    readfile($abs);
                    exit;
                }
            }
            flash('danger', 'Report file not found.');
            redirect('reports/index');
        }
        $reports = Database::all(
            "SELECT r.*, s.name AS school_name FROM reports r JOIN schools s ON s.id = r.school_id
             WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 100", [$uid]);
        $schoolName = null;
        if ($u['role'] === 'director') {
            $schoolName = (string)Database::scalar("SELECT name FROM schools WHERE id = ?", [$u['school_id']], '');
        }
        Router::render('app/reports/index', ['title' => 'Reports', 'reports' => $reports, 'school_name' => $schoolName]);
    }
}

class Ctl_export {
    public function run(): void {
        $u = require_role('sysadmin', 'teacher', 'director');
        $uid = (int)$u['id'];
        $type = $_GET['type'] ?? 'courses';
        $format = $_GET['format'] ?? 'csv';
        $role = $u['role'];

        $rows = [];
        if ($role === 'teacher') {
            // teachers only see THEIR authorised-subject courses, the students
            // enrolled in them, and the exams of those courses
            $ids = array_map('intval', array_column(SubjectAuth::courses($uid), 'id'));
            if (!$ids) {
                flash('danger', 'You have no authorised courses yet — ask the director to assign you subjects before exporting.');
                redirect('reports/index');
            }
            $in = implode(',', $ids);
            $rows = match ($type) {
                'courses' => Database::all(
                    "SELECT c.title, c.code, c.status, s.name AS school, u.first_name AS teacher_first, u.last_name AS teacher_last,
                            (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
                     FROM courses c JOIN schools s ON s.id = c.school_id JOIN users u ON u.id = c.teacher_id
                     WHERE c.id IN ($in) ORDER BY c.created_at DESC"),
                'users' => Database::all(
                    "SELECT us.first_name, us.last_name, us.email, us.phone, us.role, us.status, us.student_id
                     FROM users us
                     WHERE us.role = 'student' AND us.id IN (
                         SELECT DISTINCT ce.user_id FROM course_enrollments ce WHERE ce.course_id IN ($in)
                     ) ORDER BY us.last_name, us.first_name"),
                'exams' => Database::all(
                    "SELECT e.title, c.title AS course, e.status, e.duration_min,
                            (SELECT COUNT(*) FROM exam_attempts t WHERE t.exam_id = e.id) AS attempts
                     FROM exams e JOIN courses c ON c.id = e.course_id
                     WHERE e.course_id IN ($in) ORDER BY e.created_at DESC"),
                default => [['error' => 'Unknown type']],
            };
        } elseif ($role === 'director') {
            // directors see only their own school
            $rows = match ($type) {
                'courses' => Database::all(
                    "SELECT c.title, c.code, c.status, s.name AS school, u.first_name AS teacher_first, u.last_name AS teacher_last,
                            (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
                     FROM courses c JOIN schools s ON s.id = c.school_id JOIN users u ON u.id = c.teacher_id
                     WHERE c.school_id = ? ORDER BY c.created_at DESC", [$u['school_id']]),
                'users' => Database::all(
                    "SELECT first_name, last_name, email, phone, role, status, student_id FROM users
                     WHERE school_id = ? ORDER BY role, last_name LIMIT 2000", [$u['school_id']]),
                'exams' => Database::all(
                    "SELECT e.title, c.title AS course, e.status, e.duration_min,
                            (SELECT COUNT(*) FROM exam_attempts t WHERE t.exam_id = e.id) AS attempts
                     FROM exams e JOIN courses c ON c.id = e.course_id
                     WHERE c.school_id = ? ORDER BY e.created_at DESC", [$u['school_id']]),
                default => [['error' => 'Unknown type']],
            };
        } else {
            // admins: platform-wide
            $rows = match ($type) {
                'courses' => Database::all(
                    "SELECT c.title, c.code, c.status, s.name AS school, u.first_name AS teacher_first, u.last_name AS teacher_last,
                            (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
                     FROM courses c JOIN schools s ON s.id = c.school_id JOIN users u ON u.id = c.teacher_id
                     ORDER BY c.created_at DESC LIMIT 500"),
                'users' => Database::all("SELECT first_name, last_name, email, phone, role, status, student_id FROM users ORDER BY role, last_name LIMIT 2000"),
                'exams' => Database::all(
                    "SELECT e.title, c.title AS course, e.status, e.duration_min,
                            (SELECT COUNT(*) FROM exam_attempts t WHERE t.exam_id = e.id) AS attempts
                     FROM exams e JOIN courses c ON c.id = e.course_id ORDER BY e.created_at DESC LIMIT 500"),
                default => [['error' => 'Unknown type']],
            };
        }
        if ($format === 'pdf') {
            $title = 'Edunex ' . ucfirst($type) . ' Report';
            $stamp = date('F j, Y \a\t g:i A');
            $rowNum = 0;
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . e($title) . '</title>';
            echo '<style>';
            echo '*{box-sizing:border-box;margin:0;padding:0}';
            echo 'body{font-family:system-ui,-apple-system,sans-serif;background:#f5f5f5;color:#222}';
            echo '.viewer-bar{position:sticky;top:0;z-index:100;background:#1a1a2e;color:#fff;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,.2)}';
            echo '.viewer-bar h1{font-size:15px;font-weight:600}';
            echo '.viewer-bar .btns{display:flex;gap:10px}';
            echo '.viewer-bar a,.viewer-bar button{background:#4361ee;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px}';
            echo '.viewer-bar a:hover,.viewer-bar button:hover{background:#3a56d4}';
            echo '.viewer-bar .btn-secondary{background:#555}.viewer-bar .btn-secondary:hover{background:#444}';
            echo '.report{max-width:1000px;margin:24px auto;background:#fff;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden}';
            echo '.report-header{padding:28px 32px 20px;border-bottom:2px solid #eee}';
            echo '.report-header h2{font-size:20px;margin-bottom:4px}';
            echo '.report-header .meta{color:#666;font-size:12px}';
            echo 'table{width:100%;border-collapse:collapse}th,td{padding:10px 14px;text-align:left;border-bottom:1px solid #eee;font-size:13px}';
            echo 'th{background:#f8f9fa;font-weight:600;color:#444;position:sticky;top:52px}';
            echo 'tr:hover{background:#f8f9fa}';
            echo '.row-num{color:#999;width:40px;text-align:center}';
            echo '.footer{padding:16px 32px;border-top:2px solid #eee;text-align:center;color:#888;font-size:11px;line-height:1.6}';
            echo '.footer a{color:#4361ee;text-decoration:none}';
            echo '@media print{.viewer-bar{display:none!important}.report{box-shadow:none;margin:0;border-radius:0}body{background:#fff}}';
            echo '</style></head><body>';
            echo '<div class="viewer-bar">';
            echo '<h1>' . e($title) . '</h1>';
            echo '<div class="btns">';
            echo '<button class="btn-secondary" onclick="history.back()">← Back</button>';
            echo '<a href="javascript:window.print()">🖨 Print</a>';
            echo '<a href="javascript:downloadPDF()">⬇ Download PDF</a>';
            echo '</div></div>';
            echo '<div class="report">';
            echo '<div class="report-header">';
            echo '<h2>' . e($title) . '</h2>';
            echo '<p class="meta">Generated: ' . e($stamp) . ' · ' . count($rows) . ' records · Henok Akriso</p>';
            echo '</div>';
            if ($rows) {
                echo '<div style="overflow-x:auto"><table><thead><tr><th class="row-num">#</th>';
                foreach (array_keys($rows[0]) as $k) echo '<th>' . e(ucwords(str_replace('_', ' ', $k))) . '</th>';
                echo '</tr></thead><tbody>';
                foreach ($rows as $r) {
                    $rowNum++;
                    echo '<tr><td class="row-num">' . $rowNum . '</td>';
                    foreach (array_values($r) as $v) echo '<td>' . e(is_array($v) ? implode(', ', $v) : $v) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table></div>';
            } else {
                echo '<p style="padding:24px;text-align:center;color:#888">No data found.</p>';
            }
            echo '<div class="footer">';
            echo '<p><b>Henok Akriso</b> · henokakriso.com</p>';
            echo '<p>All system is opensourced under <a href="https://github.com/henokakriso/Edunex" target="_blank">ARWE-PL License</a></p>';
            echo '</div></div>';
            echo '<script>';
            echo 'function downloadPDF(){';
            echo '  var opt={margin:[10,10],filename:"edunex_' . $type . '_' . date('Ymd_His') . '.pdf",html2canvas:{scale:2},jsPDF:{unit:"mm",format:"a4",orientation:"landscape"}};';
            echo '  if(typeof html2pdf!=="undefined"){html2pdf().set(opt).from(document.querySelector(".report")).save();}';
            echo '  else{var s=document.createElement("script");s.src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js";s.onload=function(){html2pdf().set(opt).from(document.querySelector(".report")).save();};document.head.appendChild(s);}';
            echo '}';
            echo '</script>';
            echo '</body></html>';
            exit;
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="edunex_' . $type . '_' . date('Ymd_His') . '.csv"');
        $fp = fopen('php://output', 'w');
        if ($rows) {
            fputcsv($fp, array_keys($rows[0]));
            foreach ($rows as $r) fputcsv($fp, array_map(fn($v) => is_array($v) ? json_encode($v) : $v, array_values($r)));
        }
        fclose($fp);
        exit;
    }
}
