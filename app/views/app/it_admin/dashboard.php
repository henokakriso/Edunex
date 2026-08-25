<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem">
  <div class="card stat"><div class="stat-num" style="color:var(--warning,#f59e0b)"><?= e($stats['open']) ?></div><div class="stat-lbl">Open</div></div>
  <div class="card stat"><div class="stat-num" style="color:var(--info,#3b82f6)"><?= e($stats['in_progress']) ?></div><div class="stat-lbl">In Progress</div></div>
  <div class="card stat"><div class="stat-num" style="color:var(--success,#10b981)"><?= e($stats['resolved_today']) ?></div><div class="stat-lbl">Resolved Today</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['total']) ?></div><div class="stat-lbl">Total Tickets</div></div>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:1.5rem">
  <div class="card">
    <div class="card-head"><h2>Quick Actions</h2></div>
    <div style="padding:1rem">
      <a href="<?= url('index.php?r=it_admin/fix') ?>" class="btn btn-primary" style="width:100%;margin-bottom:0.5rem">Enter Fix Token</a>
      <a href="<?= url('index.php?r=it_admin/tickets&status=open') ?>" class="btn btn-warning" style="width:100%;margin-bottom:0.5rem">View Open Tickets</a>
      <a href="<?= url('index.php?r=it_admin/audit') ?>" class="btn btn-ghost" style="width:100%">View Audit Log</a>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2>Recent Tickets</h2></div>
    <div style="overflow-x:auto">
      <table class="table">
        <thead><tr><th>#</th><th>From</th><th>Page</th><th>Status</th><th>When</th></tr></thead>
        <tbody>
        <?php if (empty($recentTickets)): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--muted)">No tickets yet.</td></tr>
        <?php else: foreach ($recentTickets as $t): ?>
          <tr>
            <td><a href="<?= url('index.php?r=it_admin/ticket&id='.$t['id']) ?>"><?= e($t['id']) ?></a></td>
            <td><?= e($t['requested_by_name']) ?></td>
            <td><?= e($t['page_label'] ?: $t['page_route']) ?></td>
            <td><span class="badge badge-<?= $t['status']==='open'?'warning':($t['status']==='in_progress'?'info':($t['status']==='resolved'?'success':'muted')) ?>"><?= e($t['status']) ?></span></td>
            <td><?= e($t['created_at']) ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
