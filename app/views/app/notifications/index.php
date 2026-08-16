<?php /* Notifications view — Gmail-style inbox: one line = dot · avatar · title · by · date */
$iconFor = ['assignment' => icon('file'), 'exam' => icon('note'), 'feedback' => icon('chat'), 'announcement' => icon('megaphone'), 'achievement' => icon('trophy'), 'message' => icon('mail'), 'system' => icon('monitor'), 'reminder' => icon('clock')];
?>
<div class="page-head">
  <div>
    <h1><?= icon('bell') ?> Notifications</h1>
    <p class="sub"><?= (int)$unread ?> unread</p>
  </div>
  <div class="flex gap-8">
    <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-primary" name="mark_all" value="1">✓ Mark all read</button></form>
    <form method="post" class="inline" data-confirm="Delete all notifications?"><?= csrf_field() ?><button class="btn btn-danger" name="delete_all" value="1"><?= icon('trash') ?> Clear</button></form>
  </div>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="flex gap-8">
    <?php foreach (['all' => 'All', 'unread' => 'Unread', 'academic' => 'Academic', 'achievements' => 'Achievements'] as $k => $v): ?>
      <a class="btn btn-sm <?= $filter === $k ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('notifications&filter=' . $k)) ?>"><?= $v ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="card notif-inbox">
  <?php foreach ($notifs as $n): ?>
    <?php if ($n['ann_id']): /* announcement — row opens the reading pane */ ?>
      <?php $author = ['avatar' => $n['author_avatar'] ?? null, 'first_name' => $n['author_first'] ?? '', 'last_name' => $n['author_last'] ?? '']; ?>
      <div class="notif-row <?= $n['read_at'] ? '' : 'unread' ?>">
        <span class="notif-dot"></span>
        <?php if (!empty($author['avatar'])): ?>
          <img class="notif-av" src="<?= e(avatar_url($author)) ?>" alt=""
               onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
          <span class="notif-av" style="display:none"><?= e(initials($author)) ?></span>
        <?php else: ?>
          <span class="notif-av"><?= e(initials($author)) ?></span>
        <?php endif; ?>
        <a class="notif-main" href="<?= e(url('communication/announcement&id=' . (int)$n['ann_id'])) ?>">
          <b class="t"><?= e($n['title']) ?></b>
          <span class="f">by <?= e(trim(($n['author_first'] ?? '') . ' ' . ($n['author_last'] ?? ''))) ?></span>
        </a>
        <span class="notif-date"><?= e(date('M j, g:i A', strtotime($n['created_at']))) ?></span>
        <?php if (!$n['read_at']): ?>
          <form method="post" class="inline"><?= csrf_field() ?>
            <button class="btn btn-sm btn-ghost" name="mark_one" value="<?= (int)$n['id'] ?>" title="Mark as read">✓</button>
          </form>
        <?php endif; ?>
        <form method="post" class="inline" data-confirm="Delete this notification?"><?= csrf_field() ?>
          <button class="btn btn-sm btn-ghost" name="delete_one" value="<?= (int)$n['id'] ?>" title="Delete"><?= icon('trash') ?></button>
        </form>
      </div>
    <?php else: ?>
    <div class="notif-row <?= $n['read_at'] ? '' : 'unread' ?>">
      <span class="notif-dot"></span>
      <span class="notif-av"><?= $iconFor[$n['type']] ?? icon('bell') ?></span>
      <?php if ($n['link']): ?>
        <a class="notif-main" href="<?= e(url($n['link'])) ?>">
          <b class="t"><?= e($n['title']) ?></b>
          <span class="f"><?= e(ucfirst($n['type'])) ?></span>
        </a>
      <?php else: ?>
        <span class="notif-main"><b class="t"><?= e($n['title']) ?></b></span>
      <?php endif; ?>
      <span class="notif-date"><?= e(date('M j, g:i A', strtotime($n['created_at']))) ?></span>
      <?php if (!$n['read_at']): ?>
        <form method="post" class="inline"><?= csrf_field() ?>
          <button class="btn btn-sm btn-ghost" name="mark_one" value="<?= (int)$n['id'] ?>" title="Mark as read">✓</button>
        </form>
      <?php endif; ?>
      <form method="post" class="inline" data-confirm="Delete this notification?"><?= csrf_field() ?>
        <button class="btn btn-sm btn-ghost" name="delete_one" value="<?= (int)$n['id'] ?>" title="Delete"><?= icon('trash') ?></button>
      </form>
    </div>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if (!$notifs): ?><div class="alert alert-info" style="margin:12px">No notifications here. <?= icon('bell-off') ?></div><?php endif; ?>
</div>
