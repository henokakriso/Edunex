<?php /* Admin logs view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> Activity Logs</h1>
    <p class="sub"><?= count($logs) ?> recorded actions</p>
  </div>
  <div class="flex gap-8">
    <a class="btn btn-sm" href="<?= e(url('admin/logs&' . http_build_query(array_filter(['action'=>$action,'q'=>$q,'days'=>$days,'export'=>'pdf']))) ) ?>"><?= icon('file') ?> Export PDF</a>
    <a class="btn btn-sm" href="<?= e(url('admin/logs&' . http_build_query(array_filter(['action'=>$action,'q'=>$q,'days'=>$days,'export'=>'md']))) ) ?>"><?= icon('file') ?> Export Markdown</a>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <form method="get" class="grid2" style="align-items:end">
    <input type="hidden" name="r" value="admin/logs">
    <div class="flex-col"><label class="small faint">Action</label>
      <select class="input" name="action" onchange="this.form.submit()">
        <option value="">All actions</option>
        <?php foreach ($actions as $a): ?><option value="<?= e($a['action']) ?>" <?= $action === $a['action'] ? 'selected' : '' ?>><?= e($a['action']) ?> (<?= (int)$a['n'] ?>)</option><?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col"><label class="small faint">Search detail</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="keyword…"></div>
    <div class="flex-col"><label class="small faint">Since</label>
      <select class="input" name="days" onchange="this.form.submit()">
        <option value="0" <?= $days === 0 ? 'selected' : '' ?>>All time</option>
        <?php foreach ([7, 30, 90] as $d): ?><option value="<?= $d ?>" <?= $days === $d ? 'selected' : '' ?>>Last <?= $d ?> days</option><?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary">Filter</button>
  </form>
</div>

<div class="card" style="margin-bottom:18px">
  <details>
    <summary class="small"><b><?= icon('box') ?> Log rotation</b> — prune old records</summary>
    <form method="post" class="flex gap-8" style="margin-top:10px;align-items:end">
      <?= csrf_field() ?>
      <div class="flex-col"><label class="small faint">Keep records newer than (days)</label>
        <input class="input" type="number" name="keep_days" value="90" min="1" max="3650" style="width:140px"></div>
      <button class="btn btn-warning" name="rotate" value="1" data-confirm="Permanently delete log records older than this?"><?= icon('box') ?> Rotate now</button>
    </form>
  </details>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Detail</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $l): ?>
          <tr>
            <td class="small faint"><?= e(date('M j, H:i:s', strtotime($l['created_at']))) ?></td>
            <td class="small"><?= e($l['user_name'] ?? '—') ?></td>
            <td><span class="badge badge-muted"><?= e($l['action']) ?></span></td>
            <td class="small"><?= e($l['detail']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (!$logs): ?><p class="muted small" style="padding:12px">No logs match the filters.</p><?php endif; ?>
</div>
