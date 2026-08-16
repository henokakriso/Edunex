<?php /* Dept head — students + credits */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> Department Analytics</h1>
    <p class="sub"><?= e($dept['name']) ?> · student credit progress</p>
  </div>
</div>

<div class="card pad-0">
  <table class="table">
    <thead><tr><th>Student</th><th>ID</th><th>Credits earned</th><th>Requirement</th><th>Progress</th></tr></thead>
    <tbody>
      <?php foreach ($students as $r): ?>
        <tr>
          <td><b><?= e($r['student']) ?></b></td>
          <td class="tiny"><?= e($r['student_id']) ?></td>
          <td class="tiny"><b><?= number_format((float)$r['credits'], 1) ?></b></td>
          <td class="tiny"><?= (int)$dept['required_credits'] ?></td>
          <td>
            <?php $pct = $dept['required_credits'] > 0 ? min(100, (int)round($r['credits'] / $dept['required_credits'] * 100)) : 0; ?>
            <div class="progress" style="width:120px"><div style="width:<?= $pct ?>%"></div></div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$students): ?><tr><td colspan="5" class="muted">No students in this department.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
