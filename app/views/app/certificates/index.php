<?php /* Certificates list */
?>
<div class="page-head">
  <div>
    <h1><?= icon('graduation') ?> Certificates</h1>
    <p class="sub">Completed courses — verifyable online with your code</p>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px">
  <?php foreach ($certs as $c): ?>
    <div class="card cert-card">
      <div class="cert-badge"><?= icon('graduation') ?></div>
      <h3 class="small" style="margin:8px 0 2px"><?= e($c['course_title']) ?></h3>
      <p class="tiny faint"><?= e($c['first_name'] . ' ' . $c['last_name']) ?> · <?= e($c['student_id']) ?></p>
      <div class="flex-between" style="margin-top:10px">
        <span class="tiny faint">Issued <?= e(date('M j, Y', strtotime($c['issued_at']))) ?></span>
        <span class="badge badge-success"><?= e($c['grade']) ?: 'A' ?></span>
      </div>
      <p class="tiny faint mono" style="margin-top:8px"><?= e($c['cert_code']) ?></p>
      <a class="btn btn-primary btn-block" style="margin-top:10px" href="<?= e(url('certificates/view&code=' . urlencode($c['cert_code']))) ?>" target="_blank"><?= icon('printer') ?> View / Print</a>
    </div>
  <?php endforeach; ?>
</div>
<?php if (!$certs): ?>
  <div class="card"><p class="muted" style="padding:20px">No certificates yet. Complete a course 100% to earn one! <?= icon('trophy') ?></p></div>
<?php endif; ?>
