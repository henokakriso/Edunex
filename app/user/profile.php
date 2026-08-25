<?php
/**
 * Public profile view — shows who a user really is (role, school, courses)
 * without exposing private contact data. Used by the calendar "View profile" link.
 */

class Ctl_profile {
    public function run(): void {
        $u = require_login();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { flash('danger', 'User not found.'); redirect('dashboard'); }

        $profile = Database::one(
            "SELECT id, first_name, last_name, role, student_id, school_id, status,
                    xp, level, bio, birth_date, gender, created_at, last_login
             FROM users WHERE id = ? AND status = 'active'", [$id]);
        if (!$profile) { flash('danger', 'User not found.'); redirect('dashboard'); }

        // Non-admins may only view profiles within their own school (or their own)
        if ($u['role'] !== 'ministry' && (int)$u['id'] !== $id
            && (int)($u['school_id'] ?? 0) !== (int)($profile['school_id'] ?? -1)) {
            flash('danger', 'Access denied.');
            redirect('dashboard');
        }

        $school = null;
        if (!empty($profile['school_id'])) {
            $school = Database::one(
                "SELECT id, name, type, city, address FROM schools WHERE id = ? AND status = 'active'",
                [$profile['school_id']]);
        }

        $courses = [];
        if ($profile['role'] === 'teacher') {
            $courses = Database::all(
                "SELECT c.title, c.level, s.name AS subject
                 FROM courses c LEFT JOIN subjects s ON s.id = c.subject_id
                 WHERE c.teacher_id = ? AND c.status = 'published' AND c.school_id = ?
                 ORDER BY c.title", [$id, $profile['school_id']]);
        } elseif ($profile['role'] === 'student') {
            $courses = Database::all(
                "SELECT c.title, c.level, s.name AS subject
                 FROM courses c LEFT JOIN subjects s ON s.id = c.subject_id
                 JOIN course_enrollments ce ON ce.course_id = c.id
                 WHERE ce.user_id = ? AND c.status = 'published'
                 ORDER BY c.title", [$id]);
        }

        $schoolType = ['school' => 'School', 'university' => 'University', 'college' => 'College',
                       'training' => 'Training centre', 'other' => 'Institution'];

        Router::render('user/profile', [
            'title' => 'Profile', 'profile' => $profile, 'school' => $school,
            'schoolType' => $schoolType, 'courses' => $courses,
        ]);
    }
}
