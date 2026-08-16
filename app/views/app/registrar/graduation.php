<?php /* Registrar graduation audit — credits vs program requirement */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('graduation') ?> Graduation Audit</h1>
    <p class="sub">Earned credit hours vs department graduation requirements</p>
  </div>
</div>

<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Student</th><th>Department</th><th>Required</th><th>Earned</th><th>Progress</th><th>Status</th><th style="width:170px">Degree</th></tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <b><?= e($r['student']) ?></b>
            <div class="tiny faint"><?= e($r['student_id']) ?> · <?= e($r['email']) ?></div>
          </td>
          <td><?= e($r['dept_name']) ?></td>
          <td class="tiny"><?= (int)$r['required_credits'] ?></td>
          <td class="tiny"><b><?= $r['earned_credits'] > 0 ? number_format($r['earned_credits'], 1) : '0' ?></b>
            <div class="tiny faint"><?= (int)$r['passed_courses'] ?> / <?= (int)$r['enrolled_courses'] ?> courses passed</div>
          </td>
          <td>
            <?php $pct = $r['required_credits'] > 0 ? min(100, (int)round($r['earned_credits'] / $r['required_credits'] * 100)) : 0; ?>
            <div class="progress"><div style="width:<?= $pct ?>%"></div></div>
            <div class="tiny faint"><?= $pct ?>%</div>
          </td>
          <td>
            <?php if ($r['eligible']): ?>
              <span class="badge badge-success"><?= icon('check') ?> Eligible to graduate</span>
            <?php else: ?>
              <span class="badge badge-muted"><?= $r['dept_name'] === '—' ? 'No department' : 'In progress' ?></span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($r['degree'])): ?>
              <span class="badge badge-info"><?= icon('check') ?> <?= e($r['degree']['degree_code']) ?></span>
              <div class="tiny faint"><?= e(date('M j, Y', strtotime($r['degree']['issued_at']))) ?></div>
            <?php elseif ($r['eligible']): ?>
              <form method="post">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-success" name="issue_degree" value="<?= (int)$r['id'] ?>"><?= icon('certificate') ?> Issue degree</button>
              </form>
            <?php else: ?>
              <span class="tiny faint">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="7" class="tiny faint">No students.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
