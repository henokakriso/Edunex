<?php /* Admin transfers view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('refresh') ?> Transfers & Referrals</h1>
    <p class="sub">University-to-university transfers and referral codes — Ministry managed</p>
  </div>
  <button class="btn btn-primary" data-open-modal="new-code-modal">+ Generate code</button>
</div>

<!-- Generate Code Modal -->
<div class="modal-backdrop" id="new-code-modal">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <h3>New Referral/Transfer Code</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="create_code" value="1">

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:14px">
          <div class="flex-col"><label class="small faint">Issuing university</label>
            <select class="input" name="school_id"><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
          </div>
          <div class="flex-col"><label class="small faint">Purpose</label>
            <select class="input" name="purpose"><option value="referral">Referral</option><option value="transfer">Transfer</option></select>
          </div>
          <div class="flex-col"><label class="small faint">Expires</label><input class="input" type="date" name="expires_at" value="<?= e(date('Y-m-d', time() + 90 * 86400)) ?>"></div>
        </div>

        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" type="submit"><?= icon('ticket') ?> Generate</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.4fr 1fr;gap:22px;align-items:start">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('mail') ?> Transfer requests</h3>
    <?php foreach ($requests as $req): ?>
      <div class="list-row" style="padding:12px 0;border-bottom:1px solid var(--border)">
        <div class="avatar"><?= icon('refresh') ?></div>
        <div class="flex-1">
          <a href="<?= e(url('admin/transfer&id=' . $req['id'])) ?>"><b class="small"><?= e($req['sf'] . ' ' . $req['sl']) ?></b></a>
          <span class="mono tiny faint"><?= e($req['student_id']) ?></span>
          <p class="tiny faint"><?= e($req['from_school']) ?> → <?= e($req['to_school']) ?><?= $req['referral_code'] ? ' · code ' . e($req['referral_code']) : '' ?><?= $req['reason'] ? '<br>' . e($req['reason']) : '' ?></p>
        </div>
        <span class="badge <?= $req['status'] === 'pending' ? 'badge-warning' : ($req['status'] === 'approved' ? 'badge-success' : 'badge-muted') ?>"><?= e($req['status']) ?></span>
        <div class="flex gap-8">
          <a class="btn btn-sm btn-ghost" href="<?= e(url('admin/transfer&id=' . $req['id'])) ?>"><?= icon('eye') ?></a>
          <?php if ($req['status'] === 'pending'): ?>
            <form method="post" class="inline" data-confirm="Approve transfer? Student moves to the new school.">
              <?= csrf_field() ?><button class="btn btn-sm btn-success" name="approve" value="<?= (int)$req['id'] ?>">✓</button>
            </form>
            <form method="post" class="inline">
              <?= csrf_field() ?><button class="btn btn-sm btn-danger" name="reject" value="<?= (int)$req['id'] ?>"><?= icon('x') ?></button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$requests): ?><p class="muted small">No transfer requests.</p><?php endif; ?>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('ticket') ?> Codes</h3>
    <?php foreach ($codes as $c): ?>
      <div class="list-row" style="padding:8px 0">
        <span class="mono small badge badge-accent"><?= e($c['code']) ?></span>
        <div class="flex-1 small">
          <b><?= e($c['school_name']) ?></b>
          <p class="tiny faint"><?= e($c['purpose']) ?> · <?= $c['used'] ? 'used by ' . e($c['used_by'] ?? '—') : 'available' ?> · expires <?= e(date('M j', strtotime($c['expires_at']))) ?></p>
        </div>
        <form method="post" class="inline" data-confirm="Delete this code?">
          <?= csrf_field() ?><input type="hidden" name="delete_code" value="<?= (int)$c['id'] ?>">
          <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
        </form>
      </div>
    <?php endforeach; ?>
    <?php if (!$codes): ?><p class="muted small">No codes generated.</p><?php endif; ?>
  </div>
</div>
