<?php /* Login view */ ?>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger" style="margin-bottom:16px"><?= icon('ban-circle') ?> <?= e($error) ?></div>
<?php endif; ?>

<form method="post">
  <?= csrf_field() ?>
  <div class="field">
    <label>Student ID, email or phone</label>
    <input class="input" name="identifier" value="<?= e($id ?? '') ?>" placeholder="AAIS-2026-000001 or you@school.edu.et" required autofocus>
  </div>
  <div class="field">
    <div class="flex-between">
      <label>Password</label>
      <a class="tiny" href="<?= url('index.php?r=auth/forgot') ?>">Forgot password?</a>
    </div>
    <input class="input" type="password" name="password" placeholder="••••••••" required>
  </div>
  <label class="check" style="margin-bottom:18px">
    <input type="checkbox" name="remember" value="1"> Keep me signed in for 30 days
  </label>
  <button class="btn btn-primary btn-lg" style="width:100%">Sign in →</button>
</form>

<div class="divider"></div>
<p class="text-center small muted">New to Edunex? <a href="<?= url('index.php?r=auth/register') ?>">Create an account</a></p>
<p class="text-center tiny faint" style="margin-top:12px">Sign in with your Student ID, Email or Phone number.</p>
