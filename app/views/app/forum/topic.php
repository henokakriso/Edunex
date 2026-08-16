<?php /* Forum topic thread */
$emo = ['like' => icon('thumbs-up'), 'love' => icon('heart'), 'laugh' => icon('smile'), 'wow' => icon('smile'), 'sad' => icon('heart'), 'help' => icon('hand')];
?>
<div class="page-head">
  <div>
    <h1 class="small" style="margin:0 0 4px"><?= icon('chat') ?> <a class="accent" href="<?= e(url('courses/discuss&course=' . $topic['course_id'])) ?>"><?= e($topic['course_title']) ?></a></h1>
    <h1 style="margin:0"><?= $topic['pinned'] ? icon('pin') . ' ' : '' ?><?= e($topic['title']) ?></h1>
    <p class="sub">by <?= e($topic['first_name'] . ' ' . $topic['last_name']) ?> · <?= icon('eye') ?> <?= (int)$topic['views'] ?> views</p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('courses/discuss&course=' . $topic['course_id'])) ?>">← Back</a>
</div>

<div class="flex-col gap-10">
  <div class="card">
    <div class="flex gap-12">
      <div class="avatar"><?= e(mb_substr((string)$topic['first_name'], 0, 1)) ?></div>
      <div class="flex-1">
        <b class="small"><?= e($topic['first_name'] . ' ' . $topic['last_name']) ?> <span class="badge <?= $topic['author_role'] === 'teacher' ? 'badge-accent' : 'badge-muted' ?>"><?= e($topic['author_role']) ?></span></b>
        <p class="small" style="margin-top:6px;white-space:pre-wrap"><?= e($topic['body'] ?: '—') ?></p>
      </div>
    </div>
  </div>

  <?php foreach ($posts as $p): $counts = forum_reaction_counts('forum', (int)$p['id']); ?>
    <div class="card" id="post-<?= (int)$p['id'] ?>">
      <div class="flex gap-12" style="align-items:flex-start">
        <div class="avatar"><?= e(mb_substr((string)$p['first_name'], 0, 1)) ?></div>
        <div class="flex-1">
          <b class="small"><?= e($p['first_name'] . ' ' . $p['last_name']) ?> <span class="badge <?= $p['author_role'] === 'teacher' ? 'badge-accent' : 'badge-muted' ?>"><?= e($p['author_role']) ?></span></b>
          <?php if ($p['is_answer']): ?><span class="badge badge-success">✓ answer</span><?php endif; ?>
          <p class="small" style="margin-top:6px;white-space:pre-wrap"><?= e($p['body']) ?></p>
          <div class="flex gap-8" style="margin-top:10px">
            <?php foreach (['like', 'love', 'laugh', 'wow', 'help'] as $r): $cnt = $counts[$r] ?? 0; $mine = ($myReacts[(int)$p['id']] ?? '') === $r; ?>
              <button class="react-btn <?= $mine ? 'on' : '' ?>" onclick="EdunexReact('forum', <?= (int)$p['id'] ?>, '<?= $r ?>', this)"><?= $emo[$r] ?> <?= $cnt ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <span class="tiny faint"><?= e(time_ago($p['created_at'])) ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<form method="post" class="card" style="margin-top:18px">
  <?= csrf_field() ?>
  <h3 class="card-title" style="margin-top:0"><?= icon('edit') ?> Reply</h3>
  <textarea class="input" name="reply" style="min-height:90px" placeholder="Write your reply…" required></textarea>
  <div class="flex gap-8" style="margin-top:10px">
    <button class="btn btn-primary" name="reply_submit" value="1"><?= icon('reply') ?> Post reply</button>
    <label class="small faint" style="align-self:center"><input type="checkbox" name="is_answer" value="1"> Mark as answer</label>
  </div>
</form>

<script>
window.EdunexReact = function (type, id, r, btn) {
  fetch('<?= e(url('api/reactions')) ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ target_type: type, target_id: id, reaction: r })
  }).then(res => res.json()).then(d => {
    if (d.ok) location.reload();
  });
};
</script>
