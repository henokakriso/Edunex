<?php /* Teacher course manager view */
$lessonTypes = ['notes' => icon('note') . ' Notes', 'video' => icon('video') . ' Video', 'pdf' => icon('file') . ' PDF', 'slides' => icon('image') . ' Slides', 'audio' => icon('audio') . ' Audio', 'link' => icon('link') . ' Link'];
?>
<div class="page-head">
  <div>
    <h1><?= icon('gear') ?> <?= e($course['title']) ?></h1>
    <p class="sub"><?= e($course['code'] ?: '') ?> · Manage modules, lessons and enrollments</p>
  </div>
  <div class="flex gap-8">
    <form method="post" class="inline">
      <?= csrf_field() ?>
      <button class="btn <?= $course['status'] === 'published' ? 'btn-warning' : 'btn-success' ?>" name="toggle_publish" value="1">
        <?= $course['status'] === 'published' ? icon('eye-off') . ' Unpublish' : icon('rocket') . ' Publish' ?>
      </button>
    </form>
    <a class="btn btn-ghost" href="<?= e(url('teacher/exam&new=1')) ?>"><?= icon('note') ?> New exam</a>
    <a class="btn btn-ghost" href="<?= e(url('teacher/courses')) ?>">← Back</a>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.5fr 1fr;gap:22px;align-items:start">
  <div class="flex-col gap-16">
    <div class="card">
      <h3 class="card-title"><?= icon('doc') ?> Course info</h3>
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="grid2">
          <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" value="<?= e($course['title']) ?>" required></div>
          <div class="flex-col"><label class="small faint">Code</label><input class="input" name="code" value="<?= e($course['code']) ?>"></div>
          <div class="flex-col"><label class="small faint">Grade level</label><input class="input" name="level" value="<?= e($course['level']) ?>"></div>
          <div class="flex-col"><label class="small faint">Price (ETB)</label><input class="input" type="number" name="price" step="0.01" value="<?= (float)$course['price'] ?>"></div>
          <div class="flex-col"><label class="small faint">Status</label>
            <select class="input" name="status"><option value="draft" <?= $course['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= $course['status'] === 'published' ? 'selected' : '' ?>>Published</option><option value="archived" <?= $course['status'] === 'archived' ? 'selected' : '' ?>>Archived</option></select>
          </div>
          <div class="flex-col"><label class="small faint">Cover image</label><input class="input" type="file" name="image" accept="image/*"></div>
          <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Description</label><textarea class="input" name="description" rows="3"><?= e($course['description']) ?></textarea></div>
        </div>
        <button class="btn btn-primary" name="update_course" value="1"><?= icon('save') ?> Save</button>
      </form>
    </div>

    <?php foreach ($modules as $i => $m): ?>
      <div class="card">
        <div class="flex-between">
          <h3 class="card-title" style="margin:0"><?= icon('box') ?> <?= e($m['title']) ?> <span class="badge"><?= count($m['lessons']) ?></span></h3>
          <form method="post" class="inline" data-confirm="Delete this module and its lessons?">
            <?= csrf_field() ?><input type="hidden" name="delete_module" value="<?= (int)$m['id'] ?>">
            <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
          </form>
        </div>
        <div class="flex-col gap-8" style="margin-top:12px">
          <?php foreach ($m['lessons'] as $l): ?>
            <div class="list-row" style="padding:8px 10px">
              <span><?= $lessonTypes[$l['type']] ?? icon('file') ?></span>
              <span class="flex-1 small"><b><?= e($l['title']) ?></b><?= $l['duration_min'] ? ' · ' . (int)$l['duration_min'] . ' min' : '' ?></span>
              <a class="btn btn-sm btn-ghost" href="<?= e(url('teacher/lesson&course=' . $course['id'] . '&id=' . $l['id'])) ?>"><?= icon('edit') ?></a>
              <form method="post" class="inline" data-confirm="Delete this lesson?">
                <?= csrf_field() ?><input type="hidden" name="delete_lesson" value="<?= (int)$l['id'] ?>">
                <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
              </form>
            </div>
          <?php endforeach; ?>
          <a class="btn btn-sm" href="<?= e(url('teacher/lesson&course=' . $course['id'] . '&module=' . $m['id'])) ?>">+ Add lesson</a>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="card">
      <h3 class="card-title"><?= icon('plus') ?> Add module</h3>
      <form method="post" class="flex gap-8">
        <?= csrf_field() ?>
        <input class="input flex-1" name="module_name" placeholder="Module name, e.g. Chapter 1 — Numbers">
        <button class="btn btn-success" name="add_module" value="1">Add</button>
      </form>
    </div>
  </div>

  <div class="card" style="position:sticky;top:90px">
    <h3 class="card-title" style="margin-top:0"><?= icon('user') ?>‍<?= icon('graduation') ?> Enrollments (<?= count($enrollments) ?>)</h3>
    <?php if (!$enrollments): ?><p class="muted small">No students enrolled yet.</p><?php endif; ?>
    <?php foreach (array_slice($enrollments, 0, 12) as $e2): ?>
      <div class="list-row" style="padding:8px 0">
        <div class="avatar"><?= mb_substr($e2['first_name'], 0, 1) ?><?= mb_substr($e2['last_name'], 0, 1) ?></div>
        <div class="flex-1 small">
          <b><?= e($e2['first_name'] . ' ' . $e2['last_name']) ?></b>
          <div class="progress" style="height:6px;margin-top:6px"><div style="width:<?= (float)$e2['progress'] ?>%"></div></div>
          <span class="tiny faint"><?= (float)$e2['progress'] ?>% <?= $e2['completed'] ? '· ' . icon('check-circle') . ' completed' : '' ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
