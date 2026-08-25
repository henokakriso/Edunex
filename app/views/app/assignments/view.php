<?php /* Assignment detail view (student submit / teacher view) */
$statusMeta = ['submitted' => ['Awaiting grade', 'badge-warning'], 'graded' => ['Graded', 'badge-success'], 'returned' => ['Returned', 'badge-muted']];
?>
<div class="page-head">
  <div>
    <h1><?= icon('doc') ?> <?= e($assign['title']) ?></h1>
    <p class="sub"><?= e($assign['course_title']) ?> · Due <?= e(date('M j, H:i', strtotime($assign['due_date']))) ?><?= $assign['allow_late'] ? ' · Late allowed' : '' ?></p>
  </div>
  <?php if ($__u['role'] === 'student'): ?><a class="btn btn-ghost" href="<?= e(url('student/assignments')) ?>">← Back</a><?php endif; ?>
</div>

<div class="grid" style="grid-template-columns:1.5fr 1fr;gap:22px;align-items:start">
  <div class="flex-col gap-16">
    <div class="card">
      <h3 class="card-title"><?= icon('note') ?> Instructions</h3>
      <p class="small" style="white-space:pre-wrap"><?= nl2br(e($assign['description'])) ?></p>
      <?php if ($assign['rubric']): ?>
        <h3 class="card-title" style="margin-top:18px"><?= icon('ruler') ?> Rubric</h3>
        <?php foreach ($assign['rubric'] as $r): ?>
          <div class="flex-between" style="padding:6px 0;border-bottom:1px solid var(--border)">
            <span class="small"><?= e($r['criterion']) ?></span>
            <span class="small faint"><?= rtrim(rtrim((string)$r['max'], '0'), '.') ?> pts · <?= (int)$r['weight'] ?>%</span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($__u['role'] === 'student'): ?>
      <div class="card">
        <h3 class="card-title"><?= icon('edit') ?> Your submission</h3>
        <?php if ($sub && $sub['status'] !== 'submitted'): ?>
          <div class="alert alert-<?= $sub['status'] === 'graded' ? 'success' : 'info' ?>">
            <?= $sub['status'] === 'graded' ? 'Graded: <b>' . rtrim(rtrim((string)$sub['score'], '0'), '.') . '/' . rtrim(rtrim((string)$assign['max_score'], '0'), '.') . '</b>' : 'Status: ' . $sub['status'] ?>
            <?= $sub['is_late'] ? '<br><span class="tiny">' . icon('alert') . ' Submitted late</span>' : '' ?>
          </div>
          <?php if ($sub['feedback']): ?><div class="alert alert-info"><?= icon('user') ?>‍<?= icon('school') ?> <b>Teacher feedback:</b> <?= e($sub['feedback']) ?></div><?php endif; ?>
          <?php if ($sub['ai_feedback']): ?><div class="alert alert-info"><?= icon('robot') ?> <b>AI feedback:</b> <?= e($sub['ai_feedback']) ?></div><?php endif; ?>

          <?php if ($reviews || $sub['status'] !== 'submitted'): ?>
            <div class="review-thread" style="margin-top:14px;border-top:1px dashed var(--border);padding-top:12px" data-sub="<?= (int)$sub['id'] ?>">
              <b class="small"><?= icon('chat') ?> Review with your teacher</b>
              <?php if ($sub['status'] === 'submitted'): ?>
                <p class="tiny faint" style="margin-top:4px">Your teacher can leave a review here once they grade — you can reply live.</p>
              <?php endif; ?>
              <div class="review-msgs" style="margin-top:8px">
                <?php foreach ($reviews as $rm): ?>
                  <div class="review-msg <?= $rm['role'] === 'student' ? 'me' : 'them' ?>">
                    <div class="bubble"><b class="tiny"><?= e($rm['first_name'] . ' ' . $rm['last_name']) ?></b><br><?= nl2br(e($rm['message'])) ?></div>
                    <span class="tiny faint"><?= e(date('M j, H:i', strtotime($rm['created_at']))) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
              <form class="review-post flex gap-8" style="margin-top:10px">
                <?= csrf_field() ?>
                <input class="input flex-1" name="message" placeholder="Reply to your teacher…">
                <button class="btn btn-sm btn-primary"><?= icon('send') ?> Reply</button>
              </form>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <?php if ($sub): ?><div class="alert alert-warning">You already submitted — resubmitting will replace your answer.</div><?php endif; ?>
          <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <label class="small faint">Your answer</label>
            <textarea class="input" name="content" rows="8" placeholder="Write your answer here…"><?= e($sub['content'] ?? '') ?></textarea>
            <label class="small faint" style="margin-top:12px">Attachment (optional)</label>
            <input class="input" type="file" name="file">
            <?php if ($sub && $sub['file_path']): ?><p class="tiny faint" style="margin-top:6px">Current: <a class="accent" href="<?= e(url('file?p=' . $sub['file_path'])) ?>"><?= e($sub['file_path']) ?></a></p><?php endif; ?>
            <button class="btn btn-primary btn-lg" style="margin-top:14px"><?= $sub ? 'Update submission' : 'Submit assignment' ?></button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="card" style="position:sticky;top:90px">
    <h3 class="card-title" style="margin-top:0">ℹ Details</h3>
    <div class="flex-col gap-8">
      <div class="flex-between"><span class="small faint">Due</span><b class="small"><?= e(date('M j, H:i', strtotime($assign['due_date']))) ?></b></div>
      <div class="flex-between"><span class="small faint">Max score</span><b class="small"><?= rtrim(rtrim((string)$assign['max_score'], '0'), '.') ?></b></div>
      <div class="flex-between"><span class="small faint">Late penalty</span><b class="small"><?= (float)$assign['late_penalty'] ?>%</b></div>
      <div class="flex-between"><span class="small faint">Submissions</span><b class="small"><?= $subs ? count($subs) : '—' ?></b></div>
    </div>
    <?php if ($subs): ?>
      <div style="height:16px"></div>
      <h3 class="card-title" style="margin-top:0"><?= icon('users') ?> All submissions</h3>
      <?php foreach ($subs as $s2): $sm = $statusMeta[$s2['status']] ?? $statusMeta['submitted']; ?>
        <div class="list-row" style="padding:8px 0">
          <div class="avatar"><?= e(mb_substr($s2['first_name'], 0, 1)) ?></div>
          <div class="flex-1 small">
            <b><?= e($s2['first_name'] . ' ' . $s2['last_name']) ?></b>
            <p class="tiny faint"><?= e(date('M j, H:i', strtotime($s2['submitted_at']))) ?></p>
          </div>
          <span class="badge <?= $sm[1] ?>"><?= $sm[0] ?></span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  const box = document.querySelector('.review-thread');
  if (!box) return;
  const CSRF = <?= json_encode(csrf_token()) ?>;
  const poll = () => {
    const wrap = box.querySelector('.review-msgs');
    fetch('<?= e(url('assignments/review/list&sub=')) ?>' + box.dataset.sub, { headers: { 'X-CSRF-Token': CSRF }, credentials: 'same-origin' })
      .then(r => r.json())
      .then(d => {
        if (!d.msgs) return;
        const ids = Array.from(wrap.querySelectorAll('.review-msg')).map(n => n.dataset.id);
        d.msgs.filter(m => !ids.includes(String(m.id))).forEach(m => {
          const el = document.createElement('div');
          el.className = 'review-msg ' + (m.role === 'student' ? 'me' : 'them');
          el.dataset.id = m.id;
          const bubble = document.createElement('div');
          bubble.className = 'bubble';
          bubble.innerHTML = '<b class="tiny"></b><br>' + m.message.replace(/\n/g, '<br>');
          bubble.querySelector('.tiny').textContent = m.name;
          const t = document.createElement('span');
          t.className = 'tiny faint';
          t.textContent = new Date(m.created.replace(' ', 'T')).toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
          el.appendChild(bubble);
          el.appendChild(t);
          wrap.appendChild(el);
        });
        wrap.scrollTop = wrap.scrollHeight;
      })
      .catch(() => {});
  };
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
      await fetch('<?= e(url('assignments/review/post')) ?>', { method: 'POST', body: fd, headers: { 'X-CSRF-Token': CSRF }, credentials: 'same-origin' });
      input.value = '';
      poll();
    } catch (err) {}
    btn.disabled = false;
  });
  setInterval(poll, 8000);
})();
</script>

<style>
.review-msgs { display:flex; flex-direction:column; gap:6px; max-height:240px; overflow-y:auto }
.review-msg { max-width:85% }
.review-msg.them { align-self:flex-start }
.review-msg.me { align-self:flex-end; text-align:right }
.review-msg .bubble { background:var(--bg,#f3f4f6); border:1px solid var(--border); border-radius:10px; padding:6px 10px; text-align:left }
.review-msg.me .bubble { background:var(--primary); color:#fff }
.review-msg.me .bubble .tiny { color:rgba(255,255,255,.85) }
</style>
