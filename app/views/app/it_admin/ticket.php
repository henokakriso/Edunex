<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
  <a href="<?= url('index.php?r=it_admin/tickets') ?>" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="margin-bottom:1.5rem">
  <div class="card-head"><h2>Ticket Info</h2></div>
  <div class="grid" style="grid-template-columns:1fr 1fr;gap:1rem;padding:1rem">
    <div>
      <p><strong>Status:</strong> <span class="badge badge-<?= $ticket['status']==='open'?'warning':($ticket['status']==='in_progress'?'info':'success') ?>"><?= e($ticket['status']) ?></span></p>
      <p><strong>Requested By:</strong> <?= e($ticket['requested_by_name']) ?></p>
      <p><strong>Page:</strong> <?= e($ticket['page_label'] ?: $ticket['page_route']) ?></p>
      <p><strong>Route:</strong> <code><?= e($ticket['page_route']) ?></code></p>
    </div>
    <div>
      <p><strong>Description:</strong></p>
      <p><?= nl2br(e($ticket['description'] ?? 'No description')) ?></p>
      <p><strong>Created:</strong> <?= e($ticket['created_at']) ?></p>
      <?php if ($ticket['claimed_at']): ?><p><strong>Claimed:</strong> <?= e($ticket['claimed_at']) ?></p><?php endif; ?>
      <?php if ($ticket['resolved_at']): ?><p><strong>Resolved:</strong> <?= e($ticket['resolved_at']) ?></p><?php endif; ?>
    </div>
  </div>
</div>

<?php if ($ticket['status'] === 'in_progress' && (int)$ticket['it_admin_id'] === (int)$u['id']): ?>
<div class="card" style="margin-bottom:1.5rem">
  <div class="card-head"><h2>Fix Session</h2></div>
  <div style="padding:1rem">
    <a href="<?= url('index.php?r=it_admin/fix-session&id='.$ticket['id']) ?>" class="btn btn-primary">Continue Fix Session</a>
    <form method="POST" action="<?= url('index.php?r=it_admin/resolve') ?>" style="display:inline;margin-left:0.5rem" onsubmit="return confirm('Mark as resolved?')">
      <?= csrf_field() ?>
      <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
      <input type="text" name="resolution_note" placeholder="Resolution note (optional)" class="form-control" style="display:inline;width:auto">
      <button class="btn btn-success" type="submit">Resolve</button>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($scopes)): ?>
<div class="card" style="margin-bottom:1.5rem">
  <div class="card-head"><h2>Authorized Scopes</h2></div>
  <div style="padding:1rem">
    <?php foreach ($scopes as $s): ?>
      <span class="badge badge-info" style="margin:0.25rem"><?= e($s['scope_label'] ?: $s['scope_route']) ?></span>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-head"><h2>Activity Log</h2></div>
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>Admin</th><th>Action</th><th>Detail</th><th>Time</th></tr></thead>
      <tbody>
      <?php if (empty($logs)): ?>
        <tr><td colspan="4" style="text-align:center;color:var(--muted)">No activity yet.</td></tr>
      <?php else: foreach ($logs as $l): ?>
        <tr>
          <td><?= e($l['it_admin_name']) ?></td>
          <td><span class="badge"><?= e($l['action']) ?></span></td>
          <td><?= e($l['detail'] ?? '') ?></td>
          <td><?= e($l['created_at']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
