<?php /* Student attendance view */
$statusMeta = [
    'present' => ['label' => 'Present', 'cls' => 'badge-success'],
    'absent' => ['label' => 'Absent', 'cls' => 'badge-danger'],
    'late' => ['label' => 'Late', 'cls' => 'badge-warning'],
    'excused' => ['label' => 'Excused', 'cls' => 'badge-muted'],
];
?>
<div class="page-head">
  <div>
    <h1><?= icon('calendar') ?> My Attendance</h1>
    <p class="sub">Your attendance record across courses</p>
  </div>
</div>

<div class="grid4" style="margin-bottom:22px">
  <div class="card stat-card"><div class="stat-value"><?= (int)$rate ?>%</div><div class="small faint">Attendance rate</div></div>
  <?php foreach (['present', 'absent', 'late', 'excused'] as $k): ?>
    <div class="card stat-card"><div class="stat-value"><?= (int)($summary[$k] ?? 0) ?></div><div class="small faint"><?= ucfirst($k) ?></div></div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h3 class="card-title">Record (last 120 sessions)</h3>
  <?php if (!$rows): ?><p class="muted small">No attendance records yet.</p><?php endif; ?>
  <table class="table">
    <thead><tr><th>Date</th><th>Course</th><th>Status</th><th>Marked by</th><th>Note</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): $m = $statusMeta[$r['status']] ?? $statusMeta['absent']; ?>
        <tr>
          <td><?= e(date('M j, Y', strtotime($r['date']))) ?></td>
          <td><?= e($r['course_title']) ?></td>
          <td><span class="badge <?= $m['cls'] ?>"><?= $m['label'] ?></span></td>
          <td class="small faint"><?= e($r['tfirst'] . ' ' . $r['tlast']) ?></td>
          <td class="small faint"><?= e($r['note'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
