<?php /* Roster — Class grade report with 3 rows per student (FY, S2, AVG) */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('doc') ?> Class Roster</h1>
    <p class="sub"><?= e($homeroom['name']) ?> — Final Grade Report</p>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn btn-ghost" href="<?= e(url('teacher/grading')) ?>"><?= icon('arrow-left') ?> Back</a>
    <a class="btn btn-primary" href="<?= e(url('teacher/grading/roster&pdf=1')) ?>"><?= icon('file') ?> Generate PDF</a>
  </div>
</div>

<?php
  $fmt = fn($v) => $v !== null ? number_format($v, 1) : '—';
  $allAvg = array_filter(array_column($rosterData, 'fy_average'));
  $classAvg = $allAvg ? round(array_sum($allAvg) / count($allAvg), 1) : 0;
  $passCount = count(array_filter($rosterData, fn($r) => $r['fy_average'] !== null && $r['fy_average'] >= 50));
  $passRate = count($rosterData) > 0 ? round(($passCount / count($rosterData)) * 100, 0) : 0;
  $totalAbs = array_sum(array_column($rosterData, 'fy_absences'));
?>

<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px">
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:var(--accent-soft);font-size:13px;font-weight:600;color:var(--accent)">
    <?= icon('users') ?> <?= count($rosterData) ?> Students
  </div>
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:var(--accent-soft);font-size:13px;font-weight:600;color:var(--accent)">
    <?= icon('note') ?> <?= count($courses) ?> Subjects
  </div>
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:color-mix(in srgb, var(--accent) 6%, var(--card));font-size:13px;font-weight:600;color:var(--accent)">
    <?= number_format($classAvg, 1) ?>% Class Average
  </div>
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:color-mix(in srgb, var(--success) 6%, var(--card));font-size:13px;font-weight:600;color:var(--success)">
    <?= $passRate ?>% Pass Rate
  </div>
</div>

<?php if (empty($rosterData)): ?>
  <div class="card" style="text-align:center;padding:40px">
    <p class="small" style="color:var(--muted)">No students enrolled in this class yet.</p>
  </div>
<?php else: ?>

<div style="overflow-x:auto;border:1px solid var(--border);border-radius:12px;background:var(--card)">
<table style="width:100%;border-collapse:collapse;font-size:12px;white-space:nowrap">
  <thead>
    <tr style="border-bottom:2px solid var(--border)">
      <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:160px">Student Name</th>
      <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:70px">ID</th>
      <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:36px">Age</th>
      <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:32px">Sex</th>
      <?php foreach ($courses as $c): ?>
        <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:55px;border-left:2px solid var(--border)"><?= e(mb_strimwidth($c['subject_name'] ?? $c['title'], 0, 10, '')) ?></th>
      <?php endforeach; ?>
      <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);border-left:2px solid var(--border)">Abs</th>
      <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary)">Total</th>
      <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:700;color:var(--accent)">Average</th>
      <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:40px">Rank</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($rosterData as $r): ?>
    <!-- Full Year row -->
    <tr style="border-bottom:1px solid var(--border);background:var(--card)">
      <td style="padding:10px 12px;font-weight:600;border-top:2px solid var(--text)">
        <?= e($r['name']) ?>
        <span style="display:inline-block;font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;background:color-mix(in srgb, var(--accent) 10%, var(--card));color:var(--accent);margin-left:6px">FY</span>
      </td>
      <td style="padding:10px 8px;text-align:center;font-size:10px;color:var(--text-secondary)"><?= e($r['sid']) ?></td>
      <td style="padding:10px 8px;text-align:center"><?= $r['age'] ?? '—' ?></td>
      <td style="padding:10px 8px;text-align:center;font-weight:700;color:<?= $r['gender'] === 'M' ? 'var(--info)' : 'var(--accent-2)' ?>"><?= e($r['gender']) ?></td>
      <?php foreach ($courses as $c):
        $sub = $r['subjects'][$c['id']] ?? ['fy'=>null];
      ?>
        <td style="padding:8px;text-align:center;font-weight:700;border-left:2px solid var(--border);color:<?= ($sub['fy'] ?? 0) >= 50 ? 'var(--text)' : 'var(--danger)' ?>"><?= $fmt($sub['fy']) ?></td>
      <?php endforeach; ?>
      <td style="padding:8px;text-align:center;border-left:2px solid var(--border);font-weight:<?= $r['fy_absences'] > 0 ? '700' : '400' ?>;color:<?= $r['fy_absences'] > 0 ? 'var(--danger)' : 'var(--text-secondary)' ?>"><?= $r['fy_absences'] ?></td>
      <td style="padding:8px;text-align:center;font-weight:700"><?= $fmt($r['fy_total']) ?></td>
      <td style="padding:8px;text-align:center;font-weight:800;font-size:14px;color:var(--accent)"><?= $fmt($r['fy_average']) ?></td>
      <td style="padding:8px;text-align:center;font-weight:700">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;font-size:12px;<?= $r['rank'] <= 3 ? 'background:color-mix(in srgb, var(--accent) 12%, var(--card));color:var(--accent)' : 'background:var(--bg-secondary);color:var(--text-secondary)' ?>"><?= $r['rank'] ?></span>
      </td>
    </tr>
    <!-- Semester 2 row -->
    <tr style="border-bottom:1px solid var(--border);background:color-mix(in srgb, var(--accent) 2%, var(--card))">
      <td style="padding:6px 12px 6px 28px;color:var(--text-secondary);font-size:10px">
        <span style="display:inline-block;font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;background:color-mix(in srgb, var(--info) 10%, var(--card));color:var(--info)">S2</span>
      </td>
      <td style="padding:6px 8px;text-align:center;font-size:9px;color:var(--text-secondary);opacity:.5"><?= e($r['sid']) ?></td>
      <td></td><td></td>
      <?php foreach ($courses as $c):
        $sub = $r['subjects'][$c['id']] ?? ['s2'=>null];
      ?>
        <td style="padding:6px 8px;text-align:center;font-weight:600;font-size:11px;border-left:2px solid var(--border);color:<?= ($sub['s2'] ?? 0) >= 50 ? 'var(--text-secondary)' : 'var(--danger)' ?>"><?= $fmt($sub['s2']) ?></td>
      <?php endforeach; ?>
      <td style="padding:6px 8px;text-align:center;border-left:2px solid var(--border);color:<?= $r['s2_absences'] > 0 ? 'var(--danger)' : 'var(--text-secondary)' ?>;font-weight:<?= $r['s2_absences'] > 0 ? '700' : '400' ?>;font-size:11px"><?= $r['s2_absences'] ?></td>
      <td style="padding:6px 8px;text-align:center;font-weight:600;font-size:11px;color:var(--text-secondary)"><?= $fmt($r['s2_total']) ?></td>
      <td style="padding:6px 8px;text-align:center;font-weight:700;font-size:12px;color:var(--text-secondary)"><?= $fmt($r['s2_average']) ?></td>
      <td></td>
    </tr>
    <!-- Average row -->
    <tr style="border-bottom:2px solid var(--border);background:color-mix(in srgb, var(--success) 3%, var(--card))">
      <td style="padding:6px 12px 10px 28px;color:var(--text-secondary);font-size:10px;border-bottom:2px solid var(--border)">
        <span style="display:inline-block;font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;background:color-mix(in srgb, var(--success) 10%, var(--card));color:var(--success)">AVG</span>
      </td>
      <td style="padding:6px 8px 10px;text-align:center;font-size:9px;color:var(--text-secondary);opacity:.5;border-bottom:2px solid var(--border)"><?= e($r['sid']) ?></td>
      <td style="border-bottom:2px solid var(--border)"></td><td style="border-bottom:2px solid var(--border)"></td>
      <?php foreach ($courses as $c):
        $sub = $r['subjects'][$c['id']] ?? ['avg'=>null];
      ?>
        <td style="padding:6px 8px 10px;text-align:center;font-weight:600;font-size:11px;border-left:2px solid var(--border);border-bottom:2px solid var(--border);color:var(--text-secondary)"><?= $fmt($sub['avg']) ?></td>
      <?php endforeach; ?>
      <td style="padding:6px 8px 10px;text-align:center;border-left:2px solid var(--border);border-bottom:2px solid var(--border);color:var(--text-secondary);font-size:11px"><?= $r['avg_absences'] ?></td>
      <td style="padding:6px 8px 10px;text-align:center;font-weight:600;font-size:11px;border-bottom:2px solid var(--border);color:var(--text-secondary)"><?= $fmt($r['avg_total']) ?></td>
      <td style="padding:6px 8px 10px;text-align:center;font-weight:700;font-size:12px;border-bottom:2px solid var(--border);color:var(--success)"><?= $fmt($r['avg_average']) ?></td>
      <td style="border-bottom:2px solid var(--border)"></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:16px">
  <div style="padding:12px 18px;border-radius:10px;background:var(--card);border:1px solid var(--border);flex:1;min-width:150px">
    <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;font-weight:600">Class Average</div>
    <div style="font-size:22px;font-weight:800;color:var(--accent)"><?= number_format($classAvg, 1) ?>%</div>
  </div>
  <div style="padding:12px 18px;border-radius:10px;background:var(--card);border:1px solid var(--border);flex:1;min-width:150px">
    <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;font-weight:600">Pass Rate</div>
    <div style="font-size:22px;font-weight:800;color:var(--success)"><?= $passRate ?>%</div>
  </div>
  <div style="padding:12px 18px;border-radius:10px;background:var(--card);border:1px solid var(--border);flex:1;min-width:150px">
    <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;font-weight:600">Total Absences</div>
    <div style="font-size:22px;font-weight:800;color:<?= $totalAbs > 0 ? 'var(--danger)' : 'var(--text)' ?>"><?= $totalAbs ?></div>
  </div>
</div>

<div style="margin-top:12px;padding:10px 14px;border-radius:8px;background:var(--bg-secondary);font-size:11px;color:var(--text-secondary)">
  <b>Legend:</b> FY = Full Year (all assessments) · S2 = Semester 2 only · AVG = Average of FY and S2 ·
  Abs = Absence Days · Total = Sum of Subject Averages · Average = Total ÷ Subjects · Rank = Class Standing (by FY Average)
</div>

<?php endif; ?>
