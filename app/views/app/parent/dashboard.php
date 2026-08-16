<?php /* Parent dashboard view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('user') ?>‍<?= icon('user') ?>‍<?= icon('user') ?> Parent Dashboard</h1>
    <p class="sub">Follow your children's learning journey</p>
  </div>
</div>

<?php if (!$summaries): ?>
  <div class="alert alert-info">No children linked to your account yet. Ask your school to link your child's account to this parent profile.</div>
<?php endif; ?>

<div class="flex-col gap-22">
  <?php foreach ($summaries as $s): $c = $s['child']; ?>
    <div class="card">
      <div class="flex-between" style="flex-wrap:wrap;gap:14px;margin-bottom:14px">
        <div class="flex gap-12" style="align-items:center">
          <div class="avatar" style="width:44px;height:44px;font-size:1rem"><?= e(mb_substr($c['first_name'], 0, 1) . mb_substr($c['last_name'], 0, 1)) ?></div>
          <div>
            <b style="font-size:1.05rem"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></b>
            <p class="tiny faint"><?= e($c['student_id']) ?> · <?= (int)$c['streak'] ?><?= icon('flame') ?> day streak</p>
          </div>
        </div>
        <div class="flex gap-8">
          <a class="btn btn-sm" href="<?= e(url('parent/reports&child=' . $c['id'])) ?>"><?= icon('trend-up') ?> Full report</a>
        </div>
      </div>
      <div class="grid4">
        <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px">
          <div class="stat-value"><?= (int)$s['avg_progress'] ?>%</div>
          <div class="tiny faint">Avg course progress</div>
        </div>
        <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px">
          <div class="stat-value"><?= (int)$s['attendance']['rate'] ?>%</div>
          <div class="tiny faint">Attendance</div>
        </div>
        <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px">
          <div class="stat-value"><?= e($s['gpa']) ?></div>
          <div class="tiny faint">GPA (4.0)</div>
        </div>
        <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px">
          <div class="stat-value"><?= icon('bolt') ?> <?= (int)$s['xp'] ?></div>
          <div class="tiny faint">XP · Level <?= (int)$s['level'] ?></div>
        </div>
      </div>
      <div class="grid" style="grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
        <div>
          <b class="small"><?= icon('note') ?> Upcoming exams</b>
          <?php if (!$s['upcoming']): ?><p class="tiny faint">None</p><?php endif; ?>
          <?php foreach ($s['upcoming'] as $ex): ?>
            <div class="list-row" style="padding:6px 0"><span class="small flex-1"><?= e($ex['title']) ?></span><span class="tiny faint"><?= e(date('M j, H:i', strtotime($ex['start_time']))) ?></span></div>
          <?php endforeach; ?>
        </div>
        <div>
          <b class="small"><?= icon('doc') ?> Recent assignments</b>
          <?php if (!$s['assignments']): ?><p class="tiny faint">None</p><?php endif; ?>
          <?php foreach ($s['assignments'] as $as): ?>
            <div class="list-row" style="padding:6px 0">
              <span class="small flex-1"><?= e($as['title']) ?></span>
              <span class="badge <?= $as['sub_status'] === 'graded' ? 'badge-success' : ($as['sub_status'] ? 'badge-warning' : 'badge-muted') ?>"><?= $as['sub_status'] ? ($as['sub_status'] === 'graded' ? $as['score'] . ' pts' : 'Submitted') : 'Not yet' ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($anns): ?>
<div class="card" style="margin-top:22px">
  <h3 class="card-title" style="margin-top:0"><?= icon('megaphone') ?> School announcements</h3>
  <?php foreach ($anns as $a): ?>
    <div class="list-row" style="padding:10px 0">
      <div class="flex-1"><b class="small"><?= e($a['title']) ?></b><p class="tiny faint"><?= e(mb_strimwidth($a['content'], 0, 140, '…')) ?></p></div>
      <span class="tiny faint"><?= e(time_ago($a['created_at'])) ?></span>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
