<?php /* Change password */
?>
<div class="page-head">
  <div>
    <h1><?= icon('key') ?> Change Password</h1>
    <p class="sub">Min 8 chars, one uppercase, one number</p>
  </div>
</div>

<form method="post" class="card" style="max-width:480px">
  <?= csrf_field() ?>
  <div class="flex-col"><label class="small faint">Current password *</label><input class="input" type="password" name="current" required></div>
  <div class="flex-col" style="margin-top:12px"><label class="small faint">New password *</label><input class="input" type="password" name="new" required></div>
  <div class="flex-col" style="margin-top:12px"><label class="small faint">Confirm new password *</label><input class="input" type="password" name="confirm" required></div>
  <button class="btn btn-primary" style="margin-top:14px"><?= icon('lock') ?> Change password</button>
</form>
