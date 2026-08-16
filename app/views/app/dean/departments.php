<?php /* Dean departments — CRUD inside the faculty */
?>
<div class="page-head">
  <div>
    <h1><?= icon('folder') ?> Departments</h1>
    <p class="sub"><?= e($faculty['name']) ?> — <?= e($faculty['school_name']) ?></p>
  </div>
  <button class="btn btn-primary" data-open-modal="new-dept-modal"><?= icon('plus') ?> New department</button>
</div>

<div class="table-wrap">
  <table class="table">
    <thead><tr><th>Department</th><th>Head</th><th>Teachers</th><th>Courses</th><th>Status</th><th style="width:110px">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['name']) ?></b></td>
          <td><?= e($r['head'] ?: '—') ?></td>
          <td><?= (int)$r['teachers'] ?></td>
          <td><?= (int)$r['courses'] ?></td>
          <td><span class="badge <?= $r['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($r['status']) ?></span></td>
          <td>
            <?php if ($r['status'] === 'active'): ?>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-danger" name="archive_dept" value="<?= (int)$r['id'] ?>" onclick="return confirm('Archive this department?')"><?= icon('trash') ?> Archive</button>
              </form>
            <?php else: ?>
              <span class="tiny faint">archived</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="muted">No departments yet in this faculty.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal-dialog" id="new-dept-modal">
  <form method="post" class="modal-box" style="padding:22px">
    <?= csrf_field() ?>
    <h3 class="card-title"><?= icon('plus') ?> Create department</h3>
    <div class="grid2" style="margin-top:6px">
      <div class="flex-col"><label class="small faint">Name *</label><input class="input" name="name" required></div>
      <div class="flex-col"><label class="small faint">Graduation credits</label><input class="input" type="number" name="required_credits" value="120" min="0" max="600"></div>
      <div class="flex-col"><label class="small faint">Head</label><input class="input" name="head"></div>
    </div>
    <div class="flex gap-10" style="margin-top:16px">
      <button class="btn btn-success" name="create_dept" value="1"><?= icon('rocket') ?> Create</button>
      <button type="button" class="btn btn-ghost" data-close-modal="new-dept-modal">Cancel</button>
    </div>
  </form>
</div>
