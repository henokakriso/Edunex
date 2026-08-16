<?php /* Teacher book-generation job: live progress + stop + result */
$state = $state['state'] ?? 'running';
$running = in_array($state, ['running', 'starting'], true);
?>
<div class="page-head">
  <div>
    <h1><?= icon('bolt') ?> Generating course…</h1>
    <p class="sub">The C AI engine is working — you can watch progress live and stop it any time</p>
  </div>
</div>

<div class="card" style="max-width:680px">
  <?php if ($result): ?>
    <?php $ok = ($result['state'] ?? '') === 'done'; ?>
    <h3 style="margin-top:0"><?= $ok ? icon('check-circle', 'success') : icon('ban-circle') ?>
      <?= $ok ? 'Course created: ' . e($result['title'] ?? '') : 'Generation stopped' ?></h3>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin:14px 0">
      <div class="stat-box"><span class="tiny faint">Lessons</span><b><?= (int)($result['lessons'] ?? 0) ?></b></div>
      <div class="stat-box"><span class="tiny faint">Exam questions</span><b><?= (int)($result['questions'] ?? 0) ?></b></div>
      <div class="stat-box"><span class="tiny faint">Engine</span><b style="font-size:.8rem">C / llama.cpp</b></div>
    </div>
    <?php if (($result['state'] ?? '') === 'cancelled'): ?>
      <div class="alert alert-warning"><?= icon('alert') ?> Stopped early — inserted the <?= (int)($result['questions'] ?? 0) ?> questions that were already generated.</div>
    <?php endif; ?>
    <?php if ($result['summary']): ?>
      <p class="small faint"><?= e($result['summary']) ?></p>
    <?php endif; ?>
    <div class="flex gap-12" style="margin-top:12px">
      <a class="btn btn-primary" href="<?= e(url('teacher/course&id=' . (int)$result['course_id'])) ?>">Open course →</a>
      <a class="btn btn-ghost" href="<?= e(url('teacher/book')) ?>">Generate another</a>
    </div>
  <?php else: ?>
    <h3 style="margin-top:0"><?= icon('loader') ?> Preparing job…</h3>
    <div id="job-stage" class="small" style="margin:10px 0">Reading PDF and starting the C engine…</div>
    <div class="progress"><div id="job-bar" class="progress-fill" style="width:4%"></div></div>
    <div id="job-count" class="tiny faint" style="margin-top:6px"></div>
    <div class="flex gap-12" style="margin-top:16px">
      <button id="job-stop" class="btn btn-danger"><?= icon('ban-circle') ?> Stop generation</button>
      <a class="btn btn-ghost" href="<?= e(url('teacher/book')) ?>">Back</a>
    </div>
    <div id="job-done" style="display:none;margin-top:14px"></div>
  <?php endif; ?>
</div>

<?php if (!$result): ?>
<script>
(() => {
  const job = <?= json_encode($job) ?>;
  const stage = document.getElementById('job-stage');
  const bar = document.getElementById('job-bar');
  const count = document.getElementById('job-count');
  const stop = document.getElementById('job-stop');
  const doneBox = document.getElementById('job-done');
  const CSRF = <?= json_encode(csrf_token()) ?>;
  let finished = false;

  stop.addEventListener('click', async () => {
    stop.disabled = true;
    stop.textContent = 'Stopping…';
    try {
      await fetch('<?= e(url('ai/job/cancel')) ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': CSRF },
        body: 'job=' + encodeURIComponent(job)
      });
    } catch (e) {}
    stage.textContent = 'Stopping the C engine… (may take a moment)';
  });

  const labels = { summary: 'Writing the course summary…', questions: 'Generating exam questions…', starting: 'Starting…', idle: '…' };
  const es = new EventSource('<?= e(url('ai/job/progress&job=')) ?>' + job);
  es.onmessage = (ev) => {
    let m = {};
    try { m = JSON.parse(ev.data); } catch (e) { return; }
    if (labels[m.stage]) stage.textContent = labels[m.stage];
    if (m.total > 0 && m.stage === 'questions') {
      const pct = Math.min(99, Math.round(m.cur / m.total * 100));
      bar.style.width = pct + '%';
      count.textContent = 'Question ' + m.cur + ' of ' + m.total;
    }
    if (m.done) {
      es.close();
      finished = true;
      if (m.state === 'cancelled') {
        stage.textContent = 'Stopped.';
        bar.style.width = '100%';
        bar.style.background = 'var(--warning)';
        count.textContent = 'Generation cancelled';
        stop.style.display = 'none';
        location.reload();
      } else if (m.state === 'done' && m.result && m.result.course_id) {
        location.reload();
      } else if (m.state === 'error') {
        stage.textContent = 'The AI engine had an error — falling back to the built-in generator…';
        stop.style.display = 'none';
        location.reload();
      }
    }
  };
  es.onerror = () => { if (!finished) { es.close(); setTimeout(() => location.reload(), 1500); } };
})();
</script>
<?php endif; ?>
