<?php /* Create new assessment form */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('plus') ?> Create Assessment</h1>
    <p class="sub"><?= e($course['title']) ?></p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('teacher/grading&course=' . $course['id'])) ?>">← Back</a>
</div>

<form method="post">
  <?= csrf_field() ?>

  <div class="card" style="max-width:700px">
    <div style="display:flex;flex-direction:column;gap:14px">

      <div class="flex-col">
        <label class="small faint" style="font-weight:600">Assessment Type *</label>
        <select class="input" name="type" id="type-select" required onchange="onTypeChange(this)">
          <?php foreach ($types as $t): ?>
            <option value="<?= e($t['slug']) ?>"
                    data-round="<?= $t['is_round'] ? '1' : '0' ?>"
                    data-semester="<?= (int)($t['semester'] ?? 0) ?>"
                    data-max-round="<?= $t['round_num'] ?? '' ?>">
              <?= e($t['label']) ?>
              <?= $t['is_round'] ? '(Semester ' . $t['round_num'] . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="flex-col">
        <label class="small faint" style="font-weight:600">Title *</label>
        <input class="input" name="title" required placeholder="e.g. Algebra Quiz 01, Mid Exam, Final Exam">
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="flex-col">
          <label class="small faint" style="font-weight:600">Maximum Mark *</label>
          <input class="input" type="number" name="max_mark" id="max-mark" min="1" max="100" value="100" required>
          <span class="tiny faint" id="mark-hint">Enter 1–100</span>
        </div>
        <div class="flex-col">
          <label class="small faint" style="font-weight:600">Date</label>
          <input class="input" type="date" name="assessment_date" value="<?= date('Y-m-d') ?>">
        </div>
      </div>

      <div class="flex-col">
        <label class="small faint" style="font-weight:600">Semester *</label>
        <select class="input" name="semester" id="semester-select" required>
          <option value="">— Select Semester —</option>
          <option value="1">Semester 1</option>
          <option value="2">Semester 2</option>
        </select>
      </div>

      <!-- Remaining marks warning -->
      <div id="remaining-info" style="display:none;padding:12px 16px;border-radius:10px;border:1px solid var(--border);background:color-mix(in srgb, var(--accent) 4%, var(--card))">
        <div class="small" style="font-weight:600" id="remaining-text"></div>
        <div class="tiny faint" id="remaining-detail"></div>
        <div style="height:6px;border-radius:3px;background:var(--border);margin-top:8px;overflow:hidden">
          <div id="remaining-bar" style="height:100%;border-radius:3px;transition:width .2s"></div>
        </div>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:10px">
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
  const isRound = opt.dataset.round === '1';
  const semester = parseInt(opt.dataset.semester) || 0;
  const semSel = document.getElementById('semester-select');
  if (semester) semSel.value = semester;
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
  info.style.display = 'block';
  document.getElementById('remaining-text').textContent = `Semester ${sem}: ${used}/100 used, ${remaining} remaining` + (wouldExceed ? ` — EXCEEDS by ${used + maxMark - 100}!` : '');
  document.getElementById('remaining-text').style.color = wouldExceed ? 'var(--danger)' : 'var(--success)';
  document.getElementById('remaining-detail').textContent = wouldExceed
    ? `Cannot create: ${maxMark} marks exceeds ${remaining} remaining`
    : `After this: ${used + maxMark}/100 used, ${Math.max(0, 100 - used - maxMark)} remaining`;
  const bar = document.getElementById('remaining-bar');
  bar.style.width = Math.min(100, used + maxMark) + '%';
  bar.style.background = wouldExceed ? 'var(--danger)' : 'var(--accent)';
}
updateRemaining();
</script>
