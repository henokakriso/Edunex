<?php /* Registrar transcripts — per-student academic record */
?>
<div class="page-head">
  <div>
    <h1><?= icon('grades') ?> Transcripts</h1>
    <p class="sub">Academic record per student</p>
  </div>
  <form method="get" class="flex gap-6" action="<?= e(url('registrar/transcripts')) ?>">
    <select class="input" name="student_id" style="min-width:260px" required>
      <option value="">— Select student —</option>
      <?php foreach ($students as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (int)$studentId === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> (<?= e($s['student_id'] ?: '—') ?>)</option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary"><?= icon('search') ?> Load</button>
  </form>
</div>

<?php if ($student): ?>
  <div class="card" style="margin-bottom:18px">
    <div class="flex gap-12" style="align-items:center;flex-wrap:wrap">
      <div class="avatar" style="width:52px;height:52px;font-size:22px"><?= e(mb_substr($student['first_name'], 0, 1)) ?></div>
      <div class="flex-1">
        <b style="font-size:1.1rem"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></b>
        <p class="tiny faint"><?= e($student['email']) ?> · ID <?= e($student['student_id'] ?: '—') ?> · <?= e($student['group_name'] ?: 'No group') ?></p>
      </div>
      <div class="flex-col small" style="text-align:right">
        <span class="badge <?= ($student['enrollment_status'] ?? 'active') === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($student['enrollment_status'] ?? 'active') ?></span>
        <span class="tiny faint"><?= e($student['school_name']) ?></span>
        <span class="tiny faint"><?= $student['national_id'] ? 'NID ' . e($student['national_id']) : '' ?></span>
      </div>
    </div>
  </div>

  <?php if ($journey): ?>
  <div class="card" style="margin-bottom:18px">
    <h3 class="card-title" style="margin-top:0"><?= icon('compass') ?> Lifelong education journey</h3>
    <p class="tiny faint" style="margin-top:-6px">One profile follows this student from KG through university.</p>
    <div class="flex-col gap-8">
      <?php foreach ($journey as $j): ?>
        <div class="flex gap-10" style="align-items:center">
          <span class="badge"><?= e(ucwords(str_replace('_', ' ', (string)$j['entry_type']))) ?></span>
          <div class="flex-1 small">
            <b><?= e($j['school_name']) ?></b>
            <span class="tiny faint"> · <?= e(ucfirst((string)$j['education_level'])) ?> level</span>
            <p class="tiny faint" style="margin:0"><?= e(date('M Y', strtotime($j['entered_at']))) ?> — <?= $j['left_at'] ? e(date('M Y', strtotime($j['left_at']))) : 'present' ?><?= $j['notes'] ? ' · ' . e($j['notes']) : '' ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr><th>Course</th><th>Code</th><th>Credits</th><th>Semester</th><th>Exam avg</th><th>Assignment avg</th><th>Progress</th><th>Completed</th></tr>
      </thead>
      <tbody>
        <?php foreach ($transcript as $t): ?>
          <tr>
            <td><b><?= e($t['course']) ?></b></td>
            <td><?= e($t['course_code'] ?: '—') ?></td>
            <td class="tiny"><?= (float)$t['credits'] ?></td>
            <td class="tiny faint"><?= $t['semester'] ? e($t['semester']) . ($t['year_name'] ? ' · ' . e($t['year_name']) : '') : '—' ?></td>
            <td><?= $t['exam_avg'] !== null ? e($t['exam_avg']) . '%' : '—' ?></td>
            <td><?= $t['assign_avg'] !== null ? e($t['assign_avg']) . '%' : '—' ?></td>
            <td><div class="progress" style="width:110px"><div style="width:<?= min(100, (float)$t['progress']) ?>%"></div></div> <span class="tiny faint"><?= (float)$t['progress'] ?>%</span></td>
            <td><span class="badge <?= $t['completed'] ? 'badge-success' : '' ?>"><?= $t['completed'] ? 'YES' : 'NO' ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$transcript): ?><tr><td colspan="8" class="muted">No enrollments for this student yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($transcript): ?>
    <div class="flex gap-8" style="margin-top:14px;flex-wrap:wrap">
      <div class="card stat-card" style="padding:14px 16px"><b class="stat-value"><?= $cgpa !== null ? number_format($cgpa, 2) : '—' ?></b><div class="small faint">CGPA (4.0)</div></div>
      <div class="card stat-card" style="padding:14px 16px"><b class="stat-value"><?= number_format($totalCredits, 1) ?></b><div class="small faint">Total credits</div></div>
      <div class="card stat-card" style="padding:14px 16px"><b class="stat-value"><?= count($transcript) ?></b><div class="small faint">Courses taken</div></div>
      <div class="card stat-card" style="padding:14px 16px"><b class="stat-value"><?= count(array_filter($transcript, fn($t) => $t['completed'])) ?></b><div class="small faint">Completed</div></div>
      <button class="btn btn-ghost" onclick="window.print()"><?= icon('printer') ?> Print</button>
    </div>

    <?php if ($semesterGpas): ?>
    <div class="card" style="margin-top:14px">
      <h3 class="card-title" style="margin-top:0"><?= icon('calendar') ?> GPA by semester</h3>
      <table class="table">
        <thead><tr><th>Semester</th><th>GPA</th><th>Credits attempted</th></tr></thead>
        <tbody>
          <?php foreach ($semesterGpas as $sg): ?>
            <tr>
              <td><?= e($sg['name']) ?></td>
              <td><b><?= $sg['gpa'] !== null ? number_format($sg['gpa'], 2) : '—' ?></b></td>
              <td class="tiny"><?= number_format($sg['credits'], 1) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="tiny faint" style="margin-top:8px">Grade points: A ≥ 90 = 4.0 · B ≥ 80 = 3.0 · C ≥ 70 = 2.0 · D ≥ 60 = 1.0 · F = 0. Final score = 60% exams + 40% assignments.</p>
    </div>
    <?php endif; ?>
  <?php endif; ?>
<?php elseif ($studentId): ?>
  <p class="muted">Student not found in your school.</p>
<?php else: ?>
  <div class="card muted" style="padding:28px">Select a student above to load their transcript.</div>
<?php endif; ?>
