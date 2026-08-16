<?php /* Admin settings view */
$groups = [
  'General' => ['site_name', 'tagline', 'contact_email', 'contact_phone', 'address', 'support_phone', 'currency'],
  'AI & Learning' => ['ai_enabled', 'ai_provider', 'ai_model', 'ai_api_url', 'ai_api_key'],
  'Policies' => ['registration_enabled', 'transfer_enabled', 'exam_grace', 'max_upload_mb'],
];
$inputs = [
  'site_name' => 'text', 'tagline' => 'text', 'contact_email' => 'email', 'contact_phone' => 'text',
  'address' => 'text', 'support_phone' => 'text', 'currency' => 'text',
  'ai_enabled' => 'select', 'ai_language' => 'select', 'registration_enabled' => 'select',
  'transfer_enabled' => 'select', 'exam_grace' => 'number', 'max_upload_mb' => 'number',
];
?>
<div class="page-head">
  <div>
    <h1><?= icon('gear') ?> System Settings</h1>
    <p class="sub">Global platform configuration</p>
  </div>
</div>

<form method="post" class="flex-col gap-16">
  <?= csrf_field() ?>
  <?php foreach ($groups as $title => $keys): ?>
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= e($title) ?></h3>
      <div class="grid2">
        <?php foreach ($keys as $k): ?>
          <div class="flex-col">
            <label class="small faint"><?= e(str_replace('_', ' ', ucfirst($k))) ?></label>
            <?php if (($inputs[$k] ?? 'text') === 'select'): ?>
              <select class="input" name="setting_<?= e($k) ?>">
                <option value="1" <?= $settings[$k] == '1' ? 'selected' : '' ?>>Enabled</option>
                <option value="0" <?= $settings[$k] != '1' ? 'selected' : '' ?>>Disabled</option>
              </select>
            <?php elseif ($k === 'ai_language'): ?>
              <select class="input" name="setting_<?= e($k) ?>">
                <?php foreach (['am' => 'Amharic', 'en' => 'English', 'om' => 'Oromo', 'ti' => 'Tigrinya', 'so' => 'Somali'] as $lv => $ln): ?>
                  <option value="<?= $lv ?>" <?= $settings[$k] === $lv ? 'selected' : '' ?>><?= $ln ?></option>
                <?php endforeach; ?>
              </select>
            <?php elseif ($k === 'ai_provider'): ?>
              <select class="input" name="setting_<?= e($k) ?>">
                <option value="local" <?= ($settings[$k] ?: 'local') === 'local' ? 'selected' : '' ?>>Local (offline, no key)</option>
                <option value="ollama" <?= ($settings[$k] ?? '') === 'ollama' ? 'selected' : '' ?>>Ollama C backend (llama.cpp models)</option>
                <option value="openai" <?= ($settings[$k] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI-compatible API</option>
              </select>
            <?php elseif ($k === 'ai_api_key'): ?>
              <input class="input" type="password" name="setting_<?= e($k) ?>" value="<?= e((string)$settings[$k]) ?>" placeholder="sk-…">
            <?php else: ?>
              <input class="input" type="<?= $inputs[$k] ?? 'text' ?>" name="setting_<?= e($k) ?>" value="<?= e((string)$settings[$k]) ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="flex gap-12">
    <button class="btn btn-primary btn-lg" name="save_settings" value="1"><?= icon('save') ?> Save all settings</button>
    <button class="btn btn-ghost" name="clear_cache" value="1"><?= icon('spark') ?> Clear cache</button>
  </div>
</form>
