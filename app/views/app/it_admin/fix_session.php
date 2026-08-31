<?php
$adminStatuses = ['idle' => 'Paused', 'investigating' => 'Investigating', 'fixing' => 'Fixing', 'testing' => 'Testing'];
$currentStatus = $ticket['admin_status'] ?? 'investigating';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <h1 style="margin:0;font-size:20px"><?= e($title) ?></h1>
  <a href="<?= url('index.php?r=it_admin/tickets') ?>" class="btn btn-ghost btn-sm">← Back to Tickets</a>
</div>

<!-- Real-time Status Bar -->
<div style="background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
  <span style="font-size:13px;font-weight:600;color:var(--text-dim)">Your status:</span>
  <?php foreach ($adminStatuses as $key => $label): ?>
    <button type="button" class="btn <?= $currentStatus === $key ? 'btn-primary' : 'btn-ghost' ?> btn-sm" onclick="setAdminStatus(<?= (int)$ticket['id'] ?>,'<?= $key ?>',this)"><?= $label ?></button>
  <?php endforeach; ?>
  <span id="heartbeat-status" style="margin-left:auto;font-size:11px;color:var(--text-dim)"></span>
</div>

<div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:12px;padding:14px 18px;margin-bottom:20px;font-size:13px;line-height:1.5">
  <strong>Fix Session Active</strong> — You are fixing <b><?= e($ticket['page_label'] ?: $ticket['page_route']) ?></b> for <b><?= e($ticket['requested_by_name']) ?></b>.
  <br><span style="color:var(--text-dim)">You can ONLY access the page specified in this ticket. All actions are logged. Session expires in 2 hours.</span>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
  <div class="card" style="margin:0">
    <div style="padding:18px 20px">
      <div style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Ticket Details</div>
      <p style="font-size:13px;margin:0 0 6px"><b>User:</b> <?= e($ticket['requested_by_name']) ?></p>
      <p style="font-size:13px;margin:0 0 6px"><b>Page:</b> <?= e($ticket['page_label'] ?: $ticket['page_route']) ?></p>
      <p style="font-size:13px;margin:0 0 6px"><b>Priority:</b> <?= e(ucfirst($ticket['priority'] ?? 'normal')) ?></p>
      <p style="font-size:13px;margin:0"><b>Description:</b></p>
      <p style="font-size:13px;color:var(--text-dim);margin:4px 0 0;line-height:1.5"><?= nl2br(e($ticket['description'] ?? '')) ?></p>
    </div>
  </div>

  <div class="card" style="margin:0">
    <div style="padding:18px 20px">
      <div style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Authorized Pages</div>
      <?php foreach ($scopes as $s): ?>
        <a href="<?= url('index.php?r='.ltrim($s['scope_route'], '/')) ?>" class="btn btn-primary btn-sm" style="margin:0 6px 6px 0">
          <?= e($s['scope_label'] ?: $s['scope_route']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="card">
  <div style="padding:18px 20px">
    <div style="font-size:11px;font-weight:700;color:var(--text-dim);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">Resolve Ticket</div>
    <form method="POST" action="<?= url('index.php?r=it_admin/resolve') ?>" onsubmit="return confirm('Mark as resolved?')">
      <?= csrf_field() ?>
      <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
      <div class="field" style="margin-bottom:14px">
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px">Resolution Note</label>
        <textarea name="resolution_note" class="input" rows="3" placeholder="What did you fix?" style="width:100%;resize:vertical"></textarea>
      </div>
      <button class="btn btn-success" type="submit">Mark as Resolved</button>
    </form>
  </div>
</div>

<script>
var _hbTimer = null;
function setAdminStatus(ticketId, status, btn) {
  var fd = new FormData();
  fd.append('ticket_id', ticketId);
  fd.append('admin_status', status);
  fd.append('_csrf', '<?= e(csrf_token()) ?>');
  fetch('<?= url('index.php?r=it_admin/heartbeat') ?>', {method:'POST', body:fd})
    .then(function(r){ return r.json(); })
    .then(function(d) {
      if (d.ok) {
        document.querySelectorAll('.btn-primary').forEach(function(b){ b.className = 'btn btn-ghost btn-sm'; });
        btn.className = 'btn btn-primary btn-sm';
        var el = document.getElementById('heartbeat-status');
        if (el) el.textContent = 'Status: ' + status.charAt(0).toUpperCase() + status.slice(1) + ' ✓';
      }
    })
    .catch(function(){});
  // Auto-heartbeat every 30s
  if (_hbTimer) clearInterval(_hbTimer);
  _hbTimer = setInterval(function() {
    var fd2 = new FormData();
    fd2.append('ticket_id', ticketId);
    fd2.append('admin_status', status);
    fd2.append('_csrf', '<?= e(csrf_token()) ?>');
    fetch('<?= url('index.php?r=it_admin/heartbeat') ?>', {method:'POST', body:fd2}).catch(function(){});
  }, 30000);
}
</script>
