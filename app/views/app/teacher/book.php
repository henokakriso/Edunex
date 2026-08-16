<?php /* Teacher PDF book → course generator view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('books') ?> Book → Course Generator</h1>
    <p class="sub">Upload a PDF textbook — Edunex builds a course with lessons and an auto-generated chapter test in minutes</p>
  </div>
  <span class="badge badge-success">AI: <?= e(Model::provider()->name()) ?></span>
</div>

<?php if ($result): ?>
<div class="card" style="border-color:var(--success);margin-bottom:18px">
  <h3><?= icon('check-circle') ?> Course created: <?= e($result['title']) ?></h3>
  <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin:14px 0">
    <div class="stat-box"><span class="tiny faint">Lessons</span><b><?= (int)$result['lessons'] ?></b></div>
    <div class="stat-box"><span class="tiny faint">Exam questions</span><b><?= (int)$result['questions'] ?></b></div>
    <div class="stat-box"><span class="tiny faint">Words extracted</span><b><?= number_format((int)$result['words']) ?></b></div>
    <div class="stat-box"><span class="tiny faint">Engine</span><b style="font-size:.8rem"><?= e($result['provider']) ?></b></div>
  </div>
  <p class="small faint"><?= e($result['summary']) ?></p>
  <div class="flex gap-12" style="margin-top:12px">
    <a class="btn btn-primary" href="<?= e(url('teacher/course&id=' . $result['course_id'])) ?>">Open course →</a>
    <a class="btn btn-ghost" href="<?= e(url('teacher/book')) ?>">Generate another</a>
  </div>
</div>
<?php endif; ?>

<div class="card" style="max-width:640px">
  <form method="post" enctype="multipart/form-data" class="flex-col gap-12">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <?php if (!$subjects): ?>
      <div class="alert alert-warning"><?= icon('lock') ?> No subjects are assigned to you yet. Ask your director to authorise the subjects you teach before generating courses.</div>
      <button class="btn btn-primary" disabled><?= icon('bolt') ?> Generate course + exam</button>
    <?php else: ?>
    <div class="field">
      <label>Course title</label>
      <input class="input" name="title" placeholder="e.g. Biology Grade 11 — Textbook" required>
    </div>
    <div class="field">
      <label>Subject * (only your authorised subjects)</label>
      <select class="input" name="subject_id" required>
        <option value="">— Choose subject —</option>
        <?php foreach ($subjects as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <div class="field" style="flex:1">
        <label>PDF file</label>
        <input class="input" type="file" name="pdf" accept="application/pdf" required>
      </div>
      <div class="field" style="flex:1">
        <label>Level (optional)</label>
        <input class="input" name="level" placeholder="e.g. Grade 11">
      </div>
    </div>
    <div class="form-row">
      <div class="field" style="flex:1">
        <label>Lessons (3–25)</label>
        <input class="input" type="number" name="lesson_count" value="10" min="3" max="25">
      </div>
      <div class="field" style="flex:1">
        <label>Exam questions (3–20)</label>
        <input class="input" type="number" name="question_count" value="10" min="3" max="20">
      </div>
    </div>
    <p class="tiny faint">Scanned/image-only PDFs cannot be read. For best results use text-based textbooks.</p>
    <button class="btn btn-primary"><?= icon('bolt') ?> Generate course + exam</button>
    <?php endif; ?>
  </form>
</div>
