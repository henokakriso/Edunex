<?php /* Teacher exam list view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> Exams</h1>
    <p class="sub">Create and manage exams for your courses</p>
  </div>
  <a class="btn btn-primary" href="<?= e(url('teacher/exam&new=1')) ?>">+ New exam</a>
</div>

<?php if ($courseFilter): ?>
  <div class="card" style="margin-bottom:18px">
    <form method="get" class="flex gap-12" style="align-items:end">
      <input type="hidden" name="r" value="teacher/exams">
      <div class="flex-col flex-1">
        <label class="small faint">Course</label>
        <select class="input" name="course" onchange="this.form.submit()">
          <option value="">All my courses</option>
          <?php foreach ($myCourses as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $courseFilter == $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?> (<?= e($c['subject_name']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
<?php endif; ?>

<div class="flex-col gap-16">
  <?php foreach ($exams as $ex): $ed = strtotime($ex['ends_at']); $open = $ex['is_published'] && $ed > time(); ?>
    <div class="card">
      <div class="flex-between" style="flex-wrap:wrap;gap:12px">
        <div>
          <b><?= e($ex['title']) ?></b>
          <span class="badge <?= $ex['is_published'] ? 'badge-success' : 'badge-muted' ?>"><?= $ex['is_published'] ? 'Published' : 'Draft' ?></span>
          <?php if ($ex['subject_name']): ?><span class="badge badge-info"><?= e($ex['subject_name']) ?></span><?php endif; ?>
          <p class="small faint" style="margin-top:4px"><?= e($ex['course_title']) ?> · <?= (int)$ex['question_count'] ?> questions · <?= (int)$ex['duration_min'] ?> min · Passing <?= e($ex['passing_score']) ?>% · <?= $open ? 'Open until ' . date('M j, H:i', $ed) : ($ex['is_published'] ? 'Closed ' . date('M j', $ed) : 'Draft') ?></p>
        </div>
        <div class="flex gap-8">
          <a class="btn btn-sm" href="<?= e(url('teacher/exam&id=' . $ex['id'])) ?>"><?= icon('edit') ?> Edit</a>
          <a class="btn btn-sm btn-ghost" href="<?= e(url('teacher/grade&exam=' . $ex['id'])) ?>"><?= icon('check-circle') ?> Grade (<?= (int)$ex['pending_count'] ?>)</a>
          <a class="btn btn-sm btn-ghost" href="<?= e(url('teacher/exam&id=' . $ex['id'] . '&preview=1')) ?>"><?= icon('eye') ?> Preview</a>
          <?php if (!$ex['is_published']): ?>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="publish_exam" value="<?= (int)$ex['id'] ?>">
              <button class="btn btn-sm btn-success"><?= icon('rocket') ?> Publish</button>
            </form>
          <?php endif; ?>
          <form method="post" class="inline" data-confirm="Delete this exam and all its attempts?">
            <?= csrf_field() ?>
            <input type="hidden" name="delete_exam" value="<?= (int)$ex['id'] ?>">
            <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$exams): ?><div class="alert alert-info">No exams yet. Create your first exam!</div><?php endif; ?>
</div>
