<?php /* Exam result view */
$pct = $attempt['total_points'] > 0 ? round($attempt['score'] / $attempt['total_points'] * 100) : 0;
$passed = $pct >= (float)$exam['passing_score'];
?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> Result — <?= e($exam['title']) ?></h1>
    <p class="sub"><?= e($exam['course_title']) ?> · Submitted <?= e($attempt['submitted_at'] ? date('M j, H:i', strtotime($attempt['submitted_at'])) : '—') ?></p>
  </div>
</div>

<div class="card" style="text-align:center;padding:38px;margin-bottom:22px;<?= $attempt['status'] === 'graded' ? '' : 'opacity:.7' ?>">
  <div style="font-size:52px;margin-bottom:12px"><?= $attempt['status'] === 'graded' ? ($passed ? icon('spark') : icon('bolt')) : icon('loader') ?></div>
  <?php if ($attempt['status'] === 'graded'): ?>
    <h2 style="font-size:2.4rem"><?= rtrim(rtrim((string)$attempt['score'], '0'), '.') ?> / <?= rtrim(rtrim((string)$attempt['total_points'], '0'), '.') ?></h2>
    <p class="muted"><?= $pct ?>% — <?= $passed ? 'Passed' : 'Not passed yet' ?> (passing <?= e($exam['passing_score']) ?>%)</p>
  <?php else: ?>
    <h2>Awaiting grading</h2>
    <p class="muted">Your teacher will grade your essay/coding answers. You'll be notified.</p>
  <?php endif; ?>
  <div class="progress" style="max-width:380px;margin:20px auto"><div style="width:<?= $pct ?>%;background:<?= $passed ? 'var(--success)' : 'var(--warning)' ?>"></div></div>
</div>

<?php if ($attempt['status'] === 'graded' && $exam['show_result']): ?>
<div class="flex-col gap-16">
  <?php foreach ($questions as $i => $q): $a = $answers[$q['id']] ?? null; ?>
    <div class="card" style="border-left:4px solid <?= $a && $a['is_correct'] === 1 ? 'var(--success)' : ($a && $a['is_correct'] === 0 ? 'var(--danger)' : 'var(--warning)') ?>">
      <div class="flex-between">
        <b class="small">Q<?= $i + 1 ?>. <?= e($q['question']) ?></b>
        <span class="badge <?= $a && $a['is_correct'] === 1 ? 'badge-success' : ($a && $a['is_correct'] === 0 ? 'badge-danger' : 'badge-warning') ?>">
          <?= $a && $a['is_correct'] === 1 ? icon('check-circle') . ' Correct' : ($a && $a['is_correct'] === 0 ? icon('x') . ' Incorrect' : ($a ? icon('loader') . ' Pending' : '—')) ?> · <?= rtrim(rtrim((string)($a['points_earned'] ?? 0), '0'), '.') ?>/<?= rtrim(rtrim((string)$q['points'], '0'), '.') ?>
        </span>
      </div>
      <?php if ($a): ?>
        <p class="small" style="margin-top:10px"><b>Your answer:</b> <?= e(is_array(json_decode($a['answer'], true)) ? json_encode(json_decode($a['answer'], true)) : (string)$a['answer']) ?></p>
      <?php endif; ?>
      <?php if ($q['correct_answer'] && $a && $a['is_correct'] === 0): ?>
        <p class="small" style="color:var(--success)"><b>Correct answer:</b> <?= e((string)$q['correct_answer']) ?></p>
      <?php endif; ?>
      <?php if ($q['explanation']): ?><p class="tiny faint" style="margin-top:8px"><?= icon('bulb') ?> <?= e($q['explanation']) ?></p><?php endif; ?>
      <?php if ($a && $a['feedback']): ?><p class="small" style="margin-top:8px"><?= icon('user') ?>‍<?= icon('school') ?> <?= e($a['feedback']) ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
  <div class="alert alert-info">ℹ Results will appear here after your teacher grades the manual questions.</div>
<?php endif; ?>
