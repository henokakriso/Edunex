<?php /* Student transcript */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('file-text') ?> My Transcript</h1>
    <p class="sub">Academic record and GPA</p>
  </div>
</div>

<div class="grid2" style="margin-bottom:18px">
  <div class="card">
    <h3 class="card-title"><?= icon('award') ?> Cumulative GPA</h3>
    <div style="font-size:2em;font-weight:700;margin:8px 0"><?= number_format($cgpa['cgpa'], 2) ?></div>
    <p class="tiny faint"><?= (int)$cgpa['credit_hours'] ?> credit hours · <?= number_format($cgpa['quality_points'], 1) ?> quality points</p>
    <p class="tiny" style="margin-top:4px">
      Standing: <span class="badge badge-<?= $cgpa['cgpa'] >= 3.5 ? 'success' : ($cgpa['cgpa'] >= 2.0 ? 'info' : 'danger') ?>"><?= e(standing_label(academic_standing($cgpa['cgpa']))) ?></span>
    </p>
  </div>
  <div class="card">
    <h3 class="card-title"><?= icon('send') ?> Request Transcript</h3>
    <form method="post" action="<?= e(url('university/transcript')) ?>" class="flex-row gap-6" style="margin-top:6px">
      <?= csrf_field() ?>
      <select class="input" name="type" style="width:150px">
        <option value="unofficial">Unofficial</option>
        <option value="official">Official</option>
      </select>
      <button class="btn btn-success" name="request_transcript" value="1"><?= icon('send') ?> Request</button>
    </form>
  </div>
</div>

<h3 style="margin:18px 0 8px"><?= icon('list') ?> Academic Record</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Code</th><th>Course</th><th>Credits</th><th>Grade</th><th>Points</th><th>Quality Pts</th><th>Semester</th></tr>
    </thead>
    <tbody>
      <?php foreach ($records as $r): ?>
        <tr>
          <td><span class="badge"><?= e($r['code']) ?></span></td>
          <td><?= e($r['title']) ?></td>
          <td><?= (int)$r['credit_hours'] ?></td>
          <td><b><?= e($r['grade']) ?></b></td>
          <td><?= number_format((float)$r['grade_points'], 1) ?></td>
          <td><?= number_format((float)$r['quality_points'], 1) ?></td>
          <td class="tiny"><?= e($r['semester_name']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$records): ?><tr><td colspan="7" class="tiny faint">No academic records yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<h3 style="margin:18px 0 8px"><?= icon('clock') ?> Transcript Requests</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Type</th><th>Status</th><th>Requested</th><th>Processed</th><th>Hash</th></tr>
    </thead>
    <tbody>
      <?php foreach ($requests as $r): ?>
        <tr>
          <td><?= e(ucfirst($r['type'])) ?></td>
          <td><span class="badge badge-<?= $r['status'] === 'ready' ? 'success' : 'warning' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td class="tiny"><?= e($r['requested_at']) ?></td>
          <td class="tiny"><?= $r['processed_at'] ? e($r['processed_at']) : '—' ?></td>
          <td class="tiny"><?= $r['hash'] ? e(substr($r['hash'], 0, 16)) . '…' : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$requests): ?><tr><td colspan="5" class="tiny faint">No transcript requests.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
