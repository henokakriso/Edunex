<?php if ($sent): ?>
  <div class="alert alert-success" style="margin-bottom:16px"><?= icon('check-circle') ?> If an account exists for that email, a reset link is on its way. Check your inbox (and spam folder).</div>
<?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger" style="margin-bottom:16px"><?= icon('ban-circle') ?> <?= e($error) ?></div><?php endif; ?>
<?php if (!$sent): ?>
  <form method="post">
    <?= csrf_field() ?>
    <p class="muted small" style="margin-bottom:16px">Enter your account email and we'll send you a secure link to reset your password.</p>
    <div class="field"><label>Email</label><input class="input" type="email" name="email" required autofocus></div>
    <button class="btn btn-primary btn-lg" style="width:100%">Send reset link</button>
  </form>
  <p class="text-center small muted" style="margin-top:14px"><a href="<?= url('index.php?r=auth/login') ?>">← Back to sign in</a></p>
<?php endif; ?>
