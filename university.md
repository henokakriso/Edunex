# Edunex University System — Architecture Documentation

## Overview

Edunex supports 4 education tiers:
- **Primary** (Grade 1-4): Simple, teacher-paced, no AI tutor
- **Middle** (Grade 5-8): Moderate complexity, guided AI
- **Secondary** (Grade 9-12): Full features, parent portal, AI tutor
- **Higher Education** (University/College): Advanced academic system, no parent portal, faculty structure, clearance, transcripts

This document covers the **Higher Education (University)** system architecture.

---

## 1. Administrative Structure

### Hierarchy

```
University
├── Faculties (e.g., Faculty of Engineering, Faculty of Medicine)
│   ├── Departments (e.g., CS Dept, EE Dept)
│   │   ├── Programs (e.g., BSc CS, BSc SE)
│   │   │   ├── Courses (with credit hours)
│   │   │   └── Students enrolled in program
│   │   └── Head of Department (HOD)
│   └── Dean (faculty level)
├── Vice Dean (academic / administration)
├── Registrar (academic records, transcripts)
├── Bursar (fees, finance)
├── Student Affairs (housing, welfare)
├── Library Manager
└── Chancellor / President (top)
```

### Roles

| Role | Scope | Manages |
|------|-------|---------|
| `chancellor` | University-wide | Everything |
| `vice_chancellor` | University-wide | Deputies for chancellor |
| `dean` | Faculty | All departments in faculty |
| `vice_dean` | Faculty | Academic or admin side |
| `dept_head` | Department | Courses, lecturers, students |
| `lecturer` | Courses | Teaching, grading, research |
| `ta` (teaching assistant) | Courses | Assist lecturer |
| `registrar` | Academic records | Transcripts, enrollment, clearance |
| `bursar` | Finance | Fees, payments, invoices |
| `student_affairs` | Student welfare | Housing, discipline, clearance |
| `librarian` | Library | Books, lending, fines |
| `student` | Self | Own academics |

### Role vs Feature Access

| Feature | Dean | HOD | Lecturer | Registrar | Bursar | Student |
|---------|------|-----|----------|-----------|--------|---------|
| Manage Faculty | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Manage Department | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Create Courses | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Teach Courses | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Grade Students | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ |
| View Transcripts | ❌ | ❌ | ❌ | ✅ | ❌ | Self |
| Generate Transcripts | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| Manage Fees | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Process Clearance | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| Request Clearance | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Register Courses | ❌ | ❌ | ❌ | ✅ | ❌ | ✅ |
| Manage Library | ❌ | ❌ | ❌ | ❌ | ❌ | Librarian |

---

## 2. Credit & GPA System

### Grade Points

| Grade | Points | Description |
|-------|--------|-------------|
| A | 4.0 | Excellent |
| A- | 3.7 | |
| B+ | 3.3 | |
| B | 3.0 | Good |
| B- | 2.7 | |
| C+ | 2.3 | |
| C | 2.0 | Average |
| C- | 1.7 | |
| D+ | 1.3 | |
| D | 1.0 | Poor |
| F | 0.0 | Fail |
| I | - | Incomplete |
| W | - | Withdrawal |
| WF | - | Withdrawal Failing |

### Calculation

```
Quality Points = Credit Hours × Grade Points
GPA = Total Quality Points / Total Credit Hours

Example:
  CS101 (3 credits) → Grade A (4.0) → 3 × 4.0 = 12.0
  MATH101 (4 credits) → Grade B (3.0) → 4 × 3.0 = 12.0
  ENG101 (3 credits) → Grade B+ (3.3) → 3 × 3.3 = 9.9
  
  Total Quality Points = 33.9
  Total Credits = 10
  Semester GPA = 33.9 / 10 = 3.39
```

### CGPA (Cumulative)

```
CGPA = Σ (all quality points) / Σ (all credit hours)
```

### Academic Standing

| CGPA | Standing | Action |
|------|----------|--------|
| ≥ 3.5 | Dean's List | Recognition |
| ≥ 2.0 | Good Standing | None |
| 1.5 – 1.9 | Academic Probation | Warning, limited credits |
| < 1.5 | Academic Suspension | Suspended for 1 semester |
| < 1.0 (2 consecutive) | Academic Dismissal | Expelled |

### Academic Standing Rules

- Probation: Max 12 credit hours next semester
- After 2 consecutive probation semesters → Suspension
- Suspension: Cannot register for 1 semester
- Return from suspension: Probation status
- Good semester on probation → Return to Good Standing

---

## 3. Course Registration System

### Registration Flow

```
1. Registrar opens registration window (date range)
2. Student logs in → sees available courses for their program
3. Student selects courses
4. System checks:
   ├── Prerequisites passed?
   ├── Time conflicts?
   ├── Credit limit (max 18)?
   ├── Course not full?
   └── Program requirements?
5. Registration confirmed
6. Add/Drop period: first 2 weeks of semester
7. Registration locked after deadline
8. Overload request (if >18 credits) → HOD approval
```

### Prerequisite Example

```
CS201 Data Structures
├── Prerequisite: CS101 (Intro to Programming) — must pass with C or above
├── Corequisite: MATH102 (Discrete Math)
└── Credits: 3
```

### Registration Tables

```sql
course_offerings (
    id, course_id, semester_id, lecturer_id,
    max_students, current_students, schedule_json,
    status -- 'open', 'closed', 'cancelled'
)

registrations (
    id, student_id, course_offering_id,
    status, -- 'registered', 'dropped', 'completed'
    registered_at, dropped_at,
    grade -- populated after semester ends
)

prerequisites (
    id, course_id, required_course_id, min_grade
)
```

### Schedule Conflict Detection

```sql
-- Check if a student has time conflict
SELECT * FROM course_offerings co
JOIN registrations r ON r.course_offering_id = co.id
WHERE r.student_id = ? AND r.status = 'registered'
AND co.schedule_json OVERLAPS ? -- new course schedule
```

---

## 4. Clearance System

### Clearance Types

| Type | Trigger | Required Checks |
|------|---------|-----------------|
| Graduation | Student completing program | All departments |
| Transfer | Student moving to another university | All departments |
| Withdrawal | Student leaving university | Finance + Academic |
| Suspension | Academic suspension | Dorm + Library |

### Clearance Process (Graduation)

```
Step 1: Student requests clearance
        → System creates clearance_request record
        → Generates tracking code: CLR-2026-UNIV-CS-00001

Step 2: Each department checks independently
        ├── Library: All books returned? Fines paid?
        ├── Finance (Bursar): All fees paid? No balance?
        ├── Dormitory: Room checkout? Keys returned?
        ├── Lab/Workshop: Equipment returned? No damages?
        ├── Academic (Registrar): All grades submitted? No incomplete?
        ├── Disciplinary (Student Affairs): No pending cases?
        └── Department (HOD): All program courses completed?

Step 3: Each checker signs off digitally
        → clearance_item updated with checker_id, timestamp, status

Step 4: All items cleared?
        → Final approval by Registrar
        → Clearance status: 'cleared'

Step 5: Clearance certificate generated
        → PDF with QR code
        → Public verification URL
```

### Tracking Code

```
Format: CLR-YYYY-UNIV-XXX-NNNNN
Example: CLR-2026-UNIV-CS-00001

Where:
  CLR = Clearance
  2026 = Year
  UNIV = University code
  CS = Department code
  00001 = Sequential number

Verification URL: https://university.edu/verify/CLR-2026-UNIV-CS-00001
```

### Clearance Database Tables

```sql
clearance_requests (
    id, student_id, type, status,
    tracking_code UNIQUE,
    requested_at, completed_at,
    notes
)
-- type: 'graduation', 'transfer', 'withdrawal', 'suspension'
-- status: 'pending', 'in_progress', 'cleared', 'rejected'

clearance_items (
    id, request_id, department,
    checker_id, status, notes, checked_at,
    signature_hash -- HMAC of checker approval
)
-- department: 'library', 'finance', 'dormitory', 'lab', 'academic', 'disciplinary', 'department'
-- status: 'pending', 'cleared', 'failed', 'not_applicable'
```

### Clearance UI

```
Student Dashboard:
┌─────────────────────────────────────────┐
│ My Clearance Status                     │
│ Tracking: CLR-2026-UNIV-CS-00001        │
│ Status: In Progress (5/7 cleared)       │
│                                         │
│ ✅ Library        - Cleared (Aug 20)    │
│ ✅ Finance        - Cleared (Aug 21)    │
│ ✅ Dormitory      - Cleared (Aug 22)    │
│ ✅ Lab            - Cleared (Aug 22)    │
│ ⏳ Academic       - Pending             │
│ ❌ Disciplinary   - Failed (see notes)  │
│ ⏳ Department     - Pending             │
│                                         │
│ [View Certificate] [Print Tracking ID]  │
└─────────────────────────────────────────┘

Registrar Dashboard:
┌─────────────────────────────────────────┐
│ Pending Clearances                      │
│                                         │
│ CLR-2026-UNIV-CS-00001 - John Doe      │
│   Library ✅ Finance ✅ Dorm ✅         │
│   Lab ✅ Academic ⏳ Disc ❌ Dept ⏳    │
│   [View] [Approve] [Reject]            │
│                                         │
│ CLR-2026-UNIV-EE-00012 - Jane Smith    │
│   Library ✅ Finance ✅ Dorm ✅         │
│   Lab ⏳ Academic ⏳ Disc ✅ Dept ⏳    │
│   [View] [Approve] [Reject]            │
└─────────────────────────────────────────┘
```

---

## 5. Transcript System

### Official Transcript Contents

```
╔══════════════════════════════════════════════════╗
║          [UNIVERSITY NAME]                       ║
║          OFFICIAL ACADEMIC TRANSCRIPT            ║
║                                                  ║
║ Student: John Doe                                ║
║ ID: UNIV-2024-00001                              ║
║ Program: BSc Computer Science                    ║
║ Faculty: Faculty of Engineering                  ║
║ Department: Computer Science                     ║
║                                                  ║
╠══════════════════════════════════════════════════╣
║ SEMESTER: Fall 2024                              ║
║ ┌────────┬──────────────────┬───────┬───────┐    ║
║ │ Code   │ Course           │ Crd   │ Grade │    ║
║ ├────────┼──────────────────┼───────┼───────┤    ║
║ │ CS101  │ Intro to Prog    │ 3     │ A     │    ║
║ │ MATH101│ Calculus I       │ 4     │ B     │    ║
║ │ ENG101 │ English I        │ 3     │ B+    │    ║
║ └────────┴──────────────────┴───────┴───────┘    ║
║ Semester GPA: 3.39 | Credits: 10                 ║
║ Standing: Good Standing                          ║
║                                                  ║
║ SEMESTER: Spring 2025                            ║
║ ┌────────┬──────────────────┬───────┬───────┐    ║
║ │ CS102  │ Data Structures  │ 3     │ A-    │    ║
║ │ MATH102│ Discrete Math    │ 3     │ B+    │    ║
║ │ CS103  │ Digital Logic    │ 3     │ B     │    ║
║ └────────┴──────────────────┴───────┴───────┘    ║
║ Semester GPA: 3.33 | Credits: 9                  ║
║ Standing: Good Standing                          ║
║                                                  ║
╠══════════════════════════════════════════════════╣
║ CUMULATIVE GPA: 3.36                             ║
║ TOTAL CREDITS: 19                                ║
║ ACADEMIC STANDING: Good Standing                 ║
║                                                  ║
║ [Digital Signature / Hash]                       ║
║ Verification: https://university.edu/transcript  ║
╚══════════════════════════════════════════════════╝
```

### Transcript Features

- PDF generation with university letterhead
- Digital hash (SHA-256) for tamper-proof verification
- Public verification URL
- Request-based workflow (student requests → registrar approves → generates PDF)
- Multiple copies allowed (official, unofficial)
- Covers all semesters

### Transcript Database Tables

```sql
transcript_requests (
    id, student_id, type, status,
    requested_at, processed_at, processed_by,
    file_path -- PDF location
)
-- type: 'official', 'unofficial'
-- status: 'pending', 'processing', 'ready', 'delivered'

transcript_hashes (
    id, request_id, hash, verified_at
)
```

---

## 6. Thesis/Research System

### Thesis Workflow

```
Step 1: Topic Proposal
├── Student submits: Title, Abstract, Keywords
├── Selects preferred advisor
├── Department review
└── Approval / Rejection with comments

Step 2: Advisor Assignment
├── Department assigns advisor
├── Committee formed (2-3 members)
└── Timeline established

Step 3: Progress Tracking
├── Chapter submissions (1-5)
│   ├── Chapter 1: Introduction
│   ├── Chapter 2: Literature Review
│   ├── Chapter 3: Methodology
│   ├── Chapter 4: Results
│   └── Chapter 5: Conclusion
├── Advisor feedback per chapter
├── Milestone deadlines
└── Progress percentage tracking

Step 4: Defense
├── Scheduling (date, time, room)
├── Committee members assigned
├── Defense presentation
├── Result: Pass / Fail / Revise
└── Revision deadline (if needed)

Step 5: Final Submission
├── Final document upload
├── Plagiarism check (percentage)
├── Advisor sign-off
├── Department sign-off
└── Archive to university repository
```

### Thesis Tables

```sql
theses (
    id, student_id, program_id, title, abstract,
    advisor_id, status, topic_approved_at,
    defense_date, defense_result, defense_notes,
    final_submitted_at, archived_at
)
-- status: 'proposal', 'in_progress', 'defense', 'revision', 'completed', 'archived'

thesis_chapters (
    id, thesis_id, chapter_number, title,
    file_path, submitted_at, feedback, feedback_at,
    advisor_id, status
)
-- status: 'draft', 'submitted', 'reviewed', 'approved'

thesis_committee (
    id, thesis_id, member_id, role
)
-- role: 'advisor', 'co_advisor', 'examiner'
```

---

## 7. Student ID Card System

### Card Design

```
┌─────────────────────────────────────┐
│ [University Logo]                   │
│ UNIVERSITY NAME                     │
│                                     │
│ [Photo]  Name: John Doe            │
│          ID: UNIV-2024-00001       │
│          Program: BSc CS           │
│          Dept: Computer Science    │
│          Faculty: Engineering      │
│          Valid: 2024-2028          │
│                                     │
│ [Barcode: |||||||||||||||||||]      │
│ [QR Code]                           │
│                                     │
│ Student Signature: ____________    │
└─────────────────────────────────────┘
```

### Scannable Uses

- Library entry
- Exam verification (identity check)
- Dormitory access
- Clearance verification
- Lab/workshop access
- Event attendance

### ID Card Table

```sql
student_cards (
    id, student_id, card_number UNIQUE,
    barcode_data, qr_data,
    issued_at, expires_at, status,
    photo_path
)
-- status: 'active', 'expired', 'revoked'
```

---

## 8. Fee Management (Bursar)

### Fee Structure

```
Fee Categories:
├── Tuition (per credit hour × credits)
├── Registration fee (per semester)
├── Lab fee (per lab course)
├── Library fee (per semester)
├── Dormitory fee (per semester, if residential)
├── Student activity fee (per semester)
├── Technology fee (per semester)
└── Late registration penalty
```

### Invoice Example

```
╔══════════════════════════════════════╗
║ INVOICE #INV-2026-00001             ║
║ Student: John Doe                   ║
║ ID: UNIV-2024-00001                 ║
║ Semester: Fall 2026                 ║
╠══════════════════════════════════════╣
║ Tuition (15 cr × $100)    $1,500.00 ║
║ Registration Fee             $50.00 ║
║ Lab Fee (2 labs × $30)      $60.00 ║
║ Library Fee                  $25.00 ║
║ Technology Fee               $30.00 ║
╠══════════════════════════════════════╣
║ TOTAL                      $1,665.00 ║
║ Paid                         $0.00  ║
║ BALANCE                   $1,665.00 ║
╚══════════════════════════════════════╝
```

### Payment Tracking

```sql
fee_structures (
    id, university_id, name, amount,
    type, -- 'per_credit', 'fixed', 'per_course'
    applies_to -- 'all', 'program', 'course'
)

invoices (
    id, student_id, semester_id,
    total_amount, paid_amount, balance,
    status, -- 'pending', 'partial', 'paid', 'overdue'
    due_date
)

payments (
    id, invoice_id, amount,
    payment_method, reference_number,
    paid_at, recorded_by
)
```

---

## 9. Schedule/Timetable

### Weekly Timetable View

```
         Mon     Tue     Wed     Thu     Fri
08:00   CS101   ─────   CS101   ─────   CS101
        Room 3  ─────   Room 3  ─────   Room 3
        
09:00   ─────   MATH101 ─────   MATH101 ─────
        ─────   Room 5  ─────   Room 5  ─────
        
10:00   ENG101  ENG101  ─────   ─────   ─────
        Room 2  Room 2  ─────   ─────   ─────
        
11:00   ─────   ─────   CS103   CS103   ─────
        ─────   ─────   Lab A   Lab A   ─────
```

### Conflict Detection

```sql
-- Check room conflict
SELECT * FROM schedules
WHERE room_id = ? AND day = ? AND time_slot = ?
AND id != ? -- exclude current schedule

-- Check lecturer conflict
SELECT * FROM schedules
WHERE lecturer_id = ? AND day = ? AND time_slot = ?
AND id != ?

-- Check student conflict
SELECT * FROM schedules s
JOIN registrations r ON r.course_offering_id = s.course_offering_id
WHERE r.student_id = ? AND r.status = 'registered'
AND s.day = ? AND s.time_slot = ?
```

### Timetable Tables

```sql
schedules (
    id, course_offering_id,
    day, -- 'monday', 'tuesday', ...
    start_time, end_time,
    room_id, -- FK to rooms table
    type -- 'lecture', 'lab', 'tutorial'
)

rooms (
    id, name, building, capacity,
    type -- 'lecture_hall', 'lab', 'tutorial_room'
)
```

---

## 10. Academic Calendar

### Semester Structure

```
Academic Year 2026-2027
├── Fall 2026 (Sep - Jan)
│   ├── Registration: Aug 15 - Aug 30
│   ├── Add/Drop: Sep 1 - Sep 14
│   ├── Classes: Sep 1 - Dec 15
│   ├── Midterm: Oct 15 - Oct 20
│   ├── Final Exams: Dec 16 - Dec 25
│   └── Grade Deadline: Jan 5
│
├── Spring 2027 (Feb - Jun)
│   ├── Registration: Jan 20 - Feb 5
│   ├── Add/Drop: Feb 8 - Feb 21
│   ├── Classes: Feb 8 - May 25
│   ├── Midterm: Mar 20 - Mar 25
│   ├── Final Exams: May 26 - Jun 5
│   └── Grade Deadline: Jun 15
│
└── Summer 2027 (Jul - Aug)
    ├── Registration: Jun 20 - Jul 5
    ├── Classes: Jul 6 - Aug 15
    └── Final Exams: Aug 16 - Aug 22
```

### Calendar Tables

```sql
semesters (
    id, name, academic_year,
    start_date, end_date,
    registration_start, registration_end,
    add_drop_end,
    midterm_start, midterm_end,
    finals_start, finals_end,
    grade_deadline,
    status -- 'upcoming', 'active', 'completed'
)

academic_events (
    id, semester_id, title,
    event_date, event_type,
    description
)
-- event_type: 'holiday', 'ceremony', 'deadline', 'exam'
```

---

## 11. Database Schema Changes

### New Tables for University

```sql
-- Programs (degree programs)
CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    department_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    degree_type ENUM('bachelor', 'master', 'phd', 'diploma') NOT NULL,
    total_credits INT NOT NULL DEFAULT 120,
    duration_years INT NOT NULL DEFAULT 4,
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student Program Enrollment
CREATE TABLE student_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    program_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'withdrawn', 'transferred') DEFAULT 'active',
    expected_graduation DATE,
    actual_graduation DATE,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (program_id) REFERENCES programs(id)
);

-- Semesters
CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL, -- 'Fall 2026'
    academic_year VARCHAR(9) NOT NULL, -- '2026-2027'
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    registration_start DATE,
    registration_end DATE,
    add_drop_end DATE,
    midterm_start DATE,
    finals_start DATE,
    grade_deadline DATE,
    status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming'
);

-- Course Offerings (per semester)
CREATE TABLE course_offerings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    semester_id INT NOT NULL,
    lecturer_id INT,
    max_students INT DEFAULT 40,
    current_students INT DEFAULT 0,
    schedule_json JSON,
    status ENUM('open', 'closed', 'cancelled') DEFAULT 'open',
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id)
);

-- Course Registration
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_offering_id INT NOT NULL,
    status ENUM('registered', 'dropped', 'completed') DEFAULT 'registered',
    grade CHAR(2),
    quality_points DECIMAL(4,2),
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dropped_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_offering_id) REFERENCES course_offerings(id)
);

-- Prerequisites
CREATE TABLE prerequisites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    required_course_id INT NOT NULL,
    min_grade CHAR(2) DEFAULT 'D',
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (required_course_id) REFERENCES courses(id)
);

-- Academic Records (final grades)
CREATE TABLE academic_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_offering_id INT NOT NULL,
    grade CHAR(2) NOT NULL,
    credit_hours INT NOT NULL,
    quality_points DECIMAL(4,2) NOT NULL,
    semester_id INT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_offering_id) REFERENCES course_offerings(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id)
);

-- Fee Structures
CREATE TABLE fee_structures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    fee_type ENUM('per_credit', 'fixed', 'per_course') NOT NULL,
    applies_to VARCHAR(50) DEFAULT 'all',
    semester_id INT,
    FOREIGN KEY (university_id) REFERENCES schools(id)
);

-- Invoices
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    semester_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0,
    balance DECIMAL(10,2) GENERATED ALWAYS AS (total_amount - paid_amount),
    status ENUM('pending', 'partial', 'paid', 'overdue') DEFAULT 'pending',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(100),
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    recorded_by INT,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

-- Clearance Requests
CREATE TABLE clearance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    type ENUM('graduation', 'transfer', 'withdrawal', 'suspension') NOT NULL,
    status ENUM('pending', 'in_progress', 'cleared', 'rejected') DEFAULT 'pending',
    tracking_code VARCHAR(30) NOT NULL UNIQUE,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    notes TEXT,
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Clearance Items
CREATE TABLE clearance_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    department VARCHAR(50) NOT NULL,
    checker_id INT,
    status ENUM('pending', 'cleared', 'failed', 'not_applicable') DEFAULT 'pending',
    notes TEXT,
    checked_at TIMESTAMP NULL,
    signature_hash VARCHAR(64),
    FOREIGN KEY (request_id) REFERENCES clearance_requests(id)
);

-- Thesis
CREATE TABLE theses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    program_id INT NOT NULL,
    title VARCHAR(500) NOT NULL,
    abstract TEXT,
    advisor_id INT,
    status ENUM('proposal', 'in_progress', 'defense', 'revision', 'completed', 'archived') DEFAULT 'proposal',
    topic_approved_at TIMESTAMP NULL,
    defense_date DATE,
    defense_result ENUM('pass', 'fail', 'revise'),
    defense_notes TEXT,
    final_submitted_at TIMESTAMP NULL,
    archived_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (program_id) REFERENCES programs(id)
);

-- Thesis Chapters
CREATE TABLE thesis_chapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    chapter_number INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_path VARCHAR(500),
    submitted_at TIMESTAMP NULL,
    feedback TEXT,
    feedback_at TIMESTAMP NULL,
    advisor_id INT,
    status ENUM('draft', 'submitted', 'reviewed', 'approved') DEFAULT 'draft',
    FOREIGN KEY (thesis_id) REFERENCES theses(id)
);

-- Thesis Committee
CREATE TABLE thesis_committee (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    member_id INT NOT NULL,
    role ENUM('advisor', 'co_advisor', 'examiner') NOT NULL,
    FOREIGN KEY (thesis_id) REFERENCES theses(id),
    FOREIGN KEY (member_id) REFERENCES users(id)
);

-- Transcript Requests
CREATE TABLE transcript_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    type ENUM('official', 'unofficial') NOT NULL,
    status ENUM('pending', 'processing', 'ready', 'delivered') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by INT,
    file_path VARCHAR(500),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Student ID Cards
CREATE TABLE student_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    card_number VARCHAR(20) NOT NULL UNIQUE,
    barcode_data VARCHAR(50),
    qr_data VARCHAR(200),
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATE,
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    photo_path VARCHAR(500),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Schedules
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_offering_id INT NOT NULL,
    day ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room_id INT,
    schedule_type ENUM('lecture', 'lab', 'tutorial') DEFAULT 'lecture',
    FOREIGN KEY (course_offering_id) REFERENCES course_offerings(id)
);

-- Rooms
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    building VARCHAR(100),
    capacity INT NOT NULL,
    room_type ENUM('lecture_hall', 'lab', 'tutorial_room', 'seminar') NOT NULL,
    equipment JSON,
    status ENUM('available', 'maintenance', 'unavailable') DEFAULT 'available'
);

-- Academic Events
CREATE TABLE academic_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    event_date DATE NOT NULL,
    event_type ENUM('holiday', 'ceremony', 'deadline', 'exam') NOT NULL,
    description TEXT,
    FOREIGN KEY (semester_id) REFERENCES semesters(id)
);
```

---

## 12. New Routes for University

```php
// University Admin
Router::page('university/deans', Ctl_uni_deans, ['chancellor', 'vice_chancellor']);
Router::page('university/departments', Ctl_uni_departments, ['chancellor', 'vice_chancellor', 'dean']);
Router::page('university/programs', Ctl_uni_programs, ['chancellor', 'vice_chancellor', 'dean', 'dept_head']);
Router::page('university/semesters', Ctl_uni_semesters, ['registrar']);

// Registration
Router::page('university/registration', Ctl_uni_registration, ['student']);
Router::page('university/schedule', Ctl_uni_schedule, ['student', 'lecturer']);

// Clearance
Router::page('university/clearance', Ctl_uni_clearance, ['student']);
Router::page('university/clearance/manage', Ctl_uni_clearance_manage, ['registrar', 'dean', 'dept_head', 'bursar', 'librarian', 'student_affairs']);

// Transcript
Router::page('university/transcript', Ctl_uni_transcript, ['student']);
Router::page('university/transcript/manage', Ctl_uni_transcript_manage, ['registrar']);

// Thesis
Router::page('university/thesis', Ctl_uni_thesis, ['student']);
Router::page('university/thesis/manage', Ctl_uni_thesis_manage, ['dean', 'dept_head', 'lecturer']);

// Fees
Router::page('university/fees', Ctl_uni_fees, ['student']);
Router::page('university/fees/manage', Ctl_uni_fees_manage, ['bursar']);

// ID Cards
Router::page('university/id-cards', Ctl_uni_idcards, ['registrar', 'student_affairs']);

// Public Verification
Router::page('verify/clearance', Ctl_verify_clearance, ['guest']);
Router::page('verify/transcript', Ctl_verify_transcript, ['guest']);
```

---

## 13. Implementation Priority

| Phase | Feature | Effort | Dependencies |
|-------|---------|--------|--------------|
| **1** | Role system (dean, HOD, lecturer, etc.) | Medium | None |
| **2** | Programs + Semesters setup | Low | Phase 1 |
| **3** | Credit/GPA calculation | Medium | Phase 2 |
| **4** | Course registration + prerequisites | Medium | Phase 2, 3 |
| **5** | Clearance system | High | Phase 1 |
| **6** | Transcript generation | High | Phase 3 |
| **7** | Fee management (bursar) | Medium | Phase 1 |
| **8** | Thesis tracking | High | Phase 1 |
| **9** | Timetable/scheduling | Medium | Phase 2 |
| **10** | Student ID card generation | Low | Phase 1 |
| **11** | Academic calendar | Low | Phase 2 |

---

## 14. Migration from K-12

### What Changes Per Tier

| Feature | Primary | Middle | Secondary | University |
|---------|---------|--------|-----------|------------|
| Parent Portal | Full | Full | Limited | **Disabled** |
| AI Tutor | Disabled | Guided | Full | Research |
| Roles | Director, Teacher, Student | Same | Same | Dean, HOD, Lecturer, TA, Registrar, Bursar |
| Grading | Simple % | Simple % | Points | **GPA/Credits** |
| Courses | Fixed | Fixed | Some electives | **Electives + Prerequisites** |
| Registration | Auto-enrolled | Auto-enrolled | Auto-enrolled | **Self-registration** |
| Semester | Year | Year | Year | **Fall/Spring/Summer** |
| Transcript | Report card | Report card | Report card | **Official transcript** |
| Clearance | Not needed | Not needed | Not needed | **Required** |
| Thesis | Not applicable | Not applicable | Not applicable | **Full tracking** |
| Fees | Simple | Simple | Simple | **Credit-hour billing** |
| ID Card | Basic | Basic | Basic | **Barcode/QR scannable** |

---

*Last updated: 2026-08-23*
*Status: Planning*
