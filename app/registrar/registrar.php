<?php
/**
 * Registrar — school-wide academic records: enrollments, transcripts, audits.
 * Scoped to the registrar's own school (users.school_id = me).
 */

class Ctl_registrar {
    public function run(): void {
        $u = require_role('registrar');
        $sid = (int)$u['school_id'];
        $route = trim($_GET['r'] ?? '', '/');
        $route = str_replace('registrar/', '', $route);

        if (in_array($route, ['semesters', 'admissions', 'graduation'], true) && !module_active($sid, 'university')) {
            http_response_code(403);
            die('The University module is not installed for this school.');
        }

        match ($route) {
            'dashboard' => $this->dashboard($u, $sid),
            'enrollments' => $this->enrollments($u, $sid),
            'transcripts' => $this->transcripts($u, $sid),
            'semesters' => $this->semesters($u, $sid),
            'admissions' => $this->admissions($u, $sid),
            'graduation' => $this->graduation($u, $sid),
            'scholarships' => $this->scholarships($u, $sid),
            'announcements' => $this->announcements($u, $sid),
            'audit' => $this->audit($u, $sid),
            default => $this->dashboard($u, $sid),
        };
    }

    private function dashboard(array $u, int $sid): void {
        $df = demo_filter('c');
        $stats = [
            'students' => (int)Database::scalar("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=? AND status='active'", [$sid], 0),
            'courses' => (int)Database::scalar("SELECT COUNT(*) FROM courses WHERE school_id=? AND is_demo = 0", [$sid], 0),
            'enrollments' => (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments ce JOIN courses c ON c.id=ce.course_id WHERE c.school_id=? AND c.is_demo = 0", [$sid], 0),
            'exams' => (int)Database::scalar("SELECT COUNT(*) FROM exams e JOIN courses c ON c.id=e.course_id WHERE c.school_id=? AND c.is_demo = 0", [$sid], 0),
            'graded' => (int)Database::scalar("SELECT COUNT(*) FROM exam_attempts ea JOIN exams e ON e.id=ea.exam_id JOIN courses c ON c.id=e.course_id WHERE c.school_id=? AND ea.status='graded' AND c.is_demo = 0", [$sid], 0),
            'transcripts' => (int)Database::scalar("SELECT COUNT(DISTINCT user_id) FROM course_enrollments ce JOIN courses c ON c.id=ce.course_id WHERE c.school_id=? AND c.is_demo = 0", [$sid], 0),
        ];
        $recent = Database::all(
            "SELECT ce.id, ce.enrolled_at, ce.progress, CONCAT(us.first_name,' ',us.last_name) AS student, us.student_id, co.title AS course
             FROM course_enrollments ce
             JOIN courses co ON co.id = ce.course_id
             JOIN users us ON us.id = ce.user_id
             WHERE co.school_id = ? ORDER BY ce.enrolled_at DESC LIMIT 8", [$sid]);
        Router::render('app/registrar/dashboard', ['title' => 'Registrar', 'stats' => $stats, 'recent' => $recent]);
    }

    private function enrollments(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['add_enrollment'])) {
                $courseId = (int)($_POST['course_id'] ?? 0);
                $studentId = (int)($_POST['student_id'] ?? 0);
                $semesterId = (int)($_POST['semester_id'] ?? 0);
                $course = Database::one("SELECT id, title, credit_hours FROM courses WHERE id = ? AND school_id = ?", [$courseId, $sid]);
                $student = Database::one("SELECT id FROM users WHERE id = ? AND school_id = ? AND role = 'student'", [$studentId, $sid]);
                if (!$course || !$student) {
                    flash('danger', 'Course or student not found in your school.');
                } elseif (Database::one("SELECT id FROM course_enrollments WHERE course_id = ? AND user_id = ?", [$courseId, $studentId])) {
                    flash('warning', 'Student is already enrolled in that course.');
                } else {
                    $sem = null;
                    if ($semesterId) {
                        $sem = Database::one("SELECT s.id FROM semesters s JOIN academic_years y ON y.id = s.year_id WHERE s.id = ? AND y.school_id = ?", [$semesterId, $sid]);
                        if (!$sem) $semesterId = 0;
                    }
                    Database::insert('course_enrollments', [
                        'course_id' => $courseId, 'user_id' => $studentId,
                        'semester_id' => $semesterId ?: null,
                    ]);
                    log_activity('enrollment.create', 'Registrar enrolled student #' . $studentId . ' in course #' . $courseId, (int)$u['id']);
                    flash('success', 'Enrollment added.');
                }
                redirect('registrar/enrollments');
            }
            if (isset($_POST['remove_enrollment'])) {
                $eid = (int)($_POST['remove_enrollment'] ?? 0);
                $row = Database::one(
                    "SELECT ce.id FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id
                     WHERE ce.id = ? AND c.school_id = ?", [$eid, $sid]);
                if ($row) {
                    Database::delete('course_enrollments', 'id = ?', [$eid]);
                    log_activity('enrollment.remove', 'Registrar removed enrollment #' . $eid, (int)$u['id']);
                    flash('success', 'Enrollment removed.');
                } else {
                    flash('danger', 'Enrollment not found.');
                }
                redirect('registrar/enrollments');
            }
        }
        $q = trim((string)($_GET['q'] ?? ''));
        $like = '%' . $q . '%';
        $semFilter = (int)($_GET['semester'] ?? 0);
        $semCond = $semFilter ? ' AND ce.semester_id = ' . $semFilter : '';
        $where = "co.school_id = ? AND (CONCAT(us.first_name,' ',us.last_name) LIKE ? OR us.student_id LIKE ? OR co.title LIKE ?)$semCond";
        $df = demo_filter('co');
        $rows = Database::all(
            "SELECT ce.id, ce.enrolled_at, ce.progress, ce.completed, ce.semester_id, CONCAT(us.first_name,' ',us.last_name) AS student,
                    us.student_id AS sid_no, co.title AS course, co.id AS course_id, co.credit_hours, s.name AS semester
             FROM course_enrollments ce
             JOIN courses co ON co.id = ce.course_id
             JOIN users us ON us.id = ce.user_id
             LEFT JOIN semesters s ON s.id = ce.semester_id
             WHERE $where $df ORDER BY ce.enrolled_at DESC LIMIT 200", [$sid, $like, $like, $like]);
        $courses = Database::all("SELECT id, title, code, credit_hours FROM courses WHERE school_id = ? AND status = 'published' $df ORDER BY title", [$sid]);
        $students = Database::all("SELECT id, student_id, CONCAT(first_name,' ',last_name) AS name FROM users WHERE school_id = ? AND role='student' AND status='active' ORDER BY last_name LIMIT 500", [$sid]);
        $semesters = Database::all(
            "SELECT s.id, s.name, y.name AS year_name FROM semesters s
             JOIN academic_years y ON y.id = s.year_id WHERE y.school_id = ? ORDER BY y.start_date DESC, s.start_date", [$sid]);
        Router::render('app/registrar/enrollments', [
            'title' => 'Enrollments', 'rows' => $rows, 'courses' => $courses, 'students' => $students,
            'q' => $q, 'semesters' => $semesters, 'semFilter' => $semFilter,
        ]);
    }

    private function transcripts(array $u, int $sid): void {
        $studentId = (int)($_GET['student_id'] ?? 0);
        $students = Database::all(
            "SELECT u.id, u.student_id, CONCAT(u.first_name,' ',u.last_name) AS name, u.email, u.enrollment_status, sc.name AS school
             FROM users u JOIN schools sc ON sc.id = u.school_id
             WHERE u.school_id = ? AND u.role = 'student' ORDER BY u.last_name LIMIT 500", [$sid]);
        $student = null;
        $transcript = [];
        $semesterGpas = [];
        $cgpa = null;
        $totalCredits = 0;
        if ($studentId) {
            $student = Database::one(
                "SELECT u.*, sc.name AS school_name, g.name AS group_name
                 FROM users u JOIN schools sc ON sc.id = u.school_id LEFT JOIN student_groups g ON g.id = u.group_id
                 WHERE u.id = ? AND u.school_id = ? AND u.role = 'student'", [$studentId, $sid]);
            if ($student) {
                $transcript = Database::all(
                    "SELECT co.id AS course_id, co.title AS course, co.code AS course_code, co.credit_hours,
                            ce.semester_id, s.name AS semester, y.name AS year_name,
                            (SELECT ROUND(AVG(ea.score),1) FROM exam_attempts ea JOIN exams e ON e.id=ea.exam_id
                             WHERE e.course_id=co.id AND ea.student_id=? AND ea.status='graded') AS exam_avg,
                            (SELECT ROUND(AVG(s.score),1) FROM assignment_submissions s JOIN assignments a ON a.id=s.assignment_id
                             WHERE a.course_id=co.id AND s.student_id=? AND s.score IS NOT NULL) AS assign_avg,
                            ce.progress, ce.completed, ce.completed_at, ce.enrolled_at
                     FROM course_enrollments ce JOIN courses co ON co.id = ce.course_id
                     LEFT JOIN semesters s ON s.id = ce.semester_id
                     LEFT JOIN academic_years y ON y.id = s.year_id
                     WHERE ce.user_id = ? AND co.school_id = ? ORDER BY s.start_date IS NULL, s.start_date, co.title", [$studentId, $studentId, $studentId, $sid]);
                $journey = Database::all(
                    "SELECT * FROM education_history WHERE student_id = ? ORDER BY entered_at ASC", [$studentId]);
                $perSem = [];
                $cgPoints = 0.0;
                $cgCredits = 0.0;
                foreach ($transcript as $row) {
                    $total = self::finalScore((float)$row['exam_avg'], (float)$row['assign_avg']);
                    if ($total === null) continue;
                    $credits = (float)$row['credit_hours'];
                    $pts = self::gradePoints($total) * $credits;
                    $cgPoints += $pts;
                    $cgCredits += $credits;
                    $sem = (int)$row['semester_id'];
                    if (!isset($perSem[$sem])) {
                        $perSem[$sem] = [
                            'name' => $row['semester'] ? $row['semester'] . ($row['year_name'] ? ' · ' . $row['year_name'] : '') : 'No semester',
                            'points' => 0.0, 'credits' => 0.0,
                        ];
                    }
                    $perSem[$sem]['points'] += $pts;
                    $perSem[$sem]['credits'] += $credits;
                }
                foreach ($perSem as $sem) {
                    $semesterGpas[] = [
                        'name' => $sem['name'],
                        'gpa' => $sem['credits'] > 0 ? round($sem['points'] / $sem['credits'], 2) : null,
                        'credits' => $sem['credits'],
                    ];
                }
                if ($cgCredits > 0) {
                    $cgpa = round($cgPoints / $cgCredits, 2);
                    $totalCredits = $cgCredits;
                }
            }
        }
        Router::render('app/registrar/transcripts', [
            'title' => 'Transcripts', 'students' => $students, 'student' => $student, 'transcript' => $transcript,
            'studentId' => $studentId, 'journey' => $journey ?? [], 'semesterGpas' => $semesterGpas,
            'cgpa' => $cgpa, 'totalCredits' => $totalCredits,
        ]);
    }

    /** Weighted final score (%) from exam + assignment averages. */
    private static function finalScore(float $exam, float $assign): ?float {
        $parts = [];
        if ($exam > 0) $parts[] = $exam * 0.6;
        if ($assign > 0) $parts[] = $assign * 0.4;
        if (!$parts) return null;
        return array_sum($parts);
    }

    /** 4.0 scale grade points from a percentage. */
    private static function gradePoints(float $pct): float {
        return match (true) {
            $pct >= 90 => 4.0, $pct >= 80 => 3.0, $pct >= 70 => 2.0, $pct >= 60 => 1.0, default => 0.0,
        };
    }

    private function semesters(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_year'])) {
                $name = trim((string)($_POST['name'] ?? ''));
                $dup = Database::one("SELECT id FROM academic_years WHERE school_id = ? AND name = ?", [$sid, $name]);
                if ($name === '' || $dup) {
                    flash('danger', 'Year name required and must be unique for this school.');
                } else {
                    Database::insert('academic_years', [
                        'school_id' => $sid, 'name' => $name,
                        'start_date' => (string)($_POST['start_date'] ?? '') ?: null,
                        'end_date' => (string)($_POST['end_date'] ?? '') ?: null,
                    ]);
                    flash('success', 'Academic year created.');
                }
                redirect('registrar/semesters');
            }
            if (isset($_POST['create_semester'])) {
                $yearId = (int)($_POST['year_id'] ?? 0);
                $name = trim((string)($_POST['name'] ?? ''));
                $year = Database::one("SELECT id FROM academic_years WHERE id = ? AND school_id = ?", [$yearId, $sid]);
                if (!$year || $name === '') {
                    flash('danger', 'Pick a valid year and enter a semester name.');
                } else {
                    Database::insert('semesters', [
                        'year_id' => $yearId, 'name' => $name,
                        'start_date' => (string)($_POST['start_date'] ?? '') ?: null,
                        'end_date' => (string)($_POST['end_date'] ?? '') ?: null,
                    ]);
                    flash('success', 'Semester created.');
                }
                redirect('registrar/semesters');
            }
            if (isset($_POST['set_active'])) {
                $semId = (int)($_POST['set_active'] ?? 0);
                $sem = Database::one(
                    "SELECT s.id FROM semesters s JOIN academic_years y ON y.id = s.year_id WHERE s.id = ? AND y.school_id = ?",
                    [$semId, $sid]);
                if ($sem) {
                    $ids = array_column(Database::all(
                        "SELECT s.id FROM semesters s JOIN academic_years y ON y.id = s.year_id WHERE y.school_id = ?", [$sid]), 'id');
                    if ($ids) Database::update('semesters', ['is_active' => 0], 'id IN (' . implode(',', $ids) . ')', []);
                    Database::update('semesters', ['is_active' => 1], 'id = ?', [$semId]);
                    log_activity('semester.activate', 'Registrar activated semester #' . $semId, (int)$u['id']);
                    flash('success', 'Active semester updated.');
                }
                redirect('registrar/semesters');
            }
        }
        $years = Database::all(
            "SELECT y.*, (SELECT COUNT(*) FROM semesters s WHERE s.year_id = y.id) AS sem_count
             FROM academic_years y WHERE y.school_id = ? ORDER BY y.start_date DESC, y.id DESC", [$sid]);
        $semRows = Database::all(
            "SELECT s.*, y.name AS year_name FROM semesters s JOIN academic_years y ON y.id = s.year_id
             WHERE y.school_id = ? ORDER BY y.start_date DESC, s.start_date", [$sid]);
        Router::render('app/registrar/semesters', ['title' => 'Semesters', 'years' => $years, 'semRows' => $semRows]);
    }

    private function admissions(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['apply_admission'])) {
                $email = strtolower(trim((string)($_POST['email'] ?? '')));
                $existing = Database::one("SELECT id FROM users WHERE email = ?", [$email]);
                if ($email === '' || $existing) {
                    flash('danger', 'A valid, unused applicant email is required.');
                    redirect('registrar/admissions');
                }
                Database::insert('admissions', [
                    'school_id' => $sid,
                    'department_id' => (int)($_POST['department_id'] ?? 0) ?: null,
                    'semester_id' => (int)($_POST['semester_id'] ?? 0) ?: null,
                    'first_name' => trim((string)($_POST['first_name'] ?? '')),
                    'last_name' => trim((string)($_POST['last_name'] ?? '')),
                    'email' => $email,
                    'phone' => trim((string)($_POST['phone'] ?? '')),
                    'national_id' => trim((string)($_POST['national_id'] ?? '')),
                    'prior_institution' => trim((string)($_POST['prior_institution'] ?? '')),
                    'program' => trim((string)($_POST['program'] ?? '')),
                ]);
                log_activity('admission.apply', 'Admission application for ' . $email, (int)$u['id']);
                flash('success', 'Application registered as pending.');
                redirect('registrar/admissions');
            }
            $appId = (int)($_POST['decide_admission'] ?? 0);
            $decision = (string)($_POST['decision'] ?? '');
            if ($appId && in_array($decision, ['admitted', 'rejected'], true)) {
                $app = Database::one("SELECT * FROM admissions WHERE id = ? AND school_id = ?", [$appId, $sid]);
                if (!$app) {
                    flash('danger', 'Application not found.');
                    redirect('registrar/admissions');
                }
                if ($decision === 'admitted') {
                    $dup = Database::one("SELECT id FROM users WHERE email = ?", [$app['email']]);
                    if ($dup) {
                        flash('warning', 'Applicant already has an account; admission not completed.');
                    } else {
                        $hash = password_hash('Passw0rd!', PASSWORD_BCRYPT);
                        $userId = Database::insert('users', [
                            'school_id' => $sid, 'role' => 'student', 'status' => 'active', 'verified' => 1,
                            'first_name' => $app['first_name'], 'last_name' => $app['last_name'],
                            'email' => $app['email'], 'phone' => $app['phone'] ?? '',
                            'password_hash' => $hash, 'national_id' => $app['national_id'] ?: null,
                            'student_id' => generate_student_id($sid),
                            'department_id' => $app['department_id'] ?? null,
                        ]);
                        $nationalId = ensure_national_id($userId);
                        education_enter($userId, $sid, 'enrolled', ($app['prior_institution'] ? 'Prior: ' . $app['prior_institution'] : '') . ($app['program'] ? ' | Program: ' . $app['program'] : ''));
                        Database::update('admissions', [
                            'status' => 'admitted', 'user_id' => $userId,
                            'decided_by' => (int)$u['id'], 'decided_at' => date('Y-m-d H:i:s'),
                        ], 'id = ?', [$appId]);
                        log_activity('admission.admit', 'Admitted ' . $app['email'] . ' as user #' . $userId, (int)$u['id']);
                        flash('success', 'Applicant admitted — student account created.');
                    }
                } else {
                    Database::update('admissions', [
                        'status' => 'rejected', 'decided_by' => (int)$u['id'], 'decided_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$appId]);
                    log_activity('admission.reject', 'Rejected application #' . $appId, (int)$u['id']);
                    flash('success', 'Application rejected.');
                }
                redirect('registrar/admissions');
            }
        }
        $filter = in_array($_GET['status'] ?? '', ['pending', 'admitted', 'rejected'], true) ? $_GET['status'] : 'pending';
        $apps = Database::all(
            "SELECT a.*, CONCAT(a.first_name,' ',a.last_name) AS applicant, d.name AS dept_name, s.name AS sem_name
             FROM admissions a
             LEFT JOIN departments d ON d.id = a.department_id
             LEFT JOIN semesters s ON s.id = a.semester_id
             WHERE a.school_id = ? AND a.status = ? ORDER BY a.created_at DESC LIMIT 200", [$sid, $filter]);
        $departments = Database::all("SELECT id, name FROM departments WHERE school_id = ? AND status = 'active' ORDER BY name", [$sid]);
        $semesters = Database::all(
            "SELECT s.id, s.name, y.name AS year_name FROM semesters s
             JOIN academic_years y ON y.id = s.year_id WHERE y.school_id = ? ORDER BY y.start_date DESC, s.start_date", [$sid]);
        Router::render('app/registrar/admissions', [
            'title' => 'Admissions', 'apps' => $apps, 'filter' => $filter,
            'departments' => $departments, 'semesters' => $semesters,
        ]);
    }

    private function graduation(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $stuId = (int)($_POST['issue_degree'] ?? 0);
            $student = Database::one("SELECT id FROM users WHERE id = ? AND school_id = ? AND role = 'student'", [$stuId, $sid]);
            if (!$student) {
                flash('danger', 'Student not found.');
                redirect('registrar/graduation');
            }
            $existing = Database::one("SELECT id FROM degrees WHERE student_id = ? AND school_id = ?", [$stuId, $sid]);
            if ($existing) {
                flash('warning', 'A degree was already issued for this student.');
                redirect('registrar/graduation');
            }
            $dept = Database::one(
                "SELECT d.name FROM departments d JOIN users u ON u.department_id = d.id WHERE u.id = ?", [$stuId]);
            $code = 'EDG-' . strtoupper(substr(hash('sha256', $stuId . '|' . $sid . '|' . microtime(true)), 0, 12));
            Database::insert('degrees', [
                'school_id' => $sid, 'student_id' => $stuId,
                'degree_code' => $code, 'degree_name' => $dept['name'] ?? 'Degree',
                'issued_by' => (int)$u['id'],
            ]);
            log_activity('degree.issue', 'Issued degree ' . $code . ' to student #' . $stuId, (int)$u['id']);
            flash('success', 'Degree issued. Verification code: ' . $code);
            redirect('registrar/graduation');
        }
        $rows = Database::all(
            "SELECT u.id, u.student_id, CONCAT(u.first_name,' ',u.last_name) AS student, u.email,
                    d.name AS dept_name, d.required_credits,
                    COALESCE(SUM(co.credit_hours), 0) AS earned_credits,
                    COUNT(CASE WHEN (SELECT ROUND(AVG(ea.score),1) FROM exam_attempts ea JOIN exams e ON e.id=ea.exam_id
                                    WHERE e.course_id=co.id AND ea.student_id=u.id AND ea.status='graded') >= 50
                              OR (SELECT ROUND(AVG(s2.score),1) FROM assignment_submissions s2 JOIN assignments a2 ON a2.id=s2.assignment_id
                                    WHERE a2.course_id=co.id AND s2.student_id=u.id AND s2.score IS NOT NULL) >= 50 THEN 1 END) AS passed_courses,
                    COUNT(ce.id) AS enrolled_courses
             FROM users u
             LEFT JOIN departments d ON d.id = u.department_id
             LEFT JOIN course_enrollments ce ON ce.user_id = u.id
             LEFT JOIN courses co ON co.id = ce.course_id AND co.school_id = ?
             WHERE u.school_id = ? AND u.role = 'student'
             GROUP BY u.id ORDER BY u.last_name LIMIT 300", [$sid, $sid]);
        $canGraduate = [];
        foreach ($rows as $r) {
            $credits = (float)$r['earned_credits'];
            $required = (int)$r['required_credits'];
            $hasDept = $r['dept_name'] !== null;
            $degree = Database::one("SELECT degree_code, issued_at FROM degrees WHERE student_id = ? AND school_id = ?", [(int)$r['id'], $sid]);
            $canGraduate[] = [
                'id' => (int)$r['id'], 'student' => $r['student'], 'student_id' => $r['student_id'], 'email' => $r['email'],
                'dept_name' => $r['dept_name'] ?? '—', 'required_credits' => $required, 'earned_credits' => $credits,
                'passed_courses' => (int)$r['passed_courses'], 'enrolled_courses' => (int)$r['enrolled_courses'],
                'eligible' => $hasDept && $credits >= $required,
                'degree' => $degree,
            ];
        }
        Router::render('app/registrar/graduation', ['title' => 'Graduation Audit', 'rows' => $canGraduate]);
    }

    private function scholarships(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_scholarship'])) {
                Database::insert('scholarships', [
                    'school_id' => $sid,
                    'name' => trim((string)($_POST['name'] ?? '')),
                    'description' => trim((string)($_POST['description'] ?? '')),
                    'amount' => max(0, (float)($_POST['amount'] ?? 0)),
                    'deadline' => (string)($_POST['deadline'] ?? '') ?: null,
                ]);
                log_activity('scholarship.create', 'Registrar created scholarship', (int)$u['id']);
                flash('success', 'Scholarship created.');
            }
            if (isset($_POST['toggle_scholarship'])) {
                $scId = (int)$_POST['toggle_scholarship'];
                $sc = Database::one("SELECT id, status FROM scholarships WHERE id = ? AND school_id = ?", [$scId, $sid]);
                if ($sc) {
                    Database::update('scholarships', ['status' => $sc['status'] === 'open' ? 'closed' : 'open'], 'id = ?', [$scId]);
                    flash('success', 'Scholarship status updated.');
                }
            }
            if (isset($_POST['award_scholarship'])) {
                $scId = (int)($_POST['scholarship_id'] ?? 0);
                $studentId = (int)($_POST['student_id'] ?? 0);
                $sc = Database::one("SELECT id, name FROM scholarships WHERE id = ? AND school_id = ? AND status = 'open'", [$scId, $sid]);
                $student = Database::one("SELECT id FROM users WHERE id = ? AND school_id = ? AND role = 'student'", [$studentId, $sid]);
                if (!$sc || !$student) {
                    flash('danger', 'Scholarship or student not found in your school.');
                } else {
                    Database::run(
                        "INSERT IGNORE INTO scholarship_awards (scholarship_id, student_id, awarded_by) VALUES (?, ?, ?)",
                        [$scId, $studentId, (int)$u['id']]
                    );
                    log_activity('scholarship.award', 'Awarded ' . $sc['name'] . ' to student #' . $studentId, (int)$u['id']);
                    flash('success', 'Scholarship awarded.');
                }
            }
            if (isset($_POST['revoke_scholarship'])) {
                $awardId = (int)$_POST['revoke_scholarship'];
                Database::delete(
                    'scholarship_awards',
                    "id = ? AND scholarship_id IN (SELECT id FROM scholarships WHERE school_id = ?)",
                    [$awardId, $sid]
                );
                flash('success', 'Award revoked.');
            }
            redirect('registrar/scholarships');
        }
        $rows = Database::all(
            "SELECT sc.*,
                    (SELECT COUNT(*) FROM scholarship_awards sa WHERE sa.scholarship_id = sc.id) AS awards
             FROM scholarships sc WHERE sc.school_id = ? ORDER BY sc.created_at DESC", [$sid]);
        $awardRows = Database::all(
            "SELECT sa.*, sc.name AS sch_name, CONCAT(st.first_name,' ',st.last_name) AS student, st.student_id
             FROM scholarship_awards sa
             JOIN scholarships sc ON sc.id = sa.scholarship_id
             JOIN users st ON st.id = sa.student_id
             WHERE sc.school_id = ? ORDER BY sa.awarded_at DESC LIMIT 100", [$sid]);
        $students = Database::all("SELECT id, student_id, CONCAT(first_name,' ',last_name) AS name FROM users WHERE school_id = ? AND role='student' AND status='active' ORDER BY last_name LIMIT 500", [$sid]);
        Router::render('app/registrar/scholarships', [
            'title' => 'Scholarships', 'rows' => $rows, 'awardRows' => $awardRows, 'students' => $students,
        ]);
    }

    private function announcements(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $title = trim((string)($_POST['title'] ?? ''));
            $content = trim((string)($_POST['content'] ?? ''));
            $audience = in_array($_POST['audience'] ?? 'all', ['all', 'students', 'teachers', 'parents'], true) ? $_POST['audience'] : 'all';
            $aid = Database::insert('announcements', [
                'school_id' => $sid, 'author_id' => (int)$u['id'],
                'title' => $title, 'content' => $content, 'audience' => $audience,
            ]);
            $roleMap = ['all' => null, 'students' => 'student', 'teachers' => 'teacher', 'parents' => 'parent'];
            $role = $roleMap[$audience] ?? null;
            $targets = $role
                ? Database::all("SELECT id FROM users WHERE role = ? AND school_id = ?", [$role, $sid])
                : Database::all("SELECT id FROM users WHERE role != 'guest' AND school_id = ?", [$sid]);
            foreach ($targets as $t) {
                notify((int)$t['id'], 'announcement', $title, mb_strimwidth($content, 0, 120, '…'), 'communication/announcement&id=' . $aid);
            }
            log_activity('announcement.create', 'Registrar announced to school #' . $sid, (int)$u['id']);
            flash('success', 'Announcement published to ' . count($targets) . ' users.');
            redirect('registrar/announcements');
        }
        $df = demo_filter('a');
        $rows = Database::all(
            "SELECT a.*, sc.name AS school_name FROM announcements a JOIN schools sc ON sc.id = a.school_id
             WHERE a.school_id = ? $df ORDER BY a.created_at DESC LIMIT 30", [$sid]);
        Router::render('app/registrar/announcements', ['title' => 'Announcements', 'rows' => $rows]);
    }

    private function audit(array $u, int $sid): void {
        $rows = Database::all(
            "SELECT al.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email
             FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id
             WHERE u.school_id = ?
             ORDER BY al.created_at DESC LIMIT 100", [$sid]);
        Router::render('app/registrar/audit', ['title' => 'Audit Log', 'rows' => $rows]);
    }
}
