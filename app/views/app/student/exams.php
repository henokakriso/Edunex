<?php /* Student exams list view */
$now = time();
?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> My Exams</h1>
    <p class="sub">Upcoming and available exams for your courses</p>
  </div>
</div>

<div class="flex-col gap-16">
  <?php foreach ($exams as $ex): $starts = strtotime($ex['start_time']); $ends = strtotime($ex['end_time']); ?>
    <div class="card">
      <div class="flex-between" style="flex-wrap:wrap;gap:12px">
        <div>
          <b><?= e($ex['title']) ?></b>
          <span class="badge badge-accent"><?= e($ex['course_title']) ?></span>
          <p class="tiny faint" style="margin-top:4px">
            <?= (int)$ex['duration_min'] ?> min · <?= (int)$ex['question_count'] ?? '' ?> questions · Passing <?= e($ex['passing_score']) ?>%
            · <?= $starts > $now ? 'Starts ' . date('M j, H:i', $starts) : 'Open until ' . date('M j, H:i', $ends) ?>
          </p>
        </div>
        <div class="flex gap-8" style="align-items:center">
          <?php if ($ex['attempt_status'] === 'graded'): ?>
            <span class="badge badge-success">Score: <?= rtrim(rtrim((string)$ex['score'], '0'), '.') ?>/<?= rtrim(rtrim((string)$ex['total_points'], '0'), '.') ?></span>
            <a class="btn btn-sm" href="<?= e(url('exams/result&a=' . $ex['attempt_id'])) ?>">View result</a>
          <?php elseif ($ex['attempt_status'] === 'submitted'): ?>
            <span class="badge badge-warning"><?= icon('loader') ?> Awaiting grade</span>
            <a class="btn btn-sm" href="<?= e(url('exams/result&a=' . $ex['attempt_id'])) ?>">View</a>
          <?php elseif ($ex['attempt_status'] === 'in_progress'): ?>
            <a class="btn btn-primary" href="<?= e(url('exams/take&e=' . $ex['id'])) ?>"><?= icon('clock') ?> Resume exam</a>
          <?php else: ?>
            <?php if ($ex['open']): ?>
              <a class="btn btn-primary" href="<?= e(url('exams/take&e=' . $ex['id'])) ?>"><?= icon('rocket') ?> Start exam</a>
            <?php else: ?>
              <span class="badge badge-muted">Not open yet</span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$exams): ?><div class="alert alert-info">No exams available right now. Check back after your teacher publishes one.</div><?php endif; ?>
</div>
