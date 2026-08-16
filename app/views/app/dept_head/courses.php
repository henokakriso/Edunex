<?php /* Dept head — department courses */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('exam') ?> Department Courses</h1>
    <p class="sub"><?= e($dept['name']) ?></p>
  </div>
</div>

<div class="card pad-0">
  <table class="table">
    <thead><tr><th>Course</th><th>Code</th><th>Teacher</th><th>Credits</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['title']) ?></b></td>
          <td class="tiny"><?= e($r['code'] ?: '—') ?></td>
          <td class="small"><?= e($r['teacher']) ?></td>
          <td class="tiny"><?= (float)$r['credit_hours'] ?></td>
          <td><span class="badge <?= $r['status'] === 'published' ? 'badge-success' : '' ?>"><?= e($r['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="muted">No courses in this department yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
