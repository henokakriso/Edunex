<?php /* Admin platform-wide analytics view */
  $colors = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)', 'var(--chart-6)'];
  $totLogins   = (int)array_sum(array_column($loginSeries, 'logins'));
  $totSignups  = (int)array_sum(array_column($loginSeries, 'signups'));
  $totStudents = 0; $totTeachers = 0;
  foreach ($byRole as $r) {
    if ($r['role'] === 'student') $totStudents = (int)$r['n'];
    if ($r['role'] === 'teacher') $totTeachers = (int)$r['n'];
  }
  $totSchools  = count($bySchool);
?>
<style>
  .an-grid { display: grid; gap: 18px; }
  .an-2 { grid-template-columns: 1.6fr 1fr; }
  .an-2b { grid-template-columns: 1.3fr 1fr; margin-top: 18px; }
  @media (max-width: 980px) { .an-2, .an-2b { grid-template-columns: 1fr; } }
  .tstat-card .stat-text { display: flex; flex-direction: column; gap: 4px; min-width: 0; padding: 3px 0; }
  .tstat-card .stat-text b { font-size: 1.55rem; line-height: 1.1; letter-spacing: -.02em; color: var(--text); }
  .tstat-card .stat-text span { font-size: 12.5px; color: var(--text-dim); line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .legend-dot { width: 10px; height: 10px; border-radius: 3px; display: inline-block; flex: none; }
  .legend-row { border-radius: 9px; transition: background .12s ease; padding: 5px 8px; margin: 0 -8px; text-decoration: none; color: inherit; }
  .legend-row:hover { background: var(--bg-hover); }
  .perf-row { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border: 1px solid var(--border); border-radius: 13px; background: var(--bg-elev); margin-bottom: 10px; transition: border-color .15s ease, transform .15s ease; text-decoration: none; color: inherit; }
  .perf-row:last-child { margin-bottom: 0; }
  .perf-row:hover { border-color: color-mix(in srgb, var(--accent) 40%, var(--border)); transform: translateY(-1px); }
  .perf-ic { width: 40px; height: 40px; border-radius: 11px; flex: none; display: inline-flex; align-items: center; justify-content: center; background: var(--accent-soft); color: var(--accent); }
  .an-row { display: flex; align-items: center; gap: 12px; padding: 13px 0; border-bottom: 1px solid var(--border); }
  .an-row:last-child { border-bottom: none; padding-bottom: 2px; }
</style>

<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> Platform Analytics</h1>
    <p class="sub">System-wide overview — logins, enrollment, courses, schools</p>
  </div>
  <div class="flex gap-8">
    <?php foreach (['7' => '7 days', '30' => '30 days', '90' => '90 days'] as $v => $l): ?>
      <a class="btn btn-sm <?= $days == $v ? '' : 'btn-ghost' ?>" href="<?= url('admin/analytics&range=' . $v) ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="grid grid-4" style="margin-bottom:20px">
  <div class="card tstat-card"><span class="stat-ic"><?= icon('bolt') ?></span>
    <div class="stat-text"><b><?= number_format($totLogins) ?></b><span>Logins — <?= $days ?>d</span></div></div>
  <div class="card tstat-card"><span class="stat-ic"><?= icon('users') ?></span>
    <div class="stat-text"><b><?= $totStudents ?></b><span>Students</span></div></div>
  <div class="card tstat-card"><span class="stat-ic"><?= icon('users-badge') ?></span>
    <div class="stat-text"><b><?= $totTeachers ?></b><span>Teachers</span></div></div>
  <div class="card tstat-card"><span class="stat-ic"><?= icon('school') ?></span>
    <div class="stat-text"><b><?= $totSchools ?></b><span>Schools</span></div></div>
</div>

<div class="an-grid an-2">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('bolt') ?> Logins &amp; Sign-ups — last <?= $days ?> days</h3>
    <p class="small faint" style="margin-top:6px">Daily login and new user registration count</p>
    <div id="login-chart"></div>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('users') ?> Users by role</h3>
    <?php if ($byRole): ?>
      <div class="flex gap-16" style="align-items:center">
        <div id="role-donut" style="width:120px;flex:none"></div>
        <div class="flex-col" style="flex:1;min-width:0">
          <?php foreach ($byRole as $i => $r): ?>
            <div class="legend-row flex gap-8" style="align-items:center">
              <span class="legend-dot" style="background:<?= $colors[$i % 6] ?>"></span>
              <span class="small" style="flex:1"><?= e(ucfirst($r['role'])) ?></span>
              <b class="small" style="width:40px;text-align:right;font-variant-numeric:tabular-nums"><?= (int)$r['n'] ?></b>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <p class="muted small">No users yet.</p>
    <?php endif; ?>
  </div>
</div>

<div class="an-grid an-2b">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('courses') ?> Top courses by enrollment</h3>
    <?php if ($topCourses): ?>
      <div style="margin-top:10px">
        <?php foreach ($topCourses as $i => $c): ?>
          <div class="perf-row">
            <span class="perf-ic"><?= icon('courses') ?></span>
            <div class="flex-1" style="min-width:0">
              <b class="small"><?= e($c['title']) ?></b>
              <p class="tiny faint" style="margin-top:2px"><?= (int)$c['students'] ?> students</p>
            </div>
            <span class="badge <?= $c['status'] === 'published' ? 'badge-success' : 'badge-muted' ?>"><?= e($c['status']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted small">No courses yet.</p>
    <?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('school') ?> Users per school</h3>
    <?php if ($bySchool): ?>
      <div style="margin-top:10px">
        <?php foreach ($bySchool as $i => $s): $totalU = max(1, $totStudents + $totTeachers); $pct = round((int)$s['n'] / $totalU * 100, 1); ?>
          <div class="perf-row">
            <span class="perf-ic"><?= icon('school') ?></span>
            <div class="flex-1" style="min-width:0">
              <b class="small"><?= e($s['name']) ?></b>
              <p class="tiny faint" style="margin-top:2px"><?= (int)$s['n'] ?> users</p>
            </div>
            <span class="small faint" style="width:44px;text-align:right"><?= $pct ?>%</span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted small">No schools yet.</p>
    <?php endif; ?>
  </div>
</div>

<div class="an-grid an-2" style="margin-top:18px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Attendance — last <?= $days ?> days</h3>
    <p class="small faint" style="margin-top:6px">Daily present count across all schools</p>
    <div id="att-chart"></div>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('note') ?> Exam stats by course</h3>
    <?php if ($examStats): ?>
      <div style="margin-top:10px">
        <?php foreach ($examStats as $e): $avg = (float)($e['avg_pct'] ?? 0); ?>
          <div class="perf-row">
            <span class="perf-ic" style="background:<?= $avg >= 50 ? 'var(--success-soft);color:var(--success)' : 'var(--danger-soft);color:var(--danger)' ?>"><?= icon('note') ?></span>
            <div class="flex-1" style="min-width:0">
              <b class="small"><?= e($e['title']) ?></b>
              <p class="tiny faint" style="margin-top:2px"><?= (int)$e['attempts'] ?> attempts</p>
            </div>
            <span class="small" style="width:44px;text-align:right;font-weight:600;color:<?= $avg >= 50 ? 'var(--success)' : 'var(--danger)' ?>"><?= $avg ?>%</span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="muted small">No exam attempts yet.</p>
    <?php endif; ?>
  </div>
</div>

<?php if ($activityByDay): ?>
  <div class="card" style="margin-top:18px">
    <h3 class="card-title" style="margin-top:0"><?= icon('bolt') ?> Activity by day (last 7 recorded)</h3>
    <div style="margin-top:10px">
      <?php foreach ($activityByDay as $a): ?>
        <div class="an-row">
          <span class="small" style="width:100px"><?= e($a['d']) ?></span>
          <div class="progress flex-1" style="max-width:300px"><div style="width:<?= min(100, (int)$a['n']) ?>%"></div></div>
          <span class="tiny faint" style="width:60px;text-align:right"><?= (int)$a['n'] ?> events</span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  if (!window.EdunexChart) return;
  const lc = document.getElementById('login-chart');
  if (lc) EdunexChart.line(lc, {
    labels: <?= json_encode(array_column($loginSeries, 'date')) ?>,
    values: <?= json_encode(array_column($loginSeries, 'logins')) ?>
  });
  const dc = document.getElementById('role-donut');
  if (dc) EdunexChart.donut(dc, {
    labels: <?= json_encode(array_column($byRole, 'role')) ?>,
    values: <?= json_encode(array_map('intval', array_column($byRole, 'n'))) ?>
  });
  const ac = document.getElementById('att-chart');
  if (ac) EdunexChart.line(ac, {
    labels: <?= json_encode(array_column($attSeries, 'date')) ?>,
    values: <?= json_encode(array_column($attSeries, 'present')) ?>
  });
});
</script>
