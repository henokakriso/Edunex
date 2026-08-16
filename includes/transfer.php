<?php
/**
 * School transfer engine — copies a leaving student's full academic record
 * to their new account at the target school and stores a portable JSON snapshot.
 */

function transfer_copy_record(array $req, int $actorId): array {
    $oldUid = (int)($req['source_student_id'] ?? 0);
    $newUid = (int)$req['student_id'];
    if (!$oldUid || $oldUid === $newUid) {
        return ['ok' => false, 'error' => 'No source account linked to this transfer request.'];
    }
    $old = Database::one("SELECT * FROM users WHERE id = ?", [$oldUid]);
    if (!$old) return ['ok' => false, 'error' => 'Source account no longer exists.'];

    Database::transaction(function () use ($oldUid, $newUid, $old) {
        // 1. Student identity record
        Database::run(
            "UPDATE users SET xp = ?, level = ?, streak = ?, streak_last = ?,
                    bio = COALESCE(NULLIF(bio,''), NULLIF(?, '')), birth_date = COALESCE(birth_date, ?),
                    gender = COALESCE(gender, ?), privacy = COALESCE(privacy, ?), language = COALESCE(language, 'en')
             WHERE id = ?",
            [
                $old['xp'], $old['level'], $old['streak'], $old['streak_last'],
                $old['bio'] ?? '', $old['birth_date'] ?? null, $old['gender'] ?? null,
                is_string($old['privacy'] ?? null) ? $old['privacy'] : ($old['privacy'] ? json_encode($old['privacy']) : null),
                $newUid,
            ]);

        // 2. Badges (global catalog — portable)
        Database::run("INSERT IGNORE INTO user_badges (user_id, badge_id, earned_at)
                         SELECT ?, badge_id, earned_at FROM user_badges WHERE user_id = ?", [$newUid, $oldUid]);

        // 3. Goals
        Database::run("INSERT IGNORE INTO goals (user_id, title, target, current, unit, due_date, completed)
                         SELECT ?, title, target, current, unit, due_date, completed FROM goals WHERE user_id = ?", [$newUid, $oldUid]);

        // 4. Certificates (course remains in global catalog — reissue to new account)
        Database::run("INSERT IGNORE INTO certificates (student_id, course_id, cert_code, qr_hash, issued_at, grade)
                         SELECT ?, course_id, CONCAT(cert_code, '-T'), CONCAT(qr_hash, '-T'), issued_at, grade
                         FROM certificates WHERE student_id = ?", [$newUid, $oldUid]);

        // 5. Homeroom group from the source school (kept as history on the new account)
        Database::run("UPDATE users SET department_id = COALESCE(department_id, ?), group_id = group_id WHERE id = ?",
            [$old['department_id'] ?? null, $newUid]);
    });

    // 6. Portable record snapshot (grades, attendance, exams, progress, achievements)
    $snapshot = transfer_build_snapshot($oldUid, $newUid);
    Database::run(
        "UPDATE transfer_requests SET status = 'completed', record_snapshot = ?, completed_at = ?, approved_by = ? WHERE id = ?",
        [json_encode($snapshot), date('Y-m-d H:i:s'), $actorId, $req['id']]);

    return ['ok' => true, 'snapshot' => $snapshot];
}

function transfer_build_snapshot(int $oldUid, int $newUid): array {
    $grades = Database::all(
        "SELECT e.title AS exam, COALESCE(c.title, 'General') AS subject, a.score, a.total_points AS total, a.status, a.submitted_at
         FROM exam_attempts a JOIN exams e ON e.id = a.exam_id
         JOIN courses c ON c.id = e.course_id
         WHERE a.student_id = ? AND a.status IN ('submitted','graded') ORDER BY a.submitted_at DESC LIMIT 100", [$oldUid]);
    $attendance = Database::one(
        "SELECT SUM(status = 'present') AS present, SUM(status = 'absent') AS absent, SUM(status = 'late') AS late, COUNT(*) AS total
         FROM attendance WHERE student_id = ?", [$oldUid]);
    $certs = Database::all(
        "SELECT c.cert_code, c.issued_at, c.grade, co.title AS course
         FROM certificates c JOIN courses co ON co.id = c.course_id WHERE c.student_id = ?", [$oldUid]);
    $badges = Database::all(
        "SELECT b.name, b.icon, b.category, ub.earned_at
         FROM user_badges ub JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ?", [$oldUid]);
    $courses = Database::all(
        "SELECT co.title, ce.progress, ce.completed, ce.enrolled_at
         FROM course_enrollments ce JOIN courses co ON co.id = ce.course_id WHERE ce.user_id = ?", [$oldUid]);
    $old = Database::one("SELECT first_name, last_name, student_id, xp, level, streak, school_id FROM users WHERE id = ?", [$oldUid]);
    $fromSchool = $old ? Database::scalar("SELECT name FROM schools WHERE id = ?", [$old['school_id']], '') : '';
    return [
        'from_school' => $fromSchool,
        'student' => $old ? $old['first_name'] . ' ' . $old['last_name'] : '',
        'student_id' => $old['student_id'] ?? '',
        'xp' => (int)($old['xp'] ?? 0), 'level' => (int)($old['level'] ?? 1), 'streak' => (int)($old['streak'] ?? 0),
        'grades' => array_map(fn($g) => [
            'exam' => $g['exam'], 'subject' => $g['subject'], 'score' => (float)$g['score'],
            'total' => (float)$g['total'], 'status' => $g['status'],
            'date' => date('M j, Y', strtotime($g['submitted_at'] ?? $g['submitted_at'])),
        ], $grades),
        'attendance' => $attendance ? [
            'present' => (int)$attendance['present'], 'absent' => (int)$attendance['absent'],
            'late' => (int)$attendance['late'], 'total' => (int)$attendance['total'],
        ] : null,
        'certificates' => array_map(fn($c) => [
            'code' => $c['cert_code'], 'course' => $c['course'], 'grade' => $c['grade'],
            'issued' => date('M j, Y', strtotime($c['issued_at'])),
        ], $certs),
        'badges' => array_map(fn($b) => [
            'name' => $b['name'], 'icon' => $b['icon'], 'category' => $b['category'],
        ], $badges),
        'courses' => array_map(fn($c) => [
            'title' => $c['title'], 'progress' => (float)$c['progress'],
            'completed' => (bool)$c['completed'], 'enrolled' => date('M j, Y', strtotime($c['enrolled_at'])),
        ], $courses),
        'generated_at' => date('Y-m-d H:i:s'),
    ];
}
