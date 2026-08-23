<?php /* University semesters */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('calendar') ?> Semesters</h1>
    <p class="sub">Academic years and semesters for this university</p>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title"><?= icon('plus') ?> New Semester</h3>
  <form method="post" action="<?= e(url('university/semesters')) ?>" class="flex-col gap-6" style="margin-top:6px">
    <?= csrf_field() ?>
    <select class="input" name="year_id" required>
      <option value="">— Academic Year —</option>
      <?php foreach ($years as $y): ?>
        <option value="<?= (int)$y['id'] ?>"><?= e($y['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <input class="input" name="name" required placeholder="Fall 2026" maxlength="60">
    <div class="grid2">
      <input class="input" type="date" name="start_date">
      <input class="input" type="date" name="end_date">
    </div>
    <button class="btn btn-success" name="create_semester" value="1"><?= icon('save') ?> Create Semester</button>
  </form>
</div>

<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Semester</th><th>Year</th><th>Period</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($semesters as $s): ?>
        <tr>
          <td><b><?= e($s['name']) ?></b></td>
          <td><?= e($s['year_name'] ?? '—') ?></td>
          <td class="tiny faint"><?= $s['start_date'] ? e($s['start_date']) : '—' ?> → <?= $s['end_date'] ? e($s['end_date']) : '—' ?></td>
          <td></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$semesters): ?><tr><td colspan="4" class="tiny faint">No semesters yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
