<?php /* Create new assessment — Edunex glass style */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('plus') ?> Create Assessment</h1>
    <p class="sub"><?= e($course['title']) ?></p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('teacher/grading&course=' . $course['id'])) ?>"><?= icon('arrow-left') ?> Back to Gradebook</a>
</div>

<form method="post">
  <?= csrf_field() ?>

  <div class="card" style="max-width:640px">
    <div style="display:flex;flex-direction:column;gap:16px">

      <!-- Assessment Type -->
      <div class="flex-col">
        <label class="small faint" style="font-weight:600">Assessment Type *</label>
        <select class="input" name="type" id="type-select" required onchange="onTypeChange(this)" style="width:100%">
          <?php foreach ($types as $t): ?>
            <option value="<?= e($t['slug']) ?>"
                    data-round="<?= $t['is_round'] ? '1' : '0' ?>"
                    data-semester="<?= (int)($t['semester'] ?? 0) ?>"
                    data-max-round="<?= $t['round_num'] ?? '' ?>">
              <?= e($t['label']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Title -->
      <div class="flex-col">
        <label class="small faint" style="font-weight:600">Title *</label>
        <input class="input" name="title" required placeholder="e.g. Cell Structure Quiz, Mid Exam, Final Exam" style="width:100%">
      </div>

      <!-- Max Mark + Date -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="flex-col">
          <label class="small faint" style="font-weight:600">Maximum Mark *</label>
          <input class="input" type="number" name="max_mark" id="max-mark" min="1" max="100" value="100" required style="width:100%">
          <span class="tiny faint" id="mark-hint">Enter 1–100</span>
        </div>
        <div class="flex-col">
          <label class="small faint" style="font-weight:600">Date</label>
          <input class="input" type="date" name="assessment_date" value="<?= date('Y-m-d') ?>" style="width:100%">
        </div>
      </div>

      <!-- Semester -->
      <div class="flex-col">
        <label class="small faint" style="font-weight:600">Semester *</label>
        <select class="input" name="semester" id="semester-select" required style="width:100%">
          <option value="">— Select Semester —</option>
          <option value="1">Semester 1</option>
          <option value="2">Semester 2</option>
        </select>
      </div>

      <!-- Remaining marks budget -->
      <div id="remaining-info" style="display:none;padding:12px 16px;border-radius:10px;border:1px solid var(--border);background:color-mix(in srgb, var(--accent) 4%, var(--card))">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <span class="small" style="font-weight:600" id="remaining-text"></span>
          <span class="tiny" style="font-weight:600" id="remaining-pct"></span>
        </div>
        <div style="height:6px;border-radius:3px;background:var(--border);overflow:hidden">
          <div id="remaining-bar" style="height:100%;border-radius:3px;transition:width .3s ease"></div>
        </div>
        <div class="tiny faint" style="margin-top:8px" id="remaining-detail"></div>
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:6px">
        <a class="btn btn-ghost" href="<?= e(url('teacher/grading&course=' . $course['id'])) ?>">Cancel</a>
        <button class="btn btn-primary" type="submit"><?= icon('plus') ?> Create Assessment</button>
      </div>
    </div>
  </div>
</form>

<script>
const semUsed = <?= json_encode($semesterUsed) ?>;

function onTypeChange(sel) {
  const opt = sel.options[sel.selectedIndex];
  const semester = parseInt(opt.dataset.semester) || 0;
  const semSel = document.getElementById('semester-select');
  if (semester) {
    semSel.value = semester;
    semSel.disabled = true;
  } else {
    semSel.disabled = false;
  }
  updateRemaining();
}

document.getElementById('max-mark').addEventListener('input', updateRemaining);
document.getElementById('semester-select').addEventListener('change', updateRemaining);

function updateRemaining() {
  const sem = parseInt(document.getElementById('semester-select').value) || 0;
  const maxMark = parseFloat(document.getElementById('max-mark').value) || 0;
  const info = document.getElementById('remaining-info');
  if (!sem || !maxMark) { info.style.display = 'none'; return; }

  const used = semUsed[sem] || 0;
  const remaining = Math.max(0, 100 - used);
  const wouldExceed = used + maxMark > 100;
  const afterUsed = Math.min(100, used + maxMark);
  const afterRemaining = Math.max(0, 100 - afterUsed);

  info.style.display = 'block';
  info.style.borderColor = wouldExceed ? 'color-mix(in srgb, var(--danger) 30%, transparent)' : 'var(--border)';
  info.style.background = wouldExceed ? 'color-mix(in srgb, var(--danger) 5%, var(--card))' : 'color-mix(in srgb, var(--accent) 4%, var(--card))';

  document.getElementById('remaining-text').textContent = wouldExceed
    ? 'Exceeds budget'
    : `${used}/100 marks used`;
  document.getElementById('remaining-text').style.color = wouldExceed ? 'var(--danger)' : 'var(--text)';

  document.getElementById('remaining-pct').textContent = wouldExceed ? '' : `${afterUsed}%`;
  document.getElementById('remaining-pct').style.color = wouldExceed ? 'var(--danger)' : 'var(--accent)';

  const bar = document.getElementById('remaining-bar');
  bar.style.width = afterUsed + '%';
  bar.style.background = wouldExceed ? 'var(--danger)' : 'linear-gradient(90deg, var(--accent), color-mix(in srgb, var(--accent) 60%, var(--accent-2)))';

  document.getElementById('remaining-detail').textContent = wouldExceed
    ? `Cannot create: ${maxMark} marks exceeds ${remaining} remaining`
    : `After this: ${afterUsed}/100 used · ${afterRemaining} remaining`;
}
updateRemaining();
</script>
