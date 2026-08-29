<?php /* Teacher assignments list view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('doc') ?> Assignments</h1>
    <p class="sub">Create and manage assignments</p>
  </div>
  <button class="btn btn-primary" data-open-modal="new-assign-modal">+ New assignment</button>
</div>

<div class="modal-backdrop" id="new-assign-modal">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <h3><?= icon('plus') ?> New assignment</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post">
        <?= csrf_field() ?>
        <div class="grid2">
          <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" required placeholder="Essay: The Water Cycle"></div>
          <div class="flex-col"><label class="small faint">Course * (only your authorised subjects)</label>
            <select class="input" name="course_id" required>
              <option value="">— Choose course —</option>
              <?php foreach ($courses as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?> (<?= e($c['subject_name']) ?>)</option><?php endforeach; ?>
            </select>
            <?php if (!$courses): ?><p class="tiny faint" style="margin-top:4px">No authorised courses yet — ask your director to assign subjects.</p><?php endif; ?>
          </div>
          <div class="flex-col"><label class="small faint">Due date</label><input class="input" type="datetime-local" name="due_date" value="<?= e(date('Y-m-d\TH:i', time() + 86400 * 7)) ?>"></div>
          <div class="flex-col"><label class="small faint">Max score</label><input class="input" type="number" name="max_score" value="100" min="1" step="1"></div>
          <div class="flex-col"><label class="small faint">Late submissions</label>
            <select class="input" name="allow_late"><option value="1">Allowed</option><option value="0">Not allowed</option></select>
          </div>
          <div class="flex-col"><label class="small faint">Late penalty (%)</label><input class="input" type="number" name="late_penalty" value="0" min="0" max="100"></div>
          <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Description / instructions</label><textarea class="input" name="description" rows="4"></textarea></div>
          <div class="flex-col" style="grid-column:1/-1">
            <label class="small faint">Rubric — criterion, max points and weight (%)</label>
            <div id="rubric-rows" class="flex-col gap-8">
              <div class="rubric-row" data-weight="40">
                <input class="input flex-1" name="r_criterion[]" placeholder="Criterion — e.g. Content accuracy">
                <div class="rubric-num"><label class="tiny faint">Max pts</label><input class="input" type="number" name="r_max[]" value="10" min="0" step="0.5"></div>
                <div class="rubric-num"><label class="tiny faint">Weight %</label><input class="input r-weight" type="number" name="r_weight[]" value="40" min="0" max="100"></div>
                <button type="button" class="btn btn-sm btn-ghost rubric-del" title="Remove row"><?= icon('trash') ?></button>
              </div>
            </div>
            <div class="flex gap-8" style="align-items:center;margin-top:10px">
              <button type="button" class="btn btn-sm btn-ghost" id="rubric-add">+ Add row</button>
              <div class="flex-1">
                <div class="flex-between small faint"><span>Total weight</span><b id="rubric-total" class="small">40%</b></div>
                <div class="progress" style="height:8px"><div id="rubric-bar" style="width:40%"></div></div>
                <p class="tiny faint" id="rubric-hint" style="margin-top:4px">Weights don't need to sum to 100 — each criterion is scaled automatically.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" name="create_assign" value="1"><?= icon('rocket') ?> Create assignment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="flex-col gap-16">
  <?php foreach ($assignments as $a): ?>
    <div class="card">
      <div class="flex-between" style="flex-wrap:wrap;gap:12px">
        <div>
          <b><?= e($a['title']) ?></b>
          <span class="badge badge-accent"><?= e($a['course_title']) ?></span>
          <?php if ($a['subject_name']): ?><span class="badge badge-info"><?= e($a['subject_name']) ?></span><?php endif; ?>
          <?php if ((int)$a['pending'] > 0): ?><span class="badge badge-warning"><?= (int)$a['pending'] ?> to grade</span><?php endif; ?>
          <p class="tiny faint" style="margin-top:4px">Due <?= e(date('M j, H:i', strtotime($a['due_date']))) ?> · <?= (int)$a['subs'] ?> submissions · max <?= rtrim(rtrim((string)$a['max_score'], '0'), '.') ?></p>
        </div>
        <div class="flex gap-8">
          <a class="btn btn-sm" href="<?= e(url('teacher/assignment&id=' . $a['id'])) ?>"><?= icon('download') ?> Review submissions</a>
          <form method="post" class="inline" data-confirm="Delete '<?= e($a['title']) ?>' and all its submissions? This cannot be undone.">
            <?= csrf_field() ?>
            <input type="hidden" name="assignment_id" value="<?= (int)$a['id'] ?>">
            <button class="btn btn-sm btn-danger-ghost" name="delete_assign" value="1" title="Delete assignment"><?= icon('trash') ?></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$assignments): ?><div class="alert alert-info">No assignments yet.</div><?php endif; ?>
</div>

<style>
.btn-danger-ghost { color: var(--danger); border-color: var(--danger); background: transparent; }
.btn-danger-ghost:hover { background: var(--danger); color: #fff; }
.rubric-row { display:flex; gap:10px; align-items:flex-end; background:var(--bg, #fafafa); border:1px solid var(--border); border-radius:10px; padding:10px 12px }
.rubric-row .rubric-num { width:110px }
.rubric-row .rubric-num .input { margin-top:4px }
.rubric-row .rubric-del { margin-bottom:2px }
#rubric-total.ok { color:var(--success) }
#rubric-total.warn { color:var(--danger) }
</style>

<script>
(function () {
  const rows = document.getElementById('rubric-rows');
  const addBtn = document.getElementById('rubric-add');
  if (!rows || !addBtn) return;
  const totalEl = document.getElementById('rubric-total');
  const barEl = document.getElementById('rubric-bar');
  const hintEl = document.getElementById('rubric-hint');
  const rowTpl = '<input class="input flex-1" name="r_criterion[]" placeholder="Criterion — e.g. Grammar & spelling">' +
    '<div class="rubric-num"><label class="tiny faint">Max pts</label><input class="input" type="number" name="r_max[]" value="10" min="0" step="0.5"></div>' +
    '<div class="rubric-num"><label class="tiny faint">Weight %</label><input class="input r-weight" type="number" name="r_weight[]" value="30" min="0" max="100"></div>' +
    '<button type="button" class="btn btn-sm btn-ghost rubric-del" title="Remove row"><?= icon('trash') ?></button>';
  const addRow = () => {
    const d = document.createElement('div');
    d.className = 'rubric-row';
    d.innerHTML = rowTpl;
    rows.appendChild(d);
    refresh();
    d.querySelector('.r-weight').focus();
  };
  const refresh = () => {
    let total = 0;
    rows.querySelectorAll('.r-weight').forEach(w => total += Math.max(0, parseFloat(w.value) || 0));
    const capped = Math.min(100, total);
    totalEl.textContent = Math.round(total) + '%';
    totalEl.classList.toggle('ok', total > 0 && total <= 100);
    totalEl.classList.toggle('warn', total > 100);
    barEl.style.width = capped + '%';
    if (total > 100) {
      hintEl.textContent = 'Total weight exceeds 100% — scores will be scaled; keep it at or under 100.';
    } else {
      hintEl.textContent = total === 100 ? 'Weights sum to 100% — perfectly balanced.' : 'Weights don\'t need to sum to 100 — each criterion is scaled automatically.';
    }
  };
  addBtn.addEventListener('click', addRow);
  rows.addEventListener('input', e => { if (e.target.classList.contains('r-weight')) refresh(); });
  rows.addEventListener('click', e => {
    if (e.target.closest('.rubric-del')) { e.target.closest('.rubric-row').remove(); refresh(); }
  });
  refresh();
})();
</script>
