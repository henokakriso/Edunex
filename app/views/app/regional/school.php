<?php /* Regional school profile */
$cards = [
  ['Students', $stats['students'], icon('graduation')],
  ['Teachers', $stats['teachers'], icon('user')],
  ['Directors', $stats['directors'], icon('target')],
  ['Courses', $stats['courses'], icon('courses')],
  ['Enrollments', $stats['enrollments'], icon('users')],
  ['Exams', $stats['exams'], icon('exam')],
  ['Attendance records', $stats['attendance'], icon('attendance')],
  ['AI messages', $stats['ai'], icon('chip')],
];
?>
<div class="page-head">
  <div>
    <h1><?= icon('school') ?> <?= e($school['name']) ?></h1>
    <p class="sub"><?= e($school['code']) ?> · <?= e($school['type']) ?> · <?= e($school['city'] ?: '—') ?> · <span class="badge <?= $school['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($school['status']) ?></span></p>
  </div>
  <div class="flex gap-8">
    <?php if ($school['status'] === 'active'): ?>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="school_id" value="<?= (int)$school['id'] ?>">
        <button class="btn" name="action" value="suspend" onclick="return confirm('Suspend this school?')"><?= icon('pause') ?> Suspend</button>
      </form>
    <?php else: ?>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="school_id" value="<?= (int)$school['id'] ?>">
        <button class="btn btn-success" name="action" value="activate"><?= icon('check') ?> Activate</button>
      </form>
    <?php endif; ?>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="school_id" value="<?= (int)$school['id'] ?>">
      <button class="btn btn-danger" name="action" value="archive" onclick="return confirm('Archive this school?')"><?= icon('trash') ?> Archive</button>
    </form>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:22px">
  <?php foreach ($cards as [$label, $val, $ic]): ?>
    <div class="card stat-card" style="padding:16px 14px">
      <div class="stat-icon" style="font-size:22px"><?= $ic ?></div>
      <div>
        <div class="stat-value" style="font-size:1.4rem"><?= (int)$val ?></div>
        <div class="small faint"><?= $label ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> Recent staff activity</h3>
    <?php foreach ($recent as $u): ?>
      <div class="list-row" style="padding:8px 0">
        <div class="avatar"><?= e(mb_substr((string)$u['name'], 0, 1)) ?></div>
        <div class="flex-1 small">
          <b><?= e($u['name']) ?></b> <span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>" style="margin-left:6px"><?= e($u['status']) ?></span>
          <p class="tiny faint"><?= e(ucfirst($u['role'])) ?> · last login <?= $u['last_login'] ? e(time_ago($u['last_login'])) : 'never' ?></p>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$recent): ?><p class="muted small">No staff activity yet.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('link') ?> Actions</h3>
    <div class="flex-col gap-8">
      <a class="btn" href="<?= e(url('regional/directors')) ?>"><?= icon('user-plus') ?> Manage directors</a>
      <a class="btn" href="<?= e(url('regional/analytics')) ?>"><?= icon('chart-bar') ?> Regional analytics</a>
      <a class="btn" href="<?= e(url('regional/announcements')) ?>"><?= icon('megaphone') ?> Post announcement</a>
      <a class="btn" href="<?= e(url('regional/audit')) ?>"><?= icon('shield') ?> Audit log</a>
    </div>
  </div>
</div>
