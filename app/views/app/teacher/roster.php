<?php /* Roster — Class grade report (homeroom teacher only) */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('doc') ?> Class Roster</h1>
    <p class="sub"><?= e($homeroom['name']) ?> — Final Grade Report</p>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn btn-ghost" href="<?= e(url('teacher/grading')) ?>"><?= icon('arrow-left') ?> Back to Gradebook</a>
    <a class="btn btn-primary" href="<?= e(url('teacher/grading/roster&download=1')) ?>"><?= icon('file') ?> Download PDF</a>
  </div>
</div>

<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px">
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:var(--accent-soft);font-size:13px;font-weight:600;color:var(--accent)">
    <?= icon('users') ?> <?= count($rosterData) ?> Students
  </div>
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:var(--accent-soft);font-size:13px;font-weight:600;color:var(--accent)">
    <?= icon('note') ?> <?= count($courses) ?> Subjects
  </div>
</div>

<?php if (empty($rosterData)): ?>
  <div class="card" style="text-align:center;padding:40px">
    <p class="small" style="color:var(--muted)">No students enrolled in this class yet.</p>
  </div>
<?php else: ?>

<?php
  $subjectCount = count($courses);
  $fmt = fn($v) => $v !== null ? number_format($v, 1) : '—';
?>

<div style="overflow-x:auto;border:1px solid var(--border);border-radius:12px;background:var(--card)">
<table style="width:100%;border-collapse:collapse;font-size:12px;white-space:nowrap">
  <thead>
    <!-- Subject group row -->
    <tr style="border-bottom:2px solid var(--border)">
      <th rowspan="2" style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-secondary);position:sticky;left:0;background:var(--card);z-index:2;min-width:160px">Student Name</th>
      <th rowspan="2" style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);position:sticky;left:160px;background:var(--card);z-index:2;min-width:70px">ID</th>
      <th rowspan="2" style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:40px">Age</th>
      <th rowspan="2" style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:36px">Sex</th>
      <?php foreach ($courses as $c): ?>
        <th colspan="3" style="padding:8px 4px;text-align:center;font-size:11px;font-weight:700;color:var(--accent);border-left:2px solid var(--border);background:color-mix(in srgb, var(--accent) 4%, var(--card))"><?= e(mb_strimwidth($c['subject_name'] ?? $c['title'], 0, 12, '')) ?></th>
      <?php endforeach; ?>
      <th rowspan="2" style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);border-left:2px solid var(--border);min-width:40px">Abs</th>
      <th rowspan="2" style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:50px">Total</th>
      <th rowspan="2" style="padding:10px 8px;text-align:center;font-size:11px;font-weight:700;color:var(--accent);min-width:60px">Average</th>
      <th rowspan="2" style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);min-width:44px">Rank</th>
    </tr>
    <!-- Sub-header row -->
    <tr style="border-bottom:2px solid var(--border)">
      <?php foreach ($courses as $c): ?>
        <th style="padding:6px 4px;text-align:center;font-size:9px;font-weight:600;color:var(--text-secondary);border-left:2px solid var(--border);background:color-mix(in srgb, var(--accent) 3%, var(--card))">FY</th>
        <th style="padding:6px 4px;text-align:center;font-size:9px;font-weight:600;color:var(--text-secondary);background:color-mix(in srgb, var(--accent) 3%, var(--card))">S2</th>
        <th style="padding:6px 4px;text-align:center;font-size:9px;font-weight:600;color:var(--text-secondary);background:color-mix(in srgb, var(--accent) 3%, var(--card))">Avg</th>
      <?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    <?php $alt = false; foreach ($rosterData as $r):
      $bg = $alt ? 'color-mix(in srgb, var(--accent) 1.5%, var(--card))' : 'var(--card)';
      $alt = !$alt;
    ?>
    <tr style="border-bottom:1px solid var(--border);background:<?= $bg ?>;transition:background .1s" onmouseover="this.style.background='color-mix(in srgb, var(--accent) 4%, var(--card))'" onmouseout="this.style.background='<?= $bg ?>'">
      <td style="padding:10px 12px;position:sticky;left:0;background:<?= $bg ?>;z-index:1;border-right:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:28px;height:28px;border-radius:50%;background:color-mix(in srgb, var(--accent) 10%, var(--card));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--accent);flex-shrink:0"><?= e(mb_substr(explode(', ', $r['name'])[1] ?? '', 0, 1)) ?><?= e(mb_substr($r['name'], 0, 1)) ?></div>
          <span style="font-weight:600;font-size:13px"><?= e($r['name']) ?></span>
        </div>
      </td>
      <td style="padding:10px 8px;text-align:center;position:sticky;left:160px;background:<?= $bg ?>;z-index:1;border-right:1px solid var(--border);font-size:10px;color:var(--text-secondary)"><?= e($r['sid']) ?></td>
      <td style="padding:10px 8px;text-align:center"><?= $r['age'] !== null ? $r['age'] : '—' ?></td>
      <td style="padding:10px 8px;text-align:center;font-weight:700;color:<?= $r['gender'] === 'M' ? 'var(--info)' : 'var(--accent-2)' ?>"><?= e($r['gender']) ?></td>
      <?php foreach ($courses as $c):
        $sub = $r['subjects'][$c['id']] ?? ['s1'=>null,'s2'=>null,'avg'=>null];
        $avgVal = ($sub['s1'] !== null && $sub['s2'] !== null) ? round(($sub['s1'] + $sub['s2']) / 2, 1) : null;
      ?>
        <td style="padding:8px 4px;text-align:center;font-weight:700;font-size:11px;border-left:2px solid var(--border);color:<?= ($sub['avg'] ?? 0) >= 50 ? 'var(--text)' : 'var(--danger)' ?>"><?= $fmt($sub['avg']) ?></td>
        <td style="padding:8px 4px;text-align:center;font-size:10px;color:var(--text-secondary)"><?= $fmt($sub['s2']) ?></td>
        <td style="padding:8px 4px;text-align:center;font-size:10px;color:var(--text-secondary)"><?= $fmt($avgVal) ?></td>
      <?php endforeach; ?>
      <td style="padding:10px 8px;text-align:center;border-left:2px solid var(--border);font-weight:<?= $r['absences'] > 0 ? '700' : '400' ?>;color:<?= $r['absences'] > 0 ? 'var(--danger)' : 'var(--text-secondary)' ?>"><?= $r['absences'] ?></td>
      <td style="padding:10px 8px;text-align:center;font-weight:700"><?= number_format($r['total'], 1) ?></td>
      <td style="padding:10px 8px;text-align:center;font-weight:800;font-size:14px;color:var(--accent)"><?= $r['average'] !== null ? number_format($r['average'], 1) : '—' ?></td>
      <td style="padding:10px 8px;text-align:center;font-weight:700">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;font-size:12px;<?= $r['rank'] <= 3 ? 'background:color-mix(in srgb, var(--accent) 12%, var(--card));color:var(--accent)' : 'background:var(--bg-secondary);color:var(--text-secondary)' ?>"><?= $r['rank'] ?></span>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:16px">
  <div style="padding:12px 18px;border-radius:10px;background:var(--card);border:1px solid var(--border);flex:1;min-width:200px">
    <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;font-weight:600">Class Average</div>
    <?php
      $allAvg = array_filter(array_column($rosterData, 'average'));
      $classAvg = $allAvg ? round(array_sum($allAvg) / count($allAvg), 1) : '—';
    ?>
    <div style="font-size:22px;font-weight:800;color:var(--accent)"><?= is_numeric($classAvg) ? number_format($classAvg, 1) . '%' : $classAvg ?></div>
  </div>
  <div style="padding:12px 18px;border-radius:10px;background:var(--card);border:1px solid var(--border);flex:1;min-width:200px">
    <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;font-weight:600">Pass Rate</div>
    <?php
      $passCount = count(array_filter($rosterData, fn($r) => $r['average'] !== null && $r['average'] >= 50));
      $passRate = count($rosterData) > 0 ? round(($passCount / count($rosterData)) * 100, 0) : 0;
    ?>
    <div style="font-size:22px;font-weight:800;color:var(--success)"><?= $passRate ?>%</div>
  </div>
  <div style="padding:12px 18px;border-radius:10px;background:var(--card);border:1px solid var(--border);flex:1;min-width:200px">
    <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;font-weight:600">Total Absences</div>
    <?php $totalAbs = array_sum(array_column($rosterData, 'absences')); ?>
    <div style="font-size:22px;font-weight:800;color:<?= $totalAbs > 0 ? 'var(--danger)' : 'var(--text)' ?>"><?= $totalAbs ?></div>
  </div>
</div>

<div style="margin-top:12px;padding:10px 14px;border-radius:8px;background:var(--bg-secondary);font-size:11px;color:var(--text-secondary)">
  <b>Legend:</b> FY = Full Year Average · S2 = Semester 2 Total · Avg = Semester Average (FY+S2)/2 ·
  Abs = Absence Days · Total = Sum of Subject Averages · Average = Total ÷ Subjects · Rank = Class Standing
</div>

<?php endif; ?>
