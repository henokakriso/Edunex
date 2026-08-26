<?php /* Admin reports view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('trend-up') ?> Reports</h1>
    <p class="sub">Generate and download platform reports</p>
  </div>
</div>

<form method="post" class="card" style="margin-bottom:20px">
  <?= csrf_field() ?>
  <h3 class="card-title" style="margin-top:0"><?= icon('plus') ?> Generate report</h3>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">Report type</label>
      <select class="input" name="type">
        <option value="student">Student roster</option>
        <option value="teacher">Teacher roster</option>
        <option value="course">Course summary</option>
        <option value="attendance">Attendance records</option>
        <option value="system">System activity</option>
      </select>
    </div>
    <div class="flex-col"><label class="small faint">School</label>
      <select class="input" name="school_id"><option value="0">All schools</option><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Title (optional)</label><input class="input" name="title" placeholder="Q1 student roster"></div>
  </div>
  <button class="btn btn-success" name="generate" value="1"><?= icon('rocket') ?> Generate (CSV)</button>
</form>

<div class="card">
  <?php $rSortUrl = fn($col) => url('admin/reports?' . http_build_query(['sort'=>$col, 'dir'=> $sort===$col && $dir==='desc' ? 'asc' : 'desc'])); ?>
  <h3 class="card-title" style="margin-top:0"><?= icon('folder') ?> Generated reports</h3>
  <table class="table">
    <thead><tr>
      <th><a class="ajax-nav sort-link" href="<?= e($rSortUrl('title')) ?>">Title<span class="sort-arrow<?= $sort==='title' ? ' active' : '' ?>"><?= $sort==='title' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($rSortUrl('type')) ?>">Type<span class="sort-arrow<?= $sort==='type' ? ' active' : '' ?>"><?= $sort==='type' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($rSortUrl('school')) ?>">School<span class="sort-arrow<?= $sort==='school' ? ' active' : '' ?>"><?= $sort==='school' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($rSortUrl('by')) ?>">By<span class="sort-arrow<?= $sort==='by' ? ' active' : '' ?>"><?= $sort==='by' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th>Format</th>
      <th><a class="ajax-nav sort-link" href="<?= e($rSortUrl('created_at')) ?>">Created<span class="sort-arrow<?= $sort==='created_at' ? ' active' : '' ?>"><?= $sort==='created_at' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th></th>
    </tr></thead>
    <tbody>
      <?php foreach ($reports as $r): ?>
        <tr>
          <td><b class="small"><?= e($r['title']) ?></b></td>
          <td><span class="badge badge-muted"><?= e($r['type']) ?></span></td>
          <td class="small"><?= e($r['school_name']) ?></td>
          <td class="small"><?= e($r['user_name']) ?></td>
          <td class="small"><?= e($r['format']) ?></td>
          <td class="small faint"><?= e(date('M j, H:i', strtotime($r['created_at']))) ?></td>
          <td><a class="btn btn-sm" href="<?= e(url('file?p=' . $r['file_path'] . '&dl=1')) ?>">⬇ Download</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!$reports): ?><p class="muted small" style="padding:12px">No reports generated yet.</p><?php endif; ?>
</div>
