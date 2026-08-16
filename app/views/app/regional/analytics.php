<?php /* Regional analytics — aggregates across assigned schools + workload recommendation */
?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> Regional Analytics</h1>
    <p class="sub">Aggregated across your <?= (int)$totals['schools'] ?> assigned school(s)</p>
  </div>
</div>

<?php if ($workload['schoolPct'] > 100 || $workload['dirPct'] > 100): ?>
  <div class="card" style="border-color:var(--danger);padding:12px 16px;margin-bottom:16px">
    <b><?= icon('alert') ?> Workload recommendation</b>
    <p class="small" style="margin:4px 0 0">
      Recommended capacity is 15 schools or 2 directors per school per regional admin.
      Current load: <b><?= (int)$workload['schools'] ?> school(s)</b>
      <?= $workload['schoolPct'] > 100 ? '<span style="color:var(--danger)">— over the 15-school cap</span>' : '' ?>,
      <b><?= (int)$workload['directors'] ?> director(s)</b>
      <?= $workload['dirPct'] > 100 ? '<span style="color:var(--danger)">— over the 2-per-school cap</span>' : '' ?>.
      Consider a second regional admin for this region.
    </p>
  </div>
<?php endif; ?>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(165px,1fr));gap:14px;margin-bottom:22px">
  <?php
  $cards = [
    ['Schools', $totals['schools'], icon('building')],
    ['Students', $totals['students'], icon('graduation')],
    ['Teachers', $totals['teachers'], icon('user')],
    ['Directors', $totals['directors'], icon('target')],
    ['Courses', $totals['courses'], icon('courses')],
    ['Enrollments', $totals['enrollments'], icon('users')],
    ['Exams', $totals['exams'], icon('exam')],
    ['Attendance', $totals['attendance'], icon('attendance')],
    ['AI messages', $totals['ai'], icon('chip')],
  ];
  foreach ($cards as [$label, $val, $ic]): ?>
    <div class="card stat-card" style="padding:16px 14px">
      <div class="stat-icon" style="font-size:22px"><?= $ic ?></div>
      <div>
        <div class="stat-value" style="font-size:1.4rem"><?= (int)$val ?></div>
        <div class="small faint"><?= $label ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card" style="margin-bottom:20px">
  <h3 class="card-title" style="margin-top:0"><?= icon('balance') ?> Workload balance</h3>
  <div class="flex-col gap-10 small">
    <div>
      <div class="flex" style="justify-content:space-between"><b>Schools vs cap (15)</b><span class="faint"><?= (int)$workload['schools'] ?> / 15</span></div>
      <div class="progress"><div class="progress-bar <?= $workload['schoolPct'] > 100 ? 'danger' : '' ?>" style="width:<?= min(100, $workload['schoolPct']) ?>%"></div></div>
    </div>
    <div>
      <div class="flex" style="justify-content:space-between"><b>Directors vs cap (2/school → <?= (int)max($workload['schools'] * 2, 0) ?>)</b><span class="faint"><?= (int)$workload['directors'] ?> / <?= (int)max($workload['schools'] * 2, 0) ?></span></div>
      <div class="progress"><div class="progress-bar <?= $workload['dirPct'] > 100 ? 'danger' : '' ?>" style="width:<?= min(100, $workload['dirPct']) ?>%"></div></div>
    </div>
    <p class="tiny faint" style="margin:0">Recommended admins for this region: <b><?= (int)$workload['recommended'] ?></b> (whichever limit is closer to capacity).</p>
  </div>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr><th>School</th><th>Students</th><th>Teachers</th><th>Directors</th><th>Courses</th><th>Enrollments</th><th>Exams</th><th>Attendance</th><th>AI msgs</th></tr>
    </thead>
    <tbody>
      <?php foreach ($perSchool as $ps): ?>
        <tr>
          <td><a class="btn btn-ghost btn-sm" href="<?= e(url('regional/school&id=' . (int)$ps['id'])) ?>"><?= icon('school') ?> <?= e($ps['name']) ?></a></td>
          <td><?= (int)$ps['students'] ?></td>
          <td><?= (int)$ps['teachers'] ?></td>
          <td><?= (int)$ps['directors'] ?></td>
          <td><?= (int)$ps['courses'] ?></td>
          <td><?= (int)$ps['enrollments'] ?></td>
          <td><?= (int)$ps['exams'] ?></td>
          <td><?= (int)$ps['attendance'] ?></td>
          <td><?= (int)$ps['ai'] ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$perSchool): ?><tr><td colspan="9" class="muted">No schools assigned to you yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
