<?php /* Exam builder/editor view */
$qt = ['mcq' => 'Multiple choice', 'truefalse' => 'True / False', 'essay' => 'Essay', 'fill' => 'Fill in the blank', 'coding' => 'Coding', 'matching' => 'Matching', 'order' => 'Ordering', 'image' => 'Image-based', 'audio' => 'Audio-based', 'video' => 'Video-based'];
?>
<div class="page-head">
  <div>
    <h1><?= $isNew ? icon('plus') . ' New exam' : icon('edit') . ' ' . e($exam['title']) ?></h1>
    <p class="sub">Questions auto-grade for choice types; essays and coding are graded manually.</p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('teacher/exams')) ?>">← Back</a>
</div>

<form method="post" class="flex-col gap-16" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="card">
    <h3 class="card-title"><?= icon('doc') ?> Exam settings</h3>
    <div class="grid2">
      <div class="flex-col">
        <label class="small faint">Course * (only your authorised subjects)</label>
        <select class="input" name="course_id" required>
          <option value="">— Choose course —</option>
          <?php if (isset($exam['course_id']) && $exam['course_id'] && !in_array($exam['course_id'], array_column($myCourses, 'id'), true)): ?>
            <option value="<?= (int)$exam['course_id'] ?>" selected><?= e($exam['course_title'] ?? 'Current course') ?> (outside authorised subjects — keep or change)</option>
          <?php endif; ?>
          <?php foreach ($myCourses as $c): ?><option value="<?= (int)$c['id'] ?>" <?= isset($exam['course_id']) && $exam['course_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?> (<?= e($c['subject_name']) ?>)</option><?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Title *</label>
        <input class="input" name="title" value="<?= e($exam['title'] ?? '') ?>" placeholder="Mid-term Exam — Chapter 1" required>
      </div>
      <div class="flex-col">
        <label class="small faint">Duration (minutes)</label>
        <input class="input" type="number" name="duration_min" value="<?= (int)($exam['duration_min'] ?? 60) ?>" min="1">
      </div>
      <div class="flex-col">
        <label class="small faint">Passing score (%)</label>
        <input class="input" type="number" name="passing_score" value="<?= (int)($exam['passing_score'] ?? 50) ?>" min="0" max="100">
      </div>
      <div class="flex-col">
        <label class="small faint">Opens at</label>
        <input class="input" type="datetime-local" name="starts_at" value="<?= e(str_replace(' ', 'T', $exam['starts_at'] ?? date('Y-m-d H:i'))) ?>">
      </div>
      <div class="flex-col">
        <label class="small faint">Closes at</label>
        <input class="input" type="datetime-local" name="ends_at" value="<?= e(str_replace(' ', 'T', $exam['ends_at'] ?? date('Y-m-d H:i', time() + 86400))) ?>">
      </div>
      <div class="flex-col">
        <label class="small faint">Randomize questions</label>
        <select class="input" name="randomize"><option value="1" <?= !empty($exam['randomize']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($exam['randomize']) ? 'selected' : '' ?>>No</option></select>
      </div>
      <div class="flex-col">
        <label class="small faint">Shuffle answer choices</label>
        <select class="input" name="shuffle_options"><option value="1" <?= !empty($exam['shuffle_options']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($exam['shuffle_options']) ? 'selected' : '' ?>>No</option></select>
      </div>
      <div class="flex-col">
        <label class="small faint">Show results to students after grading</label>
        <select class="input" name="show_result"><option value="1" <?= !empty($exam['show_result']) ? 'selected' : '' ?>>Yes</option><option value="0" <?= empty($exam['show_result']) ? 'selected' : '' ?>>No</option></select>
      </div>
      <div class="flex-col">
        <label class="small faint">Grading mode</label>
        <select class="input" name="auto_grade">
          <option value="1" <?= !empty($exam['auto_grade']) ? 'selected' : '' ?>>Auto-grade all</option>
          <option value="0" <?= empty($exam['auto_grade']) ? 'selected' : '' ?>>Manual review (teacher grades every answer)</option>
        </select>
      </div>
      <div class="flex-col" style="grid-column:1/-1">
        <label class="small faint">Instructions for students</label>
        <textarea class="input" name="instructions" rows="2"><?= e($exam['description'] ?? '') ?></textarea>
      </div>
    </div>
    <button class="btn btn-primary" name="save_exam" value="<?= (int)($exam['id'] ?? 0) ?>"><?= icon('save') ?> Save settings</button>
  </div>
</form>

<div class="card">
  <h3 class="card-title"><?= icon('help') ?> Questions (<?= count($questions) ?>)</h3>

  <?php if (!empty($qBlocked)): ?>
    <div class="empty-state" style="margin-top:16px">
      <div class="empty-ic"><?= icon('lock') ?></div>
      <h3>Question builder is locked</h3>
      <p class="small"><?= nl2br(e($qBlockMsg)) ?></p>
      <?php if (!$exam): ?>
        <div class="empty-steps">
          <div><span class="step-n">1</span><span>Choose a <b>Course</b> — only courses in the subjects you are authorised to teach.</span></div>
          <div><span class="step-n">2</span><span>Click <b>Save settings</b> above — the exam is created and becomes editable.</span></div>
          <div><span class="step-n">3</span><span>The question builder unlocks right here, below the settings.</span></div>
        </div>
      <?php else: ?>
        <p class="small faint" style="margin-top:10px">Existing questions stay visible, but this exam is frozen for editing until it points to one of your authorised subjects.</p>
      <?php endif; ?>
    </div>
  <?php else: ?>

  <form method="post" class="card" style="margin-bottom:20px" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="exam_id" value="<?= (int)($exam['id'] ?? 0) ?>">
    <div class="grid2">
      <div class="flex-col">
        <label class="small faint">Question type</label>
        <select class="input" name="qtype" id="qtype">
          <?php foreach ($qt as $k => $v): ?><option value="<?= $k ?>" <?= $k === ($qPreview['type'] ?? 'mcq') ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Points</label>
        <input class="input" type="number" name="points" value="2" min="0.5" step="0.5">
      </div>
      <div class="flex-col" style="grid-column:1/-1">
        <label class="small faint">Question text *</label>
        <textarea class="input" name="question" rows="2" required placeholder="What is…?"></textarea>
      </div>
      <div class="flex-col" id="opt-block" style="grid-column:1/-1">
        <label class="small faint">Answer choices (one per line; first is the correct one)</label>
        <textarea class="input" name="options" rows="4" id="opt-text" placeholder="Addis Ababa
Dire Dawa
Gondar"></textarea>
      </div>
      <div class="flex-col" id="match-block" style="grid-column:1/-1;display:none">
        <label class="small faint">Matching pairs (format: left | right, one pair per line)</label>
        <textarea class="input" name="matching" rows="4" placeholder="3 + 4 | 7
5 × 5 | 25"></textarea>
      </div>
      <div class="flex-col" id="order-block" style="grid-column:1/-1;display:none">
        <label class="small faint">Ordering items (one per line, in the correct order)</label>
        <textarea class="input" name="order_items" rows="4" placeholder="First
Second
Third"></textarea>
      </div>
      <div class="flex-col" id="ans-block" style="grid-column:1/-1">
        <label class="small faint">Correct answer (for fill-in / essay guidance)</label>
        <input class="input" name="correct_answer" placeholder="e.g. 42">
      </div>
      <div class="flex-col">
        <label class="small faint">Media file (image/audio/video)</label>
        <input class="input" type="file" name="media" accept="image/*,audio/*,video/*">
      </div>
      <div class="flex-col">
        <label class="small faint">Explanation (shown after grading)</label>
        <input class="input" name="explanation" placeholder="Optional — why this answer is correct">
      </div>
    </div>
    <button class="btn btn-success" name="add_question" value="1"><?= icon('plus') ?> Add question</button>
  </form>

  <?php endif; /* qBlocked */ ?>

  <?php foreach ($questions as $i => $q): ?>
    <div class="card" style="border-left:3px solid var(--accent);margin-bottom:12px">
      <div class="flex-between">
        <div>
          <b><?= $i + 1 ?>. <?= e($q['question']) ?></b>
          <span class="badge badge-accent"><?= $qt[$q['type']] ?? $q['type'] ?></span>
          <span class="badge"><?= rtrim(rtrim((string)$q['points'], '0'), '.') ?> pts</span>
        </div>
        <div class="flex gap-8">
          <a class="btn btn-sm btn-ghost" href="#q-<?= (int)$q['id'] ?>" onclick="event.preventDefault();Edunex.editQuestion(<?= htmlspecialchars(json_encode($q), ENT_QUOTES) ?>)"><?= icon('edit') ?></a>
          <form method="post" class="inline" data-confirm="Delete this question?">
            <?= csrf_field() ?><input type="hidden" name="delete_question" value="<?= (int)$q['id'] ?>">
            <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
          </form>
        </div>
      </div>
      <?php if ($q['options']): ?><p class="small faint" style="margin-top:6px"><?= e(is_array($q['options']) ? implode(' · ', $q['options']) : (string)$q['options']) ?></p><?php endif; ?>
      <?php if ($q['correct_answer']): ?><p class="small" style="margin-top:4px"><span class="faint">Correct:</span> <?= e((string)$q['correct_answer']) ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const qtype = document.getElementById('qtype');
  const upd = () => {
    const t = qtype.value;
    document.getElementById('opt-block').style.display = ['mcq','truefalse','image','audio','video'].includes(t) ? '' : 'none';
    document.getElementById('match-block').style.display = t === 'matching' ? '' : 'none';
    document.getElementById('order-block').style.display = t === 'order' ? '' : 'none';
    document.getElementById('ans-block').style.display = t === 'matching' || t === 'order' ? 'none' : '';
    document.getElementById('opt-text').required = ['mcq','truefalse'].includes(t);
  };
  qtype.addEventListener('change', upd); upd();
  Edunex.editQuestion = (q) => {
    const f = document.getElementById('edit-qform');
    f.querySelector('textarea[name="question"]').value = q.question;
    f.querySelector('input[name="points"]').value = q.points;
    f.querySelector('input[name="correct_answer"]').value = q.correct_answer || '';
    f.querySelector('input[name="explanation"]').value = q.explanation || '';
    f.querySelector('select[name="qtype"]').value = q.type; upd();
    f.querySelector('textarea[name="options"]').value = (Array.isArray(q.options) ? q.options.join('\n') : (q.options || ''));
    f.querySelector('textarea[name="matching"]').value = q.matching ? Object.entries(q.matching).map(([l,r]) => l + ' | ' + r).join('\n') : '';
    f.querySelector('textarea[name="order_items"]').value = Array.isArray(q.order_items) ? q.order_items.join('\n') : (q.order_items || '');
    f.querySelector('input[name="qid"]').value = q.id;
    f.querySelector('button').innerHTML = <?= json_encode(icon('save')) ?> + ' Update question';
    f.style.display = '';
    f.scrollIntoView({behavior:'smooth'});
  };
});
</script>

<form method="post" class="card" id="edit-qform" enctype="multipart/form-data" style="display:<?= $editing ? '' : 'none' ?>;border:2px solid var(--accent)">
  <?= csrf_field() ?><input type="hidden" name="qid" value="<?= (int)($editing['id'] ?? 0) ?>">
  <h3 class="card-title"><?= icon('edit') ?> Editing question</h3>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">Question type</label>
      <select class="input" name="qtype" id="qtype-edit">
        <?php foreach ($qt as $k => $v): ?><option value="<?= $k ?>" <?= $editing && $editing['type'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col"><label class="small faint">Points</label><input class="input" type="number" step="0.5" name="points" value="<?= (float)($editing['points'] ?? 1) ?>"></div>
    <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Question text</label><textarea class="input" name="question" rows="2" required><?= e($editing['question'] ?? '') ?></textarea></div>
    <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Options (one per line)</label><textarea class="input" name="options" rows="4"><?= e($editing && is_array($editing['options']) ? implode("\n", $editing['options']) : '') ?></textarea></div>
    <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Matching pairs (left | right)</label><textarea class="input" name="matching" rows="4"><?= e($editing && $editing['type'] === 'matching' && is_array($editing['matching']) ? implode("\n", array_map(fn($l, $r) => $l . ' | ' . $r, array_keys($editing['matching']), $editing['matching'])) : '') ?></textarea></div>
    <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Ordering items (one per line)</label><textarea class="input" name="order_items" rows="4"><?= e($editing && is_array($editing['order_items']) ? implode("\n", $editing['order_items']) : '') ?></textarea></div>
    <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Correct answer</label><input class="input" name="correct_answer" value="<?= e((string)($editing['correct_answer'] ?? '')) ?>"></div>
    <div class="flex-col"><label class="small faint">Explanation</label><input class="input" name="explanation" value="<?= e((string)($editing['explanation'] ?? '')) ?>"></div>
  </div>
  <div class="flex gap-8">
    <button class="btn btn-primary" name="update_question" value="1"><?= icon('save') ?> Update</button>
    <button type="button" class="btn btn-ghost" onclick="document.getElementById('edit-qform').style.display='none'">Cancel</button>
  </div>
</form>

<?php if ($preview): ?>
<div class="card" style="border:2px dashed var(--accent)">
  <h3 class="card-title"><?= icon('eye') ?> Student preview</h3>
  <?php foreach ($questions as $i => $q): ?>
    <div style="margin-bottom:18px">
      <b><?= $i + 1 ?>. <?= e($q['question']) ?></b>
      <?php if ($q['options']): foreach ((array)$q['options'] as $opt): ?>
        <label class="option-item"><span class="option-radio"></span><span><?= e((string)$opt) ?></span></label>
      <?php endforeach; endif; ?>
      <?php if ($q['correct_answer']): ?><p class="small faint" style="margin-top:6px">Answer: <?= e((string)$q['correct_answer']) ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
