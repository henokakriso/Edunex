<?php /* National Education Command & Oversight Dashboard */
$adminActive = $totAdmins > 0 ? round($activeAdmins/$totAdmins*100,1) : 0;
$schoolActive = $totSchools > 0 ? round($activeSchools/$totSchools*100,1) : 0;
$completionRate = $enrollments > 0 ? round($completions/$enrollments*100,1) : 0;
?>
<style>
.cmd-section{margin-bottom:28px}
.cmd-section .section-head{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.cmd-section .section-head h2{margin:0;font-size:1rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text)}
.cmd-section .section-head .section-line{flex:1;height:1px;background:var(--glass-border)}
/* KPI Cards — Apple Glass */
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
@media(max-width:980px){.kpi-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.kpi-grid{grid-template-columns:1fr}}
.kpi{padding:18px 20px;border-radius:14px;background:var(--glass-bg);border:1px solid var(--glass-border);backdrop-filter:blur(20px) saturate(150%);-webkit-backdrop-filter:blur(20px) saturate(150%);transition:all .25s cubic-bezier(.4,0,.2,1);position:relative;overflow:hidden}
.kpi::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.06) 0%,transparent 40%,rgba(255,255,255,.02) 100%);pointer-events:none;transition:background .3s ease}
.kpi:hover{background:var(--glass-hover-bg);border-color:var(--glass-hover-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.45),inset 0 -1px 0 rgba(255,255,255,.06),inset 1px 0 0 rgba(255,255,255,.2),inset -1px 0 0 rgba(255,255,255,.06),var(--glass-hover-shadow)}
.kpi:hover::before{background:linear-gradient(135deg,rgba(255,255,255,.12) 0%,rgba(255,255,255,.03) 50%,rgba(255,255,255,.06) 100%)}
.kpi:focus-visible{outline:none;border-color:rgba(255,255,255,.25);box-shadow:0 0 0 2px var(--bg),0 0 0 4px rgba(255,255,255,.12),inset 0 1px 0 rgba(255,255,255,.4),0 0 16px rgba(255,255,255,.04)}
.kpi .kpi-ic{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.kpi .kpi-top{display:flex;align-items:center;gap:10px;margin-bottom:6px}
.kpi .kpi-top .kpi-label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);font-weight:600}
.kpi .kpi-val{font-size:1.55rem;font-weight:800;color:var(--text);line-height:1.1;letter-spacing:-.02em}
.kpi .kpi-sub{font-size:11.5px;color:var(--text-secondary);margin-top:4px}
.kpi .kpi-sub .up{color:var(--success)}
.kpi .kpi-sub .down{color:var(--danger)}
/* Alert cards — Apple Glass */
.alert-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px}
.alert-item{display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;border:1px solid var(--glass-border);background:var(--glass-bg);backdrop-filter:blur(20px) saturate(150%);-webkit-backdrop-filter:blur(20px) saturate(150%);cursor:pointer;transition:all .25s cubic-bezier(.4,0,.2,1);position:relative;overflow:hidden}
.alert-item::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.06) 0%,transparent 40%,rgba(255,255,255,.02) 100%);pointer-events:none;transition:background .3s ease}
.alert-item:hover{background:var(--glass-hover-bg);border-color:var(--glass-hover-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.45),inset 0 -1px 0 rgba(255,255,255,.06),inset 1px 0 0 rgba(255,255,255,.2),inset -1px 0 0 rgba(255,255,255,.06),var(--glass-hover-shadow)}
.alert-item:hover::before{background:linear-gradient(135deg,rgba(255,255,255,.12) 0%,rgba(255,255,255,.03) 50%,rgba(255,255,255,.06) 100%)}
.alert-item:focus-visible{outline:none;border-color:rgba(255,255,255,.25);box-shadow:0 0 0 2px var(--bg),0 0 0 4px rgba(255,255,255,.12),inset 0 1px 0 rgba(255,255,255,.4),0 0 16px rgba(255,255,255,.04)}
.alert-item .a-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.alert-item .a-dot.red{background:#ef4444}.alert-item .a-dot.orange{background:#f59e0b}.alert-item .a-dot.yellow{background:#eab308}
.alert-item .a-text{font-size:12.5px;color:var(--text);flex:1}
.alert-item .a-count{font-size:13px;font-weight:700;color:var(--text)}
/* Performance table — Apple Glass */
.perf-table{width:100%;border-collapse:separate;border-spacing:0;font-size:12.5px}
.perf-table thead th{text-align:left;padding:10px 14px;font-weight:600;color:var(--text-secondary);font-size:11px;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--glass-border);white-space:nowrap}
.perf-table tbody td{padding:10px 14px;border-bottom:1px solid var(--glass-border);color:var(--text)}
.perf-table tbody tr{transition:all .2s ease}
.perf-table tbody tr:hover{background:var(--glass-hover-bg);box-shadow:inset 0 1px 0 rgba(255,255,255,.3),inset 0 -1px 0 rgba(255,255,255,.04)}
.perf-table tbody tr:focus-visible{outline:none;box-shadow:inset 0 0 0 2px rgba(255,255,255,.15)}
/* Workload bar — Apple Glass */
.wl-bar{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.wl-bar .wl-name{width:120px;font-size:12px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wl-bar .wl-track{flex:1;height:8px;border-radius:4px;background:rgba(255,255,255,.06);border:1px solid var(--glass-border);overflow:hidden}
.wl-bar .wl-fill{height:100%;border-radius:4px;transition:width .4s ease}
.wl-bar .wl-pct{width:40px;font-size:12px;font-weight:600;text-align:right;font-variant-numeric:tabular-nums}
/* Score breakdown */
.score-row{display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--glass-border)}
.score-row:last-child{border-bottom:none}
.score-row .sr-label{flex:1;font-size:12.5px;color:var(--text)}
.score-row .sr-weight{font-size:11px;color:var(--text-secondary);width:40px;text-align:right}
.score-row .sr-val{font-size:13px;font-weight:700;width:30px;text-align:right;font-variant-numeric:tabular-nums}
/* Geographic — Apple Glass */
.geo-map-wrap{position:relative;background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:16px;overflow:hidden;backdrop-filter:blur(20px) saturate(150%);-webkit-backdrop-filter:blur(20px) saturate(150%)}
.geo-map-img{width:100%;height:auto;display:block;filter:drop-shadow(0 4px 12px rgba(0,0,0,.08))}
.geo-map-overlay{position:absolute;inset:0}
.geo-dot{position:absolute;width:18px;height:18px;border-radius:50%;transform:translate(-50%,-50%);cursor:pointer;z-index:3;transition:all .2s;border:3px solid rgba(255,255,255,.9);box-shadow:0 0 14px rgba(99,102,241,.5);animation:geoPulse 2.5s ease-in-out infinite}
.geo-dot:hover{transform:translate(-50%,-50%) scale(1.4);box-shadow:0 0 24px rgba(99,102,241,.8);z-index:10}
@keyframes geoPulse{0%,100%{box-shadow:0 0 10px rgba(99,102,241,.4)}50%{box-shadow:0 0 22px rgba(99,102,241,.7)}}
.geo-dot .dot-label{position:absolute;top:-22px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:10px;font-weight:700;color:#fff;text-shadow:0 1px 6px rgba(0,0,0,.8),0 0 12px rgba(0,0,0,.5);pointer-events:none}
[data-theme="dark"] .geo-dot .dot-label{color:#fff;text-shadow:0 1px 6px rgba(0,0,0,.9)}
/* Popup — Apple Glass */
.geo-popup{position:absolute;width:280px;background:var(--glass-bg);backdrop-filter:blur(24px) saturate(160%);-webkit-backdrop-filter:blur(24px) saturate(160%);border:1px solid var(--glass-border);border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.15);z-index:20;padding:0;display:none;overflow:hidden}
.geo-popup.show{display:block;animation:popupIn .2s ease-out}
@keyframes popupIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
[data-theme="dark"] .geo-popup{background:rgba(30,41,59,.95);border-color:rgba(255,255,255,.08)}
.geo-popup .pop-head{padding:14px 16px 10px;border-bottom:1px solid var(--glass-border);display:flex;justify-content:space-between;align-items:center}
.geo-popup .pop-head h4{margin:0;font-size:14px;font-weight:700}
.geo-popup .pop-close{background:none;border:none;cursor:pointer;color:var(--text-secondary);font-size:18px;padding:0 4px;line-height:1}
.geo-popup .pop-body{padding:12px 16px}
.geo-popup .pop-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.geo-popup .pop-stat{padding:8px 10px;border-radius:10px;background:var(--glass-bg);border:1px solid var(--glass-border);backdrop-filter:blur(10px)}
.geo-popup .pop-stat .ps-val{font-size:18px;font-weight:800;color:var(--text)}
.geo-popup .pop-stat .ps-label{font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:var(--text-secondary)}
.geo-popup .pop-stat.pop-accent{background:rgba(99,102,241,.08);border-color:rgba(99,102,241,.15)}
.geo-popup .pop-stat.pop-accent .ps-val{color:var(--accent)}
.geo-popup .pop-bar{margin-top:10px;padding-top:10px;border-top:1px solid var(--glass-border)}
.geo-popup .pop-bar-label{font-size:10px;text-transform:uppercase;color:var(--text-secondary);margin-bottom:4px}
.geo-popup .pop-bar-track{height:6px;border-radius:3px;background:rgba(255,255,255,.06);border:1px solid var(--glass-border);overflow:hidden}
.geo-popup .pop-bar-fill{height:100%;border-radius:3px;transition:width .4s ease}
/* Connection lines */
.geo-lines{position:absolute;inset:0;pointer-events:none;z-index:1}
.geo-lines line{stroke:var(--accent);stroke-width:1;stroke-dasharray:4 4;opacity:.2}
.geo-legend{display:flex;gap:16px;justify-content:center;margin-top:14px;flex-wrap:wrap}
.geo-legend span{font-size:11px;color:var(--text-secondary);display:flex;align-items:center;gap:5px}
.geo-legend span::before{content:'';width:10px;height:10px;border-radius:3px}
.geo-legend .gl-schools::before{background:#6366f1}
.geo-legend .gl-students::before{background:#22c55e}
.geo-legend .gl-teachers::before{background:#f59e0b}
.geo-legend .gl-active::before{background:#10b981}
/* Geographic summary cards — Apple Glass */
.geo-summary{display:flex;gap:12px;margin-bottom:14px;flex-wrap:wrap}
.geo-summary .gs-card{flex:1;min-width:120px;padding:10px 14px;border-radius:12px;background:var(--glass-bg);border:1px solid var(--glass-border);backdrop-filter:blur(20px) saturate(150%);-webkit-backdrop-filter:blur(20px) saturate(150%);text-align:center;transition:all .25s ease;position:relative;overflow:hidden}
.geo-summary .gs-card::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.06) 0%,transparent 40%,rgba(255,255,255,.02) 100%);pointer-events:none}
.geo-summary .gs-card:hover{background:var(--glass-hover-bg);border-color:var(--glass-hover-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.4),inset 0 -1px 0 rgba(255,255,255,.05),var(--glass-hover-shadow)}
.geo-summary .gs-val{font-size:18px;font-weight:800;color:var(--accent)}
.geo-summary .gs-label{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--text-secondary);margin-top:2px}
/* Geo data table — Apple Glass */
.geo-geo-table{margin-top:16px}
.geo-geo-table table{width:100%;border-collapse:collapse;font-size:12px}
.geo-geo-table th{text-align:left;padding:8px 12px;background:transparent;font-weight:600;color:var(--text-secondary);font-size:11px;text-transform:uppercase;letter-spacing:.3px;border-bottom:1px solid var(--glass-border)}
.geo-geo-table td{padding:8px 12px;border-bottom:1px solid var(--glass-border)}
.geo-geo-table tr:hover td{background:var(--glass-hover-bg);box-shadow:inset 0 1px 0 rgba(255,255,255,.2)}
.geo-geo-table tr:focus-visible td{outline:none;box-shadow:inset 0 0 0 2px rgba(255,255,255,.12)}
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
<?php
  $ethMap = url('public/images/ethiopia-map.png');
  // Positions verified against map.png black silhouette boundaries
  // y=10%: x=22-54%, y=20%: x=17-62%, y=35%: x=10-65%, y=50%: x=7-73%, y=55%: x=7-85%, y=60%: x=0-99%
  $regionPos = [
    'Tigray'             => [40, 12],
    'Afar'               => [55, 20],
    'Amhara'             => [38, 26],
    'Addis Ababa'        => [32, 46],
    'Dire Dawa'          => [58, 36],
    'Harari'             => [57, 38],
    'Oromia'             => [40, 54],
    'SNNPR'              => [30, 66],
    'Sidama'             => [42, 66],
    'South West Ethiopia'=> [18, 56],
    'South Ethiopia'     => [28, 76],
    'Central Ethiopia'   => [34, 60],
    'Benishangul-Gumuz'  => [12, 38],
    'Gambela'            => [9, 48],
    'Somali'             => [68, 56],
    'Contested'          => [50, 42],
  ];
  $totalSchools = array_sum(array_column($regions, 'schools'));
  $totalStudents = array_sum(array_column($regions, 'students'));
  $totalTeachers = array_sum(array_column($regions, 'teachers'));
  $totalActive = array_sum(array_column($regions, 'active_schools'));
  $maxStudents = max(array_column($regions, 'students'));
?>
<div class="cmd-section">
  <div class="section-head">
    <h2><?= icon('map') ?> Geographic Analytics</h2>
    <div class="section-line"></div>
  </div>

  <!-- Summary cards -->
  <div class="geo-summary">
    <div class="gs-card"><div class="gs-val"><?= number_format($totalSchools) ?></div><div class="gs-label">Total Schools</div></div>
    <div class="gs-card"><div class="gs-val" style="color:#22c55e"><?= number_format($totalStudents) ?></div><div class="gs-label">Students</div></div>
    <div class="gs-card"><div class="gs-val" style="color:#f59e0b"><?= number_format($totalTeachers) ?></div><div class="gs-label">Teachers</div></div>
    <div class="gs-card"><div class="gs-val" style="color:#10b981"><?= number_format($totalActive) ?></div><div class="gs-label">Active Schools</div></div>
  </div>

  <!-- Map with clickable dots + connection lines -->
  <div class="geo-map-wrap card" style="padding:20px">
    <div style="position:relative;max-width:600px;margin:0 auto" id="eth-map">
      <img class="geo-map-img" src="<?= $ethMap ?>" alt="Ethiopia Map">
      <div class="geo-map-overlay">
        <!-- SVG connection lines between all region dots -->
        <svg class="geo-lines" viewBox="0 0 100 100" preserveAspectRatio="none">
          <?php
          $posArr = [];
          foreach ($regions as $rg) {
            $pos = $regionPos[$rg['region']] ?? null;
            if ($pos) $posArr[] = $pos;
          }
          // Connect each dot to its nearest 2 neighbors
          for ($i = 0; $i < count($posArr); $i++) {
            $dists = [];
            for ($j = 0; $j < count($posArr); $j++) {
              if ($i === $j) continue;
              $dists[$j] = sqrt(pow($posArr[$i][0]-$posArr[$j][0],2) + pow($posArr[$i][1]-$posArr[$j][1],2));
            }
            asort($dists);
            $neighbors = array_slice(array_keys($dists), 0, 2);
            foreach ($neighbors as $n) {
              if ($n > $i) { // draw each line once
                echo '<line x1="'.$posArr[$i][0].'" y1="'.$posArr[$i][1].'" x2="'.$posArr[$n][0].'" y2="'.$posArr[$n][1].'"/>';
              }
            }
          }
          ?>
        </svg>

        <!-- Clickable dots for each region -->
        <?php foreach ($regions as $rg):
          $name = $rg['region'];
          $pos = $regionPos[$name] ?? null;
          if (!$pos) continue;
          $schools = (int)$rg['schools'];
          $students = (int)$rg['students'];
          $teachers = (int)$rg['teachers'];
          $active = (int)$rg['active_schools'];
          $pct = $schools > 0 ? round($active / $schools * 100) : 0;
          $ratio = $teachers > 0 ? round($students / $teachers, 1) : '—';
          $intensity = $maxStudents > 0 ? $students / $maxStudents : 0;
          $color = $pct > 80 ? '#22c55e' : ($pct > 50 ? '#f59e0b' : '#ef4444');
        ?>
        <div class="geo-dot" style="left:<?= $pos[0] ?>%;top:<?= $pos[1] ?>%;background:<?= $color ?>" data-region="<?= e($name) ?>" data-schools="<?= $schools ?>" data-students="<?= $students ?>" data-teachers="<?= $teachers ?>" data-active="<?= $active ?>" data-pct="<?= $pct ?>" data-ratio="<?= $ratio ?>" onclick="showGeoPopup(this)">
          <span class="dot-label"><?= e($name) ?></span>
        </div>
        <?php endforeach; ?>

        <!-- Popup card -->
        <div class="geo-popup" id="geo-popup">
          <div class="pop-head">
            <h4 id="pop-title">Region</h4>
            <button class="pop-close" onclick="closeGeoPopup()">&times;</button>
          </div>
          <div class="pop-body">
            <div class="pop-grid">
              <div class="pop-stat" style="background:rgba(99,102,241,.06);border-color:rgba(99,102,241,.12)"><div class="ps-val" style="color:#6366f1" id="pop-schools">0</div><div class="ps-label">Schools</div></div>
              <div class="pop-stat" style="background:rgba(16,185,129,.06);border-color:rgba(16,185,129,.12)"><div class="ps-val" style="color:#10b981" id="pop-active">0</div><div class="ps-label">Active</div></div>
              <div class="pop-stat" style="background:rgba(34,197,94,.06);border-color:rgba(34,197,94,.12)"><div class="ps-val" style="color:#22c55e" id="pop-students">0</div><div class="ps-label">Students</div></div>
              <div class="pop-stat" style="background:rgba(245,158,11,.06);border-color:rgba(245,158,11,.12)"><div class="ps-val" style="color:#f59e0b" id="pop-teachers">0</div><div class="ps-label">Teachers</div></div>
            </div>
            <div class="pop-bar">
              <div class="pop-bar-label">School Active Rate</div>
              <div class="pop-bar-track"><div class="pop-bar-fill" id="pop-bar" style="width:0%;background:#22c55e"></div></div>
              <div style="text-align:right;margin-top:3px"><span class="gl-val" id="pop-pct" style="font-size:12px;font-weight:700">0%</span></div>
            </div>
            <div class="pop-bar">
              <div class="pop-bar-label">Student : Teacher Ratio</div>
              <div style="margin-top:3px"><span class="gl-val" id="pop-ratio" style="font-size:14px;font-weight:700">—</span> <span style="font-size:10px;color:var(--text-secondary)">students per teacher</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="geo-legend">
      <span class="gl-schools">Schools</span>
      <span class="gl-students">Students</span>
      <span class="gl-teachers">Teachers</span>
      <span class="gl-active">Active</span>
    </div>
  </div>

  <!-- Data table -->
  <div class="geo-geo-table" style="margin-top:16px">
    <div class="card">
      <table>
        <thead><tr>
          <th>Region</th><th style="text-align:right">Schools</th><th style="text-align:right">Active</th>
          <th style="text-align:right">Students</th><th style="text-align:right">Teachers</th>
          <th style="text-align:right">Student:Teacher</th><th style="text-align:right">Active %</th>
        </tr></thead>
        <tbody>
        <?php foreach ($regions as $rg):
          $s = (int)$rg['schools']; $a = (int)$rg['active_schools'];
          $st = (int)$rg['students']; $t = (int)$rg['teachers'];
          $ratio = $t > 0 ? round($st / $t, 1) : '—';
          $pct = $s > 0 ? round($a / $s * 100, 1) : 0;
        ?>
          <tr>
            <td><b><?= e($rg['region']) ?></b></td>
            <td style="text-align:right;font-variant-numeric:tabular-nums"><?= number_format($s) ?></td>
            <td style="text-align:right;font-variant-numeric:tabular-nums"><?= number_format($a) ?></td>
            <td style="text-align:right;font-variant-numeric:tabular-nums"><?= number_format($st) ?></td>
            <td style="text-align:right;font-variant-numeric:tabular-nums"><?= number_format($t) ?></td>
            <td style="text-align:right;font-variant-numeric:tabular-nums"><?= $ratio ?></td>
            <td style="text-align:right"><span class="badge <?= ($pct > 80 ? 'badge-success' : ($pct > 50 ? 'badge-warning' : 'badge-danger')) ?>"><?= $pct ?>%</span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
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
function showGeoPopup(dot) {
  var popup = document.getElementById('geo-popup');
  var map = document.getElementById('eth-map');
  var mapRect = map.getBoundingClientRect();
  var dotRect = dot.getBoundingClientRect();
  var name = dot.dataset.region;
  var schools = parseInt(dot.dataset.schools);
  var students = parseInt(dot.dataset.students);
  var teachers = parseInt(dot.dataset.teachers);
  var active = parseInt(dot.dataset.active);
  var pct = parseInt(dot.dataset.pct);
  var ratio = dot.dataset.ratio;
  document.getElementById('pop-title').textContent = name;
  document.getElementById('pop-schools').textContent = schools.toLocaleString();
  document.getElementById('pop-active').textContent = active.toLocaleString();
  document.getElementById('pop-students').textContent = students.toLocaleString();
  document.getElementById('pop-teachers').textContent = teachers.toLocaleString();
  document.getElementById('pop-pct').textContent = pct + '%';
  document.getElementById('pop-ratio').textContent = ratio;
  var bar = document.getElementById('pop-bar');
  bar.style.width = pct + '%';
  bar.style.background = pct > 80 ? '#22c55e' : (pct > 50 ? '#f59e0b' : '#ef4444');
  // Position popup near the dot
  var overlay = dot.closest('.geo-map-overlay');
  var ovRect = overlay.getBoundingClientRect();
  var left = dotRect.left - ovRect.left + dotRect.width / 2;
  var top = dotRect.top - ovRect.top + dotRect.height / 2 + 20;
  if (left + 290 > ovRect.width) left = ovRect.width - 295;
  if (left < 5) left = 5;
  if (top + 260 > ovRect.height) top = dotRect.top - ovRect.top - 260;
  popup.style.left = left + 'px';
  popup.style.top = top + 'px';
  popup.classList.add('show');
  // Close on outside click
  setTimeout(function() { document.addEventListener('click', geoPopupClose); }, 0);
}
function geoPopupClose(e) {
  if (!e.target.closest('.geo-popup') && !e.target.closest('.geo-dot')) closeGeoPopup();
}
function closeGeoPopup() {
  document.getElementById('geo-popup').classList.remove('show');
  document.removeEventListener('click', geoPopupClose);
}
document.addEventListener('DOMContentLoaded', () => {
  if (!window.EdunexChart) return;
  const lc = document.getElementById('login-chart');
  if (lc) EdunexChart.line(lc, {
    labels: <?= json_encode(array_column($loginSeries, 'date')) ?>,
    values: <?= json_encode(array_column($loginSeries, 'logins')) ?>
  });
});
</script>
