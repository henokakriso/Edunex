<?php
/**
 * EDUNEX Grading & Results System — Teacher Module
 * Complete assessment engine: create assessments, enter marks, calculate semesters/finals
 */

/* =============== HELPER: Letter grade from percentage =============== */
function grading_letter(float $pct, float $pass = 50): string {
    if ($pct >= 90) return 'A+';
    if ($pct >= 80) return 'A';
    if ($pct >= 75) return 'B+';
    if ($pct >= 70) return 'B';
    if ($pct >= 65) return 'C+';
    if ($pct >= 60) return 'C';
    if ($pct >= 55) return 'D+';
    if ($pct >= 50) return 'D';
    return 'F';
}

function grading_pass(float $pct, float $pass = 50): bool {
    return $pct >= $pass;
}

/* =============== HELPER: Calculate semester total for student/course =============== */
function grading_calc_semester(int $studentId, int $courseId, int $semester, ?int $academicYearId = null): ?float {
    $where = "g.student_id = ? AND a.course_id = ? AND a.status = 'published' AND g.status IN ('submitted','verified','published','locked')";
    $args = [$studentId, $courseId];
    if ($academicYearId) { $where .= " AND a.academic_year_id = ?"; $args[] = $academicYearId; }

    if ($semester === 1) {
        $where .= " AND a.type_slug IN ('r1','r2')";
    } else {
        $where .= " AND a.type_slug IN ('r3','r4')";
    }

    $rounds = Database::all(
        "SELECT a.type_slug, a.max_mark, g.mark
         FROM grades g JOIN assessments a ON a.id = g.assessment_id
         WHERE $where", $args);

    if (empty($rounds)) return null;

    $totalPct = 0;
    $count = 0;
    foreach ($rounds as $r) {
        $max = (float)$r['max_mark'];
        $mark = (float)$r['mark'];
        if ($max > 0) {
            $totalPct += ($mark / $max) * 100;
            $count++;
        }
    }
    return $count > 0 ? round($totalPct / $count, 2) : null;
}

/* =============== HELPER: Calculate final result for student/course =============== */
function grading_calc_final(int $studentId, int $courseId, ?int $academicYearId = null): array {
    $s1 = grading_calc_semester($studentId, $courseId, 1, $academicYearId);
    $s2 = grading_calc_semester($studentId, $courseId, 2, $academicYearId);

    $bonusRow = Database::one(
        "SELECT COALESCE(SUM(points), 0) AS total FROM bonus_entries
         WHERE student_id = ? AND course_id = ? AND status = 'approved'" .
         ($academicYearId ? " AND academic_year_id = ?" : ""),
        $academicYearId ? [$studentId, $courseId, $academicYearId] : [$studentId, $courseId]);
    $bonus = (float)($bonusRow['total'] ?? 0);

    $final = null;
    if ($s1 !== null && $s2 !== null) {
        $final = round(($s1 + $s2) / 2, 2);
    } elseif ($s1 !== null) {
        $final = $s1;
    } elseif ($s2 !== null) {
        $final = $s2;
    }

    $adjusted = $final !== null ? min(100, $final + $bonus) : null;
    $passMark = (float)Database::scalar("SELECT pass_mark FROM grading_config WHERE school_id = (SELECT school_id FROM users WHERE id = ?) LIMIT 1", [$studentId], 50);

    return [
        'semester1' => $s1,
        'semester2' => $s2,
        'final_score' => $final,
        'bonus' => $bonus,
        'adjusted' => $adjusted,
        'letter' => $adjusted !== null ? grading_letter($adjusted, $passMark) : null,
        'pass' => $adjusted !== null ? grading_pass($adjusted, $passMark) : null,
    ];
}

/* =============== HELPER: Audit log =============== */
function grading_audit(int $gradeId, int $studentId, int $assessmentId, string $action, ?float $oldMark, ?float $newMark, ?string $oldStatus, ?string $newStatus, int $userId, ?string $reason = null): void {
    Database::insert('grade_audit', [
        'grade_id' => $gradeId,
        'student_id' => $studentId,
        'assessment_id' => $assessmentId,
        'action' => $action,
        'old_mark' => $oldMark,
        'new_mark' => $newMark,
        'old_status' => $oldStatus,
        'new_status' => $newStatus,
        'performed_by' => $userId,
        'reason' => $reason,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
}

/* =============== HELPER: Recalculate semester + final =============== */
function grading_recalc(int $studentId, int $courseId, ?int $academicYearId = null): void {
    $classId = (int)Database::scalar("SELECT class_id FROM assessments WHERE course_id = ? LIMIT 1", [$courseId], 0);
    foreach ([1, 2] as $sem) {
        $total = grading_calc_semester($studentId, $courseId, $sem, $academicYearId);
        $count = (int)Database::scalar(
            "SELECT COUNT(*) FROM grades g JOIN assessments a ON a.id = g.assessment_id
             WHERE g.student_id = ? AND a.course_id = ? AND g.status IN ('submitted','verified','published','locked')
             AND a.type_slug IN " . ($sem === 1 ? "('r1','r2')" : "('r3','r4')"),
            [$studentId, $courseId]);
        Database::run("INSERT INTO semester_results (student_id, course_id, class_id, academic_year_id, semester, total, assessment_count)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE total = VALUES(total), assessment_count = VALUES(assessment_count)",
            [$studentId, $courseId, $classId, $academicYearId, $sem, $total, $count]);
    }

    $final = grading_calc_final($studentId, $courseId, $academicYearId);
    Database::run("INSERT INTO final_results (student_id, course_id, class_id, academic_year_id, semester1_total, semester2_total, final_score, bonus_points, adjusted_score, letter_grade, is_pass)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE semester1_total=VALUES(semester1_total), semester2_total=VALUES(semester2_total), final_score=VALUES(final_score), bonus_points=VALUES(bonus_points), adjusted_score=VALUES(adjusted_score), letter_grade=VALUES(letter_grade), is_pass=VALUES(is_pass)",
        [$studentId, $courseId, $classId, $academicYearId, $final['semester1'], $final['semester2'], $final['final_score'], $final['bonus'], $final['adjusted'], $final['letter'], $final['pass']]);
}

/* =============== GRADING: Main landing page =============== */
class Ctl_grading {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];

        // Teacher's assigned courses
        $courses = Database::all(
            "SELECT c.id, c.title, c.code, c.level, s.name AS school_name,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
             FROM courses c JOIN schools s ON s.id = c.school_id
             WHERE c.teacher_id = ? AND c.status = 'published' ORDER BY c.title", [$uid]);

        $selectedCourse = (int)($_GET['course'] ?? 0);
        $assessments = [];
        $students = [];
        $semesterStats = [1 => null, 2 => null];
        $finalStats = null;
        $courseTotalUsed = 0;
        $courseTotalRemaining = 100;

        if ($selectedCourse) {
            // Verify teacher owns this course
            $ownCourse = Database::scalar("SELECT id FROM courses WHERE id = ? AND teacher_id = ?", [$selectedCourse, $uid]);
            if (!$ownCourse) { flash('danger', 'Access denied.'); redirect('teacher/grading'); }

            // Get assessments for this course
            $assessments = Database::all(
                "SELECT a.*, ats.label AS type_label,
                        (SELECT COUNT(*) FROM grades g WHERE g.assessment_id = a.id AND g.mark IS NOT NULL) AS graded_count,
                        (SELECT COUNT(*) FROM grades g WHERE g.assessment_id = a.id) AS total_grades,
                        (SELECT ROUND(AVG(g.percentage),1) FROM grades g WHERE g.assessment_id = a.id AND g.mark IS NOT NULL) AS avg_pct
                 FROM assessments a
                 LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
                 WHERE a.course_id = ? AND a.status IN ('published','draft')
                 ORDER BY ats.sort_order, a.assessment_date", [$selectedCourse]);

            // Get enrolled students
            $students = Database::all(
                "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
                 FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
                 WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$selectedCourse]);

            // Calculate semester/final stats
            foreach ([1, 2] as $sem) {
                $semesterStats[$sem] = grading_calc_semester_for_course($selectedCourse, $sem);
            }
            $finalStats = grading_calc_final_for_course($selectedCourse);

            // Calculate per-semester remaining marks
            $semesterUsedMarks = course_used_marks($selectedCourse);
        }

        // Assessment types for creation
        $types = Database::all("SELECT * FROM assessment_types WHERE enabled = 1 ORDER BY sort_order");

        Router::render('app/teacher/grading', [
            'title' => 'Gradebook',
            'courses' => $courses,
            'selectedCourse' => $selectedCourse,
            'assessments' => $assessments,
            'students' => $students,
            'semesterStats' => $semesterStats,
            'finalStats' => $finalStats,
            'types' => $types,
            'semesterUsedMarks' => $semesterUsedMarks ?? [1 => 0, 2 => 0],
        ]);
    }
}

/* =============== HELPER: Semester stats for a course =============== */
function grading_calc_semester_for_course(int $courseId, int $semester): array {
    $typeSlugs = $semester === 1 ? "('r1','r2')" : "('r3','r4')";
    $results = Database::all(
        "SELECT g.student_id, a.type_slug, a.max_mark, g.mark, g.percentage
         FROM grades g JOIN assessments a ON a.id = g.assessment_id
         WHERE a.course_id = ? AND a.type_slug IN $typeSlugs AND a.status = 'published'
         AND g.status IN ('submitted','verified','published','locked') AND g.mark IS NOT NULL",
        [$courseId]);

    if (empty($results)) return ['avg' => null, 'highest' => null, 'lowest' => null, 'count' => 0, 'students' => []];

    $studentData = [];
    foreach ($results as $r) {
        $sid = (int)$r['student_id'];
        $pct = $r['percentage'] ?? (($float = (float)$r['max_mark']) > 0 ? round(((float)$r['mark'] / $float) * 100, 2) : 0);
        $studentData[$sid][] = $pct;
    }

    $allPcts = [];
    $studentAvgs = [];
    foreach ($studentData as $sid => $pcts) {
        $avg = round(array_sum($pcts) / count($pcts), 2);
        $studentAvgs[$sid] = $avg;
        $allPcts[] = $avg;
    }

    return [
        'avg' => round(array_sum($allPcts) / count($allPcts), 1),
        'highest' => max($allPcts),
        'lowest' => min($allPcts),
        'count' => count($studentAvgs),
        'students' => $studentAvgs,
    ];
}

function grading_calc_final_for_course(int $courseId): array {
    $s1 = grading_calc_semester_for_course($courseId, 1);
    $s2 = grading_calc_semester_for_course($courseId, 2);

    // Get bonus per student
    $bonuses = Database::all(
        "SELECT student_id, COALESCE(SUM(points),0) AS total
         FROM bonus_entries WHERE course_id = ? AND status = 'approved' GROUP BY student_id", [$courseId]);
    $bonusMap = [];
    foreach ($bonuses as $b) $bonusMap[(int)$b['student_id']] = (float)$b['total'];

    $finals = [];
    $allStudents = array_unique(array_merge(array_keys($s1['students']), array_keys($s2['students'])));

    foreach ($allStudents as $sid) {
        $sem1 = $s1['students'][$sid] ?? null;
        $sem2 = $s2['students'][$sid] ?? null;
        $bonus = $bonusMap[$sid] ?? 0;

        if ($sem1 !== null && $sem2 !== null) {
            $final = round(($sem1 + $sem2) / 2, 2);
        } elseif ($sem1 !== null) {
            $final = $sem1;
        } elseif ($sem2 !== null) {
            $final = $sem2;
        } else {
            continue;
        }

        $adjusted = min(100, $final + $bonus);
        $finals[$sid] = [
            'semester1' => $sem1,
            'semester2' => $sem2,
            'final' => $final,
            'bonus' => $bonus,
            'adjusted' => $adjusted,
            'letter' => grading_letter($adjusted),
            'pass' => grading_pass($adjusted),
        ];
    }

    if (empty($finals)) return ['avg' => null, 'pass_rate' => null, 'students' => []];

    $allAdj = array_column($finals, 'adjusted');
    return [
        'avg' => round(array_sum($allAdj) / count($allAdj), 1),
        'pass_rate' => round((count(array_filter($finals, fn($f) => $f['pass'])) / count($finals)) * 100, 1),
        'students' => $finals,
    ];
}

function course_used_marks(int $courseId): array {
    $rows = Database::all(
        "SELECT a.type_slug, a.semester, MAX(a.max_mark) AS max_mark
         FROM assessments a WHERE a.course_id = ? AND a.status = 'published'
         AND a.type_slug IN ('r1','r2','r3','r4')
         GROUP BY a.type_slug, a.semester", [$courseId]);
    $used = [1 => 0, 2 => 0];
    foreach ($rows as $r) {
        $sem = (int)($r['semester'] ?? 0);
        if ($sem >= 1 && $sem <= 2) {
            $used[$sem] += (float)$r['max_mark'];
        }
    }
    return $used;
}

/* =============== GRADING: Gradebook for a specific assessment =============== */
class Ctl_gradebook {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];
        $assessmentId = (int)($_GET['id'] ?? 0);

        $assessment = Database::one(
            "SELECT a.*, ats.label AS type_label, c.title AS course_title, c.id AS cid
             FROM assessments a
             LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
             JOIN courses c ON c.id = a.course_id
             WHERE a.id = ? AND a.teacher_id = ?", [$assessmentId, $uid]);
        if (!$assessment) { flash('danger', 'Assessment not found.'); redirect('teacher/grading'); }

        // Get enrolled students with their current marks
        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid,
                    g.id AS gid, g.mark, g.percentage, g.letter_grade, g.status AS grade_status, g.notes
             FROM course_enrollments ce
             JOIN users u ON u.id = ce.user_id
             LEFT JOIN grades g ON g.assessment_id = ? AND g.student_id = u.id
             WHERE ce.course_id = ?
             ORDER BY u.last_name, u.first_name", [$assessmentId, $assessment['cid']]);

        // Handle POST: save grades
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $action = $_POST['action'] ?? '';

            if ($action === 'save_grades') {
                $marks = $_POST['marks'] ?? [];
                $maxMark = (float)$assessment['max_mark'];
                foreach ($marks as $studentId => $markStr) {
                    $mark = $markStr !== '' ? (float)$markStr : null;
                    if ($mark !== null && ($mark < 0 || $mark > $maxMark)) {
                        flash('danger', "Mark for student #$studentId must be between 0 and $maxMark.");
                        redirect('teacher/gradebook&id=' . $assessmentId);
                    }
                    $pct = $mark !== null && $maxMark > 0 ? round(($mark / $maxMark) * 100, 2) : null;
                    $letter = $pct !== null ? grading_letter($pct) : null;

                    $existing = Database::one("SELECT id, mark, status FROM grades WHERE assessment_id = ? AND student_id = ?", [$assessmentId, $studentId]);
                    if ($existing) {
                        if (in_array($existing['status'], ['locked'])) continue;
                        $oldMark = (float)$existing['mark'];
                        Database::update('grades', [
                            'mark' => $mark, 'percentage' => $pct, 'letter_grade' => $letter,
                            'entered_by' => $uid, 'status' => 'draft'
                        ], 'id = ?', [(int)$existing['id']]);
                        if ($oldMark !== ($mark ?? 0)) {
                            grading_audit((int)$existing['id'], $studentId, $assessmentId, 'update', $oldMark, $mark, $existing['status'], 'draft', $uid);
                        }
                    } else {
                        if ($mark === null) continue;
                        $gid = Database::insert('grades', [
                            'assessment_id' => $assessmentId, 'student_id' => $studentId,
                            'mark' => $mark, 'percentage' => $pct, 'letter_grade' => $letter,
                            'entered_by' => $uid, 'status' => 'draft'
                        ]);
                        grading_audit($gid, $studentId, $assessmentId, 'create', null, $mark, null, 'draft', $uid);
                    }
                }
                // Recalculate semester/final for all students in this course
                $enrolled = Database::all("SELECT user_id FROM course_enrollments WHERE course_id = ?", [$assessment['cid']]);
                foreach ($enrolled as $e) {
                    grading_recalc((int)$e['user_id'], (int)$assessment['cid']);
                }
                flash('success', 'Grades saved successfully.');
                redirect('teacher/gradebook&id=' . $assessmentId);
            }

            if ($action === 'submit_grades') {
                Database::run("UPDATE grades SET status = 'submitted' WHERE assessment_id = ? AND status = 'draft' AND mark IS NOT NULL", [$assessmentId]);
                Database::run("UPDATE assessments SET result_status = 'submitted' WHERE id = ?", [$assessmentId]);
                flash('success', 'Grades submitted for verification.');
                redirect('teacher/gradebook&id=' . $assessmentId);
            }
        }

        // Remaining marks for semester
        $semester = $assessment['semester'] ?? null;
        $typeSlugs = $semester === 1 ? "('r1','r2')" : ($semester === 2 ? "('r3','r4')" : "('r1','r2','r3','r4')");
        $semesterMax = Database::all(
            "SELECT a.type_slug, a.max_mark FROM assessments a
             WHERE a.course_id = ? AND a.type_slug IN $typeSlugs AND a.status = 'published'", [$assessment['cid']]);
        $semesterUsed = 0;
        foreach ($semesterMax as $sm) $semesterUsed += (float)$sm['max_mark'];
        $semesterRemaining = max(0, 100 - $semesterUsed);

        Router::render('app/teacher/gradebook', [
            'title' => 'Gradebook — ' . $assessment['title'],
            'assessment' => $assessment,
            'students' => $students,
            'semesterRemaining' => $semesterRemaining,
            'semesterUsed' => $semesterUsed,
        ]);
    }
}

/* =============== GRADING: Create new assessment =============== */
class Ctl_assessment_new {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];
        $courseId = (int)($_GET['course'] ?? 0);

        $course = Database::one("SELECT id, title FROM courses WHERE id = ? AND teacher_id = ?", [$courseId, $uid]);
        if (!$course) { flash('danger', 'Course not found.'); redirect('teacher/grading'); }

        $types = Database::all("SELECT * FROM assessment_types WHERE enabled = 1 ORDER BY sort_order");

        // Calculate remaining marks per semester
        $semesterUsed = [1 => 0, 2 => 0];
        $usedRows = Database::all(
            "SELECT a.type_slug, a.semester, MAX(a.max_mark) AS max_mark
             FROM assessments a WHERE a.course_id = ? AND a.status = 'published'
             GROUP BY a.type_slug, a.semester", [$courseId]);
        foreach ($usedRows as $r) {
            $sem = (int)($r['semester'] ?? 0);
            if ($sem >= 1 && $sem <= 2) {
                $semesterUsed[$sem] += (float)$r['max_mark'];
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $typeSlug = trim($_POST['type'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $maxMark = (float)($_POST['max_mark'] ?? 100);
            $date = $_POST['assessment_date'] ?: date('Y-m-d');
            $semester = (int)($_POST['semester'] ?? 0);

            if ($title === '') { flash('danger', 'Title required.'); redirect('teacher/assessment/new&course=' . $courseId); }
            if ($maxMark <= 0 || $maxMark > 100) { flash('danger', 'Maximum mark must be between 1 and 100.'); redirect('teacher/assessment/new&course=' . $courseId); }

            // Check round max marks
            $typeRow = Database::one("SELECT * FROM assessment_types WHERE slug = ?", [$typeSlug]);
            if ($typeRow && $typeRow['is_round']) {
                $existingMax = (float)Database::scalar(
                    "SELECT COALESCE(MAX(max_mark),0) FROM assessments WHERE course_id = ? AND type_slug = ? AND status = 'published'",
                    [$courseId, $typeSlug], 0);
                if ($existingMax > 0) { flash('danger', "Round {$typeRow['round_num']} already has a published assessment with max mark {$existingMax}."); redirect('teacher/assessment/new&course=' . $courseId); }
            }

            // Check semester total
            if ($semester >= 1 && $semester <= 2) {
                if ($semesterUsed[$semester] + $maxMark > 100) {
                    $remaining = max(0, 100 - $semesterUsed[$semester]);
                    flash('danger', "Semester $semester already uses {$semesterUsed[$semester]}/100 marks. Only $remaining remaining. Cannot create assessment worth $maxMark.");
                    redirect('teacher/assessment/new&course=' . $courseId);
                }
            }

            // Determine semester from type
            if (!$semester && $typeRow) {
                $semester = (int)($typeRow['semester'] ?? 0);
            }

            $id = Database::insert('assessments', [
                'course_id' => $courseId,
                'class_id' => (int)Database::scalar("SELECT class_id FROM course_enrollments WHERE course_id = ? LIMIT 1", [$courseId], 0),
                'teacher_id' => $uid,
                'type_slug' => $typeSlug,
                'title' => $title,
                'max_mark' => $maxMark,
                'assessment_date' => $date,
                'semester' => $semester ?: null,
                'status' => 'published',
                'result_status' => 'draft',
            ]);

            flash('success', "Assessment \"{$title}\" created.");
            redirect('teacher/gradebook&id=' . $id);
        }

        Router::render('app/teacher/assessment_new', [
            'title' => 'Create Assessment',
            'course' => $course,
            'types' => $types,
            'semesterUsed' => $semesterUsed,
        ]);
    }
}

/* =============== GRADING: Bonus entry =============== */
class Ctl_bonus {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];
        $courseId = (int)($_GET['course'] ?? 0);

        $course = Database::one("SELECT id, title FROM courses WHERE id = ? AND teacher_id = ?", [$courseId, $uid]);
        if (!$course) { flash('danger', 'Course not found.'); redirect('teacher/grading'); }

        $students = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? ORDER BY u.last_name, u.first_name", [$courseId]);

        $bonuses = Database::all(
            "SELECT b.*, u.first_name AS tfirst, u.last_name AS tlast
             FROM bonus_entries b JOIN users u ON u.id = b.teacher_id
             WHERE b.course_id = ? ORDER BY b.created_at DESC LIMIT 50", [$courseId]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['add_bonus'])) {
                $studentId = (int)$_POST['student_id'];
                $title = trim($_POST['title']);
                $reason = trim($_POST['reason'] ?? '');
                $points = (float)$_POST['points'];

                if ($title === '') { flash('danger', 'Title required.'); redirect('teacher/bonus&course=' . $courseId); }
                if ($points <= 0 || $points > 10) { flash('danger', 'Bonus points must be between 0.1 and 10.'); redirect('teacher/bonus&course=' . $courseId); }

                Database::insert('bonus_entries', [
                    'student_id' => $studentId,
                    'course_id' => $courseId,
                    'teacher_id' => $uid,
                    'title' => $title,
                    'reason' => $reason,
                    'points' => $points,
                    'status' => 'pending',
                ]);
                grading_recalc($studentId, $courseId);
                flash('success', "Bonus of +{$points} added for student.");
                redirect('teacher/bonus&course=' . $courseId);
            }
            if (($del = (int)($_POST['delete_bonus'] ?? 0))) {
                $b = Database::one("SELECT student_id FROM bonus_entries WHERE id = ? AND teacher_id = ?", [$del, $uid]);
                if ($b) {
                    Database::delete('bonus_entries', 'id = ?', [$del]);
                    grading_recalc((int)$b['student_id'], $courseId);
                    flash('success', 'Bonus deleted.');
                }
                redirect('teacher/bonus&course=' . $courseId);
            }
        }

        Router::render('app/teacher/bonus', [
            'title' => 'Bonus Grading',
            'course' => $course,
            'students' => $students,
            'bonuses' => $bonuses,
        ]);
    }
}

/* =============== GRADING: PDF Reports =============== */
class Ctl_grading_reports {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];

        $courses = Database::all(
            "SELECT c.id, c.title FROM courses c WHERE c.teacher_id = ? AND c.status = 'published' ORDER BY c.title", [$uid]);

        Router::render('app/teacher/grading_reports', [
            'title' => 'Grading Reports',
            'courses' => $courses,
        ]);
    }
}
