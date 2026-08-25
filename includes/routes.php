<?php
/**
 * EDUNEX Route registry
 * Router::page(path, controllerFile, roles) — roles: '*', 'guest', or array
 * Router::view(path, template)
 */

/* ---------------- Landing ---------------- */
Router::page('', 'landing/home.php', '*');
Router::page('landing', 'landing/home.php', '*');
Router::view('landing/home', 'landing/home');
Router::view('landing/features', 'landing/features');
Router::view('landing/ai', 'landing/ai');
Router::view('landing/pricing', 'landing/pricing');
Router::view('landing/faq', 'landing/faq');
Router::view('landing/contact', 'landing/contact');

/* ---------------- Auth ---------------- */
Router::page('auth/login', 'auth/login.php', '*');
Router::page('auth/register', 'auth/register.php', '*');
Router::page('auth/forgot', 'auth/forgot.php', '*');
Router::page('auth/reset', 'auth/reset.php', '*');
Router::page('auth/verify', 'auth/verify.php', '*');
Router::page('auth/otp', 'auth/otp.php', '*');
Router::page('auth/2fa', 'auth/twofa.php', '*');
Router::page('auth/hena_ready', 'auth/hena_ready.php', '*');
Router::page('auth/logout', 'auth/logout.php', '*');
Router::page('auth/sessions', 'auth/sessions.php', '*');

/* ---------------- App ---------------- */
Router::page('dashboard', 'dashboard.php', ['regional', 'teacher', 'student', 'parent', 'principal', 'zonal', 'woreda', 'ministry', 'bursar', 'student_affairs', 'librarian']);
Router::page('user/profile', 'user/profile.php', '*');

/* ---- Student ---- */
Router::page('student/dashboard', 'student/dashboard.php', 'student');
Router::page('student/courses', 'student/courses.php', 'student');
Router::page('student/assignments', 'student/assignments.php', 'student');
Router::page('student/exams', 'student/exams.php', 'student');
Router::page('student/grades', 'student/grades.php', 'student');
Router::page('student/grades/subject', 'student/grades_subject.php', 'student');
Router::page('student/attendance', 'student/attendance.php', 'student');
Router::page('student/schedule', 'student/schedule.php', 'student');
Router::page('student/leaderboard', 'student/leaderboard.php', 'student');
Router::page('student/theses', 'student/theses.php', 'student');

/* ---- Teacher ---- */
Router::page('teacher/dashboard', 'teacher/dashboard.php', ['teacher', 'lecturer']);
Router::page('teacher/courses', 'teacher/courses.php', ['teacher', 'lecturer']);
Router::page('teacher/course', 'teacher/course.php', ['teacher', 'lecturer']);
Router::page('teacher/lesson', 'teacher/lesson.php', ['teacher', 'lecturer']);
Router::page('teacher/exams', 'teacher/exams.php', ['teacher', 'lecturer']);
Router::page('teacher/exam', 'teacher/exam.php', ['teacher', 'lecturer']);
Router::page('teacher/assignments', 'teacher/assignments.php', ['teacher', 'lecturer']);
Router::page('teacher/assignment', 'teacher/assignment.php', ['teacher', 'lecturer']);
Router::page('assignments/review/post', 'assignments/review_post.php', ['student', 'teacher', 'lecturer']);
Router::page('assignments/review/list', 'assignments/review_list.php', ['student', 'teacher', 'lecturer']);
Router::page('teacher/grade', 'teacher/grade.php', ['teacher', 'lecturer'], 'grades.manage');
Router::page('teacher/attendance', 'teacher/attendance.php', ['teacher', 'lecturer'], 'attendance.record');
Router::page('teacher/students', 'teacher/students.php', ['teacher', 'lecturer'], 'attendance.view');
Router::page('teacher/homeroom', 'teacher/homeroom.php', ['teacher', 'lecturer']);
Router::page('teacher/verify', 'teacher/verify.php', ['teacher', 'lecturer'], 'attendance.record');
Router::page('teacher/import', 'teacher/import.php', ['teacher', 'lecturer']);
Router::page('teacher/book', 'teacher/book.php', ['teacher', 'lecturer']);
Router::page('teacher/book/job', 'teacher/book_job.php', 'teacher');
Router::page('director/dashboard', 'director/dashboard.php', 'principal');
Router::page('director/teachers', 'director/teachers.php', 'principal');
Router::page('director/students', 'director/students.php', 'principal');
Router::page('director/import', 'director/import.php', 'principal');
Router::page('director/transfers', 'director/transfers.php', 'principal');
Router::page('director/faculties', 'director/faculties.php', 'principal');
Router::page('director/reports', 'director/reports.php', 'principal');
Router::page('director/analytics', 'director/analytics.php', 'principal');

Router::page('teacher/reports', 'teacher/reports.php', ['teacher', 'lecturer']);
Router::page('teacher/analytics', 'teacher/analytics.php', ['teacher', 'lecturer']);
Router::page('teacher/library', 'teacher/library.php', ['teacher', 'lecturer']);
Router::page('teacher/forum', 'teacher/forum.php', ['teacher', 'lecturer']);

/* ---- Admin ---- */
Router::page('admin/dashboard', 'admin/dashboard.php', 'ministry');
Router::page('admin/import', 'admin/import.php', 'ministry');
Router::page('admin/users', 'admin/users.php', 'ministry');
Router::page('admin/user', 'admin/user.php', 'ministry');
Router::page('admin/schools', 'admin/schools.php', 'ministry');
Router::page('admin/school', 'admin/school.php', 'ministry');
Router::page('admin/departments', 'admin/departments.php', 'ministry');
Router::page('admin/department', 'admin/department.php', 'ministry');
Router::page('admin/subjects', 'admin/subjects.php', 'ministry');
Router::page('admin/groups', 'admin/groups.php', 'ministry');
Router::page('admin/years', 'admin/years.php', 'ministry');
Router::page('admin/courses', 'admin/courses.php', 'ministry');
Router::page('admin/roles', 'admin/roles.php', 'ministry');
Router::page('admin/settings', 'admin/settings.php', 'ministry');
Router::page('admin/logs', 'admin/logs.php', ['ministry', 'regional']);
Router::page('admin/analytics', 'admin/analytics.php', 'ministry');
Router::page('admin/reports', 'admin/reports.php', 'ministry');
Router::page('admin/backups', 'admin/backups.php', ['ministry', 'regional']);
Router::page('admin/announcements', 'admin/announcements.php', 'ministry');
Router::page('admin/library', 'admin/library.php', 'ministry');
Router::page('admin/permissions', 'admin/permissions.php', 'ministry');
Router::page('admin/transfers', 'admin/transfers.php', 'ministry');
Router::page('admin/transfer', 'admin/transfer.php', 'ministry');
Router::page('admin/system', 'admin/system.php', 'ministry');
Router::page('admin/ledger', 'admin/ledger.php', 'ministry');
Router::page('admin/security', 'admin/security.php', 'ministry');
Router::page('admin/badges', 'admin/badges.php', 'ministry');
Router::page('admin/modules', 'admin/modules.php', 'ministry');
Router::page('admin/school-modules', 'admin/school_modules.php', 'ministry');
Router::page('admin/ai-reports', 'admin/ai_reports.php', 'ministry');
Router::page('admin/override', 'admin/override.php', 'ministry');
Router::page('admin/override-exit', 'admin/override.php', '*');
Router::page('admin/finance', 'admin/finance.php', 'ministry');
Router::page('admin/licenses', 'admin/licenses.php', 'ministry');

/* ---- Regional Admin ---- */
Router::page('regional/dashboard', 'regional/regional.php', 'regional');
Router::page('regional/schools', 'regional/regional.php', 'regional');
Router::page('regional/school', 'regional/regional.php', 'regional');
Router::page('regional/directors', 'regional/regional.php', 'regional');
Router::page('regional/director', 'regional/regional.php', 'regional');
Router::page('regional/analytics', 'regional/regional.php', 'regional');
Router::page('regional/announcements', 'regional/regional.php', 'regional');
Router::page('regional/backups', 'regional/regional.php', 'regional');
Router::page('regional/audit', 'regional/regional.php', 'regional');

/* ---- Zonal Admin ---- */
Router::page('zonal/dashboard', 'zonal/zonal.php', 'zonal');
Router::page('zonal/woredas', 'zonal/zonal.php', 'zonal');
Router::page('zonal/woreda', 'zonal/zonal.php', 'zonal');
Router::page('zonal/schools', 'zonal/zonal.php', 'zonal');
Router::page('zonal/school', 'zonal/zonal.php', 'zonal');
Router::page('zonal/directors', 'zonal/zonal.php', 'zonal');
Router::page('zonal/director', 'zonal/zonal.php', 'zonal');
Router::page('zonal/analytics', 'zonal/zonal.php', 'zonal');
Router::page('zonal/announcements', 'zonal/zonal.php', 'zonal');
Router::page('zonal/audit', 'zonal/zonal.php', 'zonal');

/* ---- Woreda Admin ---- */
Router::page('woreda/dashboard', 'woreda/woreda.php', 'woreda');
Router::page('woreda/schools', 'woreda/woreda.php', 'woreda');
Router::page('woreda/school', 'woreda/woreda.php', 'woreda');
Router::page('woreda/directors', 'woreda/woreda.php', 'woreda');
Router::page('woreda/director', 'woreda/woreda.php', 'woreda');
Router::page('woreda/analytics', 'woreda/woreda.php', 'woreda');
Router::page('woreda/announcements', 'woreda/woreda.php', 'woreda');
Router::page('woreda/audit', 'woreda/woreda.php', 'woreda');

/* ---- Registrar ---- */
Router::page('registrar/dashboard', 'registrar/registrar.php', 'registrar');
Router::page('registrar/enrollments', 'registrar/registrar.php', 'registrar');
Router::page('registrar/transcripts', 'registrar/registrar.php', 'registrar');
Router::page('registrar/announcements', 'registrar/registrar.php', 'registrar');
Router::page('registrar/audit', 'registrar/registrar.php', 'registrar');
Router::page('registrar/semesters', 'registrar/registrar.php', 'registrar');
Router::page('registrar/admissions', 'registrar/registrar.php', 'registrar');
Router::page('registrar/graduation', 'registrar/registrar.php', 'registrar');
Router::page('registrar/scholarships', 'registrar/registrar.php', 'registrar');

/* ---- Dean ---- */
Router::page('dean/dashboard', 'dean/dean.php', 'dean');
Router::page('dean/departments', 'dean/dean.php', 'dean');
Router::page('dean/courses', 'dean/dean.php', 'dean');
Router::page('dean/teachers', 'dean/dean.php', 'dean');
Router::page('dean/analytics', 'dean/dean.php', 'dean');

/* ---- Vice Dean ---- */
Router::page('vice_dean/dashboard', 'dean/departments_head.php', 'vice_dean');
Router::page('vice_dean/courses', 'dean/departments_head.php', 'vice_dean');
Router::page('vice_dean/analytics', 'dean/departments_head.php', 'vice_dean');

/* ---- Department Head ---- */
Router::page('dept_head/dashboard', 'dean/departments_head.php', 'hod');
Router::page('dept_head/courses', 'dean/departments_head.php', 'hod');
Router::page('dept_head/theses', 'dean/departments_head.php', 'hod');
Router::page('dept_head/analytics', 'dean/departments_head.php', 'hod');

/* ---- University Module ---- */
Router::page('university/programs', 'university/university.php', ['registrar', 'dean', 'vice_dean', 'hod']);
Router::page('university/program', 'university/university.php', ['registrar', 'dean', 'vice_dean', 'hod']);
Router::page('university/semesters', 'university/university.php', ['registrar']);
Router::page('university/registration', 'university/university.php', ['registrar', 'student']);
Router::page('university/my-schedule', 'university/university.php', ['student']);
Router::page('university/clearance', 'university/university.php', ['student']);
Router::page('university/clearance/manage', 'university/university.php', ['registrar', 'dean', 'hod', 'bursar', 'librarian', 'student_affairs']);
Router::page('university/transcript', 'university/university.php', ['student']);
Router::page('university/transcript/manage', 'university/university.php', ['registrar']);
Router::page('university/fees', 'university/university.php', ['student']);
Router::page('university/fees/manage', 'university/university.php', ['bursar', 'registrar']);
Router::page('university/theses', 'university/university.php', ['registrar', 'dean', 'vice_dean', 'hod', 'student']);
Router::page('university/thesis', 'university/university.php', ['registrar', 'dean', 'vice_dean', 'hod', 'student']);
Router::page('university/timetable', 'university/university.php', ['registrar', 'dean', 'student']);
Router::page('university/id-cards', 'university/university.php', ['registrar', 'student_affairs']);

/* ---- Parent ---- */
Router::page('parent/dashboard', 'parent/dashboard.php', 'parent');
Router::page('parent/children', 'parent/children.php', 'parent');
Router::page('parent/reports', 'parent/reports.php', 'parent');

/* ---- Cross-role modules ---- */
Router::page('courses', 'courses/browse.php', '*');
Router::page('courses/view', 'courses/view.php', '*');
Router::page('courses/learn', 'courses/learn.php', ['student', 'teacher', 'parent', 'regional', 'principal']);
Router::page('courses/discuss', 'courses/discuss.php', '*');
Router::page('courses/certificate', 'courses/certificate.php', ['student', 'regional', 'teacher']);

Router::page('exams/take', 'exams/take.php', ['student', 'teacher']);
Router::page('exams/result', 'exams/result.php', ['student', 'teacher', 'regional']);

Router::page('assignments/view', 'assignments/view.php', ['student', 'teacher', 'parent', 'regional']);

Router::page('ai/tutor', 'ai/tutor.php', ['student', 'teacher', 'principal']);
Router::page('ai/tutor/stream', 'ai/tutor_stream.php', ['student', 'teacher', 'principal']);
Router::page('ai/assistant', 'ai/assistant.php', ['student', 'teacher', 'principal']);
Router::page('ai/assistant/stream', 'ai/assistant_stream.php', ['student', 'teacher', 'principal']);
Router::page('ai/job/progress', 'ai/job_progress.php', ['student', 'teacher', 'principal']);
Router::page('ai/job/cancel', 'ai/job_cancel.php', ['student', 'teacher', 'principal']);
Router::page('ai/history', 'ai/history.php', ['student', 'teacher', 'principal']);
Router::page('ai/flashcards', 'ai/flashcards.php', ['student', 'teacher', 'principal']);
Router::page('ai/flashcard-image', 'ai/flashcard_image.php', ['student', 'teacher', 'principal']);
Router::page('ai/quiz', 'ai/quiz.php', ['student', 'teacher', 'principal']);

Router::page('library', 'library/index.php', '*');
Router::page('library/item', 'library/item.php', '*');

Router::page('messages', 'communication/messages.php', '*');
Router::page('communication/messages', 'communication/messages.php', '*');
Router::page('communication/groups', 'communication/groups.php', '*');
Router::page('communication/announcements', 'communication/announcements.php', '*');
Router::page('communication/announcement', 'communication/announcement.php', '*');

Router::page('notifications', 'notifications/index.php', '*');
Router::page('calendar', 'calendar/index.php', '*');

Router::page('analytics/student', 'analytics/student.php', ['student', 'parent']);
Router::page('analytics/teacher', 'analytics/teacher.php', 'teacher');
Router::page('analytics/admin', 'analytics/admin.php', 'regional');

Router::page('certificates', 'certificates/index.php', ['student', 'parent']);
Router::page('certificates/view', 'certificates/view.php', '*');
Router::page('certificates/verify', 'certificates/verify.php', '*');

Router::page('gamification', 'gamification/index.php', ['student', 'regional']);
Router::page('gamification/badges', 'gamification/badges.php', ['student', 'regional']);
Router::page('gamification/leaderboard', 'gamification/leaderboard.php', ['student', 'regional']);

Router::page('notes', 'notes/notes.php', ['student', 'teacher', 'parent']);
Router::page('games', 'games/games.php', ['student', 'teacher']);
Router::page('search', 'search/index.php', '*');

Router::page('files', 'files/index.php', 'regional');
Router::page('files/view', 'files/view.php', 'regional');

Router::page('reports/index', 'reports/index.php', ['regional', 'teacher']);
Router::page('reports/export', 'reports/export.php', ['regional', 'teacher', 'principal']);

Router::page('settings/profile', 'settings/profile.php', '*');
Router::page('settings/password', 'settings/password.php', '*');
Router::page('settings/security', 'settings/security.php', '*');
Router::page('settings/hena_download', 'settings/hena_download.php', '*');
Router::page('settings/notifications', 'settings/notifications.php', '*');
Router::page('settings/preferences', 'settings/preferences.php', '*');
Router::page('settings/sessions', 'settings/sessions.php', '*');

Router::page('transfers', 'transfers/index.php', ['student', 'parent']);
Router::page('transfers/new', 'transfers/new.php', ['student', 'parent']);
Router::page('transfers/redeem', 'transfers/redeem.php', 'regional');

/* ---- Public / misc ---- */
Router::view('errors/404', 'errors/404');
Router::page('file', 'misc/file.php', '*');
Router::page('assets', 'misc/assets.php', '*');

/* ---------------- JSON API ---------------- */
Router::page('api/notifications/poll', 'api/notifications.php', '*');
Router::page('api/notifications', 'api/notifications.php', '*');
Router::page('api/search', 'api/search.php', '*');
Router::page('api/exams/autosave', 'api/exams.php', '*');
Router::page('api/exams/flag', 'api/exams.php', '*');
Router::page('api/messages/send', 'api/messages.php', '*');
Router::page('api/ai/chat', 'api/ai.php', '*');
Router::page('api/ai/warm', 'api/ai_warm.php', '*');
Router::page('api/attendance', 'api/attendance.php', '*');
Router::page('api/reactions', 'api/reactions.php', '*');
Router::page('api/upload', 'api/upload.php', '*');
Router::page('api/notify', 'api/notify.php', '*');
Router::page('api/settings', 'api/settings.php', '*');
Router::page('api/users', 'api/users.php', '*');
Router::page('api/profile', 'api/profile.php', '*');
Router::page('api/courses', 'api/courses.php', '*');
Router::page('api/calendar', 'api/calendar.php', '*');
Router::page('api/library', 'api/library.php', '*');
Router::page('api/gamification', 'api/gamification.php', '*');
Router::page('api/transfers', 'api/transfers.php', '*');
Router::page('api/reports', 'api/reports.php', '*');
Router::page('api/backups', 'api/backups.php', '*');
Router::page('api/attendance-mobile', 'api/attendance_mobile.php', '*');
Router::page('api/login', 'api/login.php', '*');
Router::page('api/activity', 'api/activity.php', '*');

/* ---- Demo toggle (any authenticated user) ---- */
Router::page('admin/toggle-demo', 'admin/toggle_demo.php', ['ministry', 'regional', 'registrar', 'dean', 'vice_dean', 'hod', 'lecturer', 'bursar', 'student_affairs', 'librarian', 'teacher', 'principal', 'student', 'parent', 'zonal', 'woreda', 'it_admin']);

/* ---- IT Admin (Fix Ticket System) ---- */
Router::page('it_admin/dashboard', 'it_admin/it_admin.php', 'it_admin');
Router::page('it_admin/tickets', 'it_admin/it_admin.php', 'it_admin');
Router::page('it_admin/ticket', 'it_admin/it_admin.php', 'it_admin');
Router::page('it_admin/fix', 'it_admin/it_admin.php', 'it_admin');
Router::page('it_admin/fix-session', 'it_admin/it_admin.php', 'it_admin');
Router::page('it_admin/resolve', 'it_admin/it_admin.php', 'it_admin');
Router::page('it_admin/audit', 'it_admin/it_admin.php', 'it_admin');
Router::page('ticket/create', 'it_admin/it_admin.php', '*');
Router::page('ticket/status', 'it_admin/it_admin.php', '*');

/* ---- Public: degree verification ---- */
Router::page('verify/degree', 'verify/degree.php', 'guest');
Router::page('verify/clearance', 'verify/verify.php', 'guest');
Router::page('verify/transcript', 'verify/verify.php', 'guest');
