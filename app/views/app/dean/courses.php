<?php /* Dean course approval — publish/reject draft courses of faculty teachers */
?>
<div class="page-head">
  <div>
    <h1><?= icon('exam') ?> Course Approval</h1>
    <p class="sub"><?= e($faculty['name']) ?> — draft courses need your approval to be published</p>
  </div>
  <div class="flex gap-6">
    <a class="btn <?= $status === 'draft' ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('dean/courses&status=draft')) ?>">Draft (<?= (int)$counts['draft'] ?>)</a>
    <a class="btn <?= $status === 'published' ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('dean/courses&status=published')) ?>">Published (<?= (int)$counts['published'] ?>)</a>
  </div>
</div>

<div class="table-wrap">
  <table class="table">
    <thead><tr><th>Course</th><th>Teacher</th><th>Department</th><th>Created</th><th style="width:170px">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['title']) ?></b><p class="tiny faint"><?= e($r['code'] ?: '—') ?></p></td>
          <td><?= e($r['teacher']) ?></td>
          <td><?= e($r['department']) ?></td>
          <td class="small faint"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
          <td>
            <div class="flex gap-6">
              <a class="btn btn-sm btn-ghost" href="<?= e(url('courses/view&id=' . (int)$r['id'])) ?>"><?= icon('eye') ?> View</a>
              <?php if ($status === 'draft'): ?>
                <form method="post" class="inline">
                  <?= csrf_field() ?><input type="hidden" name="course_id" value="<?= (int)$r['id'] ?>">
                  <button class="btn btn-sm btn-success" name="action" value="approve" onclick="return confirm('Approve and publish this course?')"><?= icon('check') ?> Approve</button>
                </form>
                <form method="post" class="inline">
                  <?= csrf_field() ?><input type="hidden" name="course_id" value="<?= (int)$r['id'] ?>">
                  <button class="btn btn-sm btn-danger" name="action" value="reject" onclick="return confirm('Return this course for revision?')"><?= icon('trash') ?> Reject</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="muted">No <?= e($status) ?> courses in your faculty.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
