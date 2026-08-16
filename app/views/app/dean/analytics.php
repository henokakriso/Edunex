<?php /* Dean analytics — per-department stats within the faculty */
?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> Analytics</h1>
    <p class="sub"><?= e($faculty['name']) ?> — <?= (int)$totals['teachers'] ?> teachers · <?= (int)$totals['courses'] ?> courses · <?= (int)$totals['students'] ?> students</p>
  </div>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr><th>Department</th><th>Teachers</th><th>Courses</th><th>Students</th><th>Avg exam score</th></tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['name']) ?></b></td>
          <td><?= (int)$r['teachers'] ?></td>
          <td><?= (int)$r['courses'] ?></td>
          <td><?= (int)$r['students'] ?></td>
          <td><?= $r['avg_score'] !== null ? e($r['avg_score']) . '%' : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="muted">No active departments in this faculty.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
