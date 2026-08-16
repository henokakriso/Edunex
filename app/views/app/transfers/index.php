<?php /* Transfer history */
$statusCls = ['pending' => 'badge-muted', 'approved' => 'badge-success', 'rejected' => 'badge-danger', 'completed' => 'badge-accent', 'cancelled' => 'badge-muted'];
?>
<div class="page-head">
  <div>
    <h1><?= icon('refresh') ?> School Transfers</h1>
    <p class="sub">Your special transferable ID: <b class="mono"><?= e($student['student_id']) ?></b></p>
  </div>
  <a class="btn btn-primary" href="<?= e(url('transfers/new')) ?>">+ Request transfer</a>
</div>

<div class="alert alert-info">
  <?= icon('graduation') ?> Your student ID is <b>transferable</b> — it moves with you between schools. Ask the target school for a <b>referral code</b> to skip the waiting period.
</div>

<div class="card">
  <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Transfer history</h3>
  <?php foreach ($requests as $r): ?>
    <div class="list-row" style="padding:11px 0">
      <span style="font-size:18px"><?= icon('train') ?></span>
      <div class="flex-1">
        <b class="small"><?= e($r['from_school']) ?> → <?= e($r['to_school']) ?></b>
        <p class="tiny faint"><?= e(date('M j, Y H:i', strtotime($r['created_at']))) ?><?= $r['reason'] ? ' · ' . e(mb_strimwidth((string)$r['reason'], 0, 60, '…')) : '' ?><?= $r['referral_code'] ? ' · code ' . e($r['referral_code']) : '' ?></p>
      </div>
      <span class="badge <?= $statusCls[$r['status']] ?? 'badge-muted' ?>"><?= e($r['status']) ?></span>
      <?php if ($r['status'] === 'pending'): ?>
        <form method="post" class="inline" data-confirm="Cancel this transfer request?">
          <?= csrf_field() ?>
          <input type="hidden" name="cancel_transfer" value="<?= (int)$r['id'] ?>">
          <button class="btn btn-sm btn-outline"><?= icon('x') ?> Cancel</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if (!$requests): ?><p class="muted small" style="padding:14px">No transfers yet.</p><?php endif; ?>
</div>
