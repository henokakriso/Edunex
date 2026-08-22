<?php /* Student analytics view — measured data + algorithmic predictions */
  $trendLabels = $trendLabels ?? [];
  $trendValues = $trendValues ?? [];
  $trendMA     = $trendMA ?? [];
  $prediction  = $prediction ?? null;
  $subjectPerf = $subjectPerf ?? [];
  $attTotal    = array_sum(array_column($attendance, 'n'));
  $attPresent  = 0;
  foreach ($attendance as $a) { if ($a['status'] === 'present') $attPresent = (int)$a['n']; }
  $attRate = $attTotal > 0 ? round($attPresent / $attTotal * 100, 1) : 0;
?>
<style>
  .perf-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
  @media (max-width: 860px) { .perf-summary { grid-template-columns: 1fr 1fr; } }
  .perf-kpi { text-align: center; padding: 18px 12px; border: 1px solid var(--border); border-radius: 13px; background: var(--bg-elev); }
  .perf-kpi b { display: block; font-size: 1.7rem; line-height: 1.1; letter-spacing: -.02em; color: var(--text); }
  .perf-kpi span { font-size: 12px; color: var(--text-dim); margin-top: 4px; display: block; }
  .subj-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); }
  .subj-row:last-child { border-bottom: none; padding-bottom: 0; }
  .trend-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
  .trend-improving { background: var(--success-soft); color: var(--success); }
  .trend-stable    { background: var(--info-soft); color: var(--info); }
  .trend-declining { background: var(--danger-soft); color: var(--danger); }
  .pred-card { border: 2px dashed var(--border); border-radius: 13px; padding: 18px; margin-top: 14px; background: var(--bg-elev); }
  .pred-card h4 { margin: 0 0 8px; font-size: 14px; color: var(--text-dim); }
  .pred-card .pred-values { display: flex; gap: 16px; margin-top: 8px; }
  .pred-card .pred-item { text-align: center; flex: 1; }
  .pred-card .pred-item b { font-size: 1.3rem; display: block; }
  .pred-card .pred-item span { font-size: 11px; color: var(--text-dim); }
  .pred-note { font-size: 11.5px; color: var(--text-dim); margin-top: 10px; font-style: italic; }
</style>

<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> <?= $u_role === 'parent' ? 'Child' : 'My' ?> Analytics — <?= e($student['first_name'] . ' ' . $student['last_name']) ?></h1>
    <p class="sub">Performance overview with measured data and trend predictions</p>
  </div>
</div>

<!-- KPI summary row -->
<div class="perf-summary">
  <div class="perf-kpi">
    <b><?= $overallAvg ?? 0 ?>%</b>
    <span><?= icon('note') ?> Exam Average</span>
  </div>
  <div class="perf-kpi">
    <b><?= $attRate ?>%</b>
    <span><?= icon('doc') ?> Attendance Rate</span>
  </div>
  <div class="perf-kpi">
    <b><?= $assignCompletion ?? 0 ?>%</b>
    <span><?= icon('file') ?> Assignment Completion</span>
  </div>
  <div class="perf-kpi">
    <b><?= count($exams) ?></b>
    <span><?= icon('graduation') ?> Exams Taken</span>
  </div>
</div>

<!-- XP trend -->
<div class="card">
  <h3 class="card-title" style="margin-top:0"><?= icon('bolt') ?> XP earned — last 30 days</h3>
  <div id="xp-chart"></div>
</div>

<!-- Performance trend + subject breakdown -->
<div class="grid" style="grid-template-columns:1.4fr 1fr;gap:22px;margin-top:22px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('trend-up') ?> Exam Score Trend</h3>
    <?php if ($trendLabels): ?>
      <p class="small faint" style="margin-top:-6px">Score % per exam over time · dashed = 3-exam moving average</p>
      <div id="trend-chart" style="margin-top:8px"></div>
      <?php if ($prediction): ?>
        <div class="pred-card">
          <h4><?= icon('brain') ?> Trend Prediction (next 3 exams)</h4>
          <div style="display:flex;align-items:center;gap:10px">
            <span class="trend-badge trend-<?= $prediction['trend'] ?>">
              <?= $prediction['trend'] === 'improving' ? '▲' : ($prediction['trend'] === 'declining' ? '▼' : '—') ?>
              <?= ucfirst($prediction['trend']) ?>
            </span>
            <span class="tiny faint"><?= $prediction['slope'] > 0 ? '+' : '' ?><?= $prediction['slope'] ?>% per exam</span>
          </div>
          <div class="pred-values">
            <?php foreach ($prediction['next3'] as $i => $p): ?>
              <div class="pred-item">
                <b><?= $p ?>%</b>
                <span>Exam <?= count($trendLabels) + $i + 1 ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <p class="pred-note">Predictions are algorithmic estimates based on past trends. Actual results may vary.</p>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="empty-state" style="padding:28px">
        <p class="small">No graded exams yet — trend data will appear here.</p>
      </div>
    <?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('books') ?> Subject Performance</h3>
    <?php if ($subjectPerf): ?>
      <div style="margin-top:6px">
        <?php foreach ($subjectPerf as $sp): $pct = (float)$sp['avg_pct']; ?>
          <div class="subj-row">
            <div class="flex-1"><b class="small"><?= e($sp['subject']) ?></b><p class="tiny faint"><?= (int)$sp['exam_count'] ?> exams</p></div>
            <div class="progress" style="width:100px"><div style="width:<?= $pct ?>%;background:<?= $pct >= 50 ? 'var(--success)' : 'var(--danger)' ?>"></div></div>
            <span class="small" style="width:44px;text-align:right;font-weight:600;color:<?= $pct >= 50 ? 'var(--success)' : 'var(--danger)' ?>"><?= $pct ?>%</span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted small" style="margin-top:10px">No subject data yet.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Course progress + attendance -->
<div class="grid" style="grid-template-columns:1fr 1fr;gap:22px;margin-top:22px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('graduation') ?> Course progress</h3>
    <?php foreach ($perCourse as $c): ?>
      <div class="list-row" style="padding:9px 0">
        <div class="flex-1"><b class="small"><?= e($c['title']) ?></b><?= $c['completed'] ? ' <span class="badge badge-success">done</span>' : '' ?></div>
        <div class="progress" style="width:110px"><div style="width:<?= (float)$c['progress'] ?>%"></div></div>
        <span class="tiny faint"><?= (float)$c['progress'] ?>%</span>
      </div>
    <?php endforeach; ?>
    <?php if (!$perCourse): ?><p class="muted small">No courses enrolled.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Attendance breakdown</h3>
    <div id="att-donut"></div>
  </div>
</div>

<!-- Recent exam results -->
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
  if (!window.EdunexChart) return;
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
  const t = document.getElementById('trend-chart');
  if (t && <?= count($trendLabels) ? 'true' : 'false' ?>) {
    EdunexChart.line(t, {
      labels: <?= json_encode($trendLabels) ?>,
      values: <?= json_encode($trendValues) ?>
    }, 'var(--chart-1)', { ma: <?= json_encode($trendMA) ?> });
  }
});
</script>
