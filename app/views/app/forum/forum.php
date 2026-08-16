<?php /* Forum topics list */
?>
<div class="page-head">
  <div>
    <h1><?= icon('chat') ?> Course Forum<?= $course ? ' — ' . e($course['title']) : '' ?></h1>
    <p class="sub">Ask questions, discuss lessons, help classmates</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('new-topic').style.display='block';this.style.display='none'">+ New topic</button>
</div>

<?php if ($course): ?>
  <form method="post" class="card" id="new-topic" style="display:none;margin-bottom:18px">
    <?= csrf_field() ?>
    <h3 class="card-title" style="margin-top:0"><?= icon('edit') ?> Start a discussion</h3>
    <input class="input" name="title" placeholder="Topic title" required>
    <textarea class="input" style="margin-top:10px;min-height:90px" name="body" placeholder="What would you like to discuss?"></textarea>
    <?php if ($__u['role'] === 'teacher'): ?><label class="small faint" style="margin-top:8px;display:block"><input type="checkbox" name="pinned" value="1"> Pin to top</label><?php endif; ?>
    <button class="btn btn-success" style="margin-top:10px" name="new_topic" value="1"><?= icon('megaphone') ?> Post</button>
  </form>
<?php endif; ?>

<div class="flex-col gap-10">
  <?php foreach ($topics as $t): ?>
    <div class="card list-row" style="padding:14px 16px">
      <div class="flex-1">
        <a class="small" href="<?= e(url('courses/discuss&course=' . $t['course_id'] . '&topic=' . $t['id'])) ?>">
          <?= $t['pinned'] ? icon('pin') . ' ' : '' ?><?= e($t['title']) ?>
        </a>
        <p class="tiny faint" style="margin-top:4px"><?= e($t['first_name'] . ' ' . $t['last_name']) ?> · <?= e(date('M j, Y', strtotime($t['created_at']))) ?> · <?= icon('eye') ?> <?= (int)$t['views'] ?></p>
      </div>
      <div class="flex gap-8" style="align-items:center">
        <span class="badge badge-muted"><?= (int)$t['posts'] ?> replies</span>
        <?php if ($t['last_post']): ?><span class="tiny faint"><?= e(time_ago($t['last_post'])) ?></span><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$topics): ?><div class="alert alert-info">No discussions yet — start one! <?= icon('bulb') ?></div><?php endif; ?>
</div>
