<?php /* Roster — Roll number, 3-letter subjects, compact */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('doc') ?> Class Roster</h1>
    <p class="sub"><?= e($homeroom['name']) ?> — Final Grade Report</p>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn btn-ghost" href="<?= e(url('teacher/grading')) ?>"><?= icon('arrow-left') ?> Back</a>
    <a class="btn btn-primary" href="<?= e(url('teacher/grading/roster&download=1')) ?>"><?= icon('file') ?> Download PDF</a>
  </div>
</div>

<?php
  $fmt = fn($v) => $v !== null ? number_format($v, 1) : '—';
  $allAvg = array_filter(array_column($rosterData, 'fy_average'));
  $classAvg = $allAvg ? round(array_sum($allAvg) / count($allAvg), 1) : 0;
  $passCount = count(array_filter($rosterData, fn($r) => $r['fy_average'] !== null && $r['fy_average'] >= 50));
  $passRate = count($rosterData) > 0 ? round(($passCount / count($rosterData)) * 100, 0) : 0;
  $totalAbs = array_sum(array_column($rosterData, 'fy_absences'));
  $subjCount = count($courses);
  $colR = 'border-right:1px solid var(--border)';
  $subLabel = fn($c) => strtoupper(mb_substr($c['subject_name'] ?? $c['title'], 0, 3));
?>

<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px">
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:var(--accent-soft);font-size:13px;font-weight:600;color:var(--accent)"><?= icon('users') ?> <?= count($rosterData) ?> Students</div>
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:var(--accent-soft);font-size:13px;font-weight:600;color:var(--accent)"><?= icon('note') ?> <?= $subjCount ?> Subjects</div>
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:color-mix(in srgb, var(--accent) 6%, var(--card));font-size:13px;font-weight:600;color:var(--accent)"><?= number_format($classAvg, 1) ?>% Avg</div>
  <div style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:color-mix(in srgb, var(--success) 6%, var(--card));font-size:13px;font-weight:600;color:var(--success)"><?= $passRate ?>% Pass</div>
</div>

<?php if (empty($rosterData)): ?>
  <div class="card" style="text-align:center;padding:40px"><p class="small" style="color:var(--muted)">No students enrolled in this class yet.</p></div>
<?php else: ?>

<div style="overflow-x:auto;border:1px solid var(--border);border-radius:14px;background:var(--card)">
<table id="roster-table" style="width:100%;border-collapse:collapse;font-size:12px;white-space:nowrap">
  <thead>
    <tr>
      <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:700;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:36px;<?= $colR ?>">#</th>
      <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:700;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);min-width:140px;<?= $colR ?>">Student</th>
      <th style="padding:10px 8px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);min-width:68px;<?= $colR ?>">ID</th>
      <th style="padding:10px 6px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:30px;<?= $colR ?>">Age</th>
      <th style="padding:10px 6px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:28px;<?= $colR ?>">Sex</th>
      <th style="padding:10px 4px;text-align:center;font-size:10px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:32px;<?= $colR ?>"></th>
      <?php foreach ($courses as $c): ?>
        <th style="padding:10px 6px;text-align:center;font-size:11px;font-weight:700;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:46px;<?= $colR ?>" title="<?= e($c['subject_name'] ?? $c['title']) ?>"><?= e($subLabel($c)) ?></th>
      <?php endforeach; ?>
      <th style="padding:10px 6px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:32px;<?= $colR ?>">Abs</th>
      <th style="padding:10px 6px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:46px;<?= $colR ?>">Tot</th>
      <th style="padding:10px 6px;text-align:center;font-size:11px;font-weight:700;color:var(--accent);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:46px;<?= $colR ?>">Avg</th>
      <th style="padding:10px 6px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:40px;cursor:pointer" onclick="sortRoster()" title="Click to cycle: Name → FY → S2 → Avg" id="rank-th">Rank</th>
    </tr>
  </thead>
  <tbody id="roster-body">
  <?php foreach ($rosterData as $ri => $r):
    $roll = $ri + 1;
    $groupBg = $ri % 2 === 0 ? 'var(--card)' : 'color-mix(in srgb, var(--accent) 1.5%, var(--card))';
    $botLine = 'border-bottom:2px solid var(--border)';
  ?>
    <!-- FY row -->
    <tr style="background:<?= $groupBg ?>">
      <td rowspan="3" style="padding:10px 6px;text-align:center;font-weight:800;font-size:13px;color:var(--accent);vertical-align:middle;<?= $botLine ?>;<?= $colR ?>;background:<?= $groupBg ?>"><?= $roll ?></td>
      <td rowspan="3" style="padding:10px 12px;font-weight:600;vertical-align:middle;<?= $botLine ?>;<?= $colR ?>;background:<?= $groupBg ?>">
        <div style="font-size:13px;line-height:1.2"><?= e($r['name']) ?></div>
        <div style="font-size:9px;color:var(--text-secondary);font-weight:400"><?= e($r['sid']) ?></div>
      </td>
      <td rowspan="3" style="padding:8px;text-align:center;vertical-align:middle;font-size:10px;color:var(--text-secondary);<?= $botLine ?>;<?= $colR ?>;background:<?= $groupBg ?>"><?= e($r['sid']) ?></td>
      <td rowspan="3" style="padding:8px;text-align:center;vertical-align:middle;<?= $botLine ?>;<?= $colR ?>;background:<?= $groupBg ?>"><?= $r['age'] ?? '—' ?></td>
      <td rowspan="3" style="padding:8px;text-align:center;vertical-align:middle;font-weight:700;<?= $botLine ?>;<?= $colR ?>;background:<?= $groupBg ?>;color:<?= $r['gender'] === 'M' ? 'var(--info)' : 'var(--accent-2)' ?>"><?= e($r['gender']) ?></td>
      <td style="padding:4px;text-align:center;<?= $botLine ?>;<?= $colR ?>;background:<?= $groupBg ?>">
        <span style="display:inline-block;font-size:8px;font-weight:700;padding:1px 4px;border-radius:3px;background:color-mix(in srgb, var(--accent) 10%, var(--card));color:var(--accent)">FY</span>
      </td>
      <?php foreach ($courses as $c):
        $sub = $r['subjects'][$c['id']] ?? ['fy'=>null];
        $val = $sub['fy'];
        $color = $val === null ? 'var(--text-secondary)' : ($val >= 50 ? 'var(--text)' : 'var(--danger)');
      ?>
        <td style="padding:8px 6px;text-align:center;font-weight:700;font-size:12px;color:<?= $color ?>;<?= $botLine ?>;<?= $colR ?>"><?= $fmt($val) ?></td>
      <?php endforeach; ?>
      <td style="padding:8px;text-align:center;<?= $botLine ?>;<?= $colR ?>;font-weight:<?= $r['fy_absences'] > 0 ? '700' : '400' ?>;color:<?= $r['fy_absences'] > 0 ? 'var(--danger)' : 'var(--text-secondary)' ?>;font-size:12px"><?= $r['fy_absences'] ?></td>
      <td style="padding:8px;text-align:center;font-weight:700;font-size:12px;<?= $botLine ?>;<?= $colR ?>"><?= $fmt($r['fy_total']) ?></td>
      <td style="padding:8px;text-align:center;font-weight:800;font-size:13px;color:var(--accent);<?= $botLine ?>;<?= $colR ?>"><?= $fmt($r['fy_average']) ?></td>
      <td style="padding:8px;text-align:center;font-weight:700;<?= $botLine ?>" data-rank="fy">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:6px;font-size:12px;<?= $r['fy_rank'] <= 3 ? 'background:color-mix(in srgb, var(--accent) 12%, var(--card));color:var(--accent);font-weight:800' : 'background:var(--bg-secondary);color:var(--text-secondary)' ?>"><?= $r['fy_rank'] ?></span>
      </td>
    </tr>
    <!-- S2 row -->
    <tr style="background:<?= $groupBg ?>">
      <td style="padding:3px 4px;text-align:center;font-size:8px;<?= $botLine ?>;<?= $colR ?>;background:<?= $groupBg ?>">
        <span style="display:inline-block;font-size:8px;font-weight:700;padding:1px 4px;border-radius:3px;background:color-mix(in srgb, var(--info) 10%, var(--card));color:var(--info)">S2</span>
      </td>
      <?php foreach ($courses as $c):
        $sub = $r['subjects'][$c['id']] ?? ['s2'=>null];
        $val = $sub['s2'];
        $color = $val === null ? 'var(--text-secondary)' : ($val >= 50 ? 'var(--text-secondary)' : 'var(--danger)');
      ?>
        <td style="padding:4px 6px;text-align:center;font-weight:600;font-size:11px;color:<?= $color ?>;<?= $botLine ?>;<?= $colR ?>"><?= $fmt($val) ?></td>
      <?php endforeach; ?>
      <td style="padding:4px;text-align:center;<?= $botLine ?>;<?= $colR ?>;font-weight:<?= $r['s2_absences'] > 0 ? '700' : '400' ?>;color:<?= $r['s2_absences'] > 0 ? 'var(--danger)' : 'var(--text-secondary)' ?>;font-size:11px"><?= $r['s2_absences'] ?></td>
      <td style="padding:4px;text-align:center;font-weight:600;font-size:11px;color:var(--text-secondary);<?= $botLine ?>;<?= $colR ?>"><?= $fmt($r['s2_total']) ?></td>
      <td style="padding:4px;text-align:center;font-weight:700;font-size:11px;color:var(--text-secondary);<?= $botLine ?>;<?= $colR ?>"><?= $fmt($r['s2_average']) ?></td>
      <td style="padding:4px;text-align:center;<?= $botLine ?>" data-rank="s2">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:5px;background:var(--bg-secondary);color:var(--text-secondary);font-size:10px;font-weight:600"><?= $r['s2_rank'] ?></span>
      </td>
    </tr>
    <!-- Avg row -->
    <tr style="background:<?= $groupBg ?>">
      <td style="padding:3px 4px 10px;text-align:center;font-size:8px;border-bottom:1px solid var(--border);<?= $colR ?>;background:<?= $groupBg ?>">
        <span style="display:inline-block;font-size:8px;font-weight:700;padding:1px 4px;border-radius:3px;background:color-mix(in srgb, var(--success) 10%, var(--card));color:var(--success)">Avg</span>
      </td>
      <?php foreach ($courses as $c):
        $sub = $r['subjects'][$c['id']] ?? ['avg'=>null];
        $val = $sub['avg'];
      ?>
        <td style="padding:4px 6px 10px;text-align:center;font-weight:600;font-size:11px;color:var(--text-secondary);border-bottom:1px solid var(--border);<?= $colR ?>"><?= $fmt($val) ?></td>
      <?php endforeach; ?>
      <td style="padding:4px 10px;text-align:center;border-bottom:1px solid var(--border);<?= $colR ?>;color:var(--text-secondary);font-size:11px"><?= $r['avg_absences'] ?></td>
      <td style="padding:4px;text-align:center;font-weight:600;font-size:11px;color:var(--text-secondary);border-bottom:1px solid var(--border);<?= $colR ?>"><?= $fmt($r['avg_total']) ?></td>
      <td style="padding:4px;text-align:center;font-weight:700;font-size:11px;color:var(--success);border-bottom:1px solid var(--border);<?= $colR ?>"><?= $fmt($r['avg_average']) ?></td>
      <td style="padding:4px 10px;text-align:center;border-bottom:1px solid var(--border)" data-rank="avg">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:5px;background:var(--bg-secondary);color:var(--text-secondary);font-size:10px;font-weight:600"><?= $r['avg_rank'] ?></span>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:16px">
  <div style="padding:14px 20px;border-radius:12px;background:var(--card);border:1px solid var(--border);flex:1;min-width:150px">
    <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;font-weight:600">Class Average</div>
    <div style="font-size:24px;font-weight:800;color:var(--accent)"><?= number_format($classAvg, 1) ?>%</div>
  </div>
  <div style="padding:14px 20px;border-radius:12px;background:var(--card);border:1px solid var(--border);flex:1;min-width:150px">
    <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;font-weight:600">Pass Rate</div>
    <div style="font-size:24px;font-weight:800;color:var(--success)"><?= $passRate ?>%</div>
  </div>
  <div style="padding:14px 20px;border-radius:12px;background:var(--card);border:1px solid var(--border);flex:1;min-width:150px">
    <div style="font-size:11px;color:var(--text-secondary);margin-bottom:4px;font-weight:600">Total Absences</div>
    <div style="font-size:24px;font-weight:800;color:<?= $totalAbs > 0 ? 'var(--danger)' : 'var(--text)' ?>"><?= $totalAbs ?></div>
  </div>
</div>

<div style="margin-top:14px;padding:10px 14px;border-radius:8px;background:var(--bg-secondary);font-size:11px;color:var(--text-secondary)">
  Subject codes: <?php foreach ($courses as $c): ?><b><?= e($subLabel($c)) ?></b>=<?= e(mb_strimwidth($c['subject_name'] ?? $c['title'], 0, 20, '')) ?><?php if (!$c === end($courses)): ?> · <?php endif; endforeach; ?> ·
  <b>FY</b>=Full Year · <b>S2</b>=Semester 2 · <b>Avg</b>=Average · <b>Abs</b>=Absences ·
  Click <b>Rank</b> to cycle sort (Name → FY → S2 → Avg)
</div>

<script>
var _sortMode = 'name';
var _sortCycle = ['name', 'fy', 's2', 'avg'];

function sortRoster() {
  var next = _sortCycle[(_sortCycle.indexOf(_sortMode) + 1) % _sortCycle.length];
  _sortMode = next;
  document.getElementById('rank-th').title = 'Sorted by: ' + next.toUpperCase() + ' (click to cycle)';

  var tbody = document.getElementById('roster-body');
  var groups = [];
  var trs = Array.from(tbody.querySelectorAll('tr'));
  for (var i = 0; i < trs.length; i += 3) {
    groups.push({ fy: trs[i], s2: trs[i+1], avg: trs[i+2] });
  }

  if (next === 'name') {
    groups.sort(function(a, b) {
      var aName = a.fy.querySelectorAll('td')[1];
      var bName = b.fy.querySelectorAll('td')[1];
      return (aName ? aName.textContent.trim() : '').localeCompare(bName ? bName.textContent.trim() : '');
    });
  } else {
    groups.sort(function(a, b) {
      var aEl = a.fy.querySelector('[data-rank="' + next + '"]');
      var bEl = b.fy.querySelector('[data-rank="' + next + '"]');
      return (aEl ? parseInt(aEl.textContent) || 999 : 999) - (bEl ? parseInt(bEl.textContent) || 999 : 999);
    });
  }

  groups.forEach(function(g) {
    tbody.appendChild(g.fy);
    tbody.appendChild(g.s2);
    tbody.appendChild(g.avg);
  });

  // Update roll numbers
  var rows = tbody.querySelectorAll('tr');
  var roll = 1;
  for (var i = 0; i < rows.length; i += 3) {
    var numCell = rows[i].querySelector('td:first-child');
    if (numCell) numCell.textContent = roll++;
  }
}
</script>
<?php endif; ?>
