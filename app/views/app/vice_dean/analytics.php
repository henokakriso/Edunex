<?php /* Vice dean — faculty analytics */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> Faculty Analytics</h1>
    <p class="sub">Departments, courses, teachers and enrollments</p>
  </div>
</div>

<div class="card pad-0">
  <table class="table">
    <thead><tr><th>Department</th><th>Courses</th><th>Teachers</th><th>Enrollments</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['dept']) ?></b></td>
          <td><?= (int)$r['courses'] ?></td>
          <td><?= (int)$r['teachers'] ?></td>
          <td><?= (int)$r['enrollments'] ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="4" class="muted">No departments yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
