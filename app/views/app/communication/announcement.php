<?php /* Announcement detail — Gmail-style reading pane (fixed wide screen, no content wrapping) */
$a = $ann;
$isOwn = (int)$a['author_id'] === (int)$__u['id'];
$audLabel = ['all' => 'Everyone', 'students' => 'Students', 'teachers' => 'Teachers', 'parents' => 'Parents', 'course' => 'Course students'][$a['audience']] ?? ucfirst($a['audience']);
?>
<div class="ann-toolbar">
  <a class="btn btn-ghost" href="<?= e(url($back)) ?>"><?= icon('back') ?> Back</a>
  <span style="flex:1"></span>
  <?php if (!$isOwn): ?>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('ann-reply-box').style.display='block';this.style.display='none'"><?= icon('forward') ?> Forward / Suggest</button>
  <?php endif; ?>
</div>

<div class="card ann-pane">
  <div class="ann-head">
    <img class="ann-av" src="<?= e(avatar_url($a)) ?>" alt=""
         onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
    <div class="ann-av" style="display:none"><?= e(initials($a)) ?></div>
    <div style="flex:1;min-width:0">
      <div class="ann-from">
        <b><?= e($author) ?></b>
        <span class="badge badge-muted"><?= e(ucfirst($a['author_role'])) ?></span>
        <?php if ($a['course_title']): ?><span class="badge accent"><?= e($a['course_title']) ?></span><?php endif; ?>
        <?php if ($a['pinned']): ?><span class="badge badge-accent"><?= icon('pin') ?> pinned</span><?php endif; ?>
      </div>
      <p class="ann-meta">
        <?= icon('forward') ?> to: <b><?= e($audLabel) ?></b> &nbsp;·&nbsp;
        <?= icon('school') ?> <?= e($a['school_name']) ?><?= $a['school_type'] ? ' · ' . e(ucfirst($a['school_type'])) : '' ?>
        &nbsp;·&nbsp; <?= e(date('M j, Y · g:i A', strtotime($a['created_at']))) ?>
      </p>
    </div>
  </div>
  <h2 class="ann-subject"><?= e($a['title']) ?></h2>
  <div class="ann-body"><?= nl2br(e($a['content'])) ?></div>
</div>

<?php if (!$isOwn): ?>
<div class="card ann-pane" id="ann-reply-box" style="display:none;margin-top:16px">
  <div style="padding:20px 28px">
    <h3 class="card-title" style="margin-top:0"><?= icon('forward') ?> Forward to <?= e($a['first_name']) ?> (<?= e($a['author_role']) ?>)</h3>
    <p class="tiny faint">Send a suggestion or comment about this announcement. It will be delivered as a message to the creator.</p>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="reply_announcement" value="1">
      <textarea class="input" name="body" rows="4" required placeholder="Type your suggestion or comment here…" style="margin:8px 0"></textarea>
      <button class="btn btn-primary"><?= icon('send') ?> Send suggestion</button>
    </form>
  </div>
</div>
<?php endif; ?>
