<?php /* Student leaderboard view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('trophy') ?> Leaderboard</h1>
    <p class="sub">Top students by XP this year</p>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.6fr 1fr;gap:22px;align-items:start">
  <div class="card">
    <h3 class="card-title" style="margin-top:0">Top 10 students</h3>
    <?php foreach ($board as $i => $b): ?>
      <div class="list-row" style="padding:10px 0;<?= $me && (int)$b['id'] === (int)$me['id'] ? 'background:color-mix(in srgb,var(--accent) 8%,transparent);border-radius:10px;padding:10px 12px' : '' ?>">
        <span class="badge <?= $b['rank'] <= 3 ? 'badge-warning' : 'badge-muted' ?>">#<?= (int)$b['rank'] ?></span>
        <div class="avatar"><?= e(mb_substr((string)$b['name'], 0, 1)) ?></div>
        <div class="flex-1">
          <b class="small"><?= e($b['name']) ?><?= $me && (int)$b['id'] === (int)$me['id'] ? ' <span class="badge badge-accent">You</span>' : '' ?></b>
          <p class="tiny faint"><?= e($b['student_id'] ?? '') ?> · Level <?= (int)(floor(sqrt((float)$b['xp'] / 100)) + 1) ?></p>
        </div>
        <b class="small"><?= icon('bolt') ?> <?= (int)$b['xp'] ?> XP</b>
      </div>
    <?php endforeach; ?>
    <?php if (!$board): ?><p class="muted small">No students yet.</p><?php endif; ?>
  </div>

  <div class="card" style="position:sticky;top:90px">
    <h3 class="card-title" style="margin-top:0">ℹ How to earn XP</h3>
    <ul class="xp-list">
      <li><?= icon('book') ?> Complete a lesson — <b>10 XP</b></li>
      <li><?= icon('note') ?> Submit an assignment — <b>10 XP</b></li>
      <li><?= icon('edit') ?> Get an assignment graded — <b>15 XP</b></li>
      <li><?= icon('note') ?> Finish an exam — <b>15 XP</b></li>
      <li><?= icon('target') ?> Score 80%+ on an exam — <b>50 XP</b></li>
      <li><?= icon('books') ?> Enroll in a course — <b>20 XP</b></li>
      <li><?= icon('chat') ?> Post in the forum — <b>5 XP</b></li>
      <li><?= icon('flame') ?> Keep your daily streak going — <b>bonuses</b></li>
    </ul>
    <p class="tiny faint" style="margin-top:10px">Every 100 XP = 1 level. Badges unlock along the way — see <a class="accent" href="<?= e(url('gamification/badges')) ?>">your badges</a>.</p>
  </div>
</div>
