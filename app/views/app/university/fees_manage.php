<?php /* Fee management for bursar */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('dollar') ?> Fee Management</h1>
    <p class="sub">Fee structures, invoices, and payments</p>
  </div>
</div>

<div class="grid2" style="margin-bottom:18px">
  <div class="card">
    <h3 class="card-title"><?= icon('plus') ?> Fee Structure</h3>
    <form method="post" action="<?= e(url('university/fees/manage')) ?>" class="flex-col gap-6" style="margin-top:6px">
      <?= csrf_field() ?>
      <input class="input" name="name" required placeholder="Tuition Fee" maxlength="100">
      <div class="grid2">
        <input class="input" type="number" name="amount" step="0.01" min="0" required placeholder="Amount">
        <select class="input" name="fee_type">
          <option value="fixed">Fixed</option>
          <option value="per_credit">Per Credit Hour</option>
          <option value="per_course">Per Course</option>
        </select>
      </div>
      <select class="input" name="semester_id">
        <option value="">— All semesters —</option>
        <?php foreach ($semesters as $s): ?>
          <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-success" name="create_fee" value="1"><?= icon('save') ?> Create</button>
    </form>
  </div>

  <div class="card">
    <h3 class="card-title"><?= icon('file') ?> Generate Invoice</h3>
    <form method="post" action="<?= e(url('university/fees/manage')) ?>" class="flex-col gap-6" style="margin-top:6px">
      <?= csrf_field() ?>
      <select class="input" name="student_id" required>
        <option value="">— Student —</option>
        <?php foreach ($students as $s): ?>
          <option value="<?= (int)$s['id'] ?>"><?= e($s['student_id']) ?> — <?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select class="input" name="semester_id" required>
        <option value="">— Semester —</option>
        <?php foreach ($semesters as $s): ?>
          <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary" name="generate_invoice" value="1"><?= icon('file') ?> Generate Invoice</button>
    </form>
  </div>
</div>

<h3 style="margin:18px 0 8px"><?= icon('list') ?> Fee Structures</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Name</th><th>Amount</th><th>Type</th><th>Applies To</th><th>Semester</th></tr>
    </thead>
    <tbody>
      <?php foreach ($feeStructures as $f): ?>
        <tr>
          <td><?= e($f['name']) ?></td>
          <td>$<?= number_format((float)$f['amount'], 2) ?></td>
          <td><?= e(ucfirst(str_replace('_', ' ', $f['fee_type']))) ?></td>
          <td><?= e($f['applies_to']) ?></td>
          <td class="tiny"><?= $f['semester_id'] ? e($f['semester_id']) : 'All' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$feeStructures): ?><tr><td colspan="5" class="tiny faint">No fee structures.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<h3 style="margin:18px 0 8px"><?= icon('file') ?> Invoices</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>#</th><th>Student</th><th>ID</th><th>Semester</th><th>Total</th><th>Paid</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($invoices as $inv): ?>
        <?php $bal = (float)$inv['total_amount'] - (float)$inv['paid_amount']; ?>
        <tr>
          <td>#<?= (int)$inv['id'] ?></td>
          <td><?= e($inv['student_name']) ?></td>
          <td><span class="badge"><?= e($inv['sid_no'] ?? '—') ?></span></td>
          <td class="tiny"><?= e($inv['sem_name']) ?></td>
          <td>$<?= number_format((float)$inv['total_amount'], 2) ?></td>
          <td>$<?= number_format((float)$inv['paid_amount'], 2) ?></td>
          <td><span class="badge badge-<?= $inv['status'] === 'paid' ? 'success' : ($inv['status'] === 'overdue' ? 'danger' : 'warning') ?>"><?= e(ucfirst($inv['status'])) ?></span></td>
          <td>
            <?php if ($inv['status'] !== 'paid'): ?>
              <form method="post" action="<?= e(url('university/fees/manage')) ?>" class="flex-row gap-4" style="align-items:center">
                <?= csrf_field() ?>
                <input type="hidden" name="invoice_id" value="<?= (int)$inv['id'] ?>">
                <input class="input" type="number" name="amount" step="0.01" min="0.01" max="<?= number_format($bal, 2, '.', '') ?>" placeholder="Amount" style="width:100px">
                <select class="input" name="method" style="width:110px">
                  <option value="cash">Cash</option>
                  <option value="bank_transfer">Bank</option>
                  <option value="mobile">Mobile</option>
                  <option value="online">Online</option>
                </select>
                <input class="input" name="reference" placeholder="Ref#" style="width:100px">
                <button class="btn btn-xs btn-success" name="record_payment" value="1"><?= icon('check') ?></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$invoices): ?><tr><td colspan="8" class="tiny faint">No invoices.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
