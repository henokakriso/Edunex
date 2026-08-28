<?php /* Admin departments view — 4-section cascading filter: Region → Zone → Level → School */
$selectedName = '';
if ($schoolId) {
    foreach ($schools as $s) { if ((int)$s['id'] === (int)$schoolId) { $selectedName = $s['name']; break; } }
}
$hasFilter = $region || $zone || $type || $schoolId;
$dSortUrl = fn($col) => url('admin/departments?' . http_build_query(array_filter(['region' => $region, 'zone' => $zone, 'type' => $type, 'school' => $schoolId, 'sort' => $col, 'dir' => $sort === $col && $dir === 'asc' ? 'desc' : 'asc'], fn($x) => $x !== '')));
?>
<div class="page-head" style="margin-bottom:18px">
  <div>
    <h1><?= icon('folder') ?> Departments</h1>
    <p class="sub"><?= count($depts) ?> department<?= count($depts) === 1 ? '' : 's' ?><?= $selectedName ? ' at ' . e($selectedName) : '' ?></p>
  </div>
</div>

<!-- Four-section cascading filter -->
<div style="margin-bottom:20px">
  <form method="get" class="ajax-nav" id="dept-filter" style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px 20px">
    <input type="hidden" name="r" value="admin/departments">
    <div style="display:flex;gap:14px;align-items:end;flex-wrap:wrap">

      <!-- 1. Region -->
      <div style="flex:1;min-width:180px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">Region</label>
        <select class="input" name="region" id="f-region" onchange="this.form.submit()" style="padding:10px 14px;width:100%">
          <option value="" <?= !$region ? 'selected' : '' ?>>— Select Region —</option>
          <?php foreach ($regions as $r): ?>
            <option value="<?= e($r) ?>" <?= $region === $r ? 'selected' : '' ?>><?= e($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 2. Zone — enabled only when region selected -->
      <div style="flex:1;min-width:180px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">Zone</label>
        <select class="input" name="zone" id="f-zone" onchange="this.form.submit()" style="padding:10px 14px;width:100%" <?= !$region ? 'disabled' : '' ?>>
          <?php if (!$region): ?>
            <option value="">— Select region first —</option>
          <?php else: ?>
            <option value="" <?= !$zone ? 'selected' : '' ?>>All Zones</option>
            <?php foreach ($allZones as $z): ?>
              <option value="<?= e($z) ?>" <?= $zone === $z ? 'selected' : '' ?>><?= e($z) ?></option>
            <?php endforeach; ?>
            <?php if (!$allZones): ?>
              <option value="" disabled>No zones in this region</option>
            <?php endif; ?>
          <?php endif; ?>
        </select>
      </div>

      <!-- 3. School Level — enabled only when region selected -->
      <div style="flex:1;min-width:180px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">School Level</label>
        <select class="input" name="type" id="f-type" onchange="this.form.submit()" style="padding:10px 14px;width:100%" <?= !$region ? 'disabled' : '' ?>>
          <?php if (!$region): ?>
            <option value="">— Select region first —</option>
          <?php else: ?>
            <option value="" <?= !$type ? 'selected' : '' ?>>All Levels</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e(ucfirst($t)) ?></option>
            <?php endforeach; ?>
            <?php if (!$types): ?>
              <option value="" disabled>No levels here</option>
            <?php endif; ?>
          <?php endif; ?>
        </select>
      </div>

      <!-- 4. School Name — enabled only when region selected -->
      <div style="flex:1;min-width:180px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">School Name</label>
        <select class="input" name="school" id="f-school" onchange="this.form.submit()" style="padding:10px 14px;width:100%" <?= !$region ? 'disabled' : '' ?>>
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

      <!-- Reset -->
      <?php if ($hasFilter): ?>
      <a class="btn btn-ghost ajax-nav" href="<?= e(url('admin/departments')) ?>" style="margin-bottom:2px;white-space:nowrap"><?= icon('x') ?> Reset</a>
      <?php endif; ?>

    </div>
  </form>
</div>

<!-- Departments table -->
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden">
  <table class="table" style="margin:0">
    <thead>
      <tr>
        <th style="width:50px;text-align:center">#</th>
        <th><a class="ajax-nav sort-link" href="<?= e($dSortUrl('name')) ?>">Department<span class="sort-arrow<?= $sort === 'name' ? ' active' : '' ?>"><?= $sort === 'name' && $dir === 'desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <?php if (!$schoolId): ?>
        <th style="width:200px">School</th>
        <?php endif; ?>
        <th style="width:180px">Head</th>
        <th style="width:90px;text-align:center"><a class="ajax-nav sort-link" href="<?= e($dSortUrl('members')) ?>">Members<span class="sort-arrow<?= $sort === 'members' ? ' active' : '' ?>"><?= $sort === 'members' && $dir === 'desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th style="width:100px;text-align:center"><a class="ajax-nav sort-link" href="<?= e($dSortUrl('status')) ?>">Status<span class="sort-arrow<?= $sort === 'status' ? ' active' : '' ?>"><?= $sort === 'status' && $dir === 'desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0; foreach ($depts as $d): ?>
      <tr class="<?= $d['status'] === 'archived' ? 'row-muted' : '' ?>">
        <td style="text-align:center;font-family:var(--font-mono,monospace);font-size:12px;color:var(--text-faint)"><?= $i + 1 ?></td>
        <td><a href="<?= e(url('admin/department&id=' . $d['id'])) ?>" class="small"><b><?= e($d['name']) ?></b></a></td>
        <?php if (!$schoolId): ?>
        <td><span class="badge badge-muted"><?= e($d['school_name']) ?></span></td>
        <?php endif; ?>
        <td class="small"><?= e($d['head']) ?: '—' ?></td>
        <td style="text-align:center"><?= (int)$d['members'] ?></td>
        <td style="text-align:center">
          <span class="badge <?= $d['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($d['status']) ?></span>
        </td>
      </tr>
      <?php $i++; endforeach; ?>
      <?php if (!$depts): ?>
      <tr><td colspan="<?= $schoolId ? 5 : 6 ?>" style="text-align:center;color:var(--muted);padding:24px"><?= $hasFilter ? 'No departments match the selected filters.' : 'Select a region to begin filtering.' ?></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
