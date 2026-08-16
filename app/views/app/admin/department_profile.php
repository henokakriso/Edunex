<?php /* Admin department profile */
?>
<div class="page-head">
  <div>
    <a class="small faint" href="<?= e(url('admin/departments')) ?>">← All departments</a>
    <h1 style="margin-top:4px"><?= icon('folder') ?> <?= e($dept['name']) ?> <span class="badge <?= $dept['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($dept['status']) ?></span></h1>
    <p class="sub"><?= e($dept['school_name']) ?><?= $dept['head'] ? ' · Head: ' . e($dept['head']) : '' ?> · <?= count($members) ?> members · <?= count($subjects) ?> subjects · <?= count($courses) ?> courses</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('edit-dept-modal').style.display='flex'"><?= icon('edit') ?> Edit</button>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;margin-bottom:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('users') ?> Members (<?= count($members) ?>)</h3>
    <?php foreach ($members as $m): ?>
      <a class="list-row" href="<?= e(url('admin/user&id=' . $m['id'])) ?>" style="text-decoration:none;padding:7px 0">
        <div class="avatar"><?= e(mb_substr($m['name'], 0, 1)) ?></div>
        <div class="flex-1 small"><b><?= e($m['name']) ?></b><p class="tiny faint"><?= e($m['role']) ?> · <?= e($m['email']) ?></p></div>
        <span class="badge <?= $m['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($m['status']) ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$members): ?><p class="muted small">No members assigned.</p><?php endif; ?>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('books') ?> Subjects (<?= count($subjects) ?>)</h3>
    <div class="flex gap-6" style="flex-wrap:wrap">
      <?php foreach ($subjects as $sb): ?>
        <a class="badge <?= $sb['status'] === 'archived' ? 'badge-muted' : 'badge-muted' ?>" href="<?= e(url('admin/subjects')) ?>" style="text-decoration:none"><?= e($sb['name']) ?><?= $sb['status'] === 'archived' ? ' (archived)' : '' ?></a>
      <?php endforeach; ?>
      <?php if (!$subjects): ?><p class="muted small">No subjects in this department.</p><?php endif; ?>
    </div>
    <h3 class="card-title" style="margin-top:16px"><?= icon('graduation') ?> Courses (<?= count($courses) ?>)</h3>
    <?php foreach ($courses as $c): ?>
      <a class="list-row" href="<?= e(url('courses/view&id=' . $c['id'])) ?>" style="text-decoration:none;padding:6px 0">
        <div class="flex-1 small"><b><?= e($c['title']) ?></b></div>
        <span class="tiny faint"><?= (int)$c['students'] ?> students</span>
        <span class="badge <?= $c['status'] === 'published' ? 'badge-success' : ($c['status'] === 'archived' ? 'badge-warning' : 'badge-muted') ?>"><?= e($c['status']) ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$courses): ?><p class="muted small">No courses.</p><?php endif; ?>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> Recent activity</h3>
    <?php foreach ($activity as $a): ?>
      <div class="list-row" style="padding:6px 0">
        <div class="flex-1 small"><?= e($a['user_name'] ?: 'System') ?> — <?= e($a['description']) ?></div>
        <div class="tiny faint"><?= e(time_ago($a['created_at'])) ?></div>
      </div>
    <?php endforeach; ?>
    <?php if (!$activity): ?><p class="muted small">No activity yet.</p><?php endif; ?>
  </div>
</div>

<div class="modal-dialog" id="edit-dept-modal">
  <div class="modal-box card">
    <h3 class="card-title" style="margin-top:0"><?= icon('edit') ?> Edit department</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="update_dept" value="1">
      <div class="flex-col" style="margin-bottom:10px"><label class="small faint">School *</label>
        <select class="input" name="school_id" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>" <?= $s['id'] == $dept['school_id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="flex-col" style="margin-bottom:10px"><label class="small faint">Name *</label><input class="input" name="name" value="<?= e($dept['name']) ?>" required></div>
      <div class="flex-col" style="margin-bottom:14px"><label class="small faint">Head</label><input class="input" name="head" value="<?= e($dept['head']) ?>"></div>
      <div class="flex gap-6"><button class="btn btn-primary"><?= icon('save') ?> Save</button><button type="button" class="btn" onclick="document.getElementById('edit-dept-modal').classList.remove('open')">Cancel</button></div>
    </form>
  </div>
</div>
<script>
document.getElementById('edit-dept-modal').addEventListener('click', e => { if (e.target.id === 'edit-dept-modal') document.getElementById('edit-dept-modal').classList.remove('open'); });
</script>
