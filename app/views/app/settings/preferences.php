<?php /* Preferences (theme/language) */
?>
<div class="page-head">
  <div>
    <h1><?= icon('palette') ?> Preferences</h1>
    <p class="sub">Appearance and language</p>
  </div>
</div>

<form method="post" class="card" style="max-width:480px">
  <?= csrf_field() ?>
  <label class="small faint">Theme</label>
  <div class="flex gap-8" style="margin-top:6px">
    <label class="theme-opt <?= ($__u['theme'] ?? 'dark') === 'dark' ? 'on' : '' ?>"><input type="radio" name="theme" value="dark" <?= ($__u['theme'] ?? 'dark') === 'dark' ? 'checked' : '' ?>> <?= icon('moon') ?> Dark (default)</label>
    <label class="theme-opt <?= ($__u['theme'] ?? 'dark') === 'light' ? 'on' : '' ?>"><input type="radio" name="theme" value="light" <?= ($__u['theme'] ?? 'dark') === 'light' ? 'checked' : '' ?>> <?= icon('sun') ?> Light</label>
  </div>
  <label class="small faint" style="display:block;margin-top:16px">Interface language</label>
  <select class="input" name="language" style="margin-top:6px"><?php foreach (['en' => 'English', 'am' => 'አማርኛ (Amharic)', 'om' => 'Afaan Oromoo', 'ti' => 'ትግርኛ (Tigrinya)', 'so' => 'Soomaali'] as $k => $v): ?><option value="<?= $k ?>" <?= ($__u['language'] ?? 'en') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
  <button class="btn btn-primary" style="margin-top:16px"><?= icon('save') ?> Save</button>
</form>
