<?php /* Clearance management for registrar/dean/etc */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('check-circle') ?> Clearance Management</h1>
    <p class="sub">Review and approve clearance requests</p>
  </div>
</div>

<div class="flex-row gap-6" style="margin-bottom:18px">
  <?php foreach (['pending','in_progress','cleared','rejected'] as $f): ?>
    <a href="<?= e(url('university/clearance/manage&status=' . $f)) ?>" class="btn btn-<?= $filter === $f ? 'primary' : 'ghost' ?> btn-sm"><?= e(ucfirst(str_replace('_', ' ', $f))) ?></a>
  <?php endforeach; ?>
</div>

<?php foreach ($requests as $r): ?>
  <div class="card" style="margin-bottom:12px">
    <div class="flex-row gap-6" style="margin-bottom:8px">
      <b><?= e($r['tracking_code']) ?></b>
      <span style="flex:1"><?= e($r['student_name']) ?> (<?= e($r['sid_no'] ?? '—') ?>)</span>
      <span class="badge badge-<?= $r['status'] === 'cleared' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= e(ucfirst($r['status'])) ?></span>
    </div>
    <p class="tiny faint"><?= e(ucfirst($r['type'])) ?> — Requested <?= e($r['requested_at']) ?></p>

    <?php if (!empty($items[$r['id']])): ?>
      <div style="display:flex;flex-direction:column;gap:6px;margin-top:8px">
        <?php foreach ($items[$r['id']] as $it): ?>
          <div class="flex-row gap-6" style="align-items:center;margin-top:8px;padding-top:6px">
            <span style="width:120px;font-weight:600;text-transform:capitalize"><?= e($it['department']) ?></span>
            <?php if ($it['status'] === 'pending'): ?>
              <form method="post" action="<?= e(url('university/clearance/manage')) ?>" class="flex-row gap-4" style="flex:1">
                <?= csrf_field() ?>
                <input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>">
                <input class="input" name="notes" placeholder="Notes (optional)" style="flex:1">
                <button class="btn btn-xs btn-success" name="status" value="passed"><?= icon('check') ?> Pass</button>
                <button class="btn btn-xs btn-danger" name="status" value="failed"><?= icon('x') ?> Fail</button>
              </form>
            <?php else: ?>
              <span class="badge badge-<?= $it['status'] === 'passed' ? 'success' : 'danger' ?>"><?= e(ucfirst($it['status'])) ?></span>
              <?php if ($it['checker_name']): ?><span class="tiny faint">by <?= e($it['checker_name']) ?></span><?php endif; ?>
              <?php if ($it['notes']): ?><span class="tiny faint">— <?= e($it['notes']) ?></span><?php endif; ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
<?php if (!$requests): ?><div class="card"><p class="tiny faint">No <?= e($filter) ?> clearance requests.</p></div><?php endif; ?>
