<?php /* Registrar audit — activity of school users */
?>
<div class="page-head">
  <div>
    <h1><?= icon('shield') ?> Audit Log</h1>
    <p class="sub">Actions by users in your school (latest 100)</p>
  </div>
</div>

<div class="table-wrap">
  <table class="table">
    <thead><tr><th>User</th><th>Action</th><th>Detail</th><th>When</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $a): ?>
        <tr>
          <td><b><?= e($a['user_name'] ?: 'System') ?></b><p class="tiny faint"><?= e($a['email'] ?: '—') ?></p></td>
          <td><span class="badge"><?= e($a['action']) ?></span></td>
          <td class="small"><?= e($a['detail']) ?></td>
          <td class="small faint"><?= e(time_ago($a['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="4" class="muted">No activity yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
