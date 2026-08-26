<?php /* Admin courses view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('graduation') ?> All Courses</h1>
    <p class="sub"><?= count($courses) ?> course<?= count($courses) === 1 ? '' : 's' ?></p>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <form method="get" class="flex gap-12" style="align-items:end">
    <input type="hidden" name="r" value="admin/courses">
    <div class="flex-col"><label class="small faint">School</label>
      <select class="input" name="school" onchange="this.form.submit()">
        <option value="">All schools</option>
        <?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>" <?= $schoolId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<div class="card">
  <?php $cSortUrl = fn($col) => url('admin/courses?' . http_build_query(array_filter(['school'=>$schoolId ?: '','sort'=>$col, 'dir'=> $sort===$col && $dir==='desc' ? 'asc' : 'desc'], fn($x)=>$x!==''))); ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr>
        <th><a class="ajax-nav sort-link" href="<?= e($cSortUrl('name')) ?>">Course<?php if($sort==='name'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
        <th><a class="ajax-nav sort-link" href="<?= e($cSortUrl('school')) ?>">School<?php if($sort==='school'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
        <th><a class="ajax-nav sort-link" href="<?= e($cSortUrl('teacher')) ?>">Teacher<?php if($sort==='teacher'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
        <th><a class="ajax-nav sort-link" href="<?= e($cSortUrl('students')) ?>">Students<?php if($sort==='students'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
        <th><a class="ajax-nav sort-link" href="<?= e($cSortUrl('lessons')) ?>">Lessons<?php if($sort==='lessons'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
        <th><a class="ajax-nav sort-link" href="<?= e($cSortUrl('status')) ?>">Status<?php if($sort==='status'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
        <?php foreach ($courses as $c): ?>
          <tr>
            <td><b class="small"><?= e($c['title']) ?></b><?= $c['code'] ? '<br><span class="tiny faint mono">' . e($c['code']) . '</span>' : '' ?></td>
            <td class="small"><?= e($c['school_name']) ?></td>
            <td class="small"><?= e($c['tfirst'] . ' ' . $c['tlast']) ?></td>
            <td class="small"><?= (int)$c['students'] ?></td>
            <td class="small"><?= (int)$c['lessons'] ?></td>
            <td><span class="badge <?= $c['status'] === 'published' ? 'badge-success' : ($c['status'] === 'archived' ? 'badge-muted' : 'badge-warning') ?>"><?= e($c['status']) ?></span></td>
<td class="actions">
              <div class="row-act">
                <a class="icon-btn" title="View" href="<?= e(url('courses/view&id=' . $c['id'])) ?>"><?= icon('eye') ?></a>
                <?php if ($c['status'] !== 'published'): ?>
                <form method="post" class="inline" data-confirm="Publish <?= e($c['title']) ?>?"><?= csrf_field() ?><button class="icon-btn success" title="Publish" name="publish_course" value="<?= (int)$c['id'] ?>"><?= icon('rocket') ?></button></form>
                <?php endif; ?>
                <?php if ($c['status'] === 'archived'): ?>
                <form method="post" class="inline" data-confirm="Restore <?= e($c['title']) ?> to drafts?"><?= csrf_field() ?><button class="icon-btn" title="Restore to draft" name="restore_course" value="<?= (int)$c['id'] ?>"><?= icon('refresh') ?></button></form>
                <?php endif; ?>
                <?php if ($c['status'] !== 'archived'): ?>
                <form method="post" class="inline" data-confirm="Archive <?= e($c['title']) ?>?"><?= csrf_field() ?><button class="icon-btn warn" title="Archive" name="archive_course" value="<?= (int)$c['id'] ?>"><?= icon('box') ?></button></form>
                <?php endif; ?>
                <form method="post" class="inline" data-confirm="Delete <?= e($c['title']) ?>?"><?= csrf_field() ?><button class="icon-btn danger" title="Delete" name="delete_course" value="<?= (int)$c['id'] ?>"><?= icon('trash') ?></button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (!$courses): ?><p class="muted small" style="padding:12px">No courses found.</p><?php endif; ?>
</div>
