<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transcript Verification</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', system-ui, sans-serif; background: #0f0f1a; color: #e0e0e0; padding: 20px; }
    .container { max-width: 700px; margin: 0 auto; background: #1a1a2e; border-radius: 12px; padding: 30px; border: 1px solid #2a2a4a; }
    .header { text-align: center; margin-bottom: 24px; border-bottom: 1px solid #2a2a4a; padding-bottom: 16px; }
    .header h1 { font-size: 1.4em; color: #fff; margin-bottom: 4px; }
    .header p { color: #888; font-size: 0.9em; }
    .student-info { text-align: center; margin: 16px 0; }
    .student-info h2 { font-size: 1.1em; color: #fff; margin-bottom: 4px; }
    .student-info p { color: #888; font-size: 0.85em; }
    .gpa-box { display: flex; justify-content: center; gap: 40px; margin: 20px 0; }
    .gpa-item { text-align: center; }
    .gpa-item .value { font-size: 1.8em; font-weight: 700; color: #fff; }
    .gpa-item .label { color: #888; font-size: 0.8em; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #2a2a4a; font-size: 0.9em; }
    th { color: #888; font-weight: 500; }
    .semester-header { background: #0a0a1a; font-weight: 600; color: #aaa; }
    .hash { text-align: center; margin-top: 20px; padding: 12px; background: #0a0a1a; border-radius: 8px; font-family: monospace; font-size: 0.8em; color: #888; word-break: break-all; }
    .info { text-align: center; color: #666; font-size: 0.75em; margin-top: 12px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Official Academic Transcript</h1>
      <p><?= e($request['school_name']) ?></p>
    </div>
    <div class="student-info">
      <h2><?= e($request['student_name']) ?></h2>
      <p>ID: <?= e($request['sid_no'] ?? '—') ?></p>
      <p>Type: <?= e(ucfirst($request['type'])) ?></p>
    </div>
    <div class="gpa-box">
      <div class="gpa-item">
        <div class="value"><?= number_format($cgpa['cgpa'], 2) ?></div>
        <div class="label">CGPA</div>
      </div>
      <div class="gpa-item">
        <div class="value"><?= (int)$cgpa['credit_hours'] ?></div>
        <div class="label">Credits</div>
      </div>
      <div class="gpa-item">
        <div class="value"><?= number_format($cgpa['quality_points'], 1) ?></div>
        <div class="label">Quality Points</div>
      </div>
    </div>
    <table>
      <thead>
        <tr><th>Code</th><th>Course</th><th>Credits</th><th>Grade</th><th>Points</th><th>Semester</th></tr>
      </thead>
      <tbody>
        <?php $currentSem = ''; foreach ($records as $r): ?>
          <?php if ($r['semester_name'] !== $currentSem): ?>
            <?php $currentSem = $r['semester_name']; ?>
            <tr class="semester-header"><td colspan="6"><?= e($currentSem) ?></td></tr>
          <?php endif; ?>
          <tr>
            <td><?= e($r['code']) ?></td>
            <td><?= e($r['title']) ?></td>
            <td><?= (int)$r['credit_hours'] ?></td>
            <td><b><?= e($r['grade']) ?></b></td>
            <td><?= number_format((float)$r['grade_points'], 1) ?></td>
            <td></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if ($request['hash']): ?>
      <div class="hash">SHA-256: <?= e($request['hash']) ?></div>
    <?php endif; ?>
    <div class="info">
      Verified on <?= e(date('Y-m-d H:i:s')) ?> · This is an official digital transcript verification.
    </div>
  </div>
</body>
</html>
