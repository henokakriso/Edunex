<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>
<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem">
  <div class="card stat"><div class="stat-num"><?= e($stats['schools']) ?></div><div class="stat-lbl">Schools</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['students']) ?></div><div class="stat-lbl">Students</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['teachers']) ?></div><div class="stat-lbl">Teachers</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['directors']) ?></div><div class="stat-lbl">Directors</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['courses']) ?></div><div class="stat-lbl">Courses</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['enroll30']) ?></div><div class="stat-lbl">Enrollments (30d)</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['pending_transfers']) ?></div><div class="stat-lbl">Pending Transfers</div></div>
</div>
<div class="card">
  <div class="card-head"><h2>Schools</h2></div>
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>School</th><th>Students</th><th>Teachers</th><th>Directors</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (empty($schoolStats)): ?>
        <tr><td colspan="5" style="text-align:center;color:var(--muted)">No schools found.</td></tr>
      <?php else: foreach ($schoolStats as $ss): ?>
        <tr>
          <td><a href="<?= url('index.php?r=woreda/school&id='.$ss['school']['id']) ?>"><?= e($ss['school']['name']) ?></a></td>
          <td><?= e($ss['students']) ?></td>
          <td><?= e($ss['teachers']) ?></td>
          <td><?= e($ss['directors']) ?></td>
          <td><span class="badge badge-<?= e($ss['school']['status'] ?? 'active') ?>"><?= e($ss['school']['status'] ?? 'active') ?></span></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
