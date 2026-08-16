<?php /* Admin transfer detail */
$snap = $snapshot ?? [];
$steps = [];
foreach (['attendance', 'grades', 'fees', 'discipline'] as $k) {
    if (isset($snap[$k]) && $snap[$k]) $steps[] = $k;
}
?>
<div class="page-head">
  <div>
    <a class="small faint" href="<?= e(url('admin/transfers')) ?>">← All transfers</a>
    <h1 style="margin-top:4px"><?= icon('refresh') ?> Transfer #<?= (int)$req['id'] ?> <span class="badge <?= $req['status'] === 'completed' ? 'badge-success' : ($req['status'] === 'pending' ? 'badge-warning' : 'badge-muted') ?>"><?= e($req['status']) ?></span></h1>
    <p class="sub"><?= e($req['sf'] . ' ' . $req['sl']) ?> · <?= e($req['from_school']) ?> → <?= e($req['to_school']) ?> · requested <?= e(time_ago($req['created_at'])) ?></p>
  </div>
  <?php if ($req['status'] === 'pending'): ?>
    <div class="flex gap-6">
      <form method="post" class="inline" data-confirm="Approve this transfer?"><?= csrf_field() ?><button class="btn btn-success" name="approve" value="1"><?= icon('check-circle') ?> Approve</button></form>
      <form method="post" class="inline" data-confirm="Reject this transfer?"><?= csrf_field() ?><button class="btn btn-danger" name="reject" value="1"><?= icon('ban-circle') ?> Reject</button></form>
    </div>
  <?php endif; ?>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;margin-bottom:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('user') ?> Student</h3>
    <div class="grid2 small" style="gap:8px 16px">
      <div class="flex-col"><span class="faint">Name</span><b><?= e($req['sf'] . ' ' . $req['sl']) ?></b></div>
      <div class="flex-col"><span class="faint">Email</span><b><?= e($req['semail']) ?></b></div>
      <div class="flex-col"><span class="faint">Student ID</span><b class="mono"><?= e($req['sstid'] ?: '—') ?></b></div>
      <div class="flex-col"><span class="faint">Account status</span><b><?= e($req['sstatus']) ?></b></div>
      <div class="flex-col"><span class="faint">Source account</span><b><?= $req['ocf'] ? e($req['ocf'] . ' ' . $req['ocl']) : 'New account (no history)' ?></b></div>
    </div>
    <?php if ($req['reason']): ?>
      <h3 class="card-title" style="margin-top:14px"><?= icon('chat') ?> Reason</h3>
      <p class="small"><?= e($req['reason']) ?></p>
    <?php endif; ?>
    <?php if ($req['referral_code']): ?>
      <p class="tiny faint" style="margin-top:8px">Referral code: <span class="mono"><?= e($req['referral_code']) ?></span></p>
    <?php endif; ?>
    <?php if ($req['decided_at']): ?>
      <p class="tiny faint" style="margin-top:8px">Decided <?= e(time_ago($req['decided_at'])) ?><?= $req['af'] ? ' by ' . e($req['af'] . ' ' . $req['al']) : '' ?></p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('box') ?> Record snapshot</h3>
    <?php if ($snap): ?>
      <div class="flex gap-6" style="flex-wrap:wrap;margin-bottom:10px">
        <?php foreach ($steps as $k): ?><span class="badge badge-success">✓ <?= e(ucfirst($k)) ?></span><?php endforeach; ?>
      </div>
      <pre class="small faint" style="max-height:220px;overflow:auto;font-size:11px"><?= e(json_encode($snap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    <?php else: ?>
      <p class="muted small">No source record — the student is a new account at the target school.</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('trend-up') ?> Performance preview</h3>
    <p class="small faint">Recent attendance</p>
    <?php foreach ($attendance as $a): ?>
      <div class="list-row" style="padding:5px 0"><div class="flex-1 small"><?= e($a['course']) ?></div><div class="tiny faint"><?= e($a['date']) ?></div><span class="badge <?= $a['status'] === 'present' ? 'badge-success' : 'badge-warning' ?>"><?= e($a['status']) ?></span></div>
    <?php endforeach; ?>
    <?php if (!$attendance): ?><p class="muted small">None recorded yet.</p><?php endif; ?>
    <p class="small faint" style="margin-top:12px">Average score by course</p>
    <?php foreach ($grades as $g): ?>
      <div class="list-row" style="padding:5px 0"><div class="flex-1 small"><?= e($g['title']) ?></div><b class="small"><?= e($g['pct']) ?>%</b></div>
    <?php endforeach; ?>
    <?php if (!$grades): ?><p class="muted small">No exam data.</p><?php endif; ?>
  </div>
</div>

<div class="card">
  <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> Request history</h3>
  <?php foreach ($history as $h): ?>
    <div class="list-row" style="padding:7px 0">
      <div class="flex-1 small"><b>#<?= (int)$h['id'] ?></b> — <?= e($h['status']) ?><?= $h['reason'] ? ' · ' . e(mb_substr($h['reason'], 0, 80)) : '' ?></div>
      <div class="tiny faint"><?= e(time_ago($h['created_at'])) ?></div>
    </div>
  <?php endforeach; ?>
  <?php if (!$history): ?><p class="muted small">No history.</p><?php endif; ?>
</div>
