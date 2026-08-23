<?php /* Admin logs view — filter, search, export PDF/MD, print */
$baseUrl = 'admin/logs';
$filterParams = array_filter(['action'=>$action, 'q'=>$q, 'days'=>$days]);
?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> Activity Logs</h1>
    <p class="sub"><?= count($logs) ?> recorded actions</p>
  </div>
  <div class="flex gap-8">
    <a class="btn btn-sm" href="<?= e(url($baseUrl . '&' . http_build_query($filterParams + ['export'=>'pdf']))) ?>"><?= icon('file') ?> Export PDF</a>
    <a class="btn btn-sm" href="<?= e(url($baseUrl . '&' . http_build_query($filterParams + ['export'=>'md']))) ?>"><?= icon('file') ?> Export Markdown</a>
    <a class="btn btn-sm" href="javascript:window.print()"><?= icon('printer') ?> Print</a>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <form method="get" style="padding:14px 18px">
    <input type="hidden" name="r" value="<?= $baseUrl ?>">
    <div class="flex gap-8" style="align-items:end;flex-wrap:wrap">
      <div class="flex-col">
        <label class="small faint">Action</label>
        <select class="input" name="action" style="min-width:160px">
          <option value="">All actions</option>
          <?php foreach ($actions as $a): ?>
            <option value="<?= e($a['action']) ?>" <?= $action === $a['action'] ? 'selected' : '' ?>><?= e($a['action']) ?> (<?= (int)$a['n'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Search detail</label>
        <input class="input" name="q" value="<?= e($q) ?>" placeholder="keyword…" style="min-width:180px">
      </div>
      <div class="flex-col">
        <label class="small faint">Since</label>
        <select class="input" name="days">
          <option value="0" <?= $days === 0 ? 'selected' : '' ?>>All time</option>
          <?php foreach ([7, 30, 90] as $d): ?>
            <option value="<?= $d ?>" <?= $days === $d ? 'selected' : '' ?>>Last <?= $d ?> days</option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary" type="submit">Filter</button>
      <?php if ($action || $q || $days): ?>
        <a class="btn" href="<?= url($baseUrl) ?>">Clear filters</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card" style="margin-bottom:18px">
  <details>
    <summary class="small" style="padding:12px 16px"><b><?= icon('box') ?> Log rotation</b> — prune old records</summary>
    <form method="post" class="flex gap-8" style="padding:0 16px 14px;align-items:end">
      <?= csrf_field() ?>
      <div class="flex-col"><label class="small faint">Keep records newer than (days)</label>
        <input class="input" type="number" name="keep_days" value="90" min="1" max="3650" style="width:140px"></div>
      <button class="btn btn-warning" name="rotate" value="1" data-confirm="Permanently delete log records older than this?"><?= icon('box') ?> Rotate now</button>
    </form>
  </details>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="table" style="table-layout:auto">
      <thead><tr>
        <th style="width:36px">#</th>
        <th style="min-width:140px">When</th>
        <th style="min-width:160px;padding-left:16px">User</th>
        <th style="min-width:180px;padding-left:16px">School</th>
        <th style="min-width:100px">Action</th>
        <th style="min-width:200px">Detail</th>
      </tr></thead>
      <tbody>
        <?php foreach ($logs as $i => $l): ?>
          <tr style="text-align:center">
            <td class="small faint" style="padding:10px 8px 10px 12px"><?= $i + 1 ?></td>
            <td class="small faint" style="padding:10px 16px"><?= e(date('M j, H:i:s', strtotime($l['created_at']))) ?></td>
            <td style="padding:10px 16px">
              <div class="small bold"><?= e($l['user_name'] ?? '—') ?></div>
              <div class="small faint" style="font-size:11px"><?= e($l['email'] ?? '') ?></div>
            </td>
            <td class="small" style="padding:10px 16px"><?= e($l['school_name'] ?? '—') ?></td>
            <td style="padding:10px 16px"><span class="badge badge-muted"><?= e($l['action']) ?></span></td>
            <td class="small" style="padding:10px 16px"><?= e($l['detail']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (!$logs): ?>
    <div class="empty-state">
      <div class="empty-ic"><?= icon('note') ?></div>
      <h3>No logs match</h3>
      <p class="small">Try different filters or <?= $action || $q || $days ? '<a href="' . e(url($baseUrl)) . '">clear filters</a>' : 'check back later' ?>.</p>
    </div>
  <?php endif; ?>
</div>
