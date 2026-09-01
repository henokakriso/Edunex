<?php
/**
 * Reports center: list generated reports + CSV export of ad-hoc queries
 */

class Ctl_index {
    public function run(): void {
        $u = require_role('ministry', 'teacher', 'principal');
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
        if ($u['role'] === 'principal') {
            $schoolName = (string)Database::scalar("SELECT name FROM schools WHERE id = ?", [$u['school_id']], '');
        }
        Router::render('app/reports/index', ['title' => 'Reports', 'reports' => $reports, 'school_name' => $schoolName]);
    }
}

class Ctl_export {
    public function run(): void {
        $u = require_role('ministry', 'teacher', 'principal');
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
        } elseif ($role === 'principal') {
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
            $pdf_title = 'Edunex ' . ucfirst($type) . ' Report';
            $pdf_subtitle = $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)) . ' Report';
            $pdf_filename = 'edunex_' . $type . '_' . date('Ymd_His') . '.pdf';
            $pdf_record_count = count($rows);
            $pdf_user_name = full_name($__u ?? []);
            $rowNum = 0;
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . e($pdf_title) . '</title>';
            echo '<style>body{margin:0;padding:20px;background:var(--bg,#f5f5f5);color:var(--text,#222)}</style>';
            echo '</head><body>';
            require_once BASE_PATH . '/includes/pdf_template.php';
            echo '<div class="pdf-toolbar no-print">';
            echo '<button class="pdf-toolbar-btn pdf-toolbar-btn--back" onclick="history.back()">← Back</button>';
            echo '<button class="pdf-toolbar-btn pdf-toolbar-btn--dl" onclick="downloadPDF()">⬇ Download PDF</button>';
            echo '<button class="pdf-toolbar-btn pdf-toolbar-btn--print" onclick="window.print()">🖨 Print</button>';
            echo '</div>';
            echo '<div class="pdf-paper" id="pdf-content">';
            echo '<div class="pdf-watermark"><img class="wm-logo" src="' . $pdf_logo_black . '" alt="EDUNEX"><div class="wm-edunex">EDUNEX</div><div class="wm-url">www.henokakriso.com</div></div>';
            echo '<div class="pdf-header"><div class="logos-row">';
            echo '<div class="flag-wrap"><img class="logo-img flag-img" src="' . $pdf_ethiopian_flag . '" alt="Ethiopia"></div>';
            echo '<div class="text-center"><h2>Federal Democratic Republic of Ethiopia</h2><div style="font-size:10px;color:var(--text-secondary);letter-spacing:.3px">Ministry of Education</div></div>';
            echo '<div class="ministry-wrap"><img class="logo-img" src="' . $pdf_ministry_logo . '" alt="Ministry"></div>';
            echo '</div><div class="pdf-sub">' . e($pdf_subtitle) . '</div></div>';
            echo '<div class="pdf-meta">';
            echo '<span>Document: <strong>' . e($pdf_doc_id) . '</strong></span>';
            echo '<span class="meta-dot"></span>';
            echo '<span>Generated: <strong>' . e($pdf_stamp) . '</strong></span>';
            echo '<span class="meta-dot"></span>';
            echo '<span>Records: <strong>' . e($pdf_record_count) . '</strong></span>';
            echo '</div>';
            if ($rows) {
                $colMap = ['first_name'=>'col-name','last_name'=>'col-name','name'=>'col-name','email'=>'col-email','phone'=>'col-phone','student_id'=>'col-id','school_name'=>'col-name','school'=>'col-name','course'=>'col-name','course_title'=>'col-name'];
                echo '<div style="overflow-x:auto"><table><thead><tr><th>#</th>';
                foreach (array_keys($rows[0]) as $k) {
                    echo '<th>' . e(ucwords(str_replace('_', ' ', $k))) . '</th>';
                }
                echo '</tr></thead><tbody>';
                foreach ($rows as $r) {
                    $rowNum++;
                    echo '<tr><td>' . $rowNum . '</td>';
                    foreach ($r as $k => $v) {
                        echo '<td>' . e(is_array($v) ? implode(', ', $v) : $v) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody></table></div>';
            } else {
                echo '<p style="text-align:center;color:var(--text-secondary);padding:40px 0;position:relative;z-index:1">No data found.</p>';
            }
            echo '<div class="pdf-footer"><span>EDUNEX LMS · henockakriso.com · GitHub @henokakriso · ARWE-PL Licensed [' . date('Y') . ']</span><span>Page 1 of 1</span></div>';
            echo '</div></body></html>';
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
