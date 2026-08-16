<?php /* Teacher lesson editor view */
$types = ['notes' => 'Notes / rich text', 'video' => 'Video (URL or file)', 'pdf' => 'PDF file', 'slides' => 'Slides / presentation', 'audio' => 'Audio file', 'link' => 'External link'];
$selectedModule = (int)($_GET['module'] ?? ($lesson['module_id'] ?? 0));
?>
<div class="page-head">
  <div>
    <h1><?= $lesson ? icon('edit') . ' Edit: ' . e($lesson['title']) : icon('plus') . ' New lesson' ?></h1>
    <p class="sub"><?= e($course['title']) ?></p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('teacher/course&id=' . $course['id'])) ?>">← Back to course</a>
</div>

<form method="post" class="card" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="grid2">
    <div class="flex-col">
      <label class="small faint">Title *</label>
      <input class="input" name="title" value="<?= e($lesson['title'] ?? '') ?>" required placeholder="Lesson 1: Introduction">
    </div>
    <div class="flex-col">
      <label class="small faint">Type</label>
      <select class="input" name="type" id="ltype">
        <?php foreach ($types as $k => $v): ?><option value="<?= $k ?>" <?= ($lesson['type'] ?? 'notes') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col">
      <label class="small faint">Module</label>
      <select class="input" name="module_id" required>
        <?php foreach ($modules as $m): ?><option value="<?= (int)$m['id'] ?>" <?= $selectedModule == $m['id'] ? 'selected' : '' ?>><?= e($m['title']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col">
      <label class="small faint">Duration (minutes)</label>
      <input class="input" type="number" name="duration_min" value="<?= (int)($lesson['duration_min'] ?? 10) ?>" min="0">
    </div>
    <div class="flex-col" id="url-block" style="grid-column:1/-1">
      <label class="small faint">Video / external URL</label>
      <input class="input" name="video_url" value="<?= e($lesson['video_url'] ?? '') ?>" placeholder="https://youtube.com/watch?v=… or https://…">
    </div>
    <div class="flex-col" id="file-block" style="grid-column:1/-1">
      <label class="small faint">File (PDF / slides / audio / video)</label>
      <input class="input" type="file" name="file" accept=".pdf,.ppt,.pptx,.mp3,.wav,.ogg,.mp4,.webm">
      <?php if ($lesson && $lesson['file_path']): ?><p class="tiny faint" style="margin-top:6px">Current: <?= e($lesson['file_path']) ?></p><?php endif; ?>
    </div>
    <div class="flex-col" style="grid-column:1/-1" id="content-block">
      <label class="small faint">Lesson content (Markdown)</label>
      <textarea class="input" name="content" rows="12" placeholder="Write your lesson content here…"><?= e($lesson['content'] ?? '') ?></textarea>
    </div>
  </div>
  <button class="btn btn-primary btn-lg"><?= icon('save') ?> <?= $lesson ? 'Save changes' : 'Create lesson' ?></button>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const t = document.getElementById('ltype');
  const upd = () => {
    const v = t.value;
    document.getElementById('url-block').style.display = ['video','link'].includes(v) ? '' : 'none';
    document.getElementById('file-block').style.display = ['pdf','slides','audio','video'].includes(v) ? '' : 'none';
    document.getElementById('content-block').style.display = ['video','link'].includes(v) ? 'none' : '';
  };
  t.addEventListener('change', upd); upd();
});
</script>
