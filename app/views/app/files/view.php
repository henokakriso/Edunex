<?php /* File detail + versions */
?>
<div class="page-head">
  <div>
    <h1><?= icon('file') ?> <?= e($f['original_name']) ?></h1>
    <p class="sub"><?= e(human_bytes((int)$f['size'])) ?> · <?= e($f['mime']) ?> · uploaded <?= e(date('M j, Y H:i', strtotime($f['created_at']))) ?></p>
  </div>
  <div class="flex gap-8">
    <a class="btn" href="<?= e(url('file&p=' . urlencode($f['path']))) ?>" target="_blank"><?= icon('download') ?> Download</a>
    <a class="btn btn-ghost" href="<?= e(url('files')) ?>">← Back</a>
  </div>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:18px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('upload') ?> Upload new version</h3>
    <form method="post" enctype="multipart/form-data" class="flex gap-8">
      <?= csrf_field() ?>
      <input type="file" name="file" class="input" style="flex:1" required>
      <button class="btn btn-success" name="new_version" value="<?= (int)$f['id'] ?>">Upload v<?= (int)$f['version'] + 1 ?></button>
    </form>
    <p class="tiny faint" style="margin-top:10px">Previous versions are kept in version history.</p>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('folder') ?> Version history (<?= count($versions) ?>)</h3>
    <?php foreach ($versions as $v): ?>
      <div class="list-row" style="padding:8px 0">
        <b class="small">v<?= (int)$v['version'] ?></b>
        <span class="tiny faint flex-1"><?= e(human_bytes((int)$v['size'])) ?> · <?= e(date('M j, H:i', strtotime($v['created_at']))) ?></span>
        <a class="btn btn-sm btn-ghost" href="<?= e(url('file&p=' . urlencode($v['path']))) ?>">⬇</a>
      </div>
    <?php endforeach; ?>
    <?php if (!$versions): ?><p class="muted small">No versions yet.</p><?php endif; ?>
  </div>
</div>
