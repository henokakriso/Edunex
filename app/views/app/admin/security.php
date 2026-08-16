<?php /* Security Console — hacker-terminal view of the Integrity Ledger (admin only) */
$sevColor = ['ok' => '#27ff9e', 'bad' => '#ff5c7a'];
$cryptoSecure = !empty($crypto['binary']);
?>
<div class="sec-console">
  <style>
    .sec-console{--sec-bg:#04060d;--sec-panel:#0a0f1e;--sec-border:#1c2a45;--sec-cyan:#38e6e6;--sec-green:#27ff9e;--sec-amber:#ffc84d;--sec-red:#ff5c7a;--sec-dim:#5d7a9a;color:var(--sec-cyan)}
    .sec-console *{box-sizing:border-box}
    .sec-console .term{background:var(--sec-bg);border:1px solid var(--sec-border);border-radius:14px;padding:18px 20px;box-shadow:0 0 0 1px #000, 0 18px 50px rgba(0,0,0,.5);position:relative;overflow:hidden}
    .sec-console .term::after{content:"";position:absolute;inset:0;pointer-events:none;background:repeating-linear-gradient(0deg,rgba(255,255,255,.025) 0 1px,transparent 1px 3px)}
    .sec-console .crt{font-family:"JetBrains Mono",ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13px;line-height:1.65}
    .sec-console .g{color:var(--sec-green)} .sec-console .c{color:var(--sec-cyan)} .sec-console .a{color:var(--sec-amber)} .sec-console .r{color:var(--sec-red)} .sec-console .dm{color:var(--sec-dim)}
    .sec-console .bar{display:flex;align-items:center;gap:8px;color:var(--sec-dim);font-size:11px;letter-spacing:.08em;text-transform:uppercase;border-bottom:1px solid var(--sec-border);padding-bottom:10px;margin-bottom:14px}
    .sec-console .bar b{color:var(--sec-cyan)}
    .sec-console .blink{animation:secBlink 1.1s steps(2) infinite}
    @keyframes secBlink{50%{opacity:0}}
    .sec-console .panel{border:1px solid var(--sec-border);border-radius:10px;background:rgba(10,15,30,.55);padding:14px}
    .sec-console .panel h4{margin:0 0 10px;font-size:11px;letter-spacing:.14em;color:var(--sec-amber);text-transform:uppercase}
    .sec-console .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px}
    .sec-console .stat-cell{border:1px solid var(--sec-border);border-radius:10px;padding:12px 14px;background:#060b18}
    .sec-console .stat-cell .v{font-size:22px;font-weight:700;color:var(--sec-green);font-family:ui-monospace,monospace}
    .sec-console .stat-cell .l{font-size:10px;letter-spacing:.12em;color:var(--sec-dim);text-transform:uppercase;margin-top:2px}
    .sec-console .press{border:1px solid var(--sec-red);color:var(--sec-red);background:rgba(255,92,122,.08);padding:14px 16px;border-radius:10px;text-transform:uppercase;letter-spacing:.1em;font-size:11px;display:flex;align-items:center;gap:10px;margin-bottom:14px;animation:secWarn 2.2s ease-in-out infinite}
    @keyframes secWarn{0%,100%{box-shadow:0 0 0 0 rgba(255,92,122,.35)}50%{box-shadow:0 0 22px 2px rgba(255,92,122,.55)}}
    .sec-console table{width:100%;border-collapse:collapse}
    .sec-console th{color:var(--sec-dim);font-size:10px;text-align:left;letter-spacing:.12em;text-transform:uppercase;padding:8px 10px;border-bottom:1px solid var(--sec-border)}
    .sec-console td{padding:10px;border-bottom:1px solid rgba(28,42,85,.45);vertical-align:middle}
    .sec-console .row:hover td{background:rgba(56,230,230,.04)}
    .sec-console .sec-btn{background:transparent;border:1px solid var(--sec-cyan);color:var(--sec-cyan);border-radius:8px;padding:6px 12px;font-size:11px;font-family:inherit;text-transform:uppercase;letter-spacing:.08em;cursor:pointer}
    .sec-console .sec-btn:hover{background:var(--sec-cyan);color:#04060d}
    .sec-console .sec-btn.danger{border-color:var(--sec-red);color:var(--sec-red)} .sec-console .sec-btn.danger:hover{background:var(--sec-red);color:#04060d}
    .sec-console .sec-btn.ghost{border-color:var(--sec-border);color:var(--sec-dim)} .sec-console .sec-btn.ghost:hover{border-color:var(--sec-amber);color:var(--sec-amber)}
    .sec-console input,.sec-console select,.sec-console textarea{background:#060b18;border:1px solid var(--sec-border);color:var(--sec-green);border-radius:8px;padding:8px 10px;font-family:inherit;font-size:12px;width:100%}
    .sec-console input:focus,.sec-console select:focus{border-color:var(--sec-cyan);outline:none;box-shadow:0 0 10px rgba(56,230,230,.25)}
    .sec-console label{display:block;font-size:10px;letter-spacing:.1em;color:var(--sec-dim);text-transform:uppercase;margin:0 0 4px}
    .sec-console .field{margin-bottom:10px}
    .sec-console .boot-line{margin:0;white-space:pre-wrap;word-break:break-word}
    .sec-console .scanner{position:relative;height:22px;overflow:hidden;border-bottom:1px solid var(--sec-border);margin:16px 0;opacity:.85}
    .sec-console .scanner span{position:absolute;top:0;left:-40%;width:40%;height:100%;background:linear-gradient(90deg,transparent,rgba(56,230,230,.55),transparent);animation:scan 3.4s linear infinite}
    @keyframes scan{to{left:105%}}
    .sec-console .sec-modal{position:fixed;inset:0;z-index:900;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.82);backdrop-filter:blur(3px)}
    .sec-console .sec-modal.show{display:flex}
    .sec-console .sec-box{max-width:520px;width:90%;border:1px solid var(--sec-red);background:#0a0f1e;border-radius:14px;padding:26px;box-shadow:0 0 60px rgba(255,92,122,.35);animation:modalIn .25s ease}
    @keyframes modalIn{from{transform:scale(.92);opacity:0}}
    .sec-console .hashm{color:var(--sec-dim);font-size:11px}
  </style>

  <div class="term crt">
    <div class="bar">
      <span style="flex:1">⚡ SECURITY CONSOLE · <b>edunex_guard</b> · integrity ledger</span>
      <span class="dm">TARGET <span class="c"><?= e($school['name'] ?? '—') ?></span></span>
    </div>

    <div class="press">
      <span style="filter:drop-shadow(0 0 6px var(--sec-red))">⚠</span>
      WARNING — restricted area. Unauthorised access is monitored and every action is appended to the integrity ledger and audit log. Proceed at your own risk.
    </div>

    <div class="boot-line"><span class="g">root@edunex:~#</span> <span class="c">open_linked_eye --school=<?= (int)$schoolId ?> --mode=read+write</span></div>
    <div class="boot-line dm">[•] native C crypto core . . . <span class="<?= $cryptoSecure ? 'g' : 'r' ?>"><?= $cryptoSecure ? 'ONLINE' : 'OFFLINE' ?></span></div>
    <div class="boot-line dm">[•] sha256 ledger chain . . . <span class="<?= $chainIntact ? 'g' : 'r' ?>"><?= $chainIntact ? 'INTACT' : 'CORRUPTED' ?></span></div>
    <div class="boot-line dm">[•] auth events in window . . . <span class="c"><?= count($authEvents) ?></span></div>
    <div class="boot-line"><span class="g">>&#9620;</span><span class="blink">█</span></div>

    <div class="scanner"><span></span></div>

    <?php if (!$schoolId || !$school): ?>
      <div class="panel">
        <h4>// SELECT TARGET SCHOOL</h4>
        <p class="dm" style="margin:0 0 12px">The console operates on a single school's integrity ledger. Choose a target:</p>
        <form method="get" class="d-flex" style="gap:10px">
          <input type="hidden" name="r" value="admin/security">
          <select name="school" style="width:auto;flex:1"><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
          <button class="sec-btn">connect</button>
        </form>
      </div>
    <?php else: ?>
      <div class="stat-grid" style="margin:14px 0">
        <div class="stat-cell"><div class="v" style="color:<?= $chainIntact ? 'var(--sec-green)' : 'var(--sec-red)' ?>"><?= $chainIntact ? 'OK' : '!!' ?></div><div class="l">chain integrity</div></div>
        <div class="stat-cell"><div class="v"><?= (int)$status['entries'] ?></div><div class="l">chain entries</div></div>
        <div class="stat-cell"><div class="v"><?= (int)$status['checked'] ?></div><div class="l">verified</div></div>
        <div class="stat-cell"><div class="v" style="color:var(--sec-amber)"><?= count($entries) ?></div><div class="l">tail window</div></div>
      </div>

      <div class="grid" style="grid-template-columns:1.1fr .9fr;gap:14px;align-items:start">
        <div class="panel">
          <h4>// AUDIT CHANNEL · append note to chain</h4>
          <form method="post" class="d-flex" style="gap:8px">
            <?= csrf_field() ?>
            <input name="note" required maxlength="240" placeholder=">[ describe the audit event … ]" style="flex:1">
            <button class="sec-btn" name="note_submit" value="1">append</button>
          </form>
          <?php foreach ($entries as $e): $sev = ($e['event_type'] ?? '') === 'audit.note' ? 'ok' : 'bad'; ?>
            <div class="d-flex" style="gap:8px;padding:9px 2px;border-bottom:1px solid rgba(28,42,85,.4)">
              <span class="dm" style="flex:none">#<?= (int)$e['id'] ?></span>
              <span class="c" style="flex:none;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($e['event_type']) ?></span>
              <span class="g flex-1" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($e['payload']) ?></span>
              <span class="dm" style="flex:none;white-space:nowrap"><?= e(date('M j H:i', strtotime($e['created_at']))) ?></span>
            </div>
          <?php endforeach; ?>
          <?php if (!$entries): ?><p class="dm" style="margin:10px 0 0">no entries in this chain tail.</p><?php endif; ?>
        </div>
        <div class="panel">
          <h4>// CONTROLS %% guarded by csrf</h4>
          <div class="d-flex" style="flex-direction:column;gap:8px">
            <form method="post"><?= csrf_field() ?><button class="sec-btn" name="reverify" value="1" style="width:100%">verify entire chain</button></form>
            <form method="post"><?= csrf_field() ?><button class="sec-btn ghost" name="export_ledger" value="1" style="width:100%">export ledger csv</button></form>
          </div>
          <div class="d-flex" style="gap:8px;margin-top:10px">
            <a class="sec-btn ghost" style="text-align:center;flex:1" href="<?= e(url('admin/ledger&school=' . $schoolId)) ?>">full ledger UI</a>
          </div>
          <p class="dm" style="font-size:11px;margin:12px 0 0">every mutation here lands in the school's hash chain and the global audit log.</p>
        </div>
      </div>

      <?php if ($authEvents): ?>
        <div class="panel" style="margin-top:14px">
          <h4>// RECENT AUTH EVENTS</h4>
          <?php foreach ($authEvents as $ev): $ok = $ev['status'] === 'success'; ?>
            <div class="d-flex" style="gap:10px;padding:6px 2px;align-items:center">
              <span class="<?= $ok ? 'g' : 'r' ?>"><?= $ok ? '✓' : '✗' ?></span>
              <span class="c flex-1" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($ev['user_name'] ?? $ev['email']) ?></span>
              <span class="<?= $ok ? 'g' : 'r' ?>"><?= e($ev['status']) ?></span>
              <span class="dm mono" style="font-size:11px"><?= e($ev['ip'] ?? '—') ?></span>
              <span class="dm" style="font-size:11px;white-space:nowrap"><?= e(date('M j H:i', strtotime($ev['created_at']))) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <p class="dm" style="margin-top:18px;font-size:11px;text-align:center">EDUNEX SECURE CORE · C-backed hashing · every mutation logged</p>
  </div>

  <div class="sec-modal" id="sec-warn">
    <div class="sec-box">
      <div style="display:flex;gap:14px;align-items:flex-start">
        <span class="r" style="font-size:30px;filter:drop-shadow(0 0 8px var(--sec-red))">⚠</span>
        <div>
          <h3 style="color:var(--sec-red);margin:0 0 8px">RESTRICTED SECURITY ZONE</h3>
          <p class="crt" style="color:var(--sec-cyan)">You are entering the EDUNEX security console. This area is monitored.</p>
          <p class="crt dm" style="margin:8px 0 0;font-size:12px">Every action here — verify, export, 2FA and audit notes — is appended to the integrity hash chain and attributed to your account.</p>
        </div>
      </div>
      <div class="d-flex" style="justify-content:flex-end;gap:10px;margin-top:20px">
        <a class="sec-btn ghost" href="<?= e(url('admin/ledger&school=' . $schoolId)) ?>">back to ledger</a>
        <button class="sec-btn" onclick="closeSecWarn()">ACKNOWLEDGE &amp; PROCEED</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  window.closeSecWarn = function () {
    var m = document.getElementById('sec-warn');
    if (m) m.classList.remove('show');
  };
  if (!sessionStorage.getItem('edunex_sec_ack')) {
    var m = document.getElementById('sec-warn');
    if (m) m.classList.add('show');
  }
  var ackBtn = document.querySelector('.sec-box .sec-btn:last-child');
  if (ackBtn) ackBtn.addEventListener('click', function () { sessionStorage.setItem('edunex_sec_ack', '1'); closeSecWarn(); });
  window.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSecWarn(); });
})();
</script>