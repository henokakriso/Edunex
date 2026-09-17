<?php /* Library index view — 3-column grid cards with thumbnails */
$typeIcons = ['book' => icon('book'), 'notes' => icon('note'), 'paper' => icon('file'), 'slides' => icon('image'), 'video' => icon('video'), 'past_exam' => icon('doc'), 'tutorial' => icon('graduation')];
$canUpload = $canUpload ?? false;
?>
<style>
.lib-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
@media(max-width:960px){.lib-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.lib-grid{grid-template-columns:1fr}}
.lib-card{border-radius:12px;border:1px solid var(--border);background:var(--bg-elev);overflow:hidden;transition:border-color .15s,box-shadow .15s;display:flex;flex-direction:column}
.lib-card:hover{border-color:var(--accent);box-shadow:0 4px 16px rgba(0,0,0,.06)}
.lib-thumb{width:100%;aspect-ratio:4/3;object-fit:cover;background:var(--bg-muted);display:block}
.lib-thumb-placeholder{width:100%;aspect-ratio:4/3;background:var(--accent-soft);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:2.4rem}
.lib-card-body{padding:12px 14px;flex:1;display:flex;flex-direction:column}
.lib-card-title{font-size:13px;font-weight:600;color:var(--text);line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.lib-card-meta{font-size:11px;color:var(--text-dim);margin-top:6px;line-height:1.4}
.lib-card-desc{font-size:11px;color:var(--muted);margin-top:4px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.lib-card-actions{margin-top:auto;padding-top:10px;display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.lib-card-actions .btn{font-size:11px;padding:4px 10px;border-radius:8px}
.lib-type-badge{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;padding:2px 7px;border-radius:6px;background:var(--accent-soft);color:var(--accent)}
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
        <div class="flex-col" style="margin-top:10px"><label class="small faint">Cover image (JPG, PNG)</label><input class="input" type="file" name="cover" accept=".jpg,.jpeg,.png,.webp"></div>
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
    <input type="hidden" name="r" value="teacher/library">
    <div class="flex-col flex-1"><label class="small faint">Search</label><div class="input-icon-wrap"><span class="input-ico"><?= icon('search') ?></span><input class="input has-ico" name="q" id="lib-search" value="<?= e($q ?? '') ?>" placeholder="Search by title, author or category…" oninput="document.getElementById('lib-clear').style.display=this.value?'flex':'none'"><button type="button" class="input-icon-btn" id="lib-clear" style="display:<?= ($q ?? '') ? 'flex' : 'none' ?>" onclick="document.getElementById('lib-search').value='';this.style.display='none';this.form.submit()"><?= icon('x') ?></button></div></div>
    <div class="flex-col"><label class="small faint">Type</label>
      <select class="input" name="type" onchange="this.form.submit()">
        <option value="">All types</option>
        <?php foreach (($types ?? []) as $t): ?>
          <option value="<?= e($t['type'] ?? '') ?>" <?= ($type ?? '') === ($t['type'] ?? '') ? 'selected' : '' ?>><?= $typeIcons[$t['type'] ?? ''] ?? icon('file') ?> <?= ucfirst(str_replace('_', ' ', $t['type'] ?? '')) ?> (<?= (int)($t['n'] ?? 0) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn"><?= icon('search') ?> Search</button>
  </form>
</div>

<div class="lib-grid">
  <?php foreach (($items ?? []) as $it): ?>
    <?php $itId = (int)($it['id'] ?? 0); $hasFile = !empty($it['file_path']); ?>
    <div class="lib-card">
      <?php if (!empty($it['cover'])): ?>
        <img class="lib-thumb" src="<?= e(url('file?p=' . $it['cover'])) ?>" alt="<?= e($it['title'] ?? '') ?>">
      <?php else: ?>
        <div class="lib-thumb-placeholder"><?= $typeIcons[$it['type'] ?? ''] ?? icon('file') ?></div>
      <?php endif; ?>
      <div class="lib-card-body">
        <div class="lib-card-title"><?= e($it['title'] ?? '') ?></div>
        <div class="lib-card-meta">
          <span class="lib-type-badge"><?= $typeIcons[$it['type'] ?? ''] ?? icon('file') ?> <?= ucfirst(str_replace('_', ' ', $it['type'] ?? '')) ?></span>
          <?php if (!empty($it['author'])): ?>
            <span style="margin-left:6px"><?= e($it['author']) ?></span>
          <?php endif; ?>
        </div>
        <?php if (!empty($it['uploader_name']) || !empty($it['category'])): ?>
          <div class="lib-card-meta">
            <?php if (!empty($it['uploader_name'])): ?>By <?= e($it['uploader_name']) ?><?php endif; ?>
            <?php if (!empty($it['category'])): ?><?php if (!empty($it['uploader_name'])): ?> · <?php endif; ?><?= e($it['category']) ?><?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="lib-card-actions">
          <?php if ($itId && in_array($itId, $myFavs ?? [], true)): ?>
            <form method="post" class="inline" style="margin:0"><?= csrf_field() ?><button class="btn btn-sm" style="background:var(--danger-soft);color:var(--danger)" name="unfavorite" value="<?= $itId ?>" title="Remove from favorites"><?= icon('heart') ?> Liked</button></form>
          <?php else: ?>
            <form method="post" class="inline" style="margin:0"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" name="favorite" value="<?= $itId ?>" title="Add to favorites"><?= icon('heart') ?> Like</button></form>
          <?php endif; ?>
          <?php if ($hasFile): ?>
            <a class="btn btn-sm btn-ghost" title="Download" href="<?= e(url('file?p=' . $it['file_path'] . '&dl=1&item=library&id=' . $itId)) ?>"><?= icon('download') ?> Download</a>
          <?php endif; ?>
          <?php if ($hasFile && preg_match('/\.(pdf)$/i', (string)$it['file_path'])): ?>
            <a class="btn btn-sm btn-ghost" title="Read online" href="<?= e(url('file?p=' . $it['file_path'] . '&item=library&id=' . $itId)) ?>" target="_blank"><?= icon('book') ?> Read</a>
          <?php endif; ?>
          <?php if ($itId): ?>
            <a class="btn btn-sm btn-primary" href="<?= e(url('library/item&id=' . $itId)) ?>" style="margin-left:auto"><?= icon('eye') ?> View</a>
          <?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-top:6px">
          <span class="tiny faint"><?= icon('download') ?> <?= (int)($it['downloads'] ?? 0) ?> downloads</span>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php if (empty($items)): ?>
  <div style="padding:40px;text-align:center;color:var(--muted);font-size:13px">
    <?= icon('university') ?> No items found.<?php if ($canUpload): ?> Try uploading something!<?php endif; ?>
  </div>
<?php endif; ?>
