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
    ['badges', 'Badges & Achievements', 'admin/badges', icon('medal')],
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
    ['grade', 'Grading', 'teacher/grade', icon('check-circle')],
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Dashboard') ?> — <?= e(APP_NAME) ?></title>
  <link rel="icon" href="<?= url('public/images/favicon.svg') ?>">
  <script>document.documentElement.dataset.theme = localStorage.getItem('edunex-theme') || '<?= e(current_theme()) ?>';</script>
  <link rel="stylesheet" href="<?= url('public/css/app.css?v=37') ?>">
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

      <?php foreach (($__nav[$__role] ?? []) as $__navItem): ?>
        <?php if (count($__navItem) === 1): ?>
          <div class="nav-section"><?= e($__navItem[0]) ?></div>
        <?php else: ?>
          <?php [, $__label, $__href, $__icon] = $__navItem; ?>
          <?php if (str_starts_with($__route, $__href)): ?>
            <?php $active = true; ?>
          <?php else: ?>
            <?php $active = $__href === $__route || ($__href === 'dashboard' && $__route === ''); ?>
          <?php endif; ?>
          <a class="nav-item <?= $active ? 'active' : '' ?>" href="<?= url('index.php?r=' . $__href) ?>">
            <span class="ico"><?= $__icon ?></span><?= e($__label) ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php $__inactive = ($__u['role'] ?? '') === 'student' && ($__u['enrollment_status'] ?? 'active') === 'inactive'; ?>
      <div class="nav-section">General</div>
      <?php if (!$__inactive): ?>
      <a class="nav-item <?= str_starts_with($__route, 'messages') ? 'active' : '' ?>" href="<?= url('index.php?r=messages') ?>">
        <span class="ico"><?= icon('chat') ?></span>Messages
      </a>
      <a class="nav-item <?= str_starts_with($__route, 'calendar') ? 'active' : '' ?>" href="<?= url('index.php?r=calendar') ?>">
        <span class="ico"><?= icon('calendar') ?></span>Calendar
      </a>
      <?php if (in_array(($__u['role'] ?? ''), ['student', 'ministry'], true)): ?>
      <a class="nav-item <?= str_starts_with($__route, 'gamification') ? 'active' : '' ?>" href="<?= url('index.php?r=gamification') ?>">
        <span class="ico"><?= icon('game') ?></span>Gamification
      </a>
      <?php endif; ?>
      <?php if (($__u['role'] ?? '') === 'ministry'): ?>
      <a class="nav-item <?= str_starts_with($__route, 'files') ? 'active' : '' ?>" href="<?= url('index.php?r=files') ?>">
        <span class="ico"><?= icon('folder') ?></span>Files
      </a>
      <?php endif; ?>
      <?php endif; ?>
      <a class="nav-item <?= str_starts_with($__route, 'notifications') ? 'active' : '' ?>" href="<?= url('index.php?r=notifications') ?>">
        <span class="ico"><?= icon('bell') ?></span>Notifications
        <?php if ($__unread > 0): ?><span class="cnt"><?= min($__unread, 99) ?></span><?php endif; ?>
      </a>
      <a class="nav-item <?= str_starts_with($__route, 'settings') ? 'active' : '' ?>" href="<?= url('index.php?r=settings/profile') ?>">
        <span class="ico"><?= icon('gear') ?></span>Settings
      </a>
      <a class="nav-item" href="<?= url('index.php?r=auth/logout') ?>">
        <span class="ico"><?= icon('logout') ?></span>Log out
      </a>
    </aside>

    <!-- ======================= MAIN ======================= -->
    <main class="main">
      <div class="topbar">
        <button class="btn btn-ghost menu-btn" style="padding:8px"><?= icon('menu') ?></button>

        <form class="search-box" method="get" action="<?= e(url('search')) ?>">
          <input type="hidden" name="r" value="search">
          <span class="input-ico"><?= icon('search') ?></span>
          <input id="global-search" type="text" name="q" placeholder="Search courses, books, people…" autocomplete="off" oninput="document.getElementById('gs-clear').style.display=this.value?'flex':'none'">
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
        <button class="topbar-icon" onclick="document.getElementById('report-issue-modal').style.display='flex'" title="Report Issue / Request Fix" style="color:var(--warning,#f59e0b)"><?= icon('wrench') ?></button>
        <?php endif; ?>

        <div class="dropdown" style="position:relative">
          <button class="topbar-icon" style="border:none;background:none">
            <img class="avatar" src="<?= e(avatar_url($__u)) ?>" alt="avatar" style="border-radius:50%">
          </button>
          <div class="dropdown-menu">
            <div class="dropdown-head"><?= e(full_name($__u)) ?></div>
            <div class="dropdown-head" style="text-transform:none;font-weight:500;padding-top:0">
              <?= e($__u['student_id'] ?? $__u['email']) ?> · Level <?= (int)$__u['level'] ?> · <?= (int)$__u['xp'] ?> XP
            </div>
            <a class="dropdown-item" href="<?= url('index.php?r=settings/profile') ?>"><?= icon('gear') ?> Profile settings</a>
            <a class="dropdown-item" href="<?= url('index.php?r=settings/security') ?>"><?= icon('lock') ?> Security</a>
            <div class="dropdown-divider"></div>
            <form method="post" action="<?= url('index.php?r=admin/toggle-demo') ?>" style="margin:0">
              <?= csrf_field() ?>
              <button type="submit" class="dropdown-item" style="gap:8px;cursor:pointer;border:none;background:none;width:100%;text-align:left;font:inherit;color:inherit">
                <?= is_demo_mode() ? icon('eye-off') : icon('eye') ?>
                <?= is_demo_mode() ? 'Switch to Normal' : 'Switch to Demo' ?>
                <span style="margin-left:auto;font-size:10px;padding:2px 6px;border-radius:4px;<?= is_demo_mode() ? 'background:var(--warning,#f59e0b);color:#000' : 'background:var(--success,#22c55e);color:#fff' ?>"><?= is_demo_mode() ? 'DEMO' : 'NORMAL' ?></span>
              </button>
            </form>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item danger" href="<?= url('index.php?r=auth/logout') ?>"><?= icon('logout') ?> Log out</a>
          </div>
        </div>
      </div>

      <?php include BASE_PATH . '/app/views/partials/flashes.php'; ?>

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
  <div id="report-issue-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="max-width:480px;width:90%;padding:1.5rem">
      <h3 style="margin:0 0 1rem">Report Issue / Request Fix</h3>
      <p style="color:var(--muted);font-size:0.85rem;margin-bottom:1rem">
        This creates a secure fix ticket. The IT admin can ONLY access this page — no other data or settings.
      </p>
      <form method="POST" action="<?= url('index.php?r=ticket/create') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="page_route" id="report-page-route" value="<?= e($__route) ?>">
        <input type="hidden" name="page_label" id="report-page-label" value="<?= e($title ?? '') ?>">
        <div class="form-group">
          <label>Describe the issue</label>
          <textarea name="description" class="form-control" rows="3" placeholder="What's wrong or what needs fixing?" required></textarea>
        </div>
        <div class="flex gap-10">
          <button class="btn btn-primary" type="submit">Create Fix Ticket</button>
          <button type="button" class="btn btn-ghost" onclick="document.getElementById('report-issue-modal').style.display='none'">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Fix Ticket Result Modal -->
  <?php if (!empty($_SESSION['fix_ticket'])): ?>
  <?php $ft = $_SESSION['fix_ticket']; unset($_SESSION['fix_ticket']); ?>
  <div id="fix-ticket-result" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);align-items:center;justify-content:center">
    <div class="card" style="max-width:480px;width:90%;padding:1.5rem">
      <h3 style="margin:0 0 0.5rem">Fix Ticket #<?= e($ft['id']) ?> Created</h3>
      <p style="color:var(--muted);font-size:0.85rem;margin-bottom:1rem">Share this token with your IT admin:</p>
      <div style="background:var(--bg-elevated,#1e293b);padding:1rem;border-radius:8px;font-family:monospace;font-size:0.9rem;word-break:break-all;margin-bottom:1rem;border:1px solid var(--border,#334155)">
        <?= e($ft['token']) ?>
      </div>
      <p style="font-size:0.85rem;color:var(--muted)">Page: <b><?= e($ft['page']) ?></b></p>
      <button class="btn btn-primary" onclick="navigator.clipboard.writeText(this.dataset.token).then(()=>this.textContent='Copied!')" data-token="<?= e($ft['token']) ?>">Copy Token</button>
      <button class="btn btn-ghost" onclick="document.getElementById('fix-ticket-result').style.display='none'">Close</button>
    </div>
  </div>
  <script>setTimeout(()=>{var m=document.getElementById('fix-ticket-result');if(m)m.style.display='flex';},100);</script>
  <?php endif; ?>
</body>
</html>
