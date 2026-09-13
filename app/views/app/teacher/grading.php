<?php /* Teacher Gradebook */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> Gradebook</h1>
    <p class="sub">Manage assessments and enter marks</p>
  </div>
  <?php if ($selectedCourse): ?>
    <div style="display:flex;gap:8px">
      <a class="btn btn-ghost" href="<?= e(url('teacher/assessment/new&course=' . $selectedCourse)) ?>"><?= icon('plus') ?> New Assessment</a>
      <a class="btn btn-ghost" href="<?= e(url('teacher/bonus&course=' . $selectedCourse)) ?>"><?= icon('spark') ?> Bonus</a>
      <a class="btn btn-ghost" href="<?= e(url('teacher/grading/students&course=' . $selectedCourse)) ?>"><?= icon('users') ?> Students</a>
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
<?php
  $currentSem = $selectedSem ?: 1;
  $semAssessments = array_filter($assessments, fn($a) => (int)($a['semester'] ?? 0) === $currentSem);
  $gradeUrl = url('teacher/grading&course=' . $selectedCourse);
?>

<?php if ($students): ?>
<!-- Semester tabs + Student Marks -->
<div class="card" style="margin-bottom:18px;overflow-x:auto;padding:0">
  <div style="display:flex;align-items:center;gap:0;padding:0;border-bottom:1px solid var(--border)">
    <a href="<?= e($gradeUrl . '&sem=1') ?>" style="padding:12px 20px;font-size:13px;font-weight:600;text-decoration:none;color:<?= $currentSem === 1 ? 'var(--accent)' : 'var(--text-secondary)' ?>;border-bottom:2px solid <?= $currentSem === 1 ? 'var(--accent)' : 'transparent' ?>;transition:all .15s">Semester 1</a>
    <a href="<?= e($gradeUrl . '&sem=2') ?>" style="padding:12px 20px;font-size:13px;font-weight:600;text-decoration:none;color:<?= $currentSem === 2 ? 'var(--accent)' : 'var(--text-secondary)' ?>;border-bottom:2px solid <?= $currentSem === 2 ? 'var(--accent)' : 'transparent' ?>;transition:all .15s">Semester 2</a>
    <a href="<?= e($gradeUrl) ?>" style="padding:12px 20px;font-size:13px;font-weight:600;text-decoration:none;color:<?= !$selectedSem ? 'var(--accent)' : 'var(--text-secondary)' ?>;border-bottom:2px solid <?= !$selectedSem ? 'var(--accent)' : 'transparent' ?>;transition:all .15s">All</a>
    <div style="flex:1"></div>
    <div style="padding:8px 16px">
      <?php $used = (int)($semesterUsedMarks[$currentSem] ?? 0); $remaining = max(0, 100 - $used); ?>
      <span class="tiny" style="font-weight:600;color:<?= $used >= 100 ? 'var(--danger)' : 'var(--text-secondary)' ?>">Budget: <?= $used ?>/100 · <?= $remaining ?> left</span>
    </div>
  </div>

  <table style="width:100%;border-collapse:collapse;font-size:13px;white-space:nowrap">
    <thead>
      <tr style="border-bottom:2px solid var(--border)">
        <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-secondary);position:sticky;left:0;background:var(--card);z-index:1;width:36px">#</th>
        <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-secondary);position:sticky;left:36px;background:var(--card);z-index:1;min-width:180px">Student</th>
        <?php foreach ($semAssessments as $a): ?>
          <th style="padding:10px 12px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary)">
            <div style="max-width:90px;overflow:hidden;text-overflow:ellipsis"><?= e($a['title']) ?></div>
            <div style="font-weight:400;color:var(--text-secondary);opacity:.6;font-size:10px">/<?= (int)$a['max_mark'] ?></div>
          </th>
        <?php endforeach; ?>
        <th style="padding:10px 12px;text-align:center;font-size:11px;font-weight:700;color:var(--accent);background:color-mix(in srgb, var(--accent) 4%, var(--card))">Total</th>
        <?php if (!$selectedSem): ?>
          <th style="padding:10px 12px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:color-mix(in srgb, var(--accent) 3%, var(--card))">S1</th>
          <th style="padding:10px 12px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:color-mix(in srgb, var(--accent) 3%, var(--card))">S2</th>
        <?php endif; ?>
        <th style="padding:10px 12px;text-align:center;font-size:12px;font-weight:700">Final</th>
        <th style="padding:10px 12px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary)">Grade</th>
      </tr>
    </thead>
    <tbody>
      <?php $rank = 0; foreach ($students as $s):
        $f = grading_calc_final((int)$s['id'], $selectedCourse);
        $rank++;
        $letter = $f['letter'] ?? null;
        $gradeColor = match($letter) {
          'A+' => '#059669', 'A' => '#10b981', 'B+' => '#3b82f6', 'B' => '#6366f1',
          'C+' => '#f59e0b', 'C' => '#f97316', 'D+' => '#ef4444', 'D' => '#dc2626', 'F' => '#991b1b',
          default => 'var(--text-secondary)'
        };
      ?>
      <tr style="border-bottom:1px solid var(--border);transition:background .1s" onmouseover="this.style.background='color-mix(in srgb, var(--accent) 2%, transparent)'" onmouseout="this.style.background=''">
        <td style="padding:10px 12px;color:var(--text-secondary);font-size:12px;position:sticky;left:0;background:var(--card);z-index:1"><?= $rank ?></td>
        <td style="padding:10px 12px;position:sticky;left:36px;background:var(--card);z-index:1">
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;border-radius:50%;background:color-mix(in srgb, <?= $gradeColor ?> 12%, var(--card));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:<?= $gradeColor ?>;flex-shrink:0"><?= e(mb_substr($s['first_name'], 0, 1)) ?></div>
            <div>
              <div style="font-weight:600;font-size:13px"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
              <div style="font-size:11px;color:var(--text-secondary)"><?= e($s['sid'] ?? '') ?></div>
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
          <td style="padding:10px 12px;text-align:center;cursor:pointer" title="Click to edit">
            <?php if ($mark !== null): ?>
              <span class="grade-cell" data-assess="<?= (int)$a['id'] ?>" data-student="<?= (int)$s['id'] ?>" data-max="<?= (int)$a['max_mark'] ?>" data-val="<?= e((string)$mark) ?>" style="font-weight:600;font-size:13px;color:<?= $pct >= 50 ? 'var(--success)' : 'var(--danger)' ?>;padding:4px 8px;border-radius:6px;transition:background .15s" onmouseover="this.style.background='color-mix(in srgb, var(--accent) 8%, transparent)'" onmouseout="this.style.background=''"><?= e((string)$mark) ?></span>
            <?php else: ?>
              <span class="grade-cell" data-assess="<?= (int)$a['id'] ?>" data-student="<?= (int)$s['id'] ?>" data-max="<?= (int)$a['max_mark'] ?>" data-val="" style="color:var(--text-secondary);opacity:.3;padding:4px 8px;border-radius:6px;transition:all .15s" onmouseover="this.style.background='color-mix(in srgb, var(--accent) 8%, transparent)';this.style.opacity='1'" onmouseout="this.style.background='';this.style.opacity='.3'">+</span>
            <?php endif; ?>
          </td>
        <?php endforeach; ?>
        <td style="padding:10px 12px;text-align:center;font-weight:700;font-size:14px;background:color-mix(in srgb, var(--accent) 4%, var(--card));color:var(--accent)">
          <?= $currentSem === 1 ? ($f['total1'] !== null ? e($f['total1']) . '%' : '—') : ($f['total2'] !== null ? e($f['total2']) . '%' : '—') ?>
        </td>
        <?php if (!$selectedSem): ?>
          <td style="padding:10px 12px;text-align:center;font-weight:600;font-size:12px;background:color-mix(in srgb, var(--accent) 2%, var(--card))">
            <?= $f['total1'] !== null ? e($f['total1']) . '%' : '—' ?>
          </td>
          <td style="padding:10px 12px;text-align:center;font-weight:600;font-size:12px;background:color-mix(in srgb, var(--accent) 2%, var(--card))">
            <?= $f['total2'] !== null ? e($f['total2']) . '%' : '—' ?>
          </td>
        <?php endif; ?>
        <td style="padding:10px 12px;text-align:center;font-weight:800;font-size:15px">
          <?= $f['adjusted'] !== null ? e($f['adjusted']) : '—' ?>
        </td>
        <td style="padding:10px 12px;text-align:center">
          <?php if ($letter): ?>
            <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:700;background:color-mix(in srgb, <?= $gradeColor ?> 12%, transparent);color:<?= $gradeColor ?>"><?= e($letter) ?></span>
          <?php else: ?>
            <span style="color:var(--text-secondary);opacity:.3">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- Summary -->
<?php if ($finalStats && !empty($finalStats['students'])): ?>
<div class="card" style="margin-bottom:18px">
  <div style="display:flex;gap:16px;flex-wrap:wrap">
    <div style="text-align:center;padding:12px 24px;border-radius:10px;background:color-mix(in srgb, var(--accent) 5%, transparent);flex:1;min-width:100px">
      <div style="font-size:22px;font-weight:800;color:var(--accent)"><?= e($finalStats['avg'] ?? '—') ?>%</div>
      <div class="tiny faint">Average</div>
    </div>
    <div style="text-align:center;padding:12px 24px;border-radius:10px;background:color-mix(in srgb, var(--success) 5%, transparent);flex:1;min-width:100px">
      <div style="font-size:22px;font-weight:800;color:var(--success)"><?= e($finalStats['pass_rate'] ?? '—') ?>%</div>
      <div class="tiny faint">Pass Rate</div>
    </div>
    <div style="text-align:center;padding:12px 24px;border-radius:10px;background:color-mix(in srgb, var(--info) 5%, transparent);flex:1;min-width:100px">
      <div style="font-size:22px;font-weight:800;color:var(--info)"><?= count($students) ?></div>
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

function editMaxMark(id, current) {
  _editAssessId = id;
  document.getElementById('new-max-mark').value = current;
  document.getElementById('edit-max-modal').style.display = 'flex';
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
      closeMaxModal();
      if (d.warning) alert(d.warning);
      location.reload();
    } else {
      alert(d.error || 'Failed');
    }
  }).catch(() => alert('Network error'));
}

// Inline grade editing
document.querySelectorAll('.grade-cell').forEach(cell => {
  cell.addEventListener('click', function(e) {
    e.stopPropagation();
    if (this.querySelector('input')) return;

    const assessId = this.dataset.assess;
    const studentId = this.dataset.student;
    const maxMark = parseInt(this.dataset.max);
    const currentVal = this.dataset.val;
    const td = this.closest('td');
    const origHTML = this.outerHTML;

    const input = document.createElement('input');
    input.type = 'number';
    input.min = '0';
    input.max = maxMark;
    input.step = '0.5';
    input.value = currentVal;
    input.style.cssText = 'width:52px;text-align:center;padding:4px 6px;font-size:13px;font-weight:600;border:2px solid var(--accent);border-radius:6px;background:var(--card);color:var(--text);outline:none;';

    this.replaceWith(input);
    input.focus();
    input.select();

    function revert() {
      if (input.parentNode) {
        input.outerHTML = origHTML;
      }
    }

    function save() {
      const newVal = input.value.trim();
      if (newVal === currentVal) { revert(); return; }

      const fd = new FormData();
      fd.append('assessment_id', assessId);
      fd.append('student_id', studentId);
      fd.append('mark', newVal);
      fd.append('_csrf', '<?= e(csrf_token()) ?>');

      fetch('<?= url('api/grading_save_mark') ?>', {
        method: 'POST',
        body: fd
      }).then(r => r.json()).then(d => {
        if (d.ok) {
          location.reload();
        } else {
          alert(d.error || 'Failed to save');
          revert();
        }
      }).catch(() => {
        alert('Network error');
        revert();
      });
    }

    input.addEventListener('keydown', ev => {
      if (ev.key === 'Enter') { ev.preventDefault(); save(); }
      if (ev.key === 'Escape') { revert(); }
    });
    input.addEventListener('blur', () => { save(); });
  });
});
</script>
