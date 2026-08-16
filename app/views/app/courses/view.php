<?php /* Course detail view */ ?>
<div class="card" style="padding:0;overflow:hidden;margin-bottom:22px">
  <div style="height:170px;background:linear-gradient(120deg, var(--accent-soft), var(--info-soft));display:flex;align-items:center;padding:34px">
    <div style="font-size:46px;margin-right:22px"><?= icon('graduation') ?></div>
    <div style="flex:1">
      <span class="badge badge-accent"><?= e($course['code'] ?: 'Course') ?> · <?= e($course['level']) ?></span>
      <h1 style="margin:8px 0"><?= e($course['title']) ?></h1>
      <p class="muted small"><?= e($course['description']) ?></p>
      <div class="flex gap-16 tiny faint" style="margin-top:10px">
        <span><?= icon('user') ?>‍<?= icon('school') ?> <?= e($course['tfirst']) ?> <?= e($course['tlast']) ?></span>
        <span><?= icon('calendar') ?> Created <?= e(date('M j, Y', strtotime($course['created_at']))) ?></span>
      </div>
    </div>
    <div style="text-align:right">
      <?php if (!empty($readonly)): ?>
        <div class="tiny faint" style="margin:0 0 8px auto;max-width:180px"><?= icon('eye') ?> Read-only preview — you can view contents but not take the course</div>
        <a class="btn btn-primary" href="<?= url('index.php?r=courses/learn&id=' . $course['id']) ?>"><?= icon('eye') ?> Preview content</a>
      <?php elseif ($enrolled): ?>
        <div class="progress" style="width:180px;margin:0 0 10px auto"><div style="width:<?= (float)$enrolled['progress'] ?>%"></div></div>
        <a class="btn btn-primary" href="<?= url('index.php?r=courses/learn&id=' . $course['id']) ?>">
          <?= $enrolled['progress'] > 0 ? '▶ Continue learning' : '▶ Start learning' ?>
        </a>
      <?php else: ?>
        <form method="post" action="<?= url('index.php?r=courses/learn&id=' . $course['id']) ?>">
          <?= csrf_field() ?>
          <button class="btn btn-primary btn-lg">Enroll free</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.6fr 1fr;align-items:start">
  <div class="flex-col gap-16">
    <div class="card">
      <h3 style="margin-bottom:16px"><?= icon('books') ?> Course content</h3>
      <?php foreach ($modules as $m): ?>
        <h4 class="small" style="margin:14px 0 8px"><?= icon('folder') ?> <?= e($m['title']) ?></h4>
        <?php foreach ($m['lessons'] as $l): ?>
          <div class="feed-item" style="padding:9px 0">
            <span><?= match ($l['type']) { 'video' => icon('video'), 'pdf' => icon('file'), 'audio' => icon('audio'), 'slides' => icon('video'), default => icon('note') } ?></span>
            <span class="small"><?= e($l['title']) ?><br><span class="faint tiny"><?= (int)$l['duration_min'] ?> min</span></span>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <?php if (!$modules): ?><p class="muted small">Content is being prepared by the teacher.</p><?php endif; ?>
    </div>

    <div class="card">
      <h3 style="margin-bottom:16px"><?= icon('chat') ?> Discussions</h3>
      <?php foreach ($topics as $t): ?>
        <a class="feed-item" href="<?= url('index.php?r=courses/discuss&id=' . $course['id'] . '&topic=' . $t['id']) ?>" style="text-decoration:none;color:var(--text)">
          <span class="feed-dot" style="background:var(--accent)"></span>
          <span><b class="small"><?= e($t['title']) ?></b><br><span class="faint tiny"><?= (int)$t['views'] ?> views</span></span>
        </a>
      <?php endforeach; ?>
      <?php if (!$topics): ?><p class="muted small">No discussions yet. Start the conversation!</p><?php endif; ?>
    </div>

    <div class="card">
      <h3 style="margin-bottom:16px"><?= icon('megaphone') ?> Announcements</h3>
      <?php foreach ($anns as $a): ?>
        <div class="feed-item"><span class="feed-dot" style="background:var(--info)"></span>
          <span><b class="small"><?= e($a['title']) ?></b><br><span class="muted tiny"><?= e(mb_substr($a['content'], 0, 130)) ?>… · <?= e(time_ago($a['created_at'])) ?></span></span></div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="flex-col gap-16">
    <div class="card">
      <h3 style="margin-bottom:14px"><?= icon('user') ?>‍<?= icon('school') ?> Teacher</h3>
      <div class="flex gap-12">
        <img class="avatar avatar-lg" src="<?= url('public/images/avatar.svg') ?>">
        <div><b><?= e($course['tfirst']) ?> <?= e($course['tlast']) ?></b><br><span class="muted small"><?= e($course['level']) ?> teacher</span></div>
      </div>
      <div class="divider"></div>
      <b class="small">Other courses by this teacher</b>
      <?php foreach ($teacherCourses as $tc): ?>
        <a class="feed-item small" href="<?= url('index.php?r=courses/view&id=' . $tc['id']) ?>"><?= icon('book') ?> <?= e($tc['title']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="card">
      <h3 style="margin-bottom:14px"><?= icon('graduation') ?> About this course</h3>
      <div class="small flex-col gap-8">
        <div><?= icon('chart-bar') ?> Level: <b><?= e($course['level'] ?: '—') ?></b></div>
        <div><?= icon('book') ?> Subject: <b><?= e($course['code'] ?: '—') ?></b></div>
        <div><?= icon('users') ?> Students: <b><?= (int)($enrolled ? 1 : 0) ?></b></div>
        <div><?= icon('medal') ?> Certificate: <b><?= 'Yes — on completion' ?></b></div>
      </div>
    </div>
  </div>
</div>
