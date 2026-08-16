<?php /* Admin transfers: approve + referral codes */
?>
<div class="page-head">
  <div>
    <h1><?= icon('refresh') ?> Transfers Admin</h1>
    <p class="sub">Approve transfer requests and issue referral codes</p>
  </div>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:18px;align-items:start">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('download') ?> Pending requests</h3>
    <?php foreach ($pending as $r): ?>
      <div class="list-row" style="padding:10px 0">
        <div class="flex-1">
          <b class="small"><?= e($r['first_name'] . ' ' . $r['last_name']) ?> <span class="mono faint"><?= e($r['student_id']) ?></span></b>
          <p class="tiny faint"><?= e($r['from_school']) ?> → <?= e($r['to_school']) ?><?= $r['referral_code'] ? ' · code: ' . e($r['referral_code']) : '' ?></p>
        </div>
        <div class="flex gap-8">
          <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-success" name="approve" value="<?= (int)$r['id'] ?>">✓ Approve</button></form>
          <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-danger" name="reject" value="<?= (int)$r['id'] ?>">✕ Reject</button></form>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$pending): ?><p class="muted small" style="padding:10px 0">No pending requests. <?= icon('spark') ?></p><?php endif; ?>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('ticket') ?> Referral codes</h3>
    <form method="post" class="flex gap-8">
      <?= csrf_field() ?>
      <input class="input" type="date" name="expires" style="width:150px" title="Expiry (optional)">
      <button class="btn btn-success" name="create_code" value="1">＋ Generate code</button>
    </form>
    <div style="margin-top:12px">
      <?php foreach ($codes as $c): ?>
        <div class="list-row" style="padding:7px 0">
          <b class="mono small"><?= e($c['code']) ?></b>
          <span class="tiny faint flex-1"><?= e($c['school_name']) ?><?= $c['used_by'] ? ' · used by ' . e($c['used_by']) : '' ?></span>
          <span class="badge <?= $c['used'] ? 'badge-success' : 'badge-muted' ?>"><?= $c['used'] ? 'used' : 'active' ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$codes): ?><p class="muted small">No codes yet.</p><?php endif; ?>
    </div>
  </div>
</div>
