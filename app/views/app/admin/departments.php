<?php /* Admin departments view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('folder') ?> Departments</h1>
    <p class="sub"><?= count($depts) ?> department<?= count($depts) === 1 ? '' : 's' ?> · click a row for the full profile</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('new-dept').style.display='block';this.style.display='none'">+ New department</button>
</div>

<form method="post" class="card" id="new-dept" style="display:none;margin-bottom:18px">
  <?= csrf_field() ?>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">School *</label>
      <select class="input" name="school_id" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Name *</label><input class="input" name="name" required placeholder="Computer Science"></div>
    <div class="flex-col"><label class="small faint">Head</label><input class="input" name="head" placeholder="Dr. …"></div>
  </div>
  <button class="btn btn-success" name="create_dept" value="1"><?= icon('plus') ?> Create</button>
</form>

<div class="card">
  <?php $dSortUrl = fn($col) => url('admin/departments?' . http_build_query(['sort'=>$col, 'dir'=> $sort===$col && $dir==='asc' ? 'desc' : 'asc'])); ?>
  <table class="table">
    <thead><tr>
      <th><a class="ajax-nav sort-link" href="<?= e($dSortUrl('name')) ?>">Department<span class="sort-arrow<?= $sort==='name' ? ' active' : '' ?>"><?= $sort==='name' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($dSortUrl('school')) ?>">School<span class="sort-arrow<?= $sort==='school' ? ' active' : '' ?>"><?= $sort==='school' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th>Head</th>
      <th><a class="ajax-nav sort-link" href="<?= e($dSortUrl('members')) ?>">Members<span class="sort-arrow<?= $sort==='members' ? ' active' : '' ?>"><?= $sort==='members' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($dSortUrl('status')) ?>">Status<span class="sort-arrow<?= $sort==='status' ? ' active' : '' ?>"><?= $sort==='status' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th>Actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($depts as $d): ?>
        <tr class="<?= $d['status'] === 'archived' ? 'row-muted' : '' ?>">
          <td><a href="<?= e(url('admin/department&id=' . $d['id'])) ?>"><b class="small"><?= e($d['name']) ?></b></a></td>
          <td class="small"><?= e($d['school_name']) ?></td>
          <td class="small"><?= e($d['head']) ?: '—' ?></td>
          <td class="small"><?= (int)$d['members'] ?></td>
          <td>
            <span class="badge <?= $d['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($d['status']) ?></span>
          </td>
          <td class="actions">
              <div class="row-act">
                <a class="icon-btn" title="Open profile" href="<?= e(url('admin/department&id=' . $d['id'])) ?>"><?= icon('eye') ?></a>
                <button class="icon-btn" title="Edit" onclick="editDept(<?= (int)$d['id'] ?>, '<?= e(addslashes($d['name'])) ?>', '<?= e(addslashes($d['head'])) ?>', <?= (int)$d['school_id'] ?>)"><?= icon('edit') ?></button>
                <?php if ($d['status'] === 'active'): ?>
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="archive_dept" value="<?= (int)$d['id'] ?>"><button class="icon-btn warn" title="Archive" data-confirm="Archive <?= e($d['name']) ?>?"><?= icon('box') ?></button></form>
                <?php else: ?>
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="restore_dept" value="<?= (int)$d['id'] ?>"><button class="icon-btn success" title="Restore" data-confirm="Restore <?= e($d['name']) ?>?"><?= icon('refresh') ?></button></form>
                <?php endif; ?>
                <form method="post" class="inline" data-confirm="Delete department <?= e($d['name']) ?>?"><?= csrf_field() ?><input type="hidden" name="delete_dept" value="<?= (int)$d['id'] ?>"><button class="icon-btn danger" title="Delete"><?= icon('trash') ?></button></form>
              </div>
            </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!$depts): ?><p class="muted small" style="padding:12px">No departments yet.</p><?php endif; ?>
</div>

<div class="modal-dialog" id="edit-dept-modal">
  <div class="modal-box card">
    <h3 class="card-title" style="margin-top:0"><?= icon('edit') ?> Edit department</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="update_dept" id="ed-id" value="">
      <div class="flex-col" style="margin-bottom:10px"><label class="small faint">School *</label>
        <select class="input" name="school_id" id="ed-school" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="flex-col" style="margin-bottom:10px"><label class="small faint">Name *</label><input class="input" name="name" id="ed-name" required></div>
      <div class="flex-col" style="margin-bottom:14px"><label class="small faint">Head</label><input class="input" name="head" id="ed-head"></div>
      <div class="flex gap-6"><button class="btn btn-primary"><?= icon('save') ?> Save</button><button type="button" class="btn" onclick="closeDeptModal()">Cancel</button></div>
    </form>
  </div>
</div>

<script>
function editDept(id, name, head, schoolId) {
  document.getElementById('ed-id').value = id;
  document.getElementById('ed-name').value = name;
  document.getElementById('ed-head').value = head;
  document.getElementById('ed-school').value = schoolId;
  document.getElementById('edit-dept-modal').classList.add('open');
}
function closeDeptModal() { document.getElementById('edit-dept-modal').classList.remove('open'); }
document.getElementById('edit-dept-modal').addEventListener('click', e => { if (e.target.id === 'edit-dept-modal') closeDeptModal(); });
</script>
