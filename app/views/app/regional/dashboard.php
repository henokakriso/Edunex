<?php /* Regional admin dashboard — assigned schools overview */
$cards = [
  ['Assigned Schools', $stats['schools'], icon('building'), 'regional/schools'],
  ['Students', $stats['students'], icon('graduation'), 'regional/analytics'],
  ['Teachers', $stats['teachers'], icon('user') . icon('school'), 'regional/analytics'],
  ['Directors', $stats['directors'], icon('target'), 'regional/directors'],
  ['Courses', $stats['courses'], icon('courses'), 'regional/analytics'],
  ['Enrollments (30d)', $stats['enroll30'], icon('trend-up'), 'regional/analytics'],
  ['Pending Transfers', $stats['pending_transfers'], icon('refresh'), 'regional/analytics'],
];
?>
<div class="page-head">
  <div>
    <h1><?= icon('globe') ?> Regional Overview</h1>
    <p class="sub">Your assigned schools only — <?= (int)$stats['schools'] ?> of <?= (int)$stats['load'] ?>% school capacity</p>
  </div>
  <div class="flex gap-8">
    <a class="btn btn-primary" href="<?= e(url('regional/directors')) ?>"><?= icon('user-plus') ?> Add director</a>
    <a class="btn btn-ghost" href="<?= e(url('regional/announcements')) ?>"><?= icon('megaphone') ?> Announce</a>
  </div>
</div>

<?php if ($stats['schools'] > 15 || $stats['schools'] > 0 && $stats['ratio'] > 2): ?>
  <div class="card" style="border-color:var(--danger);padding:12px 16px;margin-bottom:16px">
    <b><?= icon('alert') ?> Workload recommendation</b>
    <p class="small" style="margin:4px 0 0">
      You manage <?= (int)$stats['schools'] ?> school(s) and <?= (int)$stats['directors'] ?> director(s).
      Recommended capacity is 15 schools or 2 directors per school per admin
      — <?= (int)$stats['schools'] > 15 ? 'consider splitting schools between additional regional admins.' : 'consider adding a second regional admin for this region.' ?>
    </p>
  </div>
<?php endif; ?>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:22px">
  <?php foreach ($cards as [$label, $val, $ic, $link]): ?>
    <a class="card stat-card" href="<?= e(url($link)) ?>" style="padding:16px 14px;text-decoration:none">
      <div class="stat-icon" style="font-size:22px"><?= $ic ?></div>
      <div>
        <div class="stat-value" style="font-size:1.5rem"><?= (int)$val ?></div>
        <div class="small faint"><?= $label ?></div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<h3 class="card-title" style="margin:0 0 10px"><?= icon('building') ?> Your schools</h3>
<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px">
  <?php foreach ($schoolStats as $s): ?>
    <a class="card" href="<?= e(url('regional/school&id=' . (int)$s['school']['id'])) ?>" style="padding:16px;text-decoration:none">
      <div class="flex gap-10" style="align-items:flex-start">
        <div class="avatar" style="background:var(--accent-soft)"><?= icon('school') ?></div>
        <div class="flex-1">
          <b class="small"><?= e($s['school']['name']) ?> <span class="tiny faint">(<?= e($s['school']['code']) ?>)</span></b>
          <p class="tiny faint"><?= e($s['school']['city'] ?: '—') ?> · <?= e($s['school']['type']) ?></p>
        </div>
        <span class="badge <?= ($s['school']['status'] ?? 'active') === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= e($s['school']['status'] ?? 'active') ?></span>
      </div>
      <div class="flex gap-10 small" style="margin-top:10px;color:var(--text-2)">
        <span><?= icon('graduation') ?> <?= (int)$s['students'] ?> students</span>
        <span><?= icon('user') ?> <?= (int)$s['teachers'] ?> teachers</span>
        <span><?= icon('target') ?> <?= (int)$s['directors'] ?> directors</span>
        <span><?= icon('courses') ?> <?= (int)$s['courses'] ?> courses</span>
      </div>
    </a>
  <?php endforeach; ?>
  <?php if (!$schoolStats): ?>
    <div class="card muted" style="padding:24px;grid-column:1/-1">No schools assigned to you yet. Ask a Super Admin to assign schools under <b>Users → Admin (Regional)</b>.</div>
  <?php endif; ?>
</div>
