<?php if (!empty($error)): ?><div class="alert alert-danger" style="margin-bottom:16px"><?= icon('ban-circle') ?> <?= e($error) ?></div><?php endif; ?>
<div class="alert alert-info" style="margin-bottom:16px"><?= icon('mail') ?> We sent a 6-digit code to <b><?= e($email) ?></b></div>
<form method="post">
  <?= csrf_field() ?>
  <div class="field">
    <label>Verification code</label>
    <input class="input" name="code" inputmode="numeric" maxlength="6" placeholder="000000" style="text-align:center;font-size:22px;letter-spacing:10px;font-weight:800" required autofocus>
  </div>
  <button class="btn btn-primary btn-lg" style="width:100%">Verify email</button>
</form>
<form method="post" style="margin-top:12px">
  <?= csrf_field() ?>
  <input type="hidden" name="resend" value="1">
  <button class="btn btn-ghost" style="width:100%">Resend code</button>
</form>
