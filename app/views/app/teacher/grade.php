<?php /* Teacher grading view */
$gradeMode = (int)($mode['manual'] ?? 0);
?>
<div class="page-head">
  <div>
    <h1><?= icon('check-circle') ?> Grade — <?= e($exam['title']) ?></h1>
    <p class="sub"><?= e($exam['course_title']) ?> · <?= count($attempts) ?> submissions
      <?php if (!empty($exam['results_sent_at'])): ?>· <span class="badge badge-success" style="margin-left:4px"><?= icon('send') ?> Results sent <?= e(date('M j, H:i', strtotime($exam['results_sent_at']))) ?></span><?php endif; ?>
    </p>
  </div>
  <div class="flex gap-8" style="flex-wrap:wrap">
    <form method="post" class="inline" data-confirm="Send the final results to every graded student and to their homeroom teachers? Pending manual attempts will be finalised with their current points.">
      <?= csrf_field() ?>
      <input type="hidden" name="send_results" value="1">
      <button class="btn btn-primary"><?= icon('send') ?> Send results</button>
    </form>
    <a class="btn btn-ghost" href="<?= e(url('teacher/exams')) ?>">← Back</a>
  </div>
</div>

<?php if (!empty($exam['results_sent_at'])): ?>
  <div class="alert alert-info" style="margin-bottom:18px">
    <?= icon('send') ?> Results were sent on <?= e(date('M j, Y \a\t H:i', strtotime($exam['results_sent_at']))) ?> — each student received their score, and their homeroom teacher received a class summary. Sending again updates everyone with the latest grades.
  </div>
<?php endif; ?>

<?php foreach ($attempts as $att): $st = json_decode($att['answers'] ?: '{}', true); ?>
  <div class="card" style="margin-bottom:18px">
    <div class="flex-between" style="flex-wrap:wrap;gap:10px;margin-bottom:14px">
      <div>
        <b><?= icon('user') ?> <?= e($att['student_name']) ?></b>
        <span class="badge <?= $att['status'] === 'graded' ? 'badge-success' : 'badge-warning' ?>"><?= $att['status'] === 'graded' ? 'Graded' : 'Pending' ?></span>
        <span class="badge badge-muted"><?= e($att['student_id'] ?? '') ?></span>
        <p class="tiny faint" style="margin-top:4px">Submitted <?= e(date('M j, H:i', strtotime($att['submitted_at']))) ?> · <?= rtrim(rtrim((string)($att['score'] ?? 0), '0'), '.') ?> pts</p>
      </div>
      <div class="flex gap-8">
        <?php if (!$gradeMode): ?>
          <button class="btn btn-sm" onclick="document.getElementById('auto-<?= (int)$att['id'] ?>').style.display='block';this.style.display='none'"><?= icon('robot') ?> Auto-grade all</button>
        <?php endif; ?>
        <button class="btn btn-sm btn-primary" onclick="document.getElementById('grade-<?= (int)$att['id'] ?>').scrollIntoView()"><?= icon('edit') ?> Grade now</button>
      </div>
    </div>

    <div class="flex-col gap-10" id="auto-<?= (int)$att['id'] ?>" style="display:none">
      <?php foreach ($att['gradable'] as $q): ?>
        <?php $prev = $st[$q['id']] ?? ''; ?>
        <?php if (strlen(trim((string)$prev)) > 0): ?>
          <div class="flex-between" style="background:var(--bg);padding:10px;border-radius:10px">
            <div class="small"><b><?= e(mb_strimwidth((string)$q['question'], 0, 80, '…')) ?></b><br><span class="faint"><?= e(mb_strimwidth((string)$prev, 0, 160, '…')) ?></span></div>
            <div class="flex gap-8">
              <input type="number" class="input" style="width:80px" value="<?= rtrim(rtrim((string)$q['points'], '0'), '.') ?>" max="<?= (float)$q['points'] ?>" step="0.5" id="auto-pts-<?= (int)$att['id'] ?>-<?= (int)$q['id'] ?>">
              <span class="small faint">/ <?= rtrim(rtrim((string)$q['points'], '0'), '.') ?></span>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
      <form method="post" class="flex gap-8">
        <?= csrf_field() ?>
        <input type="hidden" name="auto_grade" value="<?= (int)$att['id'] ?>">
        <button class="btn btn-success">Auto-grade (rule-based on keywords)</button>
      </form>
    </div>

    <form method="post" class="flex-col gap-12" id="grade-<?= (int)$att['id'] ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="grade_attempt" value="<?= (int)$att['id'] ?>">
      <?php foreach ($att['gradable'] as $q): $prev = $st[$q['id']] ?? ''; ?>
        <div style="border-top:1px solid var(--border);padding-top:12px">
          <b class="small">Q. <?= e($q['question']) ?></b>
          <?php if ($q['type'] === 'coding'): ?>
            <pre class="codeblock"><?= e((string)$prev) ?></pre>
          <?php else: ?>
            <p class="small" style="margin:8px 0"><?= e((string)$prev) ?: '<span class="faint">(no answer)</span>' ?></p>
          <?php endif; ?>
          <div class="flex gap-8" style="align-items:end">
            <div class="flex-col">
              <label class="tiny faint">Points (max <?= rtrim(rtrim((string)$q['points'], '0'), '.') ?>)</label>
              <input class="input" style="width:100px" type="number" step="0.5" min="0" max="<?= (float)$q['points'] ?>" name="pts_<?= (int)$q['id'] ?>" value="<?= isset($st[$q['id']]) ? '' : '0' ?>">
            </div>
            <div class="flex-col flex-1">
              <label class="tiny faint">Feedback</label>
              <input class="input" name="fb_<?= (int)$q['id'] ?>" placeholder="Feedback for the student…">
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if ($att['gradable']): ?>
        <button class="btn btn-primary" style="align-self:flex-start"><?= icon('save') ?> Save grades</button>
      <?php else: ?>
        <p class="tiny faint">All questions in this attempt were auto-graded.</p>
      <?php endif; ?>
    </form>
  </div>
<?php endforeach; ?>
<?php if (!$attempts): ?><div class="alert alert-info">No submissions yet.</div><?php endif; ?>
