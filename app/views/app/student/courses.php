<?php /* Student enrolled courses */
?>
<div class="page-head">
  <div>
    <h1><?= icon('graduation') ?> My Courses</h1>
    <p class="sub">Continue where you left off</p>
  </div>
  <a class="btn btn-primary" href="<?= e(url('courses')) ?>">+ Browse catalog</a>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px">
  <?php foreach ($courses as $c): ?>
    <div class="card course-card">
      <?php if ($c['image']): ?><img class="course-cover" src="<?= e(url('file&p=' . urlencode($c['image']))) ?>" alt=""><?php endif; ?>
      <h3 class="small" style="margin:10px 0 4px"><?= e($c['title']) ?></h3>
      <p class="tiny faint"><?= e($c['code']) ?> · by <?= e($c['tfirst'] . ' ' . $c['tlast']) ?> · <?= (int)$c['done_lessons'] ?>/<?= (int)$c['total_lessons'] ?> lessons</p>
      <div class="progress" style="margin:8px 0"><div style="width:<?= (float)$c['progress'] ?>%"></div></div>
      <div class="flex-between">
        <span class="tiny faint"><?= (float)$c['progress'] ?>%</span>
        <?php if ($c['completed']): ?><span class="badge badge-success">✓ completed</span><?php endif; ?>
      </div>
      <a class="btn btn-primary btn-block" style="margin-top:10px" href="<?= e(url('courses/learn&id=' . $c['id'])) ?>">▶ Continue</a>
    </div>
  <?php endforeach; ?>
</div>
<?php if (!$courses): ?>
  <div class="alert alert-info">You're not enrolled in any courses yet. <a class="accent" href="<?= e(url('courses')) ?>">Browse the catalog →</a></div>
<?php endif; ?>
