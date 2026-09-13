<?php /* Create new assessment — Apple-style UI */ ?>
<div style="max-width:560px;margin:0 auto;padding:24px 0">

  <!-- Header -->
  <div style="display:flex;align-items:center;gap:14px;margin-bottom:32px">
    <a href="<?= e(url('teacher/grading&course=' . $course['id'])) ?>" style="width:36px;height:36px;border-radius:10px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;text-decoration:none;color:var(--text-secondary);transition:all .15s;flex-shrink:0" onmouseover="this.style.background='color-mix(in srgb, var(--accent) 6%, var(--card))';this.style.borderColor='var(--accent)'" onmouseout="this.style.background='var(--card)';this.style.borderColor='var(--border)'">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div>
      <h1 style="margin:0;font-size:22px;font-weight:700;letter-spacing:-.3px">Create Assessment</h1>
      <p style="margin:2px 0 0;font-size:13px;color:var(--text-secondary)"><?= e($course['title']) ?></p>
    </div>
  </div>

  <form method="post">
    <?= csrf_field() ?>

    <div style="display:flex;flex-direction:column;gap:20px">

      <!-- Assessment Type -->
      <div>
        <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:8px">Assessment Type</label>
        <div style="position:relative">
          <select name="type" id="type-select" required onchange="onTypeChange(this)" style="width:100%;padding:14px 16px;font-size:15px;font-weight:500;border-radius:14px;border:1.5px solid var(--border);background:var(--card);color:var(--text);outline:none;appearance:none;-webkit-appearance:none;cursor:pointer;transition:border-color .2s,box-shadow .2s" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px color-mix(in srgb, var(--accent) 12%, transparent)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
            <?php foreach ($types as $t): ?>
              <option value="<?= e($t['slug']) ?>"
                      data-round="<?= $t['is_round'] ? '1' : '0' ?>"
                      data-semester="<?= (int)($t['semester'] ?? 0) ?>"
                      data-max-round="<?= $t['round_num'] ?? '' ?>">
                <?= e($t['label']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <svg style="position:absolute;right:14px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--text-secondary)" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </div>
      </div>

      <!-- Title -->
      <div>
        <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:8px">Title</label>
        <input name="title" required placeholder="e.g. Cell Structure Quiz, Mid Exam" style="width:100%;padding:14px 16px;font-size:15px;font-weight:500;border-radius:14px;border:1.5px solid var(--border);background:var(--card);color:var(--text);outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px color-mix(in srgb, var(--accent) 12%, transparent)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
      </div>

      <!-- Max Mark + Date row -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
          <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:8px">Maximum Mark</label>
          <div style="position:relative">
            <input type="number" name="max_mark" id="max-mark" min="1" max="100" value="100" required style="width:100%;padding:14px 36px 14px 16px;font-size:15px;font-weight:500;border-radius:14px;border:1.5px solid var(--border);background:var(--card);color:var(--text);outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px color-mix(in srgb, var(--accent) 12%, transparent)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
            <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--text-secondary);pointer-events:none">/100</span>
          </div>
        </div>
        <div>
          <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:8px">Date</label>
          <input type="date" name="assessment_date" value="<?= date('Y-m-d') ?>" style="width:100%;padding:14px 16px;font-size:15px;font-weight:500;border-radius:14px;border:1.5px solid var(--border);background:var(--card);color:var(--text);outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px color-mix(in srgb, var(--accent) 12%, transparent)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
        </div>
      </div>

      <!-- Semester -->
      <div>
        <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:8px">Semester</label>
        <div style="position:relative">
          <select name="semester" id="semester-select" required style="width:100%;padding:14px 16px;font-size:15px;font-weight:500;border-radius:14px;border:1.5px solid var(--border);background:var(--card);color:var(--text);outline:none;appearance:none;-webkit-appearance:none;cursor:pointer;transition:border-color .2s,box-shadow .2s" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px color-mix(in srgb, var(--accent) 12%, transparent)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
            <option value="">— Select Semester —</option>
            <option value="1">Semester 1</option>
            <option value="2">Semester 2</option>
          </select>
          <svg style="position:absolute;right:14px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--text-secondary)" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </div>
      </div>

      <!-- Remaining marks budget -->
      <div id="remaining-info" style="display:none;border-radius:14px;padding:16px 18px;transition:all .25s">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <span style="font-size:13px;font-weight:600" id="remaining-text"></span>
          <span style="font-size:12px;font-weight:500" id="remaining-pct"></span>
        </div>
        <div style="height:6px;border-radius:3px;background:var(--border);overflow:hidden">
          <div id="remaining-bar" style="height:100%;border-radius:3px;transition:width .3s ease"></div>
        </div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:8px" id="remaining-detail"></div>
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:8px">
        <a href="<?= e(url('teacher/grading&course=' . $course['id'])) ?>" style="padding:12px 22px;font-size:14px;font-weight:600;border-radius:12px;border:1.5px solid var(--border);background:var(--card);color:var(--text-secondary);text-decoration:none;transition:all .15s" onmouseover="this.style.borderColor='var(--text-secondary)';this.style.color='var(--text)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-secondary)'">Cancel</a>
        <button type="submit" style="padding:12px 28px;font-size:14px;font-weight:600;border-radius:12px;border:none;background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;cursor:pointer;box-shadow:0 4px 14px rgba(99,102,241,.35);transition:all .15s" onmouseover="this.style.boxShadow='0 6px 20px rgba(99,102,241,.5)';this.style.transform='translateY(-1px)'" onmouseout="this.style.boxShadow='0 4px 14px rgba(99,102,241,.35)';this.style.transform='translateY(0)'">Create Assessment</button>
      </div>
    </div>
  </form>
</div>

<script>
const semUsed = <?= json_encode($semesterUsed) ?>;

function onTypeChange(sel) {
  const opt = sel.options[sel.selectedIndex];
  const semester = parseInt(opt.dataset.semester) || 0;
  const semSel = document.getElementById('semester-select');
  if (semester) {
    semSel.value = semester;
    semSel.disabled = true;
    semSel.style.opacity = '.5';
  } else {
    semSel.disabled = false;
    semSel.style.opacity = '1';
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
  info.style.background = wouldExceed
    ? 'color-mix(in srgb, #ef4444 6%, var(--card))'
    : 'color-mix(in srgb, var(--accent) 5%, var(--card))';
  info.style.border = wouldExceed
    ? '1px solid color-mix(in srgb, #ef4444 20%, transparent)'
    : '1px solid color-mix(in srgb, var(--accent) 15%, transparent)';

  document.getElementById('remaining-text').textContent = wouldExceed
    ? `Exceeds by ${used + maxMark - 100} marks`
    : `${used}/100 marks used`;
  document.getElementById('remaining-text').style.color = wouldExceed ? '#ef4444' : 'var(--text)';

  document.getElementById('remaining-pct').textContent = wouldExceed ? '' : `${afterUsed}%`;
  document.getElementById('remaining-pct').style.color = wouldExceed ? '#ef4444' : 'var(--accent)';

  const bar = document.getElementById('remaining-bar');
  bar.style.width = afterUsed + '%';
  bar.style.background = wouldExceed ? '#ef4444' : 'linear-gradient(90deg, #6366f1, #818cf8)';

  document.getElementById('remaining-detail').textContent = wouldExceed
    ? `Cannot create — ${maxMark} marks exceeds ${remaining} remaining`
    : `After this: ${afterUsed}/100 · ${afterRemaining} remaining`;
}
updateRemaining();
</script>
