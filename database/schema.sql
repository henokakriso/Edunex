-- ============================================================
-- EDUNEX Database Schema v1.0
-- AI-Powered Ethiopian Learning Platform
-- MySQL 8.x / utf8mb4
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS edunex;
CREATE DATABASE edunex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edunex;

-- ------------------------------------------------------------
-- 1. SYSTEM / ORGANIZATION
-- ------------------------------------------------------------

CREATE TABLE settings (
  `key` VARCHAR(64) PRIMARY KEY,
  `value` TEXT
) ENGINE=InnoDB;

CREATE TABLE schools (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(150) NOT NULL,
  code VARCHAR(20) NOT NULL UNIQUE,
  type ENUM('school','university','college','training','other') DEFAULT 'school',
  education_level VARCHAR(40) DEFAULT 'secondary',
  address VARCHAR(255) DEFAULT '',
  city VARCHAR(100) DEFAULT '',
  phone VARCHAR(30) DEFAULT '',
  email VARCHAR(120) DEFAULT '',
  logo VARCHAR(255) DEFAULT '',
  status ENUM('active','suspended') DEFAULT 'active',
  zone_id INT UNSIGNED NULL,
  woreda_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
  FOREIGN KEY (woreda_id) REFERENCES woredas(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE school_modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  module_key VARCHAR(60) NOT NULL,
  enabled TINYINT(1) DEFAULT 1,
  installed_at DATETIME DEFAULT NULL,
  UNIQUE KEY uq_school_mod (school_id, module_key),
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE faculties (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  code VARCHAR(30) DEFAULT NULL,
  dean_id INT UNSIGNED DEFAULT NULL,
  vice_dean_id INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fac_school (school_id),
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (dean_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (vice_dean_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE departments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  faculty_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(120) NOT NULL,
  head VARCHAR(120) DEFAULT '',
  status ENUM('active','archived') DEFAULT 'active',
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE academic_years (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(40) NOT NULL,
  start_date DATE, end_date DATE,
  is_current TINYINT(1) DEFAULT 0,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE semesters (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  year_id INT UNSIGNED NOT NULL,
  name VARCHAR(60) NOT NULL,
  start_date DATE, end_date DATE,
  FOREIGN KEY (year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE subjects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  department_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(20) DEFAULT '',
  status ENUM('active','archived') DEFAULT 'active',
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE teacher_subjects (
  teacher_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (teacher_id, subject_id),
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE assignment_reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  submission_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  role ENUM('teacher','student') NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (submission_id) REFERENCES assignment_submissions(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student_groups (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL,          -- e.g. Grade 9-A
  grade VARCHAR(20) DEFAULT '',
  section VARCHAR(20) DEFAULT '',
  homeroom_teacher_id INT UNSIGNED DEFAULT NULL,  -- teacher responsible for verifying new students
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (homeroom_teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. USERS & AUTH
-- ------------------------------------------------------------

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED DEFAULT NULL,            -- NULL = platform-level (super admin)
  role ENUM('admin','sysadmin','director','teacher','student','parent','guest','registrar','dean','vice_dean','dept_head','lecturer','bursar','student_affairs','librarian') NOT NULL DEFAULT 'student',
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(30) DEFAULT '',
  student_id VARCHAR(40) DEFAULT NULL UNIQUE,   -- special transferable ID
  password_hash VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) DEFAULT '',
  language ENUM('en','am','om','ti','so') DEFAULT 'en',
  theme ENUM('dark','light') DEFAULT 'dark',
  group_id INT UNSIGNED DEFAULT NULL,           -- student group / class
  parent_id INT UNSIGNED DEFAULT NULL,          -- for students: linked parent account
  department_id INT UNSIGNED DEFAULT NULL,
  bio TEXT,
  birth_date DATE,
  gender ENUM('m','f','o') DEFAULT NULL,
  verified TINYINT(1) DEFAULT 0,
  verified_by INT UNSIGNED DEFAULT NULL,          -- homeroom teacher who approved the student
  verified_at DATETIME,                           -- approval timestamp (target: within 24h)
  enrollment_status ENUM('active','inactive') DEFAULT 'active',  -- inactive = re-exam track (courses + exams only)
  twofa_secret VARCHAR(512) DEFAULT '',           -- TOTP secret (legacy) OR current encrypted .hena payload
  twofa_enabled TINYINT(1) DEFAULT 0,
  hena_counter INT UNSIGNED DEFAULT 0,          -- USB .hena rotation counter (incremented each login)
  xp INT DEFAULT 0, level INT DEFAULT 1, streak INT DEFAULT 0, streak_last DATE,
  last_login DATETIME,
  status ENUM('active','pending','suspended','banned') DEFAULT 'pending',
  session_version INT DEFAULT 0,
  privacy JSON,
  is_demo TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES users(id),
  FOREIGN KEY (group_id) REFERENCES student_groups(id) ON DELETE SET NULL,
  FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_users_school_role (school_id, role),
  INDEX idx_users_student_id (student_id)
) ENGINE=InnoDB;

CREATE TABLE sessions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  selector VARCHAR(32) NOT NULL UNIQUE,
  token_hash VARCHAR(64) NOT NULL,
  ip VARCHAR(45) DEFAULT '',
  user_agent VARCHAR(255) DEFAULT '',
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  used TINYINT(1) DEFAULT 0,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE otp_codes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  code VARCHAR(255) NOT NULL,
  purpose ENUM('verify','2fa','reset','transfer') DEFAULT 'verify',
  used TINYINT(1) DEFAULT 0,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE login_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  status ENUM('success','failed','locked') NOT NULL,
  ip VARCHAR(45) DEFAULT '',
  user_agent VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
  role ENUM('admin','sysadmin','director','teacher','student','parent','guest') NOT NULL,
  permission VARCHAR(60) NOT NULL,
  PRIMARY KEY (role, permission)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  action VARCHAR(100) NOT NULL,
  detail VARCHAR(500) DEFAULT '',
  ip VARCHAR(45) DEFAULT '',
  user_agent VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_user (user_id),
  INDEX idx_activity_time (created_at)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. COURSES / LMS
-- ------------------------------------------------------------

CREATE TABLE courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  teacher_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED DEFAULT NULL,
  title VARCHAR(160) NOT NULL,
  code VARCHAR(30) DEFAULT '',
  description TEXT,
  image VARCHAR(255) DEFAULT '',
  level VARCHAR(30) DEFAULT '',
  status ENUM('draft','published','archived') DEFAULT 'draft',
  price DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE course_enrollments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  progress DECIMAL(5,2) DEFAULT 0,
  completed TINYINT(1) DEFAULT 0,
  completed_at DATETIME,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_enroll (course_id, user_id),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE course_modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lessons (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  module_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  type ENUM('video','pdf','notes','slides','audio','link') DEFAULT 'notes',
  content LONGTEXT,                    -- rich text / notes
  file_path VARCHAR(255) DEFAULT '',    -- pdf/slides/audio
  video_url VARCHAR(500) DEFAULT '',    -- mp4 / youtube
  duration_min INT DEFAULT 0,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lesson_progress (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  lesson_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  completed TINYINT(1) DEFAULT 0,
  position_sec INT DEFAULT 0,
  last_accessed DATETIME,
  UNIQUE KEY uq_lesson_prog (user_id, lesson_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookmarks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  lesson_id INT UNSIGNED NOT NULL,
  note VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. ASSIGNMENTS
-- ------------------------------------------------------------

CREATE TABLE assignments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  teacher_id INT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT,
  rubric TEXT,                            -- JSON [{criterion, max, weight}]
  max_score DECIMAL(6,2) DEFAULT 100,
  due_date DATETIME,
  allow_late TINYINT(1) DEFAULT 1,
  late_penalty DECIMAL(5,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE assignment_submissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assignment_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  content TEXT,
  file_path VARCHAR(255) DEFAULT '',
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_late TINYINT(1) DEFAULT 0,
  score DECIMAL(6,2) DEFAULT NULL,
  feedback TEXT,
  ai_feedback TEXT,
  graded_by INT UNSIGNED DEFAULT NULL,
  graded_at DATETIME,
  status ENUM('submitted','graded','returned') DEFAULT 'submitted',
  UNIQUE KEY uq_sub (assignment_id, student_id),
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. EXAMS
-- ------------------------------------------------------------

CREATE TABLE exams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  teacher_id INT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT,
  type ENUM('quiz','midterm','final','practice') DEFAULT 'quiz',
  duration_min INT DEFAULT 30,
  start_time DATETIME,
  end_time DATETIME,
  passing_score DECIMAL(5,2) DEFAULT 50,
  auto_grade TINYINT(1) DEFAULT 1,
  shuffle_questions TINYINT(1) DEFAULT 0,
  show_result TINYINT(1) DEFAULT 1,
  status ENUM('draft','published','closed') DEFAULT 'draft',
  results_sent_at DATETIME DEFAULT NULL,   -- when grades were sent to students & homeroom teachers
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE exam_questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exam_id INT UNSIGNED NOT NULL,
  type ENUM('mcq','truefalse','essay','fill','coding','matching','order','image','audio','video') NOT NULL,
  question TEXT NOT NULL,
  options JSON,                           -- mcq/matching options
  correct_answer TEXT,                    -- json for matching/order
  points DECIMAL(5,2) DEFAULT 1,
  media_path VARCHAR(255) DEFAULT '',     -- image/audio/video
  explanation TEXT,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE exam_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exam_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  submitted_at DATETIME,
  auto_save TEXT,                          -- JSON draft answers
  flagged TEXT,                            -- JSON [qid,...]
  score DECIMAL(6,2) DEFAULT NULL,
  total_points DECIMAL(6,2) DEFAULT 0,
  status ENUM('in_progress','submitted','graded') DEFAULT 'in_progress',
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE exam_answers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NOT NULL,
  answer TEXT,
  is_correct TINYINT(1) DEFAULT NULL,
  points_earned DECIMAL(5,2) DEFAULT 0,
  feedback TEXT,
  UNIQUE KEY uq_answer (attempt_id, question_id),
  FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES exam_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE grade_audit (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  school_id INT UNSIGNED NOT NULL,
  assessment_type ENUM('exam','assignment','manual') NOT NULL DEFAULT 'exam',
  assessment_id INT UNSIGNED NOT NULL,
  old_score VARCHAR(50) DEFAULT NULL,
  new_score VARCHAR(50) DEFAULT NULL,
  action ENUM('create','update','delete','override') NOT NULL DEFAULT 'update',
  reason VARCHAR(500) DEFAULT '',
  actor_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_grade_audit_student (student_id),
  INDEX idx_grade_audit_course (course_id),
  INDEX idx_grade_audit_assessment (assessment_type, assessment_id),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. ATTENDANCE
-- ------------------------------------------------------------

CREATE TABLE attendance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  date DATE NOT NULL,
  status ENUM('present','absent','late','excused') NOT NULL DEFAULT 'present',
  recorded_by INT UNSIGNED NOT NULL,
  note VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_att (course_id, student_id, date),
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE attendance_codes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  code VARCHAR(20) NOT NULL UNIQUE,
  created_by INT UNSIGNED NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. DIGITAL LIBRARY
-- ------------------------------------------------------------

CREATE TABLE library_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  type ENUM('book','notes','paper','slides','video','past_exam','tutorial') NOT NULL,
  author VARCHAR(120) DEFAULT '',
  category VARCHAR(80) DEFAULT '',
  description TEXT,
  cover VARCHAR(255) DEFAULT '',
  file_path VARCHAR(255) DEFAULT '',
  downloads INT DEFAULT 0,
  status ENUM('draft','published') DEFAULT 'published',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  INDEX idx_library_search (title, category)
) ENGINE=InnoDB;

CREATE TABLE library_favorites (
  user_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, item_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (item_id) REFERENCES library_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. COMMUNICATION
-- ------------------------------------------------------------

CREATE TABLE announcements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED DEFAULT NULL,
  author_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  content TEXT NOT NULL,
  pinned TINYINT(1) DEFAULT 0,
  audience ENUM('all','students','teachers','parents','course') DEFAULT 'all',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE conversations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  is_group TINYINT(1) DEFAULT 0,
  title VARCHAR(160) DEFAULT '',
  conv_key VARCHAR(128) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE conversation_members (
  conversation_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  last_read_at DATETIME,
  PRIMARY KEY (conversation_id, user_id),
  FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT UNSIGNED NOT NULL,
  sender_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  hmac VARCHAR(128) DEFAULT '',
  attachment VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE forum_topics (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  body TEXT,
  pinned TINYINT(1) DEFAULT 0,
  views INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE forum_posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  topic_id INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  is_answer TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (topic_id) REFERENCES forum_topics(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  target_type ENUM('post','forum','message','announcement') NOT NULL,
  target_id INT UNSIGNED NOT NULL,
  reaction VARCHAR(30) DEFAULT 'like',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_react (user_id, target_type, target_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. NOTIFICATIONS / CALENDAR
-- ------------------------------------------------------------

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('assignment','exam','feedback','announcement','achievement','message','system','reminder') NOT NULL,
  title VARCHAR(200) NOT NULL,
  body VARCHAR(500) DEFAULT '',
  link VARCHAR(255) DEFAULT '',
  read_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_notif_user (user_id, read_at)
) ENGINE=InnoDB;

CREATE TABLE calendar_events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED DEFAULT NULL,      -- owner (NULL = school-wide)
  title VARCHAR(200) NOT NULL,
  type ENUM('class','exam','assignment','event','meeting','deadline','birthday','reminder') DEFAULT 'event',
  start_at DATETIME NOT NULL,
  end_at DATETIME,
  all_day TINYINT(1) DEFAULT 0,
  location VARCHAR(200) DEFAULT '',
  description VARCHAR(500) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. GAMIFICATION
-- ------------------------------------------------------------

CREATE TABLE badges (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  icon VARCHAR(20) DEFAULT 'medal',
  description VARCHAR(200) DEFAULT '',
  xp_required INT DEFAULT 0,
  category ENUM('learning','streak','quiz','attendance','community','level') DEFAULT 'learning'
) ENGINE=InnoDB;

CREATE TABLE user_badges (
  user_id INT UNSIGNED NOT NULL,
  badge_id INT UNSIGNED NOT NULL,
  earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, badge_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE challenges (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  description VARCHAR(500),
  reward_xp INT DEFAULT 50,
  starts_at DATE, ends_at DATE,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_challenges (
  user_id INT UNSIGNED NOT NULL,
  challenge_id INT UNSIGNED NOT NULL,
  progress INT DEFAULT 0,
  completed TINYINT(1) DEFAULT 0,
  PRIMARY KEY (user_id, challenge_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE goals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  target INT DEFAULT 100,
  current INT DEFAULT 0,
  unit ENUM('xp','lessons','days','quizzes') DEFAULT 'lessons',
  due_date DATE,
  completed TINYINT(1) DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 11. CERTIFICATES
-- ------------------------------------------------------------

CREATE TABLE certificates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED NOT NULL,
  cert_code VARCHAR(40) NOT NULL UNIQUE,   -- unique public ID
  qr_hash VARCHAR(64) NOT NULL UNIQUE,
  issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  grade VARCHAR(20) DEFAULT '',
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 12. AI TUTOR
-- ------------------------------------------------------------

CREATE TABLE ai_chats (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  course_id INT UNSIGNED DEFAULT NULL,
  title VARCHAR(160) DEFAULT 'New chat',
  context TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE ai_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chat_id INT UNSIGNED NOT NULL,
  role ENUM('user','ai') NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (chat_id) REFERENCES ai_chats(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ai_decks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ai_cards (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  deck_id INT UNSIGNED NOT NULL,
  front TEXT NOT NULL,
  back TEXT NOT NULL,
  image VARCHAR(255) DEFAULT '',            -- optional picture card (filename in Flash Cards dir)
  box INT DEFAULT 0,                       -- spaced repetition (Leitner)
  reviewed_at DATETIME,
  FOREIGN KEY (deck_id) REFERENCES ai_decks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ai_question_bank (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED DEFAULT NULL,
  topic VARCHAR(120) NOT NULL,
  keywords VARCHAR(300) DEFAULT '',
  type ENUM('mcq','truefalse','fill','short') DEFAULT 'mcq',
  question TEXT NOT NULL,
  options JSON,
  answer TEXT NOT NULL,
  explanation TEXT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13. TRANSFERS (school-to-school via special student ID)
-- ------------------------------------------------------------

CREATE TABLE transfer_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,          -- new account at the target school
  source_student_id INT UNSIGNED DEFAULT NULL, -- old account at the source school (data source)
  from_school_id INT UNSIGNED NOT NULL,
  to_school_id INT UNSIGNED NOT NULL,
  referral_code VARCHAR(40) DEFAULT '',    -- special referral ID
  status ENUM('pending','approved','rejected','completed','cancelled') DEFAULT 'pending',
  reason VARCHAR(500) DEFAULT '',
  approved_by INT UNSIGNED DEFAULT NULL,
  decided_at DATETIME,
  record_snapshot JSON,                      -- portable academic record copied on approval
  completed_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (from_school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (to_school_id) REFERENCES schools(id) ON DELETE CASCADE,
  INDEX idx_transfer_student (student_id)
) ENGINE=InnoDB;

CREATE TABLE transfer_codes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,        -- e.g. TRF-XXXX-XXXX
  school_id INT UNSIGNED NOT NULL,         -- issuing school
  student_id INT UNSIGNED DEFAULT NULL,    -- consumed by
  purpose ENUM('referral','transfer') DEFAULT 'referral',
  used TINYINT(1) DEFAULT 0,
  expires_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 14. FILES & REPORTS
-- ------------------------------------------------------------

CREATE TABLE ledger (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  actor_id INT UNSIGNED NOT NULL,
  event_type VARCHAR(60) NOT NULL,          -- grade.set, attendance.marked, user.created…
  entity_type VARCHAR(60) NOT NULL,         -- exam_attempt / assignment_submission / certificate…
  entity_id INT UNSIGNED NOT NULL,
  payload TEXT,                              -- JSON snapshot of the record at write time
  prev_hash CHAR(64) NOT NULL,               -- SHA-256 of previous ledger entry
  record_hash CHAR(64) NOT NULL,             -- SHA-256 of (prev_hash + payload + timestamp)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ledger_school (school_id, id),
  INDEX idx_ledger_entity (entity_type, entity_id),
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE files (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  path VARCHAR(255) NOT NULL,
  mime VARCHAR(100) DEFAULT '',
  size INT DEFAULT 0,
  version INT DEFAULT 1,
  parent_id INT UNSIGNED DEFAULT NULL,     -- folder
  is_folder TINYINT(1) DEFAULT 0,
  deleted_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE file_versions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  file_id INT UNSIGNED NOT NULL,
  version INT NOT NULL,
  path VARCHAR(255) NOT NULL,
  size INT DEFAULT 0,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reports (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('attendance','grade','course','teacher','student','department','financial','system') NOT NULL,
  title VARCHAR(200) NOT NULL,
  format ENUM('pdf','excel','csv') DEFAULT 'pdf',
  file_path VARCHAR(255) DEFAULT '',
  filters VARCHAR(500) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- BASE SEED (always imported — system config only, no demo data)
-- ============================================================

INSERT INTO settings (`key`, `value`) VALUES
  ('site_name', 'Edunex'),
  ('site_tagline', 'AI-Powered Ethiopian Learning Platform'),
  ('maintenance_mode', '0'),
  ('registration_open', '1'),
  ('default_theme', 'dark'),
  ('default_language', 'en'),
  ('announcement', ''),
  ('currency', 'ETB'),
  ('support_email', 'support@edunex.local'),
  ('school_registration_open', '1'),
  ('ai_enabled', '1'),
  ('ai_provider', 'local'),
  ('ai_api_url', ''),
  ('ai_api_key', ''),
  ('ai_model', ''),
  ('google_login_enabled', '0'),
  ('max_upload_mb', '50'),
  ('session_timeout_min', '120');

-- Role permissions
INSERT INTO role_permissions VALUES
  ('admin','dashboard'), ('admin','profile.view'), ('admin','notifications.view'),
  ('admin','courses.manage'), ('admin','courses.create'), ('admin','courses.view'),
  ('admin','lessons.manage'), ('admin','exams.manage'), ('admin','exams.create'),
  ('admin','exams.take'), ('admin','exams.grade'), ('admin','exams.view'),
  ('admin','assignments.manage'), ('admin','assignments.create'), ('admin','assignments.grade'),
  ('admin','attendance.record'), ('admin','attendance.view'), ('admin','attendance.manage'),
  ('admin','attendance.export'), ('admin','grades.manage'), ('admin','grades.view'),
  ('admin','grades.export'), ('admin','library.manage'), ('admin','library.view'),
  ('admin','library.upload'), ('admin','forum.post'), ('admin','forum.moderate'),
  ('admin','messages.send'), ('admin','messages.view'), ('admin','announcements.manage'),
  ('admin','comments.view'), ('admin','gamification.view'), ('admin','badges.manage'),
  ('admin','goals.award'), ('admin','leaderboard.view'), ('admin','files.view'),
  ('admin','files.upload'), ('admin','files.manage'), ('admin','calendar.view'),
  ('admin','calendar.create'), ('admin','ai.tutor'), ('admin','ai.assistant'),
  ('admin','ai.flashcards'), ('admin','transfers.manage'), ('admin','transfers.approve'),
  ('admin','users.view'), ('admin','users.manage'), ('admin','users.create'),
  ('admin','reports.view'), ('admin','reports.export'), ('admin','analytics.view'),
  ('admin','settings.manage'), ('admin','backups.manage'), ('admin','logs.view'),
  ('admin','ledger.verify'), ('admin','search.global'),
  ('director','schools.view'), ('director','teachers.manage'), ('director','students.view'),
  ('director','courses.view'), ('director','exams.view'), ('director','attendance.view'),
  ('director','transfers.manage'), ('director','reports.generate'), ('director','library.view'),
  ('director','users.import'), ('director','analytics.view'), ('director','reports.export'),
  ('director','users.view'), ('director','messages.send'), ('director','messages.view'),
  ('director','announcements.manage'), ('director','accounting.view'),
  ('teacher','courses.manage'), ('teacher','exams.manage'), ('teacher','assignments.manage'),
  ('teacher','attendance.manage'), ('teacher','library.manage'), ('teacher','grades.manage'),
  ('teacher','reports.generate'), ('teacher','announcements.manage'), ('teacher','forum.manage'),
  ('teacher','students.verify'), ('teacher','users.import'), ('teacher','parents.manage'),
  ('teacher','courses.view'), ('teacher','lessons.manage'), ('teacher','exams.create'),
  ('teacher','exams.grade'), ('teacher','assignments.create'), ('teacher','assignments.grade'),
  ('teacher','attendance.record'), ('teacher','attendance.view'), ('teacher','grades.view'),
  ('teacher','library.view'), ('teacher','library.upload'), ('teacher','forum.post'),
  ('teacher','messages.send'), ('teacher','messages.view'), ('teacher','reports.view'),
  ('teacher','analytics.view'), ('teacher','files.view'), ('teacher','files.upload'),
  ('teacher','calendar.view'), ('teacher','calendar.create'), ('teacher','ai.tutor'),
  ('teacher','ai.assistant'), ('teacher','ai.flashcards'), ('teacher','goals.award'),
  ('student','courses.view'), ('student','courses.enroll'), ('student','exams.take'),
  ('student','exams.view'), ('student','assignments.submit'), ('student','library.view'),
  ('student','library.borrow'), ('student','forum.post'), ('student','forum.reply'),
  ('student','messages.send'), ('student','certificates.view'), ('student','attendance.view'),
  ('student','grades.view'), ('student','gamification.view'), ('student','leaderboard.view'),
  ('student','goals.view'), ('student','files.view'), ('student','files.upload'),
  ('student','calendar.view'), ('student','ai.tutor'), ('student','ai.assistant'),
  ('student','ai.flashcards'), ('student','transfers.view'), ('student','transfers.apply'),
  ('student','registration.view'), ('student','registration.register'), ('student','registration.drop'),
  ('student','clearance.view'), ('student','clearance.request'), ('student','transcript.view'),
  ('student','transcript.request'), ('student','fees.view'), ('student','thesis.view'),
  ('student','thesis.create'), ('student','thesis.submit'),
  ('parent','children.view'), ('parent','reports.view'), ('parent','grades.view'),
  ('parent','attendance.view'), ('parent','messages.view'), ('parent','messages.send'),
  ('parent','courses.view'), ('parent','assignments.view'), ('parent','calendar.view'),
  ('guest','courses.view'),
  -- University roles
  ('registrar','dashboard'), ('registrar','programs.manage'), ('registrar','semesters.manage'),
  ('registrar','enrollments.manage'), ('registrar','transcripts.manage'), ('registrar','transcripts.generate'),
  ('registrar','clearance.manage'), ('registrar','grades.view'), ('registrar','grades.manage'),
  ('registrar','admissions.manage'), ('registrar','announcements.manage'), ('registrar','audit.view'),
  ('registrar','id_cards.manage'), ('registrar','timetable.view'), ('registrar','timetable.manage'),
  ('dean','dashboard'), ('dean','programs.view'), ('dean','programs.manage'),
  ('dean','departments.view'), ('dean','departments.manage'), ('dean','courses.approve'),
  ('dean','teachers.view'), ('dean','theses.manage'), ('dean','theses.view'),
  ('dean','clearance.manage'), ('dean','analytics.view'), ('dean','announcements.manage'),
  ('vice_dean','dashboard'), ('vice_dean','programs.view'), ('vice_dean','courses.approve'),
  ('vice_dean','theses.view'), ('vice_dean','analytics.view'),
  ('dept_head','dashboard'), ('dept_head','courses.view'), ('dept_head','courses.manage'),
  ('dept_head','theses.view'), ('dept_head','theses.manage'), ('dept_head','analytics.view'),
  ('dept_head','clearance.manage'),
  ('lecturer','dashboard'), ('lecturer','courses.view'), ('lecturer','courses.manage'),
  ('lecturer','exams.create'), ('lecturer','exams.grade'), ('lecturer','assignments.create'),
  ('lecturer','assignments.grade'), ('lecturer','attendance.record'), ('lecturer','attendance.view'),
  ('lecturer','grades.view'), ('lecturer','grades.manage'), ('lecturer','analytics.view'),
  ('bursar','dashboard'), ('bursar','fees.manage'), ('bursar','fees.view'),
  ('bursar','invoices.manage'), ('bursar','payments.record'), ('bursar','clearance.manage'),
  ('bursar','reports.view'),
  ('student_affairs','dashboard'), ('student_affairs','clearance.manage'),
  ('student_affairs','id_cards.manage'), ('student_affairs','students.view'),
  ('librarian','dashboard'), ('librarian','library.manage'), ('librarian','library.view'),
  ('librarian','library.upload'), ('librarian','clearance.manage');

-- Badges (system content)
INSERT INTO badges (name, icon, description, xp_required, category) VALUES
  ('First Steps', 'leaf', 'Complete your first lesson', 50, 'learning'),
  ('Bookworm', 'books', 'Read 5 lessons', 200, 'learning'),
  ('Quiz Whiz', 'brain', 'Score 80%+ on any quiz', 300, 'quiz'),
  ('Perfect Attendance', 'target', '7 days of perfect attendance', 350, 'attendance'),
  ('On Fire', 'flame', '7-day learning streak', 400, 'streak'),
  ('Scholar', 'graduation', 'Complete a full course', 600, 'level'),
  ('Helping Hand', 'handshake', 'Answer 5 forum questions', 250, 'community'),
  ('Marathoner', 'run', 'Reach level 5', 500, 'level');

-- Super admin placeholder — install.php replaces the hash with a random password.
-- Super admin manages all schools; Directors (created by super admin) run each school.
INSERT INTO users (school_id, role, first_name, last_name, email, phone, password_hash, verified, status)
VALUES (NULL, 'admin', 'Super', 'Admin', 'superadmin@edunex.local', '', '$2y$12$placeholderplaceholderplaceholderplaceholderplaceholderplaceholderplaceholderpla', 1, 'active');

-- ============================================================
-- DEMO SEED (only imported with --demo flag for development/tests)
-- ============================================================

INSERT INTO schools (id, name, code, type, city, email, status) VALUES
  (1, 'Addis Ababa International School', 'AAIS', 'school', 'Addis Ababa', 'info@aais.edu.et', 'active'),
  (2, 'Bahir Dar University', 'BDU', 'university', 'Bahir Dar', 'info@bdu.edu.et', 'active'),
  (3, 'Hawassa Preparatory School', 'HPS', 'school', 'Hawassa', 'info@hps.edu.et', 'active');

INSERT INTO departments (school_id, name, head) VALUES
  (1, 'Science', 'Dr. Bekele'), (1, 'Languages', 'Mrs. Tigist'),
  (2, 'Computer Science', 'Prof. Alem'), (2, 'Engineering', 'Dr. Marta'),
  (3, 'General', 'Mr. Dawit');

INSERT INTO academic_years (school_id, name, start_date, end_date, is_current) VALUES
  (1, '2025/2026', '2025-09-01', '2026-07-31', 1),
  (2, '2025/2026', '2025-09-01', '2026-07-31', 1),
  (3, '2025/2026', '2025-09-01', '2026-07-31', 1);

INSERT INTO semesters (year_id, name, start_date, end_date) VALUES
  (1, 'Semester 1', '2025-09-01', '2026-01-31'),
  (2, 'Semester 1', '2025-09-01', '2026-01-31'),
  (3, 'Semester 1', '2025-09-01', '2026-01-31');

INSERT INTO subjects (school_id, department_id, name, code) VALUES
  (1, 1, 'Mathematics', 'MATH101'), (1, 1, 'Physics', 'PHY101'), (1, 2, 'English', 'ENG101'),
  (2, 3, 'Data Structures', 'CS201'), (2, 4, 'Circuit Theory', 'EE201'),
  (3, 5, 'General Science', 'GSC101');

-- WARNING: Demo passwords are for development only. Change them before production use.
-- The installer can generate unique passwords with --admin-pass flag.
-- explicit ids: id 1 is the super admin from the base seed
INSERT INTO users (id, school_id, role, first_name, last_name, email, phone, student_id, password_hash, group_id, department_id, verified, verified_by, verified_at, xp, level, streak, language, theme, status, last_login) VALUES
  (2, 1, 'admin', 'Sara', 'Tesfaye',   'admin@edunex.local',  '+251911000001', NULL, '$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u', NULL, 1, 1, NULL, NOW(), 5000, 8, 12, 'en', 'dark', 'active', NOW()),
  (8, 1, 'director', 'Dir', 'One',    'director@edunex.local', '+251911000007', NULL, '$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u', NULL, 1, 1, NULL, NOW(), 1000, 3, 1, 'en', 'dark', 'active', NOW()),
  (3, 1, 'teacher', 'David', 'Alemu',  'teacher@edunex.local','+251911000002', NULL, '$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u', NULL, 1, 1, NULL, NOW(), 2500, 6, 5, 'en', 'dark', 'active', NOW()),
  (4, 1, 'student', 'Liya', 'Girma',   'student@edunex.local','+251911000003', 'AAIS-2026-000001', '$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u', NULL, 1, 1, 3, NOW(), 1200, 4, 3, 'en', 'dark', 'active', NOW()),
  (5, 1, 'parent', 'Hana', 'Girma',    'parent@edunex.local', '+251911000004', NULL, '$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u', NULL, NULL, 1, NULL, NOW(), 100, 1, 0, 'en', 'dark', 'active', NOW()),
  (6, 2, 'admin', 'Kebede', 'Hailu',   'admin2@edunex.local', '+251911000005', NULL, '$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u', NULL, 3, 1, NULL, NOW(), 300, 2, 1, 'am', 'light', 'active', NOW()),
  (7, 3, 'teacher', 'Meron', 'Tesfa',  'teacher3@edunex.local','+251911000006', NULL, '$2y$12$OVIoqjH/hqK2zN.z663sLOkTdCMgHWLgFTfBVImfZuuKlByi0f75u', NULL, 5, 1, NULL, NOW(), 800, 3, 2, 'en', 'light', 'active', NOW());

INSERT INTO student_groups (school_id, name, grade, section, homeroom_teacher_id) VALUES
  (1, 'Grade 9-A', '9', 'A', 3), (1, 'Grade 10-B', '10', 'B', 3),
  (2, 'CS Year 1', '1st', 'A', NULL),
  (3, 'Grade 8-C', '8', 'C', NULL);

UPDATE users SET group_id = 1, parent_id = 5 WHERE id = 4;

-- Courses
INSERT INTO courses (school_id, teacher_id, subject_id, title, code, description, status, level) VALUES
  (1, 3, 1, 'Mathematics 101', 'MATH101', 'Foundations of algebra, geometry and calculus for Grade 9 students.', 'published', 'Grade 9'),
  (1, 3, 2, 'Physics Foundations', 'PHY101', 'Mechanics, motion and energy explained with real Ethiopian examples.', 'published', 'Grade 9'),
  (3, 7, 4, 'Data Structures', 'CS201', 'Arrays, linked lists, stacks, queues, trees and graphs with C.', 'published', '1st Year');

INSERT INTO course_modules (course_id, title, sort_order) VALUES
  (1, 'Chapter 1: Algebra', 1), (1, 'Chapter 2: Geometry', 2),
  (2, 'Chapter 1: Motion', 1),
  (3, 'Module 1: Basics', 1), (3, 'Module 2: Trees', 2);

INSERT INTO lessons (module_id, course_id, title, type, content, duration_min, sort_order) VALUES
  (1, 1, 'Introduction to Algebra', 'notes', '<p>Algebra is the study of mathematical symbols and the rules for manipulating them. It is a unifying thread of almost all of mathematics.</p><p><b>Key concepts:</b> variables, expressions, equations, polynomials.</p><p>A linear equation has the form ax + b = 0. To solve, isolate x: x = -b/a.</p><p><b>Example:</b> 2x + 4 = 10 &rarr; 2x = 6 &rarr; x = 3.</p><p>Recursion in programming is when a function calls itself. In algebra, think of it as defining a value in terms of previous values, like the Fibonacci sequence: F(n) = F(n-1) + F(n-2).</p>', 25, 1),
  (1, 1, 'Linear Equations', 'video', '<p>Watch the video and practice solving linear equations step by step.</p>', 18, 2),
  (2, 1, 'Triangles and Angles', 'notes', '<p>Triangles are three-sided polygons. The sum of interior angles is always 180 degrees.</p><p>Types: equilateral, isosceles, scalene, right-angled.</p>', 20, 1),
  (3, 2, 'Distance and Velocity', 'video', '<p>Velocity is displacement over time. Distance = speed &times; time.</p>', 15, 1),
  (4, 3, 'Arrays in C', 'notes', '<p>An array is a contiguous block of memory holding elements of the same type. Indexing starts at 0 in C.</p>', 30, 1),
  (5, 3, 'Binary Trees', 'notes', '<p>A binary tree is a hierarchical structure where each node has at most two children. Trees are recursive data structures.</p>', 35, 1);

INSERT INTO course_enrollments (course_id, user_id, progress, completed) VALUES
  (1, 4, 50, 0), (2, 4, 0, 0), (3, 4, 25, 0);

-- Assignments
INSERT INTO assignments (course_id, teacher_id, title, description, rubric, max_score, due_date, allow_late, late_penalty) VALUES
  (1, 3, 'Algebra Worksheet 1', 'Solve the 20 problems in the attached sheet. Show all your steps.',
   '[{"criterion":"Correctness","max":60,"weight":60},{"criterion":"Steps shown","max":25,"weight":25},{"criterion":"Presentation","max":15,"weight":15}]',
   100, DATE_ADD(NOW(), INTERVAL 5 DAY), 1, 10),
  (2, 3, 'Motion Lab Report', 'Write a lab report about free-fall motion using the provided template.', NULL, 50, DATE_ADD(NOW(), INTERVAL 3 DAY), 1, 5);

INSERT INTO assignment_submissions (assignment_id, student_id, content, submitted_at, is_late, score, feedback, status, graded_by, graded_at) VALUES
  (1, 4, 'I solved problems 1-20. Please see the attached PDF.', '2026-07-28 10:00:00', 0, 88, 'Excellent work! Careful with problem 14.', 'graded', 3, '2026-07-29 09:00:00');

-- Exams
INSERT INTO exams (course_id, teacher_id, title, description, type, duration_min, start_time, end_time, passing_score, auto_grade, status) VALUES
  (1, 3, 'Algebra Midterm', 'Covers chapters 1-2: algebra basics and linear equations.', 'midterm', 30, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 50, 1, 'published'),
  (3, 7, 'Data Structures Quiz 1', 'Quick quiz on arrays and pointers.', 'quiz', 15, NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), 50, 1, 'published'),
  (1, 3, 'Practice: True/False', 'Practice session for exam readiness.', 'practice', 10, NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY), 50, 1, 'published');

INSERT INTO exam_questions (exam_id, type, question, options, correct_answer, points, explanation, sort_order) VALUES
  (1, 'mcq', 'Solve: 2x + 4 = 10. What is x?', '["2","3","4","5"]', '3', 2, '2x = 6, x = 3', 1),
  (1, 'mcq', 'What is the slope of y = 3x + 2?', '["1","2","3","6"]', '3', 1, 'Slope is the coefficient of x', 2),
  (1, 'truefalse', 'The sum of interior angles of a triangle is 180 degrees.', NULL, 'true', 1, 'Always true for Euclidean triangles', 3),
  (1, 'fill', 'The formula for the area of a rectangle is A = _ x width.', NULL, 'length', 1, 'A = l x w', 4),
  (1, 'essay', 'Explain the difference between a linear and a quadratic equation with one example each.', NULL, NULL, 5, 'Linear: degree 1; quadratic: degree 2', 5),
  (1, 'mcq', 'If F(0)=0, F(1)=1, F(n)=F(n-1)+F(n-2), what is F(4)?', '["1","2","3","5"]', '3', 2, 'Fibonacci: 0,1,1,2,3,5 -> F(4)=3', 6),
  (2, 'mcq', 'In C, what is the index of the first element of an array?', '["0","1","-1","None"]', '0', 1, 'C arrays are zero-indexed', 1),
  (2, 'truefalse', 'An array stores elements of different types.', NULL, 'false', 1, 'Arrays hold one type', 2),
  (3, 'truefalse', 'A triangle can have two right angles.', NULL, 'false', 1, 'Sum would exceed 180', 1),
  (3, 'mcq', 'Recursion is when a function...', '["Loops forever","Calls itself","Returns a string","Uses no memory"]', 'Calls itself', 2, 'Recursion = self-calling function', 2);

INSERT INTO exam_attempts (exam_id, student_id, started_at, submitted_at, score, total_points, status) VALUES
  (1, 4, '2026-07-20 09:00:00', '2026-07-20 09:20:00', 7, 12, 'graded');

INSERT INTO exam_answers (attempt_id, question_id, answer, is_correct, points_earned) VALUES
  (1, 1, '3', 1, 2), (1, 2, '3', 1, 1), (1, 3, 'true', 1, 1), (1, 4, 'length', 1, 1),
  (1, 5, 'Linear has degree 1, quadratic has degree 2.', NULL, 2);

-- Attendance (last 10 days)
INSERT INTO attendance (school_id, course_id, student_id, date, status, recorded_by) VALUES
  (1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 9 DAY), 'present', 3),
  (1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'present', 3),
  (1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'late', 3),
  (1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'present', 3),
  (1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'absent', 3),
  (1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'present', 3),
  (1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'present', 3),
  (1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'excused', 3),
  (1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'present', 3),
  (1, 1, 4, CURDATE(), 'present', 3);

-- Library
INSERT INTO library_items (school_id, title, type, author, category, description, downloads) VALUES
  (1, 'Algebra Workbook 2026', 'book', 'MoE Ethiopia', 'Mathematics', 'Official workbook for Grade 9 algebra.', 120),
  (1, 'Physics Notes Chapter 1', 'notes', 'Mr. David Alemu', 'Physics', 'Handwritten-style notes on motion.', 45),
  (1, '2019 National Exam Math', 'past_exam', 'MoE', 'Exams', 'Past national exam paper for practice.', 230),
  (1, 'Introduction to C Programming', 'book', 'K&R', 'Programming', 'Classic C programming reference.', 89),
  (1, 'Data Structures Slides', 'slides', 'Prof. Kebede', 'Computer Science', 'Lecture slides on trees and graphs.', 60),
  (1, 'Amharic-English Dictionary', 'book', 'Academy', 'Languages', 'Bilingual dictionary for students.', 150),
  (1, 'Ethiopian History Timeline', 'notes', 'Mrs. Tigist', 'History', 'Quick timeline of key events.', 34),
  (1, 'Science Fair Video', 'video', 'BDU Media', 'Science', 'Documentary on Ethiopian scientists.', 76);

-- Announcements
INSERT INTO announcements (school_id, course_id, author_id, title, content, pinned, audience) VALUES
  (1, NULL, 2, 'Welcome to the new academic year!', 'We are excited to welcome all students back. The digital library now has 200+ resources.', 1, 'all'),
  (1, 1, 3, 'Midterm exam schedule published', 'The Algebra midterm will take place next week. Review chapters 1-2.', 1, 'course'),
  (1, NULL, 2, 'Library maintenance on Sunday', 'The library will be briefly offline Sunday 02:00-04:00.', 0, 'all');

-- Forum
INSERT INTO forum_topics (course_id, author_id, title, body) VALUES
  (1, 4, 'How do I solve quadratic equations?', 'I keep getting confused with factoring. Any tips?'),
  (1, 3, 'Office hours this week', 'I will be available Wednesday 14:00-16:00.');

INSERT INTO forum_posts (topic_id, author_id, body, is_answer) VALUES
  (1, 3, 'Try the AC method: multiply a and c, find factors that sum to b.', 1),
  (1, 4, 'That helped a lot, thank you!', 0);

-- Notifications
INSERT INTO notifications (user_id, type, title, body, link, read_at) VALUES
  (4, 'assignment', 'Algebra Worksheet 1 graded', 'You received 88/100.', 'assignments/view?sub=1', NULL),
  (4, 'exam', 'Algebra Midterm is coming up', 'The midterm starts in 7 days.', 'exams/view?e=1', NULL),
  (4, 'announcement', 'Welcome to Edunex', 'Explore your courses and AI tutor!', 'dashboard', NULL);

-- Calendar
INSERT INTO calendar_events (school_id, user_id, title, type, start_at, end_at, all_day) VALUES
  (1, 4, 'Algebra Midterm', 'exam', DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY), 0),
  (1, 4, 'Physics Lab Report due', 'assignment', DATE_ADD(NOW(), INTERVAL 3 DAY), NULL, 0),
  (1, 4, 'Algebra Worksheet due', 'assignment', DATE_ADD(NOW(), INTERVAL 5 DAY), NULL, 0),
  (1, NULL, 'School Sports Day', 'event', DATE_ADD(NOW(), INTERVAL 12 DAY), DATE_ADD(NOW(), INTERVAL 12 DAY), 1),
  (1, 4, 'Study group: Data Structures', 'meeting', DATE_ADD(NOW(), INTERVAL 2 DAY), NULL, 0);

-- Badges
INSERT INTO badges (name, icon, description, xp_required, category) VALUES
  ('First Steps', 'leaf', 'Complete your first lesson', 50, 'learning'),
  ('Bookworm', 'books', 'Read 5 lessons', 200, 'learning'),
  ('Quiz Whiz', 'brain', 'Score 80%+ on any quiz', 300, 'quiz'),
  ('Perfect Attendance', 'target', '7 days of perfect attendance', 350, 'attendance'),
  ('On Fire', 'flame', '7-day learning streak', 400, 'streak'),
  ('Scholar', 'graduation', 'Complete a full course', 600, 'level'),
  ('Helping Hand', 'handshake', 'Answer 5 forum questions', 250, 'community'),
  ('Marathoner', 'run', 'Reach level 5', 500, 'level');

INSERT INTO user_badges (user_id, badge_id) VALUES (4, 1), (4, 2);

-- Challenges
INSERT INTO challenges (school_id, title, description, reward_xp, starts_at, ends_at) VALUES
  (1, 'Study 5 lessons this week', 'Complete 5 lessons in any course.', 100, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY)),
  (1, 'Perfect quiz score', 'Get 100% on any practice quiz.', 150, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY));

-- AI question bank
INSERT INTO ai_question_bank (school_id, topic, keywords, type, question, options, answer, explanation) VALUES
  (1, 'Algebra', 'linear equation solve', 'mcq', 'Solve 3x - 9 = 0', '["-3","3","9","27"]', '3', '3x = 9 so x = 3'),
  (1, 'Algebra', 'quadratic', 'mcq', 'The roots of x^2 - 5x + 6 = 0 are:', '["2,3","1,6","-2,-3","5,1"]', '2,3', 'Factors (x-2)(x-3)'),
  (1, 'Physics', 'velocity speed', 'mcq', 'A car travels 120 km in 2 hours. Its average speed is:', '["40 km/h","60 km/h","120 km/h","240 km/h"]', '60 km/h', 'Speed = distance / time'),
  (1, 'Data Structures', 'array index', 'truefalse', 'In C, array indexing starts at 1.', NULL, 'false', 'C arrays start at index 0'),
  (1, 'Data Structures', 'recursion', 'mcq', 'What is the base case in recursion?', '["The recursive call","The stopping condition","A syntax error","None"]', 'The stopping condition', 'Base case stops recursion'),
  (1, 'General', 'triangle angles', 'mcq', 'Sum of interior angles of a quadrilateral:', '["180","270","360","540"]', '360', 'Two triangles = 360'),
  (1, 'English', 'grammar past tense', 'fill', 'She ___ (go) to school yesterday.', NULL, 'went', 'Past tense of go is went'),
  (1, 'Algebra', 'exponent', 'mcq', '2^5 equals:', '["16","25","32","64"]', '32', '2x2x2x2x2 = 32');

-- Transfer codes (for school-to-school referral)
INSERT INTO transfer_codes (code, school_id, purpose, used, expires_at) VALUES
  ('TRF-AAIS-0001', 1, 'referral', 0, DATE_ADD(NOW(), INTERVAL 90 DAY)),
  ('TRF-BDU-0001', 2, 'referral', 0, DATE_ADD(NOW(), INTERVAL 90 DAY)),
  ('TRF-HPS-0001', 3, 'referral', 0, DATE_ADD(NOW(), INTERVAL 90 DAY));

-- Files (seed a folder)
INSERT INTO files (school_id, user_id, name, original_name, path, mime, size, is_folder) VALUES
  (1, 2, 'My Documents', 'My Documents', '', 'folder', 0, 1);

-- Messages
INSERT INTO conversations (school_id, is_group, title) VALUES
  (1, 0, 'Liya & David'), (1, 1, 'Math 101 Class Group');

INSERT INTO conversation_members (conversation_id, user_id) VALUES (1, 3), (1, 4), (2, 3), (2, 4), (2, 2);

INSERT INTO messages (conversation_id, sender_id, body) VALUES
  (1, 3, 'Great question in the forum today!'),
  (1, 4, 'Thank you teacher!'),
  (2, 3, 'Reminder: midterm next week.'),
  (2, 2, 'Don''t forget to register for the science fair.');

-- Goals
INSERT INTO goals (user_id, title, target, current, unit, due_date) VALUES
  (4, 'Finish Mathematics 101', 100, 50, 'lessons', DATE_ADD(CURDATE(), INTERVAL 30 DAY));

CREATE TABLE student_notes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  lesson_id INT UNSIGNED DEFAULT NULL,
  course_id INT UNSIGNED DEFAULT NULL,
  title VARCHAR(180) NOT NULL DEFAULT '',
  body LONGTEXT,
  pinned TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_course (course_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
  FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 14. UNIVERSITY / HIGHER EDUCATION
-- ------------------------------------------------------------

CREATE TABLE programs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  faculty_id INT UNSIGNED DEFAULT NULL,
  department_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(200) NOT NULL,
  code VARCHAR(20) NOT NULL,
  degree_type ENUM('bachelor','master','phd','diploma','certificate') DEFAULT 'bachelor',
  total_credits INT UNSIGNED DEFAULT 120,
  duration_years INT UNSIGNED DEFAULT 4,
  status ENUM('active','inactive','archived') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_prog_code (school_id, code),
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE SET NULL,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE student_programs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  program_id INT UNSIGNED NOT NULL,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expected_graduation DATE DEFAULT NULL,
  actual_graduation DATE DEFAULT NULL,
  status ENUM('active','graduated','transferred','withdrawn','suspended') DEFAULT 'active',
  UNIQUE KEY uq_stu_prog (student_id, program_id),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE course_offerings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  lecturer_id INT UNSIGNED DEFAULT NULL,
  max_students INT UNSIGNED DEFAULT 40,
  current_students INT UNSIGNED DEFAULT 0,
  room VARCHAR(60) DEFAULT '',
  schedule_json JSON DEFAULT NULL,
  status ENUM('open','full','closed','cancelled') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE,
  FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE registrations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  course_offering_id INT UNSIGNED NOT NULL,
  status ENUM('registered','dropped','completed') DEFAULT 'registered',
  grade VARCHAR(2) DEFAULT NULL,
  grade_points DECIMAL(3,2) DEFAULT NULL,
  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  dropped_at TIMESTAMP NULL,
  UNIQUE KEY uq_reg (student_id, course_offering_id),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_offering_id) REFERENCES course_offerings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE prerequisites (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_id INT UNSIGNED NOT NULL,
  required_course_id INT UNSIGNED NOT NULL,
  min_grade VARCHAR(2) DEFAULT 'D',
  FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
  FOREIGN KEY (required_course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE academic_records (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  course_offering_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  credit_hours INT UNSIGNED NOT NULL DEFAULT 3,
  grade VARCHAR(2) NOT NULL,
  grade_points DECIMAL(3,2) NOT NULL,
  quality_points DECIMAL(7,2) GENERATED ALWAYS AS (credit_hours * grade_points) STORED,
  recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_acad_rec (student_id, course_offering_id),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (course_offering_id) REFERENCES course_offerings(id) ON DELETE CASCADE,
  FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE rooms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(60) NOT NULL,
  building VARCHAR(100) DEFAULT '',
  capacity INT UNSIGNED DEFAULT 40,
  room_type ENUM('lecture_hall','lab','tutorial_room','seminar','office') DEFAULT 'lecture_hall',
  equipment JSON DEFAULT NULL,
  status ENUM('available','maintenance','unavailable') DEFAULT 'available',
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE schedules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  course_offering_id INT UNSIGNED NOT NULL,
  day ENUM('monday','tuesday','wednesday','thursday','friday','saturday') NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  room_id INT UNSIGNED DEFAULT NULL,
  schedule_type ENUM('lecture','lab','tutorial') DEFAULT 'lecture',
  FOREIGN KEY (course_offering_id) REFERENCES course_offerings(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE fee_structures (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  school_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  fee_type ENUM('per_credit','fixed','per_course') NOT NULL DEFAULT 'fixed',
  applies_to VARCHAR(80) DEFAULT 'all',
  semester_id INT UNSIGNED DEFAULT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
  FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE invoices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  semester_id INT UNSIGNED NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending','partial','paid','overdue') DEFAULT 'pending',
  due_date DATE DEFAULT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE invoice_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT UNSIGNED NOT NULL,
  fee_structure_id INT UNSIGNED DEFAULT NULL,
  description VARCHAR(200) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (fee_structure_id) REFERENCES fee_structures(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('cash','bank_transfer','mobile','online') DEFAULT 'cash',
  reference_number VARCHAR(100) DEFAULT '',
  notes TEXT,
  paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  recorded_by INT UNSIGNED DEFAULT NULL,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE clearance_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  type ENUM('graduation','transfer','withdrawal') NOT NULL DEFAULT 'graduation',
  status ENUM('pending','in_progress','cleared','rejected') DEFAULT 'pending',
  tracking_code VARCHAR(30) NOT NULL,
  notes TEXT,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL,
  UNIQUE KEY uq_clr_code (tracking_code),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE clearance_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id INT UNSIGNED NOT NULL,
  department VARCHAR(50) NOT NULL,
  checker_id INT UNSIGNED DEFAULT NULL,
  status ENUM('pending','passed','failed','not_applicable') DEFAULT 'pending',
  notes TEXT,
  checked_at TIMESTAMP NULL,
  signature_hash VARCHAR(64) DEFAULT '',
  FOREIGN KEY (request_id) REFERENCES clearance_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (checker_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE transcript_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  type ENUM('official','unofficial') NOT NULL DEFAULT 'unofficial',
  status ENUM('pending','processing','ready','delivered') DEFAULT 'pending',
  file_path VARCHAR(255) DEFAULT '',
  hash VARCHAR(64) DEFAULT '',
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at TIMESTAMP NULL,
  processed_by INT UNSIGNED DEFAULT NULL,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE theses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  program_id INT UNSIGNED NOT NULL,
  title VARCHAR(500) NOT NULL DEFAULT '',
  abstract TEXT,
  advisor_id INT UNSIGNED DEFAULT NULL,
  status ENUM('proposal','in_progress','defense','revision','completed','archived') DEFAULT 'proposal',
  topic_approved_at TIMESTAMP NULL,
  defense_date DATE DEFAULT NULL,
  defense_result ENUM('pass','fail','revise') DEFAULT NULL,
  defense_notes TEXT,
  final_submitted_at TIMESTAMP NULL,
  archived_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
  FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE thesis_chapters (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  thesis_id INT UNSIGNED NOT NULL,
  chapter_number INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL DEFAULT '',
  file_path VARCHAR(255) DEFAULT '',
  status ENUM('draft','submitted','reviewed','approved') DEFAULT 'draft',
  submitted_at TIMESTAMP NULL,
  feedback TEXT,
  feedback_at TIMESTAMP NULL,
  advisor_id INT UNSIGNED DEFAULT NULL,
  FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE,
  FOREIGN KEY (advisor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE thesis_committee (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  thesis_id INT UNSIGNED NOT NULL,
  member_id INT UNSIGNED NOT NULL,
  role ENUM('advisor','co_advisor','examiner') NOT NULL DEFAULT 'examiner',
  approved_at TIMESTAMP NULL,
  FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE,
  FOREIGN KEY (member_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student_cards (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  card_number VARCHAR(20) NOT NULL,
  barcode_data VARCHAR(60) DEFAULT '',
  qr_data VARCHAR(200) DEFAULT '',
  issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at DATE DEFAULT NULL,
  status ENUM('active','expired','revoked') DEFAULT 'active',
  photo_path VARCHAR(255) DEFAULT '',
  UNIQUE KEY uq_card_num (card_number),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE academic_events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  semester_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  event_date DATE NOT NULL,
  event_type ENUM('holiday','ceremony','deadline','exam','registration') NOT NULL DEFAULT 'deadline',
  description TEXT,
  FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Ethiopian education hierarchy
CREATE TABLE IF NOT EXISTS zones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  region_admin_id INT UNSIGNED NULL,
  admin_id INT UNSIGNED NULL,
  status VARCHAR(20) DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS woredas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  zone_id INT UNSIGNED NOT NULL,
  admin_id INT UNSIGNED NULL,
  status VARCHAR(20) DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
