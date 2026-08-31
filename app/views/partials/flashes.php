<?php if (!empty($flashes = flash_drain())): ?>
<div class="toast-wrap">
<?php foreach ($flashes as $f): ?>
  <div class="toast <?= e($f['type']) ?>" style="position:relative">
    <span class="toast-ico" style="flex-shrink:0;display:flex;font-size:20px">
      <?= match ($f['type']) { 'success' => icon('check-circle'), 'error' => icon('ban-circle'), 'warning' => icon('alert'), default => icon('info') } ?>
    </span>
    <div class="toast-body">
      <div class="toast-title"><?= e(ucfirst($f['type'])) ?></div>
      <div class="toast-msg"><?= e($f['msg']) ?></div>
    </div>
    <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>
