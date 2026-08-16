<?php /* Dept head dashboard */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('folder') ?> <?= e($dept['name']) ?></h1>
    <p class="sub">Department overview</p>
  </div>
  <?php if ($stats['theses'] > 0): ?>
    <a class="btn btn-primary" href="<?= e(url('dept_head/theses')) ?>"><?= icon('book') ?> Review <?= (int)$stats['theses'] ?> thesis(es)</a>
  <?php endif; ?>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px">
  <?php foreach ([
      ['Courses', $stats['courses'], icon('courses'), 'dept_head/courses'],
      ['Teachers', $stats['teachers'], icon('users'), 'dept_head/courses'],
      ['Students', $stats['students'], icon('graduation'), 'dept_head/analytics'],
      ['Pending theses', $stats['theses'], icon('book'), 'dept_head/theses'],
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
