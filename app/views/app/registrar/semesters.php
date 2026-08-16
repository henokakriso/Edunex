<?php /* Registrar semesters — academic years + semester management */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('calendar') ?> Semesters</h1>
    <p class="sub">Academic years and semesters for this university</p>
  </div>
</div>

<div class="grid2">
  <div class="card">
    <h3 class="card-title"><?= icon('plus') ?> New academic year</h3>
    <form method="post" action="<?= e(url('registrar/semesters')) ?>" class="flex-col gap-6" style="margin-top:6px">
      <?= csrf_field() ?>
      <input class="input" name="name" required placeholder="2026/2027" maxlength="40">
      <div class="grid2">
        <input class="input" type="date" name="start_date">
        <input class="input" type="date" name="end_date">
      </div>
      <button class="btn btn-success" name="create_year" value="1"><?= icon('save') ?> Create year</button>
    </form>
  </div>

  <div class="card">
    <h3 class="card-title"><?= icon('plus') ?> New semester</h3>
    <form method="post" action="<?= e(url('registrar/semesters')) ?>" class="flex-col gap-6" style="margin-top:6px">
      <?= csrf_field() ?>
      <select class="input" name="year_id" required>
        <option value="">— Academic year —</option>
        <?php foreach ($years as $y): ?>
          <option value="<?= (int)$y['id'] ?>"><?= e($y['name']) ?> (<?= (int)$y['sem_count'] ?> semesters)</option>
        <?php endforeach; ?>
      </select>
      <input class="input" name="name" required placeholder="Semester 2" maxlength="60">
      <div class="grid2">
        <input class="input" type="date" name="start_date">
        <input class="input" type="date" name="end_date">
      </div>
      <button class="btn btn-success" name="create_semester" value="1"><?= icon('save') ?> Create semester</button>
    </form>
  </div>
</div>

<h3 style="margin:18px 0 8px"><?= icon('clock') ?> Existing semesters</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Semester</th><th>Year</th><th>Period</th><th>Status</th><th style="width:170px"></th></tr>
    </thead>
    <tbody>
      <?php foreach ($semRows as $s): ?>
        <tr>
          <td><b><?= e($s['name']) ?></b></td>
          <td><?= e($s['year_name']) ?></td>
          <td class="tiny faint"><?= $s['start_date'] ? e($s['start_date']) : '—' ?> → <?= $s['end_date'] ? e($s['end_date']) : '—' ?></td>
          <td>
            <?php if ((int)$s['is_active']): ?>
              <span class="badge badge-success"><?= icon('check') ?> Active</span>
            <?php else: ?>
              <span class="badge">Inactive</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!(int)$s['is_active']): ?>
              <form method="post" action="<?= e(url('registrar/semesters')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-ghost" name="set_active" value="<?= (int)$s['id'] ?>"><?= icon('play') ?> Set active</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$semRows): ?><tr><td colspan="5" class="tiny faint">No semesters yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
