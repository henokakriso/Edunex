<?php if (!empty($error)): ?><div class="alert alert-danger" style="margin-bottom:16px"><?= icon('ban-circle') ?> <?= e($error) ?></div><?php endif; ?>
<div class="alert alert-success" style="margin-bottom:16px"><?= icon('check-circle') ?> You're signed in. Your USB key has been rotated for security.</div>
<div class="card text-center" style="padding:26px;max-width:420px;margin:0 auto">
  <h3 style="margin-top:0;margin-bottom:8px"><?= icon('key') ?> Save your new .hena key</h3>
  <p class="small muted" style="margin-bottom:18px">
    Download the refreshed key and replace the file on your USB stick — the previous file will no longer work.<br>
    <span style="opacity:.7">Account: <?= e($email ?? '') ?></span>
  </p>
  <a class="btn btn-primary" style="width:100%" href="<?= url('index.php?r=settings/hena_download') ?>"><?= icon('download') ?> Download .hena file</a>
  <a class="btn btn-ghost" style="width:100%;margin-top:10px" href="<?= url(dashboard_path()) ?>">Continue to dashboard</a>
</div>