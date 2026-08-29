<?php /* Director transfers view */
$snapshotView = null;
foreach (array_merge($incoming, $outgoing) as $r) {
    if (($r['record_snapshot'] ?? '') !== '' && ($_GET['snapshot'] ?? '') == $r['id']) {
        $snapshotView = json_decode($r['record_snapshot'], true);
        $snapshotId = $r['id'];
        break;
    }
}
?>
<div class="page-head">
  <div>
    <h1><?= icon('users-card') ?> School Transfers</h1>
    <p class="sub">Issue transfer codes for leaving students — the new school gets the full academic record within minutes</p>
  </div>
</div>

<?php if ($snapshotView): ?>
<div class="card" style="padding:26px">
  <div class="flex" style="justify-content:space-between;align-items:center;margin-bottom:18px">
    <h3><?= icon('file') ?> Portable record snapshot #<?= (int)$snapshotId ?></h3>
    <a class="btn btn-sm btn-ghost" href="<?= e(url('director/transfers')) ?>">← Back</a>
  </div>
  <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:22px">
    <div class="stat-box"><span class="tiny faint">Student</span><b><?= e($snapshotView['student'] ?? '—') ?></b></div>
    <div class="stat-box"><span class="tiny faint">From school</span><b><?= e($snapshotView['from_school'] ?? '—') ?></b></div>
    <div class="stat-box"><span class="tiny faint">XP / Level</span><b><?= (int)($snapshotView['xp'] ?? 0) ?> · L<?= (int)($snapshotView['level'] ?? 1) ?></b></div>
    <div class="stat-box"><span class="tiny faint">Streak</span><b><?= (int)($snapshotView['streak'] ?? 0) ?> days</b></div>
  </div>
  <?php if (!empty($snapshotView['grades'])): ?>
    <h4 class="small" style="margin-bottom:10px"><?= icon('note') ?> Exam history (<?= count($snapshotView['grades']) ?>)</h4>
    <table class="table" style="margin-bottom:22px">
      <thead><tr><th>Exam</th><th>Subject</th><th>Score</th><th>%</th><th>Pass</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach (array_slice($snapshotView['grades'], 0, 25) as $g): ?>
          <tr>
            <td class="small"><?= e($g['exam']) ?></td>
            <td class="small"><?= e($g['subject']) ?></td>
            <td class="small"><?= (float)$g['score'] ?>/<?= (float)$g['total'] ?></td>
            <td class="small"><?= round((float)$g['percentage'], 1) ?>%</td>
            <td class="small"><?= $g['passed'] ? '<span class="badge badge-success">' . icon('check-circle') . ' passed</span>' : '<span class="badge badge-danger">' . icon('ban-circle') . ' failed</span>' ?></td>
            <td class="small faint"><?= e($g['date']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <?php if (!empty($snapshotView['attendance'])): ?>
    <h4 class="small" style="margin-bottom:10px"><?= icon('doc') ?> Attendance (<?= (int)$snapshotView['attendance']['total'] ?> days)</h4>
    <div class="flex gap-12" style="margin-bottom:22px">
      <span class="badge badge-success">Present <?= (int)$snapshotView['attendance']['present'] ?></span>
      <span class="badge badge-danger">Absent <?= (int)$snapshotView['attendance']['absent'] ?></span>
      <span class="badge badge-warning">Late <?= (int)$snapshotView['attendance']['late'] ?></span>
    </div>
  <?php endif; ?>
  <?php if (!empty($snapshotView['courses'])): ?>
    <h4 class="small" style="margin-bottom:10px"><?= icon('graduation') ?> Courses</h4>
    <?php foreach ($snapshotView['courses'] as $c): ?>
      <div class="flex gap-8" style="justify-content:space-between;padding:7px 0;border-bottom:1px dashed var(--border)">
        <span class="small"><?= e($c['title']) ?></span>
        <span class="tiny faint"><?= (float)$c['progress'] ?>% <?= $c['completed'] ? '· ' . icon('check-circle') . ' completed' : '' ?></span>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  <?php if (!empty($snapshotView['badges'])): ?>
    <h4 class="small" style="margin:18px 0 10px"><?= icon('medal') ?> Badges (<?= count($snapshotView['badges']) ?>)</h4>
    <p><?php foreach ($snapshotView['badges'] as $b): ?><span class="badge"><?= icon((string)($b['icon'] ?? 'medal')) ?> <?= e($b['name']) ?></span> <?php endforeach; ?></p>
  <?php endif; ?>
  <?php if (!empty($snapshotView['certificates'])): ?>
    <h4 class="small" style="margin:18px 0 10px"><?= icon('doc') ?> Certificates</h4>
    <?php foreach ($snapshotView['certificates'] as $c): ?>
      <div class="small" style="padding:4px 0"><?= e($c['course']) ?> · <span class="mono"><?= e($c['code']) ?></span> · <?= e($c['issued']) ?></div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php else: ?>

<?php
  $unusedAll = count(array_filter($codes, fn($c) => !$c['used']));
  $pendingIn = count(array_filter($incoming, fn($r) => $r['status'] === 'pending'));
  $outAll = count($outgoing);
?>

<!-- Stats — jump to section -->
<div class="grid grid-4" style="margin-bottom:20px">
  <?php if (!$isHigherEd): ?>
  <a class="card tstat-card" href="#codes" style="text-decoration:none"><span class="stat-ico"><?= icon('key') ?></span>
    <div class="stat-text"><b><?= count($codes) ?></b><span>Codes issued</span></div></a>
  <a class="card tstat-card" href="#codes" style="text-decoration:none"><span class="stat-ico"><?= icon('clock') ?></span>
    <div class="stat-text"><b><?= (int)$unusedAll ?></b><span>Unused codes</span></div></a>
  <?php endif; ?>
  <a class="card tstat-card" href="#incoming" style="text-decoration:none"><span class="stat-ico"><?= icon('download') ?></span>
    <div class="stat-text"><b><?= (int)$pendingIn ?></b><span>Incoming pending</span></div></a>
  <a class="card tstat-card" href="#outgoing" style="text-decoration:none"><span class="stat-ico"><?= icon('send') ?></span>
    <div class="stat-text"><b><?= (int)$outAll ?></b><span>Outgoing transfers</span></div></a>
</div>

<?php if ($isHigherEd): ?>
<div class="card" style="padding:20px;margin-bottom:18px;border-left:3px solid var(--accent)">
  <div style="display:flex;align-items:center;gap:10px">
    <span style="font-size:18px"><?= icon('info') ?></span>
    <div>
      <b style="font-size:13px">University Transfers</b>
      <p style="font-size:12px;color:var(--text-secondary);margin:2px 0 0">Transfer codes for higher education institutions are issued and managed by the Ministry of Education. Contact the ministry for transfer authorizations.</p>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="grid" style="grid-template-columns:<?= $isHigherEd ? '1fr' : '1fr 1fr' ?>;gap:18px;align-items:start">
  <?php if (!$isHigherEd): ?>
  <!-- Issue transfer code -->
  <div class="card" id="codes" style="scroll-margin-top:20px">
    <div class="sec-head"><span class="sec-ico"><?= icon('upload') ?></span><h3>Issue transfer code</h3></div>
    <p class="sec-sub">Select a leaving student — they register at the new school with the code, then you approve the record copy here.</p>
    <form method="post" class="flex gap-8" style="align-items:flex-end">
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
      <div class="flex-col" style="flex:1;min-width:0">
        <label class="small faint" style="margin-bottom:6px">Leaving student</label>
        <select class="select" name="student" required style="width:100%">
          <option value="">Select student…</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= e($s['first_name'] . ' ' . $s['last_name']) ?> (<?= e($s['student_id'] ?? '—') ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary" name="issue_code" value="1"><?= icon('plus') ?> Generate code</button>
    </form>

    <?php if ($codes): ?>
      <div class="flex-between" style="margin:20px 0 10px">
        <h4 class="small"><?= icon('key') ?> Recent codes (<?= count($codes) ?>)</h4>
        <?php if ($unusedAll): ?><span class="badge badge-warning"><?= (int)$unusedAll ?> unused</span><?php endif; ?>
      </div>
      <div class="tf-table">
      <table class="table data">
        <thead><tr><th>Code</th><th>Student</th><th>Used</th><th class="actions"></th></tr></thead>
        <tbody>
          <?php foreach ($codes as $c): ?>
            <tr>
              <td class="small">
                <button type="button" class="code-copy" data-code="<?= e($c['code']) ?>" title="Click to copy"><?= e($c['code']) ?> <?= icon('clipboard') ?></button>
              </td>
              <td class="small"><?= e($c['for_student'] ?? '—') ?></td>
              <td class="small"><?= $c['used'] ? '<span class="badge badge-success">used</span>' : '<span class="badge badge-muted">no</span>' ?></td>
              <td class="actions">
                <?php if (!$c['used']): ?>
                  <form method="post" class="inline"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <button class="btn btn-sm btn-danger" name="revoke_code" value="<?= (int)$c['id'] ?>" data-confirm="Revoke this code? It can no longer be used to transfer records." title="Revoke code"><?= icon('trash') ?></button></form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    <?php else: ?>
      <div class="empty-state" style="margin-top:18px">
        <span class="empty-ic"><?= icon('key') ?></span>
        <p class="muted small">No transfer codes issued yet — generate the first one above.</p>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Incoming transfers -->
  <div class="card" id="incoming" style="scroll-margin-top:20px">
    <div class="sec-head"><span class="sec-ico" style="background:var(--info-soft);color:var(--info)"><?= icon('download') ?></span><h3>Incoming transfers</h3></div>
    <p class="sec-sub">Students registering from another school with a transfer code.</p>
    <?php if (!$incoming): ?>
      <div class="empty-state" style="margin-top:6px">
        <span class="empty-ic"><?= icon('transfer') ?></span>
        <p class="muted small">No incoming transfer requests — when a student registers with a code, they appear here for approval.</p>
      </div>
    <?php endif; ?>
    <?php foreach ($incoming as $r): ?>
      <div class="in-row">
        <span class="avatar"><?= e(mb_strtoupper(mb_substr((string)$r['first_name'], 0, 1) . mb_substr((string)$r['last_name'], 0, 1))) ?></span>
        <div style="flex:1;min-width:0">
          <b class="small"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b>
          <p class="tiny faint" style="margin-top:2px">
            from <b><?= e($r['from_school']) ?></b> · <?= e(date('M j, Y', strtotime($r['created_at']))) ?> · L<?= (int)$r['level'] ?> · <?= (int)$r['xp'] ?> XP
          </p>
        </div>
        <?php if ($r['status'] === 'pending'): ?>
          <div class="flex gap-8">
            <form method="post" class="inline">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
              <button class="btn btn-sm btn-primary" name="approve" value="<?= (int)$r['id'] ?>"><?= icon('check-circle') ?> Approve & copy</button>
            </form>
            <form method="post" class="inline">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
              <button class="btn btn-sm btn-ghost" name="reject" value="<?= (int)$r['id'] ?>" data-confirm="Reject this transfer?">Reject</button>
            </form>
          </div>
        <?php else: ?>
          <span class="badge <?= $r['status'] === 'completed' ? 'badge-success' : ($r['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>"><?= e($r['status']) ?></span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Outgoing transfers -->
<div class="card" id="outgoing" style="margin-top:18px;scroll-margin-top:20px">
  <div class="sec-head"><span class="sec-ico" style="background:var(--success-soft);color:var(--success)"><?= icon('send') ?></span><h3>Outgoing transfers</h3></div>
  <p class="sec-sub">Students who transferred to another school — their full record moved with them.</p>
  <?php if (!$outgoing): ?><p class="muted small">No students have transferred out of your school yet.</p><?php endif; ?>
  <div class="tf-table">
  <table class="table data">
    <thead><tr><th>Student</th><th>To school</th><th>Status</th><th>Record</th><th>Date</th></tr></thead>
    <tbody>
      <?php foreach ($outgoing as $r): ?>
        <tr>
          <td class="small"><b><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b> <span class="tiny faint">(<?= e($r['student_id'] ?? '—') ?>)</span></td>
          <td class="small"><?= e($r['to_school']) ?></td>
          <td class="small"><span class="badge <?= $r['status'] === 'completed' ? 'badge-success' : ($r['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>"><?= e($r['status']) ?></span></td>
          <td class="small">
            <?php if ($r['has_snapshot']): ?>
              <a class="btn btn-sm btn-ghost" href="<?= e(url('director/transfers&snapshot=' . $r['id'])) ?>"><?= icon('file') ?> View record →</a>
            <?php else: ?><span class="faint">—</span><?php endif; ?>
          </td>
          <td class="small faint"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<style>
.in-row { display:flex; gap:14px; align-items:center; padding:14px 16px; border-radius:14px; margin-bottom:12px; border:1px solid var(--border); background:var(--bg-elev); transition:border-color .15s ease, transform .15s ease; }
.in-row:hover { border-color:transparent; box-shadow: 0 0 0 1px rgba(255,255,255,.15), inset 0 1px 1px rgba(255,255,255,.2), 0 4px 12px rgba(0,0,0,.06); transform:translateX(2px); }
.in-row:last-child { margin-bottom:0; }
.in-row .avatar { background:linear-gradient(135deg,var(--info),var(--accent)); }
.in-row b.small { font-size:13.5px; }
.in-row p.tiny { font-size:11.5px; }
.code-copy { display:inline-flex; align-items:center; gap:7px; background:var(--bg-soft); border:1px solid var(--border); border-radius:8px; padding:6px 12px; font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:12.5px; font-weight:600; letter-spacing:.02em; color:var(--text); cursor:pointer; transition:all .15s ease; }
.code-copy .ico { width:13px; height:13px; opacity:.7; }
.code-copy:hover { border-color:transparent; color:var(--accent); transform:translateY(-1px); box-shadow: 0 0 0 1px rgba(255,255,255,.15), inset 0 1px 1px rgba(255,255,255,.2), 0 4px 12px rgba(0,0,0,.06); }
.code-copy.copied { border-color:var(--success); color:var(--success); }
.sec-head { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
.sec-head .sec-ico { width:32px; height:32px; border-radius:10px; background:var(--accent-soft); color:var(--accent); display:inline-flex; align-items:center; justify-content:center; flex:none; }
.sec-head .sec-ico .ico { width:16px; height:16px; }
.sec-head h3 { font-size:15.5px; letter-spacing:-.01em; }
.sec-sub { font-size:12.5px; line-height:1.55; color:var(--text-dim); margin:0 0 16px; }
.tf-table { margin-top:6px; border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.tf-table table.data td { padding:12px 14px; }
.tf-table table.data th { padding:12px 14px; }
.tf-table td.small { font-size:13px; }
.tstat-card .stat-text { display:flex; flex-direction:column; gap:4px; min-width:0; padding:3px 0; }
.tstat-card .stat-text b { font-size:1.55rem; line-height:1.1; letter-spacing:-.02em; color:var(--text); }
.tstat-card .stat-text span { font-size:12.5px; color:var(--text-dim); line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.empty-state { padding:6px 0; text-align:center; }
.empty-state p { font-size:12.5px; line-height:1.6; max-width:340px; }
@keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:none; } }
.tstat-card { animation:fadeUp .35s ease both; }
.grid-4 > .tstat-card:nth-child(1) { animation-delay:.02s; }
.grid-4 > .tstat-card:nth-child(2) { animation-delay:.07s; }
.grid-4 > .tstat-card:nth-child(3) { animation-delay:.12s; }
.grid-4 > .tstat-card:nth-child(4) { animation-delay:.17s; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.code-copy').forEach(b => {
    b.dataset.orig = b.innerHTML;
    b.addEventListener('click', async () => {
      const ok = async () => {
        try {
          await navigator.clipboard.writeText(b.dataset.code);
          return true;
        } catch (e) {
          const ta = document.createElement('textarea');
          ta.value = b.dataset.code;
          document.body.appendChild(ta);
          ta.select();
          const done = document.execCommand('copy');
          ta.remove();
          return done;
        }
      };
      if (await ok()) {
        b.classList.add('copied');
        b.innerHTML = '✓ Copied';
        setTimeout(() => { b.classList.remove('copied'); b.innerHTML = b.dataset.orig; }, 1600);
      }
    });
  });
  document.querySelectorAll('.tstat-card .stat-text b').forEach(b => {
    const target = parseInt(b.textContent, 10);
    if (isNaN(target)) return;
    const t0 = performance.now(), dur = 700;
    const tick = t => {
      const p = Math.min(1, (t - t0) / dur);
      b.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  });
});
</script>

