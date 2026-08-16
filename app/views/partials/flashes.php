<?php if (!empty($flashes = flash_drain())): foreach ($flashes as $f): ?>
  <div class="alert alert-<?= e($f['type']) ?> flash" style="margin-bottom:14px">
    <span><?= match ($f['type']) { 'success' => icon('check-circle'), 'danger' => icon('ban-circle'), 'warning' => icon('alert'), default => 'ℹ' } ?></span>
    <span><?= e($f['msg']) ?></span>
  </div>
<?php endforeach; endif; ?>
