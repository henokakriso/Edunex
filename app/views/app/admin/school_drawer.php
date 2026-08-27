<?php /* School detail — fetched into the item drawer (partial render) */
$typeIco = ['school' => icon('school'), 'university' => icon('graduation'), 'college' => icon('university'), 'training' => icon('wrench'), 'other' => icon('building')];
?>
<div class="drawer-profile">
  <div class="drawer-avatar" style="font-size:1.5rem"><?= $typeIco[$school['type']] ?? icon('school') ?></div>
  <div class="min-0 flex-1">
    <b class="drawer-name"><?= e($school['name']) ?></b>
    <p class="tiny faint ellipsis"><?= e($school['code']) ?> · <?= e(ucfirst($school['type'])) ?> · <?= e($school['school_type'] ?? 'public') ?></p>
    <div class="flex gap-6" style="margin-top:7px;flex-wrap:wrap">
      <span class="badge <?= $school['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= e($school['status']) ?></span>
      <?php
        $approvalBadge = match($school['approval_status'] ?? 'pending') {
          'pending' => 'badge-warning',
          'regional_approved' => 'badge-accent',
          'ministry_approved' => 'badge-success',
          'rejected' => 'badge-danger',
          default => 'badge-warning',
        };
        $approvalLabel = match($school['approval_status'] ?? 'pending') {
          'pending' => 'Awaiting approval',
          'regional_approved' => 'Regionally approved',
          'ministry_approved' => 'Fully approved',
          'rejected' => 'Rejected',
          default => 'Pending',
        };
      ?>
      <span class="badge <?= $approvalBadge ?>"><?= $approvalLabel ?></span>
      <span class="badge badge-accent"><?= e($school['city'] ?: 'No city') ?></span>
      <?php if (!empty($school['region'])): ?><span class="badge badge-accent"><?= e($school['region']) ?></span><?php endif; ?>
    </div>
  </div>
</div>

<div class="drawer-stats">
  <div class="stat-box"><span class="tiny faint"><?= icon('users-card') ?> Students</span><b><?= number_format($stats['students']) ?></b></div>
  <div class="stat-box"><span class="tiny faint"><?= icon('user') ?>‍<?= icon('school') ?> Teachers</span><b><?= number_format($stats['teachers']) ?></b></div>
  <div class="stat-box"><span class="tiny faint"><?= icon('graduation') ?> Directors</span><b><?= count($directors) ?></b></div>
  <div class="stat-box"><span class="tiny faint"><?= icon('users') ?> Parents</span><b><?= number_format($stats['parents']) ?></b></div>
  <div class="stat-box"><span class="tiny faint"><?= icon('books') ?> Courses</span><b><?= number_format($stats['courses']) ?></b></div>
  <div class="stat-box"><span class="tiny faint"><?= icon('note') ?> Exams</span><b><?= number_format($stats['exams']) ?></b></div>
  <div class="stat-box"><span class="tiny faint"><?= icon('university') ?> Departments</span><b><?= count($depts) ?></b></div>
  <div class="stat-box"><span class="tiny faint"><?= icon('trend-up') ?> Enrollments</span><b><?= number_format($stats['enrollments']) ?></b></div>
</div>

<div class="drawer-actions">
  <a class="drawer-action primary" href="<?= e(url('admin/school&id=' . $school['id'])) ?>"><?= icon('eye') ?> Full profile</a>
  <?php if (($school['approval_status'] ?? 'pending') === 'pending' && in_array($__u['role'], ['regional', 'ministry'])): ?>
    <form method="post" style="display:inline"><input type="hidden" name="approve_school_regional" value="<?= (int)$school['id'] ?>"><?= csrf_field() ?>
      <button class="drawer-action" style="background:var(--success);color:#fff;border:none;cursor:pointer"><?= icon('check') ?> Regional approve</button>
    </form>
  <?php endif; ?>
  <?php if (($school['approval_status'] ?? '') === 'regional_approved' && $__u['role'] === 'ministry'): ?>
    <form method="post" style="display:inline"><input type="hidden" name="approve_school_ministry" value="<?= (int)$school['id'] ?>"><?= csrf_field() ?>
      <button class="drawer-action" style="background:var(--success);color:#fff;border:none;cursor:pointer"><?= icon('check') ?> Ministry approve & activate</button>
    </form>
  <?php endif; ?>
  <?php if (in_array($school['approval_status'] ?? 'pending', ['pending', 'regional_approved']) && in_array($__u['role'], ['regional', 'ministry'])): ?>
    <form method="post" style="display:inline"><input type="hidden" name="reject_school" value="<?= (int)$school['id'] ?>"><?= csrf_field() ?>
      <button class="drawer-action" style="background:var(--danger);color:#fff;border:none;cursor:pointer" onclick="return confirm('Reject this school?')"><?= icon('x') ?> Reject</button>
    </form>
  <?php endif; ?>
</div>

<div class="drawer-section">
  <h4>Directors</h4>
  <?php if ($directors): foreach (array_slice($directors, 0, 3) as $d): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('graduation') ?> <?= e($d['name']) ?></span><span class="val small"><?= e($d['email']) ?> · <span class="badge <?= $d['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= e($d['status']) ?></span></span></div>
  <?php endforeach; else: ?>
    <div class="tiny faint" style="padding:4px 0">No directors assigned yet.</div>
  <?php endif; ?>
</div>

<div class="drawer-section">
  <h4>Recent teachers</h4>
  <?php if ($teachers): foreach ($teachers as $t): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('user') ?>‍<?= icon('school') ?> <?= e($t['name']) ?></span><span class="val small"><?= e($t['email']) ?></span></div>
  <?php endforeach; else: ?>
    <div class="tiny faint" style="padding:4px 0">No teachers yet.</div>
  <?php endif; ?>
</div>

<div class="drawer-section">
  <h4>Recent students</h4>
  <?php if ($students): foreach ($students as $st): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('users-card') ?> <?= e($st['name']) ?></span><span class="val small mono"><?= e($st['student_id']) ?></span></div>
  <?php endforeach; else: ?>
    <div class="tiny faint" style="padding:4px 0">No students yet.</div>
  <?php endif; ?>
</div>

<div class="drawer-section">
  <h4>Courses</h4>
  <?php if ($courses): foreach ($courses as $c): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('books') ?> <?= e($c['title']) ?></span><span class="val small"><?= e($c['code']) ?> · <?= (int)$c['students'] ?> stud</span></div>
  <?php endforeach; else: ?>
    <div class="tiny faint" style="padding:4px 0">No courses yet.</div>
  <?php endif; ?>
</div>

<div class="drawer-section">
  <h4>Departments</h4>
  <div class="chips" style="gap:6px">
    <?php if ($depts): foreach (array_slice($depts, 0, 8) as $d): ?>
      <span class="chip" style="cursor:default"><?= e($d['name']) ?> · <?= (int)$d['members'] ?></span>
    <?php endforeach; else: ?>
      <span class="tiny faint">No departments.</span>
    <?php endif; ?>
  </div>
</div>

<div class="drawer-section">
  <h4>School Info</h4>
  <div class="drawer-row"><span class="lbl"><?= icon('hash') ?> School ID</span><span class="val small mono"><?= e($school['school_id_number'] ?: '—') ?></span></div>
  <div class="drawer-row"><span class="lbl"><?= icon('hash') ?> Tenant ID</span><span class="val small mono"><?= e($school['tenant_id'] ?: '—') ?></span></div>
  <div class="drawer-row"><span class="lbl"><?= icon('pin') ?> Region</span><span class="val"><?= e($school['region'] ?: '—') ?></span></div>
  <div class="drawer-row"><span class="lbl"><?= icon('map') ?> Zone</span><span class="val"><?= e($school['zone_id'] ?: '—') ?></span></div>
  <div class="drawer-row"><span class="lbl"><?= icon('building') ?> Education Level</span><span class="val"><?= e(ucfirst(str_replace('-', ' ', $school['education_level'] ?? ''))) ?></span></div>
  <?php if (!empty($school['established_year'])): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('calendar') ?> Established</span><span class="val"><?= (int)$school['established_year'] ?></span></div>
  <?php endif; ?>
  <?php if (!empty($school['director_name'])): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('user') ?> Director</span><span class="val"><?= e($school['director_name']) ?></span></div>
  <?php endif; ?>
  <?php if (!empty($school['teaching_language'])): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('globe') ?> Language</span><span class="val"><?= e($school['teaching_language']) ?></span></div>
  <?php endif; ?>
  <?php if (!empty($school['academic_year'])): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('calendar') ?> Academic Year</span><span class="val"><?= e($school['academic_year']) ?> (E.C.)</span></div>
  <?php endif; ?>
</div>

<div class="drawer-section">
  <h4>Contact</h4>
  <div class="drawer-row"><span class="lbl"><?= icon('phone') ?> Phone</span><span class="val"><?= e($school['phone'] ?: '—') ?></span></div>
  <?php if (!empty($school['alt_phone'])): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('phone') ?> Alt Phone</span><span class="val"><?= e($school['alt_phone']) ?></span></div>
  <?php endif; ?>
  <div class="drawer-row"><span class="lbl"><?= icon('mail') ?> Email</span><span class="val"><?= e($school['email'] ?: '—') ?></span></div>
  <?php if (!empty($school['website'])): ?>
    <div class="drawer-row"><span class="lbl"><?= icon('globe') ?> Website</span><span class="val"><?= e($school['website']) ?></span></div>
  <?php endif; ?>
  <div class="drawer-row"><span class="lbl"><?= icon('pin') ?> Address</span><span class="val"><?= e($school['address'] ?: '—') ?></span></div>
  <div class="drawer-row"><span class="lbl"><?= icon('calendar') ?> Created</span><span class="val"><?= e(date('M j, Y', strtotime($school['created_at']))) ?></span></div>
</div>