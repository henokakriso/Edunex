<?php /* Public degree verification */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('certificate') ?> Degree Verification</h1>
    <p class="sub">Verify an EDUNEX degree using its verification code</p>
  </div>
</div>

<div class="card" style="max-width:560px">
  <form method="get" class="flex gap-6">
    <input class="input" name="code" value="<?= e($code) ?>" placeholder="EDG-XXXXXXXXXXXX" style="flex:1;font-family:monospace" required>
    <button class="btn btn-primary"><?= icon('search') ?> Verify</button>
  </form>
</div>

<?php if ($code !== ''): ?>
  <?php if ($result): ?>
    <div class="card" style="max-width:560px">
      <span class="badge badge-success" style="font-size:0.95rem"><?= icon('check') ?> VALID DEGREE</span>
      <table class="table" style="margin-top:10px">
        <tr><th class="small faint">Graduate</th><td><b><?= e($result['student']) ?></b></td></tr>
        <tr><th class="small faint">Student ID</th><td><?= e($result['student_id']) ?></td></tr>
        <tr><th class="small faint">Degree</th><td><?= e($result['degree_name']) ?></td></tr>
        <tr><th class="small faint">Institution</th><td><?= e($result['school_name']) ?></td></tr>
        <tr><th class="small faint">Issued</th><td><?= e(date('F j, Y', strtotime($result['issued_at']))) ?></td></tr>
        <tr><th class="small faint">Verification code</th><td style="font-family:monospace"><?= e($result['degree_code']) ?></td></tr>
      </table>
    </div>
  <?php else: ?>
    <div class="card" style="max-width:560px">
      <span class="badge badge-danger"><?= icon('close') ?> No degree found for code <?= e($code) ?></span>
      <p class="small faint" style="margin-top:8px">Check the code and try again, or contact the issuing institution.</p>
    </div>
  <?php endif; ?>
<?php endif; ?>
