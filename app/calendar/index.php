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

        $monthStart = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $monthEnd = date('Y-m-d', mktime(0, 0, 0, $month + 1, 1, $year));

        $events = Database::all(
            "SELECT c.*, u.first_name AS creator_first, u.last_name AS creator_last, u.role AS creator_role,
                    c.event_type AS type, c.gregorian_start AS start_at, c.gregorian_end AS end_at,
                    c.start_time, c.end_time
             FROM calendar_events c
             LEFT JOIN users u ON u.id = c.created_by
             WHERE c.school_id = ?
               AND c.gregorian_start >= ? AND c.gregorian_start < ?
               AND c.status = 'published'
             ORDER BY c.gregorian_start", [$u['school_id'] ?? 0, $monthStart, $monthEnd]);

        $examSql = "SELECT e.id, e.title, e.start_time AS start_at, e.type AS exam_type, c.title AS course_title
                    FROM exams e JOIN courses c ON c.id = e.course_id
                    WHERE e.start_time IS NOT NULL AND e.start_time >= ? AND e.start_time < ?";
        $examArgs = [$monthStart, $monthEnd];
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
        foreach ($events as $ev) {
            $day = (int)date('j', strtotime($ev['start_at']));
            $byDay[$day][] = $ev;
        }
        foreach ($exams as $ex) {
            $day = (int)date('j', strtotime($ex['start_at']));
            $byDay[$day][] = [
                'id' => 'x' . $ex['id'], 'type' => 'exam', 'title' => 'EXAM: ' . $ex['title'],
                'start_at' => $ex['start_at'], 'all_day' => 0, 'location' => $ex['course_title'], 'auto' => true,
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_event'])) {
                if (!in_array($u['role'], ['regional', 'principal', 'teacher'], true)) {
                    flash('danger', 'Only teachers, directors and admins can create events.');
                    redirect('calendar&month=' . $month . '&year=' . $year);
                }
                $start = str_replace('T', ' ', $_POST['start_at'] ?? '');
                $end = $_POST['end_at'] ? str_replace('T', ' ', $_POST['end_at']) : null;
                $startDate = substr($start, 0, 10);
                $startTime = substr($start, 11, 8) ?: null;
                Database::insert('calendar_events', [
                    'school_id' => my_school_id(), 'created_by' => $uid,
                    'title' => trim($_POST['title']), 'event_type' => $_POST['type'] ?? 'event',
                    'category' => $_POST['type'] ?? 'other',
                    'gregorian_start' => $startDate,
                    'gregorian_end' => $end ? substr($end, 0, 10) : null,
                    'start_time' => $startTime,
                    'end_time' => $end ? substr($end, 11, 8) : null,
                    'all_day' => !empty($_POST['all_day']) ? 1 : 0,
                    'status' => 'published',
                ]);
                flash('success', 'Event added.');
                redirect('calendar&month=' . $month . '&year=' . $year);
            }
            if (($del = (int)($_POST['delete_event'] ?? 0))) {
                Database::delete('calendar_events', 'id = ? AND (created_by = ?)', [$del, $uid]);
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
