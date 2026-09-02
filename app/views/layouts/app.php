<?php
/* App shell layout: sidebar + topbar + content */
$__u = me();
$__role = $__u['role'] ?? 'guest';
$__unread = Database::scalar("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL", [$__u['id']], 0);
$__route = trim($_GET['r'] ?? '', '/');

$__nav = [
  'ministry' => [
    ['dash', 'Overview', 'admin/dashboard', icon('chart-bar')],
    ['ACADEMIC'],
    ['users', 'Users', 'admin/users', icon('users')],
    ['schools', 'Schools', 'admin/schools', icon('school')],
    ['departments', 'Departments', 'admin/departments', icon('folder')],
    ['subjects', 'Subjects', 'admin/subjects', icon('books')],
    ['groups', 'Classes', 'admin/groups', icon('tag')],
    ['courses', 'Courses', 'admin/courses', icon('graduation')],
    ['ACADEMIC CALENDAR'],
    ['years', 'Academic Years', 'admin/years', icon('calendar')],
    ['calendar', 'Calendar Events', 'admin/calendar', icon('calendar-range')],
    ['holidays', 'Holidays & Observances', 'admin/holidays', icon('sunset')],
    ['transfers', 'Transfers', 'admin/transfers', icon('refresh')],
    ['OPERATIONS'],
    ['announcements', 'Announcements', 'admin/announcements', icon('megaphone')],
    ['reports', 'Reports', 'admin/reports', icon('trend-up')],
    ['analytics', 'Analytics', 'admin/analytics', icon('chart-bar')],
    ['logs', 'Logs', 'admin/logs', icon('note')],
    ['backups', 'Backups', 'admin/backups', icon('save')],
    ['PLATFORM'],
    ['modules', 'Modules', 'admin/modules', icon('box')],
    ['licenses', 'Licenses', 'admin/licenses', icon('ticket')],
    ['roles', 'Roles & Permissions', 'admin/roles', icon('lock')],
    ['settings', 'Settings', 'admin/settings', icon('gear')],
    ['ledger', 'Integrity Ledger', 'admin/ledger', icon('chain')],
    ['NATIONAL'],
    ['regions', 'Regions & Zones', 'admin/regions', icon('map')],
    ['ai_reports', 'AI Reports', 'admin/ai-reports', icon('robot')],
    ['finance', 'Finance Summary', 'admin/finance', icon('banknote')],
    ['override', 'Emergency Override', 'admin/override', icon('shield')],
  ],
  'regional' => [
    ['dash', 'Overview', 'regional/dashboard', icon('chart-bar')],
    ['SCHOOLS'],
    ['schools', 'My Schools', 'regional/schools', icon('school')],
    ['directors', 'Directors', 'regional/directors', icon('users-badge')],
    ['OPERATIONS'],
    ['analytics', 'Regional Analytics', 'regional/analytics', icon('trend-up')],
    ['announcements', 'Announcements', 'regional/announcements', icon('megaphone')],
    ['backups', 'Backups', 'regional/backups', icon('save')],
    ['audit', 'Audit Log', 'regional/audit', icon('note')],
  ],
  'zonal' => [
    ['dash', 'Overview', 'zonal/dashboard', icon('chart-bar')],
    ['WOREDAS'],
    ['woredas', 'Woredas', 'zonal/woredas', icon('folder')],
    ['SCHOOLS'],
    ['schools', 'My Zone Schools', 'zonal/schools', icon('school')],
    ['directors', 'Directors', 'zonal/directors', icon('users-badge')],
    ['OPERATIONS'],
    ['analytics', 'Zone Analytics', 'zonal/analytics', icon('trend-up')],
    ['announcements', 'Announcements', 'zonal/announcements', icon('megaphone')],
    ['audit', 'Audit Log', 'zonal/audit', icon('note')],
  ],
  'woreda' => [
    ['dash', 'Overview', 'woreda/dashboard', icon('chart-bar')],
    ['SCHOOLS'],
    ['schools', 'My Woreda Schools', 'woreda/schools', icon('school')],
    ['directors', 'Directors', 'woreda/directors', icon('users-badge')],
    ['OPERATIONS'],
    ['analytics', 'Woreda Analytics', 'woreda/analytics', icon('trend-up')],
    ['announcements', 'Announcements', 'woreda/announcements', icon('megaphone')],
    ['audit', 'Audit Log', 'woreda/audit', icon('note')],
  ],
  'it_admin' => [
    ['dash', 'Dashboard', 'it_admin/dashboard', icon('home')],
    ['FIX'],
    ['fix', 'Enter Fix Token', 'it_admin/fix', icon('wrench')],
    ['tickets', 'All Tickets', 'it_admin/tickets', icon('ticket')],
    ['MANAGEMENT'],
    ['audit', 'Audit Log', 'it_admin/audit', icon('note')],
  ],
  'registrar' => [
    ['dash', 'Dashboard', 'registrar/dashboard', icon('home')],
    ['ACADEMIC'],
    ['semesters', 'Semesters', 'registrar/semesters', icon('calendar')],
    ['admissions', 'Admissions', 'registrar/admissions', icon('flag')],
    ['enrollments', 'Enrollments', 'registrar/enrollments', icon('users')],
    ['transcripts', 'Transcripts', 'registrar/transcripts', icon('grades')],
    ['graduation', 'Graduation Audit', 'registrar/graduation', icon('medal')],
    ['scholarships', 'Scholarships', 'registrar/scholarships', icon('wallet')],
    ['UNIVERSITY'],
    ['programs', 'Programs', 'university/programs', icon('book')],
    ['university_semesters', 'Semesters', 'university/semesters', icon('calendar')],
    ['university_transcripts', 'Transcript Requests', 'university/transcript/manage', icon('file-text')],
    ['clearance_manage', 'Clearance', 'university/clearance/manage', icon('check-circle')],
    ['timetable', 'Timetable', 'university/timetable', icon('clock')],
    ['id_cards', 'ID Cards', 'university/id-cards', icon('badge')],
    ['OPERATIONS'],
    ['announcements', 'Announcements', 'registrar/announcements', icon('megaphone')],
    ['audit', 'Audit Log', 'registrar/audit', icon('note')],
  ],
  'dean' => [
    ['dash', 'Dashboard', 'dean/dashboard', icon('home')],
    ['FACULTY'],
    ['departments', 'Departments', 'dean/departments', icon('folder')],
    ['courses', 'Course Approval', 'dean/courses', icon('exam')],
    ['teachers', 'Teachers', 'dean/teachers', icon('users')],
    ['analytics', 'Analytics', 'dean/analytics', icon('chart-bar')],
    ['library', 'Library', 'teacher/library', icon('university')],
    ['UNIVERSITY'],
    ['university_programs', 'Programs', 'university/programs', icon('book')],
    ['university_theses', 'Theses', 'university/theses', icon('book')],
    ['clearance_manage', 'Clearance', 'university/clearance/manage', icon('check-circle')],
  ],
  'vice_dean' => [
    ['dash', 'Dashboard', 'dean/dashboard', icon('home')],
    ['FACULTY'],
    ['courses', 'Course Approval', 'dean/courses', icon('exam')],
    ['analytics', 'Analytics', 'dean/analytics', icon('chart-bar')],
    ['UNIVERSITY'],
    ['university_programs', 'Programs', 'university/programs', icon('book')],
    ['university_theses', 'Theses', 'university/theses', icon('book')],
  ],
  'hod' => [
    ['dash', 'Dashboard', 'dean/dashboard', icon('home')],
    ['DEPARTMENT'],
    ['courses', 'Courses', 'dept_head/courses', icon('exam')],
    ['theses', 'Theses', 'dept_head/theses', icon('book')],
    ['analytics', 'Analytics', 'dept_head/analytics', icon('chart-bar')],
    ['library', 'Library', 'teacher/library', icon('university')],
    ['UNIVERSITY'],
    ['university_theses', 'Theses', 'university/theses', icon('book')],
    ['clearance_manage', 'Clearance', 'university/clearance/manage', icon('check-circle')],
  ],
  'bursar' => [
    ['dash', 'Dashboard', 'dashboard', icon('home')],
    ['FINANCE'],
    ['fees_manage', 'Fee Structures', 'university/fees/manage', icon('dollar')],
    ['invoices', 'Invoices', 'university/fees/manage', icon('file')],
    ['clearance_manage', 'Clearance (Finance)', 'university/clearance/manage', icon('check-circle')],
  ],
  'student_affairs' => [
    ['dash', 'Dashboard', 'dashboard', icon('home')],
    ['STUDENT AFFAIRS'],
    ['clearance_manage', 'Clearance', 'university/clearance/manage', icon('check-circle')],
    ['id_cards', 'ID Cards', 'university/id-cards', icon('badge')],
  ],
  'librarian' => [
    ['dash', 'Dashboard', 'dashboard', icon('home')],
    ['LIBRARY'],
    ['library', 'Library', 'teacher/library', icon('university')],
    ['clearance_manage', 'Clearance (Library)', 'university/clearance/manage', icon('check-circle')],
  ],
  'lecturer' => [
    ['dash', 'Dashboard', 'teacher/dashboard', icon('home')],
    ['TEACHING'],
    ['courses', 'My Courses', 'teacher/courses', icon('graduation')],
    ['assignments', 'Assignments', 'teacher/assignments', icon('note')],
    ['exams', 'Exams', 'teacher/exams', icon('exam')],
    ['grades', 'Grade', 'teacher/grade', icon('grades')],
    ['students', 'Students', 'teacher/students', icon('users')],
    ['analytics', 'Analytics', 'teacher/analytics', icon('chart-bar')],
    ['library', 'Library', 'teacher/library', icon('university')],
  ],
  'principal' => [
    ['dash', 'Dashboard', 'director/dashboard', icon('home')],
    ['PEOPLE'],
    ['teachers', 'Teachers', 'director/teachers', icon('users')],
    ['students', 'Students', 'director/students', icon('graduation')],
    ['import', 'Import Users', 'director/import', icon('download')],
    ['ACADEMIC'],
    ['faculties', 'Faculties', 'director/faculties', icon('building')],
    ['courses', 'Courses', 'courses', icon('graduation')],
    ['library', 'Library', 'library', icon('university')],
    ['ai', 'AI Tutor', 'ai/tutor', icon('robot')],
    ['OPERATIONS'],
    ['announcements', 'Announcements', 'communication/announcements', icon('megaphone')],
    ['transfers', 'Transfers', 'director/transfers', icon('refresh')],
    ['reports', 'Reports', 'director/reports', icon('trend-up')],
    ['analytics', 'Analytics', 'director/analytics', icon('chart-bar')],
  ],
  'teacher' => [
    ['dash', 'Dashboard', 'teacher/dashboard', icon('home')],
    ['verify', 'Verify Students', 'teacher/verify', icon('check-circle')],
    ['courses', 'My Courses', 'teacher/courses', icon('graduation')],
    ['exams', 'Exams', 'teacher/exams', icon('note')],
    ['assignments', 'Assignments', 'teacher/assignments', icon('file')],
    ['attendance', 'Attendance', 'teacher/attendance', icon('doc')],
    ['students', 'Students', 'teacher/students', icon('users')],
    ['import', 'Import Users', 'teacher/import', icon('download')],
    ['GRADING'],
    ['grading', 'Gradebook', 'teacher/grading', icon('grades')],
    ['bonus', 'Bonus', 'teacher/bonus', icon('spark')],
    ['reports', 'Grading Reports', 'teacher/grading/reports', icon('file')],
    ['OVERVIEW'],
    ['forum', 'Discussion', 'teacher/forum', icon('chat')],
    ['library', 'Library', 'teacher/library', icon('university')],
    ['analytics', 'Analytics', 'teacher/analytics', icon('chart-bar')],
    ['reports', 'Reports', 'teacher/reports', icon('trend-up')],
    ['ai', 'AI Tutor', 'ai/tutor', icon('robot')],
  ],
  'student' => [
    ['dash', 'Dashboard', 'student/dashboard', icon('home')],
    ['courses', 'My Courses', 'student/courses', icon('graduation')],
    ['exams', 'Exams', 'student/exams', icon('note')],
    ['assignments', 'Assignments', 'student/assignments', icon('file')],
    ['attendance', 'Attendance', 'student/attendance', icon('doc')],
    ['grades', 'Grades', 'student/grades', icon('check-circle')],
    ['schedule', 'Schedule', 'student/schedule', icon('calendar')],
    ['ai', 'AI Tutor', 'ai/tutor', icon('robot')],
    ['flashcards', 'Flashcards', 'ai/flashcards', icon('game')],
    ['games', 'Games', 'games', icon('game')],
    ['library', 'Library', 'library', icon('university')],
    ['notes', 'My Notes', 'notes', icon('note')],
    ['leaderboard', 'Leaderboard', 'student/leaderboard', icon('trophy')],
    ['certificates', 'Certificates', 'certificates', icon('medal')],
    ['theses', 'My Theses', 'student/theses', icon('book')],
    ['transfers', 'Transfer', 'transfers', icon('refresh')],
    ['UNIVERSITY'],
    ['uni_registration', 'Course Registration', 'university/registration', icon('edit')],
    ['uni_schedule', 'My Timetable', 'university/my-schedule', icon('clock')],
    ['uni_transcript', 'Transcript', 'university/transcript', icon('file-text')],
    ['uni_clearance', 'Clearance', 'university/clearance', icon('check-circle')],
    ['uni_fees', 'Payments', 'university/fees', icon('dollar')],
    ['uni_theses', 'Thesis', 'university/theses', icon('book')],
  ],
  'parent' => [
    ['dash', 'Dashboard', 'parent/dashboard', icon('home')],
    ['children', 'My Children', 'parent/children', icon('users')],
    ['reports', 'Reports', 'parent/reports', icon('trend-up')],
    ['analytics', 'Analytics', 'analytics/student', icon('chart-bar')],
    ['library', 'Library', 'library', icon('university')],
  ],
];

if (($__u['role'] ?? '') === 'student' && ($__u['enrollment_status'] ?? 'active') === 'inactive') {
    $__hidden = ['student/attendance', 'student/grades', 'student/schedule', 'student/leaderboard', 'certificates', 'transfers', 'gamification'];
    $__nav['student'] = array_values(array_filter($__nav['student'], fn($i) => count($i) === 1 || !in_array($i[2], $__hidden, true)));
}

/* Education-level capability tiers + installed modules (student portal) */
if (($__u['role'] ?? '') === 'student') {
    $__sid = (int)($__u['school_id'] ?? 0);
    $__featureOf = [
        'student/exams' => 'exams', 'student/assignments' => 'assignments',
        'student/attendance' => 'attendance', 'student/grades' => 'grades',
        'student/schedule' => 'schedule', 'student/leaderboard' => 'leaderboard',
        'certificates' => 'certificates', 'transfers' => 'transfers',
        'student/theses' => 'thesis',
        'ai/tutor' => 'ai-tutor', 'ai/flashcards' => 'ai-tutor',
    ];
    $__moduleOf = ['library' => 'library', 'certificates' => 'certificate', 'student/theses' => 'thesis'];
    $__nav['student'] = array_values(array_filter($__nav['student'], function ($__i) use ($__featureOf, $__moduleOf, $__sid) {
        if (count($__i) === 1) return true;
        if (isset($__featureOf[$__i[2]]) && !student_can($__featureOf[$__i[2]])) return false;
        if (isset($__moduleOf[$__i[2]]) && !module_active($__sid, $__moduleOf[$__i[2]])) return false;
        if (str_starts_with($__i[2], 'ai/') && !module_active($__sid, 'ai-tutor')) return false;
        return true;
    }));
}

if (($__u['role'] ?? '') === 'teacher' && Database::scalar("SELECT COUNT(*) FROM student_groups WHERE homeroom_teacher_id = ?", [$__u['id']], 0) > 0) {
    $__i = array_search('attendance', array_column($__nav['teacher'], 2), true);
    array_splice($__nav['teacher'], ($__i === false ? 7 : $__i + 1), 0, [['homeroom', 'Homeroom', 'teacher/homeroom', icon('school')]]);
}

/* Director: only show faculties + transfers for university/college schools */
if (($__u['role'] ?? '') === 'principal') {
    $__schoolType = Database::scalar("SELECT type FROM schools WHERE id = ?", [$__u['school_id'] ?? 0], 'school');
    if (!in_array($__schoolType, ['university', 'college'], true)) {
        $__nav['principal'] = array_values(array_filter($__nav['principal'], fn($i) => count($i) === 1 || !in_array($i[2], ['director/faculties', 'director/transfers'])));
    }
}

/* Student: only show transfers for university/college schools */
if (($__u['role'] ?? '') === 'student') {
    $__schoolType = Database::scalar("SELECT type FROM schools WHERE id = ?", [$__u['school_id'] ?? 0], 'school');
    if (!in_array($__schoolType, ['university', 'college'], true)) {
        $__nav['student'] = array_values(array_filter($__nav['student'], fn($i) => count($i) === 1 || $i[2] !== 'transfers'));
    }
}

$__icons = [
  'dashboard' => icon('home'), 'courses' => icon('graduation'), 'exams' => icon('note'), 'assignments' => icon('file'),
  'attendance' => icon('doc'), 'grades' => icon('check-circle'), 'schedule' => icon('calendar'), 'ai' => icon('robot'),
  'flashcards' => icon('game'), 'games' => icon('game'), 'library' => icon('university'), 'leaderboard' => icon('trophy'), 'certificates' => icon('medal'),
  'transfers' => icon('refresh'), 'children' => icon('user') . '‍' . icon('user') . '‍' . icon('user'), 'reports' => icon('trend-up'), 'analytics' => icon('chart-bar'),
  'users' => icon('users'), 'schools' => icon('school'), 'departments' => icon('folder'), 'subjects' => icon('books'),
  'groups' => icon('tag'), 'years' => icon('calendar'), 'announcements' => icon('megaphone'), 'logs' => icon('note'),
  'backups' => icon('save'), 'roles' => icon('lock'), 'settings' => icon('gear'), 'grade' => icon('check-circle'),
  'students' => icon('users'), 'forum' => icon('chat'), 'notifications' => icon('bell'), 'calendar' => icon('calendar'),
  'messages' => icon('chat'), 'files' => icon('folder'), 'search' => icon('search'), 'gamification' => icon('trophy'),
  'notes' => icon('note'),
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e(current_theme()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Edunex">
  <meta name="theme-color" content="#0b0e14">
  <title><?= e($title ?? 'Dashboard') ?> — <?= e(APP_NAME) ?></title>
  <link rel="icon" href="<?= url('public/images/favicon.svg') ?>">
  <script>document.documentElement.dataset.theme = localStorage.getItem('edunex-theme') || '<?= e(current_theme()) ?>';</script>
  <link rel="stylesheet" href="<?= url('public/css/app.css?v=42') ?>">
  <style>
    <?php
    $accentMap = ['teal'=>'#0d9488','blue'=>'#0284c7','indigo'=>'#4f46e5','purple'=>'#7c3aed','pink'=>'#db2777','red'=>'#ef4444','orange'=>'#f97316','amber'=>'#f59e0b','emerald'=>'#059669','cyan'=>'#06b6d4','rose'=>'#f43f5e','violet'=>'#8b5cf6'];
    $ac = $accentMap[$__u['accent_color'] ?? 'teal'] ?? '#0d9488';
    $fs = e($__u['font_size'] ?? '14');
    $cw = e($__u['content_width'] ?? '1400');
    $compact = ($__u['compact_mode'] ?? '0') === '1';
    $noAnim = ($__u['reduce_motion'] ?? '0') === '1' || ($__u['show_animations'] ?? '1') === '0';
    $noBlur = ($__u['blur_effects'] ?? '1') === '0';
    $noBorders = ($__u['show_borders'] ?? '1') === '0';
    $noGradients = ($__u['show_gradients'] ?? '1') === '0';
    $sidebarStyle = $__u['sidebar_style'] ?? 'default';
    ?>
    :root {
      --accent: <?= $ac ?>;
      --font-size-base: <?= $fs ?>px;
      --content-max-width: <?= $cw ?>px;
      --radius-lg: <?= e($__u['card_radius'] ?? '18') ?>px;
      --radius: <?= max(0, (int)($__u['card_radius'] ?? '18') - 4) ?>px;
      --line-height: <?= e($__u['line_height'] ?? '1.55') ?>;
      <?php if ($compact): ?>
      --sidebar-w: 60px;
      <?php endif; ?>
    }
    <?php if ($noAnim): ?>
    *, *::before, *::after { animation-duration: 0s !important; transition-duration: 0s !important; }
    <?php endif; ?>
    <?php if ($noBlur): ?>
    *, *::before, *::after { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
    <?php endif; ?>
    <?php if ($noBorders): ?>
    .card, .stat-card, .stat-box, .tstat-card, .module-card { border-color: transparent !important; }
    <?php endif; ?>
    <?php if ($noGradients): ?>
    .card::before, .stat-card::before, .stat-box::before { background: none !important; }
    <?php endif; ?>
    <?php if ($sidebarStyle === 'compact'): ?>
    .sidebar .nav-label, .sidebar .brand-sub, .sidebar .brand-name, .sidebar .page-indicator-label { display: none !important; }
    .sidebar { width: 64px !important; min-width: 64px !important; padding: 14px 6px !important; overflow: hidden; }
    .sidebar .nav-item { padding: 10px !important; justify-content: center; text-indent: -999px; overflow: hidden; }
    .sidebar .nav-item .ico { margin: 0 !important; font-size: 20px; text-indent: 0; }
    .sidebar .nav-item .cnt { position: absolute; top: 4px; right: 4px; font-size: 9px; padding: 1px 4px; text-indent: 0; }
    .sidebar .brand { justify-content: center; padding: 0 0 8px !important; }
    .sidebar .brand img { margin: 0 !important; width: 34px !important; height: 34px !important; }
    .sidebar .page-indicator { justify-content: center; padding: 8px; margin: 0 4px 10px; }
    .sidebar .nav-group-toggle { justify-content: center; padding: 10px; text-indent: -999px; overflow: hidden; }
    .sidebar .nav-group-chevron { display: none; }
    .sidebar .nav-group-items .nav-item { padding-left: 10px; }
    .shell { grid-template-columns: 64px minmax(0, 1fr) !important; }
    .topbar { left: 64px !important; width: calc(100% - 64px) !important; }
    <?php elseif ($sidebarStyle === 'icons'): ?>
    .sidebar .nav-label, .sidebar .brand-sub, .sidebar .brand-name, .sidebar .nav-section, .sidebar .page-indicator { display: none !important; }
    .sidebar .nav-item { padding: 10px !important; justify-content: center; text-indent: -999px; overflow: hidden; }
    .sidebar .nav-item .ico { margin: 0 !important; font-size: 20px; text-indent: 0; }
    .sidebar .nav-item .cnt { position: absolute; top: 4px; right: 4px; font-size: 9px; padding: 1px 4px; text-indent: 0; }
    .sidebar .brand { justify-content: center; padding: 0 0 8px !important; }
    .sidebar .brand img { margin: 0 !important; width: 30px !important; height: 30px !important; }
    .sidebar .nav-group-toggle { display: none !important; }
    .sidebar .nav-group-items .nav-item { padding-left: 10px; }
    .sidebar .nav-group-items { grid-template-rows: 1fr !important; }
    .shell { grid-template-columns: 60px minmax(0, 1fr) !important; }
    .topbar { left: 60px !important; width: calc(100% - 60px) !important; }
    <?php endif; ?>
  </style>
</head>
<body>
  <div class="shell">
    <!-- ======================= SIDEBAR ======================= -->
    <aside class="sidebar">
      <div class="brand">
        <img class="brand-logo" src="<?= url('public/images/logo-black.jpeg') ?>" alt="Edunex">
        <div>
          <div class="brand-name">Edunex<?php if (is_demo_mode()): ?> <span style="display:inline-block;font-size:0.55em;background:var(--warning,#f59e0b);color:#000;padding:1px 5px;border-radius:4px;vertical-align:middle;cursor:help" title="DEMO mode active — sample data shown. Switch to Normal mode in Settings.">DEMO</span><?php endif; ?></div>
          <div class="brand-sub"><?= e(setting('site_name', 'Learning')) ?> · <?= e($__u['school_name'] ?? '') ?></div>
        </div>
      </div>

      <?php
      // Build current page indicator
      $currentPageLabel = 'Dashboard';
      $currentPageIcon = icon('chart-bar');
      foreach (($__nav[$__role] ?? []) as $ni) {
          if (count($ni) > 1) {
              [, $lbl, $href, $ico] = $ni;
              if ($__route === $href || str_starts_with($__route, $href . '/')) {
                  $currentPageLabel = $lbl;
                  $currentPageIcon = $ico;
                  break;
              }
          }
      }
      if ($__route === '' || $__route === 'dashboard') { $currentPageLabel = 'Dashboard'; $currentPageIcon = icon('chart-bar'); }
      ?>
      <div class="page-indicator">
        <span class="page-indicator-ico"><?= $currentPageIcon ?></span>
        <span class="page-indicator-label"><?= e($currentPageLabel) ?></span>
      </div>

      <?php
      // Group nav items into sections for accordion
      $__sections = [];
      $__currentSection = null;
      $__dashItem = null;
      foreach (($__nav[$__role] ?? []) as $__navItem) {
          if (count($__navItem) === 1) {
              $__currentSection = $__navItem[0];
              $__sections[$__currentSection] = [];
          } elseif ($__currentSection !== null) {
              $__sections[$__currentSection][] = $__navItem;
          } else {
              // Items before first section header (e.g. Dashboard)
              $__dashItem = $__navItem;
          }
      }
      // Detect active section
      $__activeSection = null;
      foreach ($__sections as $secName => $items) {
          foreach ($items as $item) {
              [, , $href] = $item;
              if ($__route === $href || str_starts_with($__route, $href . '/') || ($href === 'dashboard' && $__route === '')) {
                  $__activeSection = $secName;
                  break 2;
              }
          }
      }
      ?>

      <?php if ($__dashItem): ?>
        <?php [, $__label, $__href, $__icon] = $__dashItem; ?>
        <?php $active = $__route === $__href || ($__href === 'dashboard' && $__route === ''); ?>
        <a class="nav-item <?= $active ? 'active' : '' ?>" href="<?= url('index.php?r=' . $__href) ?>">
          <span class="ico"><?= $__icon ?></span><span class="nav-label"><?= e($__label) ?></span>
        </a>
      <?php endif; ?>

      <?php foreach ($__sections as $secName => $secItems): ?>
        <?php $isOpen = $secName === $__activeSection; ?>
        <div class="nav-group <?= $isOpen ? 'open' : '' ?>">
          <button class="nav-group-toggle" onclick="toggleNavGroup(this)">
            <span class="nav-group-label"><?= e($secName) ?></span>
            <span class="nav-group-chevron"><?= icon('chevron-down') ?></span>
          </button>
          <div class="nav-group-items">
            <?php foreach ($secItems as $item): ?>
              <?php [, $__label, $__href, $__icon] = $item; ?>
              <?php if (str_starts_with($__route, $__href)): ?>
                <?php $active = true; ?>
              <?php else: ?>
                <?php $active = $__href === $__route || ($__href === 'dashboard' && $__route === ''); ?>
              <?php endif; ?>
              <a class="nav-item <?= $active ? 'active' : '' ?>" href="<?= url('index.php?r=' . $__href) ?>">
                <span class="ico"><?= $__icon ?></span><span class="nav-label"><?= e($__label) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <?php $__inactive = ($__u['role'] ?? '') === 'student' && ($__u['enrollment_status'] ?? 'active') === 'inactive'; ?>
      <?php
      // Check if any General item is active
      $__generalActive = str_starts_with($__route, 'messages') || str_starts_with($__route, 'calendar') || str_starts_with($__route, 'gamification') || str_starts_with($__route, 'files') || str_starts_with($__route, 'notifications') || str_starts_with($__route, 'settings');
      ?>
      <div class="nav-group <?= $__generalActive ? 'open' : '' ?>">
        <button class="nav-group-toggle" onclick="toggleNavGroup(this)">
          <span class="nav-group-label">General</span>
          <span class="nav-group-chevron"><?= icon('chevron-down') ?></span>
        </button>
        <div class="nav-group-items">
          <?php if (!$__inactive): ?>
          <a class="nav-item <?= str_starts_with($__route, 'messages') ? 'active' : '' ?>" href="<?= url('index.php?r=messages') ?>">
            <span class="ico"><?= icon('chat') ?></span><span class="nav-label">Messages</span>
          </a>
          <a class="nav-item <?= str_starts_with($__route, 'calendar') ? 'active' : '' ?>" href="<?= url('index.php?r=calendar') ?>">
            <span class="ico"><?= icon('calendar') ?></span><span class="nav-label">Calendar</span>
          </a>
          <?php if (($__u['role'] ?? '') === 'student'): ?>
          <a class="nav-item <?= str_starts_with($__route, 'gamification') ? 'active' : '' ?>" href="<?= url('index.php?r=gamification') ?>">
            <span class="ico"><?= icon('game') ?></span><span class="nav-label">Gamification</span>
          </a>
          <?php endif; ?>
          <?php if (($__u['role'] ?? '') === 'ministry'): ?>
          <a class="nav-item <?= str_starts_with($__route, 'files') ? 'active' : '' ?>" href="<?= url('index.php?r=files') ?>">
            <span class="ico"><?= icon('folder') ?></span><span class="nav-label">Files</span>
          </a>
          <?php endif; ?>
          <?php endif; ?>
          <a class="nav-item <?= str_starts_with($__route, 'notifications') ? 'active' : '' ?>" href="<?= url('index.php?r=notifications') ?>">
            <span class="ico"><?= icon('bell') ?></span><span class="nav-label">Notifications</span>
            <?php if ($__unread > 0): ?><span class="cnt"><?= min($__unread, 99) ?></span><?php endif; ?>
          </a>
          <a class="nav-item <?= str_starts_with($__route, 'settings') ? 'active' : '' ?>" href="<?= url('index.php?r=settings/profile') ?>">
            <span class="ico"><?= icon('gear') ?></span><span class="nav-label">Settings</span>
          </a>
        </div>
      </div>
    </aside>

    <!-- ======================= MAIN ======================= -->
    <main class="main">
      <div class="topbar">
        <button class="btn btn-ghost menu-btn" style="padding:8px"><?= icon('menu') ?></button>

        <form class="search-box" method="get" action="<?= e(url('search')) ?>" id="global-search-form">
          <input type="hidden" name="r" value="search">
          <span class="input-ico"><?= icon('search') ?></span>
          <input id="global-search" type="text" name="q" placeholder="Search courses, books, people…" autocomplete="off" oninput="document.getElementById('gs-clear').style.display=this.value?'flex':'none';clearTimeout(this._t);if(this.value.length>=2)this._t=setTimeout(()=>this.form.submit(),400)">
          <button type="button" class="input-icon-btn" id="gs-clear" onclick="document.getElementById('global-search').value='';this.style.display='none';document.getElementById('global-search').focus()"><?= icon('x') ?></button>
          <button type="submit" class="search-submit" title="Search"><?= icon('search') ?></button>
        </form>

        <div class="spacer"></div>

        <a class="topbar-icon" href="<?= url('index.php?r=messages') ?>" title="Messages"><?= icon('chat') ?><?php
          $__mUnread = (int)Database::scalar(
            "SELECT COUNT(*) FROM messages m JOIN conversation_members cm ON cm.conversation_id = m.conversation_id AND cm.user_id = ?
             WHERE m.sender_id != ? AND m.created_at > COALESCE(cm.last_read_at, '1970-01-01')", [$__u['id'], $__u['id']], 0);
          if ($__mUnread > 0): ?><span class="dot"></span><?php endif; ?></a>

        <div class="dropdown" style="position:relative">
          <button class="topbar-icon" id="notif-bell">
            <?= icon('bell') ?><span class="dot" <?= $__unread ? '' : 'style="display:none"' ?>></span>
          </button>
          <div class="dropdown-menu notif-panel">
            <div class="dropdown-head flex-between">
              <span>Notifications</span>
              <?php if ($__unread): ?><button class="btn btn-sm btn-ghost" onclick="EdunexNotif.markAll()">✓ Mark all read</button><?php endif; ?>
            </div>
            <?php
              $__notifs = Database::all("SELECT * FROM notifications WHERE user_id = ? AND read_at IS NULL ORDER BY created_at DESC LIMIT 8", [$__u['id']]);
              if ($__notifs):
                foreach ($__notifs as $n):
            ?>
              <a class="notif-item <?= $n['read_at'] ? '' : 'unread' ?>" href="<?= url('index.php?r=' . ($n['link'] ?: 'notifications')) ?>"
                 onclick="EdunexNotif.markRead(<?= (int)$n['id'] ?>, this)">
                <span class="notif-ico" style="background:var(--accent-soft)"><?= match ($n['type']) { 'assignment' => icon('file'), 'exam' => icon('note'), 'feedback' => icon('chat'), 'announcement' => icon('megaphone'), 'achievement' => icon('trophy'), 'message' => icon('mail'), 'reminder' => icon('clock'), default => icon('bell') } ?></span>
                <span><b><?= e($n['title']) ?></b><br><span class="tiny faint"><?= e($n['body']) ?> · <?= e(time_ago($n['created_at'])) ?></span></span>
              </a>
            <?php endforeach;
              else: ?>
              <div class="empty" style="padding:26px"><span class="empty-ico"><?= icon('bell-off') ?></span>All caught up</div>
            <?php endif; ?>
            <?php if ($__notifs): ?><a class="dropdown-item" href="<?= url('index.php?r=notifications') ?>" style="margin-top:4px"><?= icon('bell') ?> View all</a><?php endif; ?>
          </div>
        </div>

        <button class="topbar-icon" data-theme-toggle onclick="EdunexTheme.toggle()" title="Toggle theme"><?= icon('sun') ?></button>

        <?php if (($__u['role'] ?? '') !== 'it_admin'): ?>
        <div style="position:relative">
          <button class="topbar-icon" onclick="toggleReportDropdown(event)" title="Report Issue" style="color:var(--warning,#f59e0b)"><?= icon('wrench') ?></button>
          <div id="report-dropdown" class="report-dd">
            <a class="dropdown-item" href="#" onclick="closeReportDropdown();document.getElementById('report-issue-modal').style.display='flex';return false">
              <span style="margin-right:8px;color:var(--warning)"><?= icon('plus-circle') ?></span> New Report
            </a>
            <a class="dropdown-item" href="#" onclick="closeReportDropdown();openReportTracking();return false">
              <span style="margin-right:8px;color:var(--info)"><?= icon('list') ?></span> Report Tracking
            </a>
          </div>
        </div>
        <?php endif; ?>

        <div class="dropdown" style="position:relative">
          <button class="topbar-icon" style="border:none;background:none;padding:2px">
            <img class="avatar" src="<?= e(avatar_url($__u)) ?>" alt="avatar" style="width:34px;height:34px;border-radius:50%;object-fit:cover">
          </button>
          <div class="dropdown-menu profile-dropdown">
            <div style="padding:16px 18px 12px;border-bottom:1px solid var(--glass-border)">
              <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px">
                <img class="avatar avatar-lg" src="<?= e(avatar_url($__u)) ?>" alt="avatar" style="width:48px;height:48px;border-radius:50%;object-fit:cover;box-shadow:0 2px 12px rgba(0,0,0,.2)">
                <div>
                  <div style="font-weight:700;font-size:15px;color:var(--text)"><?= e(full_name($__u)) ?></div>
                  <div style="font-size:12px;color:var(--text-dim);margin-top:2px"><?= e($__u['student_id'] ?? $__u['email']) ?></div>
                </div>
              </div>
              <div style="display:flex;gap:6px;flex-wrap:wrap">
                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:var(--accent-soft);color:var(--accent)"><?= icon('user') ?> <?= e(ucfirst($__u['role'])) ?></span>
                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(139,92,246,.1);color:#a78bfa">Level <?= (int)$__u['level'] ?></span>
                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(245,158,11,.1);color:#fbbf24"><?= icon('zap') ?> <?= (int)$__u['xp'] ?> XP</span>
              </div>
            </div>
            <div style="padding:6px">
              <a class="dropdown-item" href="<?= url('index.php?r=settings/profile') ?>">
                <span class="dropdown-ico"><?= icon('gear') ?></span> Profile settings
              </a>
              <a class="dropdown-item" href="<?= url('index.php?r=settings/security') ?>">
                <span class="dropdown-ico"><?= icon('shield') ?></span> Security
              </a>
            </div>
            <div style="height:1px;background:var(--glass-border);margin:2px 12px"></div>
            <div style="padding:6px">
              <form method="post" action="<?= url('index.php?r=admin/toggle-demo') ?>" style="margin:0">
                <?= csrf_field() ?>
                <button type="submit" class="dropdown-item" style="gap:10px;cursor:pointer;border:none;background:none;width:100%;text-align:left;font:inherit;color:inherit;padding:9px 12px;border-radius:9px">
                  <span class="dropdown-ico"><?= is_demo_mode() ? icon('eye-off') : icon('eye') ?></span>
                  <?= is_demo_mode() ? 'Switch to Normal' : 'Switch to Demo' ?>
                  <span style="margin-left:auto;font-size:10px;padding:2px 8px;border-radius:99px;font-weight:700;<?= is_demo_mode() ? 'background:rgba(245,158,11,.15);color:#fbbf24' : 'background:rgba(34,197,94,.15);color:#22c55e' ?>"><?= is_demo_mode() ? 'DEMO' : 'NORMAL' ?></span>
                </button>
              </form>
              <a class="dropdown-item danger" href="<?= url('index.php?r=auth/logout') ?>">
                <span class="dropdown-ico"><?= icon('logout') ?></span> Log out
              </a>
            </div>
          </div>
        </div>
      </div>

      <?php if (isset($_SESSION['impersonated_by']) && !empty($_SESSION['impersonated_by'])): ?>
        <div style="background:linear-gradient(90deg,#7c2d12,#9a3412);color:#fff;padding:10px 18px;border-radius:10px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
          <span><?= icon('shield') ?> <b>EMERGENCY OVERRIDE</b> — you are acting as <?= e(full_name($__u)) ?> (<?= e($__u['email']) ?>, <?= e($__u['role']) ?>). All actions are logged.</span>
          <a href="<?= e(url('admin/override-exit')) ?>" class="btn btn-xs" style="background:#fff;color:#7c2d12">Exit override</a>
        </div>
      <?php endif; ?>

      <div id="view-root">
        <?php include BASE_PATH . '/app/views/' . $__view . '.php'; ?>
      </div>
    </main>
  </div>

  <script>
    window.EDUNEX_USER = <?= json_encode(['id' => $__u['id'], 'role' => $__u['role'], 'name' => full_name($__u)]) ?>;
    window.EDUNEX = { URL: <?= json_encode(APP_URL) ?>, API: <?= json_encode(APP_URL) ?> };
    window.EDUNEX_FLASHES = <?= json_encode(flash_drain()) ?>;
    localStorage.setItem('edunex-toast-pos', <?= json_encode($__u['toast_position'] ?? 'top-right') ?>);
  </script>
  <script src="<?= url('public/js/app.js?v=12') ?>"></script>
  <script>
    (function() {
      var src = new XMLHttpRequest();
      src.open('GET', '<?= url('index.php?r=api/ai/warm') ?>', true);
      src.send();
    })();
  </script>
  <?php if (isset($__scripts)): foreach ($__scripts as $s): ?>
    <script src="<?= url('public/js/' . $s) ?>"></script>
  <?php endforeach; endif; ?>

  <!-- Report Issue Modal -->
  <div id="report-issue-modal" class="modal-backdrop" style="display:none" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal" style="max-width:520px;width:90%">
      <div style="padding:28px 28px 0">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
          <span style="width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,.12);display:flex;align-items:center;justify-content:center;color:var(--warning)"><?= icon('wrench') ?></span>
          <h3 style="margin:0;font-size:17px">Report Issue / Request Fix</h3>
        </div>
        <p style="color:var(--text-dim);font-size:13px;margin:0 0 18px;line-height:1.5">
          This creates a secure fix ticket. The IT admin can ONLY access this page — no other data or settings.
        </p>
      </div>
      <form method="POST" action="<?= url('index.php?r=ticket/create') ?>" style="padding:0 28px 24px">
        <?= csrf_field() ?>
        <?php
        $pageGroups = [
          'Dashboard & Core' => [
            'admin/dashboard' => 'Admin Dashboard',
            'dashboard' => 'Dashboard',
            'notifications' => 'Notifications',
            'messages' => 'Messages',
            'calendar' => 'Calendar',
          ],
          'User Management' => [
            'admin/users' => 'Users',
            'admin/schools' => 'Schools',
            'admin/departments' => 'Departments',
            'admin/subjects' => 'Subjects',
            'admin/groups' => 'Classes',
          ],
          'Academic' => [
            'admin/courses' => 'Courses',
            'admin/years' => 'Academic Years',
            'admin/calendar' => 'Calendar Events',
            'admin/announcements' => 'Announcements',
            'admin/reports' => 'Reports',
          ],
          'Analytics & System' => [
            'admin/analytics' => 'Command Center',
            'admin/modules' => 'Module Registry',
            'admin/regions' => 'Regions / Zones',
            'admin/override' => 'Emergency Override',
          ],
          'Student' => [
            'student/assignments' => 'Assignments',
            'student/exams' => 'Exams',
            'student/grades' => 'Grades',
            'student/attendance' => 'Attendance',
            'student/materials' => 'Materials',
            'student/gamification' => 'Gamification',
            'student/certificates' => 'Certificates',
          ],
          'Teacher' => [
            'teacher/courses' => 'My Courses',
            'teacher/assignments' => 'Assignments',
            'teacher/exams' => 'Exams',
            'teacher/grades' => 'Gradebook',
            'teacher/attendance' => 'Attendance',
            'teacher/materials' => 'Materials',
            'teacher/forum' => 'Forum',
          ],
          'Parent' => [
            'parent/overview' => 'Overview',
            'parent/children' => 'Children',
            'parent/grades' => 'Grades',
            'parent/attendance' => 'Attendance',
          ],
          'Tools & Content' => [
            'courses/browse' => 'Browse Courses',
            'library' => 'Library',
            'files' => 'Files',
            'gamification' => 'Gamification',
            'search' => 'Search',
          ],
          'Settings' => [
            'settings/profile' => 'Profile Settings',
            'settings/display' => 'Display Settings',
            'settings/security' => 'Security Settings',
          ],
        ];
        $currentRoute = e($__route);
        ?>
        <div class="field" style="margin-bottom:14px">
          <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block">Select page / system</label>
          <select class="input" id="report-page-select" required style="width:100%" onchange="var v=this.value.split('||');document.getElementById('report-page-route').value=v[0];document.getElementById('report-page-label').value=v[1]">
            <option value="">— Choose a page —</option>
            <?php foreach ($pageGroups as $group => $pages): ?>
              <optgroup label="<?= e($group) ?>">
                <?php foreach ($pages as $route => $label): ?>
                  <option value="<?= e($route) ?>||<?= e($label) ?>" <?= $currentRoute === $route ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
            <optgroup label="Other">
              <option value="<?= e($__route) ?>||Other: <?= e($__route) ?>">Current page (<?= e($__route ?: 'home') ?>)</option>
              <option value="other||Other (specify below)">Other — describe below</option>
            </optgroup>
          </select>
        </div>
        <input type="hidden" name="page_route" id="report-page-route" value="<?= e($__route) ?>">
        <input type="hidden" name="page_label" id="report-page-label" value="<?= e($title ?? '') ?>">
        <div class="field" style="margin-bottom:18px">
          <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block">Describe the issue</label>
          <textarea name="description" class="input" rows="3" placeholder="What's wrong or what needs fixing?" required style="width:100%;resize:vertical"></textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end">
          <button type="button" class="btn btn-ghost" onclick="document.getElementById('report-issue-modal').style.display='none'">Cancel</button>
          <button class="btn btn-primary" type="submit"><?= icon('send') ?> Create Fix Ticket</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Report Tracking Modal -->
  <div id="report-tracking-modal" class="modal-backdrop" style="display:none" onclick="if(event.target===this)closeReportTracking()">
    <div class="modal" style="max-width:600px;width:92%;max-height:80vh;overflow-y:auto">
      <div style="padding:28px 28px 0">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
          <div style="display:flex;align-items:center;gap:10px">
            <span style="width:36px;height:36px;border-radius:10px;background:rgba(59,130,246,.12);display:flex;align-items:center;justify-content:center;color:var(--info)"><?= icon('list') ?></span>
            <h3 style="margin:0;font-size:17px">Report Tracking</h3>
          </div>
          <button class="btn btn-ghost btn-sm" onclick="closeReportTracking()" style="padding:4px 8px">✕</button>
        </div>
        <p style="color:var(--text-dim);font-size:13px;margin:0 0 18px;line-height:1.5">
          Track your submitted fix tickets · Auto-refreshes every 15s
        </p>
      </div>
      <div id="tracking-list" style="padding:0 28px 24px">
        <div style="text-align:center;color:var(--text-dim);padding:30px">Loading...</div>
      </div>
    </div>
  </div>

  <!-- Fix Ticket Result Modal -->
  <?php if (!empty($_SESSION['fix_ticket'])): ?>
  <?php $ft = $_SESSION['fix_ticket']; unset($_SESSION['fix_ticket']); ?>
  <div id="fix-ticket-result" class="modal-backdrop" style="display:none" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal" style="max-width:480px;width:90%">
      <div style="padding:28px 28px 0">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
          <span style="width:36px;height:36px;border-radius:10px;background:rgba(34,197,94,.12);display:flex;align-items:center;justify-content:center;color:var(--success)"><?= icon('check-circle') ?></span>
          <h3 style="margin:0;font-size:17px">Fix Ticket #<?= e($ft['id']) ?> Created</h3>
        </div>
        <p style="color:var(--text-dim);font-size:13px;margin:0 0 18px;line-height:1.5">
          Share this token with your IT admin:
        </p>
      </div>
      <div style="padding:0 28px 24px">
        <div style="background:var(--bg-elev);padding:14px 16px;border-radius:12px;font-family:monospace;font-size:13px;word-break:break-all;margin-bottom:12px;border:1px solid var(--border)">
          <?= e($ft['token']) ?>
        </div>
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap">
          <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(245,158,11,.12);color:var(--warning)">⏱ Token expires in 24 hours</span>
          <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(59,130,246,.12);color:var(--info)">⏰ Admin has 6h to fix</span>
        </div>
        <p style="font-size:13px;color:var(--text-dim);margin:0 0 4px">Page: <b><?= e($ft['page']) ?></b></p>
        <p style="font-size:12px;color:var(--text-dim);margin:0 0 16px;line-height:1.5">After 6 hours the priority escalates to <span style="color:var(--danger);font-weight:600">HIGH</span>. After 12 hours to <span style="color:var(--danger);font-weight:700">CRITICAL</span>. Token expires after 24 hours.</p>
        <div style="display:flex;gap:10px;justify-content:flex-end">
          <button class="btn btn-ghost" onclick="document.getElementById('fix-ticket-result').style.display='none'">Close</button>
          <button class="btn btn-primary" onclick="navigator.clipboard.writeText(this.dataset.token).then(()=>this.textContent='Copied!')" data-token="<?= e($ft['token']) ?>"><?= icon('copy') ?> Copy Token</button>
        </div>
      </div>
    </div>
  </div>
  <script>setTimeout(()=>{var m=document.getElementById('fix-ticket-result');if(m)m.style.display='flex';},100);</script>
  <?php endif; ?>
  <script>
  /* Sidebar accordion groups */
  function toggleNavGroup(btn) {
    var group = btn.parentElement;
    var wasOpen = group.classList.contains('open');
    // Close all other groups
    document.querySelectorAll('.nav-group.open').forEach(function(g) {
      if (g !== group) g.classList.remove('open');
    });
    // Toggle this one
    if (wasOpen) {
      group.classList.remove('open');
    } else {
      group.classList.add('open');
    }
  }

  /* Report dropdown toggle */
  function toggleReportDropdown(e) {
    e.stopPropagation();
    var dd = document.getElementById('report-dropdown');
    dd.classList.toggle('open');
  }
  function closeReportDropdown() {
    var dd = document.getElementById('report-dropdown');
    if (dd) dd.classList.remove('open');
  }
  document.addEventListener('click', function(e) {
    var dd = document.getElementById('report-dropdown');
    if (dd && !dd.contains(e.target)) dd.classList.remove('open');
  });

  /* Report Tracking */
  var _trackTimer = null;
  function openReportTracking() {
    document.getElementById('report-tracking-modal').style.display = 'flex';
    loadTracking();
    if (_trackTimer) clearInterval(_trackTimer);
    _trackTimer = setInterval(loadTracking, 15000); // refresh every 15s
  }
  function closeReportTracking() {
    document.getElementById('report-tracking-modal').style.display = 'none';
    if (_trackTimer) { clearInterval(_trackTimer); _trackTimer = null; }
  }
  function loadTracking() {
    var list = document.getElementById('tracking-list');
    list.innerHTML = '<div style="text-align:center;color:var(--text-dim);padding:30px">Loading...</div>';
    fetch('<?= url('index.php?r=ticket/tracking') ?>')
      .then(function(r){ return r.json(); })
      .then(function(data) {
        var tickets = data.tickets || [];
        if (!tickets.length) {
          list.innerHTML = '<div style="text-align:center;color:var(--text-dim);padding:40px 20px;border:1px dashed var(--border);border-radius:14px"><div style="font-size:32px;margin-bottom:10px;opacity:.5">📋</div><div style="font-weight:600;margin-bottom:4px">No reports yet</div><div style="font-size:13px">Submit a fix ticket to see it here</div></div>';
          return;
        }
        var html = '<div id="ticket-accordion">';
        var statusColors = {open:'var(--warning)',in_progress:'var(--info)',resolved:'var(--success)',closed:'var(--text-dim)'};
        var statusLabels = {open:'Open',in_progress:'In Progress',resolved:'Resolved',closed:'Closed'};
        var priColors = {normal:'var(--text-dim)',high:'#f97316',critical:'#ef4444'};
        var priLabels = {normal:'',high:'HIGH',critical:'CRITICAL'};
        var adminStatuses = {idle:'Paused',investigating:'Investigating',fixing:'Fixing',testing:'Testing'};

        tickets.forEach(function(t, idx) {
          var color = statusColors[t.status] || 'var(--text-dim)';
          var label = statusLabels[t.status] || t.status;
          var priColor = priColors[t.priority] || 'var(--text-dim)';
          var priLabel = priLabels[t.priority] || '';
          var isActive = t.status === 'open' || t.status === 'in_progress';

          // Collapsed header — always visible, clickable
          html += '<div class="ticket-drawer" id="drawer-'+t.id+'" style="border:1px solid var(--glass-border);border-radius:14px;margin-bottom:8px;background:var(--glass-bg);overflow:hidden;transition:all .3s cubic-bezier(.25,.46,.45,.94)">';

          // Header (clickable)
          html += '<div class="ticket-drawer-header" onclick="toggleDrawer('+t.id+')" style="padding:14px 18px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;transition:background .15s" onmouseenter="this.style.background=\'var(--glass-hover-bg)\'" onmouseleave="this.style.background=\'transparent\'">';
          html += '<div style="display:flex;align-items:center;gap:8px;min-width:0;flex:1">';
          html += '<span style="font-weight:700;font-size:14px;color:var(--text);white-space:nowrap">#'+t.id+'</span>';
          html += '<span style="padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700;background:'+color+'22;color:'+color+';white-space:nowrap">'+label+'</span>';
          if (priLabel) html += '<span style="padding:3px 8px;border-radius:99px;font-size:10px;font-weight:700;background:'+priColor+'18;color:'+priColor+'">'+priLabel+'</span>';
          if (t.frozen == 1) html += '<span style="padding:3px 8px;border-radius:99px;font-size:10px;font-weight:700;background:rgba(239,68,68,.12);color:var(--danger)">'+icoInline('lock')+'</span>';
          html += '<span style="font-size:12px;color:var(--text-dim);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">'+eHtml(t.page_label || t.page_route)+'</span>';
          html += '</div>';
          html += '<div style="display:flex;align-items:center;gap:8px;flex-shrink:0">';
          html += '<span style="font-size:11px;color:var(--text-dim);white-space:nowrap">'+timeAgo(t.created_at)+'</span>';
          html += '<span class="drawer-chevron" style="display:inline-flex;transition:transform .25s cubic-bezier(.25,.46,.45,.94);color:var(--text-dim)">'+icoInline('chevron-down')+'</span>';
          html += '</div></div>';

          // Expandable detail panel
          html += '<div class="ticket-drawer-body" id="body-'+t.id+'" style="max-height:0;overflow:hidden;transition:max-height .35s cubic-bezier(.25,.46,.45,.94)">';
          html += '<div style="padding:0 18px 16px;border-top:1px solid var(--glass-border)">';

          // Description
          html += '<div style="padding-top:14px;margin-bottom:12px">';
          html += '<div style="font-size:13px;color:var(--text);line-height:1.5">'+eHtml(t.description)+'</div>';
          html += '</div>';

          // Time remaining
          if (isActive && t.hours_remaining !== null) {
            var hrs = t.hours_remaining;
            var timeColor = hrs > 18 ? 'var(--success)' : hrs > 6 ? 'var(--warning)' : 'var(--danger)';
            var timeText = hrs <= 0 ? '⚠ Token expired' : hrs < 1 ? '⏰ ' + Math.round(hrs * 60) + 'min remaining' : '⏰ ' + hrs + 'h remaining';
            html += '<div style="display:flex;align-items:center;gap:6px;padding:8px 12px;border-radius:10px;background:var(--bg-elev);border:1px solid var(--border);margin-bottom:10px;font-size:12px;font-weight:600;color:'+timeColor+'">'+timeText+'</div>';
          }

          // Admin status bar
          if (t.admin_name) {
            html += '<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;padding:8px 12px;border-radius:10px;background:var(--bg-elev);border:1px solid var(--border)">';
            html += '<div style="width:28px;height:28px;border-radius:50%;background:var(--accent-soft);display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:12px;font-weight:700">'+eHtml(t.admin_first_name || '?')+'</div>';
            html += '<div style="flex:1"><div style="font-size:12px;font-weight:600">'+eHtml(t.admin_name)+'</div>';
            if (t.admin_status) {
              var asColor = t.admin_status === 'fixing' ? 'var(--success)' : t.admin_status === 'testing' ? 'var(--accent)' : t.admin_status === 'investigating' ? 'var(--info)' : 'var(--text-dim)';
              html += '<div style="font-size:11px;color:'+asColor+'">'+icoInline('pulse')+' '+eHtml(adminStatuses[t.admin_status] || t.admin_status)+'</div>';
            } else {
              html += '<div style="font-size:11px;color:var(--text-dim)">Assigned</div>';
            }
            html += '</div></div>';
          }

          // Activity timeline
          if (t.logs && t.logs.length) {
            html += '<div style="margin-bottom:10px">';
            html += '<div style="font-size:11px;font-weight:600;color:var(--text-dim);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em">Activity</div>';
            html += '<div style="position:relative;padding-left:18px">';
            var showLogs = t.logs.slice(0, 5);
            showLogs.forEach(function(log, i) {
              var dotColor = log.action === 'claimed' ? 'var(--info)' : log.action === 'resolved' ? 'var(--success)' : log.action === 'frozen_by_user' ? 'var(--danger)' : log.action.includes('status_') ? 'var(--accent)' : 'var(--text-dim)';
              html += '<div style="position:relative;padding:0 0 10px 0' + (i === showLogs.length - 1 ? ';padding-bottom:0' : '') + '">';
              html += '<div style="position:absolute;left:-18px;top:5px;width:8px;height:8px;border-radius:50%;background:'+dotColor+';border:2px solid var(--bg-elev)"></div>';
              if (i < showLogs.length - 1) html += '<div style="position:absolute;left:-15px;top:15px;width:2px;height:calc(100% - 5px);background:var(--border)"></div>';
              html += '<div style="font-size:12px;color:var(--text)">'+eHtml(log.detail || log.action)+'</div>';
              html += '<div style="font-size:10px;color:var(--text-dim);margin-top:1px">'+timeAgo(log.created_at)+'</div>';
              html += '</div>';
            });
            html += '</div></div>';
          }

          // Action buttons
          if (isActive) {
            html += '<div style="display:flex;gap:8px;justify-content:flex-end;padding-top:4px">';
            if (t.frozen == 1) {
              html += '<button class="btn btn-ghost btn-sm" onclick="event.stopPropagation();unfreezeTicket('+t.id+')" style="color:var(--warning)">'+icoInline('unlock')+' Unfreeze</button>';
            } else {
              html += '<button class="btn btn-ghost btn-sm" onclick="event.stopPropagation();freezeTicket('+t.id+')" style="color:var(--danger)">'+icoInline('lock')+' Freeze</button>';
            }
            html += '</div>';
          }

          html += '</div></div></div>';
        });
        html += '</div>';
        list.innerHTML = html;
      })
      .catch(function(e) {
        list.innerHTML = '<div style="text-align:center;color:var(--danger);padding:30px">Failed to load tickets</div>';
      });
  }
  function toggleDrawer(id) {
    var body = document.getElementById('body-'+id);
    var drawer = document.getElementById('drawer-'+id);
    var chevron = drawer.querySelector('.drawer-chevron');
    if (!body) return;
    if (body.style.maxHeight && body.style.maxHeight !== '0px') {
      body.style.maxHeight = '0px';
      if (chevron) chevron.style.transform = 'rotate(0deg)';
      drawer.style.borderColor = 'var(--glass-border)';
    } else {
      body.style.maxHeight = body.scrollHeight + 200 + 'px';
      if (chevron) chevron.style.transform = 'rotate(180deg)';
      drawer.style.borderColor = 'var(--accent)';
    }
  }
  function eHtml(s) { var d = document.createElement('div'); d.textContent = s||''; return d.innerHTML; }
  function icoInline(name) {
    var icons = {
      lock: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
      unlock: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>',
      user: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
      link: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
      pulse: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
      'chevron-down': '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>',
    };
    return icons[name] || '';
  }
  function timeAgo(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr.replace(' ','T')+'Z');
    var s = Math.floor((Date.now() - d.getTime()) / 1000);
    if (s < 60) return 'just now';
    if (s < 3600) return Math.floor(s/60) + 'm ago';
    if (s < 86400) return Math.floor(s/3600) + 'h ago';
    return Math.floor(s/86400) + 'd ago';
  }
  function freezeTicket(id) {
    var reason = prompt('Why are you freezing this ticket?\n(Admin will be blocked from proceeding)');
    if (reason === null) return;
    var fd = new FormData();
    fd.append('ticket_id', id);
    fd.append('reason', reason);
    fd.append('_csrf', '<?= e(csrf_token()) ?>');
    fetch('<?= url('index.php?r=ticket/freeze') ?>', {method:'POST', body:fd})
      .then(function(r){ return r.json(); })
      .then(function(d) { toast(d.message || 'Done', 'success'); loadTracking(); });
  }
  function unfreezeTicket(id) {
    if (!confirm('Unfreeze this ticket? Admin will be able to proceed.')) return;
    var fd = new FormData();
    fd.append('ticket_id', id);
    fd.append('_csrf', '<?= e(csrf_token()) ?>');
    fetch('<?= url('index.php?r=ticket/unfreeze') ?>', {method:'POST', body:fd})
      .then(function(r){ return r.json(); })
      .then(function(d) { toast(d.message || 'Done', 'success'); loadTracking(); });
  }
  </script>
</body>
</html>
