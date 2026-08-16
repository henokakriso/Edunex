<?php /* Grade detail for one subject — grouped by class level & semester */
$avgOf = function (array $rows): ?float {
    $n = 0; $sum = 0;
    foreach ($rows as $r) { if ($r['max'] > 0) { $sum += $r['score'] / $r['max'] * 100; $n++; } }
    return $n ? round($sum / $n, 1) : null;
};
$letter = function (float $p): string {
    return $p >= 90 ? 'A' : ($p >= 80 ? 'B' : ($p >= 70 ? 'C' : ($p >= 60 ? 'D' : 'F')));
};
?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> <?= e($course['title']) ?></h1>
    <p class="sub"><?= e($course['level'] ?: 'General') ?> · Teacher: <?= e($course['tfirst']) ?> <?= e($course['tlast']) ?></p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('student/grades')) ?>">← All grades</a>
</div>

<?php if (!$groups): ?>
  <div class="empty"><span class="empty-ico"><?= icon('chart-bar') ?></span>No graded results for this subject yet.</div>
<?php endif; ?>

<?php foreach ($groups as $sem => $rows): $avg = $avgOf($rows); ?>
  <div class="card" style="margin-top:16px">
    <h3 class="card-title" style="margin-top:0"><?= icon('calendar') ?> <?= e($sem) ?>
      <?php if ($avg !== null): ?><span class="badge <?= $avg >= 60 ? 'badge-success' : 'badge-danger' ?>">Avg <?= $avg ?>% · <?= $letter($avg) ?></span><?php endif; ?>
    </h3>
    <?php foreach ($rows as $r): $pct = $r['max'] > 0 ? round($r['score'] / $r['max'] * 100) : 0; ?>
      <div class="list-row" style="padding:10px 0">
        <div class="flex-1">
          <b class="small"><?= e($r['title']) ?></b>
          <p class="tiny faint"><?= $r['kind'] === 'exam' ? 'Exam' : 'Assignment' ?> · <?= e(date('M j, Y', strtotime($r['at']))) ?><?= !empty($r['feedback']) ? ' — ' . e($r['feedback']) : '' ?></p>
        </div>
        <div class="flex gap-8" style="align-items:center">
          <?php if ($r['max'] > 0): ?>
            <div class="progress" style="width:90px"><div style="width:<?= $pct ?>%;background:<?= $pct >= 60 || ($r['pass'] && $pct >= $r['pass']) ? 'var(--success)' : 'var(--danger)' ?>"></div></div>
          <?php endif; ?>
          <b class="small"><?= rtrim(rtrim((string)$r['score'], '0'), '.') ?>/<?= rtrim(rtrim((string)$r['max'], '0'), '.') ?></b>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>
