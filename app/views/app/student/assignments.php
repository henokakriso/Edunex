<?php /* Student assignments list view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('doc') ?> My Assignments</h1>
    <p class="sub">Everything you need to hand in</p>
  </div>
</div>

<div class="flex-col gap-16">
  <?php foreach ($assigns as $a): ?>
    <div class="card" style="<?= $a['overdue'] ? 'border-left:4px solid var(--danger)' : '' ?>">
      <div class="flex-between" style="flex-wrap:wrap;gap:12px">
        <div>
          <b><?= e($a['title']) ?></b>
          <span class="badge badge-accent"><?= e($a['course_title']) ?></span>
          <?php if ($a['submitted']): ?>
            <span class="badge <?= $a['sub_status'] === 'graded' ? 'badge-success' : 'badge-warning' ?>"><?= $a['sub_status'] === 'graded' ? 'Graded' : 'Submitted — awaiting grade' ?></span>
            <?php if ($a['score'] !== null): ?><b class="small"> · <?= rtrim(rtrim((string)$a['score'], '0'), '.') ?>/<?= rtrim(rtrim((string)$a['max_score'], '0'), '.') ?></b><?php endif; ?>
          <?php else: ?>
            <span class="badge badge-muted">Not submitted</span>
          <?php endif; ?>
          <p class="tiny faint" style="margin-top:4px">
            Due <?= e(date('M j, H:i', strtotime($a['due_date']))) ?>
            <?php if ($a['overdue']): ?><b style="color:var(--danger)"> · OVERDUE</b><?php endif; ?>
            <?php if ($a['allow_late']): ?> · Late allowed<?php endif; ?>
          </p>
        </div>
        <a class="btn <?= $a['submitted'] ? 'btn-ghost' : 'btn-primary' ?>" href="<?= e(url('assignments/view&id=' . $a['id'])) ?>">
          <?= $a['submitted'] ? icon('edit') . ' Edit / view' : icon('rocket') . ' Start' ?>
        </a>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$assigns): ?><div class="alert alert-info">No assignments for your courses right now.</div><?php endif; ?>
</div>
