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
<!-- Semester Remaining Marks -->
<div class="card" style="margin-bottom:18px">
  <h4 class="card-title" style="margin-top:0">Semester Marks Used</h4>
  <div style="display:flex;gap:16px">
    <div style="flex:1;text-align:center;padding:14px;border-radius:10px;border:1px solid var(--border)">
      <div class="tiny faint">Semester 1</div>
      <div style="font-size:22px;font-weight:800;color:<?= $courseTotalUsed >= 100 ? 'var(--danger)' : 'var(--accent)' ?>"><?= (int)$courseTotalUsed ?>/100</div>
      <div class="tiny faint"><?= max(0, 100 - (int)$courseTotalUsed) ?> remaining</div>
      <div style="height:6px;border-radius:3px;background:var(--border);margin-top:6px;overflow:hidden">
        <div style="height:100%;width:<?= min(100, (int)$courseTotalUsed) ?>%;background:<?= $courseTotalUsed >= 100 ? 'var(--danger)' : 'var(--accent)' ?>;border-radius:3px"></div>
      </div>
    </div>
    <div style="flex:1;text-align:center;padding:14px;border-radius:10px;border:1px solid var(--border)">
      <div class="tiny faint">Semester 2</div>
      <div style="font-size:22px;font-weight:800;color:<?= $courseTotalUsed >= 100 ? 'var(--danger)' : 'var(--accent)' ?>"><?= (int)$courseTotalUsed ?>/100</div>
      <div class="tiny faint"><?= max(0, 100 - (int)$courseTotalUsed) ?> remaining</div>
      <div style="height:6px;border-radius:3px;background:var(--border);margin-top:6px;overflow:hidden">
        <div style="height:100%;width:<?= min(100, (int)$courseTotalUsed) ?>%;background:<?= $courseTotalUsed >= 100 ? 'var(--danger)' : 'var(--accent)' ?>;border-radius:3px"></div>
      </div>
    </div>
  </div>
</div>

<!-- Assessments list -->
<div class="card" style="margin-bottom:18px">
  <h4 class="card-title" style="margin-top:0">Assessments</h4>
  <div style="display:flex;flex-direction:column;gap:6px">
    <?php foreach ($assessments as $a): ?>
      <a href="<?= e(url('teacher/gradebook&id=' . $a['id'])) ?>" style="display:flex;align-items:center;gap:14px;padding:12px 16px;border-radius:10px;border:1px solid var(--border);text-decoration:none;color:var(--text);transition:border-color .15s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
        <span style="font-size:18px"><?= icon(in_array($a['type_slug'], ['r1','r2','r3','r4']) ? 'doc' : ($a['type_slug'] === 'quiz' ? 'spark' : 'file')) ?></span>
        <div style="flex:1;min-width:0">
          <div style="font-weight:600;font-size:13.5px"><?= e($a['title']) ?></div>
          <div class="tiny faint"><?= e($a['type_label'] ?? $a['type_slug']) ?> · Max: <?= (int)$a['max_mark'] ?> · <?= e($a['assessment_date'] ?? '—') ?></div>
        </div>
        <div style="text-align:right">
          <div class="small" style="font-weight:600"><?= (int)$a['graded_count'] ?>/<?= (int)$a['total_grades'] ?> graded</div>
          <?php if ($a['avg_pct']): ?>
            <div class="tiny faint">Avg: <?= e($a['avg_pct']) ?>%</div>
          <?php endif; ?>
        </div>
        <span class="badge <?= $a['result_status'] === 'locked' ? 'badge-muted' : ($a['result_status'] === 'published' ? 'badge-success' : ($a['result_status'] === 'submitted' ? 'badge-info' : 'badge-warning')) ?>"><?= e($a['result_status']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
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
