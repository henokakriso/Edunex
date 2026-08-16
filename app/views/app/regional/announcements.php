<?php /* Regional announcements — post to assigned schools */
?>
<div class="page-head">
  <div>
    <h1><?= icon('megaphone') ?> Announcements</h1>
    <p class="sub">Broadcast to your assigned schools</p>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('send') ?> New announcement</h3>
    <form method="post" class="flex-col gap-10">
      <?= csrf_field() ?>
      <div class="grid2">
        <div class="flex-col"><label class="small faint">School *</label>
          <select class="input" name="school_id" required>
            <?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Audience</label>
          <select class="input" name="audience">
            <option value="all">Everyone</option><option value="students">Students</option>
            <option value="teachers">Teachers</option><option value="parents">Parents</option>
          </select>
        </div>
      </div>
      <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" required maxlength="200"></div>
      <div class="flex-col"><label class="small faint">Content *</label><textarea class="input" name="content" rows="4" required></textarea></div>
      <div><button class="btn btn-success" type="submit"><?= icon('rocket') ?> Publish</button></div>
    </form>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('menu') ?> Recent announcements</h3>
    <?php foreach ($rows as $a): ?>
      <div class="list-row" style="padding:9px 0">
        <div class="avatar" style="background:var(--accent-soft)"><?= icon('megaphone') ?></div>
        <div class="flex-1">
          <b class="small"><?= e($a['title']) ?></b>
          <p class="tiny faint"><?= e($a['school_name']) ?> · <?= e(ucfirst($a['audience'])) ?> · <?= e(time_ago($a['created_at'])) ?></p>
          <p class="tiny" style="margin-top:2px"><?= e(mb_strimwidth((string)$a['content'], 0, 90, '…')) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><p class="muted small">No announcements yet.</p><?php endif; ?>
  </div>
</div>
