<?php /* AI Tutor chat view */
$csrftok = csrf_token();
$chatId = (int)($chat['id'] ?? 0);
$chatTitle = $chat['title'] ?? 'New chat';
$msgCount = count($messages);
$aiIcon = json_encode(icon('robot'));
$chatIcon = json_encode(icon('chat'));
$editIcon = json_encode(icon('edit'));
$trashIcon = json_encode(icon('trash'));
?>
<div class="page-head">
  <div>
    <h1><?= icon('robot') ?> AI Tutor</h1>
    <p class="sub">Local C-backed model — streaming replies, works offline, understands Amharic &amp; English</p>
  </div>
</div>

<div class="tutor-layout">
  <div class="card tutor-sidebar">
    <div class="flex-between" style="padding:2px 6px 9px">
      <h3 class="card-title" style="margin:0"><?= icon('chat') ?> Chats</h3>
      <span class="tiny faint" id="chat-count"><?= count($chats) ?></span>
    </div>
    <button type="button" class="btn-new-chat" id="btn-new-chat" title="Start a fresh conversation"><?= icon('plus') ?> New chat</button>
    <div class="flex-col gap-4" id="chat-list">
      <?php foreach ($chats as $c): ?>
        <div class="chat-tab <?= (int)$c['id'] === $chatId ? 'active' : '' ?>" data-id="<?= (int)$c['id'] ?>">
          <span class="chat-title" title="<?= e($c['title']) ?>"><?= icon('chat') ?> <?= e(mb_strimwidth((string)$c['title'], 0, 26, '…')) ?></span>
          <span class="chat-actions">
            <button type="button" class="ren" title="Rename"><?= icon('edit') ?></button>
            <button type="button" class="del" title="Delete"><?= icon('trash') ?></button>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card tutor-chat" style="padding:0;overflow:hidden">
    <div class="tutor-head">
      <div class="tutor-head-info">
        <b class="tutor-title"><?= icon('robot') ?> <?= e($chatTitle) ?></b>
        <p class="tutor-meta">
          <span class="msg-count"><span id="msg-count"><?= $msgCount ?></span> message(s)</span>
          <span class="sep">·</span>
          <span class="model-chip"><span class="dot" id="model-dot"></span><span id="model-badge">auto-routed</span></span>
        </p>
      </div>
      <div class="flex gap-6" style="flex-shrink:0">
        <?php if ($chat): ?>
          <button type="button" class="btn btn-sm btn-ghost" id="head-rename" title="Rename chat"><?= icon('edit') ?> Rename</button>
          <button type="button" class="btn btn-sm btn-danger-ghost" id="head-delete" title="Delete chat"><?= icon('trash') ?></button>
        <?php endif; ?>
      </div>
    </div>

    <div id="chat-log" class="tutor-log">
      <?php foreach ($messages as $m): ?>
        <div class="chat-msg <?= $m['role'] === 'ai' ? 'ai' : 'me' ?>">
          <div class="avatar-bubble">
            <span class="chat-avatar <?= $m['role'] === 'ai' ? 'ai' : 'me' ?>"><?= $m['role'] === 'ai' ? icon('robot') : 'Y' ?></span>
            <div class="bubble"><?= e($m['content']) ?></div>
          </div>
          <span class="meta"><?= $m['role'] === 'ai' ? 'Tutor' : 'You' ?> · <?= e(date('H:i', strtotime($m['created_at']))) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$messages): ?>
        <div class="empty-state" style="margin:26px 10px">
          <div class="empty-ic"><?= icon('robot') ?></div>
          <h3>Ask anything — in Amharic or English</h3>
          <p class="small">Your personal tutor adapts to your pace. Try one of these:</p>
          <div class="suggest-chips">
            <button type="button" data-q="Explain recursion like I'm five">Explain recursion</button>
            <button type="button" data-q="What is photosynthesis?">What is photosynthesis?</button>
            <button type="button" data-q="Make a quiz on loops">Quiz on loops</button>
            <button type="button" data-q="Translate 'how are you' to Amharic">Translate to Amharic</button>
            <button type="button" data-q="Solve 2x + 6 = 14">Solve 2x + 6 = 14</button>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <form id="tutor-form" class="tutor-form" action="<?= e(url('ai/tutor')) ?>" method="get">
      <input class="input" style="flex:1;min-width:0" id="tutor-msg" name="message" placeholder="Ask your question…" autofocus>
      <button class="btn btn-primary btn-send" id="tutor-send" title="Send">➤</button>
      <button type="button" class="btn btn-danger" id="tutor-stop" style="display:none"><?= icon('ban-circle') ?> Stop</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const log = document.getElementById('chat-log');
  if (log) log.scrollTop = log.scrollHeight;
});
let TUTOR_CHAT = <?= (int)$chatId ?>;
const isCurrent = (id) => String(id) === String(TUTOR_CHAT);
const TUTOR_CSRF = <?= json_encode($csrftok) ?>;
const TUTOR_BASE = <?= json_encode(url('ai/tutor')) ?>;
const ICO = {
  chat: <?= $chatIcon ?>,
  robot: <?= $aiIcon ?>,
  edit: <?= $editIcon ?>,
  trash: <?= $trashIcon ?>
};

function esc(s) {
  const d = document.createElement('div');
  d.textContent = String(s);
  return d.innerHTML;
}

function bumpCount(delta) {
  const el = document.getElementById('chat-count');
  if (el) el.textContent = Math.max(0, (parseInt(el.textContent) || 0) + delta);
}

function tutorPost(body, onOk, opts) {
  body.set('_csrf', TUTOR_CSRF);
  fetch(TUTOR_BASE, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: body.toString(),
    keepalive: opts && opts.keepalive
  }).then(r => {
    if (r.redirected || r.status === 302 || r.status === 204) return { ok: true };
    return r.json().catch(() => ({ ok: true }));
  }).then(onOk).catch(() => {});
}

function tutorRename(id) {
  const tab = document.querySelector('.chat-tab[data-id="' + id + '"]');
  const current = tab ? tab.querySelector('.chat-title').textContent.replace(/^\S+\s*/, '') : '';
  const title = prompt('Rename this chat:', current);
  if (title === null || !title.trim()) return;
  const b = new URLSearchParams();
  b.set('rename_chat', '1'); b.set('chat_id', id); b.set('title', title.trim());
  tutorPost(b, (res) => {
    if (!res.ok) return;
    if (tab) tab.querySelector('.chat-title').innerHTML = ICO.chat + ' ' + esc(title);
    if (isCurrent(id)) {
      const h = document.querySelector('.tutor-title');
      if (h) h.innerHTML = ICO.robot + ' ' + esc(title);
    }
  });
}

function tutorDelete(id) {
  const tab = document.querySelector('.chat-tab[data-id="' + id + '"]');
  const parent = tab ? tab.parentNode : null;
  const next = tab ? tab.nextElementSibling : null;
  if (tab) { tab.remove(); bumpCount(-1); }                 // sidebar: gone NOW
  const b = new URLSearchParams();
  b.set('delete_chat', '1'); b.set('chat_id', id);
  tutorPost(b, (res) => {
    if (!res.ok && tab && parent) {                          // server refused → restore truth
      parent.insertBefore(tab, next);
      bumpCount(1);
      return;
    }
    if (!res.ok) location.reload();
  }, { keepalive: true });                                    // survives navigation → delete always lands
  if (isCurrent(id)) {
    const first = document.querySelector('.chat-tab');       // newest remaining chat
    if (first) location.href = TUTOR_BASE + '&chat=' + first.dataset.id;  // switch NOW
    else showEmptyChat();                                    // 0 chats → intro NOW, no reload
  }
}

function armDelete(btn, id) {
  if (btn.dataset.armed) {
    clearTimeout(btn._armTimer);
    delete btn.dataset.armed;
    tutorDelete(id);
    return;
  }
  btn.dataset.armed = '1';
  btn.classList.add('armed');
  btn.innerHTML = ICO.trash;
  btn._armTimer = setTimeout(() => {
    delete btn.dataset.armed;
    btn.classList.remove('armed');
  }, 3000);
}

function bindChip(chip) {
  chip.addEventListener('click', () => {
    const input = document.getElementById('tutor-msg');
    input.value = chip.dataset.q || chip.textContent.trim();
    document.getElementById('tutor-form').dispatchEvent(new Event('submit', { cancelable: true }));
  });
}

function buildEmptyState() {
  const box = document.createElement('div');
  box.className = 'empty-state';
  box.style.cssText = 'margin:26px 10px';
  const ic = document.createElement('div');
  ic.className = 'empty-ic';
  ic.innerHTML = ICO.robot;
  const h = document.createElement('h3');
  h.textContent = 'Ask anything — in Amharic or English';
  const p = document.createElement('p');
  p.className = 'small';
  p.textContent = 'Your personal tutor adapts to your pace. Try one of these:';
  const chips = document.createElement('div');
  chips.className = 'suggest-chips';
  ['Explain recursion like I\'m five', 'What is photosynthesis?', 'Make a quiz on loops', 'Translate \'how are you\' to Amharic', 'Solve 2x + 6 = 14'].forEach(q => {
    const c = document.createElement('button');
    c.type = 'button';
    c.dataset.q = q;
    c.textContent = q.length > 26 ? q.slice(0, 26) + '…' : q;
    bindChip(c);
    chips.appendChild(c);
  });
  box.appendChild(ic); box.appendChild(h); box.appendChild(p); box.appendChild(chips);
  return box;
}

function showEmptyChat() {
  TUTOR_CHAT = 0;
  history.replaceState(null, '', TUTOR_BASE);
  const log = document.getElementById('chat-log');
  if (log) { log.innerHTML = ''; log.appendChild(buildEmptyState()); }
  const title = document.querySelector('.tutor-title');
  if (title) title.innerHTML = ICO.robot + ' New chat';
  const badge = document.getElementById('model-badge');
  if (badge) badge.textContent = 'auto-routed';
  const dot = document.getElementById('model-dot');
  if (dot) dot.classList.remove('pulse');
  const count = document.getElementById('msg-count');
  if (count) count.textContent = '0';
  ['head-rename', 'head-delete'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
}

function tutorNewChat() {
  const b = new URLSearchParams();
  b.set('new_chat', '1');
  tutorPost(b, (res) => {
    if (res.ok && res.id) location.href = TUTOR_BASE + '&chat=' + res.id;
  });
}

function bindTab(tab) {
  tab.addEventListener('click', (e) => {
    if (e.target.closest('button')) return;
    location.href = '<?= url('ai/tutor&chat=') ?>' + tab.dataset.id;
  });
  const ren = tab.querySelector('.ren');
  if (ren) ren.addEventListener('click', (e) => { e.stopPropagation(); tutorRename(tab.dataset.id); });
  const del = tab.querySelector('.del');
  if (del) del.addEventListener('click', (e) => { e.stopPropagation(); armDelete(del, tab.dataset.id); });
}

function ensureChatTab(id, title) {
  let tab = document.querySelector('.chat-tab[data-id="' + id + '"]');
  const name = title.length > 26 ? title.slice(0, 26) + '…' : title;
  if (!tab) {
    tab = document.createElement('div');
    tab.className = 'chat-tab active';
    tab.dataset.id = id;
    const t = document.createElement('span');
    t.className = 'chat-title';
    t.title = title;
    t.innerHTML = ICO.chat + ' ' + esc(name);
    const acts = document.createElement('span');
    acts.className = 'chat-actions';
    const ren = document.createElement('button');
    ren.type = 'button'; ren.className = 'ren'; ren.title = 'Rename'; ren.innerHTML = ICO.edit;
    const del = document.createElement('button');
    del.type = 'button'; del.className = 'del'; del.title = 'Delete'; del.innerHTML = ICO.trash;
    acts.appendChild(ren); acts.appendChild(del);
    tab.appendChild(t); tab.appendChild(acts);
    document.getElementById('chat-list').prepend(tab);
    bindTab(tab);
    bumpCount(1);
  } else {
    tab.querySelector('.chat-title').innerHTML = ICO.chat + ' ' + esc(name);
  }
  document.querySelectorAll('.chat-tab').forEach(x => x.classList.remove('active'));
  tab.classList.add('active');
}

document.querySelectorAll('.chat-tab').forEach(bindTab);
const newBtn = document.getElementById('btn-new-chat');
if (newBtn) newBtn.addEventListener('click', tutorNewChat);
document.querySelectorAll('.suggest-chips button').forEach(chip => {
  chip.addEventListener('click', () => {
    const input = document.getElementById('tutor-msg');
    input.value = chip.dataset.q || chip.textContent.trim();
    document.getElementById('tutor-form').dispatchEvent(new Event('submit', { cancelable: true }));
  });
});
const headRename = document.getElementById('head-rename');
if (headRename) headRename.addEventListener('click', () => tutorRename(TUTOR_CHAT));
const headDelete = document.getElementById('head-delete');
if (headDelete) headDelete.addEventListener('click', () => tutorDelete(TUTOR_CHAT));

document.getElementById('tutor-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const input = document.getElementById('tutor-msg');
  const msg = input.value.trim();
  if (!msg) return;
  const log = document.getElementById('chat-log');
  const sendBtn = document.getElementById('tutor-send');
  const stopBtn = document.getElementById('tutor-stop');
  const badge = document.getElementById('model-badge');
  const dot = document.getElementById('model-dot');
  const countEl = document.getElementById('msg-count');
  const empty = log.querySelector('.empty-state');
  if (empty) empty.remove();
  const me = document.createElement('div');
  me.className = 'chat-msg me';
  const meRow = document.createElement('div');
  meRow.className = 'avatar-bubble';
  const meAv = document.createElement('span');
  meAv.className = 'chat-avatar me';
  meAv.textContent = 'Y';
  const meBubble = document.createElement('div');
  meBubble.className = 'bubble';
  meBubble.textContent = msg;
  meRow.appendChild(meAv);
  meRow.appendChild(meBubble);
  me.appendChild(meRow);
  const meMeta = document.createElement('span');
  meMeta.className = 'meta';
  meMeta.textContent = 'You';
  me.appendChild(meMeta);
  log.appendChild(me);
  const ai = document.createElement('div');
  ai.className = 'chat-msg ai';
  const aiRow = document.createElement('div');
  aiRow.className = 'avatar-bubble';
  const aiAv = document.createElement('span');
  aiAv.className = 'chat-avatar ai';
  aiAv.innerHTML = ICO.robot;
  const aiBubble = document.createElement('div');
  aiBubble.className = 'bubble typing';
  aiBubble.innerHTML = '<span class="typing-dots"><i></i><i></i><i></i></span>';
  aiRow.appendChild(aiAv);
  aiRow.appendChild(aiBubble);
  ai.appendChild(aiRow);
  const aiMeta = document.createElement('span');
  aiMeta.className = 'meta';
  aiMeta.textContent = 'Tutor';
  ai.appendChild(aiMeta);
  log.appendChild(ai);
  log.scrollTop = log.scrollHeight;
  input.value = '';
  sendBtn.disabled = true;
  stopBtn.style.display = '';
  if (badge) badge.textContent = 'thinking…';
  if (dot) dot.classList.add('pulse');
  const controller = new AbortController();
  const stopped = () => {
    aiBubble.classList.remove('typing');
    aiBubble.textContent = (aiBubble.textContent || '') + ' ⏹ (stopped)';
    log.scrollTop = log.scrollHeight;
  };
  stopBtn.onclick = () => { controller.abort(); stopped(); };
  const params = new URLSearchParams();
  params.set('message', msg);
  params.set('chat', TUTOR_CHAT);
  let full = '';
  let named = false;
  const nameChat = () => {
    if (named) return;
    named = true;
    if (TUTOR_CHAT) ensureChatTab(TUTOR_CHAT, msg);
    const h = document.querySelector('.tutor-title');
    if (h) h.innerHTML = ICO.robot + ' ' + esc(msg.length > 40 ? msg.slice(0, 40) + '…' : msg);
  };
  try {
    const resp = await fetch('<?= e(url('ai/tutor/stream')) ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': TUTOR_CSRF },
      body: params.toString(),
      signal: controller.signal
    });
    if (!resp.ok) throw new Error('HTTP ' + resp.status);
    const reader = resp.body.getReader();
    const decoder = new TextDecoder();
    let buf = '';
    let newChatId = TUTOR_CHAT;
    while (true) {
      const { done, value } = await reader.read();
      if (done) break;
      buf += decoder.decode(value, { stream: true });
      const parts = buf.split('\n\n');
      buf = parts.pop();
      for (const part of parts) {
        const line = part.trim();
        if (!line.startsWith('data:')) continue;
        try {
          const evt = JSON.parse(line.slice(5).trim());
          if (evt.delta) {
            if (!full) { aiBubble.classList.remove('typing'); nameChat(); }
            full += evt.delta;
            aiBubble.textContent = full;
            log.scrollTop = log.scrollHeight;
          }
          if (evt.model && !full) { if (badge) badge.textContent = evt.model; }
          if (evt.chat && evt.chat !== TUTOR_CHAT) {
            newChatId = TUTOR_CHAT = evt.chat;                  // auto-created chat: name it right away
            history.replaceState(null, '', '<?= url('ai/tutor&chat=') ?>' + evt.chat);
            nameChat();
          }
        } catch (err) {}
      }
    }
    if (full === '') aiBubble.textContent = '(no response)';
    if (full !== '') {
      ensureChatTab(newChatId, msg);
      const h = document.querySelector('.tutor-title');
      if (h) h.innerHTML = ICO.robot + ' ' + esc(msg.length > 40 ? msg.slice(0, 40) + '…' : msg);
      if (countEl) countEl.textContent = (parseInt(countEl.textContent) || 0) + 2;
    }
  } catch (err) {
    aiBubble.classList.remove('typing');
    if (controller.signal.aborted) {
      if (!full) aiBubble.textContent = '(stopped before any reply)';
    } else {
      aiBubble.textContent = '⚠ Connection error: ' + err.message;
    }
  } finally {
    sendBtn.disabled = false;
    stopBtn.style.display = 'none';
    stopBtn.onclick = null;
    if (dot) dot.classList.remove('pulse');
    input.focus();
  }
});
</script>
