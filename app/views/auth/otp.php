<?php if (!empty($error)): ?><div class="alert alert-danger" style="margin-bottom:16px"><?= icon('ban-circle') ?> <?= e($error) ?></div><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  <div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= e($email) ?>" required></div>
  <div class="field"><label>One-time code</label><input class="input" name="code" inputmode="numeric" maxlength="6" placeholder="000000" required></div>
  <button class="btn btn-primary btn-lg" style="width:100%">Verify code</button>
</form>
