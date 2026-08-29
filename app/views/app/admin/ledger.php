<?php /* Integrity Ledger — tamper-evident hash chain with C-backed cryptography */
$actionBadge = ['INSERT' => 'success', 'UPDATE' => 'warning', 'DELETE' => 'danger'];
$ledgerSortUrl = fn($col) => url('admin/ledger?' . http_build_query(array_merge($_GET, ['sort'=>$col, 'dir'=> ($_GET['sort'] ?? '')===$col && ($_GET['dir'] ?? 'desc')==='asc' ? 'desc' : 'asc'])));
$sort = $_GET['sort'] ?? 'id';
$dir = $_GET['dir'] ?? 'desc';
$chainOk = $status['ok'] ?? true;
?>
<style>
.av-dashboard{--av-green:#27ff9e;--av-red:#ff5c7a;--av-amber:#ffc84d;--av-cyan:#38e6e6;--av-bg:#0a0f1e;--av-panel:#0d1321;--av-border:#1c2a45}
.av-hero{background:linear-gradient(135deg,var(--av-bg) 0%,#0d1a2e 50%,var(--av-bg) 100%);border:1px solid var(--av-border);border-radius:16px;padding:28px 32px;display:flex;align-items:center;gap:28px;position:relative;overflow:hidden;margin-bottom:18px}
.av-hero::before{content:"";position:absolute;inset:0;background:repeating-linear-gradient(0deg,rgba(255,255,255,.015) 0 1px,transparent 1px 3px);pointer-events:none}
.av-hero::after{content:"";position:absolute;top:-50%;right:-10%;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(39,255,158,.06) 0%,transparent 70%);pointer-events:none}
.av-shield{position:relative;width:110px;height:110px;flex-shrink:0}
.av-shield-ring{position:absolute;inset:0;border-radius:50%;border:3px solid var(--av-green);animation:avPulse 2.5s ease-in-out infinite}
.av-shield-ring.warning{border-color:var(--av-red);animation:avPulseDanger 1.5s ease-in-out infinite}
.av-shield-inner{position:absolute;inset:12px;border-radius:50%;background:rgba(39,255,158,.08);display:flex;align-items:center;justify-content:center;flex-direction:column;border:1px solid rgba(39,255,158,.2)}
.av-shield-inner.warning{background:rgba(255,92,122,.08);border-color:rgba(255,92,122,.2)}
.av-shield-icon{font-size:2.2em;color:var(--av-green);line-height:1}
.av-shield-icon.warning{color:var(--av-red)}
.av-shield-status{font-size:10px;letter-spacing:.15em;color:var(--av-green);font-weight:700;margin-top:4px}
.av-shield-status.warning{color:var(--av-red)}
@keyframes avPulse{0%,100%{box-shadow:0 0 0 0 rgba(39,255,158,.35)}50%{box-shadow:0 0 24px 4px rgba(39,255,158,.2)}}
@keyframes avPulseDanger{0%,100%{box-shadow:0 0 0 0 rgba(255,92,122,.4)}50%{box-shadow:0 0 30px 6px rgba(255,92,122,.3)}}
.av-info{flex:1;position:relative;z-index:1}
.av-title{font-size:1.3em;font-weight:700;margin:0 0 4px}
.av-title.ok{color:var(--av-green)} .av-title.danger{color:var(--av-red)}
.av-subtitle{font-size:.82em;color:var(--av-cyan);opacity:.8;margin:0 0 12px}
.av-meta{display:flex;gap:16px;flex-wrap:wrap}
.av-meta-item{display:flex;align-items:center;gap:6px;font-size:.78em;color:rgba(255,255,255,.55)}
.av-meta-item .dot{width:6px;height:6px;border-radius:50%;background:var(--av-green);flex-shrink:0}
.av-meta-item .dot.amber{background:var(--av-amber)} .av-meta-item .dot.red{background:var(--av-red)}
.av-progress{height:3px;background:var(--av-border);border-radius:2px;margin-top:14px;overflow:hidden}
.av-progress-bar{height:100%;border-radius:2px;background:linear-gradient(90deg,var(--av-green),var(--av-cyan));transition:width .6s}
.av-progress-bar.warning{background:linear-gradient(90deg,var(--av-red),var(--av-amber))}

.av-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:18px}
.av-stat{background:var(--av-panel);border:1px solid var(--av-border);border-radius:12px;padding:16px;text-align:center;position:relative;overflow:hidden;transition:border-color .2s}
.av-stat:hover{border-color:var(--av-cyan)}
.av-stat::before{content:"";position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--av-cyan),transparent);opacity:.4}
.av-stat-icon{font-size:1.3em;margin-bottom:4px}
.av-stat-val{font-size:1.6em;font-weight:700;font-family:ui-monospace,monospace;line-height:1.2}
.av-stat-val.green{color:var(--av-green)} .av-stat-val.cyan{color:var(--av-cyan)} .av-stat-val.amber{color:var(--av-amber)} .av-stat-val.red{color:var(--av-red)}
.av-stat-label{font-size:10px;letter-spacing:.12em;color:rgba(255,255,255,.4);text-transform:uppercase;margin-top:2px}

.av-quick{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.av-quick-btn{background:rgba(56,230,230,.06);border:1px solid var(--av-border);color:var(--av-cyan);border-radius:8px;padding:7px 14px;font-size:11px;font-family:inherit;letter-spacing:.06em;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .15s}
.av-quick-btn:hover{background:var(--av-cyan);color:var(--av-bg);border-color:var(--av-cyan)}
.av-quick-btn.danger{color:var(--av-red);border-color:rgba(255,92,122,.3)} .av-quick-btn.danger:hover{background:var(--av-red);color:#fff;border-color:var(--av-red)}

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

<!-- Antivirus-style hero -->
<div class="av-hero av-dashboard">
  <div class="av-shield">
    <div class="av-shield-ring<?= $chainOk ? '' : ' warning' ?>"></div>
    <div class="av-shield-inner<?= $chainOk ? '' : ' warning' ?>">
      <div class="av-shield-icon<?= $chainOk ? '' : ' warning' ?>"><?= $chainOk ? icon('shield') : icon('shield') ?></div>
      <div class="av-shield-status<?= $chainOk ? '' : ' warning' ?>"><?= $chainOk ? 'SECURED' : 'THREAT' ?></div>
    </div>
  </div>
  <div class="av-info">
    <div class="av-title <?= $chainOk ? 'ok' : 'danger' ?>"><?= $chainOk ? 'System Protected — No Threats Detected' : 'Threat Detected — Chain Compromised' ?></div>
    <div class="av-subtitle"><?= $chainOk ? 'All record hashes are valid. Integrity chain is secure.' : 'Tampering found at entry #' . number_format($status['broken_at']) . '. Immediate action required.' ?></div>
    <div class="av-meta">
      <div class="av-meta-item"><span class="dot<?= $chainOk ? '' : ' red' ?>"></span> <?= $chainOk ? 'Real-time protection: ON' : 'INTEGRITY VIOLATION' ?></div>
      <div class="av-meta-item"><span class="dot"></span> <?= number_format($status['entries']) ?> records scanned</div>
      <div class="av-meta-item"><span class="dot amber"></span> <?= $stats['active_keys'] ?> active key(s)</div>
      <?php if ($stats['last_verify']): ?>
        <div class="av-meta-item"><span class="dot"></span> Last scan: <?= e($stats['last_verify']['verified_at']) ?></div>
      <?php endif; ?>
    </div>
    <div class="av-progress"><div class="av-progress-bar<?= $chainOk ? '' : ' warning' ?>" style="width:<?= $chainOk ? '100' : '0' ?>%"></div></div>
  </div>
</div>

<!-- Stats -->
<div class="av-stats av-dashboard">
  <div class="av-stat">
    <div class="av-stat-icon">🔗</div>
    <div class="av-stat-val green"><?= number_format($stats['total']) ?></div>
    <div class="av-stat-label">Chain Links</div>
  </div>
  <div class="av-stat">
    <div class="av-stat-icon">🔐</div>
    <div class="av-stat-val cyan"><?= $stats['active_keys'] ?></div>
    <div class="av-stat-label">HMAC Keys</div>
  </div>
  <div class="av-stat">
    <div class="av-stat-icon">📋</div>
    <div class="av-stat-val cyan"><?= count($stats['tables']) ?></div>
    <div class="av-stat-label">Tables Monitored</div>
  </div>
  <div class="av-stat">
    <div class="av-stat-icon">⚙️</div>
    <div class="av-stat-val <?= $cryptoLoaded ? 'green' : 'red' ?>"><?= $cryptoLoaded ? 'C' : 'PHP' ?></div>
    <div class="av-stat-label">Crypto Engine</div>
  </div>
  <div class="av-stat">
    <div class="av-stat-icon">➕</div>
    <div class="av-stat-val green"><?= number_format($stats['actions']['INSERT'] ?? 0) ?></div>
    <div class="av-stat-label">Writes</div>
  </div>
  <div class="av-stat">
    <div class="av-stat-icon">✏️</div>
    <div class="av-stat-val amber"><?= number_format($stats['actions']['UPDATE'] ?? 0) ?></div>
    <div class="av-stat-label">Modifications</div>
  </div>
  <div class="av-stat">
    <div class="av-stat-icon">🗑️</div>
    <div class="av-stat-val red"><?= number_format($stats['actions']['DELETE'] ?? 0) ?></div>
    <div class="av-stat-label">Deletions</div>
  </div>
  <div class="av-stat">
    <div class="av-stat-icon">🔄</div>
    <form method="post" class="inline" style="margin-top:4px"><?= csrf_field() ?>
      <button class="av-quick-btn" name="rotate_key" value="1" onclick="return confirm('Rotate HMAC key?')" style="border:none;background:none;padding:0;font-size:inherit">⟳ Rotate</button>
    </form>
    <div class="av-stat-label">Key Rotation</div>
  </div>
</div>

<!-- Quick actions -->
<div class="av-quick av-dashboard">
  <form method="post" class="inline"><?= csrf_field() ?>
    <button class="av-quick-btn" name="reverify" value="1">🔍 Full Chain Scan</button>
  </form>
  <form method="post" class="inline"><?= csrf_field() ?>
    <button class="av-quick-btn" name="export_ledger" value="1">⤓ Export Report</button>
  </form>
  <form method="post" class="inline"><?= csrf_field() ?>
    <button class="av-quick-btn danger" name="rotate_key" value="1" onclick="return confirm('Rotate HMAC key?')">🔑 Rotate Key</button>
  </form>
</div>

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
