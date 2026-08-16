<?php /* Notification preferences */
?>
<div class="page-head">
  <div>
    <h1><?= icon('bell') ?> Notification Preferences</h1>
    <p class="sub">Choose what you want to be notified about</p>
  </div>
</div>

<form method="post" class="card" style="max-width:560px">
  <?= csrf_field() ?>
  <?php foreach ($types as [$key, $label]): $on = ($privacy['notify_' . $key] ?? '1') === '1'; ?>
    <label class="list-row" style="padding:10px 0;cursor:pointer">
      <span class="flex-1"><?= $label ?></span>
      <input type="checkbox" name="prefs[]" value="<?= $key ?>" <?= $on ? 'checked' : '' ?>>
    </label>
  <?php endforeach; ?>
  <button class="btn btn-primary" style="margin-top:14px"><?= icon('save') ?> Save preferences</button>
</form>
