<?php /* Admin backups view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('save') ?> Backups</h1>
    <p class="sub">Database snapshots (mysqldump)</p>
  </div>
  <form method="post" class="inline">
    <?= csrf_field() ?>
    <button class="btn btn-primary" name="create_backup" value="1"><?= icon('shield') ?> Create backup now</button>
  </form>
</div>

<div class="card">
  <table class="table">
    <thead><tr><th>File</th><th>Size</th><th>Created</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($backups as $b): ?>
        <tr>
          <td class="mono small"><?= e($b['file']) ?></td>
          <td class="small"><?= e(round($b['size'] / 1024, 1)) ?> KB</td>
          <td class="small faint"><?= e(date('M j, H:i', $b['time'])) ?></td>
          <td>
            <div class="flex gap-8">
              <a class="btn btn-sm" href="<?= e(url('file?p=backups/' . $b['file'] . '&dl=1')) ?>" title="Download">⬇</a>
              <form method="post" class="inline" data-confirm="Delete this backup?">
                <?= csrf_field() ?><input type="hidden" name="delete_backup" value="<?= e($b['file']) ?>">
                <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!$backups): ?><p class="muted small" style="padding:12px">No backups yet. Create your first one for safety.</p><?php endif; ?>
</div>
