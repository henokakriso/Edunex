<?php /* National Education Command & Oversight Dashboard */
$adminActive = $totAdmins > 0 ? round($activeAdmins/$totAdmins*100,1) : 0;
$schoolActive = $totSchools > 0 ? round($activeSchools/$totSchools*100,1) : 0;
$completionRate = $enrollments > 0 ? round($completions/$enrollments*100,1) : 0;
?>
<style>
.cmd-section{margin-bottom:28px}
.cmd-section .section-head{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.cmd-section .section-head h2{margin:0;font-size:1rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text)}
.cmd-section .section-head .section-line{flex:1;height:1px;background:var(--border)}
/* KPI Cards */
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
@media(max-width:980px){.kpi-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.kpi-grid{grid-template-columns:1fr}}
.kpi{padding:18px 20px;border-radius:14px;background:var(--bg-elev);border:1px solid var(--border);transition:border-color .15s,transform .15s}
.kpi:hover{border-color:color-mix(in srgb,var(--accent) 30%,var(--border));transform:translateY(-1px)}
.kpi .kpi-ic{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.kpi .kpi-top{display:flex;align-items:center;gap:10px;margin-bottom:6px}
.kpi .kpi-top .kpi-label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);font-weight:600}
.kpi .kpi-val{font-size:1.55rem;font-weight:800;color:var(--text);line-height:1.1;letter-spacing:-.02em}
.kpi .kpi-sub{font-size:11.5px;color:var(--text-secondary);margin-top:4px}
.kpi .kpi-sub .up{color:var(--success)}
.kpi .kpi-sub .down{color:var(--danger)}
/* Alert cards */
.alert-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px}
.alert-item{display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;border:1px solid var(--border);background:var(--bg-elev);cursor:pointer;transition:all .15s}
.alert-item:hover{border-color:color-mix(in srgb,var(--accent) 30%,var(--border));transform:translateY(-1px)}
.alert-item .a-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.alert-item .a-dot.red{background:#ef4444}.alert-item .a-dot.orange{background:#f59e0b}.alert-item .a-dot.yellow{background:#eab308}
.alert-item .a-text{font-size:12.5px;color:var(--text);flex:1}
.alert-item .a-count{font-size:13px;font-weight:700;color:var(--text)}
/* Performance table */
.perf-table{width:100%;border-collapse:separate;border-spacing:0;font-size:12.5px}
.perf-table thead th{text-align:left;padding:10px 14px;font-weight:600;color:var(--text-secondary);font-size:11px;text-transform:uppercase;letter-spacing:.4px;border-bottom:2px solid var(--border);white-space:nowrap}
.perf-table tbody td{padding:10px 14px;border-bottom:1px solid var(--border);color:var(--text)}
.perf-table tbody tr{transition:background .12s}
.perf-table tbody tr:hover{background:rgba(99,102,241,.03)}
.perf-table tbody tr:nth-child(even){background:rgba(99,102,241,.02)}
/* Workload bar */
.wl-bar{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.wl-bar .wl-name{width:120px;font-size:12px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wl-bar .wl-track{flex:1;height:8px;border-radius:4px;background:var(--border);overflow:hidden}
.wl-bar .wl-fill{height:100%;border-radius:4px;transition:width .4s ease}
.wl-bar .wl-pct{width:40px;font-size:12px;font-weight:600;text-align:right;font-variant-numeric:tabular-nums}
/* Score breakdown */
.score-row{display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid rgba(0,0,0,.04)}
.score-row:last-child{border-bottom:none}
.score-row .sr-label{flex:1;font-size:12.5px;color:var(--text)}
.score-row .sr-weight{font-size:11px;color:var(--text-secondary);width:40px;text-align:right}
.score-row .sr-val{font-size:13px;font-weight:700;width:30px;text-align:right;font-variant-numeric:tabular-nums}
/* Geographic */
.geo-row{display:flex;align-items:center;gap:14px;padding:12px 16px;border-radius:10px;border:1px solid var(--border);margin-bottom:6px;background:var(--bg-elev);transition:all .15s}
.geo-row:hover{border-color:color-mix(in srgb,var(--accent) 30%,var(--border))}
.geo-row .geo-name{font-size:13px;font-weight:600;color:var(--text);width:120px}
.geo-row .geo-stat{font-size:12px;color:var(--text-secondary);width:80px;text-align:right;font-variant-numeric:tabular-nums}
</style>

<!-- ═══════════════ PAGE HEAD ═══════════════ -->
<div class="page-head">
  <div>
    <h1><?= icon('shield') ?> National Education Command Center</h1>
    <p class="sub">Oversight dashboard — admin performance, school compliance, national metrics</p>
  </div>
</div>

<!-- ═══════════════ 1. TOP KPI CARDS ═══════════════ -->
<div class="cmd-section">
  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:var(--accent-soft);color:var(--accent)"><?= icon('shield') ?></div>
        <span class="kpi-label">Admins</span>
      </div>
      <div class="kpi-val"><?= number_format($totAdmins) ?></div>
      <div class="kpi-sub"><span class="up"><?= $activeAdmins ?> active</span> · <?= $adminActive ?>% utilization</div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:var(--success-soft);color:var(--success)"><?= icon('school') ?></div>
        <span class="kpi-label">Schools</span>
      </div>
      <div class="kpi-val"><?= number_format($totSchools) ?></div>
      <div class="kpi-sub"><span class="up"><?= number_format($activeSchools) ?> active</span> · <?= number_format($pendingVerif) ?> pending verification</div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:var(--warning-soft);color:var(--warning)"><?= icon('users') ?></div>
        <span class="kpi-label">Students</span>
      </div>
      <div class="kpi-val"><?= number_format($totStudents) ?></div>
      <div class="kpi-sub"><span class="up">+<?= number_format($newStudents) ?></span> new (30d)</div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:rgba(168,85,247,.1);color:#a855f7"><?= icon('users-badge') ?></div>
        <span class="kpi-label">Teachers</span>
      </div>
      <div class="kpi-val"><?= number_format($totTeachers) ?></div>
      <div class="kpi-sub"><span class="up"><?= $activeTeachers ?> active</span></div>
    </div>
  </div>
</div>

<!-- ═══════════════ 2. SECOND KPI ROW ═══════════════ -->
<div class="cmd-section">
  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:rgba(16,185,129,.1);color:#10b981"><?= icon('bolt') ?></div>
        <span class="kpi-label">Active Sessions (30d)</span>
      </div>
      <div class="kpi-val"><?= number_format($logins30) ?></div>
      <div class="kpi-sub"><?= number_format($failedLogins) ?> failed attempts</div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:rgba(99,102,241,.1);color:var(--accent)"><?= icon('courses') ?></div>
        <span class="kpi-label">Courses</span>
      </div>
      <div class="kpi-val"><?= number_format($totCourses) ?></div>
      <div class="kpi-sub"><?= $activeCourses ?> published</div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:rgba(245,158,11,.1);color:#f59e0b"><?= icon('note') ?></div>
        <span class="kpi-label">Avg Progress</span>
      </div>
      <div class="kpi-val"><?= $avgProgress ?>%</div>
      <div class="kpi-sub"><?= $completionRate ?>% completion rate</div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:rgba(239,68,68,.1);color:#ef4444"><?= icon('alert') ?></div>
        <span class="kpi-label">Issues</span>
      </div>
      <div class="kpi-val"><?= number_format($inactiveSchools) ?></div>
      <div class="kpi-sub">inactive schools · <?= $passRate ?>% pass rate</div>
    </div>
  </div>
</div>

<!-- ═══════════════ 3. ALERTS ═══════════════ -->
<div class="cmd-section">
  <div class="section-head">
    <h2><?= icon('alert') ?> Attention Required</h2>
    <div class="section-line"></div>
  </div>
  <div class="alert-grid">
    <?php if ($pendingVerif > 0): ?>
    <div class="alert-item">
      <div class="a-dot orange"></div>
      <div class="a-text"><?= $pendingVerif ?> schools pending verification</div>
      <div class="a-count"><?= $pendingVerif ?></div>
    </div>
    <?php endif; ?>
    <?php if ($inactiveSchools > 0): ?>
    <div class="alert-item">
      <div class="a-dot red"></div>
      <div class="a-text"><?= $inactiveSchools ?> schools inactive</div>
      <div class="a-count"><?= $inactiveSchools ?></div>
    </div>
    <?php endif; ?>
    <?php if ($failedLogins > 10): ?>
    <div class="alert-item">
      <div class="a-dot red"></div>
      <div class="a-text"><?= number_format($failedLogins) ?> failed login attempts (30d)</div>
      <div class="a-count"><?= $failedLogins ?></div>
    </div>
    <?php endif; ?>
    <div class="alert-item">
      <div class="a-dot yellow"></div>
      <div class="a-text"><?= number_format($recentTasks) ?> activity events (30d)</div>
      <div class="a-count"><?= number_format($recentTasks) ?></div>
    </div>
  </div>
</div>

<!-- ═══════════════ 4. ADMIN PERFORMANCE ═══════════════ -->
<div class="cmd-section">
  <div class="section-head">
    <h2><?= icon('users-badge') ?> Admin Performance</h2>
    <div class="section-line"></div>
  </div>
  <div class="card" style="overflow-x:auto">
    <?php if ($admins): ?>
    <table class="perf-table">
      <thead><tr>
        <th>Admin</th><th>Role</th><th>Region</th><th>Schools</th><th>Status</th><th>Last Login</th>
      </tr></thead>
      <tbody>
        <?php foreach ($admins as $a): ?>
        <tr>
          <td style="font-weight:600"><?= e($a['name']) ?></td>
          <td><span class="badge <?= $a['role']==='regional'?'badge-danger':'badge-accent' ?>"><?= e(ucfirst($a['role'])) ?></span></td>
          <td><?= e($a['region'] ?? '—') ?></td>
          <td style="font-variant-numeric:tabular-nums"><?= (int)$a['schools_assigned'] ?></td>
          <td><span class="badge <?= $a['status']==='active'?'badge-success':'badge-muted' ?>"><?= e(ucfirst($a['status'])) ?></span></td>
          <td class="small faint"><?= e($a['last_login'] ?? 'Never') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p class="muted small" style="padding:20px">No admin data yet.</p>
    <?php endif; ?>
  </div>
</div>

<!-- ═══════════════ 5. ADMIN WORKLOAD ═══════════════ -->
<div class="cmd-section">
  <div class="section-head">
    <h2><?= icon('chart-bar') ?> Admin Workload Distribution</h2>
    <div class="section-line"></div>
  </div>
  <div class="card">
    <?php $maxSchools = max(1, ...array_column($admins, 'schools_assigned')); ?>
    <?php foreach (array_slice($admins, 0, 8) as $a): ?>
      <?php $pct = round((int)$a['schools_assigned'] / $maxSchools * 100); ?>
      <div class="wl-bar">
        <span class="wl-name"><?= e($a['name']) ?></span>
        <div class="wl-track"><div class="wl-fill" style="width:<?= $pct ?>%;background:<?= $pct > 85 ? '#ef4444' : ($pct > 60 ? '#f59e0b' : 'var(--accent)') ?>;opacity:.7"></div></div>
        <span class="wl-pct" style="color:<?= $pct > 85 ? '#ef4444' : ($pct > 60 ? '#f59e0b' : 'var(--accent)') ?>"><?= (int)$a['schools_assigned'] ?></span>
      </div>
    <?php endforeach; ?>
    <?php if (!$admins): ?><p class="muted small">No admin workload data.</p><?php endif; ?>
  </div>
</div>

<!-- ═══════════════ 6. SCHOOL OVERSIGHT ═══════════════ -->
<div class="cmd-section">
  <div class="section-head">
    <h2><?= icon('school') ?> School Oversight</h2>
    <div class="section-line"></div>
  </div>
  <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:14px">
    <div class="kpi" style="padding:14px 16px"><div class="kpi-val" style="font-size:1.2rem"><?= number_format($totSchools) ?></div><div class="kpi-sub">Total Schools</div></div>
    <div class="kpi" style="padding:14px 16px"><div class="kpi-val" style="font-size:1.2rem;color:var(--success)"><?= number_format($activeSchools) ?></div><div class="kpi-sub">Active</div></div>
    <div class="kpi" style="padding:14px 16px"><div class="kpi-val" style="font-size:1.2rem;color:var(--warning)"><?= number_format($pendingVerif) ?></div><div class="kpi-sub">Pending Verification</div></div>
    <div class="kpi" style="padding:14px 16px"><div class="kpi-val" style="font-size:1.2rem;color:var(--danger)"><?= number_format($inactiveSchools) ?></div><div class="kpi-sub">Inactive</div></div>
  </div>
  <?php if ($regions): ?>
  <div class="card" style="overflow-x:auto">
    <table class="perf-table">
      <thead><tr><th>Region</th><th style="text-align:right">Schools</th><th style="text-align:right">Active</th><th style="text-align:right">Students</th><th style="text-align:right">Teachers</th></tr></thead>
      <tbody>
        <?php foreach ($regions as $rg): ?>
        <tr>
          <td style="font-weight:600"><?= e($rg['region']) ?></td>
          <td style="text-align:right;font-variant-numeric:tabular-nums"><?= number_format($rg['schools']) ?></td>
          <td style="text-align:right;font-variant-numeric:tabular-nums"><?= number_format($rg['active_schools']) ?></td>
          <td style="text-align:right;font-variant-numeric:tabular-nums"><?= number_format($rg['students']) ?></td>
          <td style="text-align:right;font-variant-numeric:tabular-nums"><?= number_format($rg['teachers']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- ═══════════════ 7. NATIONAL EDUCATION METRICS ═══════════════ -->
<div class="cmd-section">
  <div class="section-head">
    <h2><?= icon('graduation') ?> National Education Metrics</h2>
    <div class="section-line"></div>
  </div>
  <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="kpi">
      <div class="kpi-top"><div class="kpi-ic" style="background:var(--accent-soft);color:var(--accent)"><?= icon('users') ?></div><span class="kpi-label">Enrollment</span></div>
      <div class="kpi-val"><?= number_format($totStudents) ?></div>
      <div class="kpi-sub"><?= $enrollments ?> course enrollments</div>
    </div>
    <div class="kpi">
      <div class="kpi-top"><div class="kpi-ic" style="background:var(--success-soft);color:var(--success)"><?= icon('users-badge') ?></div><span class="kpi-label">Teachers</span></div>
      <div class="kpi-val"><?= number_format($totTeachers) ?></div>
      <div class="kpi-sub"><?= $activeTeachers ?> active</div>
    </div>
    <div class="kpi">
      <div class="kpi-top"><div class="kpi-ic" style="background:rgba(99,102,241,.1);color:var(--accent)"><?= icon('courses') ?></div><span class="kpi-label">Courses</span></div>
      <div class="kpi-val"><?= number_format($totCourses) ?></div>
      <div class="kpi-sub"><?= $activeCourses ?> published</div>
    </div>
    <div class="kpi">
      <div class="kpi-top"><div class="kpi-ic" style="background:rgba(168,85,247,.1);color:#a855f7"><?= icon('note') ?></div><span class="kpi-label">Pass Rate</span></div>
      <div class="kpi-val"><?= $passRate ?>%</div>
      <div class="kpi-sub"><?= $avgProgress ?>% avg progress</div>
    </div>
  </div>
</div>

<!-- ═══════════════ 8. GEOGRAPHIC ANALYTICS ═══════════════ -->
<?php if ($regions): ?>
<div class="cmd-section">
  <div class="section-head">
    <h2><?= icon('map') ?> Geographic Analytics</h2>
    <div class="section-line"></div>
  </div>
  <div class="card">
    <?php foreach ($regions as $rg): ?>
    <div class="geo-row">
      <span class="geo-name"><?= e($rg['region']) ?></span>
      <span class="geo-stat"><?= number_format($rg['schools']) ?> schools</span>
      <span class="geo-stat"><?= number_format($rg['students']) ?> students</span>
      <span class="geo-stat"><?= number_format($rg['teachers']) ?> teachers</span>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ═══════════════ 9. SECURITY & AUDIT ═══════════════ -->
<div class="cmd-section">
  <div class="section-head">
    <h2><?= icon('shield') ?> Security &amp; Audit</h2>
    <div class="section-line"></div>
  </div>
  <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="kpi">
      <div class="kpi-top"><div class="kpi-ic" style="background:rgba(16,185,129,.1);color:#10b981"><?= icon('bolt') ?></div><span class="kpi-label">Logins (30d)</span></div>
      <div class="kpi-val"><?= number_format($logins30) ?></div>
      <div class="kpi-sub">successful sessions</div>
    </div>
    <div class="kpi">
      <div class="kpi-top"><div class="kpi-ic" style="background:rgba(239,68,68,.1);color:#ef4444"><?= icon('alert') ?></div><span class="kpi-label">Failed Logins</span></div>
      <div class="kpi-val"><?= number_format($failedLogins) ?></div>
      <div class="kpi-sub">last 30 days</div>
    </div>
    <div class="kpi">
      <div class="kpi-top"><div class="kpi-ic" style="background:rgba(245,158,11,.1);color:#f59e0b"><?= icon('doc') ?></div><span class="kpi-label">Activity Events</span></div>
      <div class="kpi-val"><?= number_format($totalTasks) ?></div>
      <div class="kpi-sub"><?= number_format($recentTasks) ?> in 30d</div>
    </div>
  </div>
</div>

<!-- ═══════════════ 10. LOGIN ACTIVITY CHART ═══════════════ -->
<div class="cmd-section">
  <div class="section-head">
    <h2><?= icon('chart-bar') ?> Login Activity — Last 30 Days</h2>
    <div class="section-line"></div>
  </div>
  <div class="card">
    <div id="login-chart"></div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  if (!window.EdunexChart) return;
  const lc = document.getElementById('login-chart');
  if (lc) EdunexChart.line(lc, {
    labels: <?= json_encode(array_column($loginSeries, 'date')) ?>,
    values: <?= json_encode(array_column($loginSeries, 'logins')) ?>
  });
});
</script>
