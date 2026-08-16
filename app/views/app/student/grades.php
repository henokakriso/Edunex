<?php /* Student grades view — grouped by grade/level and semester */
$group = function (array $rows): array {
    $out = [];
    foreach ($rows as $r) {
        $level = $r['course_level'] ?: 'General';
        $sem = $r['semester'] ?? 'Other';
        $out[$level][$sem][] = $r;
    }
    ksort($out);
    return $out;
};
$examsBy = $group($exams);
$assignsBy = $group($assigns);
?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> My Grades</h1>
    <p class="sub">Exam scores, assignment results and course progress — grouped by grade level and semester</p>
  </div>
</div>

<?php foreach ($examsBy as $level => $sems): foreach ($sems as $sem => $rows): ?>
  <div class="card" style="margin-top:18px">
    <h3 class="card-title" style="margin-top:0"><?= icon('note') ?> <?= e($level) ?> · <?= e($sem) ?> <span class="badge badge-muted">Exams</span></h3>
    <?php foreach ($rows as $ex): $pct = $ex['total_points'] > 0 ? round($ex['score'] / $ex['total_points'] * 100) : 0; ?>
      <a class="list-row" href="<?= e(url('student/grades/subject&id=' . (int)$ex['course_id'])) ?>" style="padding:10px 0;color:inherit;text-decoration:none;border-radius:8px;display:flex;align-items:center;gap:10px" onmouseover="this.style.background='color-mix(in srgb,var(--accent) 6%,transparent)'" onmouseout="this.style.background='transparent'">
        <div class="flex-1">
          <b class="small"><?= e($ex['title']) ?></b>
          <p class="tiny faint"><?= e($ex['course_title']) ?> · <?= e(date('M j, Y', strtotime($ex['submitted_at']))) ?></p>
        </div>
        <div class="flex gap-8" style="align-items:center">
          <div class="progress" style="width:90px"><div style="width:<?= $pct ?>%;background:<?= $pct >= (float)$ex['passing_score'] ? 'var(--success)' : 'var(--danger)' ?>"></div></div>
          <b class="small"><?= rtrim(rtrim((string)$ex['score'], '0'), '.') ?>/<?= rtrim(rtrim((string)$ex['total_points'], '0'), '.') ?></b>
          <span class="tiny faint" title="View subject history"><?= icon('arrow-right') ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endforeach; endforeach; ?>
<?php if (!$exams): ?><div class="card"><h3 class="card-title" style="margin-top:0"><?= icon('note') ?> Exam results</h3><p class="muted small">No graded exams yet.</p></div><?php endif; ?>

<?php foreach ($assignsBy as $level => $sems): foreach ($sems as $sem => $rows): ?>
  <div class="card" style="margin-top:18px">
    <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> <?= e($level) ?> · <?= e($sem) ?> <span class="badge badge-muted">Assignments</span></h3>
    <?php foreach ($rows as $a): ?>
      <a class="list-row" href="<?= e(url('student/grades/subject&id=' . (int)$a['course_id'])) ?>" style="padding:10px 0;color:inherit;text-decoration:none;border-radius:8px;display:flex;align-items:center;gap:10px" onmouseover="this.style.background='color-mix(in srgb,var(--accent) 6%,transparent)'" onmouseout="this.style.background='transparent'">
        <div class="flex-1">
          <b class="small"><?= e($a['title']) ?></b>
          <p class="tiny faint"><?= e($a['course_title']) ?><?= $a['feedback'] ? ' — ' . e($a['feedback']) : '' ?></p>
        </div>
        <b class="small"><?= rtrim(rtrim((string)$a['score'], '0'), '.') ?>/<?= rtrim(rtrim((string)$a['max_score'], '0'), '.') ?></b>
        <span class="tiny faint"><?= icon('arrow-right') ?></span>
      </a>
    <?php endforeach; ?>
  </div>
<?php endforeach; endforeach; ?>
<?php if (!$assigns): ?><div class="card" style="margin-top:18px"><h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Assignment grades</h3><p class="muted small">No graded assignments yet.</p></div><?php endif; ?>

<div class="card" style="margin-top:22px">
  <h3 class="card-title"><?= icon('books') ?> Course progress</h3>
  <?php foreach ($courses as $c): $pct = $c['total'] ? round($c['done'] / $c['total'] * 100) : 0; ?>
    <div class="list-row" style="padding:10px 0">
      <div class="flex-1"><b class="small"><?= e($c['title']) ?></b></div>
      <div class="flex gap-8" style="align-items:center">
        <div class="progress" style="width:160px"><div style="width:<?= (float)$c['progress'] ?>%"></div></div>
        <span class="small faint"><?= (float)$c['progress'] ?>%</span>
      </div>
    </div>
  <?php endforeach; ?>
</div>
