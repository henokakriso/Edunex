<?php if (!empty($error)): ?><div class="alert alert-danger" style="margin-bottom:16px"><?= icon('ban-circle') ?> <?= e($error) ?></div><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="token" value="<?= e($token) ?>">
  <div class="field"><label>New password (min 8, uppercase + number)</label><input class="input" type="password" name="password" required autofocus></div>
  <div class="field"><label>Confirm new password</label><input class="input" type="password" name="password2" required></div>
  <button class="btn btn-primary btn-lg" style="width:100%">Update password</button>
</form>
