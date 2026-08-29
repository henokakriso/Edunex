<?php /* Teacher discussion hub */
?>
<style>
  .forum-filter { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 14px; align-items: end; }
  .forum-filter .input { width: 100%; }
  @media (max-width: 860px) { .forum-filter { grid-template-columns: 1fr; } }
</style>
<div class="page-head">
  <div>
    <h1><?= icon('chat') ?> Discussion</h1>
    <p class="sub">Start course discussions — students enrolled in the course can reply and ask questions.</p>
  </div>
</div>

<?php if (!$courses): ?>
  <div class="empty-state">
    <div class="empty-ic"><?= icon('chat') ?></div>
    <h3>No courses to discuss yet</h3>
    <p class="small">Discussions are created per course. Ask the director to assign you subjects so you can build courses and start discussions.</p>
  </div>
<?php else: ?>

  <div class="card" style="margin-bottom:18px">
    <form method="get" class="forum-filter">
      <input type="hidden" name="r" value="teacher/forum">
      <div class="flex-col">
        <label class="small faint">Course</label>
        <select class="input" name="course" onchange="this.form.submit()">
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $courseId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?> (<?= e($c['subject_name']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Action</label>
        <button type="button" class="btn btn-primary" data-open-modal="new-topic-modal"><?= icon('plus') ?> New topic</button>
      </div>
    </form>
    <p class="tiny faint" style="margin:10px 0 0">Only courses in the subjects assigned to you by the director. Students enrolled in <b><?= $course ? e($course['title']) : 'the selected course' ?></b> can see and join these discussions.</p>
  </div>

  <?php if ($course): ?>
    <div class="modal-backdrop" id="new-topic-modal">
      <div class="modal" style="max-width:560px">
        <div class="modal-head">
          <h3><?= icon('edit') ?> New topic — <?= e($course['title']) ?></h3>
          <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
        </div>
        <div class="modal-body">
          <form method="post">
            <?= csrf_field() ?>
            <input class="input" name="title" placeholder="Topic title — e.g. "Explain question 4 from the worksheet"" required>
            <textarea class="input" style="margin-top:10px;min-height:100px" name="body" placeholder="What would you like to discuss? Add context so students can give good answers."></textarea>
            <label class="check" style="margin-top:10px"><input type="checkbox" name="pinned" value="1"> Pin to top of the course discussion</label>
            <div class="modal-foot">
              <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
              <button class="btn btn-primary" name="new_topic" value="1"><?= icon('megaphone') ?> Post discussion</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="flex-col gap-10">
    <?php foreach ($topics as $t): ?>
      <div class="card list-row" style="padding:15px 17px">
        <div class="flex-1" style="min-width:0">
          <a class="small" style="font-weight:600" href="<?= e(url('courses/discuss&course=' . $t['course_id'] . '&topic=' . $t['id'])) ?>">
            <?= $t['pinned'] ? icon('pin') . ' ' : '' ?><?= e($t['title']) ?>
          </a>
          <?php if ($t['body']): ?><p class="tiny faint" style="margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e(mb_strimwidth((string)$t['body'], 0, 110, '…')) ?></p><?php endif; ?>
          <p class="tiny faint" style="margin-top:4px"><?= e($t['first_name'] . ' ' . $t['last_name']) ?> · <?= e(date('M j, Y', strtotime($t['created_at']))) ?> · <?= icon('eye') ?> <?= (int)$t['views'] ?></p>
        </div>
        <div class="flex gap-8" style="align-items:center">
          <span class="badge badge-muted"><?= (int)$t['posts'] ?> replies</span>
          <?php if ($t['last_post']): ?><span class="tiny faint"><?= e(time_ago($t['last_post'])) ?></span><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($course && !$topics): ?>
    <div class="empty-state" style="margin-top:18px">
      <div class="empty-ic"><?= icon('chat') ?></div>
      <h3>No discussions in this course yet</h3>
      <p class="small">Click <b>New topic</b> above to start the first discussion for <?= e($course['title']) ?> — students will see it in their course page.</p>
    </div>
  <?php endif; ?>

<?php endif; ?>
