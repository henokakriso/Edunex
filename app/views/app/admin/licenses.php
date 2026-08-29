<?php /* Admin licenses — institution license management with tier system */
$allTiers = ['trial','standard','premium','enterprise'];
$tierColors = ['trial'=>'indigo','standard'=>'blue','premium'=>'amber','enterprise'=>'emerald'];
$tierLabels = ['trial'=>'Trial','standard'=>'Standard','premium'=>'Premium','enterprise'=>'Enterprise'];
$tierIcons = ['trial'=>'seedling','standard'=>'shield','premium'=>'star','enterprise'=>'rocket'];

// Get module counts per tier
$tierCounts = Database::all("SELECT tier, COUNT(*) AS cnt FROM license_tier_features GROUP BY tier");
$cntMap = array_column($tierCounts, 'cnt', 'tier');

// Auto-expire check
license_auto_expire();

// Stats
$active = 0; $expiring = 0; $expired = 0;
foreach ($rows as $l) {
    if ($l['status'] === 'active') $active++;
    if ($l['status'] === 'expired') $expired++;
    if ($l['status'] === 'active' && $l['expires_at'] && (strtotime($l['expires_at']) - time()) / 86400 <= 30) $expiring++;
}
?>
<div class="page-head">
  <div>
    <h1><?= icon('ticket') ?> Licenses</h1>
    <p class="sub">Platform licensing — <?= count($rows) ?> issued</p>
  </div>
  <div class="flex gap-8">
    <button class="btn" data-open-modal="tiers-modal"><?= icon('layers') ?> Tier comparison</button>
    <button class="btn btn-primary" data-open-modal="new-license-modal"><?= icon('plus') ?> Issue license</button>
  </div>
</div>

<!-- Stats cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:18px">
  <div class="card" style="padding:16px;text-align:center">
    <div class="stat-label">Active</div>
    <div class="stat-num" style="color:var(--c-success)"><?= $active ?></div>
  </div>
  <div class="card" style="padding:16px;text-align:center">
    <div class="stat-label">Expiring Soon</div>
    <div class="stat-num" style="color:var(--c-warning)"><?= $expiring ?></div>
  </div>
  <div class="card" style="padding:16px;text-align:center">
    <div class="stat-label">Expired</div>
    <div class="stat-num" style="color:var(--c-danger)"><?= $expired ?></div>
  </div>
  <div class="card" style="padding:16px;text-align:center">
    <div class="stat-label">Total Seats Used</div>
    <div class="stat-num"><?= array_sum(array_map(fn($l) => (int)$l['seats'], $rows)) ?></div>
  </div>
</div>

<!-- Tier cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:20px">
  <?php foreach ($allTiers as $t): ?>
    <?php
      $licCount = count(array_filter($rows, fn($l) => $l['type'] === $t));
      $color = $tierColors[$t];
    ?>
    <div class="card" style="padding:18px;border-left:4px solid var(--c-<?= $color ?>)">
      <div class="flex gap-8" style="align-items:center;margin-bottom:6px">
        <span style="font-size:1.2em;color:var(--c-<?= $color ?>)"><?= icon($tierIcons[$t]) ?></span>
        <strong style="font-size:1.05em"><?= $tierLabels[$t] ?></strong>
      </div>
      <div class="flex gap-12" style="margin-top:6px">
        <span class="tiny faint"><?= $cntMap[$t] ?? 0 ?> modules</span>
        <span class="tiny faint"><?= $licCount ?> licenses</span>
      </div>
      <div class="flex gap-12" style="margin-top:4px">
        <span class="tiny faint">Seats: <?= Database::scalar("SELECT max_seats FROM license_tier_features WHERE tier=? LIMIT 1",[$t],0) ?: '∞' ?></span>
        <span class="tiny faint">Schools: <?= Database::scalar("SELECT max_schools FROM license_tier_features WHERE tier=? LIMIT 1",[$t],0) ?: '∞' ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Licenses table -->
<div class="table-wrap">
  <?php $liSortUrl = fn($col) => url('admin/licenses?' . http_build_query(['sort'=>$col, 'dir'=> $sort===$col && $dir==='asc' ? 'desc' : 'asc'])); ?>
  <table class="table">
    <thead><tr>
      <th class="col-num">#</th>
      <th>License key</th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('institution')) ?>">Institution<span class="sort-arrow<?= $sort==='institution' ? ' active' : '' ?>"><?= $sort==='institution' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('type')) ?>">Type<span class="sort-arrow<?= $sort==='type' ? ' active' : '' ?>"><?= $sort==='type' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th>Seats</th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('issued')) ?>">Issued<span class="sort-arrow<?= $sort==='issued' ? ' active' : '' ?>"><?= $sort==='issued' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('expires')) ?>">Expires<span class="sort-arrow<?= $sort==='expires' ? ' active' : '' ?>"><?= $sort==='expires' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('status')) ?>">Status<span class="sort-arrow<?= $sort==='status' ? ' active' : '' ?>"><?= $sort==='status' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th style="width:120px">Actions</th>
    </tr></thead>
    <tbody>
      <?php $i = 0; foreach ($rows as $l): ?>
        <?php
          $lic = license_for_school((int)$l['school_id']);
          $usage = $l['school_id'] ? license_seat_usage((int)$l['school_id']) : null;
          $daysLeft = $l['expires_at'] ? (int)((strtotime($l['expires_at']) - time()) / 86400) : null;
        ?>
        <tr>
          <td class="col-num"><?= $i + 1 ?></td>
          <td><code style="font-size:.85em"><?= e($l['license_key']) ?></code></td>
          <td>
            <?= e($l['institution'] ?: '—') ?>
            <?php if ($l['school_name']): ?><p class="tiny faint"><?= e($l['school_name']) ?></p><?php endif; ?>
          </td>
          <td><span class="badge badge-<?= $tierColors[$l['type']] ?? '' ?>"><?= e($tierLabels[$l['type']] ?? $l['type']) ?></span></td>
          <td>
            <?php if ($usage && $usage['limit'] > 0): ?>
              <div class="flex gap-4" style="align-items:center">
                <span class="tiny"><?= $usage['used'] ?>/<?= $usage['limit'] ?></span>
                <div style="width:50px;height:6px;border-radius:3px;background:var(--c-border);overflow:hidden">
                  <div style="width:<?= min(100, $usage['pct']) ?>%;height:100%;border-radius:3px;background:<?= $usage['pct'] > 90 ? 'var(--c-danger)' : ($usage['pct'] > 70 ? 'var(--c-warning)' : 'var(--c-success)') ?>;transition:width .3s"></div>
                </div>
              </div>
            <?php else: ?>
              <span class="tiny faint"><?= (int)$l['seats'] ?: '∞' ?></span>
            <?php endif; ?>
          </td>
          <td class="small faint"><?= e($l['issued_at'] ?: '—') ?></td>
          <td class="small <?= $daysLeft !== null && $daysLeft <= 0 ? 'danger' : ($daysLeft !== null && $daysLeft <= 30 ? 'warning' : 'faint') ?>">
            <?= e($l['expires_at'] ?: '—') ?>
            <?php if ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30): ?>
              <span class="tiny">(<?= $daysLeft ?>d)</span>
            <?php endif; ?>
          </td>
          <td><span class="badge <?= $l['status'] === 'active' ? 'badge-success' : ($l['status'] === 'expired' ? 'badge-danger' : 'badge-warning') ?>"><?= e($l['status']) ?></span></td>
          <td>
            <div class="flex gap-6">
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm" name="toggle_license" value="<?= (int)$l['id'] ?>" onclick="return confirm('<?= $l['status'] === 'active' ? 'Suspend' : 'Reactivate' ?> this license?')"><?= $l['status'] === 'active' ? icon('pause') : icon('check') ?></button>
              </form>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-ghost" name="delete_license" value="<?= (int)$l['id'] ?>" onclick="return confirm('Delete this license?')"><?= icon('trash') ?></button>
              </form>
            </div>
          </td>
        </tr>
      <?php $i++; endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="9" class="muted">No licenses issued yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Tier Comparison Modal -->
<div class="modal-dialog" id="tiers-modal">
  <div class="modal-box" style="max-width:700px;padding:22px">
    <h3 class="card-title"><?= icon('layers') ?> Tier Comparison</h3>
    <p class="tiny faint" style="margin:4px 0 14px">Modules available per license tier</p>
    <?php
    $allModules = Database::all("SELECT DISTINCT module_key FROM license_tier_features ORDER BY module_key");
    $tierModules = [];
    foreach ($allTiers as $t) {
        $rows2 = Database::all("SELECT module_key, max_seats, max_schools FROM license_tier_features WHERE tier=?", [$t]);
        $tierModules[$t] = array_column($rows2, 'module_key');
    }
    ?>
    <div style="max-height:60vh;overflow-y:auto">
      <table class="table" style="font-size:.85em">
        <thead><tr>
          <th>Module</th>
          <?php foreach ($allTiers as $t): ?>
            <th style="text-align:center"><span class="badge badge-<?= $tierColors[$t] ?>"><?= $tierLabels[$t] ?></span></th>
          <?php endforeach; ?>
        </tr></thead>
        <tbody>
          <?php foreach ($allModules as $m): ?>
            <tr>
              <td><?= e(ucwords(str_replace('-', ' ', $m['module_key']))) ?></td>
              <?php foreach ($allTiers as $t): ?>
                <td style="text-align:center">
                  <?php if (in_array($m['module_key'], $tierModules[$t])): ?>
                    <span style="color:var(--c-success)"><?= icon('check') ?></span>
                  <?php else: ?>
                    <span style="color:var(--c-border)">—</span>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="flex gap-10" style="margin-top:14px;justify-content:flex-end">
      <button class="btn btn-ghost" data-close-modal="tiers-modal">Close</button>
    </div>
  </div>
</div>

<!-- Issue license modal -->
<div class="modal-dialog" id="new-license-modal">
  <form method="post" class="modal-box" style="padding:22px">
    <?= csrf_field() ?>
    <h3 class="card-title"><?= icon('ticket') ?> Issue license</h3>
    <div class="grid2" style="margin-top:6px">
      <div class="flex-col"><label class="small faint">Institution *</label><input class="input" name="institution" required></div>
      <div class="flex-col"><label class="small faint">Linked school</label>
        <select class="input" name="school_id"><option value="0">— Platform-wide —</option>
          <?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">Type</label>
        <select class="input" name="type" id="license-type-select">
          <?php foreach ($allTiers as $t): ?>
            <option value="<?= $t ?>"><?= $tierLabels[$t] ?> — <?= $cntMap[$t] ?? 0 ?> modules, <?= Database::scalar("SELECT max_seats FROM license_tier_features WHERE tier=?",[$t],0) ?: '∞' ?> seats</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">Seats (users)</label><input class="input" type="number" name="seats" min="0" value="0" id="license-seats"></div>
      <div class="flex-col"><label class="small faint">Issued</label><input class="input" type="date" name="issued_at" value="<?= date('Y-m-d') ?>"></div>
      <div class="flex-col"><label class="small faint">Expires</label><input class="input" type="date" name="expires_at"></div>
    </div>
    <p class="tiny faint" style="margin:10px 0 0">License key is auto-generated. Seat limit shown per tier.</p>
    <div class="flex gap-10" style="margin-top:14px">
      <button class="btn btn-success" name="create_license" value="1"><?= icon('rocket') ?> Issue</button>
      <button type="button" class="btn btn-ghost" data-close-modal="new-license-modal">Cancel</button>
    </div>
  </form>
</div>
