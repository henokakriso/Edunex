<?php /* Vice dean dashboard — faculty overview */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('building') ?> Vice Dean</h1>
    <p class="sub"><?= $faculty ? e($faculty['name']) . ' · ' . e($faculty['school_name']) : 'No active faculty assigned' ?></p>
  </div>
  <?php if ($stats['pending'] > 0): ?>
    <a class="btn btn-primary" href="<?= e(url('vice_dean/courses')) ?>"><?= icon('exam') ?> Review <?= (int)$stats['pending'] ?> course(s)</a>
  <?php endif; ?>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:22px">
  <?php foreach ([
      ['Departments', $stats['departments'], icon('folder'), 'vice_dean/analytics'],
      ['Teachers', $stats['teachers'], icon('users'), 'vice_dean/analytics'],
      ['Courses', $stats['courses'], icon('courses'), 'vice_dean/courses'],
      ['Pending approval', $stats['pending'], icon('exam'), 'vice_dean/courses'],
  ] as [$label, $val, $ic, $link]): ?>
    <a class="card stat-card" href="<?= e(url($link)) ?>" style="padding:16px 14px;text-decoration:none">
      <div class="stat-icon" style="font-size:22px"><?= $ic ?></div>
      <div>
        <div class="stat-value" style="font-size:1.5rem"><?= (int)$val ?></div>
        <div class="small faint"><?= $label ?></div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="card pad-0">
  <table class="table">
    <thead><tr><th>Course</th><th>Teacher</th><th>Status</th><th>Approved</th></tr></thead>
    <tbody>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td><b><?= e($r['title']) ?></b></td>
          <td class="small"><?= e($r['teacher']) ?></td>
          <td><span class="badge <?= $r['status'] === 'published' ? 'badge-success' : '' ?>"><?= e($r['status']) ?></span></td>
          <td class="tiny faint"><?= $r['approved_at'] ? e($r['approved_at']) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$recent): ?><tr><td colspan="4" class="muted">No courses in your faculty yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
