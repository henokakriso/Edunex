<?php /* Parent reports view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('trend-up') ?> Child Reports</h1>
    <p class="sub">Detailed progress for your children</p>
  </div>
</div>

<?php if (count($summaries) > 1): ?>
  <div class="card" style="margin-bottom:18px">
    <form method="get" class="flex gap-12" style="align-items:end">
      <input type="hidden" name="r" value="parent/reports">
      <div class="flex-col"><label class="small faint">Child</label>
        <select class="input" name="child" onchange="this.form.submit()">
          <?php foreach ($summaries as $s): ?><option value="<?= (int)$s['child']['id'] ?>" <?= $cid == $s['child']['id'] ? 'selected' : '' ?>><?= e($s['child']['first_name'] . ' ' . $s['child']['last_name']) ?></option><?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php if ($detail): $c = $detail['child']; ?>
  <div class="card" style="margin-bottom:18px">
    <div class="flex-between" style="flex-wrap:wrap;gap:12px">
      <div>
        <h3 style="margin:0"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></h3>
        <p class="tiny faint"><?= e($c['student_id']) ?> · GPA <?= e($detail['gpa']) ?> · Level <?= (int)$detail['level'] ?> · <?= icon('bolt') ?> <?= (int)$detail['xp'] ?> XP</p>
      </div>
      <div class="flex gap-8">
        <a class="btn btn-sm" href="<?= e(url('certificates/view&student=' . $c['id'])) ?>"><?= icon('medal') ?> Certificates (<?= count($detail['certificates']) ?>)</a>
      </div>
    </div>
    <div class="grid4" style="margin-top:14px">
      <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px"><div class="stat-value"><?= (int)$detail['avg_progress'] ?>%</div><div class="tiny faint">Course progress</div></div>
      <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px"><div class="stat-value"><?= (int)$detail['attendance']['rate'] ?>%</div><div class="tiny faint">Attendance</div></div>
      <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px"><div class="stat-value"><?= count($detail['grades']) ?></div><div class="tiny faint">Exams graded</div></div>
      <div class="stat-card" style="border:1px solid var(--border);border-radius:12px;padding:12px"><div class="stat-value"><?= (int)$detail['completed_courses'] ?></div><div class="tiny faint">Courses completed</div></div>
    </div>
  </div>

  <div class="grid" style="grid-template-columns:1.3fr 1fr;gap:22px;align-items:start">
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('note') ?> Exam grades</h3>
      <?php foreach ($detail['grades'] as $g): $pct = $g['total_points'] > 0 ? round($g['score'] / $g['total_points'] * 100) : 0; ?>
        <div class="list-row" style="padding:9px 0">
          <div class="flex-1"><b class="small"><?= e($g['title']) ?></b><p class="tiny faint"><?= e($g['course_title']) ?> · <?= e(date('M j, Y', strtotime($g['submitted_at']))) ?></p></div>
          <div class="flex gap-8" style="align-items:center">
            <div class="progress" style="width:80px"><div style="width:<?= $pct ?>%"></div></div>
            <b class="small"><?= rtrim(rtrim((string)$g['score'], '0'), '.') ?>/<?= rtrim(rtrim((string)$g['total_points'], '0'), '.') ?></b>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$detail['grades']): ?><p class="muted small">No graded exams yet.</p><?php endif; ?>
    </div>

    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Attendance (last 100)</h3>
      <?php $cols = ['present' => 'var(--success)', 'late' => 'var(--warning)', 'absent' => 'var(--danger)', 'excused' => 'var(--muted)']; ?>
      <div class="flex gap-8" style="flex-wrap:wrap;margin-bottom:10px">
        <?php foreach ($detail['attendance']['breakdown'] as $b): ?>
          <span class="badge badge-muted"><?= e($b['status']) ?>: <?= (int)$b['n'] ?></span>
        <?php endforeach; ?>
      </div>
      <div class="flex gap-6" style="flex-wrap:wrap">
        <?php foreach ($detail['attendance_rows'] as $i => $ar): ?>
          <span title="<?= e(date('M j', strtotime($ar['date']))) ?> — <?= e($ar['status']) ?>" style="width:14px;height:14px;border-radius:4px;background:<?= $cols[$ar['status']] ?? 'var(--border)' ?>"></span>
        <?php endforeach; ?>
      </div>
      <?php if (!$detail['attendance_rows']): ?><p class="muted small">No attendance recorded.</p><?php endif; ?>
    </div>
  </div>

  <?php if ($detail['certificates']): ?>
    <div class="card" style="margin-top:22px">
      <h3 class="card-title" style="margin-top:0"><?= icon('medal') ?> Certificates earned</h3>
      <div class="flex-col gap-8">
        <?php foreach ($detail['certificates'] as $cert): ?>
          <div class="list-row" style="padding:8px 0">
            <span class="flex-1 small"><b><?= e($cert['course_title']) ?></b></span>
            <span class="mono tiny faint"><?= e($cert['cert_code']) ?></span>
            <span class="tiny faint"><?= e(date('M j, Y', strtotime($cert['issued_at']))) ?></span>
            <a class="btn btn-sm btn-ghost" href="<?= e(url('certificates/view&code=' . urlencode($cert['cert_code']))) ?>"><?= icon('eye') ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
<?php else: ?>
  <div class="alert alert-info">Select a child to see their report.</div>
<?php endif; ?>
