<?php /* Admin subjects view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('books') ?> Subjects</h1>
    <p class="sub"><?= count($subjects) ?> subject<?= count($subjects) === 1 ? '' : 's' ?></p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('new-subject').style.display='block';this.style.display='none'">+ New subject</button>
</div>

<form method="post" class="card" id="new-subject" style="display:none;margin-bottom:18px">
  <?= csrf_field() ?>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">School *</label>
      <select class="input" name="school_id" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Department</label>
      <select class="input" name="department_id"><option value="0">— none —</option><?php foreach ($depts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Name *</label><input class="input" name="name" required placeholder="Mathematics"></div>
    <div class="flex-col"><label class="small faint">Code</label><input class="input" name="code" placeholder="MATH"></div>
  </div>
  <button class="btn btn-success" name="create_subject" value="1"><?= icon('plus') ?> Create</button>
</form>

<div class="card">
  <?php $sSortUrl = fn($col) => url('admin/subjects?' . http_build_query(['sort'=>$col, 'dir'=> $sort===$col && $dir==='asc' ? 'desc' : 'asc'])); ?>
  <table class="table">
    <thead><tr>
      <th><a class="ajax-nav sort-link" href="<?= e($sSortUrl('name')) ?>">Subject<?php if($sort==='name'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($sSortUrl('code')) ?>">Code<?php if($sort==='code'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($sSortUrl('school')) ?>">School<?php if($sort==='school'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($sSortUrl('department')) ?>">Department<?php if($sort==='department'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($sSortUrl('status')) ?>">Status<?php if($sort==='status'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th>Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($subjects as $s): ?>
        <tr class="<?= $s['status'] === 'archived' ? 'row-muted' : '' ?>">
          <td><b class="small"><?= e($s['name']) ?></b></td>
          <td class="small mono"><?= e($s['code']) ?: '—' ?></td>
          <td class="small"><?= e($s['school_name']) ?></td>
          <td class="small"><?= e($s['dept_name']) ?: '—' ?></td>
          <td><span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($s['status']) ?></span></td>
          <td class="actions">
              <div class="row-act">
                <button class="icon-btn" title="Edit" onclick="editSubject(<?= (int)$s['id'] ?>, '<?= e(addslashes($s['name'])) ?>', '<?= e(addslashes($s['code'])) ?>', <?= (int)$s['school_id'] ?>, <?= (int)($s['department_id'] ?? 0) ?>)"><?= icon('edit') ?></button>
                <?php if ($s['status'] === 'active'): ?>
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="archive_subject" value="<?= (int)$s['id'] ?>"><button class="icon-btn warn" title="Archive" data-confirm="Archive <?= e($s['name']) ?>?"><?= icon('box') ?></button></form>
                <?php else: ?>
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="restore_subject" value="<?= (int)$s['id'] ?>"><button class="icon-btn success" title="Restore" data-confirm="Restore <?= e($s['name']) ?>?"><?= icon('refresh') ?></button></form>
                <?php endif; ?>
                <form method="post" class="inline" data-confirm="Delete subject <?= e($s['name']) ?>?"><?= csrf_field() ?><input type="hidden" name="delete_subject" value="<?= (int)$s['id'] ?>"><button class="icon-btn danger" title="Delete"><?= icon('trash') ?></button></form>
              </div>
            </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!$subjects): ?><p class="muted small" style="padding:12px">No subjects yet.</p><?php endif; ?>
</div>

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
        <select class="input" name="department_id" id="es-dept"><option value="0">— none —</option><?php foreach ($depts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select>
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
