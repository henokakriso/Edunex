<?php /* Director dashboard — clean, organized, Apple glassmorphism */
$u = $__u;
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$maxWeek = max(1, max(array_column($weeks, 'count')));
$todoCls = ['accent' => 'var(--accent)', 'warn' => 'var(--warning)', 'info' => 'var(--info)', 'ok' => 'var(--success)'];
?>
<style>
  .dir-dash { font-family: -apple-system, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', system-ui, sans-serif; color: var(--text); }
  .dir-dash h1 { font-family: -apple-system, 'SF Pro Display', 'Helvetica Neue', sans-serif; font-weight: 700; letter-spacing: -.025em; color: var(--text); font-size: 1.6rem; }
  .dir-dash h3, .dir-dash .card-title { font-family: -apple-system, 'SF Pro Display', 'Helvetica Neue', sans-serif; font-weight: 600; letter-spacing: -.02em; color: var(--text); }
  .dir-dash .stat-value { font-family: -apple-system, 'SF Pro Display', 'Helvetica Neue', sans-serif; font-weight: 800; letter-spacing: -.03em; color: var(--text); font-size: 1.7rem; }
  .dir-dash .card { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; background: var(--bg-elev) !important; border: 1px solid var(--border) !important; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04) !important; }
</style>
<div class="dir-dash">
<div class="page-head">
  <div>
    <h1><?= $greeting ?>, <?= e($u['first_name']) ?> <?= icon('hand') ?></h1>
    <p class="sub"><?= e(date('l, F j, Y')) ?> · <?= e($u['school_name'] ?? 'Your school') ?></p>
  </div>
  <div class="flex gap-8" style="flex-wrap:wrap">
    <a class="btn btn-primary" href="<?= e(url('director/teachers')) ?>"><?= icon('plus') ?> New teacher</a>
    <a class="btn btn-ghost" href="<?= e(url('director/import')) ?>"><?= icon('download') ?> Import</a>
  </div>
</div>

<!-- Stat cards -->
<div class="grid grid-3" style="margin-bottom:22px">
  <a class="card stat-card" href="<?= e(url('director/teachers')) ?>" style="text-decoration:none;color:inherit">
    <span class="stat-ico"><?= icon('users') ?></span>
    <div><div class="stat-value" data-val="<?= (int)$stats['teachers'] ?>">0</div><div class="small faint">Teachers · on staff</div></div>
  </a>
  <a class="card stat-card" href="<?= e(url('director/students')) ?>" style="text-decoration:none;color:inherit">
    <span class="stat-ico"><?= icon('graduation') ?></span>
    <div><div class="stat-value" data-val="<?= (int)$stats['students'] ?>">0</div><div class="small faint">Students · <?= (int)$stats['active'] ?> active</div></div>
  </a>
  <a class="card stat-card" href="<?= e(url('courses')) ?>" style="text-decoration:none;color:inherit">
    <span class="stat-ico"><?= icon('books') ?></span>
    <div><div class="stat-value" data-val="<?= (int)$stats['courses'] ?>">0</div><div class="small faint">Courses · <?= (int)$stats['exams'] ?> exams</div></div>
  </a>
</div>

<div class="grid" style="grid-template-columns:1.4fr 1fr;gap:22px;align-items:start">
  <div class="flex-col gap-20">

    <!-- Signup trend -->
    <div class="card">
      <div class="flex-between" style="margin-bottom:10px">
        <h3 class="card-title" style="margin:0"><?= icon('trend-up') ?> New accounts</h3>
        <span class="tiny faint">teachers + students · last 8 weeks</span>
      </div>
      <div style="height:140px;position:relative">
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
              <title><?= e($weeks[$i]['label']) ?>: <?= (int)$weeks[$i]['count'] ?></title>
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
      <?php if (!$recent): ?><p class="muted small" style="padding:0 18px 16px">No teachers or students yet.</p><?php endif; ?>
      <?php foreach (array_slice($recent, 0, 5) as $r): ?>
        <div class="list-row" style="padding:10px 18px;border-bottom:1px solid var(--border)">
          <div class="avatar" style="width:34px;height:34px;font-size:13px;flex-shrink:0"><?= e(mb_strtoupper(mb_substr((string)$r['name'], 0, 1))) ?></div>
          <div style="min-width:0;flex:1">
            <div class="small" style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($r['name']) ?></div>
            <div class="tiny faint"><?= e($r['email']) ?></div>
          </div>
          <span class="tiny faint" style="flex-shrink:0"><?= e(date('M j', strtotime($r['created_at']))) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="flex-col gap-20">

    <!-- Today's focus -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('target') ?> Today's focus</h3>
      <?php if (!$todo): ?><p class="muted small"><?= icon('medal') ?> All clear — nothing needs your attention.</p><?php endif; ?>
      <?php foreach ($todo as $t): ?>
        <a class="list-row" href="<?= e(url($t['link'])) ?>" style="padding:10px 0;text-decoration:none;color:inherit;border-bottom:1px solid var(--border)">
          <span class="feed-dot" style="background:<?= $todoCls[$t['cls']] ?? 'var(--accent)' ?>"></span>
          <span class="small flex-1"><?= e($t['label']) ?></span>
          <span class="tiny faint"><?= icon('reply') ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Quick actions -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('bolt') ?> Quick actions</h3>
      <?php $quick = [
        ['label' => 'Create teacher', 'href' => 'director/teachers', 'icon' => 'users', 'bg' => 'var(--accent-soft)', 'fg' => 'var(--accent)'],
        ['label' => 'Import users', 'href' => 'director/import', 'icon' => 'download', 'bg' => 'var(--info-soft)', 'fg' => 'var(--info)'],
        ['label' => 'Transfers', 'href' => 'director/transfers', 'icon' => 'refresh', 'bg' => 'var(--success-soft)', 'fg' => 'var(--success)'],
        ['label' => 'Reports', 'href' => 'director/reports', 'icon' => 'trend-up', 'bg' => 'var(--warning-soft)', 'fg' => 'var(--warning)'],
      ]; ?>
      <?php foreach ($quick as $q): ?>
        <a class="list-row" href="<?= e(url($q['href'])) ?>" style="padding:10px 0;text-decoration:none;color:inherit;border-bottom:1px solid var(--border)">
          <span class="stat-ico" style="width:32px;height:32px;border-radius:9px;background:<?= $q['bg'] ?>;color:<?= $q['fg'] ?>;font-size:15px"><?= icon($q['icon']) ?></span>
          <span class="small flex-1"><?= e($q['label']) ?></span>
          <span class="tiny faint"><?= icon('reply') ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Academic summary -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('chart-bar') ?> Academic</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px">
        <div style="text-align:center;padding:10px;border-radius:10px;background:color-mix(in srgb, var(--accent) 6%, transparent)">
          <div class="small" style="font-weight:700;color:var(--accent)"><?= (int)$stats['courses'] ?></div>
          <div class="tiny faint">Courses</div>
        </div>
        <div style="text-align:center;padding:10px;border-radius:10px;background:color-mix(in srgb, var(--info) 6%, transparent)">
          <div class="small" style="font-weight:700;color:var(--info)"><?= (int)$stats['exams'] ?></div>
          <div class="tiny faint">Exams</div>
        </div>
        <div style="text-align:center;padding:10px;border-radius:10px;background:color-mix(in srgb, var(--success) 6%, transparent)">
          <div class="small" style="font-weight:700;color:var(--success)"><?= $stats['students'] > 0 ? (int)round($stats['active'] / $stats['students'] * 100) : 0 ?>%</div>
          <div class="tiny faint">Active rate</div>
        </div>
        <div style="text-align:center;padding:10px;border-radius:10px;background:color-mix(in srgb, var(--warning) 6%, transparent)">
          <div class="small" style="font-weight:700;color:var(--warning)"><?= (int)$stats['transfers'] ?></div>
          <div class="tiny faint">Transfers</div>
        </div>
      </div>
    </div>

    <!-- Recent activity -->
    <div class="card" style="padding:0;overflow:hidden">
      <div style="padding:14px 18px 8px"><b class="small"><?= icon('clock') ?> Recent activity</b></div>
      <?php if (!$activities): ?><p class="muted small" style="padding:0 18px 16px">No recent activity.</p><?php endif; ?>
      <?php foreach (array_slice($activities, 0, 5) as $a): ?>
        <div style="padding:8px 18px;border-bottom:1px solid var(--border);display:flex;gap:10px;align-items:flex-start">
          <div style="width:6px;height:6px;border-radius:50%;background:var(--accent);opacity:.5;margin-top:6px;flex-shrink:0"></div>
          <div style="min-width:0;flex:1">
            <div class="tiny" style="overflow-wrap:anywhere"><b><?= e($a['first_name']) ?></b> <?= e($a['detail'] ?: $a['action']) ?></div>
          </div>
          <div class="tiny faint" style="flex-shrink:0;white-space:nowrap"><?= e(date('M j', strtotime($a['created_at']))) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const vals = document.querySelectorAll('.stat-value[data-val]');
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
