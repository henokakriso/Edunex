<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>
<div class="card">
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>User</th><th>Action</th><th>Detail</th><th>Time</th></tr></thead>
      <tbody>
      <?php if (empty($logs)): ?>
        <tr><td colspan="4" style="text-align:center;color:var(--muted)">No activity logged.</td></tr>
      <?php else: foreach ($logs as $l): ?>
        <tr>
          <td><?= e($l['user_name'] ?? 'System') ?></td>
          <td><span class="badge"><?= e($l['action']) ?></span></td>
          <td><?= e($l['detail'] ?? '') ?></td>
          <td><?= e($l['created_at']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
