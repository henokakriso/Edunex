<?php /* Files manager view */
$ico = fn($name) => str_contains((string)$name, '.') ? icon('file') : icon('folder');
?>
<div class="page-head">
  <div>
    <h1><?= $trash ? icon('trash') . ' Trash' : icon('folder') . ' My Files' ?></h1>
    <p class="sub"><?= e(human_bytes($quota)) ?> used · files are versioned automatically</p>
  </div>
  <div class="flex gap-6" style="flex-wrap:wrap">
    <a class="btn btn-sm btn-ghost" href="<?= e(url($trash ? 'files' : 'files&trash=1')) ?>"><?= $trash ? '← Back to files' : icon('trash') . ' Trash' ?></a>
    <?php if (!$trash): ?><button class="btn btn-primary" onclick="document.getElementById('upload-box').style.display='block';this.style.display='none'"><?= icon('upload') ?> Upload</button><?php endif; ?>
  </div>
</div>

<?php if (!$trash): ?>
<div class="flex gap-8" style="margin-bottom:14px;flex-wrap:wrap;align-items:center">
  <a class="btn btn-sm btn-ghost" href="<?= e(url('files')) ?>"><?= icon('home') ?> Root</a>
  <?php foreach ($crumbs as $c): ?>
    <span class="tiny faint">/</span>
    <a class="btn btn-sm btn-ghost" href="<?= e(url('files&folder=' . $c['id'])) ?>"><?= e($c['name']) ?></a>
  <?php endforeach; ?>
  <form method="post" class="inline"><?= csrf_field() ?><input class="input" style="width:180px" name="new_folder" placeholder="New folder name"><button class="btn btn-sm" name="mkfolder" value="1">＋ Folder</button></form>
</div>

<div id="upload-box" class="card" style="display:none;margin-bottom:16px">
  <form method="post" enctype="multipart/form-data" class="flex gap-8">
    <?= csrf_field() ?>
    <input type="file" name="file" class="input" style="flex:1" required>
    <button class="btn btn-success" name="upload" value="1"><?= icon('upload') ?> Upload to <?= $crumbs ? 'this folder' : 'root' ?></button>
  </form>
</div>
<?php endif; ?>

<div class="card" style="padding:6px 16px">
  <?php foreach ($items as $f): ?>
    <div class="list-row" style="padding:10px 0;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:8px">
      <span style="font-size:18px"><?= $ico($f['name']) ?></span>
      <?php if ($f['is_folder']): ?>
        <a class="small flex-1" href="<?= $trash ? '#' : e(url('files&folder=' . $f['id'])) ?>"><?= e($f['name']) ?></a>
      <?php else: ?>
        <a class="small flex-1" href="<?= $trash ? '#' : e(url('files/view&id=' . $f['id'])) ?>"><?= e($f['original_name']) ?> <span class="badge badge-muted">v<?= (int)$f['version'] ?></span></a>
      <?php endif; ?>
      <span class="tiny faint"><?= $f['is_folder'] ? '' : e(human_bytes((int)$f['size'])) ?> · <?= e(date('M j', strtotime($f['deleted_at'] ?: $f['created_at']))) ?></span>
      <?php if (!$f['is_folder'] && !$trash): ?><a class="btn btn-sm btn-ghost" href="<?= e(url('file&p=' . urlencode($f['path']))) ?>" target="_blank">⬇</a><?php endif; ?>
      <?php if ($trash): ?>
        <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-success" name="restore" value="<?= (int)$f['id'] ?>"><?= icon('refresh') ?> Restore</button>
        <button class="btn btn-sm btn-danger" name="purge" value="<?= (int)$f['id'] ?>" data-confirm="Delete permanently? This cannot be undone."><?= icon('trash') ?> Purge</button></form>
      <?php else: ?>
        <div class="flex gap-4" style="flex-wrap:wrap">
          <form method="post" class="inline" style="display:inline-flex;gap:6px"><?= csrf_field() ?>
            <select class="input" style="width:150px" name="to_folder">
              <option value="">— Move to… —</option>
              <?php foreach ($folders as $fo): ?><option value="<?= (int)$fo['id'] ?>"><?= e($fo['name']) ?></option><?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-ghost" name="move" value="<?= (int)$f['id'] ?>">⇄</button>
          </form>
          <form method="post" class="inline" style="display:inline-flex;gap:6px"><?= csrf_field() ?>
            <input class="input" style="width:120px" name="rename_to" placeholder="Rename" required>
            <button class="btn btn-sm btn-ghost" name="rename" value="<?= (int)$f['id'] ?>"><?= icon('edit') ?></button>
          </form>
          <form method="post" class="inline" data-confirm="Move to trash?"><?= csrf_field() ?><button class="btn btn-sm btn-danger" name="delete" value="<?= (int)$f['id'] ?>"><?= icon('trash') ?></button></form>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if (!$items): ?><p class="muted small" style="padding:16px"><?= $trash ? 'Trash is empty.' : 'This folder is empty. Upload a file or create a folder.' ?></p><?php endif; ?>
</div>
