<?php /* Student analytics view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> <?= $u_role === 'parent' ? 'Child' : 'My' ?> Analytics — <?= e($student['first_name'] . ' ' . $student['last_name']) ?></h1>
    <p class="sub">Last 30 days of learning activity</p>
  </div>
</div>

<div class="card">
  <h3 class="card-title" style="margin-top:0"><?= icon('bolt') ?> XP earned — last 30 days</h3>
  <div id="xp-chart" ></div></canvas>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:22px;margin-top:22px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('graduation') ?> Course progress</h3>
    <?php foreach ($perCourse as $c): ?>
      <div class="list-row" style="padding:9px 0">
        <div class="flex-1"><b class="small"><?= e($c['title']) ?></b><?= $c['completed'] ? ' <span class="badge badge-success">✓ done</span>' : '' ?></div>
        <div class="progress" style="width:110px"><div style="width:<?= (float)$c['progress'] ?>%"></div></div>
        <span class="tiny faint"><?= (float)$c['progress'] ?>%</span>
      </div>
    <?php endforeach; ?>
    <?php if (!$perCourse): ?><p class="muted small">No courses enrolled.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Attendance breakdown</h3>
    <div id="att-donut" ></div></canvas>
  </div>
</div>

<div class="card" style="margin-top:22px">
  <h3 class="card-title" style="margin-top:0"><?= icon('note') ?> Recent exam results</h3>
  <?php foreach ($exams as $ex): $pct = $ex['total_points'] > 0 ? round($ex['score'] / $ex['total_points'] * 100) : 0; ?>
    <div class="list-row" style="padding:8px 0">
      <div class="flex-1"><b class="small"><?= e($ex['title']) ?></b><p class="tiny faint"><?= e($ex['course_title']) ?></p></div>
      <div class="flex gap-8" style="align-items:center">
        <div class="progress" style="width:90px"><div style="width:<?= $pct ?>%;background:<?= $pct >= 50 ? 'var(--success)' : 'var(--danger)' ?>"></div></div>
        <b class="small"><?= $pct ?>%</b>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$exams): ?><p class="muted small">No graded exams yet.</p><?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  if (window.EdunexChart) {
    const x = document.getElementById('xp-chart');
    if (x) EdunexChart.line(x, {
      labels: <?= json_encode(array_column($series, 'date')) ?>,
      values: <?= json_encode(array_column($series, 'xp')) ?>
    });
    const a = document.getElementById('att-donut');
    if (a) EdunexChart.donut(a, {
      labels: <?= json_encode(array_column($attendance, 'status')) ?>,
      values: <?= json_encode(array_column($attendance, 'n')) ?>
    });
  }
});
</script>
