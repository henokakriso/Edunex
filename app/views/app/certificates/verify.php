<?php /* Public certificate verification */
$ok = $result !== null;
?>
<div class="page-head">
  <div>
    <h1><?= icon('check-circle') ?> Certificate Verification</h1>
    <p class="sub">Verify any Edunex certificate using its unique code</p>
  </div>
</div>

<div class="card" style="max-width:620px;margin:0 auto">
  <form method="post" class="flex gap-8">
    <?= csrf_field() ?>
    <input class="input" style="flex:1;font-family:monospace" name="code" placeholder="CERT-XXXX-XXXX-XXXX" value="<?= e($_POST['code'] ?? '') ?>" required>
    <button class="btn btn-primary">Verify</button>
  </form>

  <?php if ($ok): ?>
    <?php if ($result): ?>
      <div class="alert alert-success" style="margin-top:18px">
        <b>✔ Valid certificate</b>
        <p class="small" style="margin:8px 0 0">
          <b><?= e($result['first_name'] . ' ' . $result['last_name']) ?></b> (<?= e($result['student_id']) ?>) successfully completed
          <b><?= e($result['course_title']) ?></b> at <b><?= e($result['school_name']) ?></b>,
          issued <?= e(date('F j, Y', strtotime($result['issued_at']))) ?>.
        </p>
        <p class="tiny faint" style="margin:4px 0 0">Roll Number: <?= e($result['student_id']) ?></p>
        <p class="tiny faint mono" style="margin:8px 0 0">Code: <?= e($result['cert_code']) ?> · Hash: <?= e(mb_strimwidth((string)$result['qr_hash'], 0, 16, '…')) ?></p>
      </div>
    <?php else: ?>
      <div class="alert alert-danger" style="margin-top:18px"><?= icon('x') ?> No certificate found with that code. Please double-check it.</div>
    <?php endif; ?>
  <?php else: ?>
    <p class="muted small" style="margin-top:18px">Enter the certificate code shown on the certificate document (e.g. <span class="mono">CERT-8F3A1B2C</span>).</p>
  <?php endif; ?>
</div>
