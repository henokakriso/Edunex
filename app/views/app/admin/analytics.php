<?php /* Admin analytics view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> Platform Analytics</h1>
    <p class="sub">Last <?= (int)$days ?> days</p>
  </div>
  <div class="flex gap-8">
    <?php foreach ([7, 14, 30, 90] as $r): ?>
      <a class="btn btn-sm <?= $days === $r ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('admin/analytics&range=' . $r)) ?>"><?= $r ?>d</a>
    <?php endforeach; ?>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.5fr 1fr;gap:22px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('trend-up') ?> Activity — last <?= (int)$days ?> days</h3>
    <div id="series-chart" ></div></canvas>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('users') ?> Users by role</h3>
    <div id="role-donut" ></div></canvas>
  </div>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:22px;margin-top:22px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('graduation') ?> Top courses by enrollment</h3>
    <?php foreach ($topCourses as $c): ?>
      <div class="list-row" style="padding:8px 0">
        <span class="small flex-1"><?= e($c['title']) ?></span>
        <span class="badge badge-muted"><?= e($c['status']) ?></span>
        <b class="small"><?= (int)$c['students'] ?></b>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('bolt') ?> Recent activity volume</h3>
    <?php foreach ($activityByDay as $a): ?>
      <div class="list-row" style="padding:6px 0">
        <span class="small flex-1"><?= e(date('M j', strtotime($a['d']))) ?></span>
        <span class="small"><?= (int)$a['n'] ?> actions</span>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:22px;margin-top:22px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Attendance (present) — last <?= (int)$days ?> days</h3>
    <div id="att-chart" ></div></canvas>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('school') ?> Users by school</h3>
    <div id="school-chart" ></div></canvas>
  </div>
</div>

<div class="card" style="margin-top:22px">
  <h3 class="card-title" style="margin-top:0"><?= icon('note') ?> Exam performance by course (avg score %)</h3>
  <?php foreach ($examStats as $ex): ?>
    <div class="list-row" style="padding:7px 0">
      <span class="small flex-1"><?= e($ex['title']) ?></span>
      <span class="tiny faint"><?= (int)$ex['attempts'] ?> attempts</span>
      <div class="progress" style="width:160px;height:8px"><div class="progress-bar" style="width:<?= min(100, (float)$ex['avg_pct']) ?>%;background:var(--chart-2)"></div></div>
      <b class="small" style="width:52px;text-align:right"><?= e($ex['avg_pct'] !== null ? $ex['avg_pct'] . '%' : '—') ?></b>
    </div>
  <?php endforeach; ?>
  <?php if (!$examStats): ?><p class="muted small">No exam attempts yet.</p><?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  if (window.EdunexChart) {
    const s = document.getElementById('series-chart');
    if (s) EdunexChart.multi(s, {
      labels: <?= json_encode(array_column($loginSeries, 'date')) ?>,
      series: [
        { name: 'Logins', values: <?= json_encode(array_column($loginSeries, 'logins')) ?>, color: '#3ecf8e' },
        { name: 'Signups', values: <?= json_encode(array_column($loginSeries, 'signups')) ?>, color: '#7c5cff' },
      ]
    });
    const d = document.getElementById('role-donut');
    if (d) EdunexChart.donut(d, {
      labels: <?= json_encode(array_column($byRole, 'role')) ?>,
      values: <?= json_encode(array_column($byRole, 'n')) ?>
    });
    const a = document.getElementById('att-chart');
    if (a) EdunexChart.line(a, { labels: <?= json_encode(array_column($attSeries, 'date')) ?>, values: <?= json_encode(array_column($attSeries, 'present')) ?> }, 'var(--chart-2)');
    const sc = document.getElementById('school-chart');
    if (sc) EdunexChart.bar(sc, { labels: <?= json_encode(array_column($bySchool, 'name')) ?>, values: <?= json_encode(array_column($bySchool, 'users')) ?> }, 'var(--chart-4)');
  }
});
</script>
