<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
  <a href="<?= url('index.php?r=it_admin/fix') ?>" class="btn btn-primary">Enter Fix Token</a>
</div>

<div style="margin-bottom:1rem">
  <a href="<?= url('index.php?r=it_admin/tickets') ?>" class="btn <?= !$currentStatus ? 'btn-primary' : 'btn-ghost' ?>">All</a>
  <a href="<?= url('index.php?r=it_admin/tickets&status=open') ?>" class="btn <?= $currentStatus==='open' ? 'btn-warning' : 'btn-ghost' ?>">Open</a>
  <a href="<?= url('index.php?r=it_admin/tickets&status=in_progress') ?>" class="btn <?= $currentStatus==='in_progress' ? 'btn-info' : 'btn-ghost' ?>">In Progress</a>
  <a href="<?= url('index.php?r=it_admin/tickets&status=resolved') ?>" class="btn <?= $currentStatus==='resolved' ? 'btn-success' : 'btn-ghost' ?>">Resolved</a>
</div>

<div class="card">
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>#</th><th>Requested By</th><th>Page</th><th>Description</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (empty($tickets)): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--muted)">No tickets found.</td></tr>
      <?php else: foreach ($tickets as $t): ?>
        <tr>
          <td><a href="<?= url('index.php?r=it_admin/ticket&id='.$t['id']) ?>"><?= e($t['id']) ?></a></td>
          <td><?= e($t['requested_by_name']) ?></td>
          <td><?= e($t['page_label'] ?: $t['page_route']) ?></td>
          <td><?= e(mb_substr($t['description'] ?? '', 0, 60)) ?></td>
          <td><span class="badge badge-<?= $t['status']==='open'?'warning':($t['status']==='in_progress'?'info':($t['status']==='resolved'?'success':'muted')) ?>"><?= e($t['status']) ?></span></td>
          <td><?= e($t['created_at']) ?></td>
          <td>
            <?php if ($t['status'] === 'open'): ?>
              <form method="POST" action="<?= url('index.php?r=it_admin/fix') ?>" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($t['api_token']) ?>">
                <button class="btn btn-sm btn-primary" type="submit">Claim & Fix</button>
              </form>
            <?php elseif ($t['status'] === 'in_progress' && (int)$t['it_admin_id'] === (int)($_GET['_uid'] ?? 0)): ?>
              <a href="<?= url('index.php?r=it_admin/fix-session&id='.$t['id']) ?>" class="btn btn-sm btn-info">Continue</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
