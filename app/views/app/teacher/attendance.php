<?php /* Teacher attendance view */
$statusMeta = ['present' => ['Present', 'ok'], 'late' => ['Late', 'warn'], 'absent' => ['Absent', 'danger'], 'excused' => ['Excused', 'info']];
?>
<style>
  .seg { display: inline-flex; gap: 6px; }
  .seg-btn { padding: 7px 12px; border: 1px solid var(--border); background: var(--bg-elev); border-radius: 8px; cursor: pointer; font-size: .78rem; color: var(--text-faint); transition: all .15s; display: inline-flex; align-items: center; gap: 5px; }
  .seg-btn:hover { border-color: var(--accent); color: var(--text); }
  .seg-btn svg { width: 14px; height: 14px; }
  .seg-btn.ok.active { background: var(--success); border-color: var(--success); color: #fff; }
  .seg-btn.warn.active { background: var(--warning); border-color: var(--warning); color: #fff; }
  .seg-btn.danger.active { background: var(--danger); border-color: var(--danger); color: #fff; }
  .seg-btn.info.active { background: var(--accent); border-color: var(--accent); color: #fff; }
  .status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 99px; font-size: .76rem; font-weight: 600; }
  .status-pill.ok { background: rgba(46,160,67,.12); color: var(--success); }
  .status-pill.warn { background: rgba(219,171,9,.14); color: var(--warning); }
  .status-pill.danger { background: rgba(218,54,51,.12); color: var(--danger); }
  .status-pill.info { background: rgba(29,105,201,.12); color: var(--accent); }
  #att-summary { font-size: .8rem; }
  #att-summary b.ok { color: var(--success); } #att-summary b.warn { color: var(--warning); }
  #att-summary b.danger { color: var(--danger); } #att-summary b.info { color: var(--accent); }
  .att-note { flex: 1; min-width: 140px; }
  .att-filter { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto; gap: 14px; align-items: end; }
  .att-filter .input { width: 100%; }
  .att-filter .day-nav { display: flex; gap: 8px; }
  @media (max-width: 860px) { .seg { flex-wrap: wrap; } .seg-btn { padding: 6px 9px; } .att-filter { grid-template-columns: 1fr; } .att-filter .day-nav a { flex: 1; justify-content: center; } }
</style>

<div class="page-head">
  <div>
    <h1><?= icon('calendar') ?> Attendance
      <?php if ($isHomeroom): ?><span class="badge badge-accent">Homeroom teacher — all subjects</span><?php endif; ?>
    </h1>
    <p class="sub">Mark attendance per course and date</p>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <form method="get" class="att-filter">
    <input type="hidden" name="r" value="teacher/attendance">
    <div class="flex-col">
      <label class="small faint">Course</label>
      <select class="input" name="course" onchange="this.form.submit()">
        <?php foreach ($courses as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $courseId === (int)$c['id'] ? 'selected' : '' ?>>
            <?= e($c['title']) ?> (<?= e($c['subject_name']) ?>)<?= $isHomeroom && (int)$c['teacher_id'] !== (int)$uid ? ' · view-only' : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col">
      <label class="small faint">Date</label>
      <input class="input" type="date" name="date" value="<?= e($date) ?>" onchange="this.form.submit()">
    </div>
    <div class="flex-col">
      <label class="small faint">Day</label>
      <div class="day-nav">
        <a class="btn btn-ghost" href="<?= e(url('teacher/attendance&course=' . $courseId . '&date=' . date('Y-m-d', strtotime($date) - 86400))) ?>">← Prev day</a>
        <a class="btn btn-ghost" href="<?= e(url('teacher/attendance&course=' . $courseId . '&date=' . date('Y-m-d', strtotime($date) + 86400))) ?>">Next day →</a>
      </div>
    </div>
  </form>
  <p class="tiny faint" style="margin:10px 0 0">
    <?php if ($isHomeroom): ?>Homeroom: you can record attendance for any subject, and view every subject's report. Courses marked <b>view-only</b> are recorded by their own teacher.<?php else: ?>Only courses in the subjects assigned to you by the director.<?php endif; ?>
  </p>
</div>

<?php if (!$courseId): ?>
  <div class="alert alert-info">No courses available yet. Ask the director to assign you subjects so you can record attendance.</div>
<?php elseif (!$students): ?>
  <div class="alert alert-info">No students enrolled in this course yet.</div>
<?php elseif (!$canEdit && $course): ?>
  <?php /* Homeroom view-only report for another teacher's course */ ?>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= count($students) ?> students — <?= e(date('D, M j, Y', strtotime($date))) ?></h3>
    <?php if ($saved): ?><div class="alert alert-info" style="margin-bottom:12px"><?= icon('eye') ?> View-only report — attendance for <b><?= e($course['title']) ?></b> is recorded by <?= e($course['teacher_name'] ?? 'another teacher') ?>. You can see who is present or absent on any subject, but only that teacher can edit their records.</div>
    <?php else: ?><div class="alert alert-info" style="margin-bottom:12px"><?= icon('eye') ?> View-only — attendance for this date hasn't been recorded yet by <?= e($course['teacher_name'] ?? 'the course teacher') ?>.</div><?php endif; ?>
    <table class="table">
      <thead><tr><th>Student</th><th>Status</th><th>Note</th></tr></thead>
      <tbody>
        <?php foreach ($students as $s): $ex = $existing[$s['id']] ?? null; $st = $ex['status'] ?? 'present'; $m = $statusMeta[$st] ?? $statusMeta['present']; ?>
          <tr>
            <td>
              <div class="flex gap-8" style="align-items:center">
                <div class="avatar" style="width:30px;height:30px;font-size:.7rem"><?= e(mb_substr($s['name'], 0, 1)) ?></div>
                <div><b class="small"><?= e($s['name']) ?></b><p class="tiny faint"><?= e($s['student_id']) ?></p></div>
              </div>
            </td>
            <td>
              <?php if ($ex): ?><span class="status-pill <?= $m[1] ?>"><?= icon($st === 'present' ? 'check-circle' : ($st === 'absent' ? 'ban-circle' : ($st === 'excused' ? 'hand' : 'clock'))) ?> <?= $m[0] ?></span>
              <?php else: ?><span class="tiny faint">not recorded</span><?php endif; ?>
            </td>
            <td class="tiny faint"><?= e($ex['note'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php elseif ($saved && !$editMode): ?>
  <?php /* Locked state after save — CRUD via Edit / Clear */ ?>
  <div class="card" style="border-color:var(--success)">
    <div class="flex-between" style="flex-wrap:wrap;gap:12px">
      <div>
        <h3 class="card-title" style="margin-top:0"><?= icon('check-circle') ?> Attendance saved — <?= e(date('D, M j, Y', strtotime($date))) ?></h3>
        <p class="small faint"><?= count($students) ?> students · recorded by you on <?= e(date('M j, H:i', strtotime($existing[array_key_first($existing)]['created_at'] ?? 'now'))) ?>. Records are locked to prevent accidental changes.</p>
      </div>
      <div class="flex gap-8">
        <form method="get" class="inline">
          <input type="hidden" name="r" value="teacher/attendance">
          <input type="hidden" name="course" value="<?= (int)$courseId ?>">
          <input type="hidden" name="date" value="<?= e($date) ?>">
          <input type="hidden" name="mode" value="edit">
          <button class="btn btn-primary btn-sm"><?= icon('edit') ?> Edit attendance</button>
        </form>
        <form method="post" class="inline" data-confirm="Clear all <?= count($students) ?> attendance records for <?= e(date('M j, Y', strtotime($date))) ?>? You can record them again.">
          <?= csrf_field() ?>
          <input type="hidden" name="course_id" value="<?= (int)$courseId ?>">
          <button class="btn btn-danger-ghost btn-sm" name="clear_attendance" value="1"><?= icon('trash') ?> Clear</button>
        </form>
      </div>
    </div>
    <table class="table" style="margin-top:10px">
      <thead><tr><th>Student</th><th>Status</th><th>Note</th></tr></thead>
      <tbody>
        <?php foreach ($students as $s): $ex = $existing[$s['id']] ?? null; $st = $ex['status'] ?? 'present'; $m = $statusMeta[$st] ?? $statusMeta['present']; ?>
          <tr>
            <td>
              <div class="flex gap-8" style="align-items:center">
                <div class="avatar" style="width:30px;height:30px;font-size:.7rem"><?= e(mb_substr($s['name'], 0, 1)) ?></div>
                <div><b class="small"><?= e($s['name']) ?></b><p class="tiny faint"><?= e($s['student_id']) ?></p></div>
              </div>
            </td>
            <td><span class="status-pill <?= $m[1] ?>"><?= icon($st === 'present' ? 'check-circle' : ($st === 'absent' ? 'ban-circle' : ($st === 'excused' ? 'hand' : 'clock'))) ?> <?= $m[0] ?></span></td>
            <td class="tiny faint"><?= e($ex['note'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <form method="post" class="card" id="att-form">
    <?= csrf_field() ?>
    <input type="hidden" name="course_id" value="<?= (int)$courseId ?>">
    <div class="flex-between" style="flex-wrap:wrap;gap:8px;align-items:center">
      <h3 class="card-title" style="margin:0"><?= count($students) ?> students — <?= e(date('D, M j, Y', strtotime($date))) ?></h3>
      <span class="small faint" id="att-summary"><b class="ok">0 present</b> · <b class="warn">0 late</b> · <b class="danger">0 absent</b> · <b class="info">0 excused</b></span>
    </div>
    <table class="table">
      <thead><tr><th>Student</th><th>Status</th><th>Note</th></tr></thead>
      <tbody>
        <?php foreach ($students as $s): $ex = $existing[$s['id']] ?? null; $cur = $ex['status'] ?? 'present'; ?>
          <tr class="att-row" data-sid="<?= (int)$s['id'] ?>">
            <td>
              <div class="flex gap-8" style="align-items:center">
                <div class="avatar" style="width:30px;height:30px;font-size:.7rem"><?= e(mb_substr($s['name'], 0, 1)) ?></div>
                <div><b class="small"><?= e($s['name']) ?></b><p class="tiny faint"><?= e($s['student_id']) ?></p></div>
              </div>
            </td>
            <td>
              <div class="seg">
                <?php foreach ($statusMeta as $key => $meta): ?>
                  <button type="button" class="seg-btn <?= $meta[1] ?> <?= $cur === $key ? 'active' : '' ?>" data-st="<?= $key ?>">
                    <?= icon($key === 'present' ? 'check-circle' : ($key === 'absent' ? 'ban-circle' : ($key === 'excused' ? 'hand' : 'clock'))) ?> <?= $meta[0] ?>
                  </button>
                <?php endforeach; ?>
                <input type="hidden" name="status[<?= (int)$s['id'] ?>]" value="<?= $cur ?>">
              </div>
            </td>
            <td><input class="input att-note" name="note[<?= (int)$s['id'] ?>]" value="<?= e($ex['note'] ?? '') ?>" placeholder="Optional note"></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="flex gap-8" style="margin-top:14px;flex-wrap:wrap">
      <button class="btn btn-primary" name="save_attendance" value="1"><?= icon('save') ?> Save attendance</button>
      <?php if ($saved): ?>
        <span class="tiny faint" style="align-self:center"><?= icon('lock') ?> Already saved for this day — saving again updates the records.</span>
      <?php else: ?>
        <span class="tiny faint" style="align-self:center">You can also mark attendance quickly via the mobile app or desktop terminal. Once saved, records are locked — use Edit if you need to change them.</span>
      <?php endif; ?>
    </div>
  </form>
<?php endif; ?>

<script>
(function () {
  const counts = { present: 0, late: 0, absent: 0, excused: 0 };
  const sum = document.getElementById('att-summary');
  if (!sum) return;
  function recount() {
    counts.present = counts.late = counts.absent = counts.excused = 0;
    document.querySelectorAll('#att-form .att-row').forEach(row => {
      const v = row.querySelector('input[type=hidden]').value;
      counts[v] = (counts[v] || 0) + 1;
    });
    sum.innerHTML = '<b class="ok">' + counts.present + ' present</b> · <b class="warn">' + counts.late + ' late</b> · <b class="danger">' + counts.absent + ' absent</b> · <b class="info">' + counts.excused + ' excused</b>';
  }
  document.querySelectorAll('#att-form .seg-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const seg = btn.closest('.seg');
      seg.querySelectorAll('.seg-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      seg.querySelector('input[type=hidden]').value = btn.dataset.st;
      recount();
    });
  });
  recount();
})();
</script>
