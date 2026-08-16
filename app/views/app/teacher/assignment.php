<?php /* Teacher assignment review view */
$statusMeta = ['submitted' => ['Awaiting grade', 'badge-warning'], 'graded' => ['Graded', 'badge-success'], 'returned' => ['Returned', 'badge-muted']];
?>
<style>
  .btn-danger-ghost { color: var(--danger); border-color: var(--danger); background: transparent; }
  .btn-danger-ghost:hover { background: var(--danger); color: #fff; }
  #assign-edit-card .rubric-row { display: flex; gap: 10px; align-items: flex-end; background: var(--bg, #fafafa); border: 1px solid var(--border); border-radius: 10px; padding: 10px 12px; }
  #assign-edit-card .rubric-row .input { flex: 1; min-width: 0; }
  #assign-edit-card .progress-bar { background: var(--accent); transition: width .2s; }
  #assign-edit-card .progress-bar.progress-ok { background: var(--success); }
  #assign-edit-card .progress-bar.progress-danger { background: var(--danger); }
</style>
<div class="page-head">
  <div>
    <h1><?= icon('download') ?> <?= e($assign['title']) ?></h1>
    <p class="sub"><?= e($assign['course_title']) ?> · Due <?= e(date('M j, H:i', strtotime($assign['due_date']))) ?> · <?= count($subs) ?> submission<?= count($subs) === 1 ? '' : 's' ?></p>
  </div>
  <div class="flex gap-8">
    <?php if ($subs): ?>
      <form method="post" class="inline" data-confirm="Generate AI feedback for all submissions?">
        <?= csrf_field() ?><button class="btn btn-ghost" name="ai_feedback_all" value="1"><?= icon('robot') ?> AI feedback for all</button>
      </form>
    <?php endif; ?>
    <button class="btn btn-ghost" id="edit-assign-toggle"><?= icon('edit') ?> Edit settings</button>
    <form method="post" class="inline" data-confirm="Delete this assignment and all its submissions? This cannot be undone.">
      <?= csrf_field() ?>
      <button class="btn btn-danger-ghost" name="delete_assign" value="1"><?= icon('trash') ?> Delete</button>
    </form>
    <a class="btn btn-ghost" href="<?= e(url('teacher/assignments')) ?>">← Back</a>
  </div>
</div>

<?php $now = date('Y-m-d H:i:s'); $d = DateTime::createFromFormat('Y-m-d H:i:s', $assign['due_date'] ?: $now); $dueVal = $d ? $d->format('Y-m-d\TH:i') : ''; ?>
<div class="card" id="assign-edit-card" style="margin-bottom:20px;display:none">
  <h3 class="card-title"><?= icon('pencil') ?> Edit assignment</h3>
  <form method="post" class="flex-col gap-12">
    <?= csrf_field() ?>
    <input type="hidden" name="update_assign" value="1">
    <div class="grid-2 gap-12">
      <div class="flex-col gap-4">
        <label class="small faint" for="e-title">Title *</label>
        <input class="input" id="e-title" name="title" required value="<?= e($assign['title']) ?>">
      </div>
      <div class="flex-col gap-4">
        <label class="small faint" for="e-course">Course</label>
        <select class="input" id="e-course" name="course_id">
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$assign['course_id'] ? 'selected' : '' ?>><?= e($c['title']) ?> (<?= e($c['subject_name']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col gap-4">
        <label class="small faint" for="e-due">Due date *</label>
        <input class="input" type="datetime-local" id="e-due" name="due_date" required value="<?= e($dueVal) ?>">
      </div>
      <div class="flex-col gap-4">
        <label class="small faint" for="e-max">Max score</label>
        <input class="input" type="number" min="1" step="0.5" id="e-max" name="max_score" value="<?= rtrim(rtrim((string)$assign['max_score'], '0'), '.') ?>">
      </div>
    </div>
    <div class="flex gap-16">
      <label class="flex gap-8 small" style="align-items:center">
        <input type="checkbox" name="allow_late" <?= $assign['allow_late'] ? 'checked' : '' ?>> Allow late submissions
      </label>
      <div class="flex-col gap-4" style="max-width:180px">
        <label class="small faint" for="e-penalty">Late penalty %/day</label>
        <input class="input" type="number" min="0" max="100" id="e-penalty" name="late_penalty" value="<?= (int)$assign['late_penalty'] ?>">
      </div>
    </div>
    <div class="flex-col gap-4">
      <label class="small faint" for="e-desc">Description</label>
      <textarea class="input" id="e-desc" name="description" rows="3"><?= e($assign['description']) ?></textarea>
    </div>

    <div class="flex-between" style="align-items:center">
      <label class="small faint">Rubric</label>
      <button type="button" class="btn btn-ghost btn-sm" id="rubric-add"><?= icon('plus') ?> Add row</button>
    </div>
    <div id="rubric-editor" class="flex-col gap-8"></div>
    <div class="small">
      <span id="rubric-total">0</span>% total · <span class="hint">Weights must add up to 100.</span>
      <div class="progress" style="margin-top:6px"><div class="progress-bar" id="rubric-bar" style="width:0%"></div></div>
    </div>

    <div class="flex gap-8">
      <button class="btn btn-primary" type="submit">Save changes</button>
      <button type="button" class="btn btn-ghost" id="assign-edit-cancel">Cancel</button>
    </div>
  </form>
</div>

<script>
(function () {
  const card = document.getElementById('assign-edit-card');
  const tgl = document.getElementById('edit-assign-toggle');
  const can = document.getElementById('assign-edit-cancel');
  if (!card || !tgl) return;
  tgl.addEventListener('click', () => { card.style.display = card.style.display === 'none' ? 'block' : 'none'; });
  if (can) can.addEventListener('click', () => { card.style.display = 'none'; });

  const editor = document.getElementById('rubric-editor');
  const totalEl = document.getElementById('rubric-total');
  const barEl = document.getElementById('rubric-bar');
  const initial = <?= $assign['rubric'] ? json_encode(array_map(fn($r) => ['c' => $r['criterion'], 'm' => (float)$r['max'], 'w' => (float)$r['weight']], $assign['rubric'])) : '[]' ?>;
  function total() {
    let t = 0; document.querySelectorAll('#rubric-editor .r-weight').forEach(i => { t += parseFloat(i.value) || 0; });
    totalEl.textContent = Math.round(t);
    barEl.style.width = Math.min(100, t) + '%';
    barEl.classList.toggle('progress-danger', t > 100);
    barEl.classList.toggle('progress-ok', t === 100);
  }
  function row(r) {
    const d = document.createElement('div');
    d.className = 'rubric-row flex gap-8';
    d.innerHTML =
      '<input class="input r-criterion" name="r_criterion[]" placeholder="Criterion" value="' + (r.c || '') + '">' +
      '<input class="input r-max" name="r_max[]" type="number" min="0" step="0.5" placeholder="Max pts" value="' + (r.m || '') + '">' +
      '<input class="input r-weight" name="r_weight[]" type="number" min="0" max="100" placeholder="Weight %" value="' + (r.w || '') + '">' +
      '<button type="button" class="btn btn-ghost btn-sm rubric-del"><?= icon('x') ?></button>';
    d.querySelector('.r-weight').addEventListener('input', total);
    d.querySelector('.rubric-del').addEventListener('click', () => { d.remove(); total(); });
    return d;
  }
  document.getElementById('rubric-add').addEventListener('click', () => { editor.appendChild(row({})); });
  initial.forEach(r => editor.appendChild(row(r)));
  total();
})();
</script>

<?php if ($assign['rubric']): ?>
<div class="card" style="margin-bottom:20px">
  <h3 class="card-title"><?= icon('ruler') ?> Rubric</h3>
  <div class="flex-col gap-8">
    <?php foreach ($assign['rubric'] as $r): ?>
      <div class="flex-between">
        <span class="small"><?= e($r['criterion']) ?></span>
        <span class="small faint"><?= rtrim(rtrim((string)$r['max'], '0'), '.') ?> pts · <?= (int)$r['weight'] ?>%</span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="flex-col gap-16">
  <?php foreach ($subs as $s): $sm = $statusMeta[$s['status']] ?? $statusMeta['submitted']; ?>
    <div class="card">
      <div class="flex-between" style="flex-wrap:wrap;gap:10px">
        <div class="flex gap-12" style="align-items:center">
          <div class="avatar"><?= e(mb_substr($s['first_name'], 0, 1) . mb_substr($s['last_name'], 0, 1)) ?></div>
          <div>
            <b class="small"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></b>
            <span class="badge badge-muted"><?= e($s['student_id'] ?? '') ?></span>
            <span class="badge <?= $sm[1] ?>"><?= $sm[0] ?></span>
            <?php if ($s['is_late']): ?><span class="badge badge-danger">Late</span><?php endif; ?>
            <p class="tiny faint" style="margin-top:4px">Submitted <?= e(date('M j, H:i', strtotime($s['submitted_at']))) ?></p>
          </div>
        </div>
        <?php if ($s['score'] !== null): ?><b class="small"><?= rtrim(rtrim((string)$s['score'], '0'), '.') ?>/<?= rtrim(rtrim((string)$assign['max_score'], '0'), '.') ?></b><?php endif; ?>
      </div>

      <?php if ($s['content']): ?><p class="small" style="margin:12px 0"><?= nl2br(e($s['content'])) ?></p><?php endif; ?>
      <?php if ($s['file_path']): ?><p class="small"><a class="accent" href="<?= e(url('file?p=' . $s['file_path'])) ?>"><?= icon('paperclip') ?> Download attachment</a></p><?php endif; ?>

      <?php if ($s['ai_feedback']): ?>
        <div class="alert alert-info" style="margin:10px 0"><?= icon('robot') ?> <b>AI feedback:</b> <?= e($s['ai_feedback']) ?></div>
      <?php endif; ?>

      <form method="post" class="flex gap-8" style="align-items:end;margin-top:10px">
        <?= csrf_field() ?>
        <input type="hidden" name="grade_sub" value="<?= (int)$s['id'] ?>">
        <div class="flex-col">
          <label class="tiny faint">Score (max <?= rtrim(rtrim((string)$assign['max_score'], '0'), '.') ?>)</label>
          <input class="input" style="width:100px" type="number" step="0.5" min="0" max="<?= (float)$assign['max_score'] ?>" name="score" value="<?= $s['score'] !== null ? (float)$s['score'] : '' ?>" required>
        </div>
        <div class="flex-col flex-1">
          <label class="tiny faint">Feedback</label>
          <input class="input" name="feedback" value="<?= e($s['feedback'] ?? '') ?>" placeholder="Write feedback for the student…">
        </div>
        <button class="btn btn-primary"><?= icon('save') ?> Save grade</button>
      </form>

      <div class="review-thread" style="margin-top:14px;border-top:1px dashed var(--border);padding-top:12px" data-sub="<?= (int)$s['id'] ?>">
        <div class="flex-between" style="align-items:center">
          <b class="small"><?= icon('chat') ?> Review &amp; reply</b>
          <span class="tiny faint">student can reply live</span>
        </div>
        <div class="review-msgs" style="margin-top:8px">
          <?php foreach (($reviews[(int)$s['id']] ?? []) as $rm): ?>
            <div class="review-msg <?= $rm['role'] === 'teacher' ? 'me' : 'them' ?>">
              <div class="bubble"><b class="tiny"><?= e($rm['first_name'] . ' ' . $rm['last_name']) ?></b><br><?= nl2br(e($rm['message'])) ?></div>
              <span class="tiny faint"><?= e(date('M j, H:i', strtotime($rm['created_at']))) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <form class="review-post flex gap-8" style="margin-top:10px">
          <?= csrf_field() ?>
          <input class="input flex-1" name="message" placeholder="Review this submission… the student sees it live">
          <button class="btn btn-sm btn-primary"><?= icon('send') ?> Send</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$subs): ?><div class="alert alert-info">No submissions yet.</div><?php endif; ?>
</div>

<script>
(function () {
  const CSRF = <?= json_encode(csrf_token()) ?>;
  const poll = (box, quiet) => {
    const sub = box.dataset.sub;
    const wrap = box.querySelector('.review-msgs');
    fetch('<?= e(url('assignments/review/list&sub=')) ?>' + sub, { headers: { 'X-CSRF-Token': CSRF }, credentials: 'same-origin' })
      .then(r => r.json())
      .then(d => {
        if (!d.msgs) return;
        const ids = Array.from(wrap.querySelectorAll('.review-msg')).map(n => n.dataset.id);
        const fresh = d.msgs.filter(m => !ids.includes(String(m.id)));
        if (!fresh.length) return;
        fresh.forEach(m => {
          const el = document.createElement('div');
          el.className = 'review-msg ' + (m.role === 'teacher' ? 'me' : 'them');
          el.dataset.id = m.id;
          el.innerHTML = '<div class="bubble"><b class="tiny"></b><br></div><span class="tiny faint"></span>';
          el.querySelector('.tiny').textContent = m.name;
          el.querySelector('.bubble').innerHTML += m.message.replace(/\n/g, '<br>');
          el.querySelector('.tiny.faint').textContent = new Date(m.created.replace(' ', 'T')).toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
          wrap.appendChild(el);
        });
        box.scrollIntoView({ block: 'nearest' });
      })
      .catch(() => {});
  };
  document.querySelectorAll('.review-thread').forEach(box => {
    const form = box.querySelector('.review-post');
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const input = form.querySelector('input[name=message]');
      const msg = input.value.trim();
      if (!msg) return;
      const btn = form.querySelector('button');
      btn.disabled = true;
      const fd = new FormData();
      fd.set('sub', box.dataset.sub);
      fd.set('message', msg);
      try {
        await fetch('<?= e(url('assignments/review/post')) ?>', {
          method: 'POST', body: fd, headers: { 'X-CSRF-Token': CSRF }, credentials: 'same-origin'
        });
        input.value = '';
        poll(box, true);
      } catch (err) {}
      btn.disabled = false;
    });
    setInterval(() => poll(box), 8000);
  });
})();
</script>

<style>
.review-msgs { display:flex; flex-direction:column; gap:6px; max-height:220px; overflow-y:auto }
.review-msg { max-width:85% }
.review-msg.them { align-self:flex-start; text-align:left }
.review-msg.me { align-self:flex-end; text-align:right }
.review-msg .bubble { background:var(--bg,#f3f4f6); border:1px solid var(--border); border-radius:10px; padding:6px 10px; text-align:left }
.review-msg.me .bubble { background:var(--primary); color:#fff }
.review-msg.me .bubble .tiny { color:rgba(255,255,255,.85) }
</style>
