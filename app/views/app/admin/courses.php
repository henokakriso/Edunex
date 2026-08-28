<?php /* Admin courses view — read-only for ministry, cascading filter */
$hasFilter = $region || $zone || $type || $schoolId;
$cSortUrl = fn($col) => url('admin/courses?' . http_build_query(array_filter(['region'=>$region,'zone'=>$zone,'type'=>$type,'school'=>$schoolId,'sort'=>$col,'dir'=>$sort===$col && $dir==='desc' ? 'asc' : 'desc'], fn($x) => $x !== '')));
?>
<div class="page-head" style="margin-bottom:18px">
  <div>
    <h1><?= icon('graduation') ?> All Courses</h1>
    <p class="sub"><?= count($courses) ?> course<?= count($courses) === 1 ? '' : 's' ?></p>
  </div>
</div>

<!-- Four-section cascading filter -->
<div style="margin-bottom:20px">
  <form method="get" class="ajax-nav" style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px 20px">
    <input type="hidden" name="r" value="admin/courses">
    <div style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">

      <div style="flex:1;min-width:180px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">Region</label>
        <select class="input" name="region" onchange="this.form.submit()" style="padding:10px 14px;width:100%">
          <option value="" <?= !$region ? 'selected' : '' ?>>— Select Region —</option>
          <?php foreach ($regions as $r): ?>
            <option value="<?= e($r) ?>" <?= $region === $r ? 'selected' : '' ?>><?= e($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="flex:1;min-width:180px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">Zone</label>
        <select class="input" name="zone" onchange="this.form.submit()" style="padding:10px 14px;width:100%" <?= !$region ? 'disabled' : '' ?>>
          <?php if (!$region): ?>
            <option value="">— Select region first —</option>
          <?php else: ?>
            <option value="" <?= !$zone ? 'selected' : '' ?>>All Zones</option>
            <?php foreach ($allZones as $z): ?>
              <option value="<?= e($z) ?>" <?= $zone === $z ? 'selected' : '' ?>><?= e($z) ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <div style="flex:1;min-width:180px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">School Level</label>
        <select class="input" name="type" onchange="this.form.submit()" style="padding:10px 14px;width:100%" <?= !$region ? 'disabled' : '' ?>>
          <?php if (!$region): ?>
            <option value="">— Select region first —</option>
          <?php else: ?>
            <option value="" <?= !$type ? 'selected' : '' ?>>All Levels</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e(ucfirst($t)) ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <div style="flex:1;min-width:180px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">School Name</label>
        <select class="input" name="school" onchange="this.form.submit()" style="padding:10px 14px;width:100%" <?= !$region ? 'disabled' : '' ?>>
          <?php if (!$region): ?>
            <option value="">— Select region first —</option>
          <?php else: ?>
            <?php
              $dd = $schools;
              if ($region) $dd = array_values(array_filter($dd, fn($s) => ($s['region'] ?: 'Other') === $region));
              if ($zone) {
                $zIds = array_column(Database::all("SELECT id FROM zones WHERE name = ?", [$zone]), 'id');
                $dd = $zIds ? array_values(array_filter($dd, fn($s) => in_array((int)($s['zone_id'] ?? 0), $zIds))) : [];
              }
              if ($type) $dd = array_values(array_filter($dd, fn($s) => ($s['type'] ?: 'school') === $type));
            ?>
            <option value="" <?= !$schoolId ? 'selected' : '' ?>>All Schools</option>
            <?php foreach ($dd as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= $schoolId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
            <?php if (!$dd): ?>
              <option value="" disabled>No schools match</option>
            <?php endif; ?>
          <?php endif; ?>
        </select>
      </div>

      <?php if ($hasFilter): ?>
      <a class="btn btn-ghost ajax-nav" href="<?= e(url('admin/courses')) ?>" style="margin-bottom:2px;white-space:nowrap"><?= icon('x') ?> Reset</a>
      <?php endif; ?>

    </div>
  </form>
</div>

<!-- Courses table -->
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden">
  <table class="table" style="margin:0">
    <thead>
      <tr>
        <th style="width:50px;text-align:center">#</th>
        <th><a class="ajax-nav sort-link" href="<?= e($cSortUrl('name')) ?>">Course<span class="sort-arrow<?= $sort==='name' ? ' active' : '' ?>"><?= $sort==='name' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <?php if (!$schoolId): ?>
        <th style="width:200px"><a class="ajax-nav sort-link" href="<?= e($cSortUrl('school')) ?>">School<span class="sort-arrow<?= $sort==='school' ? ' active' : '' ?>"><?= $sort==='school' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <?php endif; ?>
        <th style="width:180px"><a class="ajax-nav sort-link" href="<?= e($cSortUrl('teacher')) ?>">Teacher<span class="sort-arrow<?= $sort==='teacher' ? ' active' : '' ?>"><?= $sort==='teacher' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th style="width:90px;text-align:center"><a class="ajax-nav sort-link" href="<?= e($cSortUrl('students')) ?>">Students<span class="sort-arrow<?= $sort==='students' ? ' active' : '' ?>"><?= $sort==='students' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th style="width:90px;text-align:center"><a class="ajax-nav sort-link" href="<?= e($cSortUrl('lessons')) ?>">Lessons<span class="sort-arrow<?= $sort==='lessons' ? ' active' : '' ?>"><?= $sort==='lessons' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th style="width:100px;text-align:center"><a class="ajax-nav sort-link" href="<?= e($cSortUrl('status')) ?>">Status<span class="sort-arrow<?= $sort==='status' ? ' active' : '' ?>"><?= $sort==='status' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0; foreach ($courses as $c): ?>
      <tr>
        <td style="text-align:center;font-family:var(--font-mono,monospace);font-size:12px;color:var(--text-faint)"><?= $i + 1 ?></td>
        <td>
          <b class="small"><?= e($c['title']) ?></b>
          <?php if ($c['code']): ?><br><span class="tiny faint mono"><?= e($c['code']) ?></span><?php endif; ?>
        </td>
        <?php if (!$schoolId): ?>
        <td><span class="badge badge-muted"><?= e($c['school_name']) ?></span></td>
        <?php endif; ?>
        <td class="small"><?= e($c['tfirst'] . ' ' . $c['tlast']) ?></td>
        <td style="text-align:center"><?= (int)$c['students'] ?></td>
        <td style="text-align:center"><?= (int)$c['lessons'] ?></td>
        <td style="text-align:center">
          <span class="badge <?= $c['status'] === 'published' ? 'badge-success' : ($c['status'] === 'archived' ? 'badge-muted' : 'badge-warning') ?>"><?= e($c['status']) ?></span>
        </td>
      </tr>
      <?php $i++; endforeach; ?>
      <?php if (!$courses): ?>
      <tr>
        <td colspan="<?= $schoolId ? 6 : 7 ?>" style="text-align:center;color:var(--muted);padding:24px">
          <?= $hasFilter ? 'No courses match the selected filters.' : 'Select a region to begin filtering.' ?>
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
