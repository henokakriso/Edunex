<?php
/**
 * Calendar: month view + personal events + school events
 */

class Ctl_index {
    public function run(): void {
        $u = require_login();
        $uid = (int)$u['id'];
        $month = (int)($_GET['month'] ?? date('n'));
        $year = (int)($_GET['year'] ?? date('Y'));
        if ($month < 1) $month = 1; if ($month > 12) $month = 12;
        $first = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $first);
        $startDow = (int)date('w', $first);

        $events = Database::all(
            "SELECT c.*, u.first_name AS creator_first, u.last_name AS creator_last, u.role AS creator_role
             FROM calendar_events c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE (c.user_id = ? OR c.user_id IS NULL OR c.school_id = ?)
               AND c.start_at >= ? AND c.start_at < ?
             ORDER BY c.start_at", [$uid, $u['school_id'],
                date('Y-m-d', mktime(0, 0, 0, $month, 1, $year)),
                date('Y-m-d', mktime(0, 0, 0, $month + 1, 1, $year))]);

        $examSql = "SELECT e.id, e.title, e.start_time AS start_at, e.type AS exam_type, c.title AS course_title
                    FROM exams e JOIN courses c ON c.id = e.course_id
                    WHERE e.start_time IS NOT NULL AND e.start_time >= ? AND e.start_time < ?";
        $examArgs = [date('Y-m-d', mktime(0, 0, 0, $month, 1, $year)), date('Y-m-d', mktime(0, 0, 0, $month + 1, 1, $year))];
        if (in_array($u['role'], ['student', 'parent'], true)) {
            $uidFor = $u['role'] === 'parent' ? ($u['child_id'] ?? 0) : $uid;
            $examSql .= " AND c.id IN (SELECT course_id FROM course_enrollments WHERE user_id = ?)";
            $examArgs[] = $uidFor ?: 0;
        } elseif ($u['role'] === 'teacher') {
            $examSql .= " AND e.teacher_id = ?";
            $examArgs[] = $uid;
        } elseif (in_array($u['role'], ['regional', 'principal'], true)) {
            if (!empty($u['school_id'])) {
                $examSql .= " AND c.school_id = ?";
                $examArgs[] = $u['school_id'];
            }
        }
        $exams = Database::all($examSql, $examArgs);

        $byDay = [];
        foreach ($events as $ev) $byDay[(int)date('j', strtotime($ev['start_at']))][] = $ev;
        foreach ($exams as $ex) {
            $byDay[(int)date('j', strtotime($ex['start_at']))][] = [
                'id' => 'x' . $ex['id'], 'type' => 'exam', 'title' => 'EXAM: ' . $ex['title'],
                'start_at' => $ex['start_at'], 'all_day' => 0, 'location' => $ex['course_title'], 'auto' => true,
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_event'])) {
                // Only teachers, admins (super admin) and directors may create events.
                if (!in_array($u['role'], ['regional', 'principal', 'teacher'], true)) {
                    flash('danger', 'Only teachers, directors and admins can create events.');
                    redirect('calendar&month=' . $month . '&year=' . $year);
                }
                $start = str_replace('T', ' ', $_POST['start_at'] ?? '');
                $end = $_POST['end_at'] ? str_replace('T', ' ', $_POST['end_at']) : null;
                Database::insert('calendar_events', [
                    'school_id' => my_school_id(), 'user_id' => $uid,
                    'title' => trim($_POST['title']), 'type' => $_POST['type'] ?? 'event',
                    'start_at' => $start, 'end_at' => $end,
                    'all_day' => !empty($_POST['all_day']) ? 1 : 0,
                    'location' => trim($_POST['location'] ?? ''), 'description' => trim($_POST['description'] ?? ''),
                ]);
                flash('success', 'Event added.');
                redirect('calendar&month=' . $month . '&year=' . $year);
            }
            if (($del = (int)($_POST['delete_event'] ?? 0))) {
                Database::delete('calendar_events', 'id = ? AND (user_id = ? OR user_id IS NULL)', [$del, $uid]);
                flash('success', 'Event deleted.');
                redirect('calendar&month=' . $month . '&year=' . $year);
            }
        }

        Router::render('app/calendar/index', [
            'title' => 'Calendar', 'month' => $month, 'year' => $year, 'daysInMonth' => $daysInMonth,
            'startDow' => $startDow, 'byDay' => $byDay, 'events' => $events, 'exams' => $exams,
        ]);
    }
}
