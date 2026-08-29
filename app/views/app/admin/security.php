<?php /* Security Console — hacker-terminal integrity ledger verification */
$chainOk = $status['ok'] ?? true;
$cryptoOk = $cryptoLoaded ?? false;
?>
<style>
.sec-console{--sec-bg:#04060d;--sec-panel:#0a0f1e;--sec-border:#1c2a45;--sec-cyan:#38e6e6;--sec-green:#27ff9e;--sec-amber:#ffc84d;--sec-red:#ff5c7a;--sec-dim:#5d7a9a}
.sec-console *{box-sizing:border-box}
.sec-console .term{background:var(--sec-bg);border:1px solid var(--sec-border);border-radius:14px;padding:20px 24px;box-shadow:0 0 0 1px #000, 0 18px 50px rgba(0,0,0,.5);position:relative;overflow:hidden}
.sec-console .term::after{content:"";position:absolute;inset:0;pointer-events:none;background:repeating-linear-gradient(0deg,rgba(255,255,255,.025) 0 1px,transparent 1px 3px)}
.sec-console .crt{font-family:"JetBrains Mono",ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13px;line-height:1.65}
.sec-console .g{color:var(--sec-green)} .sec-console .c{color:var(--sec-cyan)} .sec-console .a{color:var(--sec-amber)} .sec-console .r{color:var(--sec-red)} .sec-console .dm{color:var(--sec-dim)}
.sec-console .bar{display:flex;align-items:center;gap:8px;color:var(--sec-dim);font-size:11px;letter-spacing:.08em;text-transform:uppercase;border-bottom:1px solid var(--sec-border);padding-bottom:10px;margin-bottom:14px}
.sec-console .bar b{color:var(--sec-cyan)}
.sec-console .panel{border:1px solid var(--sec-border);border-radius:10px;background:rgba(10,15,30,.55);padding:14px;margin-bottom:12px}
.sec-console .panel h4{margin:0 0 10px;font-size:11px;letter-spacing:.14em;color:var(--sec-amber);text-transform:uppercase}
.sec-console .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px}
.sec-console .stat-cell{border:1px solid var(--sec-border);border-radius:10px;padding:12px 14px;background:#060b18}
.sec-console .stat-cell .v{font-size:22px;font-weight:700;font-family:ui-monospace,monospace}
.sec-console .stat-cell .l{font-size:10px;letter-spacing:.12em;color:var(--sec-dim);text-transform:uppercase;margin-top:2px}
.sec-console .sec-btn{background:transparent;border:1px solid var(--sec-cyan);color:var(--sec-cyan);border-radius:8px;padding:6px 14px;font-size:11px;font-family:inherit;text-transform:uppercase;letter-spacing:.08em;cursor:pointer;transition:all .15s}
.sec-console .sec-btn:hover{background:var(--sec-cyan);color:#04060d}
.sec-console .sec-btn.danger{border-color:var(--sec-red);color:var(--sec-red)} .sec-console .sec-btn.danger:hover{background:var(--sec-red);color:#04060d}
.sec-console .sec-btn.ghost{border-color:var(--sec-border);color:var(--sec-dim)} .sec-console .sec-btn.ghost:hover{border-color:var(--sec-amber);color:var(--sec-amber)}
.sec-console table{width:100%;border-collapse:collapse}
.sec-console th{color:var(--sec-dim);font-size:10px;text-align:left;letter-spacing:.12em;text-transform:uppercase;padding:8px 10px;border-bottom:1px solid var(--sec-border)}
.sec-console td{padding:8px 10px;border-bottom:1px solid rgba(28,42,85,.35);vertical-align:middle;font-size:12px}
.sec-console .row:hover td{background:rgba(56,230,230,.04)}
.sec-console .hash-mono{font-family:ui-monospace,monospace;font-size:10px;color:var(--sec-dim);word-break:break-all}
.sec-console .scanner{position:relative;height:22px;overflow:hidden;border-bottom:1px solid var(--sec-border);margin:16px 0;opacity:.85}
.sec-console .scanner span{position:absolute;top:0;left:-40%;width:40%;height:100%;background:linear-gradient(90deg,transparent,rgba(56,230,230,.55),transparent);animation:scan 3.4s linear infinite}
@keyframes scan{to{left:105%}}
.sec-console .intact-glow{box-shadow:0 0 30px rgba(39,255,158,.15);border-color:var(--sec-green) !important}
.sec-console .broken-glow{box-shadow:0 0 30px rgba(255,92,122,.2);border-color:var(--sec-red) !important;animation:secWarn 2s ease-in-out infinite}
@keyframes secWarn{0%,100%{box-shadow:0 0 0 0 rgba(255,92,122,.3)}50%{box-shadow:0 0 30px 4px rgba(255,92,122,.5)}}
.sec-console .boot-line{margin:0;white-space:pre-wrap;word-break:break-word}
</style>

<div class="sec-console">
  <div class="term crt">
    <div class="bar">
      <span style="flex:1">⚡ SECURITY CONSOLE · <b>edunex_guard</b> · integrity ledger</span>
      <span class="dm"><?= date('Y-m-d H:i:s') ?></span>
    </div>

    <!-- Boot lines -->
    <div style="margin-bottom:14px">
      <p class="boot-line g">[<span class="dm"><?= date('H:i:s') ?></span>] ledger_crypto.so loaded via FFI</p>
      <p class="boot-line c">[<span class="dm"><?= date('H:i:s', strtotime('-1 second')) ?></span>] HMAC-SHA256 engine: OpenSSL <?= OPENSSL_VERSION_TEXT ?></p>
      <p class="boot-line <?= $chainOk ? 'g' : 'r' ?>">[<span class="dm"><?= date('H:i:s', strtotime('-2 seconds')) ?></span>] chain verification: <?= $chainOk ? 'ALL LINKS VALID ✓' : 'BROKEN LINK DETECTED ✗' ?></p>
      <p class="boot-line dm">[<span class="dm"><?= date('H:i:s', strtotime('-3 seconds')) ?></span>] <?= number_format($stats['total']) ?> entries scanned · <?= $stats['active_keys'] ?> active HMAC key(s)</p>
    </div>

    <div class="scanner"><span></span></div>

    <!-- Status panel -->
    <div class="panel <?= $chainOk ? 'intact-glow' : 'broken-glow' ?>">
      <div class="stat-grid">
        <div class="stat-cell">
          <div class="v" style="color:<?= $chainOk ? 'var(--sec-green)' : 'var(--sec-red)' ?>"><?= $chainOk ? '✓ INTACT' : '✗ BROKEN' ?></div>
          <div class="l">Chain Status</div>
        </div>
        <div class="stat-cell">
          <div class="v g"><?= number_format($stats['total']) ?></div>
          <div class="l">Total Entries</div>
        </div>
        <div class="stat-cell">
          <div class="v c"><?= $stats['active_keys'] ?></div>
          <div class="l">HMAC Keys</div>
        </div>
        <div class="stat-cell">
          <div class="v a"><?= count($stats['tables']) ?></div>
          <div class="l">Tables Tracked</div>
        </div>
        <div class="stat-cell">
          <div class="v" style="color:<?= $cryptoOk ? 'var(--sec-green)' : 'var(--sec-red)' ?>"><?= $cryptoOk ? 'C/FFI' : 'PHP' ?></div>
          <div class="l">Crypto Engine</div>
        </div>
        <div class="stat-cell">
          <div class="v g"><?= number_format($stats['actions']['INSERT'] ?? 0) ?></div>
          <div class="l">INSERTs</div>
        </div>
        <div class="stat-cell">
          <div class="v a"><?= number_format($stats['actions']['UPDATE'] ?? 0) ?></div>
          <div class="l">UPDATEs</div>
        </div>
        <div class="stat-cell">
          <div class="v r"><?= number_format($stats['actions']['DELETE'] ?? 0) ?></div>
          <div class="l">DELETEs</div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-8" style="margin-bottom:14px">
      <form method="post" class="inline"><?= csrf_field() ?>
        <button class="sec-btn" name="reverify" value="1">⟲ Re-verify Chain</button>
      </form>
      <form method="post" class="inline"><?= csrf_field() ?>
        <button class="sec-btn ghost" name="export_ledger" value="1">⤓ Export CSV</button>
      </form>
      <form method="post" class="inline"><?= csrf_field() ?>
        <button class="sec-btn danger" name="rotate_key" value="1" onclick="return confirm('Rotate HMAC key?')">⟳ Rotate Key</button>
      </form>
    </div>

    <!-- Last verification -->
    <?php if ($stats['last_verify']): ?>
      <div class="panel">
        <h4>Last Verification</div>
        <div class="c">verified_at: <span class="g"><?= e($stats['last_verify']['verified_at']) ?></span></div>
        <div class="c">total_records: <span class="g"><?= number_format($stats['last_verify']['total_records']) ?></span></div>
        <div class="c">broken_links: <span class="<?= (int)$stats['last_verify']['broken_links'] > 0 ? 'r' : 'g' ?>"><?= (int)$stats['last_verify']['broken_links'] ?></span></div>
        <?php if ($stats['last_verify']['first_hash']): ?>
          <div class="dm" style="margin-top:6px">first_hash: <span class="hash-mono"><?= e($stats['last_verify']['first_hash']) ?></span></div>
          <div class="dm">last_hash: <span class="hash-mono"><?= e($stats['last_verify']['last_hash']) ?></span></div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Recent entries -->
    <div class="panel">
      <h4>Recent Chain Entries</h4>
      <?php if (!$recent): ?>
        <div class="dm" style="padding:20px;text-align:center">No entries yet — chain is empty</div>
      <?php else: ?>
        <div style="overflow-x:auto">
          <table>
            <thead><tr>
              <th>#</th><th>Action</th><th>Table</th><th>Record</th><th>User</th><th>Hash</th><th>Time</th>
            </tr></thead>
            <tbody>
              <?php foreach ($recent as $r): ?>
                <tr class="row">
                  <td class="dm"><?= $r['id'] ?></td>
                  <td><span class="badge badge-<?= $r['action'] === 'INSERT' ? 'success' : ($r['action'] === 'UPDATE' ? 'warning' : 'danger') ?>" style="font-size:10px;padding:2px 6px"><?= $r['action'] ?></span></td>
                  <td class="c"><?= e($r['table_name']) ?></td>
                  <td class="dm">#<?= $r['record_id'] ?></td>
                  <td class="dm"><?= e($r['user_name'] ?? '—') ?></td>
                  <td class="hash-mono"><?= e(substr($r['record_hash'], 0, 12)) ?>…</td>
                  <td class="dm"><?= e($r['recorded_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Tables breakdown -->
    <?php if ($stats['tables']): ?>
      <div class="panel">
        <h4>Tracked Tables</h4>
        <div class="flex gap-6" style="flex-wrap:wrap">
          <?php foreach ($stats['tables'] as $t): ?>
            <div style="border:1px solid var(--sec-border);border-radius:8px;padding:6px 10px;background:#060b18">
              <span class="c"><?= e($t['table_name']) ?></span>
              <span class="g" style="margin-left:6px"><?= number_format($t['cnt']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Top contributors -->
    <?php if (!empty($stats['top_users'])): ?>
      <div class="panel">
        <h4>Top Contributors</h4>
        <table>
          <thead><tr><th>User</th><th>Entries</th></tr></thead>
          <tbody>
            <?php foreach ($stats['top_users'] as $u): ?>
              <tr class="row">
                <td class="g"><?= e($u['full_name']) ?></td>
                <td class="c"><?= number_format($u['cnt']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <div class="scanner"><span></span></div>
    <div class="dm" style="text-align:center;font-size:10px;letter-spacing:.1em">
      EDUNEX INTEGRITY LEDGER · SHA-256 + HMAC-SHA256 · C/FFI BACKED
    </div>
  </div>
</div>
