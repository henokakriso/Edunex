<?php /* Student notes view */
$editing = null;
if (isset($_GET['edit'])) {
    foreach ($notes as $n) if ((int)$n['id'] === (int)$_GET['edit']) { $editing = $n; break; }
}
?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> My Notes</h1>
    <p class="sub">Personal notes on your courses and lessons</p>
  </div>
</div>

<div class="flex gap-8" style="margin-bottom:18px;flex-wrap:wrap">
  <a class="btn btn-sm <?= !$filterCourse ? 'btn-primary' : '' ?>" href="<?= url('notes') ?>">All</a>
  <?php foreach ($courses as $c): ?>
    <a class="btn btn-sm <?= (int)$filterCourse === (int)$c['id'] ? 'btn-primary' : '' ?>" href="<?= url('notes&course=' . $c['id']) ?>"><?= e($c['title']) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($editing): ?>
  <div class="card" style="margin-bottom:18px">
    <b class="small" style="display:block;margin-bottom:10px">Edit note</b>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="note_id" value="<?= (int)$editing['id'] ?>">
      <input class="input" name="title" placeholder="Title (optional)" value="<?= e($editing['title']) ?>" style="margin-bottom:10px">
      <textarea class="input" name="body" rows="8" style="min-height:160px" placeholder="Write your note…"><?= e($editing['body']) ?></textarea>
      <div class="flex gap-8" style="margin-top:10px">
        <button class="btn btn-primary" type="submit">Save</button>
        <a class="btn" href="<?= url('notes' . ($filterCourse ? '&course=' . $filterCourse : '')) ?>">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php if (!$editing): ?>
  <div class="card" style="margin-bottom:18px">
    <b class="small" style="display:block;margin-bottom:10px">New note</b>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <input class="input" name="title" placeholder="Title (optional)" style="margin-bottom:10px">
      <textarea class="input" name="body" rows="4" placeholder="Write your note…" required></textarea>
      <div class="flex gap-8" style="margin-top:10px">
        <select class="input" name="course_id" style="max-width:260px">
          <option value="">No course</option>
          <?php foreach (Database::all("SELECT id, title FROM courses WHERE school_id = ? AND status = 'published' ORDER BY title", [my_school_id()]) as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$filterCourse === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" type="submit">Save note</button>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php if (!$notes): ?>
  <div class="card"><p class="muted" style="padding:20px">No notes yet. Start writing to remember what you learn!</p></div>
<?php else: ?>
  <div style="display:grid;gap:12px">
    <?php foreach ($notes as $n): ?>
      <div class="card" style="padding:16px;position:relative">
        <div class="flex-between">
          <div>
            <b class="small"><?= e($n['title'] ?: 'Untitled') ?></b>
            <?php if ($n['course_title']): ?><span class="tiny faint"> · <?= e($n['course_title']) ?></span><?php endif; ?>
            <?php if ($n['lesson_title']): ?><span class="tiny faint"> → <?= e($n['lesson_title']) ?></span><?php endif; ?>
          </div>
          <div class="flex gap-6">
            <form method="post" class="inline"><input type="hidden" name="action" value="pin"><input type="hidden" name="note_id" value="<?= (int)$n['id'] ?>"><button class="btn btn-sm" title="Pin"><?= $n['pinned'] ? '📌' : '📍' ?></button></form>
            <a class="btn btn-sm" href="<?= url('notes&edit=' . $n['id'] . ($filterCourse ? '&course=' . $filterCourse : '')) ?>">Edit</a>
            <form method="post" class="inline" data-confirm="Delete this note?"><input type="hidden" name="action" value="delete"><input type="hidden" name="note_id" value="<?= (int)$n['id'] ?>"><button class="btn btn-sm btn-danger"><?= icon('trash') ?></button></form>
          </div>
        </div>
        <div class="prose tiny" style="margin-top:8px;max-height:200px;overflow:auto"><?= nl2br(e($n['body'])) ?></div>
        <p class="tiny faint" style="margin-top:8px">Updated <?= e(date('M j, H:i', strtotime($n['updated_at']))) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
