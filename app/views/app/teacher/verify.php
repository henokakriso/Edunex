<?php /* Homeroom verification queue */
?>
<div class="page-head">
  <div>
    <h1><?= icon('check-circle') ?> Verify Students</h1>
    <p class="sub">New student accounts must be verified within <b>24 hours</b>
      <?php if ($overdue > 0): ?><span class="badge badge-danger"><?= (int)$overdue ?> overdue</span><?php endif; ?>
    </p>
  </div>
</div>

<?php if (!$pending && !$other): ?>
  <div class="alert alert-info"><?= icon('spark') ?> No students waiting for verification.</div>
<?php endif; ?>

<?php if ($pending): ?>
  <h3 class="small" style="margin:14px 0 8px">Your homeroom class</h3>
  <div class="card" style="padding:0;overflow:hidden">
    <table class="table">
      <thead><tr><th>Student</th><th>ID / Email</th><th>Class</th><th>Registered</th><th>Age</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($pending as $st):
          $age = floor((time() - strtotime($st['created_at'])) / 3600); ?>
          <tr>
            <td><b><?= e($st['first_name'] . ' ' . $st['last_name']) ?></b></td>
            <td class="tiny faint"><?= e($st['student_id'] ?? '') ?><br><?= e($st['email']) ?></td>
            <td><?= e($st['group_name'] ?? '—') ?></td>
            <td class="tiny"><?= e(date('M j, H:i', strtotime($st['created_at']))) ?></td>
            <td>
              <?php if ($age > 24): ?><span class="badge badge-danger"><?= (int)$age ?>h (overdue)</span>
              <?php else: ?><span class="badge badge-info"><?= (int)$age ?>h / 24h</span><?php endif; ?>
            </td>
            <td class="flex gap-8" style="justify-content:end">
              <form method="post"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="student_id" value="<?= (int)$st['id'] ?>">
                <button name="action" value="approve" class="btn btn-sm btn-success"><?= icon('check') ?> Approve</button></form>
              <form method="post"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="student_id" value="<?= (int)$st['id'] ?>">
                <button name="action" value="reject" class="btn btn-sm btn-danger" data-confirm="Reject this student?">✕ Reject</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php if ($other): ?>
  <h3 class="small" style="margin:18px 0 8px">Other pending accounts (no homeroom teacher assigned)</h3>
  <div class="card" style="padding:0;overflow:hidden">
    <table class="table">
      <thead><tr><th>Student</th><th>ID / Email</th><th>Class</th><th>Registered</th><th>Age</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($other as $st):
          $age = floor((time() - strtotime($st['created_at'])) / 3600); ?>
          <tr>
            <td><b><?= e($st['first_name'] . ' ' . $st['last_name']) ?></b></td>
            <td class="tiny faint"><?= e($st['student_id'] ?? '') ?><br><?= e($st['email']) ?></td>
            <td><?= e($st['group_name'] ?? '—') ?></td>
            <td class="tiny"><?= e(date('M j, H:i', strtotime($st['created_at']))) ?></td>
            <td>
              <?php if ($age > 24): ?><span class="badge badge-danger"><?= (int)$age ?>h (overdue)</span>
              <?php else: ?><span class="badge badge-info"><?= (int)$age ?>h / 24h</span><?php endif; ?>
            </td>
            <td class="flex gap-8" style="justify-content:end">
              <form method="post"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="student_id" value="<?= (int)$st['id'] ?>">
                <button name="action" value="approve" class="btn btn-sm btn-success"><?= icon('check') ?> Approve</button></form>
              <form method="post"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="student_id" value="<?= (int)$st['id'] ?>">
                <button name="action" value="reject" class="btn btn-sm btn-danger">✕ Reject</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
