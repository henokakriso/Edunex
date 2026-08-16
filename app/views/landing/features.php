<?php $title = 'Features'; ?>
<div class="landing-hero" style="padding:60px 0 40px">
  <h1>Every feature, <span class="grad">crafted for learning</span></h1>
  <p class="lead">From the classroom to the exam hall to the report card — Edunex covers the full journey.</p>
</div>
<?php
$groups = [
  [icon('graduation'), 'Learning', [
    ['Courses & lessons', 'Notes, PDFs, videos and slides organized into modules.'],
    ['Resume where you left off', 'Progress tracking remembers your exact position.'],
    ['Bookmarks', 'Save any lesson and jump back instantly.'],
    ['Discussions', 'Course forums with answers, reactions and moderation.'],
  ]],
  [icon('note'), 'Examinations', [
    ['10 question types', 'MCQ, true/false, essay, fill-in-blanks, coding, matching, ordering, image, audio, video.'],
    ['Timers & auto-save', 'Countdown timers with automatic draft saving.'],
    ['Flag & review', 'Flag questions, navigate freely, review before submitting.'],
    ['Smart grading', 'Instant auto-grading plus manual and AI-assisted review.'],
  ]],
  [icon('doc'), 'Academic management', [
    ['Attendance', 'Present, absent, late, excused — with weekly and monthly graphs.'],
    ['Grades & analytics', 'Track trends, predict performance, find weak topics.'],
    ['Reports', 'Attendance, grade, teacher, department and financial reports.'],
    ['Transfers', 'School-to-school transfer using the student special ID.'],
  ]],
  [icon('robot'), 'AI capabilities', [
    ['AI Tutor chat', 'Natural language explanations with conversation memory.'],
    ['Study assistant', 'Summaries, quizzes, flashcards, study plans, readiness scores.'],
    ['Amharic translation', 'Built-in educational phrase dictionary.'],
    ['AI feedback', 'Instant feedback on assignments and essay answers.'],
  ]],
  [icon('university'), 'Library & resources', [
    ['Digital library', 'Books, notes, past exams, tutorials — searchable.'],
    ['Downloads & favorites', 'Offline-friendly downloads and personal favorites.'],
    ['File manager', 'Uploads with version history and previews.'],
  ]],
  [icon('lock'), 'Security & trust', [
    ['Role-based access', 'Admin, teacher, student, parent, guest — each scoped.'],
    ['2FA & OTP', 'Two-factor authentication and OTP verification.'],
    ['Audit logs', 'Every sensitive action is recorded.'],
    ['Verified certificates', 'QR-coded certificates with a public verification page.'],
  ]],
];
foreach ($groups as [$ico, $title, $items]): ?>
  <div style="margin-bottom:52px">
    <h2 style="margin-bottom:18px"><?= $ico ?> <?= e($title) ?></h2>
    <div class="grid-4">
      <?php foreach ($items as [$t, $d]): ?>
        <div class="card card-hover">
          <b style="font-size:13.5px"><?= e($t) ?></b>
          <p class="muted small" style="margin-top:6px"><?= e($d) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>
