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
<!-- Semester boxes -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
  <?php for ($sem = 1; $sem <= 2; $sem++): ?>
    <?php
      $semAssessments = array_filter($assessments, fn($a) => (int)($a['semester'] ?? 0) === $sem);
      $used = (int)($semesterUsedMarks[$sem] ?? 0);
      $remaining = max(0, 100 - $used);
      $semAvg = $semesterStats[$sem]['avg'] ?? null;
      $semStudents = $semesterStats[$sem]['count'] ?? 0;
    ?>
    <div class="card" style="padding:0;overflow:hidden">
      <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div>
          <h4 class="card-title" style="margin:0;font-size:16px">Semester <?= $sem ?></h4>
          <div class="tiny faint" style="margin-top:2px"><?= $semStudents ?> students with grades</div>
        </div>
        <div style="text-align:right">
          <?php if ($semAvg !== null): ?>
            <div style="font-size:20px;font-weight:800;color:<?= $semAvg >= 50 ? 'var(--success)' : 'var(--danger)' ?>"><?= e($semAvg) ?>%</div>
            <div class="tiny faint">Average</div>
          <?php else: ?>
            <div class="tiny faint">No grades yet</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Marks budget bar -->
      <div style="padding:12px 20px;background:color-mix(in srgb, var(--accent) 3%, transparent)">
        <div style="display:flex;align-items:center;gap:10px">
          <span class="tiny faint" style="font-weight:600">Exam budget:</span>
          <div style="flex:1;height:6px;border-radius:3px;background:var(--border);overflow:hidden">
            <div style="height:100%;width:<?= min(100, $used) ?>%;background:<?= $used >= 100 ? 'var(--danger)' : 'var(--accent)' ?>;border-radius:3px"></div>
          </div>
          <span class="tiny" style="font-weight:600;color:<?= $used >= 100 ? 'var(--danger)' : 'var(--accent)' ?>"><?= $used ?>/100</span>
          <span class="tiny faint"><?= $remaining ?> left</span>
        </div>
      </div>

      <!-- Assessments -->
      <div style="padding:8px 12px">
        <?php if (empty($semAssessments)): ?>
          <div style="text-align:center;padding:20px" class="tiny faint">No assessments yet</div>
        <?php else: ?>
          <?php foreach ($semAssessments as $a): ?>
            <a href="<?= e(url('teacher/gradebook&id=' . $a['id'])) ?>" style="display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;text-decoration:none;color:var(--text);transition:background .15s;border:1px solid transparent" onmouseover="this.style.background='color-mix(in srgb, var(--accent) 4%, transparent)';this.style.borderColor='var(--border)'" onmouseout="this.style.background='';this.style.borderColor='transparent'">
              <span style="font-size:16px;color:var(--accent)"><?= icon(in_array($a['type_slug'], ['r1','r2','r3','r4']) ? 'doc' : ($a['type_slug'] === 'quiz' ? 'spark' : 'file')) ?></span>
              <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:13px"><?= e($a['title']) ?></div>
                <div class="tiny faint"><?= e($a['type_label'] ?? $a['type_slug']) ?> · Max: <span id="max-mark-<?= (int)$a['id'] ?>"><?= (int)$a['max_mark'] ?></span></div>
              </div>
              <div style="text-align:right">
                <div class="small" style="font-weight:600"><?= (int)$a['graded_count'] ?>/<?= (int)$a['total_grades'] ?></div>
                <?php if ($a['avg_pct']): ?>
                  <div class="tiny" style="color:<?= $a['avg_pct'] >= 50 ? 'var(--success)' : 'var(--danger)' ?>"><?= e($a['avg_pct']) ?>%</div>
                <?php endif; ?>
              </div>
              <button type="button" onclick="event.preventDefault();event.stopPropagation();editMaxMark(<?= (int)$a['id'] ?>, <?= (int)$a['max_mark'] ?>)" style="padding:3px 8px;font-size:11px;font-weight:600;color:var(--accent);background:color-mix(in srgb, var(--accent) 8%, transparent);border:1px solid color-mix(in srgb, var(--accent) 25%, transparent);border-radius:6px;cursor:pointer;white-space:nowrap">✏</button>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Add assessment button -->
      <div style="padding:8px 12px 12px;border-top:1px solid var(--border)">
        <a class="btn btn-ghost btn-sm" href="<?= e(url('teacher/assessment/new&course=' . $selectedCourse . '&semester=' . $sem)) ?>" style="width:100%;justify-content:center">+ Add Assessment</a>
      </div>
    </div>
  <?php endfor; ?>
</div>

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
