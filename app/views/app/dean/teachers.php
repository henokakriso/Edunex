<?php /* Dean teachers — list faculty teachers, move between departments */
?>
<div class="page-head">
  <div>
    <h1><?= icon('users') ?> Teachers</h1>
    <p class="sub"><?= e($faculty['name']) ?> — <?= count($rows) ?> teacher(s)</p>
  </div>
</div>

<div class="table-wrap">
  <table class="table">
    <thead><tr><th>Teacher</th><th>Department</th><th>Courses</th><th>Status</th><th style="width:220px">Move to department</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['name']) ?></b><p class="tiny faint"><?= e($r['email']) ?></p></td>
          <td><span class="badge"><?= e($r['department']) ?></span></td>
          <td><?= (int)$r['courses'] ?></td>
          <td><span class="badge <?= $r['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($r['status']) ?></span></td>
          <td>
            <form method="post" class="flex gap-6">
              <?= csrf_field() ?>
              <input type="hidden" name="teacher_id" value="<?= (int)$r['id'] ?>">
              <select class="input" name="department_id">
                <?php foreach ($depts as $d): ?>
                  <option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm" name="move_teacher" value="1"><?= icon('swap') ?> Move</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="muted">No teachers in this faculty yet. Teachers are added by the director; assign them to this faculty's departments.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
