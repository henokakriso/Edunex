<?php /* Registrar enrollments — add/remove course enrollments */
?>
<div class="page-head">
  <div>
    <h1><?= icon('users') ?> Enrollments</h1>
    <p class="sub">Course enrollments for your school</p>
  </div>
  <form method="get" class="flex gap-6" action="<?= e(url('registrar/enrollments')) ?>">
    <input class="input" name="q" value="<?= e($q) ?>" placeholder="Search student or course…" style="min-width:200px">
    <select class="input" name="semester">
      <option value="0">All semesters</option>
      <?php foreach ($semesters as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= $semFilter === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> · <?= e($s['year_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn"><?= icon('search') ?> Search</button>
  </form>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title" style="margin-top:0"><?= icon('user-plus') ?> Enroll a student</h3>
  <form method="post" class="grid2" style="margin-top:6px">
    <?= csrf_field() ?>
    <div class="flex-col"><label class="small faint">Course *</label>
      <select class="input" name="course_id" required>
        <option value="">— Select course —</option>
        <?php foreach ($courses as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?> (<?= e($c['code'] ?: '') ?>) · <?= (float)$c['credits'] ?> cr</option><?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col"><label class="small faint">Student *</label>
      <select class="input" name="student_id" required>
        <option value="">— Select student —</option>
        <?php foreach ($students as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['student_id'] ?: '—') ?>)</option><?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col"><label class="small faint">Semester</label>
      <select class="input" name="semester_id">
        <option value="0">— None —</option>
        <?php foreach ($semesters as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?> · <?= e($s['year_name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div><button class="btn btn-success" name="add_enrollment" value="1"><?= icon('plus') ?> Add enrollment</button></div>
  </form>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr><th>Student</th><th>Course</th><th>Credits</th><th>Semester</th><th>Enrolled</th><th>Progress</th><th>Status</th><th style="width:110px">Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['student']) ?></b><p class="tiny faint"><?= e($r['sid_no'] ?: '—') ?></p></td>
          <td><?= e($r['course']) ?></td>
          <td class="tiny"><?= (float)$r['credits'] ?></td>
          <td class="tiny faint"><?= e($r['semester'] ?: '—') ?></td>
          <td class="small faint"><?= e(date('M j, Y', strtotime($r['enrolled_at']))) ?></td>
          <td>
            <div class="progress" style="width:110px"><div style="width:<?= min(100, (float)$r['progress']) ?>%"></div></div>
            <span class="tiny faint"><?= (float)$r['progress'] ?>%</span>
          </td>
          <td><span class="badge <?= $r['completed'] ? 'badge-success' : '' ?>"><?= $r['completed'] ? 'COMPLETED' : 'ACTIVE' ?></span></td>
          <td>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-danger" name="remove_enrollment" value="<?= (int)$r['id'] ?>" onclick="return confirm('Remove this enrollment?')"><?= icon('trash') ?> Drop</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="8" class="muted">No enrollments found<?= $q ? ' for “' . e($q) . '”' : '' ?>.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
