<?php /* Library item detail view */
$typeIcons = ['book' => icon('book'), 'notes' => icon('note'), 'paper' => icon('file'), 'slides' => icon('image'), 'video' => icon('video'), 'past_exam' => icon('doc'), 'tutorial' => icon('graduation')];
?>
<div class="page-head">
  <div>
    <h1><?= $typeIcons[$item['type']] ?? icon('file') ?> <?= e($item['title']) ?></h1>
    <p class="sub"><?= e($item['author']) ?: '—' ?> · <?= e($item['school_name']) ?> · <?= e(ucfirst(str_replace('_', ' ', $item['type']))) ?></p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('library')) ?>">← Back</a>
</div>

<div class="grid" style="grid-template-columns:1.5fr 1fr;gap:22px;align-items:start">
  <div class="card">
    <h3 class="card-title" style="margin-top:0">Description</h3>
    <p class="small" style="white-space:pre-wrap"><?= nl2br(e($item['description'])) ?></p>
    <?php if ($item['file_path']): ?>
      <div class="alert alert-success" style="margin-top:16px">
        <?= icon('paperclip') ?> <b>File available:</b> <?= e(basename((string)$item['file_path'])) ?>
      </div>
      <a class="btn btn-primary btn-lg" href="<?= e(url('file?p=' . $item['file_path'] . '&dl=1&item=library&id=' . $item['id'])) ?>"><?= icon('download') ?> Download</a>
    <?php else: ?>
      <div class="alert alert-warning">This item has no file attached yet.</div>
    <?php endif; ?>
    <form method="post" class="inline" style="margin-top:10px">
      <?= csrf_field() ?><button class="btn btn-ghost" name="favorite" value="<?= (int)$item['id'] ?>"><?= icon('heart') ?> Add to favorites</button>
    </form>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0">ℹ Details</h3>
    <div class="flex-col gap-8">
      <div class="flex-between"><span class="small faint">Category</span><b class="small"><?= e($item['category']) ?: '—' ?></b></div>
      <div class="flex-between"><span class="small faint">Downloads</span><b class="small"><?= (int)$item['downloads'] ?></b></div>
      <div class="flex-between"><span class="small faint">Added</span><b class="small"><?= e(date('M j, Y', strtotime($item['created_at']))) ?></b></div>
    </div>
  </div>
</div>

<?php if ($related): ?>
<div class="card" style="margin-top:22px">
  <h3 class="card-title" style="margin-top:0"><?= icon('link') ?> Related</h3>
  <div class="flex-col gap-8">
    <?php foreach ($related as $r): ?>
      <a class="list-row" href="<?= e(url('library/item&id=' . $r['id'])) ?>" style="text-decoration:none">
        <span><?= $typeIcons[$r['type']] ?? icon('file') ?></span>
        <span class="flex-1 small"><b><?= e($r['title']) ?></b></span>
        <span class="tiny faint"><?= e($r['category']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
