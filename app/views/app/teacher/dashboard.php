<?php /* Teacher dashboard view — interactive */
$u = me();
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$statCards = [
    ['label' => 'Courses', 'value' => $stats['courses'], 'icon' => icon('books'), 'link' => 'teacher/courses', 'hint' => $stats['published'] . ' published'],
    ['label' => 'Students', 'value' => $stats['students'], 'icon' => icon('users-card'), 'link' => 'teacher/students', 'hint' => 'enrolled'],
    ['label' => 'Lessons', 'value' => $stats['lessons'], 'icon' => icon('book'), 'link' => 'teacher/courses', 'hint' => 'across courses'],
    ['label' => 'Exams', 'value' => $stats['exams'], 'icon' => icon('note'), 'link' => 'teacher/exams', 'hint' => $stats['pending_grades'] . ' to grade'],
    ['label' => 'Assignments', 'value' => $stats['assignments'], 'icon' => icon('file'), 'link' => 'teacher/assignments', 'hint' => $stats['pending_submissions'] . ' to review'],
    ['label' => 'To grade', 'value' => $stats['pending_grades'] + $stats['pending_submissions'], 'icon' => icon('check-circle'), 'link' => 'teacher/grade', 'hint' => 'pending queue'],
];
$maxWeek = max(1, max(array_column($weeks, 'count')));
$maxStu = max(1, max(array_column($courseStats, 'students') ?: [1]));
$statusCls = ['published' => 'badge-success', 'draft' => 'badge-muted', 'archived' => 'badge-danger'];
$todoCls = ['accent' => 'var(--accent)', 'warn' => 'var(--warning)', 'info' => 'var(--info)', 'ok' => 'var(--success)'];
?>
<div class="page-head">
  <div>
    <h1><?= $greeting ?>, <?= e($u['first_name']) ?> <?= icon('hand') ?></h1>
    <p class="sub"><?= e(date('l, F j, Y')) ?> · <?= e($u['school_name'] ?? 'Your school') ?> · Manage courses, grade work and track your students.</p>
  </div>
  <div class="flex gap-8" style="flex-wrap:wrap">
    <a class="btn btn-primary" href="<?= e(url('teacher/courses')) ?>"><?= icon('plus') ?> New course</a>
    <a class="btn btn-ghost" href="<?= e(url('ai/assistant')) ?>"><?= icon('robot') ?> AI assistant</a>
    <?php if ($stats['pending_verify'] > 0): ?>
      <a class="btn btn-warn" href="<?= e(url('teacher/verify')) ?>"><?= icon('check-circle') ?> <?= (int)$stats['pending_verify'] ?> to verify</a>
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

    <!-- Enrollment trend -->
    <div class="card">
      <div class="flex-between" style="margin-bottom:10px">
        <h3 class="card-title" style="margin:0"><?= icon('trend-up') ?> Enrollment trend</h3>
        <span class="tiny faint">last 8 weeks</span>
      </div>
      <div style="height:150px;position:relative">
        <svg viewBox="0 0 100 40" preserveAspectRatio="none" style="position:absolute;inset:0;width:100%;height:100%">
          <defs>
            <linearGradient id="trendFill" x1="0" y1="0" x2="0" y2="1">
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
          <polygon points="<?= $poly ?>" fill="url(#trendFill)"/>
          <polyline points="<?= implode(' ', array_map(fn($p) => number_format($p[0], 1) . ',' . number_format($p[1], 1), $pts)) ?>" fill="none" stroke="var(--accent)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
          <?php foreach ($pts as $i => [$x, $y]): ?>
            <circle cx="<?= number_format($x, 1) ?>" cy="<?= number_format($y, 1) ?>" r="1.1" fill="var(--accent)">
              <title><?= e($weeks[$i]['label']) ?>: <?= (int)$weeks[$i]['count'] ?> enrollment<?= $weeks[$i]['count'] === 1 ? '' : 's' ?></title>
            </circle>
          <?php endforeach; ?>
        </svg>
      </div>
      <div class="flex-between tiny faint" style="margin-top:4px">
        <?php foreach ($weeks as $i => $wk): ?><span style="flex:1;text-align:<?= $i === 0 ? 'left' : ($i === $n - 1 ? 'right' : 'center') ?>"><?= e($wk['label']) ?></span><?php endforeach; ?>
      </div>
    </div>

    <!-- Course performance -->
    <div class="card">
      <div class="flex-between" style="margin-bottom:14px">
        <h3 class="card-title" style="margin:0"><?= icon('chart-bar') ?> Course performance</h3>
        <a class="small accent" href="<?= e(url('teacher/courses')) ?>">Manage courses →</a>
      </div>
      <?php if (!$courseStats): ?><p class="muted small">No courses yet. <a class="accent" href="<?= e(url('teacher/courses')) ?>">Create your first course →</a></p><?php endif; ?>
      <?php foreach ($courseStats as $c): $pct = (int)($c['avg_progress'] ?? 0); $stu = (int)$c['students']; ?>
        <a class="list-row" href="<?= e(url('teacher/course&id=' . (int)$c['id'])) ?>" style="padding:12px 0;border-bottom:1px solid var(--border);color:inherit;text-decoration:none">
          <div class="flex-1" style="min-width:0">
            <div class="flex-between" style="margin-bottom:6px">
              <b class="small" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($c['title']) ?></b>
              <span class="badge <?= $statusCls[$c['status']] ?? 'badge-muted' ?>"><?= e($c['status']) ?></span>
            </div>
            <div class="flex gap-8" style="align-items:center">
              <div class="progress" style="flex:1;max-width:280px"><div style="width:<?= $pct ?>%"></div></div>
              <span class="tiny faint"><?= $pct ?>% avg</span>
            </div>
          </div>
          <div class="flex gap-12 tiny faint" style="text-align:center;flex-shrink:0">
            <span><?= icon('users') ?> <?= $stu ?></span>
            <span><?= icon('note') ?> <?= (int)$c['exams'] ?></span>
            <span><?= icon('file') ?> <?= (int)$c['assignments'] ?></span>
            <span><?= icon('book') ?> <?= (int)$c['lessons'] ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Recent enrollments -->
    <div class="card">
      <div class="flex-between" style="margin-bottom:14px">
        <h3 class="card-title" style="margin:0"><?= icon('spark') ?> Recent enrollments</h3>
        <a class="small accent" href="<?= e(url('teacher/students')) ?>">All students →</a>
      </div>
      <?php if (!$recent): ?><p class="muted small">No enrollments yet. Publish your courses to attract students.</p><?php endif; ?>
      <?php foreach ($recent as $r): ?>
        <div class="list-row">
          <div class="avatar"><?= e(mb_substr((string)$r['first_name'], 0, 1)) ?><?= e(mb_substr((string)$r['last_name'], 0, 1)) ?></div>
          <div class="flex-1">
            <b class="small"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b>
            <p class="tiny faint">enrolled in <?= e($r['course_title']) ?> · <?= e(date('M j, H:i', strtotime($r['enrolled_at']))) ?></p>
          </div>
          <a class="btn btn-sm btn-ghost" href="<?= e(url('teacher/course&id=' . (int)$r['course_id'])) ?>">Manage</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="flex-col gap-24" style="position:sticky;top:84px">

    <!-- To-do -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('target') ?> Today's focus</h3>
      <?php foreach ($todo as $t): ?>
        <?php if ($t['link']): ?>
          <a class="list-row" href="<?= e(url($t['link'])) ?>" style="padding:10px 0;color:inherit;text-decoration:none;border-bottom:1px solid var(--border)">
            <span class="feed-dot" style="background:<?= $todoCls[$t['cls']] ?? 'var(--accent)' ?>"></span>
            <span class="small flex-1"><?= icon($t['icon']) ?> <?= e($t['label']) ?></span>
            <span class="tiny faint"><?= icon('reply') ?></span>
          </a>
        <?php else: ?>
          <div class="list-row" style="padding:10px 0">
            <span class="feed-dot" style="background:<?= $todoCls[$t['cls']] ?? 'var(--accent)' ?>"></span>
            <span class="small"><?= icon($t['icon']) ?> <?= e($t['label']) ?></span>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Verify banner -->
    <?php if ($stats['pending_verify'] > 0): ?>
      <div class="alert alert-warning" style="margin:0">
        <?= icon('clock') ?> <b><?= (int)$stats['pending_verify'] ?></b> new student account<?= $stats['pending_verify'] === 1 ? '' : 's' ?> waiting — please verify within 24h.
        <a href="<?= e(url('teacher/verify')) ?>">Verify now →</a>
      </div>
    <?php endif; ?>

    <!-- Activity -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> Recent activity</h3>
      <?php if (!$activities): ?><p class="muted small">No recent activity.</p><?php endif; ?>
      <?php foreach (array_slice($activities, 0, 6) as $a): ?>
        <div class="list-row" style="padding:8px 0;align-items:flex-start">
          <div class="small flex-1" style="overflow-wrap:anywhere"><?= e($a['description']) ?></div>
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
