<?php /* Library index view */
$typeIcons = ['book' => icon('book'), 'notes' => icon('note'), 'paper' => icon('file'), 'slides' => icon('image'), 'video' => icon('video'), 'past_exam' => icon('doc'), 'tutorial' => icon('graduation')];
?>
<div class="page-head">
  <div>
    <h1><?= icon('university') ?> Digital Library</h1>
    <p class="sub">Books, notes, past exams and tutorials</p>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <form method="get" class="flex gap-12" style="align-items:end">
    <input type="hidden" name="r" value="library">
    <div class="flex-col flex-1"><label class="small faint">Search</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="Search by title, author or category…"></div>
    <div class="flex-col"><label class="small faint">Type</label>
      <select class="input" name="type" onchange="this.form.submit()">
        <option value="">All types</option>
        <?php foreach ($types as $t): ?><option value="<?= e($t['type']) ?>" <?= $type === $t['type'] ? 'selected' : '' ?>><?= $typeIcons[$t['type']] ?? icon('file') ?> <?= ucfirst(str_replace('_', ' ', $t['type'])) ?> (<?= (int)$t['n'] ?>)</option><?php endforeach; ?>
      </select>
    </div>
    <button class="btn"><?= icon('search') ?> Search</button>
  </form>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
  <?php foreach ($items as $it): ?>
    <div class="card" style="display:flex;flex-direction:column">
      <div class="flex gap-12">
        <div class="avatar" style="width:46px;height:46px;font-size:1.2rem;border-radius:12px"><?= $typeIcons[$it['type']] ?? icon('file') ?></div>
        <div class="flex-1">
          <b class="small"><?= e($it['title']) ?></b>
          <p class="tiny faint"><?= e($it['author']) ?: '—' ?> · <?= e($it['category']) ?></p>
        </div>
      </div>
      <p class="tiny faint" style="margin-top:8px;flex:1"><?= e(mb_strimwidth((string)$it['description'], 0, 90, '…')) ?></p>
      <div class="flex-between" style="margin-top:10px">
        <span class="tiny faint">⬇ <?= (int)$it['downloads'] ?> · <?= icon('heart') ?> <?= (int)$it['favs'] ?></span>
        <div class="flex gap-8">
          <?php if (in_array((int)$it['id'], $myFavs, true)): ?>
            <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" name="unfavorite" value="<?= (int)$it['id'] ?>"><?= icon('heart') ?></button></form>
          <?php else: ?>
            <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" name="favorite" value="<?= (int)$it['id'] ?>"><?= icon('heart') ?></button></form>
          <?php endif; ?>
          <?php if ($it['file_path']): ?>
            <a class="btn btn-sm btn-ghost" title="Download" href="<?= e(url('file?p=' . $it['file_path'] . '&dl=1&item=library&id=' . $it['id'])) ?>"><?= icon('download') ?></a>
          <?php endif; ?>
          <a class="btn btn-sm" href="<?= e(url('library/item&id=' . $it['id'])) ?>">View</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php if (!$items): ?><div class="alert alert-info">No items found.</div><?php endif; ?>
