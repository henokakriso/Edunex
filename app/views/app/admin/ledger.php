<?php /* Integrity Ledger — tamper-evident hash chain with C-backed cryptography */
$actionBadge = ['INSERT' => 'success', 'UPDATE' => 'warning', 'DELETE' => 'danger'];
$ledgerSortUrl = fn($col) => url('admin/ledger?' . http_build_query(array_merge($_GET, ['sort'=>$col, 'dir'=> ($_GET['sort'] ?? '')===$col && ($_GET['dir'] ?? 'desc')==='asc' ? 'desc' : 'asc'])));
$sort = $_GET['sort'] ?? 'id';
$dir = $_GET['dir'] ?? 'desc';
?>
<style>
.stat-ring{width:100px;height:100px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-direction:column;border:3px solid var(--c-success);margin:0 auto}
.stat-ring.broken{border-color:var(--c-danger);animation:pulse-danger 1.5s infinite}
@keyframes pulse-danger{0%,100%{box-shadow:0 0 0 0 rgba(255,50,50,.3)}50%{box-shadow:0 0 20px 4px rgba(255,50,50,.4)}}
.filter-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:end}
.filter-bar .flex-col{min-width:120px}
</style>

<div class="page-head page-head-flex">
  <div>
    <h1><?= icon('chain') ?> Integrity Ledger</h1>
    <p class="sub">Tamper-evident hash chain · C-backed SHA-256 cryptography</p>
  </div>
  <div class="flex gap-8">
    <a class="btn btn-ghost" href="<?= e(url('admin/security')) ?>"><?= icon('shield') ?> Security Console</a>
    <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-primary" name="reverify" value="1"><?= icon('search') ?> Re-verify chain</button></form>
    <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-ghost" name="export_ledger" value="1"><?= icon('download') ?> Export CSV</button></form>
  </div>
</div>

<!-- Status banner -->
<?php if ($status['ok']): ?>
  <div class="card" style="border-left:4px solid var(--c-success);padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;gap:14px">
    <div class="stat-ring">
      <span style="font-size:1.4em;color:var(--c-success)"><?= icon('shield') ?></span>
      <span class="tiny bold" style="color:var(--c-success)">INTACT</span>
    </div>
    <div>
      <strong>Chain Integrity: Verified</strong>
      <p class="tiny faint" style="margin:2px 0 0"><?= number_format($status['entries']) ?> entries · All hashes valid · No tampering detected</p>
      <?php if ($stats['last_verify']): ?>
        <p class="tiny faint">Last verified: <?= e($stats['last_verify']['verified_at']) ?> by <?= e($stats['last_verify']['verified_by'] ?? 'system') ?></p>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <div class="card" style="border-left:4px solid var(--c-danger);padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;gap:14px;background:rgba(255,50,50,.05)">
    <div class="stat-ring broken">
      <span style="font-size:1.4em;color:var(--c-danger)"><?= icon('shield') ?></span>
      <span class="tiny bold" style="color:var(--c-danger)">BROKEN</span>
    </div>
    <div>
      <strong style="color:var(--c-danger)">Chain Integrity: COMPROMISED</strong>
      <p class="tiny" style="color:var(--c-danger);margin:2px 0 0">Tampering detected at entry #<?= number_format($status['broken_at']) ?>. Immediate investigation required.</p>
    </div>
  </div>
<?php endif; ?>

<!-- Stats grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:18px">
  <div class="card" style="padding:14px;text-align:center">
    <div class="stat-label">Total Entries</div>
    <div class="stat-num"><?= number_format($stats['total']) ?></div>
  </div>
  <div class="card" style="padding:14px;text-align:center">
    <div class="stat-label">Active HMAC Keys</div>
    <div class="stat-num" style="color:var(--c-primary)"><?= $stats['active_keys'] ?></div>
  </div>
  <div class="card" style="padding:14px;text-align:center">
    <div class="stat-label">Tables Tracked</div>
    <div class="stat-num"><?= count($stats['tables']) ?></div>
  </div>
  <div class="card" style="padding:14px;text-align:center">
    <div class="stat-label">Crypto Engine</div>
    <div class="stat-num" style="color:<?= $cryptoLoaded ? 'var(--c-success)' : 'var(--c-danger)' ?>"><?= $cryptoLoaded ? 'C/FFI' : 'PHP' ?></div>
    <div class="tiny faint"><?= $cryptoLoaded ? 'ledger_crypto.so loaded' : 'fallback mode' ?></div>
  </div>
  <div class="card" style="padding:14px;text-align:center">
    <div class="stat-label">INSERT</div>
    <div class="stat-num" style="color:var(--c-success)"><?= number_format($stats['actions']['INSERT'] ?? 0) ?></div>
  </div>
  <div class="card" style="padding:14px;text-align:center">
    <div class="stat-label">UPDATE</div>
    <div class="stat-num" style="color:var(--c-warning)"><?= number_format($stats['actions']['UPDATE'] ?? 0) ?></div>
  </div>
  <div class="card" style="padding:14px;text-align:center">
    <div class="stat-label">DELETE</div>
    <div class="stat-num" style="color:var(--c-danger)"><?= number_format($stats['actions']['DELETE'] ?? 0) ?></div>
  </div>
  <div class="card" style="padding:14px;text-align:center">
    <div class="stat-label">Key Rotation</div>
    <form method="post" class="inline" style="margin-top:4px"><?= csrf_field() ?>
      <button class="btn btn-sm btn-ghost" name="rotate_key" value="1" onclick="return confirm('Rotate HMAC key? Previous chain entries remain valid.')"><?= icon('refresh') ?> Rotate</button>
    </form>
  </div>
</div>

<!-- Tables breakdown -->
<?php if ($stats['tables']): ?>
  <div class="card" style="padding:14px 18px;margin-bottom:16px">
    <strong class="tiny faint" style="text-transform:uppercase;letter-spacing:.1em">Tracked Tables</strong>
    <div class="flex gap-8" style="margin-top:8px;flex-wrap:wrap">
      <?php foreach ($stats['tables'] as $t): ?>
        <span class="badge badge-primary"><?= e($t['table_name']) ?> · <?= number_format($t['cnt']) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Filters -->
<form method="get" class="card filter-bar" style="padding:12px 16px;margin-bottom:16px">
  <input type="hidden" name="r" value="admin/ledger">
  <div class="flex-col"><label class="tiny faint">Table</label>
    <select class="input" name="table"><option value="">All tables</option>
      <?php foreach ($stats['tables'] as $t): ?><option value="<?= e($t['table_name']) ?>" <?= ($filters['table'] ?? '') === $t['table_name'] ? 'selected' : '' ?>><?= e($t['table_name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="flex-col"><label class="tiny faint">Action</label>
    <select class="input" name="action"><option value="">All</option>
      <option value="INSERT" <?= ($filters['action'] ?? '') === 'INSERT' ? 'selected' : '' ?>>INSERT</option>
      <option value="UPDATE" <?= ($filters['action'] ?? '') === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
      <option value="DELETE" <?= ($filters['action'] ?? '') === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
    </select>
  </div>
  <div class="flex-col"><label class="tiny faint">From</label><input class="input" type="date" name="from" value="<?= e($filters['from'] ?? '') ?>"></div>
  <div class="flex-col"><label class="tiny faint">To</label><input class="input" type="date" name="to" value="<?= e($filters['to'] ?? '') ?>"></div>
  <div class="flex-col"><label class="tiny faint">&nbsp;</label><button class="btn btn-sm" type="submit"><?= icon('filter') ?> Filter</button></div>
</form>

<!-- Chain entries -->
<div class="table-wrap">
  <?php if (!$entries): ?>
    <div class="muted" style="text-align:center;padding:40px">
      <?= icon('chain') ?>
      <p>No ledger entries yet. Records will appear here as they are written.</p>
    </div>
  <?php else: ?>
    <table class="table">
      <thead><tr>
        <th class="col-num">#</th>
        <th><a class="ajax-nav sort-link" href="<?= e($ledgerSortUrl('action')) ?>">Action<span class="sort-arrow<?= $sort==='action' ? ' active' : '' ?>"><?= $sort==='action' && $dir==='asc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th><a class="ajax-nav sort-link" href="<?= e($ledgerSortUrl('table_name')) ?>">Table<span class="sort-arrow<?= $sort==='table_name' ? ' active' : '' ?>"><?= $sort==='table_name' && $dir==='asc' ? '&#9660;' : '&#9650;' ?></span></a></th>
        <th>Record</th>
        <th>Data</th>
        <th>Hash Chain</th>
        <th>User</th>
        <th>IP</th>
        <th><a class="ajax-nav sort-link" href="<?= e($ledgerSortUrl('recorded_at')) ?>">Time<span class="sort-arrow<?= $sort==='recorded_at' ? ' active' : '' ?>"><?= $sort==='recorded_at' && $dir==='asc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      </tr></thead>
      <tbody>
        <?php foreach ($entries as $e): ?>
          <tr>
            <td class="col-num"><?= $e['id'] ?></td>
            <td><span class="badge badge-<?= $actionBadge[$e['action']] ?? 'primary' ?>"><?= $e['action'] ?></span></td>
            <td><strong><?= e(ucwords(str_replace('_', ' ', $e['table_name']))) ?></strong></td>
            <td class="col-num">#<?= $e['record_id'] ?></td>
            <td>
              <?php if ($e['data_json'] && $e['data_json'] !== '{}'): ?>
                <code class="tiny" style="word-break:break-all"><?= e(mb_substr($e['data_json'], 0, 80)) ?><?= mb_strlen($e['data_json']) > 80 ? '…' : '' ?></code>
              <?php else: ?>
                <span class="faint tiny">—</span>
              <?php endif; ?>
            </td>
            <td>
              <code class="tiny mono" style="word-break:break-all">
                <span class="faint">prev:</span> <?= e(substr($e['prev_hash'], 0, 8)) ?>…<?= e(substr($e['prev_hash'], -4)) ?>
                <span style="color:var(--c-primary);margin:0 2px">→</span>
                <span class="faint">hash:</span> <?= e(substr($e['record_hash'], 0, 8)) ?>…<?= e(substr($e['record_hash'], -4)) ?>
                <?php if ($e['hmac_hash']): ?>
                  <span style="color:var(--c-primary);margin:0 2px">→</span>
                  <span class="faint">hmac:</span> <?= e(substr($e['hmac_hash'], 0, 8)) ?>…<?= e(substr($e['hmac_hash'], -4)) ?>
                <?php endif; ?>
              </code>
            </td>
            <td class="small faint"><?= e($e['user_name'] ?? '—') ?></td>
            <td class="small faint"><?= e($e['ip_address'] ?? '—') ?></td>
            <td class="small faint"><?= e($e['recorded_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if ($pages > 1): ?>
      <div class="flex" style="justify-content:center;gap:6px;padding:14px 0">
        <?php for ($p = max(1, $page - 3); $p <= min($pages, $page + 3); $p++): ?>
          <?php $qp = array_merge($_GET, ['r' => 'admin/ledger', 'page' => $p]); ?>
          <a href="<?= e(url('admin/ledger&' . http_build_query($qp))) ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"><?= $p ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
