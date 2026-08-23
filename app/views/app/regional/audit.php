<?php /* Regional audit — activity of users in assigned schools */
$baseUrl = 'regional/audit';
?>
<div class="page-head">
  <div>
    <h1><?= icon('shield') ?> Audit Log</h1>
    <p class="sub">Actions by users in your assigned schools (latest 100)</p>
  </div>
  <div class="flex gap-8">
    <a class="btn btn-sm" href="<?= e(url($baseUrl . '&export=pdf')) ?>"><?= icon('file') ?> Export PDF</a>
    <a class="btn btn-sm" href="<?= e(url($baseUrl . '&export=md')) ?>"><?= icon('file') ?> Export Markdown</a>
    <a class="btn btn-sm" href="javascript:window.print()"><?= icon('printer') ?> Print</a>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th style="width:36px">#</th><th>User</th><th>School</th><th>Action</th><th>Detail</th><th>When</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $i => $a): ?>
          <tr>
            <td class="small faint"><?= $i + 1 ?></td>
            <td><b><?= e(($a['first_name'] . ' ' . $a['last_name']) ?: 'System') ?></b><p class="tiny faint"><?= e($a['email'] ?: '—') ?></p></td>
            <td><?= e($a['school_name'] ?? '—') ?></td>
            <td><span class="badge"><?= e($a['action']) ?></span></td>
            <td class="small"><?= e($a['detail']) ?></td>
            <td class="small faint"><?= e(time_ago($a['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="muted" style="padding:20px;text-align:center">No activity in your schools yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
