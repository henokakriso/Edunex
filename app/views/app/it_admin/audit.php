<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>
<div class="card">
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>Ticket</th><th>IT Admin</th><th>Requested By</th><th>Action</th><th>Detail</th><th>IP</th><th>Time</th></tr></thead>
      <tbody>
      <?php if (empty($logs)): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--muted)">No activity logged.</td></tr>
      <?php else: foreach ($logs as $l): ?>
        <tr>
          <td><a href="<?= url('index.php?r=it_admin/ticket&id='.$l['ticket_id']) ?>">#<?= e($l['ticket_id']) ?></a></td>
          <td><?= e($l['it_admin_name']) ?></td>
          <td><?= e($l['requested_by_name']) ?></td>
          <td><span class="badge"><?= e($l['action']) ?></span></td>
          <td><?= e($l['detail'] ?? '') ?></td>
          <td><code><?= e($l['ip_address'] ?? '') ?></code></td>
          <td><?= e($l['created_at']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
