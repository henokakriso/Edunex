<?php /* Leaderboard view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('trophy') ?> Leaderboard</h1>
    <p class="sub">XP race</p>
  </div>
  <div class="flex gap-8" style="flex-wrap:wrap">
    <?php foreach (['school' => 'My school', 'all' => 'All schools'] as $k => $v): ?>
      <a class="btn <?= $scope === $k ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('gamification/leaderboard&scope=' . $k . '&role=' . $role)) ?>"><?= $v ?></a>
    <?php endforeach; ?>
    <span class="spacer"></span>
    <?php foreach (['student' => icon('graduation') . ' Students', 'teacher' => icon('user') . '‍' . icon('school') . ' Teachers', 'parent' => icon('users') . ' Parents'] as $k => $v): ?>
      <a class="btn <?= $role === $k ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('gamification/leaderboard&scope=' . $scope . '&role=' . $k)) ?>"><?= $v ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <?php foreach ($list as $i => $s): $badge = $i === 0 ? icon('medal') : ($i === 1 ? icon('medal') : ($i === 2 ? icon('medal') : '')); ?>
    <div class="list-row" style="padding:9px 0;<?= (int)$s['id'] === (int)$__u['id'] ? 'background:var(--surface2);border-radius:8px;padding-left:8px' : '' ?>">
      <span class="tiny" style="width:34px"><?= $badge ?: ($i + 1) . '.' ?></span>
      <div class="avatar"><?= e(mb_substr((string)($s['first_name'] . ' ' . $s['last_name']), 0, 1)) ?></div>
      <b class="small flex-1"><?= e($s['first_name'] . ' ' . $s['last_name']) ?><?= (int)$s['id'] === (int)$__u['id'] ? ' <span class="badge badge-accent">you</span>' : '' ?></b>
      <span class="tiny faint">Lv <?= (int)$s['level'] ?></span>
      <div class="progress" style="width:120px;margin:0 12px"><div style="width:<?= min(100, (int)$s['xp'] / 5) ?>%"></div></div>
      <b class="small accent"><?= (int)$s['xp'] ?> XP</b>
    </div>
  <?php endforeach; ?>
  <?php if (!$list): ?><p class="muted small">No students yet.</p><?php endif; ?>
</div>
