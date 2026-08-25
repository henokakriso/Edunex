<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>
<div class="card">
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>School</th><th>Students</th><th>Teachers</th><th>Courses</th><th>Enrollments</th><th>Student:Teacher</th></tr></thead>
      <tbody>
      <?php if (empty($schoolStats)): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--muted)">No data.</td></tr>
      <?php else: foreach ($schoolStats as $ss): ?>
        <tr>
          <td><?= e($ss['school']['name']) ?></td>
          <td><?= e($ss['students']) ?></td>
          <td><?= e($ss['teachers']) ?></td>
          <td><?= e($ss['courses']) ?></td>
          <td><?= e($ss['enrollments']) ?></td>
          <td><?= e($ss['student_teacher_ratio']) ?>:1</td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
