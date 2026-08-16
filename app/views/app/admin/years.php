<?php /* Admin academic years view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('calendar') ?> Academic Years & Semesters</h1>
    <p class="sub">Organize the school calendar</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('new-year').style.display='block';this.style.display='none'">+ New year</button>
</div>

<form method="post" class="card" id="new-year" style="display:none;margin-bottom:18px">
  <?= csrf_field() ?>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">School *</label>
      <select class="input" name="school_id" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Name *</label><input class="input" name="name" required placeholder="2026/27"></div>
    <div class="flex-col"><label class="small faint">Start</label><input class="input" type="date" name="start_date"></div>
    <div class="flex-col"><label class="small faint">End</label><input class="input" type="date" name="end_date"></div>
  </div>
  <button class="btn btn-success" name="create_year" value="1"><?= icon('plus') ?> Create</button>
</form>

<div class="flex-col gap-16">
  <?php foreach ($years as $y): ?>
    <div class="card">
      <div class="flex-between" style="flex-wrap:wrap;gap:10px">
        <div>
          <b><?= e($y['name']) ?></b>
          <?php if ($y['is_current']): ?><span class="badge badge-success">Current</span><?php endif; ?>
          <span class="badge badge-muted"><?= e($y['school_name']) ?></span>
          <p class="tiny faint" style="margin-top:4px"><?= $y['start_date'] ? e(date('M j, Y', strtotime($y['start_date']))) . ' → ' . e(date('M j, Y', strtotime($y['end_date']))) : 'Dates not set' ?></p>
        </div>
        <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm <?= $y['is_current'] ? 'btn-ghost' : 'btn-success' ?>" name="set_current" value="<?= (int)$y['id'] ?>"><?= $y['is_current'] ? '✓ Current' : 'Set current' ?></button></form>
      </div>
      <div style="border-top:1px solid var(--border);margin-top:12px;padding-top:12px">
        <?php foreach ($y['semesters'] as $sem): ?>
          <div class="list-row" style="padding:6px 0">
            <span class="small flex-1"><b><?= e($sem['name']) ?></b> <span class="faint">· <?= $sem['start_date'] ? e(date('M j', strtotime($sem['start_date']))) . ' → ' . e(date('M j', strtotime($sem['end_date']))) : '' ?></span></span>
          </div>
        <?php endforeach; ?>
        <form method="post" class="flex gap-8" style="margin-top:8px">
          <?= csrf_field() ?>
          <input type="hidden" name="year_id" value="<?= (int)$y['id'] ?>">
          <input class="input" name="name" placeholder="Semester name (e.g. Semester I)" style="max-width:260px">
          <input class="input" type="date" name="start_date" style="max-width:150px">
          <input class="input" type="date" name="end_date" style="max-width:150px">
          <button class="btn btn-sm" name="create_semester" value="1"><?= icon('plus') ?> Semester</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$years): ?><div class="alert alert-info">No academic years yet.</div><?php endif; ?>
</div>
