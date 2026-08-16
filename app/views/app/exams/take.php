<?php /* Exam taking view */
$qCount = count($questions);
$typeIcons = ['mcq' => icon('target'), 'truefalse' => icon('balance'), 'essay' => icon('note'), 'fill' => icon('type'), 'coding' => icon('monitor'), 'matching' => icon('link'), 'order' => icon('calculator'), 'image' => icon('image'), 'audio' => icon('audio'), 'video' => icon('video')];
?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> <?= e($exam['title']) ?></h1>
    <p class="sub"><?= e($exam['course_title']) ?> · <?= $qCount ?> questions · <?= (int)$exam['duration_min'] ?> minutes · Passing <?= e($exam['passing_score']) ?>%</p>
  </div>
  <div class="flex gap-12">
    <span class="timer" id="exam-timer">--:--</span>
    <span class="badge badge-success" id="autosave-status" title="Your answers are saved automatically">✓ Autosave on</span>
  </div>
</div>

<form method="post" id="exam-form">
  <?= csrf_field() ?>
  <input type="hidden" name="submit_exam" value="1">
  <div class="grid" style="grid-template-columns: 1.6fr 1fr;align-items:start">
    <div class="flex-col gap-16">
      <?php foreach ($questions as $i => $q): ?>
        <div class="card" id="q-<?= (int)$q['id'] ?>" data-qid="<?= (int)$q['id'] ?>">
          <div class="flex-between" style="margin-bottom:14px">
            <span class="badge badge-accent">Question <?= $i + 1 ?> · <?= rtrim(rtrim((string)$q['points'], '0'), '.') ?> pt</span>
            <button type="button" class="btn btn-sm btn-ghost" onclick="EdunexExam.flag(<?= (int)$q['id'] ?>, this, <?= (int)$attempt['id'] ?>, '<?= csrf_token() ?>')"><?= icon('flag') ?> Flag</button>
          </div>
          <?php if (in_array($q['type'], ['image', 'audio', 'video']) && $q['media_path']): ?>
            <div style="margin-bottom:14px">
              <?php if ($q['type'] === 'image'): ?><img src="<?= e(url('file?p=' . $q['media_path'])) ?>" style="max-height:220px;border-radius:12px"><?php endif; ?>
              <?php if ($q['type'] === 'audio'): ?><audio controls src="<?= e(url('file?p=' . $q['media_path'])) ?>" style="width:100%"></audio><?php endif; ?>
              <?php if ($q['type'] === 'video'): ?><video controls src="<?= e(url('file?p=' . $q['media_path'])) ?>" style="width:100%;border-radius:12px"></video><?php endif; ?>
            </div>
          <?php endif; ?>
          <b style="font-size:15px;margin-bottom:16px;display:block"><?= $typeIcons[$q['type']] ?> <?= e($q['question']) ?></b>

          <?php if ($q['type'] === 'mcq'): ?>
            <?php foreach ($q['options'] as $oi => $opt): ?>
              <label class="option-item">
                <input type="radio" name="q_<?= (int)$q['id'] ?>" value="<?= e((string)$opt) ?>">
                <span class="option-radio"></span><span><?= e((string)$opt) ?></span>
              </label>
            <?php endforeach; ?>
          <?php elseif ($q['type'] === 'truefalse'): ?>
            <?php foreach (['True', 'False'] as $tv): ?>
              <label class="option-item">
                <input type="radio" name="q_<?= (int)$q['id'] ?>" value="<?= $tv ?>">
                <span class="option-radio"></span><span><?= $tv ?></span>
              </label>
            <?php endforeach; ?>
          <?php elseif ($q['type'] === 'fill'): ?>
            <input class="input" name="q_<?= (int)$q['id'] ?>" placeholder="Type your answer…">
          <?php elseif ($q['type'] === 'essay'): ?>
            <textarea class="input" name="q_<?= (int)$q['id'] ?>" rows="5" placeholder="Write your answer here…"></textarea>
          <?php elseif ($q['type'] === 'coding'): ?>
            <textarea class="input" name="q_<?= (int)$q['id'] ?>" rows="9" style="font-family:monospace" placeholder="// Write your code here"></textarea>
          <?php elseif ($q['type'] === 'matching'): ?>
            <?php foreach ($q['matching'] as $left => $right): ?>
              <div class="flex gap-8" style="margin-bottom:10px">
                <input class="input" value="<?= e((string)$left) ?>" readonly style="max-width:220px">
                <input type="hidden" name="m_left_<?= (int)$q['id'] ?>[]" value="<?= e((string)$left) ?>">
                <input class="input" name="m_<?= (int)$q['id'] ?>[]" placeholder="Match with…">
              </div>
            <?php endforeach; ?>
          <?php elseif ($q['type'] === 'order'): ?>
            <div class="flex-col gap-8" id="order-<?= (int)$q['id'] ?>">
              <?php foreach ($q['order_items'] as $oi => $item): ?>
                <div class="option-item" style="cursor:grab">
                  <span class="option-radio"></span>
                  <span><?= e((string)$item) ?></span>
                  <input type="hidden" name="q_<?= (int)$q['id'] ?>[]" value="<?= e((string)$item) ?>">
                </div>
              <?php endforeach; ?>
            </div>
            <p class="help">Drag items to arrange in the correct order.</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <button class="btn btn-success btn-lg" style="width:100%" data-confirm="Submit your exam? You can't change answers after submitting."><?= icon('check-circle') ?> Submit exam</button>
    </div>

    <div class="exam-sidebar">
      <div class="card" style="position:sticky;top:90px">
        <b class="small" style="display:block;margin-bottom:10px">Question navigator</b>
        <div class="qnav" style="margin-bottom:14px">
          <?php foreach ($questions as $i => $q): ?>
            <button type="button" data-q="<?= (int)$q['id'] ?>" onclick="document.getElementById('q-<?= (int)$q['id'] ?>').scrollIntoView({behavior:'smooth'})"><?= $i + 1 ?></button>
          <?php endforeach; ?>
        </div>
        <div class="tiny faint flex-col gap-8">
          <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:var(--success);vertical-align:middle"></span> Answered</span>
          <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:var(--warning);vertical-align:middle"></span> Flagged</span>
          <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:var(--bg-elev);border:1px solid var(--border);vertical-align:middle"></span> Not answered</span>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  EdunexExam.start(<?= max(0, $deadline - time()) ?>, <?= (int)$attempt['id'] ?>, '<?= csrf_token() ?>');
  // mark answered
  document.querySelectorAll('#exam-form .option-item input').forEach(inp => {
    inp.addEventListener('change', () => {
      const card = inp.closest('.card');
      const qid = card.dataset.qid;
      card.querySelectorAll('.option-item').forEach(o => o.classList.toggle('selected', o.contains(inp)));
      const nav = document.querySelector(`.qnav button[data-q="${qid}"]`);
      if (nav) nav.classList.add('answered');
    });
  });
  // order drag
  document.querySelectorAll('[id^="order-"]').forEach(zone => {
    const items = [...zone.querySelectorAll('.option-item')];
    items.forEach((item, idx) => {
      item.addEventListener('click', () => {
        const next = (idx + 1) % items.length;
        zone.insertBefore(items[idx], items[next]);
        items.push(items.shift());
      });
    });
  });
});
</script>
