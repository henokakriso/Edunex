<?php /* ERP HR — positions, staff, attendance, leave */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('users') ?> HR Management</h1>
    <p class="sub">Positions, staff records, attendance and leave</p>
  </div>
  <div class="flex gap-6">
    <button class="btn btn-ghost" data-open-modal="position-modal"><?= icon('plus') ?> Position</button>
    <button class="btn btn-primary" data-open-modal="staff-modal"><?= icon('plus') ?> Staff record</button>
  </div>
</div>

<div class="card" style="margin-bottom:14px">
  <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> My time card</h3>
  <div class="flex gap-6" style="flex-wrap:wrap;align-items:center">
    <?php if ($staffToday && $staffToday['check_in']): ?>
      <span class="badge badge-success">In <?= e($staffToday['check_in']) ?></span>
      <?php if (!$staffToday['check_out']): ?><form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-xs" name="clock_out" value="1"><?= icon('logout') ?> Check out</button></form><?php endif; ?>
    <?php else: ?>
      <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-xs btn-primary" name="clock_in" value="1"><?= icon('login') ?> Check in now</button></form>
    <?php endif; ?>
    <span class="tiny faint">Mark daily attendance for staff in bulk below.</span>
  </div>
</div>

<div class="card pad-0" style="margin-bottom:18px">
  <h3 class="card-title" style="padding:12px 14px 0"><?= icon('users') ?> Staff (<?= count($staff) ?>)</h3>
  <table class="table">
    <thead><tr><th>Staff member</th><th>Position</th><th>Department</th><th>Supervisor</th><th>Hired</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($staff as $r): ?>
        <tr>
          <td><b><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b><div class="tiny faint"><?= e($r['email']) ?> · <?= e($r['role']) ?></div></td>
          <td><?= e($r['position'] ?: '—') ?></td>
          <td><?= e($r['dept'] ?: '—') ?></td>
          <td class="tiny"><?= e($r['sup_first'] ? $r['sup_first'] . ' ' . $r['sup_last'] : '—') ?></td>
          <td class="tiny"><?= e($r['hire_date'] ?: '—') ?></td>
          <td><span class="badge badge-<?= $r['status'] === 'active' ? 'success' : 'info' ?>"><?= e($r['status']) ?></span><?= demo_badge($r) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$staff): ?><tr><td colspan="6" class="muted">No staff records yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="grid2">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('calendar') ?> Daily attendance</h3>
    <form method="post" class="grid2" style="gap:8px">
      <?= csrf_field() ?>
      <div class="flex-col"><label class="small faint">Date *</label><input class="input" type="date" name="work_date" value="<?= date('Y-m-d') ?>" required></div>
      <div><label class="small faint">Status per active staff</label>
        <div class="flex-col gap-6" style="max-height:220px;overflow:auto;margin-top:4px">
          <?php foreach ($staff as $r): ?>
            <label class="flex gap-6" style="font-size:.85rem">
              <span style="min-width:180px"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></span>
              <select class="input" name="att_<?= (int)$r['user_id'] ?>" style="padding:2px 6px">
                <option value="">—</option>
                <?php foreach (['present', 'late', 'remote', 'absent'] as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?>
              </select>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div><button class="btn btn-primary" name="mark_attendance" value="1"><?= icon('save') ?> Save attendance</button></div>
    </form>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('medical') ?> Leave requests</h3>
    <div class="flex-col gap-6" style="max-height:280px;overflow:auto">
      <?php foreach ($leave as $l): ?>
        <div class="card" style="padding:10px 12px">
          <div class="flex-between" style="gap:8px;flex-wrap:wrap">
            <div>
              <b><?= e($l['first_name'] . ' ' . $l['last_name']) ?></b>
              <span class="badge badge-<?= $l['status'] === 'approved' ? 'success' : ($l['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= e($l['status']) ?></span><?= demo_badge($l) ?>
              <div class="tiny faint"><?= e($l['type']) ?> · <?= e($l['start_date']) ?> → <?= e($l['end_date']) ?> (<?= (int)$l['days'] ?>d) · <?= e($l['position'] ?: '—') ?></div>
              <div class="tiny"><?= e($l['reason'] ?: '') ?></div>
            </div>
            <?php if ($l['status'] === 'pending'): ?>
              <div class="flex gap-6">
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="leave_id" value="<?= (int)$l['id'] ?>"><button class="btn btn-xs btn-success" name="decide_leave" value="approved">Approve</button></form>
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="leave_id" value="<?= (int)$l['id'] ?>"><button class="btn btn-xs btn-danger" name="decide_leave" value="rejected">Reject</button></form>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$leave): ?><div class="muted small">No leave requests.</div><?php endif; ?>
    </div>
  </div>
</div>

<div class="card pad-0" style="margin-top:18px">
  <h3 class="card-title" style="padding:12px 14px 0"><?= icon('clock') ?> Recent attendance (<?= count($attendance) ?>)</h3>
  <table class="table">
    <thead><tr><th>Date</th><th>Staff</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($attendance as $a): ?>
        <tr>
          <td><?= e($a['work_date']) ?></td>
          <td><?= e($a['first_name'] . ' ' . $a['last_name']) ?></td>
          <td><?= e($a['check_in'] ?: '—') ?></td>
          <td><?= e($a['check_out'] ?: '—') ?></td>
          <td><span class="badge badge-<?= $a['status'] === 'present' ? 'success' : ($a['status'] === 'late' ? 'warning' : 'info') ?>"><?= e($a['status']) ?></span><?= demo_badge($a) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- modals -->
<div class="modal" id="position-modal"><div class="modal-content">
  <h3><?= icon('plus') ?> New position</h3>
  <form method="post" class="flex-col gap-6">
    <?= csrf_field() ?>
    <div class="grid2">
      <input class="input" name="title" placeholder="Title *" required>
      <select class="input" name="department_id"><option value="0">— Department —</option><?php foreach ($depts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select>
      <select class="input" name="level"><?php foreach (['staff', 'senior', 'management', 'executive'] as $l): ?><option><?= $l ?></option><?php endforeach; ?></select>
      <input class="input" type="number" step="0.01" name="salary_scale" placeholder="Salary scale (ETB) *" required>
    </div>
    <button class="btn btn-primary" name="add_position" value="1"><?= icon('save') ?> Create position</button>
  </form>
</div></div>

<div class="modal" id="staff-modal"><div class="modal-content">
  <h3><?= icon('plus') ?> Staff record</h3>
  <form method="post" class="flex-col gap-6">
    <?= csrf_field() ?>
    <div class="grid2">
      <select class="input" name="user_id" required><option value="">— User —</option><?php foreach ($candidates as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
      <select class="input" name="position_id"><option value="0">— Position —</option><?php foreach ($positions as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['title']) ?> (<?= number_format((float)$p['salary_scale']) ?>)</option><?php endforeach; ?></select>
      <select class="input" name="supervisor_id"><option value="0">— Supervisor —</option><?php foreach ($staff as $s): ?><option value="<?= (int)$s['user_id'] ?>"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></option><?php endforeach; ?></select>
      <select class="input" name="employment_type"><?php foreach (['full', 'part', 'contract'] as $t): ?><option><?= $t ?></option><?php endforeach; ?></select>
      <input class="input" type="date" name="hire_date" value="<?= date('Y-m-d') ?>">
    </div>
    <button class="btn btn-primary" name="add_staff" value="1"><?= icon('save') ?> Save staff record</button>
  </form>
</div></div>
