<?php /* Announcements view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('megaphone') ?> Announcements</h1>
    <p class="sub">Updates from your school and teachers</p>
  </div>
</div>

<div class="flex-col gap-16">
  <?php foreach ($anns as $a): ?>
    <div class="card" style="<?= $a['pinned'] ? 'border-left:4px solid var(--accent)' : '' ?>">
      <b><?= e($a['title']) ?></b>
      <?php if ($a['pinned']): ?><span class="badge badge-accent"><?= icon('pin') ?></span><?php endif; ?>
      <span class="badge badge-muted"><?= e($a['audience']) ?></span>
      <?php if ($a['course_title']): ?><span class="badge badge-accent"><?= e($a['course_title']) ?></span><?php endif; ?>
      <p class="small" style="margin-top:8px;white-space:pre-wrap"><?= nl2br(e($a['content'])) ?></p>
      <p class="tiny faint" style="margin-top:8px">by <?= e($a['author_name']) ?> · <?= e($a['school_name']) ?> · <?= e(time_ago($a['created_at'])) ?></p>
    </div>
  <?php endforeach; ?>
  <?php if (!$anns): ?><div class="alert alert-info">No announcements for you yet.</div><?php endif; ?>
</div>
