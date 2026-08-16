<?php /* Integrity ledger view — redesigned */
$hashMask = fn(string $h) => '<span class="mono tiny hash-mask" data-hash="' . e($h) . '">' . substr($h, 0, 6) . '…</span>';
?>
<div class="page-head page-head-flex">
  <div>
    <h1><?= icon('chain') ?> Integrity Ledger</h1>
    <p class="sub">Tamper-evident hash chain with C-backed cryptography for every record write</p>
  </div>
  <?php if ($status): ?>
  <div class="d-flex" style="gap:8px">
    <a class="btn btn-ghost" href="<?= e(url('admin/security&school=' . $schoolId)) ?>"><?= icon('shield') ?> Security Console</a>
    <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-primary" name="reverify" value="1"><?= icon('search') ?> Re-verify chain</button></form>
    <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-ghost" name="export_ledger" value="1"><?= icon('download') ?> Export CSV</button></form>
  </div>
  <?php endif; ?>
</div>

<div class="card" style="margin-bottom:16px;padding:14px 18px">
  <form method="get" class="d-flex gap-8" style="align-items:end">
    <input type="hidden" name="r" value="admin/ledger">
    <div class="flex-col" style="flex:1;max-width:380px">
      <label class="small faint">School</label>
      <select class="input" name="school" onchange="this.form.submit()">
        <option value="">Select school…</option>
        <?php foreach ($schools as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= $schoolId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if ($school): ?><span class="badge badge-primary small">Viewing: <?= e($school['name']) ?></span><?php endif; ?>
  </form>
</div>

<?php if ($status): ?>
  <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:16px">
    <div class="stat-box">
      <span class="tiny faint">Chain integrity</span>
      <?php if ($status['ok']): ?><b style="color:var(--success)"><?= icon('check-circle') ?> INTACT</b>
      <?php else: ?><b style="color:var(--danger)"><?= icon('ban-circle') ?> BROKEN · entry #<?= (int)$status['broken_at'] ?></b><?php endif; ?>
    </div>
    <div class="stat-box"><span class="tiny faint">Entries</span><b><?= number_format((int)$status['entries']) ?></b></div>
    <div class="stat-box"><span class="tiny faint">Verified</span><b><?= number_format((int)$status['checked']) ?><span class="faint"> / <?= number_format((int)$status['entries']) ?></span></b></div>
    <div class="stat-box">
      <span class="tiny faint">Staff 2FA coverage</span>
      <b><?= (int)$staffTwofa['ok'] ?> / <?= (int)$staffTwofa['total'] ?><?= $staffTwofa['total'] ? ' · ' . round($staffTwofa['ok'] / max(1, $staffTwofa['total']) * 100) . '%' : '' ?></b>
      <span class="tiny" style="color:<?= $staffTwofa['total'] > 0 && $staffTwofa['ok'] === $staffTwofa['total'] ? 'var(--success)' : ($staffTwofa['ok'] > 0 ? 'var(--warning)' : 'var(--danger)') ?>"><?= $staffTwofa['total'] === 0 ? 'No staff to protect' : ($staffTwofa['ok'] === $staffTwofa['total'] ? icon('check-circle') . ' All protected' : ($staffTwofa['total'] - $staffTwofa['ok']) . ' unprotected') ?></span>
    </div>
    <div class="stat-box"><span class="tiny faint">Last event</span><b style="font-size:.75rem;word-break:break-word"><?= e($status['last']['event_type'] ?? '—') ?></b></div>
  </div>

  <?php if (!$status['ok']): ?>
    <div class="alert alert-danger" style="margin-bottom:16px">
      <?= icon('ban-circle') ?> <b>Tamper detected!</b> The hash chain is broken at entry #<?= (int)$status['broken_at'] ?>. A record was modified after it was written to the ledger.
    </div>
  <?php endif; ?>

  <?php /* ————— Two-factor coverage & recent sign-ins ————— */ ?>
    <div class="card twofa-card" style="padding:0">
      <div style="padding:16px 18px;border-bottom:1px solid var(--border)">
        <div class="d-flex" style="align-items:center;gap:14px;flex-wrap:wrap">
          <div style="flex:1;min-width:220px">
            <h3 class="card-title" style="margin:0"><?= icon('shield') ?> Two-factor coverage <span class="faint">(staff)</span></h3>
            <p class="tiny faint" style="margin:6px 0 0">USB <code>.hena</code> keys let signing staff prove their identity so every chain entry is attributable. Enable a fresh key for any staff member and hand them the downloaded file.</p>
          </div>
          <div id="twofa-donut" style="--p:0"></div>
        </div>
        <div class="twofa-meter" style="margin-top:14px">
          <div class="tiny faint d-flex flex-between" style="margin-bottom:5px">
            <span><b class="mono"><?= (int)$staffTwofa['ok'] ?> / <?= (int)$staffTwofa['total'] ?></b> protected</span>
            <span class="<?= $staffTwofa['total'] > 0 && $staffTwofa['ok'] === $staffTwofa['total'] ? 'faint' : ($staffTwofa['ok'] > 0 ? 'danger' : 'danger') ?>" data-unprotected-count>
              <?= (int)($staffTwofa['total'] - $staffTwofa['ok']) ?> unprotected
            </span>
          </div>
          <div class="twofa-bar"><span style="width:<?= $staffTwofa['total'] ? round($staffTwofa['ok'] / $staffTwofa['total'] * 100) : 0 ?>%"></span></div>
        </div>
      </div>
      <div class="twofa-toolbar">
        <div class="twofa-search">
          <?= icon('search') ?>
          <input type="text" id="twofa-q" placeholder="Filter by name or email…" autocomplete="off">
        </div>
        <div class="twofa-filters" id="twofa-filters">
          <button type="button" class="chip active" data-role="all">All <span class="chip-count"><?= count($staff) ?></span></button>
          <?php $roleCounts = []; foreach ($staff as $s) { $roleCounts[$s['role']] = ($roleCounts[$s['role']] ?? 0) + 1; } foreach (['admin', 'director', 'teacher'] as $r): ?>
            <button type="button" class="chip" data-role="<?= $r ?>"><?= e(ucfirst($r)) ?><?= $r === 'teacher' ? 's' : 's' ?><span class="chip-count"><?= (int)($roleCounts[$r] ?? 0) ?></span></button>
          <?php endforeach; ?>
          <label class="chip chip-toggle" title="Highlight staff still lacking a USB key">
            <input type="checkbox" id="twofa-unprot"> <?= icon('lock') ?> unprotected only
          </label>
        </div>
      </div>
      <div class="twofa-list" id="twofa-list">
        <?php foreach ($staff as $s): $prot = (int)$s['twofa_enabled'] === 1; ?>
          <div class="twofa-row <?= $prot ? 'row-on' : 'row-off' ?>" data-name="<?= e(strtolower($s['first_name'] . ' ' . $s['last_name'])) ?>" data-email="<?= e(strtolower($s['email'])) ?>" data-role="<?= e($s['role']) ?>" data-protected="<?= $prot ? '1' : '0' ?>">
            <span class="avatar <?= $prot ? 'avatar-on' : 'avatar-off' ?>"><?= e(strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1))) ?></span>
            <div class="flex-1" style="min-width:0">
              <div class="d-flex" style="align-items:center;gap:8px;flex-wrap:wrap">
                <b class="small"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></b>
                <?php if ($prot): ?><span class="badge badge-success"><span class="live-dot"></span> protected</span>
                <?php else: ?><span class="badge badge-muted"><span class="lock-dot"></span> 2FA off</span><?php endif; ?>
              </div>
              <p class="tiny faint mono" style="margin:2px 0 0"><?= e($s['email']) ?> · <span class="faint"><?= e(ucfirst($s['role'])) ?></span><?php if ($s['last_login']): ?> · <span class="faint">last in <?= e(date('M j', strtotime($s['last_login']))) ?></span><?php endif; ?></p>
            </div>
            <div class="twofa-actions">
              <?php if ($prot): ?>
                <form method="post" class="inline" data-confirm="Download the current activation file? It won't be re-issued.">
                  <?= csrf_field() ?><input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
                  <button class="btn btn-sm btn-ghost" name="download_2fa_key" value="1" title="Fetch the .hena file again"><?= icon('download') ?> Key</button>
                </form>
                <form method="post" class="inline" data-confirm="Disable USB 2FA for this staff member? This rotates and invalidates their current key.">
                  <?= csrf_field() ?><input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
                  <button class="btn btn-sm btn-outline" name="disable_2fa" value="1"><?= icon('x') ?> Disable</button>
                </form>
              <?php else: ?>
                <form method="post" class="inline" data-confirm="Issue a fresh USB .hena 2FA key for this staff? You'll be taken to download the activation file.">
                  <?= csrf_field() ?><input type="hidden" name="staff_id" value="<?= (int)$s['id'] ?>">
                  <button class="btn btn-sm btn-primary" name="enable_2fa" value="1"><?= icon('plus') ?> Enable</button>
                </form>
              <?php endif; ?>
            </div>
            <?php if ((int)$keyFileHint === (int)$s['id'] && $prot): ?>
              <div class="twofa-hint"><?= icon('download') ?> Key issued — use <b>Key</b> above to fetch the activation file again.</div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <div class="twofa-empty" id="twofa-empty" hidden><?= icon('search') ?> No staff match this filter.</div>
      </div>
    </div>
    <div class="card" style="padding:0">
      <div style="padding:16px 18px;border-bottom:1px solid var(--border)"><h3 class="card-title" style="margin:0"><?= icon('activity') ?> Recent sign-ins (school)</h3></div>
      <div class="list-col" style="padding:0 18px 14px">
        <?php foreach ($authEvents as $ev): $ok = $ev['status'] === 'success'; ?>
          <div class="list-row" style="padding:8px 0;gap:10px">
            <span style="color:<?= $ok ? 'var(--success)' : 'var(--danger)' ?>;flex:none"><?= $ok ? icon('check-circle') : icon('ban-circle') ?></span>
            <b class="small flex-1" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($ev['user_name'] ?? $ev['email']) ?></b>
            <span class="tiny <?= $ok ? 'faint' : 'danger' ?>" style="white-space:nowrap"><?= e($ev['status']) ?></span>
            <span class="tiny faint mono" style="white-space:nowrap"><?= e($ev['ip'] ?? '—') ?></span>
            <span class="tiny faint" style="white-space:nowrap"><?= e(date('M j H:i', strtotime($ev['created_at']))) ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (!$authEvents): ?><p class="muted small">No recent sign-ins for this school's staff.</p><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="chain-explorer" style="margin-bottom:16px">
    <div class="card" style="margin:0;overflow:hidden">
      <div class="chain-head" style="padding:16px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;flex-wrap:wrap">
        <div style="flex:1;min-width:240px">
          <h3 class="card-title" style="margin:0"><?= icon('chain') ?> Chain explorer</h3>
          <p class="tiny faint" style="margin:4px 0 0">Immutable blocks linked by SHA-256 — each entry commits to the one before it. Click a hash to inspect the links.</p>
        </div>
        <div class="d-flex" style="gap:8px;flex-wrap:wrap">
          <span class="badge badge-success chip-intact"><?= icon('check-circle') ?> genesis anchored</span>
          <span class="badge badge-info"><?= (int)$status['entries'] ?> blocks</span>
        </div>
      </div>

      <?php if (!$entries): ?>
        <p class="muted small" style="padding:20px">No ledger entries for this school yet — they appear as grades, attendance and certificates are recorded.</p>
      <?php else: ?>
        <div class="chain-list">
          <?php
          foreach ($entries as $idx => $e):
            $et = (string)$e['event_type'];
            $ent = (string)$e['entity_type'];
            $kind = match(true) {
              $et === 'ledger.genesis' => ['genesis', 'Genesis', 'var(--success)'],
              str_starts_with($et, 'audit.') => ['note', 'Audit note', 'var(--warning)'],
              str_starts_with($et, 'certificate') => ['cert', 'Certificate', 'var(--info)'],
              str_starts_with($et, 'grade') => ['grade', 'Grade', 'var(--accent)'],
              str_starts_with($et, 'attendance') => ['attend', 'Attendance', 'var(--success)'],
              str_starts_with($et, 'user') => ['user', 'Account', 'var(--accent-3)'],
              default => ['ev', ucfirst(str_replace('.', ' ', $et)), 'var(--accent)'],
            };
            $pretty = $e['payload'];
            $decoded = json_decode((string)$pretty, true);
            if (is_array($decoded)) {
                $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
            $isLast = $idx === count($entries) - 1;
            $verified = $chainIntact;
          ?>
          <div class="chain-block kind-<?= $kind[0] ?>" style="--kind:<?= $kind[2] ?>">
            <div class="chain-rail">
              <span class="chain-node"><?= $isLast ? '▸' : '' ?></span>
              <?php if (!$isLast): ?><span class="chain-line"></span><?php endif; ?>
            </div>
            <div class="chain-main">
              <div class="chain-top">
                <div class="chain-meta">
                  <span class="chain-num mono">#<?= (int)$e['id'] ?></span>
                  <span class="badge chain-kind"><span class="dot"></span><?= e($kind[1]) ?></span>
                  <code class="chain-event"><?= e($et) ?></code>
                  <span class="tiny faint mono chain-entity"><?= e($ent) ?>#<?= (int)$e['entity_id'] ?></span>
                </div>
                <div class="chain-side">
                  <span class="chain-time tiny faint"><?= e(date("M j, H:i", strtotime($e['created_at']))) ?></span>
                  <span class="badge chain-ok" title="<?= $verified ? 'Hash chain verified' : 'Not verified' ?>"><?= icon('check-circle') ?></span>
                </div>
              </div>
              <div class="chain-actor">
                <span class="chain-ava"><?= e(strtoupper(substr($e['actor'] ?: 'S', 0, 1))) ?></span>
                <span class="small chain-actor-name"><b><?= e($e['actor'] ?: 'system') ?></b> signed</span>
              </div>
              <pre class="chain-payload"><?= e($pretty) ?></pre>
              <div class="chain-links">
                <div class="chain-hash">
                  <span class="tiny faint hash-tag">prev</span>
                  <code class="hash-sha" title="<?= e($e['prev_hash']) ?>"><?= e(substr($e['prev_hash'], 0, 10)) ?><span class="hash-more">…</span></code>
                </div>
                <div class="chain-hash">
                  <span class="tiny faint hash-tag">self</span>
                  <code class="hash-sha" title="<?= e($e['record_hash']) ?>"><?= e(substr($e['record_hash'], 0, 10)) ?><span class="hash-more">…</span></code>
                </div>
                <span class="chain-gap"></span>
                <span class="chain-links d-flex gap-8" style="gap:8px">
                  <a href="#" class="hash-toggle tiny" data-full="<?= e($e['record_hash']) ?>" data-prev="<?= e($e['prev_hash']) ?>" data-actor="<?= e($e['actor'] ?: 'system') ?>" onclick="return ledgerHashToggle(this)"><?= icon('lock') ?> chain</a>
                  <button type="button" class="tiny payload-toggle" onclick="this.closest('.chain-block').querySelector('.chain-payload').classList.toggle('open')">view → payload</button>
                </span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<script>
window.ledgerHashToggle = function (el) {
  if (el.dataset.revealed) {
    el.innerHTML = '<?= icon('lock') ?>&nbsp;chained&nbsp;✓';
    delete el.dataset.revealed;
  } else {
    el.innerHTML = '<span class="mono">h ' + el.dataset.full.slice(0, 16) + '… · p ' + el.dataset.prev.slice(0, 16) + '…</span>';
    el.dataset.revealed = '1';
  }
  return false;
};

(function () {
  // — Two-factor coverage donut —
  (function drawDonut() {
    var el = document.getElementById('twofa-donut');
    if (!el) return;
    var ok = <?= (int)$staffTwofa['ok'] ?>, total = <?= (int)$staffTwofa['total'] ?>;
    var pct = total ? Math.round(ok / total * 100) : 0;
    var color = total && ok === total ? 'var(--success)' : (ok > 0 ? 'var(--warning)' : 'var(--danger)');
    if (window.donut) window.donut(el, pct, color);
  })();

  // Filter + search staff rows
  var q = document.getElementById('twofa-q');
  var filters = document.getElementById('twofa-filters');
  var list = document.getElementById('twofa-list');
  var empty = document.getElementById('twofa-empty');
  var unprot = document.getElementById('twofa-unprot');
  if (!list) return;
  var rows = Array.prototype.slice.call(list.querySelectorAll('.twofa-row'));
  var activeRole = 'all';

  function updateCounter() {
    var shown = rows.filter(function (r) { return r.style.display !== 'none'; }).length;
    if (empty) empty.hidden = shown !== 0;
    if (typeof window.toast === 'function' && shown === 0 && list.dataset.toasted !== '1') {
      list.dataset.toasted = '1';
    }
  }

  function apply() {
    var kw = (q ? q.value.trim().toLowerCase() : '');
    var unprotectedOnly = unprot ? unprot.checked : false;
    var shown = 0;
    rows.forEach(function (r) {
      var hit = true;
      if (activeRole !== 'all' && r.dataset.role !== activeRole) hit = false;
      if (hit && unprotectedOnly && r.dataset.protected !== '0') hit = false;
      if (hit && kw && r.dataset.name.indexOf(kw) === -1 && r.dataset.email.indexOf(kw) === -1) hit = false;
      r.style.display = hit ? '' : 'none';
      if (hit) shown++;
    });
    if (empty) empty.hidden = shown !== 0;
    document.querySelectorAll('#twofa-filters .chip-count').forEach(function (c) {
      var chip = c.parentElement, role = chip.dataset.role;
      if (role !== 'all') c.textContent = rows.filter(function (r) {
        return r.dataset.role === role && r.style.display !== 'none';
      }).length;
    });
    var all = rows.filter(function (r) { return r.style.display !== 'none'; }).length;
    var allChip = document.querySelector('#twofa-filters [data-role="all"] .chip-count');
    if (allChip) allChip.textContent = all;
  }

  filters.addEventListener('click', function (e) {
    var chip = e.target.closest('#twofa-filters .chip[data-role]');
    if (!chip) return;
    document.querySelectorAll('#twofa-filters .chip[data-role]').forEach(function (c) { c.classList.remove('active'); });
    chip.classList.add('active');
    activeRole = chip.dataset.role;
    apply();
  });
  if (unprot) unprot.addEventListener('change', function () {
    document.querySelectorAll('.twofa-row').forEach(function (r) { r.classList.toggle('dimmed', unprot.checked && r.dataset.protected === '1'); });
    apply();
  });
  if (q) q.addEventListener('input', apply);
  apply();

  // Loading state on the 2FA action buttons (no double submit, no layout shift)
  document.querySelectorAll('#twofa-list form.inline[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function () {
      var btn = f.querySelector('.btn');
      if (btn && !btn.dataset.submitting) {
        btn.dataset.submitting = '1';
        btn.classList.add('loading');
        btn.dataset.html = btn.innerHTML;
        btn.innerHTML = '<?= icon('lock') ?>';
      }
    });
  });
})();
</script>