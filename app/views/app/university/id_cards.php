<?php /* Student ID cards */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('badge') ?> Student ID Cards</h1>
    <p class="sub">Generate and manage student identification cards</p>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title"><?= icon('plus') ?> Generate Card</h3>
  <form method="post" action="<?= e(url('university/id-cards')) ?>" class="flex-row gap-6" style="margin-top:6px">
    <?= csrf_field() ?>
    <select class="input" name="student_id" required style="flex:1">
      <option value="">— Select student —</option>
      <?php foreach ($students as $s): ?>
        <option value="<?= (int)$s['id'] ?>"><?= e($s['student_id']) ?> — <?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-success" name="generate_card" value="1"><?= icon('badge') ?> Generate</button>
  </form>
</div>

<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Card #</th><th>Student</th><th>ID</th><th>Barcode</th><th>Issued</th><th>Expires</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php foreach ($cards as $c): ?>
        <tr>
          <td><b><?= e($c['card_number']) ?></b></td>
          <td><?= e($c['student_name']) ?></td>
          <td class="tiny"><?= e($c['sid_no'] ?? '—') ?></td>
          <td class="tiny monospace"><?= e($c['barcode_data']) ?></td>
          <td class="tiny"><?= e($c['issued_at']) ?></td>
          <td class="tiny"><?= $c['expires_at'] ? e($c['expires_at']) : '—' ?></td>
          <td><span class="badge badge-<?= $c['status'] === 'active' ? 'success' : 'ghost' ?>"><?= e(ucfirst($c['status'])) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$cards): ?><tr><td colspan="7" class="tiny faint">No ID cards generated.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
