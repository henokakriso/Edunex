<?php /* Security / 2FA */
$showHena = ($_GET['show'] ?? '') === 'hena' && !empty($hena_new);
?>
<div class="page-head">
  <div>
    <h1><?= icon('shield') ?> Security</h1>
    <p class="sub">Two-factor authentication</p>
  </div>
</div>

<div class="card" style="max-width:560px">
  <?php if (!empty($showHena)): ?>
    <h3 class="card-title" style="margin-top:0"><?= icon('key') ?> USB 2-key is ON — save your .hena file</h3>
    <p class="small muted">Your encrypted one-time key has been generated. Download it now and store it on your USB stick. You will need this file on every login — and after each login a new one is issued to replace it.</p>
    <div class="flex gap-8" style="margin-top:14px">
      <a class="btn btn-primary" href="<?= url('index.php?r=settings/hena_download&cv=' . time()) ?>"><?= icon('download') ?> Download .hena file</a>
    </div>
    <p class="tiny faint" style="margin-top:10px">Keep the file private. Anyone with it and your password can sign in as you.</p>
  <?php elseif ($mode === 'hena'): ?>
    <h3 class="card-title" style="margin-top:0"><?= icon('check-circle') ?> USB 2FA is ON — <?= icon('key') ?></h3>
    <p class="small muted">Signing in requires importing the encrypted .hena file from your USB stick. Each sign-in rotates the key, generating a file you must re-save.</p>
    <div class="flex gap-8" style="margin-top:14px">
      <form method="post" class="flex gap-8" style="margin-top:14px">
        <?= csrf_field() ?>
        <button class="btn btn-danger" name="disable_2fa" value="1">Disable USB 2FA</button>
      </form>
    </div>
  <?php elseif ($mode === 'totp'): ?>
    <h3 class="card-title" style="margin-top:0"><?= icon('check-circle') ?> Two-factor authentication is ON</h3>
    <p class="small muted">Every login (web and desktop app) requires a code from your authenticator app.</p>
    <form method="post" class="flex gap-8" style="margin-top:14px">
      <?= csrf_field() ?>
      <input class="input" style="flex:1;max-width:180px" name="code" placeholder="6-digit code" required>
      <button class="btn btn-danger" name="disable_2fa" value="1">Disable 2FA</button>
    </form>
  <?php else: ?>
    <h3 class="card-title" style="margin-top:0"><?= icon('unlock') ?> Two-factor authentication is OFF</h3>
    <p class="small muted">Add an extra layer of security: after enabling, every sign-in will ask you to import the encrypted .hena file from your USB stick — and issue a new key to replace it.</p>
    <form method="post" style="margin-top:14px">
      <?= csrf_field() ?>
      <button class="btn btn-primary" name="enable_2fa" value="1">Enable USB 2FA</button>
    </form>
  <?php endif; ?>
</div>