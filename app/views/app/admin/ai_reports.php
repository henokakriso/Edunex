<?php /* Sysadmin: national AI usage report */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('robot') ?> National AI Report</h1>
    <p class="sub">AI usage across all institutions</p>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:18px">
  <?php foreach ([
      ['AI messages', $totals['msgs'], icon('messages')],
      ['Chats', $totals['chats'], icon('chat')],
      ['Active users', $totals['users'], icon('users')],
      ['Active (7d)', $totals['active7'], icon('clock')],
  ] as [$label, $val, $ic]): ?>
    <div class="card stat-card" style="padding:16px 14px">
      <div class="stat-icon" style="font-size:22px"><?= $ic ?></div>
      <div>
        <div class="stat-value" style="font-size:1.5rem"><?= (int)$val ?></div>
        <div class="small faint"><?= $label ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title" style="margin-top:0"><?= icon('brain') ?> AI narrative summary</h3>
  <p class="small"><?= e($narrative) ?></p>
</div>

<div class="grid2">
  <div class="card pad-0">
    <h3 class="card-title" style="padding:12px 14px 0"><?= icon('school') ?> By institution</h3>
    <table class="table">
      <thead><tr><th>School</th><th>Level</th><th>Users</th><th>Chats</th><th>Messages</th></tr></thead>
      <tbody>
        <?php foreach ($perSchool as $r): ?>
          <tr>
            <td><b><?= e($r['name']) ?></b></td>
            <td><span class="badge badge-info"><?= e($r['education_level']) ?></span></td>
            <td><?= (int)$r['users'] ?></td>
            <td><?= (int)$r['chats'] ?></td>
            <td><b><?= (int)$r['msgs'] ?></b></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$perSchool): ?><tr><td colspan="5" class="muted">No AI usage yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="flex-col gap-14">
    <div class="card pad-0">
      <h3 class="card-title" style="padding:12px 14px 0"><?= icon('trend-up') ?> By education level</h3>
      <table class="table">
        <thead><tr><th>Level</th><th>Chats</th><th>Messages</th></tr></thead>
        <tbody>
          <?php foreach ($perLevel as $r): ?>
            <tr><td><?= e($r['level']) ?></td><td><?= (int)$r['chats'] ?></td><td><b><?= (int)$r['msgs'] ?></b></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card pad-0">
      <h3 class="card-title" style="padding:12px 14px 0"><?= icon('users') ?> By role</h3>
      <table class="table">
        <thead><tr><th>Role</th><th>Chats</th><th>Messages</th></tr></thead>
        <tbody>
          <?php foreach ($perRole as $r): ?>
            <tr><td><?= e($r['role']) ?></td><td><?= (int)$r['chats'] ?></td><td><b><?= (int)$r['msgs'] ?></b></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
