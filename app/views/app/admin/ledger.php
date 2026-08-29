<?php /* Integrity Ledger — tamper-evident hash chain with C-backed cryptography */
$hashShort = fn($h) => '<code class="mono tiny">' . e(substr($h, 0, 8)) . '…' . e(substr($h, -6)) . '</code>';
$actionBadge = ['INSERT' => 'success', 'UPDATE' => 'warning', 'DELETE' => 'danger'];
?>
<style>
.ledger-chain{display:flex;flex-direction:column;gap:2px;position:relative}
.ledger-chain::before{content:"";position:absolute;left:19px;top:0;bottom:0;width:2px;background:linear-gradient(to bottom,var(--c-success),var(--c-primary),var(--c-amber))}
.ledger-entry{display:grid;grid-template-columns:40px 1fr;gap:12px;align-items:start;padding:10px 0}
.ledger-dot{width:12px;height:12px;border-radius:50%;border:2px solid var(--c-success);background:var(--c-bg);margin-top:6px;z-index:1}
.ledger-dot.broken{border-color:var(--c-danger);background:var(--c-danger);box-shadow:0 0 8px var(--c-danger)}
.ledger-card{background:var(--c-card);border:1px solid var(--c-border);border-radius:10px;padding:12px 14px;transition:border-color .2s}
.ledger-card:hover{border-color:var(--c-primary)}
.ledger-card .meta{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:6px}
.ledger-card .data{font-size:.8em;color:var(--c-text-muted);max-height:60px;overflow:hidden;text-overflow:ellipsis}
.hash-full{font-family:ui-monospace,monospace;font-size:.72em;color:var(--c-text-muted);word-break:break-all;line-height:1.4}
.stat-ring{width:100px;height:100px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-direction:column;border:3px solid var(--c-success);margin:0 auto}
.stat-ring.broken{border-color:var(--c-danger);animation:pulse-danger 1.5s infinite}
@keyframes pulse-danger{0%,100%{box-shadow:0 0 0 0 rgba(255,50,50,.3)}50%{box-shadow:0 0 20px 4px rgba(255,50,50,.4)}}
.chain-verify-anim{display:inline-block;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
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
<div class="card" style="padding:16px 18px">
  <div class="flex" style="justify-content:space-between;align-items:center;margin-bottom:12px">
    <strong>Chain History</strong>
    <span class="tiny faint"><?= number_format($total) ?> total entries</span>
  </div>

  <?php if (!$entries): ?>
    <div class="muted" style="text-align:center;padding:40px">
      <?= icon('chain') ?>
      <p>No ledger entries yet. Records will appear here as they are written.</p>
    </div>
  <?php else: ?>
    <div class="ledger-chain">
      <?php foreach ($entries as $i => $e): ?>
        <div class="ledger-entry">
          <div class="ledger-dot"></div>
          <div class="ledger-card">
            <div class="meta">
              <span class="badge badge-<?= $actionBadge[$e['action']] ?? 'primary' ?>"><?= $e['action'] ?></span>
              <strong><?= e(ucwords(str_replace('_', ' ', $e['table_name']))) ?></strong>
              <span class="tiny faint">#<?= $e['record_id'] ?></span>
              <?php if ($e['user_name']): ?>
                <span class="tiny faint">by <?= e($e['user_name']) ?></span>
              <?php endif; ?>
              <span class="tiny faint" style="margin-left:auto"><?= e($e['recorded_at']) ?></span>
            </div>
            <div class="hash-full">
              <span class="tiny faint">prev:</span> <?= $hashShort($e['prev_hash']) ?>
              <span style="margin:0 6px;color:var(--c-primary)">→</span>
              <span class="tiny faint">hash:</span> <?= $hashShort($e['record_hash']) ?>
              <?php if ($e['hmac_hash']): ?>
                <span style="margin:0 6px;color:var(--c-primary)">→</span>
                <span class="tiny faint">hmac:</span> <?= $hashShort($e['hmac_hash']) ?>
              <?php endif; ?>
            </div>
            <?php if ($e['data_json'] && $e['data_json'] !== '{}'): ?>
              <div class="data" style="margin-top:4px"><code><?= e(mb_substr($e['data_json'], 0, 120)) ?><?= mb_strlen($e['data_json']) > 120 ? '…' : '' ?></code></div>
            <?php endif; ?>
            <?php if ($e['ip_address']): ?>
              <div class="tiny faint" style="margin-top:2px">IP: <?= e($e['ip_address']) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
      <div class="flex" style="justify-content:center;gap:6px;margin-top:16px">
        <?php for ($p = max(1, $page - 3); $p <= min($pages, $page + 3); $p++): ?>
          <?php $qp = array_merge($_GET, ['r' => 'admin/ledger', 'page' => $p]); ?>
          <a href="<?= e(url('admin/ledger&' . http_build_query($qp))) ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"><?= $p ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
