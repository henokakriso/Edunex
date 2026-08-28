<?php /* Admin subjects view — 5-section cascading filter: Region → Zone → Level → School → Department */
$hasFilter = $region || $zone || $type || $schoolId || $deptId;
$sSortUrl = fn($col) => url('admin/subjects?' . http_build_query(array_filter(['region'=>$region,'zone'=>$zone,'type'=>$type,'school'=>$schoolId,'dept'=>$deptId,'sort'=>$col,'dir'=>$sort===$col && $dir==='asc' ? 'desc' : 'asc'], fn($x) => $x !== '')));
?>
<div class="page-head" style="margin-bottom:18px">
  <div>
    <h1><?= icon('books') ?> Subjects</h1>
    <p class="sub"><?= count($subjects) ?> subject<?= count($subjects) === 1 ? '' : 's' ?></p>
  </div>
</div>

<!-- Five-section cascading filter -->
<div style="margin-bottom:20px">
  <form method="get" class="ajax-nav" style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px 20px">
    <input type="hidden" name="r" value="admin/subjects">
    <div style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">

      <!-- 1. Region -->
      <div style="flex:1;min-width:160px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">Region</label>
        <select class="input" name="region" onchange="this.form.submit()" style="padding:10px 14px;width:100%">
          <option value="" <?= !$region ? 'selected' : '' ?>>— Select Region —</option>
          <?php foreach ($regions as $r): ?>
            <option value="<?= e($r) ?>" <?= $region === $r ? 'selected' : '' ?>><?= e($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 2. Zone -->
      <div style="flex:1;min-width:160px">
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

      <!-- 3. School Level -->
      <div style="flex:1;min-width:160px">
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

      <!-- 4. School Name -->
      <div style="flex:1;min-width:160px">
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

      <!-- 5. Department -->
      <div style="flex:1;min-width:160px">
        <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">Department</label>
        <select class="input" name="dept" onchange="this.form.submit()" style="padding:10px 14px;width:100%" <?= !$schoolId ? 'disabled' : '' ?>>
          <?php if (!$schoolId): ?>
            <option value="">— Select school first —</option>
          <?php else: ?>
            <option value="" <?= !$deptId ? 'selected' : '' ?>>All Departments</option>
            <?php foreach ($allDepts as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= $deptId == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
            <?php if (!$allDepts): ?>
              <option value="" disabled>No departments here</option>
            <?php endif; ?>
          <?php endif; ?>
        </select>
      </div>

      <!-- Reset -->
      <?php if ($hasFilter): ?>
      <a class="btn btn-ghost ajax-nav" href="<?= e(url('admin/subjects')) ?>" style="margin-bottom:2px;white-space:nowrap"><?= icon('x') ?> Reset</a>
      <?php endif; ?>

    </div>
  </form>
</div>

<!-- Subjects table -->
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden">
  <table class="table" style="margin:0">
    <thead>
      <tr>
        <th style="width:50px;text-align:center">#</th>
        <th><a class="ajax-nav sort-link" href="<?= e($sSortUrl('name')) ?>">Subject<span class="sort-arrow<?= $sort==='name' ? ' active' : '' ?>"><?= $sort==='name' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th style="width:100px"><a class="ajax-nav sort-link" href="<?= e($sSortUrl('code')) ?>">Code<span class="sort-arrow<?= $sort==='code' ? ' active' : '' ?>"><?= $sort==='code' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <?php if (!$schoolId): ?>
        <th style="width:200px"><a class="ajax-nav sort-link" href="<?= e($sSortUrl('school')) ?>">School<span class="sort-arrow<?= $sort==='school' ? ' active' : '' ?>"><?= $sort==='school' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <?php endif; ?>
        <?php if (!$deptId): ?>
        <th style="width:180px"><a class="ajax-nav sort-link" href="<?= e($sSortUrl('department')) ?>">Department<span class="sort-arrow<?= $sort==='department' ? ' active' : '' ?>"><?= $sort==='department' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <?php endif; ?>
        <th style="width:100px;text-align:center"><a class="ajax-nav sort-link" href="<?= e($sSortUrl('status')) ?>">Status<span class="sort-arrow<?= $sort==='status' ? ' active' : '' ?>"><?= $sort==='status' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0; foreach ($subjects as $s): ?>
      <tr class="<?= $s['status'] === 'archived' ? 'row-muted' : '' ?>">
        <td style="text-align:center;font-family:var(--font-mono,monospace);font-size:12px;color:var(--text-faint)"><?= $i + 1 ?></td>
        <td><b class="small"><?= e($s['name']) ?></b></td>
        <td class="small mono"><?= e($s['code']) ?: '—' ?></td>
        <?php if (!$schoolId): ?>
        <td><span class="badge badge-muted"><?= e($s['school_name']) ?></span></td>
        <?php endif; ?>
        <?php if (!$deptId): ?>
        <td class="small"><?= e($s['dept_name']) ?: '—' ?></td>
        <?php endif; ?>
        <td style="text-align:center"><span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($s['status']) ?></span></td>
      </tr>
      <?php $i++; endforeach; ?>
      <?php if (!$subjects): ?>
      <tr>
        <td colspan="<?= $schoolId ? ($deptId ? 5 : 5) : 6 ?>" style="text-align:center;color:var(--muted);padding:24px">
          <?= $hasFilter ? 'No subjects match the selected filters.' : 'Select a region to begin filtering.' ?>
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Edit modal -->
<div class="modal-dialog" id="edit-subject-modal">
  <div class="modal-box card">
    <h3 class="card-title" style="margin-top:0"><?= icon('edit') ?> Edit subject</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="update_subject" id="es-id" value="">
      <div class="flex-col" style="margin-bottom:10px"><label class="small faint">School *</label>
        <select class="input" name="school_id" id="es-school" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="flex-col" style="margin-bottom:10px"><label class="small faint">Department</label>
        <select class="input" name="department_id" id="es-dept"><option value="0">— none —</option><?php foreach ($allDepts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="flex-col" style="margin-bottom:10px"><label class="small faint">Name *</label><input class="input" name="name" id="es-name" required></div>
      <div class="flex-col" style="margin-bottom:14px"><label class="small faint">Code</label><input class="input" name="code" id="es-code"></div>
      <div class="flex gap-6"><button class="btn btn-primary"><?= icon('save') ?> Save</button><button type="button" class="btn" onclick="closeSubjectModal()">Cancel</button></div>
    </form>
  </div>
</div>

<script>
function editSubject(id, name, code, schoolId, deptId) {
  document.getElementById('es-id').value = id;
  document.getElementById('es-name').value = name;
  document.getElementById('es-code').value = code;
  document.getElementById('es-school').value = schoolId;
  document.getElementById('es-dept').value = deptId;
  document.getElementById('edit-subject-modal').classList.add('open');
}
function closeSubjectModal() { document.getElementById('edit-subject-modal').classList.remove('open'); }
document.getElementById('edit-subject-modal').addEventListener('click', e => { if (e.target.id === 'edit-subject-modal') closeSubjectModal(); });
</script>
