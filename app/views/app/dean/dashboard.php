<?php /* Dean dashboard — faculty overview */
?>
<?php if (!$faculty): ?>
  <div class="page-head">
    <div><h1><?= icon('building') ?> Dean</h1><p class="sub">You have no active faculty assigned</p></div>
  </div>
  <div class="card muted" style="padding:28px">
    Your dean account is not linked to a faculty yet. Ask your director to assign you to a faculty
    under <b>Director → Faculties</b>.
  </div>
<?php else: ?>
<?php
$cards = [
  ['Departments', $stats['departments'], icon('folder'), 'dean/departments'],
  ['Teachers', $stats['teachers'], icon('users'), 'dean/teachers'],
  ['Courses', $stats['courses'], icon('courses'), 'dean/courses'],
  ['Pending approval', $stats['pending'], icon('exam'), 'dean/courses&status=draft'],
  ['Students enrolled', $stats['students'], icon('graduation'), 'dean/analytics'],
  ['Exams', $stats['exams'], icon('note'), 'dean/analytics'],
];
?>
<div class="page-head">
  <div>
    <h1><?= icon('building') ?> <?= e($faculty['name']) ?></h1>
    <p class="sub"><?= e($faculty['school_name']) ?> · <?= e($faculty['code'] ?: 'Faculty') ?></p>
  </div>
  <?php if ($stats['pending'] > 0): ?>
    <a class="btn btn-primary" href="<?= e(url('dean/courses')) ?>"><?= icon('exam') ?> Review <?= (int)$stats['pending'] ?> course(s)</a>
  <?php endif; ?>
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
  <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> Recent courses in your faculty</h3>
  <?php foreach ($recent as $r): ?>
    <div class="list-row" style="padding:8px 0">
      <div class="avatar" style="background:var(--accent-soft)"><?= icon('courses') ?></div>
      <div class="flex-1 small">
        <b><?= e($r['title']) ?></b>
        <p class="tiny faint">by <?= e($r['teacher']) ?> · <?= e(time_ago($r['created_at'])) ?></p>
      </div>
      <span class="badge <?= $r['status'] === 'published' ? 'badge-success' : ($r['status'] === 'draft' ? 'badge-warning' : '') ?>"><?= e($r['status']) ?></span>
    </div>
  <?php endforeach; ?>
  <?php if (!$recent): ?><p class="muted small">No courses yet in your faculty.</p><?php endif; ?>
</div>
<?php endif; ?>
