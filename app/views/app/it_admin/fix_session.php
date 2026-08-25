<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
  <a href="<?= url('index.php?r=it_admin/tickets') ?>" class="btn btn-ghost">← Back to Tickets</a>
</div>

<div style="background:var(--warning-soft,#fef3c7);border:1px solid var(--warning,#f59e0b);border-radius:8px;padding:1rem;margin-bottom:1.5rem">
  <strong>Fix Session Active</strong> — You are fixing <b><?= e($ticket['page_label'] ?: $ticket['page_route']) ?></b> for <b><?= e($ticket['requested_by_name']) ?></b>.
  <br><small>You can ONLY access the page specified in this ticket. All actions are logged. Session expires in 2 hours.</small>
</div>

<div class="card" style="margin-bottom:1.5rem">
  <div class="card-head"><h2>Ticket #<?= e($ticket['id']) ?></h2></div>
  <div style="padding:1rem">
    <p><strong>User:</strong> <?= e($ticket['requested_by_name']) ?></p>
    <p><strong>Page:</strong> <?= e($ticket['page_label'] ?: $ticket['page_route']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(e($ticket['description'] ?? '')) ?></p>
  </div>
</div>

<div class="card" style="margin-bottom:1.5rem">
  <div class="card-head"><h2>Authorized Pages</h2></div>
  <div style="padding:1rem">
    <?php foreach ($scopes as $s): ?>
      <a href="<?= url('index.php?r='.ltrim($s['scope_route'], '/')) ?>" class="btn btn-primary" style="margin:0.25rem">
        <?= e($s['scope_label'] ?: $s['scope_route']) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <div class="card-head"><h2>Resolve Ticket</h2></div>
  <form method="POST" action="<?= url('index.php?r=it_admin/resolve') ?>" style="padding:1rem" onsubmit="return confirm('Mark as resolved?')">
    <?= csrf_field() ?>
    <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
    <div class="form-group">
      <label>Resolution Note</label>
      <textarea name="resolution_note" class="form-control" rows="3" placeholder="What did you fix?"></textarea>
    </div>
    <button class="btn btn-success" type="submit">Mark as Resolved</button>
  </form>
</div>
