<?php /* Active sessions */
$me = $_SERVER['REMOTE_ADDR'] ?? '';
?>
<div class="page-head">
  <div>
    <h1><?= icon('monitor') ?> Active Sessions</h1>
    <p class="sub">Devices where you're signed in</p>
  </div>
  <form method="post" data-confirm="Revoke ALL sessions? You'll be logged out everywhere."><?= csrf_field() ?><button class="btn btn-danger" name="kill_all" value="1">Revoke all</button></form>
</div>

<div class="card" style="max-width:640px">
  <?php foreach ($sessions as $s): ?>
    <div class="list-row" style="padding:10px 0">
      <span style="font-size:18px"><?= $s['ip'] === $me ? icon('monitor') : icon('phone') ?></span>
      <div class="flex-1">
        <b class="small"><?= e($s['user_agent'] ?: 'Unknown device') ?></b>
        <p class="tiny faint">IP <?= e($s['ip']) ?> · <?= e(date('M j, H:i', strtotime($s['created_at']))) ?> · expires <?= e(date('M j', strtotime($s['expires_at']))) ?></p>
      </div>
      <?php if ($s['ip'] === $me): ?><span class="badge badge-accent">this device</span><?php endif; ?>
      <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" name="kill" value="<?= (int)$s['id'] ?>">Revoke</button></form>
    </div>
  <?php endforeach; ?>
  <?php if (!$sessions): ?><p class="muted small" style="padding:14px">No remembered sessions.</p><?php endif; ?>
</div>
