<?php /* Regional backups — full CRUD: create, download, rename, delete */
?>
<div class="page-head">
  <div>
    <h1><?= icon('save') ?> Backups</h1>
    <p class="sub">Database snapshots · <?= count($backups) ?> backup<?= count($backups) === 1 ? '' : 's' ?></p>
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
      <button class="btn btn-primary" name="create_backup" value="1"><?= icon('shield') ?> Create backup now</button>
    </form>
  </div>
</div>

<?php if ($backups): ?>
<div class="card">
  <table class="table">
    <thead><tr><th style="width:36px">#</th><th>File</th><th>Size</th><th>Created</th><th style="width:240px">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($backups as $i => $b): ?>
        <tr>
          <td class="small faint"><?= $i + 1 ?></td>
          <td class="mono small"><?= e($b['file']) ?></td>
          <td class="small"><?= e(round($b['size'] / 1024, 1)) ?> KB</td>
          <td class="small faint"><?= e(date('M j, Y H:i', $b['time'])) ?></td>
          <td>
            <div class="flex gap-6" style="white-space:nowrap">
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="download_backup" value="1">
                <input type="hidden" name="file" value="<?= e($b['file']) ?>">
                <button class="btn btn-sm btn-primary"><?= icon('download') ?> Download</button>
              </form>
              <button class="btn btn-sm" onclick="document.getElementById('rename-<?= $i ?>').style.display='flex'">✏️ Rename</button>
              <form method="post" class="inline" data-confirm="Delete this backup?">
                <?= csrf_field() ?>
                <input type="hidden" name="delete_backup" value="<?= e($b['file']) ?>">
                <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
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
<?php else: ?>
  <div class="card">
    <div class="empty-state">
      <div class="empty-ic"><?= icon('save') ?></div>
      <h3>No backups yet</h3>
      <p class="small">Create your first backup for safety.</p>
    </div>
  </div>
<?php endif; ?>
