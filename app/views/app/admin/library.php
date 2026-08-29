<?php /* Admin library view */
$typeIcons = ['book' => icon('book'), 'notes' => icon('note'), 'paper' => icon('file'), 'slides' => icon('image'), 'video' => icon('video'), 'past_exam' => icon('doc'), 'tutorial' => icon('graduation')];
?>
<div class="page-head">
  <div>
    <h1><?= icon('university') ?> Library</h1>
    <p class="sub">Manage digital library items</p>
  </div>
  <button class="btn btn-primary" data-open-modal="new-item-modal">+ Add item</button>
</div>

<!-- Add Library Item Modal -->
<div class="modal-backdrop" id="new-item-modal">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <h3>Add Library Item</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="create_item" value="1">

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:14px">
          <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" required placeholder="Mathematics Grade 9 Textbook"></div>
          <div class="flex-col"><label class="small faint">Type</label>
            <select class="input" name="type"><?php foreach ($typeIcons as $k => $v): ?><option value="<?= $k ?>"><?= $v ?> <?= ucfirst(str_replace('_', ' ', $k)) ?></option><?php endforeach; ?></select>
          </div>
          <div class="flex-col"><label class="small faint">School</label>
            <select class="input" name="school_id"><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
          </div>
          <div class="flex-col"><label class="small faint">Author</label><input class="input" name="author"></div>
          <div class="flex-col"><label class="small faint">Category</label><input class="input" name="category" placeholder="e.g. STEM"></div>
          <div class="flex-col"><label class="small faint">Status</label>
            <select class="input" name="status"><option value="published">Published</option><option value="draft">Draft</option></select>
          </div>
          <div class="flex-col"><label class="small faint">File</label><input class="input" type="file" name="file"></div>
          <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Description</label><textarea class="input" name="description" rows="3"></textarea></div>
        </div>

        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" type="submit"><?= icon('plus') ?> Add Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card">
  <?php $lbSortUrl = fn($col) => url('admin/library?' . http_build_query(['sort'=>$col, 'dir'=> $sort===$col && $dir==='desc' ? 'asc' : 'desc'])); ?>
  <table class="table">
    <thead><tr>
      <th class="col-num">#</th>
      <th><a class="ajax-nav sort-link" href="<?= e($lbSortUrl('title')) ?>">Item<span class="sort-arrow<?= $sort==='title' ? ' active' : '' ?>"><?= $sort==='title' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($lbSortUrl('type')) ?>">Type<span class="sort-arrow<?= $sort==='type' ? ' active' : '' ?>"><?= $sort==='type' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($lbSortUrl('school')) ?>">School<span class="sort-arrow<?= $sort==='school' ? ' active' : '' ?>"><?= $sort==='school' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($lbSortUrl('downloads')) ?>">Downloads<span class="sort-arrow<?= $sort==='downloads' ? ' active' : '' ?>"><?= $sort==='downloads' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($lbSortUrl('status')) ?>">Status<span class="sort-arrow<?= $sort==='status' ? ' active' : '' ?>"><?= $sort==='status' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th></th>
    </tr></thead>
    <tbody>
      <?php $i = 0; foreach ($items as $it): ?>
        <tr>
          <td class="col-num"><?= $i + 1 ?></td>
          <td>
            <div class="flex gap-8" style="align-items:center">
              <span style="font-size:1.2rem"><?= $typeIcons[$it['type']] ?? icon('file') ?></span>
              <div><b class="small"><?= e($it['title']) ?></b><p class="tiny faint"><?= e($it['author']) ?: '—' ?> · <?= e($it['category']) ?></p></div>
            </div>
          </td>
          <td><span class="badge badge-muted"><?= e($it['type']) ?></span></td>
          <td class="small"><?= e($it['school_name']) ?></td>
          <td class="small"><?= (int)$it['downloads'] ?></td>
          <td><span class="badge <?= $it['status'] === 'published' ? 'badge-success' : 'badge-warning' ?>"><?= e($it['status']) ?></span></td>
          <td>
            <div class="flex gap-8">
              <?php if ($it['file_path']): ?><a class="btn btn-sm btn-ghost" href="<?= e(url('file?p=' . $it['file_path'])) ?>">⬇</a><?php endif; ?>
              <form method="post" class="inline" data-confirm="Delete <?= e($it['title']) ?>?">
                <?= csrf_field() ?><input type="hidden" name="delete_item" value="<?= (int)$it['id'] ?>">
                <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
              </form>
            </div>
          </td>
        </tr>
      <?php $i++; endforeach; ?>
    </tbody>
  </table>
  <?php if (!$items): ?><p class="muted small" style="padding:12px">Library is empty.</p><?php endif; ?>
</div>
