<?php /* Roster — Roll number, 3-letter subjects, clickable sort on all columns */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('doc') ?> Class Roster</h1>
    <p class="sub"><?= e($homeroom['name']) ?> — Final Grade Report</p>
  </div>
  <div style="display:flex;gap:8px">
    <a class="btn btn-ghost" href="<?= e(url('teacher/grading')) ?>"><?= icon('arrow-left') ?> Back</a>
    <button class="btn btn-ghost" onclick="window.print()" style="cursor:pointer"><?= icon('file') ?> Print</button>
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
  $sortStyle = 'cursor:pointer;user-select:none';
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
      <th style="padding:10px 6px;text-align:center;font-size:11px;font-weight:700;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:36px;<?= $colR ?>;<?=$sortStyle?>" onclick="sortCol('roll')" title="Sort by roll number">#</th>
      <th style="padding:10px 10px;text-align:left;font-size:11px;font-weight:700;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);min-width:130px;<?= $colR ?>;<?=$sortStyle?>" onclick="sortCol('name')" title="Sort by name">Student</th>
      <th style="padding:10px 6px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);min-width:60px;<?= $colR ?>">ID</th>
      <th style="padding:10px 4px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:28px;<?= $colR ?>">Age</th>
      <th style="padding:10px 4px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:26px;<?= $colR ?>">Sex</th>
      <th style="padding:10px 4px;text-align:center;font-size:10px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:30px;<?= $colR ?>"></th>
      <?php foreach ($courses as $c): ?>
        <th style="padding:10px 4px;text-align:center;font-size:11px;font-weight:700;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:42px;<?= $colR ?>;<?=$sortStyle?>" onclick="sortCol('sub_<?= $c['id'] ?>')" title="Sort by <?= e($c['subject_name'] ?? $c['title']) ?>"><?= e($subLabel($c)) ?></th>
      <?php endforeach; ?>
      <th style="padding:10px 4px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:30px;<?= $colR ?>;<?=$sortStyle?>" onclick="sortCol('abs')" title="Sort by absences">Abs</th>
      <th style="padding:10px 4px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:40px;<?= $colR ?>;<?=$sortStyle?>" onclick="sortCol('total')" title="Sort by total">Tot</th>
      <th style="padding:10px 4px;text-align:center;font-size:11px;font-weight:700;color:var(--accent);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:40px;<?= $colR ?>;<?=$sortStyle?>" onclick="sortCol('avg')" title="Sort by average">Avg</th>
      <th style="padding:10px 4px;text-align:center;font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-secondary);border-bottom:2px solid var(--border);width:38px;<?=$sortStyle?>" onclick="sortCol('rank')" title="Sort by rank">Rank</th>
    </tr>
  </thead>
  <tbody id="roster-body">
  <?php foreach ($rosterData as $ri => $r):
    $roll = $ri + 1;
    $groupBg = $ri % 2 === 0 ? 'var(--card)' : 'color-mix(in srgb, var(--accent) 1.5%, var(--card))';
    $botLine = 'border-bottom:2px solid var(--border)';
  ?>
    <tr style="background:<?= $groupBg ?>">
      <td rowspan="3" style="padding:10px 4px;text-align:center;font-weight:800;font-size:13px;color:var(--accent);vertical-align:middle;<?= $botLine ?>;<?= $colR ?>;background:<?= $groupBg ?>"><?= $roll ?></td>
      <td rowspan="3" style="padding:10px 10px;font-weight:600;vertical-align:middle;<?= $botLine ?>;<?= $colR ?>;background:<?= $groupBg ?>">
        <div style="font-size:13px;line-height:1.2" data-sort-name="<?= e($r['name']) ?>"><?= e($r['name']) ?></div>
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
        <td style="padding:8px 4px;text-align:center;font-weight:700;font-size:12px;color:<?= $color ?>;<?= $botLine ?>;<?= $colR ?>" data-sort="sub_<?= $c['id'] ?>"><?= $fmt($val) ?></td>
      <?php endforeach; ?>
      <td style="padding:8px;text-align:center;<?= $botLine ?>;<?= $colR ?>;font-weight:<?= $r['fy_absences'] > 0 ? '700' : '400' ?>;color:<?= $r['fy_absences'] > 0 ? 'var(--danger)' : 'var(--text-secondary)' ?>;font-size:12px" data-sort="abs"><?= $r['fy_absences'] ?></td>
      <td style="padding:8px;text-align:center;font-weight:700;font-size:12px;<?= $botLine ?>;<?= $colR ?>" data-sort="total"><?= $fmt($r['fy_total']) ?></td>
      <td style="padding:8px;text-align:center;font-weight:800;font-size:13px;color:var(--accent);<?= $botLine ?>;<?= $colR ?>" data-sort="avg"><?= $fmt($r['fy_average']) ?></td>
      <td style="padding:8px;text-align:center;font-weight:700;<?= $botLine ?>" data-rank="fy" data-sort="rank">
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
        <td style="padding:4px 4px;text-align:center;font-weight:600;font-size:11px;color:<?= $color ?>;<?= $botLine ?>;<?= $colR ?>"><?= $fmt($val) ?></td>
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
        <td style="padding:4px 4px 10px;text-align:center;font-weight:600;font-size:11px;color:var(--text-secondary);border-bottom:1px solid var(--border);<?= $colR ?>"><?= $fmt($val) ?></td>
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

<div style="display:flex;gap:8px;flex-wrap:wrap;margin:14px 0;padding:10px 14px;border-radius:8px;background:var(--bg-secondary);font-size:11px;color:var(--text-secondary)">
  <span style="margin-right:4px">Subject codes:</span>
  <?php foreach ($courses as $i => $c): ?>
    <span><b><?= e($subLabel($c)) ?></b>=<?= e(mb_strimwidth($c['subject_name'] ?? $c['title'], 0, 20, '')) ?><?php if ($i < count($courses) - 1): ?><span style="margin:0 3px;opacity:.4">|</span><?php endif; ?></span>
  <?php endforeach; ?>
  <span style="margin-left:auto;opacity:.6">Click any column header to sort ↕</span>
</div>

<script>
var _sortCol = 'roll';
var _sortAsc = true;

function sortCol(col) {
  if (_sortCol === col) { _sortAsc = !_sortAsc; } else { _sortCol = col; _sortAsc = true; }

  var tbody = document.getElementById('roster-body');
  var groups = [];
  var trs = Array.from(tbody.querySelectorAll('tr'));
  for (var i = 0; i < trs.length; i += 3) {
    groups.push({ fy: trs[i], s2: trs[i+1], avg: trs[i+2] });
  }

  groups.sort(function(a, b) {
    var va, vb;
    if (col === 'roll') {
      va = parseInt(a.fy.querySelector('td:first-child').textContent) || 0;
      vb = parseInt(b.fy.querySelector('td:first-child').textContent) || 0;
    } else if (col === 'name') {
      va = a.fy.querySelector('[data-sort-name]').getAttribute('data-sort-name');
      vb = b.fy.querySelector('[data-sort-name]').getAttribute('data-sort-name');
      return _sortAsc ? va.localeCompare(vb) : vb.localeCompare(va);
    } else if (col === 'rank') {
      va = parseInt(a.fy.querySelector('[data-rank="fy"]').textContent) || 999;
      vb = parseInt(b.fy.querySelector('[data-rank="fy"]').textContent) || 999;
    } else if (col === 'abs') {
      va = parseInt(a.fy.querySelector('[data-sort="abs"]').textContent) || 0;
      vb = parseInt(b.fy.querySelector('[data-sort="abs"]').textContent) || 0;
    } else if (col === 'total') {
      va = parseFloat(a.fy.querySelector('[data-sort="total"]').textContent) || 0;
      vb = parseFloat(b.fy.querySelector('[data-sort="total"]').textContent) || 0;
    } else if (col === 'avg') {
      va = parseFloat(a.fy.querySelector('[data-sort="avg"]').textContent) || 0;
      vb = parseFloat(b.fy.querySelector('[data-sort="avg"]').textContent) || 0;
    } else if (col.startsWith('sub_')) {
      var subId = col.replace('sub_', '');
      va = parseFloat(a.fy.querySelector('[data-sort="sub_' + subId + '"]').textContent) || 0;
      vb = parseFloat(b.fy.querySelector('[data-sort="sub_' + subId + '"]').textContent) || 0;
    } else {
      return 0;
    }
    return _sortAsc ? (va - vb) : (vb - va);
  });

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
