<?php /* AI assistant quick Q&A */
$csrftok = csrf_token();
?>
<div class="page-head">
  <div>
    <h1><?= icon('spark') ?> AI Assistant</h1>
    <p class="sub">Quick questions, instant streaming answers — no chat history needed</p>
  </div>
</div>

<div class="card" style="max-width:680px;margin:0 auto">
  <form id="ask-form" class="flex gap-8" onsubmit="return false">
    <input class="input" style="flex:1" id="ask-q" name="question" placeholder="e.g. What is photosynthesis? / ፕሮግራሚንግ ምንድን ነው?" required>
    <button class="btn btn-primary" id="ask-btn">Ask</button>
    <button type="button" class="btn btn-danger" id="ask-stop" style="display:none"><?= icon('ban-circle') ?> Stop</button>
  </form>
  <div id="ask-out" style="margin-top:16px;display:none">
    <div class="alert alert-info" style="white-space:pre-wrap;margin-bottom:8px" id="ask-answer"></div>
    <p class="tiny faint" id="ask-hint"></p>
  </div>
  <div class="flex gap-8" style="margin-top:14px;flex-wrap:wrap">
    <button class="btn btn-sm btn-ghost" data-sample="explain recursion"><?= icon('refresh') ?> Recursion</button>
    <button class="btn btn-sm btn-ghost" data-sample="make a study plan for exams"><?= icon('calendar') ?> Study plan</button>
    <button class="btn btn-sm btn-ghost" data-sample="grammar check: he go to school"><?= icon('edit') ?> Grammar</button>
  </div>
</div>

<script>
const ASK_CSRF = <?= json_encode($csrftok) ?>;
document.querySelectorAll('[data-sample]').forEach(b => b.addEventListener('click', () => {
  document.getElementById('ask-q').value = b.dataset.sample;
  document.getElementById('ask-form').dispatchEvent(new Event('submit'));
}));
document.getElementById('ask-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const q = document.getElementById('ask-q').value.trim();
  if (!q) return;
  const out = document.getElementById('ask-out');
  const answer = document.getElementById('ask-answer');
  const hint = document.getElementById('ask-hint');
  out.style.display = '';
  answer.classList.add('typing');
  answer.textContent = '…';
  hint.textContent = '';
  document.getElementById('ask-btn').disabled = true;
  const stopBtn = document.getElementById('ask-stop');
  stopBtn.style.display = '';
  const controller = new AbortController();
  let full = '';
  stopBtn.onclick = () => {
    controller.abort();
    hint.textContent = '⏹ Stopped — partial answer kept.';
    if (!full) answer.textContent = '(stopped before any reply)';
  };
  try {
    const resp = await fetch('<?= e(url('ai/assistant/stream')) ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': ASK_CSRF },
      body: new URLSearchParams({ question: q }),
      signal: controller.signal
    });
    if (!resp.ok) throw new Error('HTTP ' + resp.status);
    const reader = resp.body.getReader();
    const decoder = new TextDecoder();
    let buf = '';
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
          if (evt.delta) { full += evt.delta; answer.classList.remove('typing'); answer.textContent = full; }
        } catch (err) {}
      }
    }
    if (full === '') answer.textContent = '(no response)';
  } catch (err) {
    if (!controller.signal.aborted) {
      answer.classList.remove('typing');
      answer.textContent = '⚠ Connection error: ' + err.message;
    }
  } finally {
    document.getElementById('ask-btn').disabled = false;
    stopBtn.style.display = 'none';
    stopBtn.onclick = null;
  }
});
</script>
