<?php /* Director dashboard — interactive, matches other role dashboards */
$u = $__u;
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$statCards = [
    ['label' => 'Teachers', 'value' => $stats['teachers'], 'icon' => icon('users'), 'link' => 'director/teachers', 'hint' => 'on staff'],
    ['label' => 'Students', 'value' => $stats['students'], 'icon' => icon('graduation'), 'link' => 'director/students', 'hint' => $stats['active'] . ' active'],
    ['label' => 'Courses', 'value' => $stats['courses'], 'icon' => icon('books'), 'link' => 'courses', 'hint' => $stats['exams'] . ' exams'],
    ['label' => 'Active', 'value' => $stats['active'], 'icon' => icon('check-circle'), 'link' => 'director/students&filter=active', 'hint' => 'enrolled now'],
    ['label' => 'Re-exam', 'value' => $stats['inactive'], 'icon' => icon('pause'), 'link' => 'director/students&filter=inactive', 'hint' => 'inactive students'],
    ['label' => 'Pending', 'value' => $stats['pending'], 'icon' => icon('clock'), 'link' => 'director/students&filter=pending', 'hint' => 'need verification'],
];
$maxWeek = max(1, max(array_column($weeks, 'count')));
$todoCls = ['accent' => 'var(--accent)', 'warn' => 'var(--warning)', 'info' => 'var(--info)', 'ok' => 'var(--success)'];
?>
<div class="page-head">
  <div>
    <h1><?= $greeting ?>, <?= e($u['first_name']) ?> <?= icon('hand') ?></h1>
    <p class="sub"><?= e(date('l, F j, Y')) ?> · <?= e($u['school_name'] ?? 'Your school') ?> · Manage teachers, students and transfers.</p>
  </div>
  <div class="flex gap-8" style="flex-wrap:wrap">
    <a class="btn btn-primary" href="<?= e(url('director/teachers')) ?>"><?= icon('plus') ?> New teacher</a>
    <a class="btn btn-ghost" href="<?= e(url('director/import')) ?>"><?= icon('download') ?> Import users</a>
    <?php if ($pending > 0): ?>
      <a class="btn btn-warn" href="<?= e(url('director/students&filter=pending')) ?>"><?= icon('clock') ?> <?= (int)$pending ?> overdue</a>
    <?php endif; ?>
  </div>
</div>

<!-- Stat cards -->
<div class="grid grid-4" style="margin-bottom:22px">
  <?php foreach ($statCards as $c): ?>
    <a class="card stat-card tstat-card" href="<?= e(url($c['link'])) ?>" style="text-decoration:none;color:inherit">
      <span class="stat-ico"><?= $c['icon'] ?></span>
      <div style="min-width:0">
        <div class="stat-value tstat-val" data-val="<?= (int)$c['value'] ?>">0</div>
        <div class="small faint" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $c['label'] ?></div>
        <div class="tiny faint"><?= e($c['hint']) ?></div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="grid" style="grid-template-columns:1.55fr 1fr;gap:22px;align-items:start">
  <div class="flex-col gap-24">

    <!-- Signup trend -->
    <div class="card">
      <div class="flex-between" style="margin-bottom:10px">
        <h3 class="card-title" style="margin:0"><?= icon('trend-up') ?> New accounts</h3>
        <span class="tiny faint">teachers + students · last 8 weeks</span>
      </div>
      <div style="height:150px;position:relative">
        <svg viewBox="0 0 100 40" preserveAspectRatio="none" style="position:absolute;inset:0;width:100%;height:100%">
          <defs>
            <linearGradient id="dirTrendFill" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="var(--accent)" stop-opacity=".35"/>
              <stop offset="100%" stop-color="var(--accent)" stop-opacity="0"/>
            </linearGradient>
          </defs>
          <?php
            $n = count($weeks);
            $pts = [];
            foreach ($weeks as $i => $wk) {
              $x = $n === 1 ? 50 : $i / ($n - 1) * 100;
              $y = 40 - ($wk['count'] / $maxWeek) * 34 - 2;
              $pts[] = [$x, $y];
            }
            $poly = '0,40 ';
            foreach ($pts as [$x, $y]) $poly .= number_format($x, 1) . ',' . number_format($y, 1) . ' ';
            $poly .= '100,40';
          ?>
          <polygon points="<?= $poly ?>" fill="url(#dirTrendFill)"/>
          <polyline points="<?= implode(' ', array_map(fn($p) => number_format($p[0], 1) . ',' . number_format($p[1], 1), $pts)) ?>" fill="none" stroke="var(--accent)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
          <?php foreach ($pts as $i => [$x, $y]): ?>
            <circle cx="<?= number_format($x, 1) ?>" cy="<?= number_format($y, 1) ?>" r="1.1" fill="var(--accent)">
              <title><?= e($weeks[$i]['label']) ?>: <?= (int)$weeks[$i]['count'] ?> new account<?= $weeks[$i]['count'] === 1 ? '' : 's' ?></title>
            </circle>
          <?php endforeach; ?>
        </svg>
      </div>
      <div class="flex-between tiny faint" style="margin-top:4px">
        <?php foreach ($weeks as $i => $wk): ?><span style="flex:1;text-align:<?= $i === 0 ? 'left' : ($i === $n - 1 ? 'right' : 'center') ?>"><?= e($wk['label']) ?></span><?php endforeach; ?>
      </div>
    </div>

    <!-- Recently added -->
    <div class="card" style="padding:0;overflow:hidden">
      <div class="card-head" style="padding:14px 18px"><b><?= icon('spark') ?> Recently added</b><a class="small accent" href="<?= e(url('director/students')) ?>">All students →</a></div>
      <?php if (!$recent): ?><p class="muted small" style="padding:0 18px 16px">No teachers or students yet. Use the import tool to add them.</p><?php endif; ?>
      <?php foreach (array_slice($recent, 0, 6) as $r): ?>
        <div class="list-row" style="padding:11px 18px;border-bottom:1px solid var(--border)">
          <div class="avatar"><?= e(mb_strtoupper(mb_substr((string)$r['name'], 0, 1))) ?></div>
          <div class="flex-1" style="min-width:0">
            <b class="small" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block"><?= e($r['name']) ?></b>
            <span class="tiny faint"><?= e($r['email']) ?> · <?= e(date('M j, H:i', strtotime($r['created_at']))) ?></span>
          </div>
          <?php if ($r['role'] === 'teacher'): ?><span class="badge badge-muted">Teacher</span>
          <?php else: ?><?= $r['status'] === 'active' ? '<span class="badge badge-success">active</span>' : '<span class="badge badge-warning">' . e($r['status']) . '</span>' ?><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="flex-col gap-24" style="position:sticky;top:84px">

    <!-- Today's focus -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('target') ?> Today's focus</h3>
      <?php if (!$todo): ?><p class="muted small">All clear — nothing needs your attention. <?= icon('medal') ?></p><?php endif; ?>
      <?php foreach ($todo as $t): ?>
        <a class="list-row" href="<?= e(url($t['link'])) ?>" style="padding:10px 0;text-decoration:none;color:inherit;border-bottom:1px solid var(--border)">
          <span class="feed-dot" style="background:<?= $todoCls[$t['cls']] ?? 'var(--accent)' ?>"></span>
          <span class="small flex-1"><?= icon($t['icon']) ?> <?= e($t['label']) ?></span>
          <span class="tiny faint"><?= icon('reply') ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Quick actions -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('bolt') ?> Quick actions</h3>
      <?php $quick = [
        ['label' => 'Create teacher', 'href' => 'director/teachers', 'icon' => 'users', 'bg' => 'var(--accent-soft)', 'fg' => 'var(--accent)'],
        ['label' => 'Import users (Excel/CSV)', 'href' => 'director/import', 'icon' => 'download', 'bg' => 'var(--info-soft)', 'fg' => 'var(--info)'],
        ['label' => 'Transfers', 'href' => 'director/transfers', 'icon' => 'refresh', 'bg' => 'var(--success-soft)', 'fg' => 'var(--success)'],
        ['label' => 'Reports', 'href' => 'director/reports', 'icon' => 'trend-up', 'bg' => 'var(--warning-soft)', 'fg' => 'var(--warning)'],
      ]; ?>
      <?php foreach ($quick as $q): ?>
        <a class="list-row" href="<?= e(url($q['href'])) ?>" style="padding:10px 0;text-decoration:none;color:inherit;border-bottom:1px solid var(--border)">
          <span class="stat-ico" style="background:<?= $q['bg'] ?>;color:<?= $q['fg'] ?>"><?= icon($q['icon']) ?></span>
          <span class="small flex-1"><?= e($q['label']) ?></span>
          <span class="tiny faint"><?= icon('reply') ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Academic summary -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('chart-bar') ?> Academic</h3>
      <div class="flex-between" style="margin-top:8px"><span class="tiny faint">Courses</span><b><?= (int)$stats['courses'] ?></b></div>
      <div class="flex-between" style="margin-top:6px"><span class="tiny faint">Exams</span><b><?= (int)$stats['exams'] ?></b></div>
      <div class="flex-between" style="margin-top:6px"><span class="tiny faint">Active rate</span>
        <b><?= $stats['students'] > 0 ? (int)round($stats['active'] / $stats['students'] * 100) : 0 ?>%</b></div>
      <div class="flex-between" style="margin-top:6px"><span class="tiny faint">Pending transfers</span><b><?= (int)$stats['transfers'] ?></b></div>
    </div>

    <!-- Recent activity -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> Recent activity</h3>
      <?php if (!$activities): ?><p class="muted small">No recent activity.</p><?php endif; ?>
      <?php foreach ($activities as $a): ?>
        <div class="list-row" style="padding:8px 0;align-items:flex-start">
          <div class="small flex-1" style="overflow-wrap:anywhere"><b><?= e($a['first_name'] . ' ' . $a['last_name']) ?></b> <?= e($a['detail'] ?: $a['action']) ?></div>
          <div class="tiny faint" style="flex-shrink:0;margin-left:8px"><?= e(date('M j H:i', strtotime($a['created_at']))) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const vals = document.querySelectorAll('.tstat-val');
  const t0 = performance.now();
  const dur = 700;
  const targets = Array.from(vals).map(v => parseInt(v.dataset.val, 10) || 0);
  function tick(now) {
    const p = Math.min(1, (now - t0) / dur);
    const ease = 1 - Math.pow(1 - p, 3);
    vals.forEach((v, i) => { v.textContent = Math.round(targets[i] * ease); });
    if (p < 1) requestAnimationFrame(tick);
  }
  requestAnimationFrame(tick);
});
</script>
