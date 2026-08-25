<?php /* Course learning view with sidebar lesson list, resume, bookmarks */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('book') ?> <?= e($lesson['title']) ?></h1>
    <p class="sub"><?= e($course['title']) ?> · <?= $done ?>/<?= $total ?> lessons · <?= $total ? round($done / $total * 100) : 0 ?>%</p>
  </div>
  <div class="flex gap-8">
    <?php if (!empty($readonly)): ?>
      <span class="badge badge-info" style="padding:7px 12px"><?= icon('eye') ?> Read-only preview</span>
    <?php else: ?>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="bookmark">
        <button class="btn btn-sm <?= $bookmarked ? 'btn-soft' : '' ?>"><?= $bookmarked ? icon('bookmark') . ' Bookmarked' : icon('bookmark') . ' Bookmark' ?></button>
      </form>
      <a class="btn btn-sm" href="<?= url('notes&course=' . $course['id']) ?>"><?= icon('note') ?> Notes</a>
    <?php endif; ?>
  </div>
</div>

<div class="grid" style="grid-template-columns: 300px 1fr;align-items:start">
  <div class="card exam-sidebar" style="padding:14px">
    <b class="small" style="padding:6px 8px;display:block">Course content</b>
    <?php foreach ($modules as $m): ?>
      <div class="nav-section" style="padding:10px 8px 4px"><?= e($m['title']) ?></div>
      <?php foreach ($m['lessons'] as $l): ?>
        <?php
          $lp = Database::one("SELECT * FROM lesson_progress WHERE user_id = ? AND lesson_id = ?", [me()['id'], $l['id']]);
          $active = (int)$l['id'] === (int)$lesson['id'];
        ?>
        <a class="nav-item <?= $active ? 'active' : '' ?>" href="<?= url('index.php?r=courses/learn&id=' . $course['id'] . '&lesson=' . $l['id']) ?>" style="font-size:12.5px;padding:7px 8px">
          <span class="ico"><?= $lp && $lp['completed'] ? icon('check-circle') : match ($l['type']) { 'video' => icon('video'), 'pdf' => icon('file'), 'audio' => icon('audio'), default => icon('note') } ?></span>
          <span class="truncate"><?= e($l['title']) ?></span>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>

  <div class="card" style="min-height:420px">
    <?php if ($lesson['type'] === 'video' && ($lesson['video_url'] || $lesson['file_path'])): ?>
      <video controls style="width:100%;border-radius:12px;margin-bottom:18px" src="<?= $lesson['video_url'] ? e($lesson['video_url']) : e(url('file?p=' . $lesson['file_path'])) ?>"></video>
    <?php elseif ($lesson['type'] === 'audio' && $lesson['file_path']): ?>
      <audio controls style="width:100%;margin-bottom:18px" src="<?= e(url('file?p=' . $lesson['file_path'])) ?>"></audio>
    <?php elseif ($lesson['type'] === 'pdf' && $lesson['file_path']): ?>
      <iframe src="<?= e(url('file?p=' . $lesson['file_path'])) ?>" style="width:100%;height:520px;border:1px solid var(--border);border-radius:12px;margin-bottom:18px"></iframe>
    <?php elseif ($lesson['type'] === 'link' && $lesson['video_url']): ?>
      <a class="btn btn-primary" target="_blank" href="<?= e($lesson['video_url']) ?>">Open external link ↗</a>
    <?php endif; ?>

    <div class="prose" style="font-size:14.5px;line-height:1.75">
      <?= $lesson['content'] ?: '<p class="muted">The teacher hasn\'t added content for this lesson yet.</p>' ?>
    </div>

    <div class="flex-between" style="margin-top:26px;padding-top:18px">
      <?php
        $prevL = null; $nextL = null;
        $ids = array_keys($allLessons);
        $pos = array_search((int)$lesson['id'], $ids, true);
        if ($pos !== false) { $nextL = $ids[$pos + 1] ?? null; $prevL = $ids[$pos - 1] ?? null; }
      ?>
      <div>
        <?php if ($prevL): ?><a class="btn btn-sm" href="<?= url('index.php?r=courses/learn&id=' . $course['id'] . '&lesson=' . $prevL) ?>">← Previous</a><?php endif; ?>
      </div>
      <?php if (empty($readonly)): ?>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="complete">
          <button class="btn btn-primary"><?= icon('check-circle') ?> Mark as complete (+10 XP)</button>
        </form>
      <?php endif; ?>
      <div>
        <?php if ($nextL): ?><a class="btn btn-sm" href="<?= url('index.php?r=courses/learn&id=' . $course['id'] . '&lesson=' . $nextL) ?>">Next →</a><?php endif; ?>
      </div>
    </div>
  </div>
</div>
