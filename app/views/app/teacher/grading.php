<?php /* Teacher Gradebook — main landing with course selector, assessment list, grade entry */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> Gradebook</h1>
    <p class="sub">Manage assessments and enter marks</p>
  </div>
  <?php if ($selectedCourse): ?>
    <div style="display:flex;gap:8px">
      <a class="btn btn-ghost" href="<?= e(url('teacher/assessment/new&course=' . $selectedCourse)) ?>"><?= icon('plus') ?> New Assessment</a>
      <a class="btn btn-ghost" href="<?= e(url('teacher/bonus&course=' . $selectedCourse)) ?>"><?= icon('spark') ?> Bonus</a>
      <a class="btn btn-ghost" href="<?= e(url('teacher/grading/reports')) ?>"><?= icon('file') ?> Reports</a>
    </div>
  <?php endif; ?>
</div>

<!-- Course selector -->
<div class="card" style="margin-bottom:18px">
  <div style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
    <div style="flex:1;min-width:240px">
      <label class="small faint" style="display:block;margin-bottom:6px;font-weight:600">My Courses</label>
      <select class="input" id="course-select" onchange="window.location.href='<?= e(url('teacher/grading')) ?>&course='+this.value" style="width:100%">
        <option value="">— Select Course —</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $selectedCourse == $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?> (<?= (int)$c['students'] ?> students)</option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</div>

<?php if ($selectedCourse && $assessments): ?>
<!-- Student Marks Table -->
<?php if ($students && $assessments): ?>
<?php
  $currentSem = $selectedSem ?: 1;
  $semAssessments = array_filter($assessments, fn($a) => (int)($a['semester'] ?? 0) === $currentSem);
  $gradeUrl = url('teacher/grading&course=' . $selectedCourse);
?>
<div class="card" style="margin-bottom:18px;overflow-x:auto">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap">
    <h4 class="card-title" style="margin:0">Student Marks</h4>
    <div style="display:flex;gap:4px;margin-left:auto">
      <a href="<?= e($gradeUrl . '&sem=1') ?>" class="btn btn-sm <?= $currentSem === 1 ? 'btn-primary' : 'btn-ghost' ?>">Semester 1</a>
      <a href="<?= e($gradeUrl . '&sem=2') ?>" class="btn btn-sm <?= $currentSem === 2 ? 'btn-primary' : 'btn-ghost' ?>">Semester 2</a>
      <a href="<?= e($gradeUrl) ?>" class="btn btn-sm <?= !$selectedSem ? 'btn-primary' : 'btn-ghost' ?>">All</a>
    </div>
  </div>
  <table class="table" style="margin:0;white-space:nowrap">
    <thead>
      <tr>
        <th style="position:sticky;left:0;background:var(--card);z-index:1">#</th>
        <th style="position:sticky;left:40px;background:var(--card);z-index:1;min-width:160px">Student</th>
        <?php foreach ($semAssessments as $a): ?>
          <th style="text-align:center;font-size:11px;max-width:80px">
            <div><?= e($a['title']) ?></div>
            <div class="tiny faint"><?= e($a['type_label'] ?? $a['type_slug']) ?> · /<?= (int)$a['max_mark'] ?></div>
          </th>
        <?php endforeach; ?>
        <th style="text-align:center;font-size:11px;background:color-mix(in srgb, var(--accent) 6%, var(--card))">Total</th>
        <?php if (!$selectedSem): ?>
          <th style="text-align:center;font-size:11px;background:color-mix(in srgb, var(--accent) 6%, var(--card))">S1</th>
          <th style="text-align:center;font-size:11px;background:color-mix(in srgb, var(--accent) 6%, var(--card))">S2</th>
        <?php endif; ?>
        <th style="text-align:center;font-size:11px;font-weight:700">Final</th>
        <th style="text-align:center;font-size:11px">Grade</th>
      </tr>
    </thead>
    <tbody>
      <?php $rank = 0; ?>
      <?php foreach ($students as $s): ?>
        <?php
          $f = grading_calc_final((int)$s['id'], $selectedCourse);
          $rank++;
        ?>
        <tr>
          <td style="position:sticky;left:0;background:var(--card);z-index:1"><?= $rank ?></td>
          <td style="position:sticky;left:40px;background:var(--card);z-index:1">
            <div style="display:flex;align-items:center;gap:8px">
              <div class="avatar" style="width:28px;height:28px;font-size:.65rem;flex-shrink:0"><?= e(mb_substr($s['last_name'], 0, 1)) ?></div>
              <div>
                <div class="small" style="font-weight:600"><?= e($s['last_name'] . ', ' . $s['first_name']) ?></div>
                <div class="tiny faint"><?= e($s['sid'] ?? '') ?></div>
              </div>
            </div>
          </td>
          <?php foreach ($semAssessments as $a): ?>
            <?php
              $grade = Database::one("SELECT mark FROM grades WHERE assessment_id = ? AND student_id = ?", [(int)$a['id'], (int)$s['id']]);
              $mark = $grade ? (float)$grade['mark'] : null;
              $max = (float)$a['max_mark'];
              $pct = ($mark !== null && $max > 0) ? ($mark / $max * 100) : null;
            ?>
            <td style="text-align:center">
              <?php if ($mark !== null): ?>
                <span style="font-weight:600;<?= $pct >= 50 ? 'color:var(--success)' : 'color:var(--danger)' ?>"><?= e((string)$mark) ?></span>
              <?php else: ?>
                <span class="tiny faint">—</span>
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
          <td style="text-align:center;background:color-mix(in srgb, var(--accent) 6%, var(--card));font-weight:700">
            <?= $currentSem === 1 ? ($f['total1'] !== null ? e($f['total1']) . '%' : '—') : ($f['total2'] !== null ? e($f['total2']) . '%' : '—') ?>
          </td>
          <?php if (!$selectedSem): ?>
            <td style="text-align:center;background:color-mix(in srgb, var(--accent) 3%, transparent);font-weight:600">
              <?= $f['total1'] !== null ? e($f['total1']) . '%' : '—' ?>
            </td>
            <td style="text-align:center;background:color-mix(in srgb, var(--accent) 3%, transparent);font-weight:600">
              <?= $f['total2'] !== null ? e($f['total2']) . '%' : '—' ?>
            </td>
          <?php endif; ?>
          <td style="text-align:center;font-weight:700;font-size:14px">
            <?= $f['adjusted'] !== null ? e($f['adjusted']) : '—' ?>
          </td>
          <td style="text-align:center">
            <?php if ($f['letter']): ?>
              <span class="badge <?= $f['pass'] ? 'badge-success' : 'badge-danger' ?>" style="font-size:11px"><?= e($f['letter']) ?></span>
            <?php else: ?>
              <span class="tiny faint">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- Summary stats -->
<?php if ($finalStats && !empty($finalStats['students'])): ?>
<div class="card">
  <h4 class="card-title" style="margin-top:0">Class Summary</h4>
  <div style="display:flex;gap:12px;flex-wrap:wrap">
    <div style="text-align:center;padding:12px 20px;border-radius:10px;border:1px solid var(--border);flex:1;min-width:120px">
      <div style="font-size:20px;font-weight:800;color:var(--accent)"><?= e($finalStats['avg'] ?? '—') ?></div>
      <div class="tiny faint">Average</div>
    </div>
    <div style="text-align:center;padding:12px 20px;border-radius:10px;border:1px solid var(--border);flex:1;min-width:120px">
      <div style="font-size:20px;font-weight:800;color:var(--success)"><?= e($finalStats['pass_rate'] ?? '—') ?>%</div>
      <div class="tiny faint">Pass Rate</div>
    </div>
    <div style="text-align:center;padding:12px 20px;border-radius:10px;border:1px solid var(--border);flex:1;min-width:120px">
      <div style="font-size:20px;font-weight:800;color:var(--info)"><?= count($students) ?></div>
      <div class="tiny faint">Students</div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php elseif ($selectedCourse && empty($assessments)): ?>
<div class="card" style="text-align:center;padding:40px">
  <div style="font-size:28px;margin-bottom:10px"><?= icon('note') ?></div>
  <p class="small" style="color:var(--muted)">No assessments yet for this course.</p>
  <a class="btn btn-primary" href="<?= e(url('teacher/assessment/new&course=' . $selectedCourse)) ?>" style="margin-top:12px"><?= icon('plus') ?> Create First Assessment</a>
</div>
<?php elseif (!$selectedCourse && $courses): ?>
<div class="card" style="text-align:center;padding:40px">
  <div style="font-size:28px;margin-bottom:10px"><?= icon('graduation') ?></div>
  <p class="small" style="color:var(--muted)">Select a course above to view its gradebook.</p>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:40px">
  <div style="font-size:28px;margin-bottom:10px"><?= icon('graduation') ?></div>
  <p class="small" style="color:var(--muted)">No courses assigned to you yet.</p>
</div>
<?php endif; ?>

<!-- Edit Max Mark Modal -->
<div id="edit-max-modal" style="position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.4);display:none;align-items:center;justify-content:center" onclick="if(event.target===this)closeMaxModal()">
  <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:24px;width:340px;max-width:90vw">
    <h4 style="margin:0 0 12px;font-size:15px">Edit Max Mark</h4>
    <input type="number" id="new-max-mark" min="1" max="100" class="input" style="width:100%;margin-bottom:6px">
    <div id="max-mark-remaining" class="tiny faint" style="margin-bottom:12px"></div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeMaxModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveMaxMark()">Save</button>
    </div>
  </div>
</div>

<script>
let _editAssessId = null;
let _editSemester = 0;
let _editCourseId = <?= (int)($selectedCourse ?? 0) ?>;

function editMaxMark(id, current) {
  _editAssessId = id;
  document.getElementById('new-max-mark').value = current;
  document.getElementById('edit-max-modal').style.display = 'flex';
  // Get semester info
  const m = document.querySelector('[data-assess-id="'+id+'"]');
  document.getElementById('new-max-mark').focus();
}

function closeMaxModal() {
  document.getElementById('edit-max-modal').style.display = 'none';
  _editAssessId = null;
}

function saveMaxMark() {
  const val = parseInt(document.getElementById('new-max-mark').value);
  if (!val || val < 1 || val > 100) { alert('Must be 1-100'); return; }
  fetch('<?= url('api/grading_max_mark') ?>', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'assessment_id=' + _editAssessId + '&max_mark=' + val + '&_csrf=<?= e(csrf_token()) ?>'
  }).then(r => r.json()).then(d => {
    if (d.ok) {
      document.getElementById('max-mark-' + _editAssessId).textContent = val;
      closeMaxModal();
      if (d.warning) alert(d.warning);
      location.reload();
    } else {
      alert(d.error || 'Failed');
    }
  }).catch(() => alert('Network error'));
}
</script>
