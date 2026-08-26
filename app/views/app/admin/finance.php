<?php /* Sysadmin: financial summary (finance module) */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('banknote') ?> Financial Summary</h1>
    <p class="sub">Course revenue for institutions with the Finance module enabled</p>
  </div>
</div>

<?php if (!$rows): ?>
  <div class="card muted" style="padding:28px">
    No school has the Finance module enabled yet. Enable it per school under
    <a href="<?= e(url('admin/school-modules')) ?>">School Modules</a> to see financial summaries here.
  </div>
<?php else: ?>
  <div class="card pad-0">
    <?php $fSortUrl = fn($col) => url('admin/finance?' . http_build_query(['sort'=>$col, 'dir'=> $sort===$col && $dir==='asc' ? 'desc' : 'asc'])); ?>
    <table class="table">
      <thead><tr>
        <th><a class="ajax-nav sort-link" href="<?= e($fSortUrl('name')) ?>">Institution<span class="sort-arrow<?= $sort==='name' ? ' active' : '' ?>"><?= $sort==='name' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th><a class="ajax-nav sort-link" href="<?= e($fSortUrl('level')) ?>">Level<span class="sort-arrow<?= $sort==='level' ? ' active' : '' ?>"><?= $sort==='level' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th><a class="ajax-nav sort-link" href="<?= e($fSortUrl('paid_courses')) ?>">Paid courses<span class="sort-arrow<?= $sort==='paid_courses' ? ' active' : '' ?>"><?= $sort==='paid_courses' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th><a class="ajax-nav sort-link" href="<?= e($fSortUrl('revenue')) ?>">Estimated revenue<span class="sort-arrow<?= $sort==='revenue' ? ' active' : '' ?>"><?= $sort==='revenue' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      </tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><b><?= e($r['name']) ?></b></td>
            <td><span class="badge badge-info"><?= e($r['level']) ?></span></td>
            <td><?= (int)$r['paid_courses'] ?></td>
            <td><b><?= number_format($r['revenue']) ?> ETB</b></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="tiny faint" style="margin-top:8px"><?= count($notEnabled) ?> school(s) have the Finance module disabled and are excluded.</p>
<?php endif; ?>
