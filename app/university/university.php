<?php
/**
 * University module — programs, course registration, clearance, transcripts,
 * fees/bursar, thesis, timetable, ID cards.
 */

class Ctl_university {
    public function run(): void {
        $u = require_role('registrar', 'dean', 'vice_dean', 'dept_head', 'lecturer', 'student', 'bursar', 'student_affairs', 'librarian');
        $sid = (int)$u['school_id'];
        $route = trim($_GET['r'] ?? '', '/');
        $route = str_replace('university/', '', $route);

        match ($route) {
            'programs'             => $this->programs($u, $sid),
            'program'              => $this->program_detail($u, $sid),
            'semesters'            => $this->semesters($u, $sid),
            'registration'         => $this->registration($u, $sid),
            'my-schedule'          => $this->my_schedule($u, $sid),
            'clearance'            => $this->clearance($u, $sid),
            'clearance/manage'     => $this->clearance_manage($u, $sid),
            'transcript'           => $this->transcript($u, $sid),
            'transcript/manage'    => $this->transcript_manage($u, $sid),
            'fees'                 => $this->fees($u, $sid),
            'fees/manage'          => $this->fees_manage($u, $sid),
            'theses'               => $this->theses($u, $sid),
            'thesis'               => $this->thesis_detail($u, $sid),
            'timetable'            => $this->timetable($u, $sid),
            'id-cards'             => $this->id_cards($u, $sid),
            default                => $this->programs($u, $sid),
        };
    }

    /* ========== PROGRAMS ========== */
    private function programs(array $u, int $sid): void {
        $demo = is_demo_mode() ? '' : ' AND p.is_demo = 0';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_program'])) {
                $name = trim((string)($_POST['name'] ?? ''));
                $code = strtoupper(trim((string)($_POST['code'] ?? '')));
                if ($name === '' || $code === '') {
                    flash('danger', 'Name and code are required.');
                } else {
                    Database::insert('programs', [
                        'school_id'     => $sid,
                        'faculty_id'    => (int)($_POST['faculty_id'] ?? 0) ?: null,
                        'department_id' => (int)($_POST['department_id'] ?? 0) ?: null,
                        'name'          => $name,
                        'code'          => $code,
                        'degree_type'   => in_array($_POST['degree_type'] ?? '', ['bachelor','master','phd','diploma','certificate'], true) ? $_POST['degree_type'] : 'bachelor',
                        'total_credits' => max(1, (int)($_POST['total_credits'] ?? 120)),
                        'duration_years'=> max(1, (int)($_POST['duration_years'] ?? 4)),
                    ]);
                    log_activity('program.create', "Created program $code", (int)$u['id']);
                    flash('success', 'Program created.');
                }
                redirect('university/programs');
            }
            if (isset($_POST['delete_program'])) {
                $pid = (int)$_POST['delete_program'];
                Database::update('programs', ['status' => 'archived'], 'id = ? AND school_id = ?', [$pid, $sid]);
                flash('success', 'Program archived.');
                redirect('university/programs');
            }
        }
        $programs = Database::all(
            "SELECT p.*, f.name AS faculty_name, d.name AS dept_name,
                    (SELECT COUNT(*) FROM student_programs sp WHERE sp.program_id = p.id AND sp.status = 'active') AS students
             FROM programs p
             LEFT JOIN faculties f ON f.id = p.faculty_id
             LEFT JOIN departments d ON d.id = p.department_id
             WHERE p.school_id = ? AND p.status != 'archived' $demo
             ORDER BY f.name, d.name, p.name", [$sid]);
        $faculties = Database::all("SELECT id, name FROM faculties WHERE school_id = ? ORDER BY name", [$sid]);
        $departments = Database::all("SELECT id, name FROM departments WHERE school_id = ? AND status = 'active' ORDER BY name", [$sid]);
        Router::render('app/university/programs', [
            'title' => 'Programs', 'programs' => $programs,
            'faculties' => $faculties, 'departments' => $departments,
        ]);
    }

    private function program_detail(array $u, int $sid): void {
        $pid = (int)($_GET['id'] ?? 0);
        $prog = Database::one("SELECT * FROM programs WHERE id = ? AND school_id = ?", [$pid, $sid]);
        if (!$prog) { flash('danger', 'Program not found.'); redirect('university/programs'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['enroll_student'])) {
                $studentId = (int)($_POST['student_id'] ?? 0);
                $stu = Database::one("SELECT id FROM users WHERE id = ? AND school_id = ? AND role = 'student'", [$studentId, $sid]);
                if ($stu) {
                    Database::insert('student_programs', [
                        'student_id' => $studentId, 'program_id' => $pid, 'status' => 'active',
                    ], true); // ignore duplicate
                    flash('success', 'Student enrolled in program.');
                } else {
                    flash('danger', 'Student not found.');
                }
                redirect("university/program&id=$pid");
            }
        }

        $students = Database::all(
            "SELECT sp.*, CONCAT(u.first_name,' ',u.last_name) AS name, u.student_id AS sid_no
             FROM student_programs sp JOIN users u ON u.id = sp.student_id
             WHERE sp.program_id = ? ORDER BY u.last_name", [$pid]);
        $allStudents = Database::all(
            "SELECT id, student_id, CONCAT(first_name,' ',last_name) AS name
             FROM users WHERE school_id = ? AND role = 'student' AND status = 'active' ORDER BY last_name LIMIT 500", [$sid]);
        Router::render('app/university/program_detail', [
            'title' => $prog['name'], 'prog' => $prog, 'students' => $students, 'allStudents' => $allStudents,
        ]);
    }

    /* ========== SEMESTERS ========== */
    private function semesters(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_semester'])) {
                $name = trim((string)($_POST['name'] ?? ''));
                $year = trim((string)($_POST['academic_year'] ?? ''));
                if ($name !== '' && $year !== '') {
                    Database::insert('semesters', [
                        'year_id' => (int)($_POST['year_id'] ?? 0) ?: 0,
                        'name' => $name,
                        'start_date' => (string)($_POST['start_date'] ?? '') ?: null,
                        'end_date' => (string)($_POST['end_date'] ?? '') ?: null,
                    ]);
                    flash('success', 'Semester created.');
                } else {
                    flash('danger', 'Name and academic year are required.');
                }
                redirect('university/semesters');
            }
        }
        $semesters = Database::all(
            "SELECT s.*, y.name AS year_name FROM semesters s
             JOIN academic_years y ON y.id = s.year_id
             WHERE y.school_id = ? ORDER BY y.start_date DESC, s.start_date DESC", [$sid]);
        $years = Database::all("SELECT id, name FROM academic_years WHERE school_id = ? ORDER BY start_date DESC", [$sid]);
        Router::render('app/university/semesters', [
            'title' => 'Semesters', 'semesters' => $semesters, 'years' => $years,
        ]);
    }

    /* ========== COURSE REGISTRATION ========== */
    private function registration(array $u, int $sid): void {
        $studentId = $u['role'] === 'student' ? (int)$u['id'] : (int)($_GET['student_id'] ?? 0);
        $semesterId = (int)($_GET['semester_id'] ?? 0);
        $demo = is_demo_mode() ? '' : ' AND co.is_demo = 0';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['register_course']) && $studentId) {
                $offeringId = (int)$_POST['register_course'];
                $result = register_course($studentId, $offeringId);
                flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Course registered.' : $result['error']);
                redirect("university/registration&semester_id=$semesterId" . ($studentId != $u['id'] ? "&student_id=$studentId" : ''));
            }
            if (isset($_POST['drop_course']) && $studentId) {
                $offeringId = (int)$_POST['drop_course'];
                $result = drop_course($studentId, $offeringId);
                flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Course dropped.' : $result['error']);
                redirect("university/registration&semester_id=$semesterId" . ($studentId != $u['id'] ? "&student_id=$studentId" : ''));
            }
        }

        $activeSem = $semesterId ?: (int)Database::scalar(
            "SELECT s.id FROM semesters s JOIN academic_years y ON y.id = s.year_id WHERE y.school_id = ? ORDER BY y.start_date DESC, s.start_date DESC LIMIT 1", [$sid], 0);

        $offerings = [];
        $registered = [];
        $load = ['enrolled' => 0, 'max' => 18, 'remaining' => 18];
        if ($activeSem) {
            $offerings = Database::all(
                "SELECT co.*, c.title, c.code, c.credits, CONCAT(u.first_name,' ',u.last_name) AS lecturer_name
                 FROM course_offerings co
                 JOIN courses c ON c.id = co.course_id
                 LEFT JOIN users u ON u.id = co.lecturer_id
                 WHERE co.semester_id = ? AND co.status = 'open' $demo
                 ORDER BY c.code", [$activeSem]);
            if ($studentId) {
                $registered = Database::all(
                    "SELECT r.*, c.title, c.code, c.credits
                     FROM registrations r
                     JOIN course_offerings co ON co.id = r.course_offering_id
                     JOIN courses c ON c.id = co.course_id
                     WHERE r.student_id = ? AND co.semester_id = ? AND r.status = 'registered'
                     ORDER BY c.code", [$studentId, $activeSem]);
                $load = student_credit_load($studentId, $activeSem);
            }
        }

        $semesters = Database::all(
            "SELECT s.id, s.name FROM semesters s JOIN academic_years y ON y.id = s.year_id
             WHERE y.school_id = ? ORDER BY y.start_date DESC, s.start_date DESC", [$sid]);
        $students = $u['role'] !== 'student'
            ? Database::all("SELECT id, student_id, CONCAT(first_name,' ',last_name) AS name FROM users WHERE school_id = ? AND role = 'student' AND status = 'active' ORDER BY last_name LIMIT 500", [$sid])
            : [];

        Router::render('app/university/registration', [
            'title' => 'Course Registration', 'offerings' => $offerings, 'registered' => $registered,
            'load' => $load, 'semesters' => $semesters, 'activeSem' => $activeSem,
            'studentId' => $studentId, 'students' => $students,
        ]);
    }

    /* ========== MY SCHEDULE ========== */
    private function my_schedule(array $u, int $sid): void {
        $studentId = (int)$u['id'];
        $rows = Database::all(
            "SELECT s.day, s.start_time, s.end_time, s.schedule_type, c.title, c.code, co.room
             FROM registrations r
             JOIN course_offerings co ON co.id = r.course_offering_id
             JOIN courses c ON c.id = co.course_id
             JOIN schedules s ON s.course_offering_id = co.id
             WHERE r.student_id = ? AND r.status = 'registered'
             ORDER BY FIELD(s.day,'monday','tuesday','wednesday','thursday','friday','saturday'), s.start_time",
            [$studentId]);
        Router::render('app/university/my_schedule', ['title' => 'My Schedule', 'rows' => $rows]);
    }

    /* ========== CLEARANCE ========== */
    private function clearance(array $u, int $sid): void {
        $studentId = (int)$u['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['request_clearance'])) {
                $type = in_array($_POST['type'] ?? '', ['graduation','transfer','withdrawal'], true) ? $_POST['type'] : 'graduation';
                $result = create_clearance_request($studentId, $type);
                flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Clearance requested. Tracking: ' . $result['tracking_code'] : $result['error']);
                redirect('university/clearance');
            }
        }

        $requests = Database::all(
            "SELECT cr.*, (SELECT COUNT(*) FROM clearance_items ci WHERE ci.request_id = cr.id AND ci.status = 'passed') AS passed,
                    (SELECT COUNT(*) FROM clearance_items ci WHERE ci.request_id = cr.id) AS total
             FROM clearance_requests cr WHERE cr.student_id = ? ORDER BY cr.requested_at DESC", [$studentId]);
        $items = [];
        foreach ($requests as $r) {
            $items[$r['id']] = Database::all(
                "SELECT ci.*, CONCAT(u.first_name,' ',u.last_name) AS checker_name
                 FROM clearance_items ci LEFT JOIN users u ON u.id = ci.checker_id
                 WHERE ci.request_id = ? ORDER BY ci.department", [$r['id']]);
        }
        Router::render('app/university/clearance', [
            'title' => 'My Clearance', 'requests' => $requests, 'items' => $items,
        ]);
    }

    private function clearance_manage(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $itemId = (int)($_POST['item_id'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['passed','failed','not_applicable'], true) ? $_POST['status'] : 'passed';
            $notes = trim((string)($_POST['notes'] ?? ''));
            if ($itemId) {
                $result = check_clearance_item($itemId, (int)$u['id'], $status, $notes);
                flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? "Item marked as $status." : $result['error']);
            }
            redirect('university/clearance/manage');
        }

        $filter = in_array($_GET['status'] ?? '', ['pending','in_progress','cleared','rejected'], true) ? $_GET['status'] : 'pending';
        $requests = Database::all(
            "SELECT cr.*, CONCAT(u.first_name,' ',u.last_name) AS student_name, u.student_id AS sid_no
             FROM clearance_requests cr JOIN users u ON u.id = cr.student_id
             WHERE cr.status = ? ORDER BY cr.requested_at DESC LIMIT 100", [$filter]);
        $items = [];
        foreach ($requests as $r) {
            $items[$r['id']] = Database::all(
                "SELECT ci.*, CONCAT(u.first_name,' ',u.last_name) AS checker_name
                 FROM clearance_items ci LEFT JOIN users u ON u.id = ci.checker_id
                 WHERE ci.request_id = ? ORDER BY ci.department", [$r['id']]);
        }
        Router::render('app/university/clearance_manage', [
            'title' => 'Clearance Management', 'requests' => $requests, 'items' => $items, 'filter' => $filter,
        ]);
    }

    /* ========== TRANSCRIPTS ========== */
    private function transcript(array $u, int $sid): void {
        $studentId = (int)$u['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['request_transcript'])) {
                $type = in_array($_POST['type'] ?? '', ['official','unofficial'], true) ? $_POST['type'] : 'unofficial';
                $result = create_transcript_request($studentId, $type);
                flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Transcript requested.' : $result['error']);
                redirect('university/transcript');
            }
        }

        $requests = Database::all(
            "SELECT tr.*, CONCAT(u.first_name,' ',u.last_name) AS processed_by_name
             FROM transcript_requests tr LEFT JOIN users u ON u.id = tr.processed_by
             WHERE tr.student_id = ? ORDER BY tr.requested_at DESC", [$studentId]);

        // Academic record
        $cGpa = compute_cgpa($studentId);
        $records = Database::all(
            "SELECT ar.*, c.title, c.code, sem.name AS semester_name
             FROM academic_records ar
             JOIN course_offerings co ON co.id = ar.course_offering_id
             JOIN courses c ON c.id = co.course_id
             JOIN semesters sem ON sem.id = ar.semester_id
             WHERE ar.student_id = ?
             ORDER BY sem.start_date DESC, c.code", [$studentId]);

        Router::render('app/university/transcript', [
            'title' => 'My Transcript', 'requests' => $requests, 'records' => $records, 'cgpa' => $cGpa,
        ]);
    }

    private function transcript_manage(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $reqId = (int)($_POST['process_request'] ?? 0);
            if ($reqId) {
                $hash = transcript_hash($reqId);
                Database::update('transcript_requests', [
                    'status' => 'ready', 'processed_at' => date('Y-m-d H:i:s'),
                    'processed_by' => (int)$u['id'], 'hash' => $hash,
                ], 'id = ?', [$reqId]);
                log_activity('transcript.process', "Processed transcript request #$reqId", (int)$u['id']);
                flash('success', 'Transcript ready for download.');
            }
            redirect('university/transcript/manage');
        }
        $requests = Database::all(
            "SELECT tr.*, CONCAT(u.first_name,' ',u.last_name) AS student_name, u.student_id AS sid_no
             FROM transcript_requests tr JOIN users u ON u.id = tr.student_id
             WHERE tr.status = 'pending' ORDER BY tr.requested_at DESC LIMIT 100");
        Router::render('app/university/transcript_manage', [
            'title' => 'Transcript Requests', 'requests' => $requests,
        ]);
    }

    /* ========== FEES / BURSAR ========== */
    private function fees(array $u, int $sid): void {
        $studentId = (int)$u['id'];
        $demo = is_demo_mode() ? '' : ' AND i.is_demo = 0';
        $invoices = Database::all(
            "SELECT i.*, sem.name AS sem_name
             FROM invoices i JOIN semesters sem ON sem.id = i.semester_id
             WHERE i.student_id = ? $demo ORDER BY i.created_at DESC", [$studentId]);
        $payments = Database::all(
            "SELECT p.*, i.id AS inv_id FROM payments p
             JOIN invoices i ON i.id = p.invoice_id
             WHERE p.student_id = ? ORDER BY p.paid_at DESC LIMIT 50", [$studentId]);
        $totalBalance = 0.0;
        foreach ($invoices as $inv) {
            if ($inv['status'] !== 'paid') $totalBalance += (float)$inv['total_amount'] - (float)$inv['paid_amount'];
        }
        Router::render('app/university/fees', [
            'title' => 'My Fees', 'invoices' => $invoices, 'payments' => $payments, 'totalBalance' => $totalBalance,
        ]);
    }

    private function fees_manage(array $u, int $sid): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            // Create fee structure
            if (isset($_POST['create_fee'])) {
                Database::insert('fee_structures', [
                    'school_id'   => $sid,
                    'name'        => trim((string)($_POST['name'] ?? '')),
                    'amount'      => max(0, (float)($_POST['amount'] ?? 0)),
                    'fee_type'    => in_array($_POST['fee_type'] ?? '', ['per_credit','fixed','per_course'], true) ? $_POST['fee_type'] : 'fixed',
                    'applies_to'  => trim((string)($_POST['applies_to'] ?? 'all')),
                    'semester_id' => (int)($_POST['semester_id'] ?? 0) ?: null,
                ]);
                flash('success', 'Fee structure created.');
                redirect('university/fees/manage');
            }
            // Generate invoice
            if (isset($_POST['generate_invoice'])) {
                $stuId = (int)$_POST['student_id'];
                $semId = (int)$_POST['semester_id'];
                $result = create_student_invoice($stuId, $semId);
                flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Invoice created: $' . number_format($result['total'], 2) : $result['error']);
                redirect('university/fees/manage');
            }
            // Record payment
            if (isset($_POST['record_payment'])) {
                $invId = (int)$_POST['invoice_id'];
                $amount = max(0, (float)($_POST['amount'] ?? 0));
                $method = (string)($_POST['method'] ?? 'cash');
                $ref = trim((string)($_POST['reference'] ?? ''));
                $result = record_payment($invId, $amount, $method, $ref, '', (int)$u['id']);
                flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Payment recorded.' : $result['error']);
                redirect('university/fees/manage');
            }
        }

        $demoFee = is_demo_mode() ? '' : ' AND fs.is_demo = 0';
        $demoInv = is_demo_mode() ? '' : ' AND i.is_demo = 0';
        $feeStructures = Database::all("SELECT * FROM fee_structures WHERE school_id = ? AND status = 'active' $demoFee ORDER BY name", [$sid]);
        $invoices = Database::all(
            "SELECT i.*, CONCAT(u.first_name,' ',u.last_name) AS student_name, u.student_id AS sid_no, sem.name AS sem_name
             FROM invoices i JOIN users u ON u.id = i.student_id JOIN semesters sem ON sem.id = i.semester_id
             WHERE u.school_id = ? $demoInv ORDER BY i.created_at DESC LIMIT 100", [$sid]);
        $students = Database::all("SELECT id, student_id, CONCAT(first_name,' ',last_name) AS name FROM users WHERE school_id = ? AND role = 'student' AND status = 'active' ORDER BY last_name LIMIT 500", [$sid]);
        $semesters = Database::all(
            "SELECT s.id, s.name FROM semesters s JOIN academic_years y ON y.id = s.year_id
             WHERE y.school_id = ? ORDER BY y.start_date DESC, s.start_date DESC", [$sid]);
        Router::render('app/university/fees_manage', [
            'title' => 'Fee Management', 'feeStructures' => $feeStructures, 'invoices' => $invoices,
            'students' => $students, 'semesters' => $semesters,
        ]);
    }

    /* ========== THESES ========== */
    private function theses(array $u, int $sid): void {
        $demo = is_demo_mode() ? '' : ' AND t.is_demo = 0';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_thesis'])) {
                $studentId = (int)$u['id'];
                $prog = Database::one("SELECT program_id FROM student_programs WHERE student_id = ? AND status = 'active' LIMIT 1", [$studentId]);
                if (!$prog) { flash('danger', 'You are not enrolled in a program.'); redirect('university/theses'); }
                $result = create_thesis($studentId, (int)$prog['program_id'], trim((string)($_POST['title'] ?? '')), trim((string)($_POST['abstract'] ?? '')));
                flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Thesis proposal created.' : $result['error']);
                redirect('university/theses');
            }
        }

        $myThesis = null;
        $theses = [];
        if ($u['role'] === 'student') {
            $myThesis = Database::one(
                "SELECT t.*, CONCAT(u.first_name,' ',u.last_name) AS advisor_name
                 FROM theses t LEFT JOIN users u ON u.id = t.advisor_id
                 WHERE t.student_id = ? $demo ORDER BY t.created_at DESC LIMIT 1", [(int)$u['id']]);
        } else {
            $theses = Database::all(
                "SELECT t.*, CONCAT(u.first_name,' ',u.last_name) AS student_name, u.student_id AS sid_no,
                        CONCAT(a.first_name,' ',a.last_name) AS advisor_name
                 FROM theses t
                 JOIN users u ON u.id = t.student_id
                 LEFT JOIN users a ON a.id = t.advisor_id
                 WHERE t.program_id IN (SELECT id FROM programs WHERE school_id = ?) $demo
                 ORDER BY t.created_at DESC LIMIT 100", [$sid]);
        }
        Router::render('app/university/theses', [
            'title' => 'Theses', 'myThesis' => $myThesis, 'theses' => $theses,
        ]);
    }

    private function thesis_detail(array $u, int $sid): void {
        $tid = (int)($_GET['id'] ?? 0);
        $thesis = Database::one(
            "SELECT t.*, CONCAT(u.first_name,' ',u.last_name) AS student_name, u.student_id AS sid_no,
                    CONCAT(a.first_name,' ',a.last_name) AS advisor_name
             FROM theses t
             JOIN users u ON u.id = t.student_id
             LEFT JOIN users a ON a.id = t.advisor_id
             WHERE t.id = ?", [$tid]);
        if (!$thesis) { flash('danger', 'Thesis not found.'); redirect('university/theses'); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['assign_advisor']) && in_array($u['role'], ['dean','dept_head'])) {
                assign_thesis_advisor($tid, (int)$_POST['advisor_id']);
                flash('success', 'Advisor assigned.');
            }
            if (isset($_POST['submit_chapter']) && (int)$thesis['student_id'] === (int)$u['id']) {
                submit_thesis_chapter((int)$_POST['chapter_id'], (int)$u['id']);
                flash('success', 'Chapter submitted for review.');
            }
            if (isset($_POST['defense_result']) && in_array($u['role'], ['dean','dept_head'])) {
                thesis_defense_result($tid, $_POST['result'] ?? 'pass', trim((string)($_POST['notes'] ?? '')));
                flash('success', 'Defense result recorded.');
            }
            if (isset($_POST['schedule_defense']) && in_array($u['role'], ['dean','dept_head'])) {
                schedule_defense($tid, (string)($_POST['defense_date'] ?? ''));
                flash('success', 'Defense scheduled.');
            }
            redirect("university/thesis&id=$tid");
        }

        $chapters = Database::all("SELECT tc.*, CONCAT(u.first_name,' ',u.last_name) AS advisor_name FROM thesis_chapters tc LEFT JOIN users u ON u.id = tc.advisor_id WHERE tc.thesis_id = ? ORDER BY tc.chapter_number", [$tid]);
        $committee = Database::all(
            "SELECT tc.*, CONCAT(u.first_name,' ',u.last_name) AS member_name
             FROM thesis_committee tc JOIN users u ON u.id = tc.member_id
             WHERE tc.thesis_id = ?", [$tid]);
        $lecturers = Database::all("SELECT id, CONCAT(first_name,' ',last_name) AS name FROM users WHERE school_id = ? AND role IN ('lecturer','dept_head','dean') ORDER BY last_name", [$sid]);
        Router::render('app/university/thesis_detail', [
            'title' => $thesis['title'] ?: 'Thesis', 'thesis' => $thesis,
            'chapters' => $chapters, 'committee' => $committee, 'lecturers' => $lecturers,
        ]);
    }

    /* ========== TIMETABLE ========== */
    private function timetable(array $u, int $sid): void {
        $semesterId = (int)($_GET['semester_id'] ?? 0);
        if (!$semesterId) {
            $semesterId = (int)Database::scalar(
                "SELECT s.id FROM semesters s JOIN academic_years y ON y.id = s.year_id WHERE y.school_id = ? ORDER BY y.start_date DESC, s.start_date DESC LIMIT 1", [$sid], 0);
        }
        $demo = is_demo_mode() ? '' : ' AND s.is_demo = 0';
        $rows = Database::all(
            "SELECT s.day, s.start_time, s.end_time, s.schedule_type, c.title, c.code, co.room, CONCAT(u.first_name,' ',u.last_name) AS lecturer
             FROM schedules s
             JOIN course_offerings co ON co.id = s.course_offering_id
             JOIN courses c ON c.id = co.course_id
             LEFT JOIN users u ON u.id = co.lecturer_id
             WHERE co.semester_id = ? $demo
             ORDER BY FIELD(s.day,'monday','tuesday','wednesday','thursday','friday','saturday'), s.start_time", [$semesterId]);
        $semesters = Database::all(
            "SELECT s.id, s.name FROM semesters s JOIN academic_years y ON y.id = s.year_id
             WHERE y.school_id = ? ORDER BY y.start_date DESC", [$sid]);
        Router::render('app/university/timetable', [
            'title' => 'Timetable', 'rows' => $rows, 'semesters' => $semesters, 'activeSem' => $semesterId,
        ]);
    }

    /* ========== ID CARDS ========== */
    private function id_cards(array $u, int $sid): void {
        $demo = is_demo_mode() ? '' : ' AND sc.is_demo = 0';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['generate_card'])) {
                $stuId = (int)$_POST['student_id'];
                $result = generate_student_card($stuId);
                flash($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Card generated.' : $result['error']);
                redirect('university/id-cards');
            }
        }
        $cards = Database::all(
            "SELECT sc.*, CONCAT(u.first_name,' ',u.last_name) AS student_name, u.student_id AS sid_no
             FROM student_cards sc JOIN users u ON u.id = sc.student_id
             WHERE u.school_id = ? $demo ORDER BY sc.issued_at DESC LIMIT 200", [$sid]);
        $students = Database::all(
            "SELECT id, student_id, CONCAT(first_name,' ',last_name) AS name
             FROM users WHERE school_id = ? AND role = 'student' AND status = 'active' ORDER BY last_name LIMIT 500", [$sid]);
        Router::render('app/university/id_cards', [
            'title' => 'Student ID Cards', 'cards' => $cards, 'students' => $students,
        ]);
    }
}
