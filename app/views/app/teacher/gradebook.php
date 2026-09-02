<?php /* Gradebook — mark entry for a specific assessment */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> <?= e($assessment['title']) ?></h1>
    <p class="sub"><?= e($assessment['type_label'] ?? $assessment['type_slug']) ?> · <?= e($assessment['course_title']) ?> · Max: <?= (int)$assessment['max_mark'] ?></p>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn btn-ghost" href="<?= e(url('teacher/grading&course=' . $assessment['cid'])) ?>">← Back to Gradebook</a>
  </div>
</div>

<!-- Semester remaining info -->
<div class="card" style="margin-bottom:14px;padding:12px 18px">
  <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <span class="small" style="font-weight:600">Semester <?= e($assessment['semester'] ?? '—') ?>:</span>
    <span class="small"><?= (int)$semesterUsed ?>/100 marks used</span>
    <div style="flex:1;height:6px;border-radius:3px;background:var(--border);overflow:hidden;min-width:100px">
      <div style="height:100%;width:<?= min(100, (int)$semesterUsed) ?>%;background:var(--accent);border-radius:3px"></div>
    </div>
    <span class="small" style="font-weight:600;color:<?= $semesterRemaining > 0 ? 'var(--success)' : 'var(--danger)' ?>"><?= (int)$semesterRemaining ?> remaining</span>
  </div>
</div>

<!-- Result status -->
<div class="card" style="margin-bottom:14px;padding:12px 18px">
  <div style="display:flex;align-items:center;gap:12px">
    <span class="small faint">Status:</span>
    <span class="badge <?= $assessment['result_status'] === 'locked' ? 'badge-muted' : ($assessment['result_status'] === 'published' ? 'badge-success' : ($assessment['result_status'] === 'submitted' ? 'badge-info' : 'badge-warning')) ?>"><?= e(ucfirst($assessment['result_status'])) ?></span>
    <?php if ($assessment['result_status'] === 'draft'): ?>
      <span class="tiny faint">· Enter marks below, then save or submit</span>
    <?php elseif ($assessment['result_status'] === 'submitted'): ?>
      <span class="tiny faint">· Submitted — awaiting verification</span>
    <?php elseif ($assessment['result_status'] === 'locked'): ?>
      <span class="tiny faint">· Locked — no changes allowed</span>
    <?php endif; ?>
  </div>
</div>

<!-- Grade entry form -->
<form method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save_grades">

  <div class="card" style="overflow-x:auto">
    <table class="table" style="margin:0">
      <thead>
        <tr>
          <th style="width:40px;text-align:center">#</th>
          <th style="width:220px">Student</th>
          <th style="width:120px">Student ID</th>
          <th style="width:120px;text-align:center">Mark (out of <?= (int)$assessment['max_mark'] ?>)</th>
          <th style="width:90px;text-align:center">Percentage</th>
          <th style="width:70px;text-align:center">Grade</th>
          <th style="width:90px">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 0; foreach ($students as $s): $i++; ?>
          <tr>
            <td style="text-align:center;color:var(--text-faint)"><?= $i ?></td>
            <td><b class="small"><?= e($s['last_name'] . ', ' . $s['first_name']) ?></b></td>
            <td class="small" style="font-family:ui-monospace,monospace;color:var(--text-dim)"><?= e($s['sid'] ?? '—') ?></td>
            <td style="text-align:center">
              <?php if ($assessment['result_status'] === 'locked'): ?>
                <span class="small" style="font-weight:600"><?= $s['mark'] !== null ? e($s['mark']) : '—' ?></span>
              <?php else: ?>
                <input type="number" step="0.1" min="0" max="<?= (int)$assessment['max_mark'] ?>"
                       name="marks[<?= (int)$s['id'] ?>]"
                       value="<?= $s['mark'] !== null ? e($s['mark']) : '' ?>"
                       class="input" style="width:90px;text-align:center;margin:0 auto;display:block"
                       oninput="calcPct(this, <?= (int)$assessment['max_mark'] ?>)">
              <?php endif; ?>
            </td>
            <td style="text-align:center">
              <span class="pct-display small" data-student="<?= (int)$s['id'] ?>" style="font-weight:600">
                <?= $s['percentage'] !== null ? e($s['percentage']) . '%' : '—' ?>
              </span>
            </td>
            <td style="text-align:center">
              <span class="grade-display" data-student="<?= (int)$s['id'] ?>" style="font-weight:700;color:<?= ($s['percentage'] ?? 0) >= 50 ? 'var(--success)' : 'var(--danger)' ?>">
                <?= e($s['letter_grade'] ?? '—') ?>
              </span>
            </td>
            <td>
              <span class="badge <?= ($s['grade_status'] ?? 'draft') === 'locked' ? 'badge-muted' : (($s['grade_status'] ?? 'draft') === 'submitted' ? 'badge-info' : 'badge-warning') ?>"><?= e(ucfirst($s['grade_status'] ?? 'draft')) ?></span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Actions -->
  <?php if ($assessment['result_status'] !== 'locked'): ?>
  <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end">
    <button type="submit" class="btn btn-primary"><?= icon('check') ?> Save Grades</button>
    <?php if ($assessment['result_status'] === 'draft'): ?>
      <button type="submit" class="btn btn-ghost" name="action" value="submit_grades" onclick="return confirm('Submit all grades for verification? You won\'t be able to edit after submitting.')">Submit for Verification</button>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</form>

<!-- Summary stats -->
<div class="card" style="margin-top:18px">
  <h4 class="card-title" style="margin-top:0">Assessment Statistics</h4>
  <div style="display:flex;gap:16px;flex-wrap:wrap">
    <?php
    $marks = array_filter(array_column($students, 'mark'), fn($m) => $m !== null);
    $pcts = array_filter(array_column($students, 'percentage'), fn($p) => $p !== null);
    ?>
    <div style="text-align:center;padding:12px 20px;border-radius:10px;border:1px solid var(--border);flex:1;min-width:100px">
      <div style="font-size:18px;font-weight:800;color:var(--accent)"><?= count($marks) ?>/<?= count($students) ?></div>
      <div class="tiny faint">Graded</div>
    </div>
    <?php if ($pcts): ?>
    <div style="text-align:center;padding:12px 20px;border-radius:10px;border:1px solid var(--border);flex:1;min-width:100px">
      <div style="font-size:18px;font-weight:800;color:var(--info)"><?= round(array_sum($pcts) / count($pcts), 1) ?>%</div>
      <div class="tiny faint">Average</div>
    </div>
    <div style="text-align:center;padding:12px 20px;border-radius:10px;border:1px solid var(--border);flex:1;min-width:100px">
      <div style="font-size:18px;font-weight:800;color:var(--success)"><?= max($pcts) ?>%</div>
      <div class="tiny faint">Highest</div>
    </div>
    <div style="text-align:center;padding:12px 20px;border-radius:10px;border:1px solid var(--border);flex:1;min-width:100px">
      <div style="font-size:18px;font-weight:800;color:var(--danger)"><?= min($pcts) ?>%</div>
      <div class="tiny faint">Lowest</div>
    </div>
    <div style="text-align:center;padding:12px 20px;border-radius:10px;border:1px solid var(--border);flex:1;min-width:100px">
      <div style="font-size:18px;font-weight:800;color:var(--success)"><?= round(count(array_filter($pcts, fn($p) => $p >= 50)) / count($pcts) * 100, 1) ?>%</div>
      <div class="tiny faint">Pass Rate</div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function calcPct(input, maxMark) {
  const row = input.closest('tr');
  const val = parseFloat(input.value);
  const pctEl = row.querySelector('.pct-display');
  const gradeEl = row.querySelector('.grade-display');
  if (isNaN(val) || val < 0 || input.value === '') {
    pctEl.textContent = '—';
    gradeEl.textContent = '—';
    gradeEl.style.color = '';
    return;
  }
  const pct = Math.round((val / maxMark) * 10000) / 100;
  pctEl.textContent = pct + '%';
  let letter = 'F';
  if (pct >= 90) letter = 'A+';
  else if (pct >= 80) letter = 'A';
  else if (pct >= 75) letter = 'B+';
  else if (pct >= 70) letter = 'B';
  else if (pct >= 65) letter = 'C+';
  else if (pct >= 60) letter = 'C';
  else if (pct >= 55) letter = 'D+';
  else if (pct >= 50) letter = 'D';
  gradeEl.textContent = letter;
  gradeEl.style.color = pct >= 50 ? 'var(--success)' : 'var(--danger)';
}
</script>
