<?php /* Admin backups view — full CRUD: create, list, rename, download, delete */
$busy = !empty($_GET['creating']);
?>
<div class="page-head">
  <div>
    <h1><?= icon('save') ?> Backups</h1>
    <p class="sub">Database snapshots via mysqldump · <?= count($backups) ?> backup<?= count($backups) === 1 ? '' : 's' ?></p>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <div class="flex-between" style="padding:16px 20px">
    <div>
      <b class="small"><?= icon('shield') ?> Create new backup</b>
      <p class="tiny faint">Creates a full SQL dump of the edunex database</p>
    </div>
    <form method="post" class="inline">
      <?= csrf_field() ?>
      <button class="btn btn-primary" name="create_backup" value="1" <?= $busy ? 'disabled' : '' ?>>
        <?= $busy ? '⏳ Creating…' : icon('shield') . ' Create backup now' ?>
      </button>
    </form>
  </div>
</div>

<?php if ($backups): ?>
<div class="card">
  <div class="table-wrap">
  <table class="table" style="table-layout:auto">
    <thead><tr><th style="width:36px">#</th><th style="padding-left:20px;min-width:200px">File</th><th style="min-width:80px">Size</th><th style="min-width:140px">Created</th><th style="width:120px;text-align:center">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($backups as $i => $b): ?>
        <tr>
          <td class="small faint" style="padding:10px 8px 10px 12px"><?= $i + 1 ?></td>
          <td class="mono small" style="padding:10px 20px 10px 12px"><?= e($b['file']) ?></td>
          <td class="small" style="padding:10px 16px"><?= e(round($b['size'] / 1024, 1)) ?> KB</td>
          <td class="small faint" style="padding:10px 16px"><?= e(date('M j, Y H:i', $b['time'])) ?></td>
          <td style="padding:10px 12px">
            <div class="flex gap-8" style="white-space:nowrap;justify-content:center">
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="download_backup" value="1">
                <input type="hidden" name="file" value="<?= e($b['file']) ?>">
                <button class="btn btn-sm btn-primary" title="Download"><?= icon('download') ?></button>
              </form>
              <button class="btn btn-sm" onclick="document.getElementById('rename-<?= $i ?>').style.display='flex'" title="Rename"><?= icon('tag') ?></button>
              <form method="post" class="inline" data-confirm="Delete this backup permanently?">
                <?= csrf_field() ?>
                <input type="hidden" name="delete_backup" value="<?= e($b['file']) ?>">
                <button class="btn btn-sm btn-danger" title="Delete"><?= icon('trash') ?></button>
              </form>
            </div>
            <form method="post" id="rename-<?= $i ?>" class="flex gap-6" style="display:none;margin-top:8px;align-items:center">
              <?= csrf_field() ?>
              <input type="hidden" name="old_name" value="<?= e($b['file']) ?>">
              <input class="input" name="new_name" value="<?= e($b['file']) ?>" style="width:220px;font-size:12px" required pattern="[\w\-]+\.sql">
              <button class="btn btn-sm btn-primary" type="submit">Save</button>
              <button class="btn btn-sm" type="button" onclick="this.closest('form').style.display='none'">Cancel</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php else: ?>
  <div class="card">
    <div class="empty-state">
      <div class="empty-ic"><?= icon('save') ?></div>
      <h3>No backups yet</h3>
      <p class="small">Create your first backup for safety. You can download, rename, or delete backups anytime.</p>
    </div>
  </div>
<?php endif; ?>
