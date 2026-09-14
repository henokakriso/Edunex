<?php /* Teacher: Course Students — manage enrollments */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('users') ?> Course Students</h1>
    <p class="sub"><?= e($courseTitle ?? '') ?> — enrolled students</p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('teacher/grading&course=' . $selectedCourse)) ?>"><?= icon('arrow-left') ?> Back to Gradebook</a>
</div>

<!-- Enroll form -->
<div class="card" style="margin-bottom:18px">
  <h4 class="card-title" style="margin-top:0">Enroll Student</h4>
  <form method="get" action="" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
    <input type="hidden" name="route" value="teacher/grading/students">
    <input type="hidden" name="course" value="<?= (int)$selectedCourse ?>">
    <div style="flex:1;min-width:240px">
      <label class="small faint" style="display:block;margin-bottom:4px;font-weight:600">Search by name or Student ID</label>
      <input type="text" name="q" class="input" placeholder="e.g. Abel or SCI-2026-001" value="<?= e($searchQuery ?? '') ?>" style="width:100%">
    </div>
    <button type="submit" class="btn btn-primary" style="height:38px"><?= icon('search') ?> Search</button>
  </form>

  <?php if (!empty($searchResults)): ?>
    <div style="margin-top:12px;display:flex;flex-direction:column;gap:6px">
      <?php foreach ($searchResults as $sr):
        $alreadyEnrolled = in_array((int)$sr['id'], $enrolledIds ?? []);
      ?>
        <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid var(--border);border-radius:10px;background:var(--card)">
          <div style="width:36px;height:36px;border-radius:50%;background:color-mix(in srgb, var(--accent) 10%, var(--card));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:var(--accent)"><?= e(mb_substr($sr['first_name'], 0, 1)) ?></div>
          <div style="flex:1">
            <div style="font-weight:600;font-size:13px"><?= e($sr['first_name'] . ' ' . $sr['last_name']) ?></div>
            <div style="font-size:11px;color:var(--text-secondary)"><?= e($sr['student_id'] ?? $sr['email']) ?></div>
          </div>
          <?php if ($alreadyEnrolled): ?>
            <span class="badge badge-success" style="font-size:11px">Enrolled</span>
          <?php else: ?>
            <button type="button" class="btn btn-primary btn-sm" onclick="enrollStudent(<?= (int)$sr['id'] ?>)">Enroll</button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Enrolled students list -->
<div class="card">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
    <h4 class="card-title" style="margin:0">Enrolled Students (<?= count($enrolled) ?>)</h4>
  </div>

  <?php if (empty($enrolled)): ?>
    <div style="text-align:center;padding:30px;color:var(--text-secondary)">No students enrolled yet.</div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:4px">
      <?php foreach ($enrolled as $s): ?>
        <?php
          $f = grading_calc_final((int)$s['id'], $selectedCourse);
          $letter = $f['letter'] ?? null;
          $gradeColor = match($letter) {
            'A+' => '#059669', 'A' => '#10b981', 'B+' => '#3b82f6', 'B' => '#6366f1',
            'C+' => '#f59e0b', 'C' => '#f97316', 'D+' => '#ef4444', 'D' => '#dc2626', 'F' => '#991b1b',
            default => 'var(--text-secondary)'
          };
        ?>
        <div style="display:flex;align-items:center;gap:14px;padding:12px 16px;border:1px solid var(--border);border-radius:10px;transition:background .1s" onmouseover="this.style.background='color-mix(in srgb, var(--accent) 2%, transparent)'" onmouseout="this.style.background=''">
          <div style="width:38px;height:38px;border-radius:50%;background:color-mix(in srgb, <?= $gradeColor ?> 10%, var(--card));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:<?= $gradeColor ?>;flex-shrink:0"><?= e(mb_substr($s['first_name'], 0, 1)) ?></div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:14px"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
            <div style="font-size:11px;color:var(--text-secondary)"><?= e($s['student_id'] ?? '') ?></div>
          </div>
          <div style="text-align:right;min-width:60px">
            <?php if ($letter): ?>
              <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:700;background:color-mix(in srgb, <?= $gradeColor ?> 12%, transparent);color:<?= $gradeColor ?>"><?= e($letter) ?></span>
            <?php else: ?>
              <span style="font-size:12px;color:var(--text-secondary)">—</span>
            <?php endif; ?>
          </div>
          <span class="badge badge-success" style="font-size:11px">Enrolled</span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
const CSRF = '<?= e(csrf_token()) ?>';
const COURSE_ID = <?= (int)$selectedCourse ?>;
const API_URL = '<?= url('api/grading_students') ?>';

function enrollStudent(studentId) {
  fetch(API_URL, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'action=enroll&course_id=' + COURSE_ID + '&student_id=' + studentId + '&_csrf=' + CSRF
  }).then(r => r.json()).then(d => {
    if (d.ok) { location.reload(); }
    else { alert(d.error || 'Failed'); }
  }).catch(() => alert('Network error'));
}
</script>
