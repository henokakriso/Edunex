<?php /* Regional director profile — reset password, transfer between assigned schools */
?>
<div class="page-head">
  <div>
    <h1><?= icon('user') ?> <?= e($d['first_name'] . ' ' . $d['last_name']) ?></h1>
    <p class="sub"><?= e($d['email']) ?> · <span class="badge <?= $d['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($d['status']) ?></span></p>
  </div>
  <div class="flex gap-8">
    <form method="post">
      <?= csrf_field() ?>
      <button class="btn" name="reset_password" value="1" onclick="return confirm('Reset this director\u2019s password? A temporary password will be shown.')"><?= icon('refresh') ?> Reset password</button>
    </form>
    <form method="post">
      <?= csrf_field() ?>
      <label class="small faint">Transfer to</label>
      <div class="flex gap-6">
        <select class="input" name="to_school" required style="max-width:220px">
          <?php foreach ($schools as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= (int)$s['id'] === (int)$d['school_id'] ? 'disabled' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" name="transfer_director" value="1" onclick="return confirm('Move this director to the selected school?')"><?= icon('swap') ?> Transfer</button>
      </div>
    </form>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('user') ?> Profile</h3>
    <div class="flex-col gap-8 small">
      <div><b class="faint">School</b><p><?= e($d['school_name'] ?? '—') ?></p></div>
      <div><b class="faint">Phone</b><p><?= e($d['phone'] ?: '—') ?></p></div>
      <div><b class="faint">Joined</b><p><?= $d['created_at'] ? e(date('M j, Y', strtotime($d['created_at']))) : '—' ?></p></div>
      <div><b class="faint">Last login</b><p><?= $d['last_login'] ? e(time_ago($d['last_login'])) : 'never' ?></p></div>
    </div>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> Recent activity</h3>
    <?php foreach ($log as $l): ?>
      <div class="list-row" style="padding:7px 0">
        <div class="flex-1 small">
          <b><?= e($l['action']) ?></b>
          <p class="tiny faint"><?= e($l['detail']) ?> · <?= e(time_ago($l['created_at'])) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$log): ?><p class="muted small">No recent activity.</p><?php endif; ?>
  </div>
</div>
