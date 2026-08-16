<?php /* Student dashboard view */
$u = me();
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
?>
<div class="page-head">
  <div>
    <h1><?= $greeting ?>, <?= e($u['first_name']) ?> <?= icon('hand') ?></h1>
    <p class="sub"><?= e(date('l, F j')) ?> · Here's what's happening with your studies today.</p>
  </div>
  <div class="flex gap-8">
    <span class="badge badge-accent"><?= icon('flame') ?> <?= (int)$u['streak'] ?>-day streak</span>
    <span class="badge"><?= icon('star') ?> Level <?= (int)$u['level'] ?></span>
    <span class="badge badge-info"><?= (int)$u['xp'] ?> XP</span>
    <a class="btn btn-primary btn-sm" href="<?= url('index.php?r=ai/tutor') ?>"><?= icon('robot') ?> Ask AI</a>
  </div>
</div>

<!-- Quick actions -->
<div class="grid-4" style="margin-bottom:22px">
  <?php
  $actions = [
    [icon('books'), 'Continue learning', 'student/courses'],
    [icon('note'), 'Take an exam', 'student/exams'],
    [icon('file'), 'Submit assignment', 'student/assignments'],
    [icon('book'), 'Browse library', 'library'],
  ];
  foreach ($actions as [$i, $t, $r]): ?>
    <a href="<?= url('index.php?r=' . $r) ?>" class="card card-hover flex gap-12" style="text-decoration:none;color:var(--text);padding:16px">
      <span style="font-size:22px"><?= $i ?></span><b class="small"><?= e($t) ?></b>
    </a>
  <?php endforeach; ?>
</div>

<div class="grid" style="grid-template-columns: 1.7fr 1fr;align-items:start" id="dash-grid">
  <div class="flex-col gap-16">

    <!-- AI Recommendations -->
    <div class="card">
      <div class="flex-between" style="margin-bottom:14px">
        <h3><?= icon('robot') ?> AI recommendations</h3>
        <a class="small" href="<?= url('index.php?r=ai/tutor') ?>">Open tutor →</a>
      </div>
      <?php foreach ($aiRecs as [$ico, $t, $d, $link]): ?>
        <a class="feed-item" href="<?= url('index.php?r=' . $link) ?>" style="text-decoration:none;color:var(--text)">
          <span class="feed-dot" style="background:var(--accent)"></span>
          <span><b class="small"><?= icon($ico) ?> <?= e($t) ?></b><br><span class="muted tiny"><?= e($d) ?></span></span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Course progress -->
    <div class="card">
      <div class="flex-between" style="margin-bottom:14px">
        <h3><?= icon('books') ?> My courses</h3>
        <a class="small" href="<?= url('index.php?r=student/courses') ?>">All courses →</a>
      </div>
      <?php if (!$courses): ?>
        <div class="empty"><span class="empty-ico"><?= icon('users-card') ?></span>No courses yet — browse the <a href="<?= url('index.php?r=courses') ?>">course catalog</a>.</div>
      <?php else: foreach ($courses as $c): ?>
        <div class="feed-item">
          <div style="flex:1">
            <div class="flex-between">
              <b class="small"><?= e($c['title']) ?></b>
              <span class="tiny faint"><?= (int)$c['done_lessons'] ?>/<?= (int)$c['total_lessons'] ?> lessons · <?= round($c['progress']) ?>%</span>
            </div>
            <div class="progress" style="margin-top:8px"><div style="width:<?= (float)$c['progress'] ?>%"></div></div>
            <div class="flex-between tiny faint" style="margin-top:6px">
              <span>Teacher: <?= e($c['tfirst']) ?> <?= e($c['tlast']) ?></span>
              <?php if ($c['completed']): ?><span class="badge badge-success">✓ Completed</span><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Upcoming exams & assignments -->
    <div class="grid" style="grid-template-columns:1fr 1fr;gap:16px">
      <div class="card">
        <h3 style="margin-bottom:14px"><?= icon('note') ?> Upcoming exams</h3>
        <?php if (!$upcomingExams): ?><p class="muted small">No upcoming exams <?= icon('spark') ?></p><?php endif; ?>
        <?php foreach ($upcomingExams as $e): ?>
          <div class="feed-item">
            <span class="feed-dot" style="background:var(--danger)"></span>
            <span><b class="small"><?= e($e['title']) ?></b><br>
              <span class="muted tiny"><?= e($e['course_title']) ?> · <?= date('M j, H:i', strtotime($e['start_time'])) ?> · <?= (int)$e['duration_min'] ?> min</span></span>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="card">
        <h3 style="margin-bottom:14px"><?= icon('file') ?> Due assignments</h3>
        <?php if (!$assignments): ?><p class="muted small">Nothing due — enjoy! <?= icon('spark') ?></p><?php endif; ?>
        <?php foreach ($assignments as $a): ?>
          <div class="feed-item">
            <span class="feed-dot" style="background:var(--warning)"></span>
            <span><b class="small"><?= e($a['title']) ?></b><br>
              <span class="muted tiny"><?= e($a['course_title']) ?> · Due <?= e(time_ago($a['due_date'])) ?><?= $a['my_status'] ? ' · ' . $a['my_score'] . '/'. (float)$a['max_score'] . ' pts' : '' ?></span></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Announcements -->
    <div class="card">
      <h3 style="margin-bottom:14px"><?= icon('megaphone') ?> Announcements</h3>
      <?php foreach ($announcements as $an): ?>
        <div class="feed-item">
          <span class="feed-dot" style="background:var(--info)"></span>
          <span><b class="small"><?= e($an['title']) ?></b><br>
            <span class="muted tiny"><?= e(mb_substr($an['content'], 0, 140)) ?>… · <?= e($an['first_name']) ?> <?= e($an['last_name']) ?> · <?= e(time_ago($an['created_at'])) ?></span></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="flex-col gap-16">
    <!-- Today's schedule -->
    <div class="card">
      <h3 style="margin-bottom:14px"><?= icon('calendar') ?> Today's schedule</h3>
      <?php if (!$schedule): ?><p class="muted small">Nothing scheduled today. Enjoy the free time!</p><?php endif; ?>
      <?php foreach ($schedule as $ev): ?>
        <div class="feed-item">
          <span class="feed-dot" style="background:var(--info)"></span>
          <span><b class="small"><?= e($ev['title']) ?></b><br><span class="muted tiny"><?= date('H:i', strtotime($ev['start_at'])) ?><?= $ev['location'] ? ' · ' . e($ev['location']) : '' ?></span></span>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Attendance -->
    <div class="card">
      <div class="flex-between" style="margin-bottom:14px">
        <h3><?= icon('doc') ?> Attendance (30 days)</h3>
        <a class="small" href="<?= url('index.php?r=student/attendance') ?>">Detail →</a>
      </div>
      <?php
        $total = max(1, (int)($attStats['total'] ?? 0));
        $pct = round(($attStats['present'] ?? 0) / $total * 100);
      ?>
      <div class="flex gap-16" style="align-items:center">
        <div data-donut="<?= $pct ?>"></div>
        <div class="small">
          <div><?= icon('check-circle') ?> Present: <b><?= (int)($attStats['present'] ?? 0) ?></b></div>
          <div><?= icon('ban-circle') ?> Absent: <b><?= (int)($attStats['absent'] ?? 0) ?></b></div>
          <div><?= icon('clock') ?> Late: <b><?= (int)($attStats['late'] ?? 0) ?></b></div>
          <div><?= icon('shield') ?> Excused: <b><?= (int)($attStats['excused'] ?? 0) ?></b></div>
        </div>
      </div>
      <div class="flex gap-4" style="margin-top:14px;flex-wrap:wrap">
        <?php foreach (array_reverse($attendance) as $a): ?>
          <span title="<?= e($a['date']) ?>" style="width:14px;height:14px;border-radius:4px;background:<?= match ($a['status']) { 'present' => 'var(--success)', 'late' => 'var(--warning)', 'excused' => 'var(--info)', default => 'var(--danger)' } ?>"></span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Achievements -->
    <div class="card">
      <h3 style="margin-bottom:14px"><?= icon('trophy') ?> Achievements</h3>
      <div class="flex gap-8" style="flex-wrap:wrap">
        <?php foreach ($badges as $b): ?>
          <span class="badge" title="<?= e($b['description']) ?>"><?= icon($b['icon']) ?> <?= e($b['name']) ?></span>
        <?php endforeach; ?>
        <?php if (!$badges): ?><p class="muted small">Complete lessons and quizzes to earn badges!</p><?php endif; ?>
      </div>
      <?php if ($goals): ?>
        <div class="divider"></div>
        <h4 class="small" style="margin-bottom:10px"><?= icon('target') ?> Goals</h4>
        <?php foreach ($goals as $g): ?>
          <div class="tiny faint" style="margin-bottom:6px"><?= e($g['title']) ?> — <?= (int)$g['current'] ?>/<?= (int)$g['target'] ?> <?= e($g['unit']) ?></div>
          <div class="progress progress-sm" style="margin-bottom:10px"><div style="width:<?= min(100, $g['current'] / max(1, $g['target']) * 100) ?>%"></div></div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Recent activity -->
    <div class="card">
      <h3 style="margin-bottom:14px"><?= icon('file') ?> Recent activity</h3>
      <?php foreach ($recentActivity as $r): ?>
        <div class="feed-item"><span class="feed-dot" style="background:var(--accent-3)"></span><span class="tiny"><b><?= e($r['action']) ?></b><br><span class="faint"><?= e(time_ago($r['created_at'])) ?></span></span></div>
      <?php endforeach; ?>
      <?php if (!$recentActivity): ?><p class="muted small">Your activity will appear here.</p><?php endif; ?>
    </div>
  </div>
</div>
