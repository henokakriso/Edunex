<?php /* Admin groups (classes) view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('tag') ?> Classes</h1>
    <p class="sub">Student groups / sections</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('new-group').style.display='block';this.style.display='none'">+ New class</button>
</div>

<form method="post" class="card" id="new-group" style="display:none;margin-bottom:18px">
  <?= csrf_field() ?>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">School *</label>
      <select class="input" name="school_id" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Name *</label><input class="input" name="name" required placeholder="Grade 9-A"></div>
    <div class="flex-col"><label class="small faint">Grade</label><input class="input" name="grade" placeholder="9"></div>
    <div class="flex-col"><label class="small faint">Section</label><input class="input" name="section" placeholder="A"></div>
  </div>
  <button class="btn btn-success" name="create_group" value="1"><?= icon('plus') ?> Create</button>
</form>

<div class="card">
  <?php $gSortUrl = fn($col) => url('admin/groups?' . http_build_query(['sort'=>$col, 'dir'=> $sort===$col && $dir==='asc' ? 'desc' : 'asc'])); ?>
  <table class="table">
    <thead><tr>
      <th><a class="ajax-nav sort-link" href="<?= e($gSortUrl('name')) ?>">Class<?php if($sort==='name'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($gSortUrl('school')) ?>">School<?php if($sort==='school'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($gSortUrl('grade')) ?>">Grade<?php if($sort==='grade'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($gSortUrl('section')) ?>">Section<?php if($sort==='section'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($gSortUrl('students')) ?>">Students<?php if($sort==='students'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th></th>
    </tr></thead>
    <tbody>
      <?php foreach ($groups as $g): ?>
        <tr>
          <td><b class="small"><?= e($g['name']) ?></b></td>
          <td class="small"><?= e($g['school_name']) ?></td>
          <td class="small"><?= e($g['grade']) ?: '—' ?></td>
          <td class="small"><?= e($g['section']) ?: '—' ?></td>
          <td class="small"><?= (int)$g['members'] ?></td>
          <td>
            <form method="post" class="inline" data-confirm="Delete class <?= e($g['name']) ?>?">
              <?= csrf_field() ?><input type="hidden" name="delete_group" value="<?= (int)$g['id'] ?>">
              <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!$groups): ?><p class="muted small" style="padding:12px">No classes yet.</p><?php endif; ?>
</div>
