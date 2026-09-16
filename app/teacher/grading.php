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

/* =============== HELPER: Calculate midterm-only percentage for a semester =============== */
function grading_calc_midterm(int $studentId, int $courseId, int $semester, ?int $academicYearId = null): ?float {
    $where = "g.student_id = ? AND a.course_id = ? AND a.type_slug = 'r1' AND a.semester = ? AND a.status = 'published' AND g.status IN ('draft','submitted','verified','published','locked') AND g.mark IS NOT NULL";
    $args = [$studentId, $courseId, $semester];
    if ($academicYearId) { $where .= " AND a.academic_year_id = ?"; $args[] = $academicYearId; }

    $rows = Database::all(
        "SELECT a.max_mark, g.mark
         FROM grades g JOIN assessments a ON a.id = g.assessment_id
         WHERE $where", $args);

    if (empty($rows)) return null;

    $totalMark = 0; $totalMax = 0;
    foreach ($rows as $r) { $totalMark += (float)$r['mark']; $totalMax += (float)$r['max_mark']; }
    return $totalMax > 0 ? round(($totalMark / $totalMax) * 100, 2) : null;
}

/* =============== HELPER: Calculate cumulative semester percentage (all assessments in semester) =============== */
/* semester 1 → all assessments with semester=1, semester 2 → all with semester=2 */
function grading_calc_semester_total(int $studentId, int $courseId, int $semester, ?int $academicYearId = null): ?float {
    $where = "g.student_id = ? AND a.course_id = ? AND a.status = 'published' AND g.status IN ('draft','submitted','verified','published','locked') AND g.mark IS NOT NULL AND a.semester = ?";
    $args = [$studentId, $courseId, $semester];
    if ($academicYearId) { $where .= " AND a.academic_year_id = ?"; $args[] = $academicYearId; }

    $rows = Database::all(
        "SELECT a.type_slug, a.max_mark, g.mark
         FROM grades g JOIN assessments a ON a.id = g.assessment_id
         WHERE $where", $args);

    if (empty($rows)) return null;

    $totalMark = 0; $totalMax = 0;
    foreach ($rows as $r) { $totalMark += (float)$r['mark']; $totalMax += (float)$r['max_mark']; }
    return $totalMax > 0 ? round(($totalMark / $totalMax) * 100, 2) : null;
}

/* =============== HELPER: Calculate final result for student/course =============== */
/* Returns: midterm1, total1, midterm2, total2, final, bonus, adjusted, letter, pass */
function grading_calc_final(int $studentId, int $courseId, ?int $academicYearId = null): array {
    $midterm1 = grading_calc_midterm($studentId, $courseId, 1, $academicYearId);
    $total1   = grading_calc_semester_total($studentId, $courseId, 1, $academicYearId);
    $midterm2 = grading_calc_midterm($studentId, $courseId, 2, $academicYearId);
    $total2   = grading_calc_semester_total($studentId, $courseId, 2, $academicYearId);

    $bonusRow = Database::one(
        "SELECT COALESCE(SUM(points), 0) AS total FROM bonus_entries
         WHERE student_id = ? AND course_id = ? AND status = 'approved'" .
         ($academicYearId ? " AND academic_year_id = ?" : ""),
        $academicYearId ? [$studentId, $courseId, $academicYearId] : [$studentId, $courseId]);
    $bonus = (float)($bonusRow['total'] ?? 0);

    $final = null;
    if ($total1 !== null && $total2 !== null) {
        $final = round(($total1 + $total2) / 2, 2);
    } elseif ($total1 !== null) {
        $final = $total1;
    } elseif ($total2 !== null) {
        $final = $total2;
    }

    $adjusted = $final !== null ? min(100, $final + $bonus) : null;
    $passMark = (float)Database::scalar("SELECT pass_mark FROM grading_config WHERE school_id = (SELECT school_id FROM users WHERE id = ?) LIMIT 1", [$studentId], 50);

    return [
        'midterm1'  => $midterm1,
        'total1'    => $total1,
        'midterm2'  => $midterm2,
        'total2'    => $total2,
        'final_score' => $final,
        'bonus'     => $bonus,
        'adjusted'  => $adjusted,
        'letter'    => $adjusted !== null ? grading_letter($adjusted, $passMark) : null,
        'pass'      => $adjusted !== null ? grading_pass($adjusted, $passMark) : null,
    ];
}

/* =============== HELPER: Audit log =============== */
function grading_audit(int $gradeId, int $studentId, int $assessmentId, string $action, ?float $oldMark, ?float $newMark, ?string $oldStatus, ?string $newStatus, int $userId, ?string $reason = null): void {
    $courseId = (int)Database::scalar("SELECT course_id FROM assessments WHERE id = ?", [$assessmentId], 0);
    $schoolId = (int)Database::scalar("SELECT school_id FROM courses WHERE id = ?", [$courseId], 0);
    $typeSlug = (string)Database::scalar("SELECT type_slug FROM assessments WHERE id = ?", [$assessmentId], '');
    $aType = in_array($typeSlug, ['r1','r2']) ? 'exam' : ($typeSlug === 'assignment' ? 'assignment' : 'manual');
    Database::insert('grade_audit', [
        'student_id' => $studentId,
        'course_id' => $courseId,
        'school_id' => $schoolId,
        'assessment_type' => $aType,
        'assessment_id' => $assessmentId,
        'old_score' => $oldMark !== null ? (string)$oldMark : null,
        'new_score' => $newMark !== null ? (string)$newMark : null,
        'action' => $action,
        'reason' => $reason,
        'actor_id' => $userId,
    ]);
}

/* =============== HELPER: Recalculate semester + final =============== */
function grading_recalc(int $studentId, int $courseId, ?int $academicYearId = null): void {
    $classId = (int)Database::scalar("SELECT class_id FROM assessments WHERE course_id = ? AND class_id IS NOT NULL AND class_id > 0 LIMIT 1", [$courseId], 0);
    foreach ([1, 2] as $sem) {
        $total = grading_calc_semester_total($studentId, $courseId, $sem, $academicYearId);
        $count = (int)Database::scalar(
            "SELECT COUNT(*) FROM grades g JOIN assessments a ON a.id = g.assessment_id
             WHERE g.student_id = ? AND a.course_id = ? AND g.status IN ('draft','submitted','verified','published','locked')
             AND a.semester = ?",
            [$studentId, $courseId, $sem]);
        // Delete first to avoid MySQL NULL unique key issue, then insert
        Database::run("DELETE FROM semester_results WHERE student_id = ? AND course_id = ? AND academic_year_id <=> ? AND semester = ?",
            [$studentId, $courseId, $academicYearId, $sem]);
        Database::run("INSERT INTO semester_results (student_id, course_id, class_id, academic_year_id, semester, total, assessment_count)
            VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$studentId, $courseId, $classId, $academicYearId, $sem, $total, $count]);
    }

    $final = grading_calc_final($studentId, $courseId, $academicYearId);
    Database::run("DELETE FROM final_results WHERE student_id = ? AND course_id = ? AND academic_year_id <=> ?",
        [$studentId, $courseId, $academicYearId]);
    Database::run("INSERT INTO final_results (student_id, course_id, class_id, academic_year_id, semester1_total, semester2_total, final_score, bonus_points, adjusted_score, letter_grade, is_pass)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$studentId, $courseId, $classId, $academicYearId, $final['total1'], $final['total2'], $final['final_score'], $final['bonus'], $final['adjusted'], $final['letter'], (int)($final['pass'] ?? 0)]);
}

/* =============== GRADING: Main landing page =============== */
class Ctl_grading {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];

        // Teacher's classes (distinct classes from their courses)
        $classes = Database::all(
            "SELECT DISTINCT sg.id, sg.name, sg.grade, sg.section
             FROM courses c
             JOIN student_groups sg ON sg.id = c.class_id
             WHERE c.teacher_id = ? AND c.status = 'published' AND c.class_id IS NOT NULL
             ORDER BY sg.grade, sg.section", [$uid]);

        $selectedClass = (int)($_GET['class'] ?? 0);
        $selectedCourse = (int)($_GET['course'] ?? 0);
        $selectedSem = (int)($_GET['sem'] ?? 0);
        $assessments = [];
        $students = [];
        $semesterStats = [1 => null, 2 => null];
        $finalStats = null;
        $courseTotalUsed = 0;
        $courseTotalRemaining = 100;
        $classSubjects = [];
        $className = '';

        // If class selected, show subjects for that class
        if ($selectedClass) {
            $classRow = Database::one("SELECT id, name FROM student_groups WHERE id = ?", [$selectedClass]);
            $className = $classRow['name'] ?? '';
            $classSubjects = Database::all(
                "SELECT c.id, c.title, c.code,
                        (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS students
                 FROM courses c
                 WHERE c.teacher_id = ? AND c.class_id = ? AND c.status = 'published'
                 ORDER BY c.title", [$uid, $selectedClass]);
        }

        // If course selected, show gradebook
        if ($selectedCourse) {
            // Verify teacher owns this course
            $ownCourse = Database::scalar("SELECT id FROM courses WHERE id = ? AND teacher_id = ?", [$selectedCourse, $uid]);
            if (!$ownCourse) { flash('danger', 'Access denied.'); redirect('teacher/grading'); }

            // Get class name for this course
            $courseClass = Database::one(
                "SELECT sg.name AS class_name FROM courses c JOIN student_groups sg ON sg.id = c.class_id WHERE c.id = ?",
                [$selectedCourse]);
            $className = $courseClass['class_name'] ?? $className;

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

            // Get enrolled students for THIS class only
            $students = Database::all(
                "SELECT u.id, u.first_name, u.last_name, u.student_id AS sid
                 FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
                 WHERE ce.course_id = ? AND (ce.class_id = ? OR ce.class_id IS NULL)
                 ORDER BY u.last_name, u.first_name", [$selectedCourse, $selectedClass]);

            // Calculate semester/final stats
            foreach ([1, 2] as $sem) {
                $semesterStats[$sem] = grading_calc_semester_for_course($selectedCourse, $sem);
            }
            $finalStats = grading_calc_final_for_course($selectedCourse);

            // Calculate per-semester remaining marks
            $semesterUsedMarks = course_used_marks($selectedCourse);
        }

        Router::render('app/teacher/grading', [
            'title' => 'Gradebook',
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'classSubjects' => $classSubjects,
            'className' => $className,
            'selectedCourse' => $selectedCourse,
            'selectedSem' => $selectedSem,
            'assessments' => $assessments,
            'students' => $students,
            'semesterStats' => $semesterStats,
            'finalStats' => $finalStats,
            'semesterUsedMarks' => $semesterUsedMarks ?? [1 => 0, 2 => 0],
        ]);
    }
}

/* =============== HELPER: Semester stats for a course =============== */
function grading_calc_semester_for_course(int $courseId, int $semester): array {
    $results = Database::all(
        "SELECT g.student_id, a.type_slug, a.max_mark, g.mark, g.percentage
         FROM grades g JOIN assessments a ON a.id = g.assessment_id
         WHERE a.course_id = ? AND a.semester = ? AND a.status = 'published'
         AND g.status IN ('draft','submitted','verified','published','locked') AND g.mark IS NOT NULL",
         [$courseId, $semester]);

    if (empty($results)) return ['avg' => null, 'highest' => null, 'lowest' => null, 'count' => 0, 'students' => []];

    $studentData = [];
    foreach ($results as $r) {
        $sid = (int)$r['student_id'];
        $studentData[$sid][] = ['mark' => (float)$r['mark'], 'max' => (float)$r['max_mark']];
    }

    $allPcts = [];
    $studentAvgs = [];
    foreach ($studentData as $sid => $marks) {
        $totalMark = 0; $totalMax = 0;
        foreach ($marks as $m) { $totalMark += $m['mark']; $totalMax += $m['max']; }
        $pct = $totalMax > 0 ? round(($totalMark / $totalMax) * 100, 2) : 0;
        $studentAvgs[$sid] = $pct;
        $allPcts[] = $pct;
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
        $total1 = $s1['students'][$sid] ?? null;
        $total2 = $s2['students'][$sid] ?? null;
        $bonus = $bonusMap[$sid] ?? 0;

        if ($total1 !== null && $total2 !== null) {
            $final = round(($total1 + $total2) / 2, 2);
        } elseif ($total1 !== null) {
            $final = $total1;
        } elseif ($total2 !== null) {
            $final = $total2;
        } else {
            continue;
        }

        $adjusted = min(100, $final + $bonus);
        $finals[$sid] = [
            'total1' => $total1,
            'total2' => $total2,
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
        "SELECT a.semester, SUM(a.max_mark) AS total_max
         FROM assessments a WHERE a.course_id = ? AND a.status = 'published'
         AND a.semester IN (1, 2)
         GROUP BY a.semester", [$courseId]);
    $used = [1 => 0, 2 => 0];
    foreach ($rows as $r) {
        $sem = (int)$r['semester'];
        if ($sem >= 1 && $sem <= 2) {
            $used[$sem] = (float)$r['total_max'];
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
        $semesterUsed = 0;
        $semesterRemaining = 0;
        if ($semester === 1 || $semester === 2) {
            $semesterMax = Database::all(
                "SELECT a.type_slug, a.max_mark FROM assessments a
                 WHERE a.course_id = ? AND a.semester = ? AND a.status = 'published'", [$assessment['cid'], $semester]);
            foreach ($semesterMax as $sm) $semesterUsed += (float)$sm['max_mark'];
            $semesterRemaining = max(0, 100 - $semesterUsed);
        }

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
            if ($semester < 1 || $semester > 2) { flash('danger', 'Semester required.'); redirect('teacher/assessment/new&course=' . $courseId); }

            // Check round max marks
            $typeRow = Database::one("SELECT * FROM assessment_types WHERE slug = ?", [$typeSlug]);
            if ($typeRow && $typeRow['is_round']) {
                $existingMax = (float)Database::scalar(
                    "SELECT COALESCE(MAX(max_mark),0) FROM assessments WHERE course_id = ? AND type_slug = ? AND status = 'published'",
                    [$courseId, $typeSlug], 0);
                if ($existingMax > 0) { flash('danger', "Semester {$typeRow['semester']} already has a published assessment with max mark {$existingMax}."); redirect('teacher/assessment/new&course=' . $courseId); }
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
            $courseClass = Database::one("SELECT class_id FROM courses WHERE id = ?", [$courseId]);
            $classParam = $courseClass && $courseClass['class_id'] ? '&class=' . (int)$courseClass['class_id'] : '';
            redirect('teacher/grading' . $classParam . '&course=' . $courseId);
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
                    'status' => 'approved',
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
            "SELECT c.id, c.title, c.class_id, sg.name AS class_name
             FROM courses c
             LEFT JOIN student_groups sg ON sg.id = c.class_id
             WHERE c.teacher_id = ? AND c.status = 'published' AND c.class_id IS NOT NULL
             ORDER BY sg.grade, sg.section, c.title", [$uid]);

        Router::render('app/teacher/grading_reports', [
            'title' => 'Grading Reports',
            'courses' => $courses,
        ]);
    }
}

class Ctl_grading_students {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];
        $courseId = (int)($_GET['course'] ?? 0);

        if ($courseId <= 0) {
            redirect('teacher/grading');
        }

        $course = Database::one("SELECT id, title, teacher_id, class_id FROM courses WHERE id = ? AND status = 'published'", [$courseId]);
        if (!$course || (int)$course['teacher_id'] !== $uid) {
            redirect('teacher/grading');
        }

        $classId = (int)($course['class_id'] ?? 0);

        // Enrolled students for this class only
        $enrolled = Database::all(
            "SELECT u.id, u.first_name, u.last_name, u.student_id
             FROM course_enrollments ce
             JOIN users u ON u.id = ce.user_id
             WHERE ce.course_id = ? AND u.role = 'student'
             AND (ce.class_id = ? OR ce.class_id IS NULL)
             ORDER BY u.last_name, u.first_name", [$courseId, $classId ?: null]);
        $enrolledIds = array_column($enrolled, 'id');

        // Search
        $searchQuery = trim($_GET['q'] ?? '');
        $searchResults = [];
        if ($searchQuery !== '') {
            $like = '%' . $searchQuery . '%';
            $searchResults = Database::all(
                "SELECT id, first_name, last_name, student_id, email FROM users
                 WHERE role = 'student' AND (first_name LIKE ? OR last_name LIKE ? OR student_id LIKE ? OR email LIKE ?)
                 ORDER BY last_name, first_name LIMIT 10",
                [$like, $like, $like, $like]);
        }

        Router::render('app/teacher/grading_students', [
            'title' => 'Course Students',
            'selectedCourse' => $courseId,
            'courseTitle' => $course['title'],
            'classId' => $classId,
            'enrolled' => $enrolled,
            'enrolledIds' => $enrolledIds,
            'searchQuery' => $searchQuery,
            'searchResults' => $searchResults,
        ]);
    }
}

/* =============== ROSTER: Class grade report (homeroom teacher only) =============== */
class Ctl_roster {
    public function run(): void {
        $u = require_role('teacher', 'lecturer');
        $uid = (int)$u['id'];

        $homeroom = Database::one(
            "SELECT id, name, grade, section, homeroom_teacher_id FROM student_groups WHERE homeroom_teacher_id = ?", [$uid]);
        if (!$homeroom) {
            flash('danger', 'You are not assigned as a homeroom teacher for any class.');
            redirect('teacher/grading');
        }
        $classId = (int)$homeroom['id'];

        $courses = Database::all(
            "SELECT c.id, c.title, c.subject_id, s.name AS subject_name
             FROM courses c LEFT JOIN subjects s ON s.id = c.subject_id
             WHERE c.class_id = ? AND c.status = 'published'
             ORDER BY s.name, c.title", [$classId]);

        $students = Database::all(
            "SELECT DISTINCT u.id, u.first_name, u.last_name, u.student_id AS sid,
                    u.birth_date, u.gender
             FROM course_enrollments ce JOIN users u ON u.id = ce.user_id
             WHERE ce.class_id = ? AND u.role = 'student'
             ORDER BY u.last_name, u.first_name", [$classId]);

        $rosterData = [];
        foreach ($students as $s) {
            $age = null;
            if ($s['birth_date']) {
                $age = (new DateTime($s['birth_date']))->diff(new DateTime())->y;
            }
            $gender = strtoupper($s['gender'] ?? '');
            if (!in_array($gender, ['M', 'F'])) $gender = 'M';

            $subjects = [];
            $fyTotal = 0; $fyCount = 0;
            $s2Total = 0; $s2Count = 0;

            foreach ($courses as $c) {
                $cid = (int)$c['id'];
                $fy = $this->courseFullYearTotal((int)$s['id'], $cid);
                $s2 = $this->courseSemesterTotal((int)$s['id'], $cid, 2);
                $avg = ($fy !== null && $s2 !== null) ? round(($fy + $s2) / 2, 1) : ($fy ?? $s2 ?? null);

                $subjects[$cid] = ['fy' => $fy, 's2' => $s2, 'avg' => $avg];
                if ($fy !== null) { $fyTotal += $fy; $fyCount++; }
                if ($s2 !== null) { $s2Total += $s2; $s2Count++; }
            }

            $absTotal = (int)Database::scalar(
                "SELECT COUNT(*) FROM attendance WHERE student_id = ? AND status = 'absent'
                 AND course_id IN (SELECT id FROM courses WHERE class_id = ?)",
                [(int)$s['id'], $classId]);
            $absS2 = (int)Database::scalar(
                "SELECT COUNT(*) FROM attendance att
                 WHERE att.student_id = ? AND att.status = 'absent'
                 AND att.course_id IN (SELECT id FROM courses WHERE class_id = ?)
                 AND att.date >= COALESCE((SELECT MIN(a.assessment_date) FROM assessments a
                   JOIN courses c ON c.id = a.course_id WHERE c.class_id = ? AND a.semester = 2), '1900-01-01')
                 AND att.date <= COALESCE((SELECT MAX(a.assessment_date) FROM assessments a
                   JOIN courses c ON c.id = a.course_id WHERE c.class_id = ? AND a.semester = 2), '2099-12-31')",
                [(int)$s['id'], $classId, $classId, $classId]);

            $rosterData[] = [
                'id' => (int)$s['id'],
                'name' => $s['last_name'] . ', ' . $s['first_name'],
                'sid' => $s['sid'] ?? '',
                'age' => $age,
                'gender' => $gender,
                'subjects' => $subjects,
                'fy_total' => $fyCount > 0 ? round($fyTotal, 1) : null,
                'fy_average' => $fyCount > 0 ? round($fyTotal / $fyCount, 1) : null,
                's2_total' => $s2Count > 0 ? round($s2Total, 1) : null,
                's2_average' => $s2Count > 0 ? round($s2Total / $s2Count, 1) : null,
                'avg_total' => ($fyCount > 0 && $s2Count > 0) ? round(($fyTotal + $s2Total) / 2, 1) : null,
                'avg_average' => ($fyCount > 0 && $s2Count > 0) ? round(($fyTotal / $fyCount + $s2Total / $s2Count) / 2, 1) : null,
                'fy_absences' => $absTotal,
                's2_absences' => $absS2,
                'avg_absences' => round(($absTotal + $absS2) / 2, 0),
            ];
        }

        // Rank by FY average
        usort($rosterData, fn($a, $b) => ($b['fy_average'] ?? -1) <=> ($a['fy_average'] ?? -1));
        $rank = 0; $prev = null;
        foreach ($rosterData as &$r) {
            if ($r['fy_average'] !== null && $r['fy_average'] !== $prev) { $rank++; $prev = $r['fy_average']; }
            $r['fy_rank'] = $rank;
        }
        unset($r);

        // Rank by S2 average
        usort($rosterData, fn($a, $b) => ($b['s2_average'] ?? -1) <=> ($a['s2_average'] ?? -1));
        $rank = 0; $prev = null;
        foreach ($rosterData as &$r) {
            if ($r['s2_average'] !== null && $r['s2_average'] !== $prev) { $rank++; $prev = $r['s2_average']; }
            $r['s2_rank'] = $rank;
        }
        unset($r);

        // Rank by AVG average
        usort($rosterData, fn($a, $b) => ($b['avg_average'] ?? -1) <=> ($a['avg_average'] ?? -1));
        $rank = 0; $prev = null;
        foreach ($rosterData as &$r) {
            if ($r['avg_average'] !== null && $r['avg_average'] !== $prev) { $rank++; $prev = $r['avg_average']; }
            $r['avg_rank'] = $rank;
        }
        unset($r);

        // Sort by name (last, first) for display
        usort($rosterData, fn($a, $b) => strcmp($a['name'], $b['name']));

        // PDF download (server-side)
        if (isset($_GET['download'])) {
            $this->renderServerPDF($rosterData, $courses, $homeroom);
            exit;
        }
        // PDF viewer (HTML preview)
        if (isset($_GET['pdf'])) {
            $this->renderPDFViewer($rosterData, $courses, $homeroom);
            exit;
        }

        Router::render('app/teacher/roster', [
            'title' => 'Class Roster',
            'homeroom' => $homeroom,
            'courses' => $courses,
            'rosterData' => $rosterData,
        ]);
    }

    private function courseSemesterTotal(int $studentId, int $courseId, int $semester): ?float {
        $row = Database::one(
            "SELECT COALESCE(SUM(g.mark), 0) AS total_mark,
                    COALESCE(SUM(a.max_mark), 0) AS total_max
             FROM grades g JOIN assessments a ON a.id = g.assessment_id
             WHERE g.student_id = ? AND a.course_id = ? AND a.semester = ?
             AND g.status IN ('published','locked') AND g.mark IS NOT NULL",
            [$studentId, $courseId, $semester]);
        if (!$row || (float)$row['total_max'] == 0) return null;
        return round(((float)$row['total_mark'] / (float)$row['total_max']) * 100, 1);
    }

    private function courseFullYearTotal(int $studentId, int $courseId): ?float {
        $row = Database::one(
            "SELECT COALESCE(SUM(g.mark), 0) AS total_mark,
                    COALESCE(SUM(a.max_mark), 0) AS total_max
             FROM grades g JOIN assessments a ON a.id = g.assessment_id
             WHERE g.student_id = ? AND a.course_id = ?
             AND g.status IN ('published','locked') AND g.mark IS NOT NULL",
            [$studentId, $courseId]);
        if (!$row || (float)$row['total_max'] == 0) return null;
        return round(((float)$row['total_mark'] / (float)$row['total_max']) * 100, 1);
    }

    private function renderServerPDF(array $rosterData, array $courses, array $homeroom): void {
        $fmt = fn($v) => $v !== null ? number_format((float)$v, 1) : '—';
        $subLabel = fn($c) => strtoupper(mb_substr($c['subject_name'] ?? $c['title'], 0, 3));

        // Build summary stats
        $allAvg = array_filter(array_column($rosterData, 'fy_average'));
        $classAvg = $allAvg ? round(array_sum($allAvg) / count($allAvg), 1) : 0;
        $passCount = count(array_filter($rosterData, fn($r) => $r['fy_average'] !== null && $r['fy_average'] >= 50));
        $passRate = count($rosterData) > 0 ? round(($passCount / count($rosterData)) * 100, 0) : 0;
        $totalAbs = array_sum(array_column($rosterData, 'fy_absences'));

        // Build JSON payload for Python generator
        $coursesJson = [];
        foreach ($courses as $c) {
            $coursesJson[] = [
                'id' => (int)$c['id'],
                'subject_name' => $c['subject_name'] ?? $c['title'],
                'title' => $c['title'] ?? '',
            ];
        }

        $rosterJson = [];
        foreach ($rosterData as $r) {
            $subjects = [];
            foreach ($courses as $c) {
                $subjects[(string)$c['id']] = [
                    'fy' => $r['subjects'][$c['id']]['fy'] ?? null,
                    's2' => $r['subjects'][$c['id']]['s2'] ?? null,
                    'avg' => $r['subjects'][$c['id']]['avg'] ?? null,
                ];
            }
            $rosterJson[] = [
                'name' => $r['name'],
                'sid' => $r['sid'],
                'age' => $r['age'] ?? null,
                'gender' => $r['gender'] ?? 'M',
                'subjects' => $subjects,
                'fy_absences' => $r['fy_absences'] ?? 0,
                'fy_total' => $r['fy_total'] ?? null,
                'fy_average' => $r['fy_average'] ?? null,
                'fy_rank' => $r['fy_rank'] ?? '',
                's2_absences' => $r['s2_absences'] ?? 0,
                's2_total' => $r['s2_total'] ?? null,
                's2_average' => $r['s2_average'] ?? null,
                's2_rank' => $r['s2_rank'] ?? '',
                'avg_absences' => $r['avg_absences'] ?? 0,
                'avg_total' => $r['avg_total'] ?? null,
                'avg_average' => $r['avg_average'] ?? null,
                'avg_rank' => $r['avg_rank'] ?? '',
            ];
        }

        $payload = json_encode([
            'courses' => $coursesJson,
            'roster' => $rosterJson,
            'homeroom' => ['name' => $homeroom['name']],
            'stats' => [
                'student_count' => count($rosterData),
                'subject_count' => count($courses),
                'class_avg' => number_format($classAvg, 1),
                'pass_rate' => $passRate,
                'total_absences' => $totalAbs,
            ],
            'doc_id' => 'EDU-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'stamp' => date('F Y'),
        ]);

        $script = __DIR__ . '/../../scripts/roster_pdf.py';
        $tmpJson = tempnam(sys_get_temp_dir(), 'roster_json_') . '.json';
        $tmpPdf = tempnam(sys_get_temp_dir(), 'roster_pdf_') . '.pdf';

        file_put_contents($tmpJson, $payload);
        exec('python3 ' . escapeshellarg($script) . ' -i ' . escapeshellarg($tmpJson) . ' -o ' . escapeshellarg($tmpPdf) . ' 2>&1', $output, $returnCode);

        @unlink($tmpJson);

        if ($returnCode === 0 && file_exists($tmpPdf) && filesize($tmpPdf) > 0) {
            $filename = 'class_roster_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $homeroom['name']) . '_' . date('Ymd') . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($tmpPdf);
            @unlink($tmpPdf);
            exit;
        }

        // Fallback: error message
        @unlink($tmpPdf);
        http_response_code(500);
        echo 'PDF generation failed: ' . implode("\n", $output);
        exit;
    }

    private function renderPDFViewer(array $rosterData, array $courses, array $homeroom): void {
        global $__u;
        $u = require_role('teacher', 'lecturer');
        $__u = $u;

        $fmt = fn($v) => $v !== null ? number_format($v, 1) : '—';

        $pdf_title = 'Class Roster';
        $pdf_subtitle = $homeroom['name'] . ' — ' . date('F Y');
        $pdf_doc_id = 'EDU-ROSTER-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $pdf_stamp = date('F j, Y \a\t g:i A');
        $pdf_filename = 'class_roster_' . $homeroom['name'] . '_' . date('Ymd') . '.pdf';
        $pdf_record_count = count($rosterData);
        $pdf_orientation = 'landscape';
        $backUrl = url('teacher/grading/roster');

        $studentCount = count($rosterData);
        $subjectCount = count($courses);
        $allAvg = array_filter(array_column($rosterData, 'fy_average'));
        $classAvg = $allAvg ? round(array_sum($allAvg) / count($allAvg), 1) : 0;
        $passCount = count(array_filter($rosterData, fn($r) => $r['fy_average'] !== null && $r['fy_average'] >= 50));
        $passRate = $studentCount > 0 ? round(($passCount / $studentCount) * 100, 1) : 0;

        // Landscape paper width
        $paperWidth = '297mm';
        require __DIR__ . '/../../includes/pdf_template.php';
        ?>
        <div class="pdf-viewer">
          <div class="pdf-toolbar">
            <a href="<?= e($backUrl) ?>" class="pdf-toolbar-btn pdf-toolbar-btn--back">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
              Back
            </a>
            <div style="flex:1"></div>
            <button onclick="window.print()" class="pdf-toolbar-btn pdf-toolbar-btn--print">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
              Print
            </button>
            <a href="<?= e(url('teacher/grading/roster&download=1')) ?>" class="pdf-toolbar-btn pdf-toolbar-btn--dl">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
              Download PDF
            </a>
          </div>

          <div class="pdf-paper" id="pdf-content" style="max-width:<?= $paperWidth ?>;margin:0 auto">
            <div class="pdf-header">
              <div class="logos-row">
                <div class="flag-wrap"><img class="flag-img" src="<?= e(url('public/images/ethiopian-flag.jpeg')) ?>" alt="Ethiopia"></div>
                <div class="text-center">
                  <h2>Federal Democratic Republic of Ethiopia</h2>
                  <div class="pdf-sub"><?= e($pdf_subtitle) ?></div>
                </div>
                <div class="ministry-wrap"><img class="logo-img" src="<?= e(url('public/images/ministry-logo.png')) ?>" alt="Ministry"></div>
              </div>
            </div>

            <div class="pdf-meta">
              <span><span class="meta-dot"></span>Class: <b><?= e($homeroom['name']) ?></b></span>
              <span><span class="meta-dot"></span>Students: <b><?= $studentCount ?></b></span>
              <span><span class="meta-dot"></span>Subjects: <b><?= $subjectCount ?></b></span>
              <span><span class="meta-dot"></span>Class Average: <b><?= number_format($classAvg, 1) ?>%</b></span>
              <span><span class="meta-dot"></span>Pass Rate: <b><?= $passRate ?>%</b></span>
              <span><span class="meta-dot"></span>Date: <b><?= date('F j, Y') ?></b></span>
            </div>

            <?php
            $colB = 'border-right:1px solid #e5e7eb';
            $subLabel = fn($c) => strtoupper(mb_substr($c['subject_name'] ?? $c['title'], 0, 3));
            ?>
            <table style="width:100%;border-collapse:collapse;font-size:11px">
              <thead>
                <tr>
                  <th style="text-align:center;padding:8px 6px;width:30px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>">#</th>
                  <th style="text-align:left;padding:8px 10px;min-width:130px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>">Student</th>
                  <th style="text-align:center;padding:8px 6px;min-width:60px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>">ID</th>
                  <th style="text-align:center;padding:8px 4px;width:30px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>">Age</th>
                  <th style="text-align:center;padding:8px 4px;width:28px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>">Sex</th>
                  <th style="text-align:center;padding:8px 4px;width:28px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>"></th>
                  <?php foreach ($courses as $c): ?>
                    <th style="text-align:center;padding:8px 4px;width:40px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>;font-size:9px" title="<?= e($c['subject_name'] ?? $c['title']) ?>"><?= e($subLabel($c)) ?></th>
                  <?php endforeach; ?>
                  <th style="text-align:center;padding:8px 4px;width:30px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>">Abs</th>
                  <th style="text-align:center;padding:8px 4px;width:40px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>">Tot</th>
                  <th style="text-align:center;padding:8px 4px;width:40px;border-bottom:2px solid #1a1a2e;background:#f8fafc;<?= $colB ?>;color:#6366f1;font-weight:700">Avg</th>
                  <th style="text-align:center;padding:8px 4px;width:36px;border-bottom:2px solid #1a1a2e;background:#f8fafc" onclick="sortRoster()" id="rank-th">Rank</th>
                </tr>
              </thead>
              <tbody id="roster-body">
              <?php foreach ($rosterData as $ri => $r):
                $roll = $ri + 1;
                $groupBg = $ri % 2 === 0 ? '#ffffff' : '#f8fafc';
              ?>
                <!-- FY row -->
                <tr>
                  <td rowspan="3" style="text-align:center;padding:8px 4px;font-weight:800;font-size:12px;color:#6366f1;vertical-align:middle;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>"><?= $roll ?></td>
                  <td rowspan="3" style="padding:8px 10px;font-weight:600;vertical-align:middle;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>">
                    <div style="font-size:11px;line-height:1.2"><?= e($r['name']) ?></div>
                    <div style="font-size:8px;color:#6b7280;font-weight:400"><?= e($r['sid']) ?></div>
                  </td>
                  <td rowspan="3" style="text-align:center;vertical-align:middle;font-size:9px;color:#6b7280;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>"><?= e($r['sid']) ?></td>
                  <td rowspan="3" style="text-align:center;vertical-align:middle;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>;font-size:11px"><?= $r['age'] ?? '—' ?></td>
                  <td rowspan="3" style="text-align:center;vertical-align:middle;font-weight:700;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>;color:<?= $r['gender'] === 'M' ? '#0ea5e9' : '#ec4899' ?>;font-size:11px"><?= e($r['gender']) ?></td>
                  <td style="text-align:center;padding:3px;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>">
                    <span style="display:inline-block;font-size:7px;font-weight:700;padding:1px 3px;border-radius:2px;background:#e0e7ff;color:#3730a3">FY</span>
                  </td>
                  <?php foreach ($courses as $c):
                    $sub = $r['subjects'][$c['id']] ?? ['fy'=>null];
                    $val = $sub['fy'];
                    $color = $val === null ? '#9ca3af' : ($val >= 50 ? '#1a1a2e' : '#dc2626');
                  ?>
                    <td style="text-align:center;padding:6px 4px;font-weight:700;color:<?= $color ?>;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>"><?= $fmt($val) ?></td>
                  <?php endforeach; ?>
                  <td style="text-align:center;padding:6px;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>;font-weight:<?= $r['fy_absences'] > 0 ? '700' : '400' ?>;color:<?= $r['fy_absences'] > 0 ? '#dc2626' : '#6b7280' ?>;font-size:11px"><?= $r['fy_absences'] ?></td>
                  <td style="text-align:center;padding:6px;font-weight:700;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>;font-size:11px"><?= $fmt($r['fy_total']) ?></td>
                  <td style="text-align:center;padding:6px;font-weight:800;color:#6366f1;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>;font-size:12px"><?= $fmt($r['fy_average']) ?></td>
                  <td style="text-align:center;padding:6px;font-weight:700;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>" data-rank="fy">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:5px;<?= $r['fy_rank'] <= 3 ? 'background:#e0e7ff;color:#3730a3;font-weight:800' : 'background:#f1f5f9;color:#64748b' ?>;font-size:11px"><?= $r['fy_rank'] ?></span>
                  </td>
                </tr>
                <!-- S2 row -->
                <tr>
                  <td style="text-align:center;padding:2px;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>">
                    <span style="display:inline-block;font-size:7px;font-weight:700;padding:1px 3px;border-radius:2px;background:#dbeafe;color:#1e40af">S2</span>
                  </td>
                  <?php foreach ($courses as $c):
                    $sub = $r['subjects'][$c['id']] ?? ['s2'=>null];
                    $val = $sub['s2'];
                    $color = $val === null ? '#9ca3af' : ($val >= 50 ? '#374151' : '#dc2626');
                  ?>
                    <td style="text-align:center;padding:3px 4px;font-weight:600;font-size:10px;color:<?= $color ?>;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>"><?= $fmt($val) ?></td>
                  <?php endforeach; ?>
                  <td style="text-align:center;padding:3px;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>;font-weight:<?= $r['s2_absences'] > 0 ? '700' : '400' ?>;color:<?= $r['s2_absences'] > 0 ? '#dc2626' : '#9ca3af' ?>;font-size:10px"><?= $r['s2_absences'] ?></td>
                  <td style="text-align:center;padding:3px;font-weight:600;font-size:10px;color:#374151;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>"><?= $fmt($r['s2_total']) ?></td>
                  <td style="text-align:center;padding:3px;font-weight:700;font-size:11px;color:#374151;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>"><?= $fmt($r['s2_average']) ?></td>
                  <td style="text-align:center;padding:3px;border-bottom:2px solid #e5e7eb;background:<?= $groupBg ?>" data-rank="s2">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:4px;background:#f1f5f9;color:#64748b;font-size:9px;font-weight:600"><?= $r['s2_rank'] ?></span>
                  </td>
                </tr>
                <!-- Avg row -->
                <tr>
                  <td style="text-align:center;padding:2px;border-bottom:1px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>">
                    <span style="display:inline-block;font-size:7px;font-weight:700;padding:1px 3px;border-radius:2px;background:#d1fae5;color:#065f46">Avg</span>
                  </td>
                  <?php foreach ($courses as $c):
                    $sub = $r['subjects'][$c['id']] ?? ['avg'=>null];
                  ?>
                    <td style="text-align:center;padding:3px 4px;font-weight:600;font-size:10px;color:#374151;border-bottom:1px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>"><?= $fmt($sub['avg']) ?></td>
                  <?php endforeach; ?>
                  <td style="text-align:center;padding:3px;border-bottom:1px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>;color:#9ca3af;font-size:10px"><?= $r['avg_absences'] ?></td>
                  <td style="text-align:center;padding:3px;font-weight:600;font-size:10px;color:#374151;border-bottom:1px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>"><?= $fmt($r['avg_total']) ?></td>
                  <td style="text-align:center;padding:3px;font-weight:700;font-size:11px;color:#059669;border-bottom:1px solid #e5e7eb;background:<?= $groupBg ?>;<?= $colB ?>"><?= $fmt($r['avg_average']) ?></td>
                  <td style="text-align:center;padding:3px;border-bottom:1px solid #e5e7eb;background:<?= $groupBg ?>" data-rank="avg">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:4px;background:#f1f5f9;color:#64748b;font-size:9px;font-weight:600"><?= $r['avg_rank'] ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>

            <div class="pdf-footer">
              <span><?= e($pdf_doc_id) ?></span>
              <span><?= e($pdf_stamp) ?></span>
            </div>
          </div>
        </div>

        <script>
        var _sortMode = 'name';
        var _sortCycle = ['name', 'fy', 's2', 'avg'];

        function sortRoster() {
          var next = _sortCycle[(_sortCycle.indexOf(_sortMode) + 1) % _sortCycle.length];
          _sortMode = next;
          document.getElementById('rank-th').title = 'Sorted by: ' + next.toUpperCase() + ' (click to cycle)';

          var tbody = document.getElementById('roster-body');
          var groups = [];
          var trs = tbody.querySelectorAll('tr');
          for (var i = 0; i < trs.length; i += 3) {
            groups.push({ fy: trs[i], s2: trs[i+1], avg: trs[i+2] });
          }

          if (next === 'name') {
            groups.sort(function(a, b) {
              var aName = a.fy.querySelectorAll('td')[1];
              var bName = b.fy.querySelectorAll('td')[1];
              return (aName ? aName.textContent.trim() : '').localeCompare(bName ? bName.textContent.trim() : '');
            });
          } else {
            groups.sort(function(a, b) {
              var aEl = a.fy.querySelector('[data-rank="' + next + '"]');
              var bEl = b.fy.querySelector('[data-rank="' + next + '"]');
              return (aEl ? parseInt(aEl.textContent) || 999 : 999) - (bEl ? parseInt(bEl.textContent) || 999 : 999);
            });
          }
          groups.forEach(function(g) {
            tbody.appendChild(g.fy);
            tbody.appendChild(g.s2);
            tbody.appendChild(g.avg);
          });
          // Update roll numbers
          var rows = tbody.querySelectorAll('tr');
          var roll = 1;
          for (var i = 0; i < rows.length; i += 3) {
            var numCell = rows[i].querySelector('td:first-child');
            if (numCell) numCell.textContent = roll++;
          }
        }
        </script>
        <?php
    }
}
