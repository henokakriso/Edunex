<?php /* Admin system view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('monitor') ?> System Status</h1>
    <p class="sub">Environment and health check</p>
  </div>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:22px;align-items:start">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('monitor') ?> Environment</h3>
    <table class="table">
      <tbody>
        <?php foreach ($info as $k => $v): ?>
          <tr><td class="small faint"><?= e($k) ?></td><td class="small"><b><?= e((string)$v) ?></b></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('box') ?> Database tables (<?= count($tables) ?>)</h3>
    <div class="flex-col gap-6">
      <?php $total = 0; foreach ($tables as $name => $rows): $total += $rows; ?>
        <div class="flex-between small">
          <span class="mono"><?= e($name) ?></span>
          <span class="faint"><?= (int)$rows ?></span>
        </div>
      <?php endforeach; ?>
      <div style="height:16px"></div>
      <div class="flex-between small"><b>Total rows</b><b><?= (int)$total ?></b></div>
    </div>
  </div>
</div>
