<?php /* Student clearance */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('check-circle') ?> My Clearance</h1>
    <p class="sub">Track your clearance status for graduation, transfer, or withdrawal</p>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title"><?= icon('plus') ?> Request Clearance</h3>
  <form method="post" action="<?= e(url('university/clearance')) ?>" class="flex-row gap-6" style="margin-top:6px">
    <?= csrf_field() ?>
    <select class="input" name="type" style="width:200px">
      <option value="graduation">Graduation</option>
      <option value="transfer">Transfer</option>
      <option value="withdrawal">Withdrawal</option>
    </select>
    <button class="btn btn-success" name="request_clearance" value="1"><?= icon('send') ?> Request Clearance</button>
  </form>
</div>

<?php foreach ($requests as $r): ?>
  <?php
    $statuses = ['pending' => 'warning', 'in_progress' => 'info', 'cleared' => 'success', 'rejected' => 'danger'];
    $badge = $statuses[$r['status']] ?? 'ghost';
  ?>
  <div class="card" style="margin-bottom:12px">
    <div class="flex-row gap-6" style="margin-bottom:8px">
      <span class="badge badge-<?= $badge ?>"><?= e(ucfirst($r['status'])) ?></span>
      <b><?= e($r['tracking_code']) ?></b>
      <span class="tiny faint" style="flex:1"><?= e(ucfirst($r['type'])) ?> — <?= e($r['requested_at']) ?></span>
      <span class="tiny"><?= (int)$r['passed'] ?>/<?= (int)$r['total'] ?> cleared</span>
    </div>
    <div class="progress-bar" style="height:6px;background:var(--border);border-radius:3px;margin-bottom:8px">
      <div style="height:100%;width:<?= (int)$r['total'] > 0 ? (int)$r['passed'] * 100 / (int)$r['total'] : 0 ?>%;background:var(--success);border-radius:3px"></div>
    </div>
    <?php if (!empty($items[$r['id']])): ?>
      <div style="display:flex;flex-wrap:wrap;gap:6px">
        <?php foreach ($items[$r['id']] as $it): ?>
          <?php $ic = ['passed' => 'success', 'failed' => 'danger', 'pending' => 'warning', 'not_applicable' => 'ghost']; ?>
          <span class="badge badge-<?= $ic[$it['status']] ?? 'ghost' ?>" title="<?= e($it['notes'] ?? '') ?>">
            <?= e(ucfirst($it['department'])) ?>: <?= e($it['status']) ?>
            <?php if ($it['checker_name']): ?> <span class="tiny faint">by <?= e($it['checker_name']) ?></span><?php endif; ?>
          </span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
<?php if (!$requests): ?><div class="card"><p class="tiny faint">No clearance requests yet.</p></div><?php endif; ?>
