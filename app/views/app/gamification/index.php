<?php /* Gamification hub view */
$isAdmin = ($__u['role'] ?? '') === 'ministry';
$isPlayer = $isPlayer ?? ($__u['role'] ?? '') === 'student';
?>
<?php if ($isAdmin): ?>
<div class="alert alert-info" style="margin-bottom:16px">
  <?= icon('medal') ?> You are signed in as an administrator. Students are the players of Edunex — <a href="<?= e(url('admin/badges')) ?>">manage badges & achievements here</a>. Students earn XP, levels, streaks and badges automatically.
</div>
<?php endif; ?>
<div class="page-head">
  <div>
    <h1><?= icon('game') ?> Gamification</h1>
    <p class="sub">Level <?= (int)$me['level'] ?> · <?= (int)$me['xp'] ?> XP · <?= icon('flame') ?> <?= (int)$me['streak'] ?> day streak</p>
  </div>
  <div class="flex gap-8">
    <a class="btn btn-ghost" href="<?= e(url('gamification/badges')) ?>"><?= icon('medal') ?> Badges</a>
    <a class="btn btn-ghost" href="<?= e(url('gamification/leaderboard')) ?>"><?= icon('trophy') ?> Leaderboard</a>
  </div>
</div>

<div class="card">
  <h3 class="card-title" style="margin-top:0"><?= icon('target') ?> Challenges</h3>
  <div class="flex-col gap-10">
    <?php foreach ($challenges as $ch): $pct = min(100, round($ch['x'] / max(1, (int)$ch['reward_xp']) * 100)); ?>
      <div class="flex gap-12" style="align-items:center">
        <span style="font-size:22px"><?= $ch['done'] ? icon('check-circle') : icon('target') ?></span>
        <div class="flex-1">
          <b class="small"><?= e($ch['title']) ?></b>
          <p class="tiny faint"><?= e($ch['description']) ?> · Reward: +<?= (int)$ch['reward_xp'] ?> XP</p>
          <div class="progress" style="height:8px"><div style="width:<?= $pct ?>%"></div></div>
        </div>
        <span class="tiny faint"><?= $ch['done'] ? '+' . $ch['earned'] . ' XP ✓' : $ch['x'] . ' / ' . (int)$ch['reward_xp'] ?></span>
      </div>
    <?php endforeach; ?>
    <?php if (!$challenges): ?><p class="muted small">No active challenges in your school.</p><?php endif; ?>
  </div>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:18px;margin-top:18px">
  <?php if ($isPlayer): ?>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('target') ?> My goals</h3>
    <?php foreach ($goals as $g): ?>
      <div class="list-row" style="padding:8px 0">
        <div class="flex-1"><b class="small"><?= e($g['title']) ?></b><p class="tiny faint"><?= e($g['unit']) ?> · <?= (int)$g['current'] ?> / <?= (int)$g['target'] ?><?= $g['due_date'] ? ' · due ' . e(date('M j', strtotime($g['due_date']))) : '' ?></p></div>
        <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-success" name="complete_goal" value="<?= (int)$g['id'] ?>">✓</button></form>
        <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-danger" name="delete_goal" value="<?= (int)$g['id'] ?>">✕</button></form>
      </div>
    <?php endforeach; ?>
    <?php if (!$goals): ?><p class="muted small">No active goals. Set one below!</p><?php endif; ?>
    <form method="post" class="flex gap-8" style="margin-top:12px">
      <?= csrf_field() ?>
      <input class="input" style="flex:1" name="goal" placeholder="e.g. Finish 5 lessons this week" required>
      <input class="input" style="width:90px" type="number" name="target" value="5" title="Target">
      <select class="input" style="width:110px" name="unit"><?php foreach (['lessons', 'xp', 'days', 'quizzes'] as $un): ?><option value="<?= $un ?>"><?= $un ?></option><?php endforeach; ?></select>
      <button class="btn btn-primary">+ Goal</button>
    </form>
  </div>
  <?php endif; ?>

  <div class="card <?= $isPlayer ? '' : 'full-width' ?>" style="<?= $isPlayer ? '' : 'grid-column:1/-1;max-width:560px;justify-self:center;width:100%' ?>">
    <h3 class="card-title" style="margin-top:0"><?= icon('trophy') ?> Top students</h3>
    <?php foreach ($top as $i => $s): $medal = $i === 0 ? 'medal-gold' : ($i === 1 ? 'medal-silver' : ($i === 2 ? 'medal-bronze' : '')); ?>
      <div class="list-row" style="padding:7px 0">
        <span class="tiny" style="width:24px"><?= $medal ? icon($medal) : ($i + 1) . '.' ?></span>
        <b class="small flex-1"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></b>
        <span class="tiny faint">Lv <?= (int)$s['level'] ?> · <?= (int)$s['xp'] ?> XP</span>
      </div>
    <?php endforeach; ?>
    <p class="tiny faint" style="margin-top:10px">Your rank: #<?= (int)$myRank ?></p>
  </div>
</div>
