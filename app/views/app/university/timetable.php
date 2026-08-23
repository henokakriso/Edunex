<?php /* Timetable view */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('clock') ?> Timetable</h1>
    <p class="sub">Weekly class schedule</p>
  </div>
</div>

<div style="margin-bottom:18px">
  <form method="get" action="<?= e(url('university/timetable')) ?>" class="flex-row gap-6">
    <input type="hidden" name="r" value="university/timetable">
    <select class="input" name="semester_id" style="width:200px">
      <?php foreach ($semesters as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (int)$s['id'] === $activeSem ? 'selected' : '' ?>><?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary"><?= icon('search') ?> View</button>
  </form>
</div>

<?php
$days = ['monday','tuesday','wednesday','thursday','friday','saturday'];
$byDay = [];
foreach ($rows as $r) { $byDay[$r['day']][] = $r; }
?>
<div class="card pad-0" style="overflow-x:auto">
  <table class="table">
    <thead>
      <tr>
        <th style="width:100px">Time</th>
        <?php foreach ($days as $d): ?>
          <th style="min-width:140px;text-transform:capitalize"><?= e($d) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php for ($h = 8; $h <= 17; $h++): ?>
        <tr>
          <td class="tiny" style="font-weight:600"><?= sprintf('%02d:00', $h) ?></td>
          <?php foreach ($days as $d): ?>
            <td>
              <?php foreach (($byDay[$d] ?? []) as $r):
                $startH = (int)date('H', strtotime($r['start_time']));
                if ($startH === $h): ?>
                  <div style="background:var(--primary-bg,#1a1a2e);border-left:3px solid var(--primary);padding:4px 6px;border-radius:4px;margin:2px 0;font-size:0.85em">
                    <div style="font-weight:600"><?= e($r['code']) ?></div>
                    <div class="tiny faint"><?= e($r['title']) ?></div>
                    <div class="tiny faint"><?= e($r['lecturer'] ?? '') ?> <?= $r['room'] ? '· ' . e($r['room']) : '' ?></div>
                  </div>
                <?php endif; endforeach; ?>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>
</div>
<?php if (!$rows): ?><p class="tiny faint" style="margin-top:12px">No scheduled classes for this semester.</p><?php endif; ?>
