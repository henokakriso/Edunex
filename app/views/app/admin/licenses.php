<?php /* Admin licenses — institution license management */
?>
<div class="page-head">
  <div>
    <h1><?= icon('ticket') ?> Licenses</h1>
    <p class="sub">Platform licensing — <?= count($rows) ?> issued</p>
  </div>
  <button class="btn btn-primary" data-open-modal="new-license-modal"><?= icon('plus') ?> Issue license</button>
</div>

<div class="table-wrap">
  <?php $liSortUrl = fn($col) => url('admin/licenses?' . http_build_query(['sort'=>$col, 'dir'=> $sort===$col && $dir==='asc' ? 'desc' : 'asc'])); ?>
  <table class="table">
    <thead><tr>
      <th class="col-num">#</th>
      <th>License key</th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('institution')) ?>">Institution<span class="sort-arrow<?= $sort==='institution' ? ' active' : '' ?>"><?= $sort==='institution' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('type')) ?>">Type<span class="sort-arrow<?= $sort==='type' ? ' active' : '' ?>"><?= $sort==='type' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('seats')) ?>">Seats<span class="sort-arrow<?= $sort==='seats' ? ' active' : '' ?>"><?= $sort==='seats' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('issued')) ?>">Issued<span class="sort-arrow<?= $sort==='issued' ? ' active' : '' ?>"><?= $sort==='issued' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('expires')) ?>">Expires<span class="sort-arrow<?= $sort==='expires' ? ' active' : '' ?>"><?= $sort==='expires' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($liSortUrl('status')) ?>">Status<span class="sort-arrow<?= $sort==='status' ? ' active' : '' ?>"><?= $sort==='status' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th style="width:120px">Actions</th>
    </tr></thead>
    <tbody>
      <?php $i = 0; foreach ($rows as $l): ?>
        <tr>
          <td class="col-num"><?= $i + 1 ?></td>
          <td><code style="font-size:.85em"><?= e($l['license_key']) ?></code></td>
          <td><?= e($l['institution'] ?: '—') ?><?= $l['school_name'] ? '<p class="tiny faint">' . e($l['school_name']) . '</p>' : '' ?></td>
          <td><span class="badge"><?= e($l['type']) ?></span></td>
          <td><?= (int)$l['seats'] ?></td>
          <td class="small faint"><?= e($l['issued_at'] ?: '—') ?></td>
          <td class="small <?= $l['expires_at'] && $l['expires_at'] < date('Y-m-d') ? 'danger' : 'faint' ?>"><?= e($l['expires_at'] ?: '—') ?></td>
          <td><span class="badge <?= $l['status'] === 'active' ? 'badge-success' : ($l['status'] === 'expired' ? '' : 'badge-warning') ?>"><?= e($l['status']) ?></span></td>
          <td>
            <div class="flex gap-6">
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm" name="toggle_license" value="<?= (int)$l['id'] ?>" onclick="return confirm('<?= $l['status'] === 'active' ? 'Suspend' : 'Reactivate' ?> this license?')"><?= $l['status'] === 'active' ? icon('pause') . ' Suspend' : icon('check') . ' Activate' ?></button>
              </form>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-danger" name="delete_license" value="<?= (int)$l['id'] ?>" onclick="return confirm('Delete this license?')"><?= icon('trash') ?></button>
              </form>
            </div>
          </td>
        </tr>
      <?php $i++; endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="9" class="muted">No licenses issued yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

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
        <select class="input" name="type">
          <option value="trial">Trial</option><option value="standard">Standard</option>
          <option value="premium">Premium</option><option value="enterprise">Enterprise</option>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">Seats (users)</label><input class="input" type="number" name="seats" min="0" value="0"></div>
      <div class="flex-col"><label class="small faint">Issued</label><input class="input" type="date" name="issued_at" value="<?= date('Y-m-d') ?>"></div>
      <div class="flex-col"><label class="small faint">Expires</label><input class="input" type="date" name="expires_at"></div>
    </div>
    <p class="tiny faint" style="margin:10px 0 0">A unique license key is generated automatically on issue.</p>
    <div class="flex gap-10" style="margin-top:14px">
      <button class="btn btn-success" name="create_license" value="1"><?= icon('rocket') ?> Issue</button>
      <button type="button" class="btn btn-ghost" data-close-modal="new-license-modal">Cancel</button>
    </div>
  </form>
</div>
