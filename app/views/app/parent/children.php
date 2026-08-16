<?php /* Parent children view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('user') ?> My Children</h1>
    <p class="sub"><?= count($summaries) ?> child<?= count($summaries) === 1 ? '' : 'ren' ?> linked</p>
  </div>
</div>

<div class="flex-col gap-22">
  <?php foreach ($summaries as $s): $c = $s['child']; ?>
    <div class="card">
      <div class="flex-between" style="flex-wrap:wrap;gap:12px">
        <div class="flex gap-12" style="align-items:center">
          <div class="avatar" style="width:44px;height:44px;font-size:1rem"><?= e(mb_substr($c['first_name'], 0, 1) . mb_substr($c['last_name'], 0, 1)) ?></div>
          <div>
            <b><?= e($c['first_name'] . ' ' . $c['last_name']) ?></b>
            <p class="tiny faint"><?= e($c['student_id']) ?> · <?= e($c['email']) ?></p>
          </div>
        </div>
        <div class="flex gap-8">
          <a class="btn btn-sm" href="<?= e(url('parent/reports&child=' . $c['id'])) ?>"><?= icon('trend-up') ?> Report</a>
        </div>
      </div>
      <div class="grid4" style="margin-top:14px">
        <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px"><div class="stat-value"><?= (int)$s['courses'] ?></div><div class="tiny faint">Courses</div></div>
        <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px"><div class="stat-value"><?= (int)$s['completed_courses'] ?></div><div class="tiny faint">Completed</div></div>
        <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px"><div class="stat-value"><?= (int)$s['attendance']['rate'] ?>%</div><div class="tiny faint">Attendance</div></div>
        <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px"><div class="stat-value"><?= icon('bolt') ?> <?= (int)$s['xp'] ?></div><div class="tiny faint">XP</div></div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$summaries): ?><div class="alert alert-info">No children linked yet.</div><?php endif; ?>
</div>
