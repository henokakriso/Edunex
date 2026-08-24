<?php /* Vice dean — course approval */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('exam') ?> Course Approval</h1>
    <p class="sub">Faculty courses awaiting review</p>
  </div>
</div>

<div class="card pad-0">
  <table class="table">
    <thead><tr><th>Course</th><th>Teacher</th><th>Department</th><th>Credits</th><th>Status</th><th style="width:250px">Decision</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['title']) ?></b><div class="tiny faint"><?= e($r['code'] ?: '') ?></div></td>
          <td class="small"><?= e($r['teacher']) ?></td>
          <td class="small"><?= e($r['dept']) ?></td>
          <td class="tiny"><?= (float)$r['credits'] ?></td>
          <td><span class="badge <?= $r['status'] === 'published' ? 'badge-success' : '' ?>"><?= e($r['status']) ?></span></td>
          <td>
            <?php if ($r['status'] === 'draft'): ?>
              <form method="post" class="flex gap-6">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="published">
                <button class="btn btn-sm btn-success" name="approve_course" value="<?= (int)$r['id'] ?>"><?= icon('check') ?> Approve</button>
              </form>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="draft">
                <button class="btn btn-sm btn-ghost" name="approve_course" value="<?= (int)$r['id'] ?>"><?= icon('ban') ?> Return</button>
              </form>
            <?php else: ?>
              <span class="tiny faint"><?= e($r['approved_at'] ?: '') ?></span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="muted">No courses in your faculty.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
