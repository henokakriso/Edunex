<?php /* Regional backups — platform backup files (same dump used for your schools) */
?>
<div class="page-head">
  <div>
    <h1><?= icon('save') ?> Backups</h1>
    <p class="sub">Database snapshots — your schools are covered by these platform-wide dumps</p>
  </div>
</div>

<div class="card">
  <h3 class="card-title" style="margin-top:0"><?= icon('files') ?> Available backups</h3>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>File</th><th>Size</th><th>Created</th><th style="width:120px">Action</th></tr></thead>
      <tbody>
        <?php foreach ($backups as $b): ?>
          <tr>
            <td><b><?= e($b['file']) ?></b></td>
            <td><?= e(human_bytes($b['size'])) ?></td>
            <td><?= e(date('M j, Y H:i', $b['time'])) ?></td>
            <td><a class="btn btn-sm" href="<?= e(url('admin/backups&download=' . urlencode($b['file']))) ?>"><?= icon('download') ?> Download</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$backups): ?><tr><td colspan="4" class="muted">No backups yet. Super Admins create them from Admin → Backups.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <p class="tiny faint" style="margin:10px 0 0">Backups are created platform-wide by Super Admins. If you need school-specific exports, ask your Super Admin.</p>
</div>
