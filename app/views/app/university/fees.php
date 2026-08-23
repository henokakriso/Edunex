<?php /* Student fees view */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('dollar') ?> My Fees</h1>
    <p class="sub">Invoices and payment history</p>
  </div>
</div>

<div class="grid2" style="margin-bottom:18px">
  <div class="card">
    <h3 class="card-title"><?= icon('alert') ?> Outstanding Balance</h3>
    <div style="font-size:2em;font-weight:700;margin:8px 0;color:<?= $totalBalance > 0 ? 'var(--danger)' : 'var(--success)' ?>">$<?= number_format($totalBalance, 2) ?></div>
  </div>
  <div class="card">
    <h3 class="card-title"><?= icon('info') ?> Summary</h3>
    <p class="tiny">Total invoices: <?= count($invoices) ?></p>
    <p class="tiny">Total payments: <?= count($payments) ?></p>
  </div>
</div>

<h3 style="margin:18px 0 8px"><?= icon('file') ?> Invoices</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Semester</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Due</th></tr>
    </thead>
    <tbody>
      <?php foreach ($invoices as $inv): ?>
        <?php $bal = (float)$inv['total_amount'] - (float)$inv['paid_amount']; ?>
        <tr>
          <td><?= e($inv['sem_name']) ?></td>
          <td>$<?= number_format((float)$inv['total_amount'], 2) ?></td>
          <td>$<?= number_format((float)$inv['paid_amount'], 2) ?></td>
          <td style="color:<?= $bal > 0 ? 'var(--danger)' : 'var(--success)' ?>">$<?= number_format($bal, 2) ?></td>
          <td><span class="badge badge-<?= $inv['status'] === 'paid' ? 'success' : ($inv['status'] === 'overdue' ? 'danger' : 'warning') ?>"><?= e(ucfirst($inv['status'])) ?></span></td>
          <td class="tiny"><?= $inv['due_date'] ? e($inv['due_date']) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$invoices): ?><tr><td colspan="6" class="tiny faint">No invoices.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<h3 style="margin:18px 0 8px"><?= icon('cash') ?> Payment History</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th><th>Invoice</th></tr>
    </thead>
    <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td class="tiny"><?= e($p['paid_at']) ?></td>
          <td>$<?= number_format((float)$p['amount'], 2) ?></td>
          <td><?= e(ucfirst(str_replace('_', ' ', $p['payment_method']))) ?></td>
          <td class="tiny"><?= e($p['reference_number'] ?: '—') ?></td>
          <td>#<?= (int)$p['inv_id'] ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$payments): ?><tr><td colspan="5" class="tiny faint">No payments recorded.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
