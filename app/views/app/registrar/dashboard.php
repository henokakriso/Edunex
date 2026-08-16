<?php /* Registrar dashboard — school academic records */
$cards = [
  ['Active Students', $stats['students'], icon('graduation'), 'registrar/enrollments'],
  ['Courses', $stats['courses'], icon('courses'), 'registrar/enrollments'],
  ['Enrollments', $stats['enrollments'], icon('users'), 'registrar/enrollments'],
  ['Exams', $stats['exams'], icon('exam'), 'registrar/transcripts'],
  ['Graded attempts', $stats['graded'], icon('check-circle'), 'registrar/transcripts'],
  ['Students on transcript', $stats['transcripts'], icon('grades'), 'registrar/transcripts'],
];
?>
<div class="page-head">
  <div>
    <h1><?= icon('grades') ?> Registrar Overview</h1>
    <p class="sub">Enrollments, transcripts &amp; academic records</p>
  </div>
  <div class="flex gap-8">
    <a class="btn btn-primary" href="<?= e(url('registrar/enrollments')) ?>"><?= icon('user-plus') ?> Enroll student</a>
    <a class="btn btn-ghost" href="<?= e(url('registrar/transcripts')) ?>"><?= icon('grades') ?> Transcripts</a>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:22px">
  <?php foreach ($cards as [$label, $val, $ic, $link]): ?>
    <a class="card stat-card" href="<?= e(url($link)) ?>" style="padding:16px 14px;text-decoration:none">
      <div class="stat-icon" style="font-size:22px"><?= $ic ?></div>
      <div>
        <div class="stat-value" style="font-size:1.5rem"><?= (int)$val ?></div>
        <div class="small faint"><?= $label ?></div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> Recent enrollments</h3>
  <?php foreach ($recent as $r): ?>
    <a class="list-row" href="<?= e(url('registrar/enrollments&q=' . urlencode($r['student']))) ?>" style="padding:8px 0;text-decoration:none">
      <div class="avatar"><?= e(mb_substr((string)$r['student'], 0, 1)) ?></div>
      <div class="flex-1 small">
        <b><?= e($r['student']) ?></b> <span class="tiny faint">(<?= e($r['student_id'] ?: '—') ?>)</span>
        <p class="tiny faint"><?= e($r['course']) ?> · <?= e(time_ago($r['enrolled_at'])) ?> · <?= (float)$r['progress'] ?>% progress</p>
      </div>
      <span class="badge <?= $r['completed'] ? 'badge-success' : '' ?>"><?= $r['completed'] ? 'COMPLETED' : 'IN PROGRESS' ?></span>
    </a>
  <?php endforeach; ?>
  <?php if (!$recent): ?><p class="muted small">No enrollments yet.</p><?php endif; ?>
</div>
