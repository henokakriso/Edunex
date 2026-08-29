<?php /* Library index view */
$typeIcons = ['book' => icon('book'), 'notes' => icon('note'), 'paper' => icon('file'), 'slides' => icon('image'), 'video' => icon('video'), 'past_exam' => icon('doc'), 'tutorial' => icon('graduation')];
$canUpload = $canUpload ?? false;
?>
<style>
.lib-row{display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:12px;border:1px solid var(--border);background:var(--bg-elev);transition:border-color .15s,box-shadow .15s}
.lib-row:hover{border-color:transparent;box-shadow:0 0 0 1px rgba(255,255,255,.15),inset 0 1px 1px rgba(255,255,255,.2),0 4px 12px rgba(0,0,0,.06)}
.lib-row:focus-within{border-color:var(--accent);box-shadow:0 0 0 3px rgba(99,102,241,.15)}
.lib-icon{width:42px;height:42px;border-radius:10px;background:var(--accent-soft);color:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem}
.lib-title{font-size:14px;font-weight:600;color:var(--text);line-height:1.3}
.lib-meta{font-size:12px;color:var(--text-secondary);margin-top:2px}
.lib-desc{font-size:12px;color:var(--muted);margin-top:3px;line-height:1.4}
.lib-actions{margin-left:auto;display:flex;gap:6px;flex-shrink:0;align-items:center}
</style>

<div class="page-head">
  <div>
    <h1><?= icon('university') ?> Digital Library</h1>
    <p class="sub">Books, notes, past exams and tutorials<?= $canUpload ? ' — you can upload new items' : '' ?></p>
  </div>
  <?php if ($canUpload): ?>
    <button class="btn btn-primary" data-open-modal="upload-modal">+ Upload item</button>
  <?php endif; ?>
</div>

<?php if ($canUpload): ?>
<div class="modal-backdrop" id="upload-modal">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <h3><?= icon('upload') ?> Upload to Library</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="grid2">
          <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" required placeholder="Book or resource title"></div>
          <div class="flex-col"><label class="small faint">Type</label>
            <select class="input" name="type">
              <option value="book">Book</option><option value="notes">Notes</option><option value="paper">Paper</option>
              <option value="slides">Slides</option><option value="video">Video</option><option value="past_exam">Past Exam</option><option value="tutorial">Tutorial</option>
            </select>
          </div>
          <div class="flex-col"><label class="small faint">Author</label><input class="input" name="author" placeholder="Author name"></div>
          <div class="flex-col"><label class="small faint">Category</label><input class="input" name="category" placeholder="e.g. Mathematics, STEM"></div>
        </div>
        <div class="flex-col" style="margin-top:10px"><label class="small faint">Description</label><textarea class="input" name="description" rows="3" placeholder="Brief description..."></textarea></div>
        <div class="flex-col" style="margin-top:10px"><label class="small faint">File (PDF, DOC, PPT, MP4, MP3)</label><input class="input" type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.webm,.mp3"></div>
        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" name="upload_item" value="1"><?= icon('upload') ?> Upload item</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

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

<div style="display:flex;flex-direction:column;gap:8px">
  <?php foreach ($items as $it): ?>
    <div class="lib-row" tabindex="0">
      <div class="lib-icon"><?= $typeIcons[$it['type']] ?? icon('file') ?></div>
      <div style="flex:1;min-width:0">
        <div class="lib-title"><?= e($it['title']) ?></div>
        <div class="lib-meta"><?= e($it['author']) ?: '—' ?> · <?= e($it['category']) ?> · <?= e($it['school_name']) ?></div>
        <?php if ($it['description']): ?><div class="lib-desc"><?= e(mb_strimwidth((string)$it['description'], 0, 120, '…')) ?></div><?php endif; ?>
      </div>
      <div class="lib-actions">
        <span class="tiny faint">⬇ <?= (int)$it['downloads'] ?></span>
        <?php if (in_array((int)$it['id'], $myFavs, true)): ?>
          <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" name="unfavorite" value="<?= (int)$it['id'] ?>" title="Unfavorite"><?= icon('heart') ?></button></form>
        <?php else: ?>
          <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" name="favorite" value="<?= (int)$it['id'] ?>" title="Favorite"><?= icon('heart') ?></button></form>
        <?php endif; ?>
        <?php if ($it['file_path']): ?>
          <a class="btn btn-sm btn-ghost" title="Download" href="<?= e(url('file?p=' . $it['file_path'] . '&dl=1&item=library&id=' . $it['id'])) ?>"><?= icon('download') ?></a>
        <?php endif; ?>
        <a class="btn btn-sm" href="<?= e(url('library/item&id=' . $it['id'])) ?>">View</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php if (!$items): ?><div style="padding:40px;text-align:center;color:var(--muted);font-size:13px">No items found.</div><?php endif; ?>
