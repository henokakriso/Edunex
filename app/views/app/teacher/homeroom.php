<?php /* Teacher homeroom overview */
$res = count(array_filter($students, fn($s) => $s['avg'] !== null));
?>
<div class="page-head">
  <div>
    <h1><?= icon('school') ?> Homeroom — <?= e($group['name']) ?></h1>
    <p class="sub">
      <?php if ($group['grade'] || $group['section']): ?>Grade <?= e($group['grade']) ?><?= $group['section'] ? ' · Section ' . e($group['section']) : '' ?> — <?php endif; ?>
      <?= count($students) ?> student(s) · round results, averages &amp; class rank
    </p>
  </div>
</div>

<?php if (count($groups) > 1): ?>
  <div class="flex gap-8" style="margin-bottom:18px;flex-wrap:wrap">
    <?php foreach ($groups as $g): ?>
      <a class="btn <?= (int)$g['id'] === (int)$group['id'] ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('teacher/homeroom&group=' . (int)$g['id'])) ?>"><?= icon('school') ?> <?= e($g['name']) ?></a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!$students): ?>
  <div class="empty-state">
    <div class="empty-ic"><?= icon('users') ?></div>
    <h3>No students in this class yet</h3>
    <p class="small">Students are added to this class by the director or during registration.</p>
  </div>
<?php else: ?>

  <div class="grid2" style="margin-bottom:18px">
    <div class="card stat-card">
      <span class="stat-ico"><?= icon('trend-up') ?></span>
      <div style="min-width:0">
        <div class="stat-value"><?= $classAvg !== null ? $classAvg . '%' : '—' ?></div>
        <div class="small faint">Class average</div>
        <div class="tiny faint"><?= $res ? $res . ' of ' . count($students) . ' students have results' : 'No grades recorded yet' ?></div>
      </div>
    </div>
    <div class="card stat-card">
      <span class="stat-ico"><?= icon('medal') ?></span>
      <div style="min-width:0">
        <div class="stat-value"><?= $top ? e(mb_substr($top['name'], 0, 22)) : '—' ?></div>
        <div class="small faint">Top of class</div>
        <div class="tiny faint"><?= $top ? 'Semester average ' . $top['avg'] . '%' : 'Waiting for results' ?></div>
      </div>
    </div>
    <div class="card stat-card">
      <span class="stat-ico"><?= icon('calendar') ?></span>
      <div style="min-width:0">
        <div class="stat-value"><?= $classAtt !== null ? $classAtt . '%' : '—' ?></div>
        <div class="small faint">Class attendance</div>
        <div class="tiny faint">Present rate across recorded days</div>
      </div>
    </div>
    <div class="card stat-card">
      <span class="stat-ico"><?= icon('note') ?></span>
      <div style="min-width:0">
        <div class="stat-value"><?= count($subjects) ?></div>
        <div class="small faint">Subjects with courses</div>
        <div class="tiny faint">Analysed below per course</div>
      </div>
    </div>
  </div>

  <div class="card">
    <h3 class="card-title"><?= icon('users') ?> Class results — <?= e($group['name']) ?></h3>
    <?php if ($res === 0): ?>
      <div class="alert alert-info" style="margin-bottom:0">
        <?= icon('info') ?> No semester results yet. Averages and ranks appear here automatically once teachers <b>send exam results</b> (Grading → <b>Send results</b>) or grade assignments.
      </div>
    <?php else: ?>
    <table class="table">
      <thead><tr><th>#</th><th>Student</th><th>Semester avg</th><th>Exams</th><th>Assignments</th><th>Attendance</th></tr></thead>
      <tbody>
        <?php foreach ($students as $s): ?>
          <tr>
            <td><?= $s['rank'] !== null ? $s['rank'] : '<span class="tiny faint">—</span>' ?></td>
            <td>
              <div class="flex gap-8" style="align-items:center">
                <div class="avatar" style="width:30px;height:30px;font-size:.7rem"><?= e(mb_substr($s['name'], 0, 1)) ?></div>
                <div><b class="small"><?= e($s['name']) ?></b><p class="tiny faint"><?= e($s['student_id']) ?></p></div>
              </div>
            </td>
            <td>
              <?php if ($s['avg'] !== null): ?>
                <b><?= $s['avg'] ?>%</b>
                <div class="progress progress-sm" style="margin-top:4px"><div style="width:<?= min(100, $s['avg']) ?>%;background:<?= $s['avg'] >= 50 ? 'var(--success)' : 'var(--danger)' ?>"></div></div>
              <?php else: ?><span class="tiny faint">no results</span><?php endif; ?>
            </td>
            <td class="small"><?= $s['exams_pct'] !== null ? $s['exams_pct'] . '%' : '<span class="faint">—</span>' ?></td>
            <td class="small"><?= $s['assign_pct'] !== null ? $s['assign_pct'] . '%' : '<span class="faint">—</span>' ?></td>
            <td class="small"><?= $s['att_pct'] !== null ? $s['att_pct'] . '%' : '<span class="faint">—</span>' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php if ($subjects): ?>
  <div class="card" style="margin-top:18px">
    <h3 class="card-title"><?= icon('note') ?> Subject analysis — <?= e($group['name']) ?></h3>
    <p class="small faint" style="margin-top:-6px">Average result of this class per course (exams and assignments combined where available).</p>
    <table class="table">
      <thead><tr><th>Course</th><th>Subject</th><th>Teacher</th><th>Enrolled</th><th>Exam avg</th><th>Assignment avg</th></tr></thead>
      <tbody>
        <?php foreach ($subjects as $sub): ?>
          <tr>
            <td><b class="small"><?= e($sub['course']) ?></b></td>
            <td class="small"><?= e($sub['subject']) ?></td>
            <td class="small"><?= e($sub['teacher']) ?></td>
            <td class="small"><?= (int)$sub['enrolled'] ?></td>
            <td class="small"><?= $sub['exam_pct'] !== null ? $sub['exam_pct'] . '% <span class="faint">(' . (int)$sub['exam_n'] . ')</span>' : '<span class="faint">—</span>' ?></td>
            <td class="small"><?= $sub['assign_pct'] !== null ? $sub['assign_pct'] . '% <span class="faint">(' . (int)$sub['assign_n'] . ')</span>' : '<span class="faint">—</span>' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

<?php endif; ?>
