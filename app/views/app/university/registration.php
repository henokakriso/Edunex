<?php /* Course registration for students */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('edit') ?> Course Registration</h1>
    <p class="sub">Register for courses this semester</p>
  </div>
</div>

<?php if ($u['role'] !== 'student'): ?>
<div class="card" style="margin-bottom:18px">
  <form method="get" action="<?= e(url('university/registration')) ?>" class="flex-row gap-6">
    <input type="hidden" name="r" value="university/registration">
    <select class="input" name="student_id" style="flex:1">
      <option value="">— Select student —</option>
      <?php foreach ($students as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (int)$s['id'] === $studentId ? 'selected' : '' ?>><?= e($s['student_id']) ?> — <?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select class="input" name="semester_id">
      <option value="">— All semesters —</option>
      <?php foreach ($semesters as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (int)$s['id'] === $activeSem ? 'selected' : '' ?>><?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary"><?= icon('search') ?> Filter</button>
  </form>
</div>
<?php endif; ?>

<div class="grid2" style="margin-bottom:18px">
  <div class="card">
    <h3 class="card-title"><?= icon('info') ?> Credit Load</h3>
    <div style="font-size:1.4em;font-weight:700;margin:8px 0"><?= (int)$load['enrolled'] ?> / <?= (int)$load['max'] ?> credits</div>
    <div class="progress-bar" style="height:8px;background:var(--border);border-radius:4px;margin-top:4px">
      <div style="height:100%;width:<?= min(100, (int)$load['max'] > 0 ? (int)$load['enrolled'] * 100 / (int)$load['max'] : 0) ?>%;background:var(--primary);border-radius:4px"></div>
    </div>
    <p class="tiny faint" style="margin-top:4px"><?= (int)$load['remaining'] ?> credits remaining</p>
  </div>
  <div class="card">
    <h3 class="card-title"><?= icon('check') ?> Registered Courses (<?= count($registered) ?>)</h3>
    <?php foreach ($registered as $r): ?>
      <div class="flex-row gap-6" style="margin:4px 0">
        <span class="badge"><?= e($r['code']) ?></span>
        <span style="flex:1"><?= e($r['title']) ?></span>
        <span class="tiny faint"><?= (int)$r['credits'] ?>cr</span>
      </div>
    <?php endforeach; ?>
    <?php if (!$registered): ?><p class="tiny faint">No courses registered yet.</p><?php endif; ?>
  </div>
</div>

<h3 style="margin:18px 0 8px"><?= icon('book') ?> Available Courses</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Code</th><th>Course</th><th>Credits</th><th>Lecturer</th><th>Room</th><th>Capacity</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($offerings as $o): ?>
        <?php $isReg = in_array($o['id'], array_column($registered, 'course_offering_id')); ?>
        <tr>
          <td><span class="badge"><?= e($o['code']) ?></span></td>
          <td><?= e($o['title']) ?></td>
          <td><?= (int)$o['credits'] ?></td>
          <td class="tiny"><?= e($o['lecturer_name'] ?? '—') ?></td>
          <td class="tiny"><?= e($o['room'] ?? '—') ?></td>
          <td class="tiny"><?= (int)$o['current_students'] ?>/<?= (int)$o['max_students'] ?></td>
          <td>
            <?php if ($isReg): ?>
              <form method="post" action="<?= e(url('university/registration&semester_id=' . $activeSem)) ?>" style="display:inline">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-ghost" name="drop_course" value="<?= (int)$o['id'] ?>" onclick="return confirm('Drop this course?')"><?= icon('x') ?> Drop</button>
              </form>
            <?php else: ?>
              <form method="post" action="<?= e(url('university/registration&semester_id=' . $activeSem)) ?>" style="display:inline">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-success" name="register_course" value="<?= (int)$o['id'] ?>" <?= $o['current_students'] >= $o['max_students'] ? 'disabled' : '' ?>><?= icon('plus') ?> Register</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$offerings): ?><tr><td colspan="7" class="tiny faint">No course offerings for this semester.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
