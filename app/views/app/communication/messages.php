<?php /* Messages view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('chat') ?> Messages</h1>
    <p class="sub">Direct and group conversations</p>
  </div>
  <a class="btn btn-primary" href="<?= e(url('communication/groups')) ?>">+ New group</a>
</div>

<div class="grid" style="grid-template-columns:340px 1fr;gap:18px;align-items:start">
  <div class="card">
    <h3 class="card-title" style="margin-top:0">Conversations</h3>
    <div class="flex-col gap-6" style="max-height:560px;overflow-y:auto">
      <?php foreach ($convs as $cv): ?>
        <a href="<?= e(url('messages&conv=' . $cv['id'])) ?>" class="chat-row <?= $open && (int)$open['id'] === (int)$cv['id'] ? 'active' : '' ?>" style="text-decoration:none">
          <div class="avatar" style="<?= $cv['is_group'] ? 'background:var(--accent-soft)' : '' ?>"><?= e(mb_substr((string)$cv['title'], 0, 1)) ?></div>
          <div class="flex-1">
            <b class="small"><?= e($cv['title']) ?></b>
            <p class="tiny faint"><?= e(mb_strimwidth((string)$cv['last_body'], 0, 40, '…')) ?></p>
          </div>
          <div class="flex-col" style="align-items:flex-end;gap:4px">
            <?php if ($cv['last_at']): ?><span class="tiny faint"><?= e(time_ago($cv['last_at'])) ?></span><?php endif; ?>
            <?php if ((int)$cv['unread'] > 0): ?><span class="cnt"><?= (int)$cv['unread'] ?></span><?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
      <?php if (!$convs): ?><p class="muted small" style="padding:10px">No conversations yet. Start one with a classmate, teacher or a user from another school.</p><?php endif; ?>
    </div>
    <div style="padding-top:12px;margin-top:10px">
      <div class="flex gap-8">
        <input class="input flex-1" id="msg-people-search" type="search" placeholder="Find someone to message…" autocomplete="off">
        <button class="btn btn-sm" id="msg-people-go" type="button"><?= icon('chat') ?> Open</button>
      </div>
      <div id="msg-people-results" style="margin-top:8px"></div>
      <form method="post" class="flex gap-8" style="margin-top:8px">
        <?= csrf_field() ?>
        <input class="input flex-1" name="other_id" type="number" placeholder="Or user ID">
        <button class="btn btn-sm" name="start_dm" value="1">Start</button>
      </form>
    </div>
  </div>

  <div class="card" style="display:flex;flex-direction:column;min-height:560px">
    <?php if ($open): ?>
      <div class="flex-between" style="padding-bottom:12px;border-bottom:1px solid var(--border)">
        <div class="flex gap-10" style="align-items:center">
          <div class="avatar"><?= e(mb_substr((string)$open['title'], 0, 1)) ?></div>
          <b><?= e($open['title']) ?></b><?= $open['is_group'] ? '<span class="badge badge-accent">group</span>' : '' ?>
        </div>
      </div>
      <div class="chat-area" id="chat-area" style="flex:1;overflow-y:auto;padding:16px 4px">
        <?php foreach ($messages as $m): $mine = (int)$m['sender_id'] === (int)$__u['id']; ?>
          <div class="bubble <?= $mine ? 'mine' : 'theirs' ?>">
            <?php if (!$mine): ?><b class="tiny"><?= e($m['first_name'] . ' ' . mb_substr((string)$m['last_name'], 0, 1)) ?>.</b><?php endif; ?>
            <div><?= nl2br(e($m['body'])) ?></div>
            <span class="tiny faint"><?= e(time_ago($m['created_at'])) ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (!$messages): ?><p class="muted small" style="text-align:center;padding:30px">Say hi <?= icon('hand') ?></p><?php endif; ?>
      </div>
      <form method="post" class="flex gap-8" style="padding-top:12px">
        <?= csrf_field() ?>
        <input type="hidden" name="conv_id" value="<?= (int)$open['id'] ?>">
        <input class="input flex-1" name="body" placeholder="Type a message…" autofocus required>
        <button class="btn btn-primary" name="send_message" value="1">➤ Send</button>
      </form>
      <script>document.addEventListener('DOMContentLoaded', () => { const a = document.getElementById('chat-area'); if (a) a.scrollTop = a.scrollHeight; });</script>
    <?php else: ?>
      <div class="empty" style="flex:1"><span class="empty-ico"><?= icon('chat') ?></span>Select a conversation</div>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  const box = document.getElementById('msg-people-search');
  const res = document.getElementById('msg-people-results');
  if (!box) return;
  let timer = null;
  const openUser = () => {
    let raw = box.value.trim();
    const m = raw.match(/^#?(\d+)$/);
    const id = m ? m[1] : raw;
    if (!id) return;
    window.location.href = EDUNEX.URL + '/index.php?r=messages&to=' + encodeURIComponent(id);
  };
  box.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); openUser(); } });
  const go = document.getElementById('msg-people-go');
  if (go) go.addEventListener('click', openUser);
  box.addEventListener('input', () => {
    clearTimeout(timer);
    const q = box.value.trim();
    if (q.length < 2) { res.innerHTML = ''; return; }
    timer = setTimeout(async () => {
      try {
        const r = await fetch(EDUNEX.API + '/api/search?q=' + encodeURIComponent(q));
        const d = await r.json();
        const people = d.results.filter(i => i.type === 'student' || i.type === 'teacher' || i.type === 'parent').slice(0, 6);
        res.innerHTML = people.length
          ? people.map((p, i) => `<button type="button" class="dropdown-item" data-id="${p.id}" style="text-align:left;width:100%">${escapeHtml(p.title)} <span class="faint small">(${p.type}${p.subtitle ? ' · ' + escapeHtml(p.subtitle) : ''})</span></button>`).join('')
          : '<div class="dropdown-head">No people found</div>';
        res.querySelectorAll('button').forEach(b => b.onclick = function () {
          location.href = EDUNEX.URL + '/index.php?r=messages&to=' + encodeURIComponent(this.dataset.id);
        });
      } catch (err) {}
    }, 250);
  });
  /* Lightweight poll: if a conversation is open, reload the page silently every 25s
     so new messages / unread counts appear without manual refresh. */
  if (new URLSearchParams(location.search).get('conv')) {
    setInterval(() => {
      if (document.visibilityState === 'visible') location.reload();
    }, 25000);
  }
})();
</script>
