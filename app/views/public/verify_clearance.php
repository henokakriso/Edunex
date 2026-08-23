<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clearance Verification — <?= e($request['tracking_code']) ?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', system-ui, sans-serif; background: #0f0f1a; color: #e0e0e0; padding: 20px; }
    .container { max-width: 600px; margin: 0 auto; background: #1a1a2e; border-radius: 12px; padding: 30px; border: 1px solid #2a2a4a; }
    .header { text-align: center; margin-bottom: 24px; border-bottom: 1px solid #2a2a4a; padding-bottom: 16px; }
    .header h1 { font-size: 1.4em; color: #fff; margin-bottom: 4px; }
    .header p { color: #888; font-size: 0.9em; }
    .tracking { background: #0a0a1a; padding: 12px; border-radius: 8px; text-align: center; margin: 16px 0; font-family: monospace; font-size: 1.1em; letter-spacing: 1px; }
    .status { text-align: center; padding: 12px; border-radius: 8px; margin: 16px 0; font-weight: 600; }
    .status.cleared { background: #0a2e1a; color: #4ade80; border: 1px solid #166534; }
    .status.pending { background: #2e2a0a; color: #facc15; border: 1px solid #854d0e; }
    .status.rejected { background: #2e0a0a; color: #f87171; border: 1px solid #991b1b; }
    .items { margin: 20px 0; }
    .item { display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-bottom: 1px solid #2a2a4a; }
    .item:last-child { border-bottom: none; }
    .item .dept { text-transform: capitalize; font-weight: 500; }
    .item .checker { color: #888; font-size: 0.85em; }
    .badge { padding: 3px 10px; border-radius: 12px; font-size: 0.8em; font-weight: 600; }
    .badge.passed { background: #0a2e1a; color: #4ade80; }
    .badge.failed { background: #2e0a0a; color: #f87171; }
    .badge.pending { background: #2e2a0a; color: #facc15; }
    .info { color: #888; font-size: 0.85em; text-align: center; margin-top: 16px; }
    .info b { color: #aaa; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Clearance Verification</h1>
      <p><?= e($request['school_name']) ?></p>
    </div>
    <div class="tracking"><?= e($request['tracking_code']) ?></div>
    <p style="text-align:center;margin:8px 0"><b><?= e($request['student_name']) ?></b> (<?= e($request['sid_no'] ?? '—') ?>)</p>
    <p style="text-align:center;color:#888;font-size:0.9em"><?= e(ucfirst($request['type'])) ?> · Requested <?= e($request['requested_at']) ?></p>
    <?php
      $statusClass = $request['status'] === 'cleared' ? 'cleared' : ($request['status'] === 'rejected' ? 'rejected' : 'pending');
    ?>
    <div class="status <?= $statusClass ?>"><?= e(ucfirst($request['status'])) ?></div>
    <div class="items">
      <?php foreach ($items as $it): ?>
        <div class="item">
          <div>
            <div class="dept"><?= e($it['department']) ?></div>
            <?php if ($it['checker_name']): ?>
              <div class="checker">Checked by <?= e($it['checker_name']) ?> · <?= e($it['checked_at']) ?></div>
            <?php endif; ?>
          </div>
          <span class="badge <?= $it['status'] ?>"><?= e(ucfirst($it['status'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="info">
      This clearance was verified on <?= e(date('Y-m-d H:i:s')) ?>.<br>
      Tracking code: <b><?= e($request['tracking_code']) ?></b>
    </div>
  </div>
</body>
</html>
