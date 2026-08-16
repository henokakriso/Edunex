<?php
class Ctl_student_dashboard {
    public function run(): void {
        $u = require_role('student');
        $uid = (int)$u['id'];

        $courses = Database::all(
            "SELECT c.*, ce.progress, ce.completed,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS total_lessons,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id AND l.id IN (SELECT lesson_id FROM lesson_progress WHERE user_id = ? AND completed = 1)) AS done_lessons,
                    u.first_name AS tfirst, u.last_name AS tlast
             FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id JOIN users u ON u.id = c.teacher_id
             WHERE ce.user_id = ? ORDER BY ce.enrolled_at DESC", [$uid, $uid]);

        $upcomingExams = Database::all(
            "SELECT e.*, c.title AS course_title FROM exams e JOIN courses c ON c.id = e.course_id
             JOIN course_enrollments ce ON ce.course_id = e.course_id
             WHERE ce.user_id = ? AND e.status = 'published' AND e.end_time > NOW() ORDER BY e.start_time LIMIT 5", [$uid]);

        $assignments = Database::all(
            "SELECT a.*, c.title AS course_title,
                    (SELECT score FROM assignment_submissions s WHERE s.assignment_id = a.id AND s.student_id = ?) AS my_score,
                    (SELECT status FROM assignment_submissions s WHERE s.assignment_id = a.id AND s.student_id = ?) AS my_status
             FROM assignments a JOIN courses c ON c.id = a.course_id
             JOIN course_enrollments ce ON ce.course_id = a.course_id
             WHERE ce.user_id = ? AND a.due_date > NOW() ORDER BY a.due_date LIMIT 5", [$uid, $uid, $uid]);

        $announcements = Database::all(
            "SELECT an.*, u.first_name, u.last_name FROM announcements an JOIN users u ON u.id = an.author_id
             WHERE an.school_id = ? AND (an.audience = 'all' OR an.audience = 'students')
             ORDER BY an.pinned DESC, an.created_at DESC LIMIT 4", [$u['school_id']]);

        $attendance = Database::all(
            "SELECT date, status FROM attendance WHERE student_id = ? AND course_id IN (SELECT id FROM courses WHERE school_id = ?) ORDER BY date DESC LIMIT 10", [$uid, $u['school_id']]);

        $attStats = Database::one(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'present') AS present,
                    SUM(status = 'absent') AS absent,
                    SUM(status = 'late') AS late,
                    SUM(status = 'excused') AS excused
             FROM attendance WHERE student_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", [$uid]);

        $badges = Database::all(
            "SELECT b.*, ub.earned_at FROM user_badges ub JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ? ORDER BY ub.earned_at DESC LIMIT 6", [$uid]);

        $schedule = Database::all(
            "SELECT * FROM calendar_events WHERE (user_id = ? OR user_id IS NULL AND school_id = ?) AND DATE(start_at) = CURDATE() ORDER BY start_at", [$uid, $u['school_id']]);

        $goals = Database::all("SELECT * FROM goals WHERE user_id = ? AND completed = 0 LIMIT 3", [$uid]);

        // AI recommendations based on weakest course + attendance
        $aiRecs = [];
        $weakest = null;
        if ($courses) {
            usort($courses, fn($a, $b) => $a['progress'] <=> $b['progress']);
            $weakest = $courses[0];
            $aiRecs[] = ['book', 'Continue ' . $weakest['title'], 'You are ' . $weakest['progress'] . '% through. The AI tutor can explain ' . $weakest['title'] . ' — ask "Explain ' . $weakest['title'] . '"', 'courses/learn&id=' . $weakest['id']];
        }
        if (($attStats['absent'] ?? 0) > 0) {
            $aiRecs[] = ['clipboard', 'Attendance alert', 'You were absent ' . $attStats['absent'] . '× in the last 30 days. Perfect attendance earns you 350 XP!', 'student/attendance'];
        }
        if ($upcomingExams) {
            $aiRecs[] = ['note', 'Exam prep', 'Your next exam is "' . $upcomingExams[0]['title'] . '" in ' . $upcomingExams[0]['course_title'] . '. Ask me: "Am I ready for my exam?"', 'ai/tutor'];
        }
        $aiRecs[] = ['robot', 'Daily review', '5-minute AI quiz keeps your streak alive: "Create a quiz about ' . ($weakest ? $weakest['title'] : 'algebra') . '"', 'ai/tutor'];

        $recentActivity = Database::all(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$uid]);

        Router::render('app/student/dashboard', [
            'title' => 'Student Dashboard', 'courses' => $courses, 'upcomingExams' => $upcomingExams,
            'assignments' => $assignments, 'announcements' => $announcements, 'attendance' => $attendance,
            'attStats' => $attStats, 'badges' => $badges, 'schedule' => $schedule, 'goals' => $goals,
            'aiRecs' => $aiRecs, 'recentActivity' => $recentActivity,
        ]);
    }
}
