<?php if (!empty($error)): ?><div class="alert alert-danger" style="margin-bottom:16px"><?= icon('ban-circle') ?> <?= e($error) ?></div><?php endif; ?>
<?php if (!empty($use_hena)): ?>
<div class="alert alert-info" style="margin-bottom:16px"><?= icon('key') ?> Insert your USB key and import the encrypted <strong>.hena</strong> file to finish signing in.</div>
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="field">
    <label>USB .hena key file</label>
    <input class="input" type="file" name="hena_file" accept=".hena" style="padding:10px;font-size:15px" required>
    <small style="color:var(--muted)">Your key rotates on every sign-in — after logging in, save the new .hena file to your USB stick.</small>
  </div>
  <button class="btn btn-primary btn-lg" style="width:100%">Unlock with USB key</button>
</form>
<?php else: ?>
<div class="alert alert-info" style="margin-bottom:16px"><?= icon('lock') ?> Enter the 6-digit code from your authenticator app.</div>
<form method="post">
  <?= csrf_field() ?>
  <div class="field">
    <label>Authenticator code</label>
    <input class="input" name="code" inputmode="numeric" maxlength="6" placeholder="000000" style="text-align:center;font-size:22px;letter-spacing:10px;font-weight:800" required autofocus>
  </div>
  <button class="btn btn-primary btn-lg" style="width:100%">Verify</button>
</form>
<?php endif; ?>