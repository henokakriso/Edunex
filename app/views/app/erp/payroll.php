<?php /* ERP Payroll — runs, entries, payslips, trend */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('banknote') ?> Payroll</h1>
    <p class="sub">Payroll runs, payslips and monthly totals</p>
  </div>
  <button class="btn btn-primary" data-open-modal="run-modal"><?= icon('plus') ?> New run</button>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title" style="margin-top:0"><?= icon('trend-up') ?> Monthly net payroll</h3>
  <div class="flex gap-14" style="flex-wrap:wrap">
    <?php foreach ($trend as $t): ?>
      <div class="flex-col">
        <span class="tiny faint"><?= e($t['period']) ?></span>
        <b><?= number_format((float)$t['total']) ?></b>
      </div>
    <?php endforeach; ?>
    <?php if (!$trend): ?><span class="muted small">No runs yet — create the first one.</span><?php endif; ?>
  </div>
</div>

<div class="card pad-0">
  <h3 class="card-title" style="padding:12px 14px 0"><?= icon('banknote') ?> Payroll runs</h3>
  <table class="table">
    <thead><tr><th>Period</th><th>Status</th><th>Entries</th><th>Total net</th><th>Created by</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($runs as $r): ?>
        <tr>
          <td><b><?= e($r['period']) ?></b><?= demo_badge($r) ?></td>
          <td><span class="badge badge-<?= $r['status'] === 'paid' ? 'success' : ($r['status'] === 'approved' ? 'info' : 'warning') ?>"><?= e($r['status']) ?></span></td>
          <td><?= (int)$r['entries'] ?></td>
          <td><b><?= number_format((float)$r['total']) ?> ETB</b></td>
          <td class="tiny"><?= e($r['by_first'] ? $r['by_first'] . ' ' . $r['by_last'] : '—') ?></td>
          <td class="flex gap-6">
            <?php if ($r['status'] === 'draft'): ?>
              <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="run_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-xs btn-success" name="approve_run" value="1"><?= icon('check') ?> Approve</button></form>
              <form method="post" class="inline" onsubmit="return confirm('Delete this draft run and its entries?')"><?= csrf_field() ?><input type="hidden" name="run_id" value="<?= (int)$r['id'] ?>"><button class="btn btn-xs btn-danger" name="delete_run" value="1">Delete</button></form>
            <?php else: ?><span class="tiny faint">—</span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$runs): ?><tr><td colspan="6" class="muted">No payroll runs yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="card pad-0" style="margin-top:18px">
  <h3 class="card-title" style="padding:12px 14px 0"><?= icon('file') ?> Payroll entries / payslips</h3>
  <table class="table">
    <thead><tr><th>Period</th><th>Employee</th><th>Position</th><th>Basic</th><th>Allowance</th><th>Deduction</th><th>Net</th><th>Bank</th></tr></thead>
    <tbody>
      <?php foreach ($entries as $e): ?>
        <tr>
          <td class="tiny"><?= e($e['period'] ?? '—') ?></td>
          <td><b><?= e($e['name']) ?></b><div class="tiny faint"><?= e($e['email']) ?></div></td>
          <td class="tiny"><?= e($e['position'] ?: '—') ?></td>
          <td><?= number_format((float)$e['basic']) ?></td>
          <td class="tiny">+ <?= number_format((float)$e['allowance']) ?></td>
          <td class="tiny">- <?= number_format((float)$e['deduction']) ?></td>
          <td><b><?= number_format((float)$e['net']) ?> ETB</b></td>
          <td class="tiny"><?= e($e['bank'] ?: '—') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$entries): ?><tr><td colspan="8" class="muted">No entries — create a run to generate payslips from active staff.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal" id="run-modal"><div class="modal-content">
  <h3><?= icon('plus') ?> New payroll run</h3>
  <form method="post" class="flex-col gap-6">
    <?= csrf_field() ?>
    <input class="input" type="month" name="period" value="<?= date('Y-m') ?>" required>
    <p class="tiny faint">Entries are generated automatically from active HR staff × position salary scale (allowance +15%, deduction −10%).</p>
    <button class="btn btn-primary" name="create_run" value="1"><?= icon('banknote') ?> Generate run</button>
  </form>
</div></div>
