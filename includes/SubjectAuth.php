<?php
/** Teacher subject-authorisation helpers (director-assigned subjects). */

class SubjectAuth {
    /** Authorised subject ids for a teacher. */
    public static function ids(int $uid): array {
        return array_map('intval', array_column(Database::all(
            "SELECT s.id FROM teacher_subjects ts JOIN subjects s ON s.id = ts.subject_id
             WHERE ts.teacher_id = ? AND s.status = 'active'", [$uid]), 'id'));
    }

    public static function isAuthorized(int $uid, int $subjectId): bool {
        if ($subjectId <= 0) return false;
        return (bool)Database::one(
            "SELECT 1 FROM teacher_subjects ts JOIN subjects s ON s.id = ts.subject_id
             WHERE ts.teacher_id = ? AND ts.subject_id = ? AND s.status = 'active'", [$uid, $subjectId]);
    }

    /** Courses owned by the teacher whose subject is authorised. */
    public static function courses(int $uid): array {
        return Database::all(
            "SELECT c.id, c.title, c.subject_id, s.name AS subject_name
             FROM courses c JOIN subjects s ON s.id = c.subject_id
             WHERE c.teacher_id = ? AND c.subject_id IN (
                 SELECT subject_id FROM teacher_subjects WHERE teacher_id = ?
             ) ORDER BY s.name, c.title", [$uid, $uid]);
    }
}
