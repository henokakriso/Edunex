<?php /* Admin dashboard view — executive control center */
$cards = [
  ['Total Schools', $stats['schools'], icon('school'), 'admin/schools'],
  ['Total Students', $stats['students'], icon('user') . '‍' . icon('graduation'), 'admin/users&role=student'],
  ['Total Teachers', $stats['teachers'], icon('user') . '‍' . icon('school'), 'admin/users&role=teacher'],
  ['Total Parents', $stats['parents'], icon('users'), 'admin/users&role=parent'],
  ['Total Directors', $stats['directors'], icon('target'), 'admin/users&role=director'],
  ['Active Users', $stats['active_users'], icon('check-circle'), 'admin/users'],
  ['Online Now', $stats['online'], icon('check-circle'), 'admin/users'],
  ['Courses', $stats['courses'], icon('graduation'), 'admin/courses'],
  ['Subjects', $stats['subjects'], icon('search'), 'admin/subjects'],
  ['Departments', $stats['departments'], icon('folder'), 'admin/departments'],
  ['Exams', $stats['exams'], icon('note'), 'admin/courses'],
  ['Announcements', $stats['announcements'], icon('megaphone'), 'admin/announcements'],
  ['Messages', $stats['messages'], icon('chat'), 'messages'],
  ['Transfers', $stats['transfers'], icon('refresh'), 'admin/transfers'],
  ["Today's Attendance", $stats['attendance_today'], icon('doc'), 'admin/reports'],
  ['Pending Requests', $stats['pending'], icon('loader'), 'admin/transfers'],
  ['Storage Used', human_bytes($stats['storage']), icon('save'), 'admin/backups'],
  ['AI Messages', $stats['ai_msgs'], icon('robot'), 'admin/analytics'],
  ['AI Users', $stats['ai_users'], icon('brain'), 'admin/analytics'],
  ['Enrollments', $stats['enrollments'], icon('users'), 'admin/courses'],
  ['Regional Admins', $stats['admins'], icon('globe'), 'admin/users&role=admin'],
];
?>
<div class="page-head">
  <div>
    <h1><?= icon('university') ?> Admin Overview</h1>
    <p class="sub">Executive control center · platform-wide</p>
  </div>
  <div class="flex gap-8">
    <a class="btn btn-primary" href="<?= e(url('admin/users')) ?>"><?= icon('user') ?> New user</a>
    <a class="btn btn-ghost" href="<?= e(url('admin/announcements')) ?>"><?= icon('megaphone') ?> Announce</a>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:24px">
  <?php foreach ($cards as [$label, $val, $icon, $link]): ?>
    <a class="card stat-card" href="<?= e(url($link)) ?>" style="padding:16px 14px;transition:transform .15s ease,box-shadow .15s ease;border:1px solid var(--border)">
      <div class="stat-icon" style="font-size:22px"><?= $icon ?></div>
      <div>
        <div class="stat-value" style="font-size:1.5rem"><?= is_int($val) ? (int)$val : e($val) ?></div>
        <div class="small faint"><?= $label ?></div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px">
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('trend-up') ?> Student &amp; teacher growth</h3>
    <div id="growth-chart"></div>
  </div>
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('school') ?> School growth</h3>
    <div id="school-chart"></div>
  </div>
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('key') ?> Daily logins — 30 days</h3>
    <div id="login-chart"></div>
  </div>
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Attendance trend — 30 days</h3>
    <div id="attendance-chart"></div>
  </div>
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('users') ?> User roles</h3>
    <div class="flex gap-16" style="align-items:center;flex-wrap:wrap">
      <div id="role-chart" width="120"></div>
      <div class="flex-col gap-4" style="flex:1;min-width:140px">
        <?php foreach ($roleDist as $i => $rd): ?>
          <div class="flex gap-6 small" style="align-items:center">
            <span class="swatch" style="width:10px;height:10px;border-radius:2px;background:var(--chart-<?= ($i % 6) + 1 ?>)"></span>
            <b class="flex-1"><?= e(role_label($rd['role'])) ?></b>
            <span class="faint"><?= (int)$rd['n'] ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('chart-bar') ?> Grade distribution</h3>
    <div id="grade-chart"></div>
  </div>
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('calendar') ?> Monthly registrations</h3>
    <div id="reg-chart"></div>
  </div>
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('check-circle') ?> Course completion rate</h3>
    <div id="comp-chart"></div>
  </div>
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('refresh') ?> Transfer statistics</h3>
    <div class="flex gap-16" style="align-items:center;flex-wrap:wrap">
      <div id="transfer-chart" width="120"></div>
      <div class="flex-col gap-4" style="flex:1;min-width:140px">
        <?php foreach ($tLabels as $i => $tl): ?>
          <div class="flex gap-6 small" style="align-items:center">
            <span class="swatch" style="width:10px;height:10px;border-radius:2px;background:var(--chart-<?= ($i % 6) + 1 ?>)"></span>
            <b class="flex-1"><?= e(ucfirst($tl)) ?></b>
            <span class="faint"><?= (int)($tDist[$i] ?? 0) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="card" style="padding:22px">
    <h3 class="card-title" style="margin-top:0"><?= icon('clock') ?> Platform activity — last 7 days</h3>
    <div id="activity-chart"></div>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;margin-top:24px">
  <div class="card" style="padding:20px">
    <h3 class="card-title" style="margin-top:0"><?= icon('school') ?> Schools</h3>
    <?php foreach ($schoolStats as $s): ?>
      <a class="list-row" href="<?= e(url('admin/school&id=' . $s['id'])) ?>" style="padding:10px 12px;margin:4px 0;border-radius:10px;text-decoration:none;transition:background .15s">
        <div class="avatar" style="background:var(--accent-soft)"><?= icon('school') ?></div>
        <div class="flex-1">
          <b class="small"><?= e($s['name']) ?> <span class="tiny faint">(<?= e($s['code']) ?>)</span></b>
          <p class="tiny faint"><?= (int)$s['users'] ?> users · <?= (int)$s['courses'] ?> courses · <?= (int)$s['enrollments'] ?> enrollments</p>
        </div>
        <span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= e($s['status']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="card" style="padding:20px">
    <h3 class="card-title" style="margin-top:0"><?= icon('spark') ?> Recent signups</h3>
    <?php foreach ($newUsers as $nu): ?>
      <a class="list-row" href="<?= e(url('admin/user&id=' . $nu['id'])) ?>" style="padding:10px 12px;margin:4px 0;border-radius:10px;text-decoration:none;transition:background .15s">
        <div class="avatar"><?= e(mb_substr((string)$nu['name'], 0, 1)) ?></div>
        <div class="flex-1 small">
          <b><?= e($nu['name']) ?></b>
          <p class="tiny faint"><?= e($nu['email']) ?> · <?= e($nu['role']) ?> · <?= e(time_ago($nu['created_at'])) ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="card" style="padding:20px">
    <h3 class="card-title" style="margin-top:0"><?= icon('note') ?> Recent activity</h3>
    <?php foreach (array_slice($activity, 0, 8) as $a): ?>
      <div class="list-row" style="padding:10px 12px;margin:4px 0;border-radius:10px">
        <div class="flex-1 small">
          <b><?= e($a['user_name'] ?: 'System') ?></b> — <?= e($a['description']) ?>
          <p class="tiny faint"><?= e($a['action']) ?> · <?= e(time_ago($a['created_at'])) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="card" style="padding:20px">
    <h3 class="card-title" style="margin-top:0"><?= icon('chain') ?> Blockchain integrity</h3>
    <p class="tiny faint" style="margin-top:-4px">Hash-chained ledger per school — grades, attendance & certificates</p>
    <?php foreach ($ledger as $lg): ?>
      <a class="list-row" href="<?= e(url('admin/ledger&school=' . $lg['id'])) ?>" style="padding:10px 12px;margin:4px 0;border-radius:10px;text-decoration:none;transition:background .15s">
        <div class="flex-1 small"><b><?= e($lg['school']) ?></b><p class="tiny faint"><?= (int)$lg['entries'] ?> chained records</p></div>
        <span class="badge <?= $lg['ok'] ? 'badge-success' : 'badge-danger' ?>"><?= $lg['ok'] ? icon('check-circle') . ' INTACT' : icon('ban-circle') . ' BROKEN #' . (int)$lg['broken_at'] ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$ledger): ?><p class="muted small">No schools yet.</p><?php endif; ?>
  </div>
  <div class="card" style="padding:20px">
    <h3 class="card-title" style="margin-top:0"><?= icon('globe') ?> Regional admin performance</h3>
    <p class="tiny faint" style="margin-top:-4px">Capacity: 15 schools per admin · workload balance recommendations</p>
    <?php foreach ($adminPerf as $ap): ?>
      <a class="list-row" href="<?= e(url('admin/user&id=' . (int)$ap['id'])) ?>" style="padding:10px 12px;margin:4px 0;border-radius:10px;text-decoration:none;transition:background .15s">
        <div class="avatar" style="background:var(--accent-soft)"><?= icon('globe') ?></div>
        <div class="flex-1 small">
          <b><?= e($ap['name']) ?></b>
          <p class="tiny faint"><?= e($ap['email']) ?> · <?= (int)$ap['schools'] ?> schools · <?= (int)$ap['directors'] ?> directors · <?= (int)$ap['students'] ?> students</p>
        </div>
        <?php if ($ap['over']): ?>
          <span class="badge badge-danger" title="Exceeds the 15-school capacity — consider reassigning schools"><?= icon('alert') ?> OVER CAP</span>
        <?php else: ?>
          <span class="badge badge-success"><?= icon('check-circle') ?> BALANCED</span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
    <?php if (!$adminPerf): ?><p class="muted small">No regional admins yet.</p><?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const g = (id, fn) => { const el = document.getElementById(id); if (el && window.EdunexChart) fn(el); };
  g('growth-chart', el => EdunexChart.multi(el, {
    labels: <?= json_encode($months) ?>,
    series: [
      { name: 'Students', values: <?= json_encode($studentsGrowth) ?> },
      { name: 'Teachers', values: <?= json_encode($teachersGrowth) ?> },
    ]
  }));
  g('school-chart', el => EdunexChart.line(el, { labels: <?= json_encode($months) ?>, values: <?= json_encode($schoolsGrowth) ?> }, 'var(--chart-2)'));
  g('login-chart', el => EdunexChart.line(el, { labels: <?= json_encode($days) ?>, values: <?= json_encode($dailyLogins) ?> }, 'var(--chart-3)'));
  g('attendance-chart', el => EdunexChart.line(el, { labels: <?= json_encode($days) ?>, values: <?= json_encode($attendanceTrend) ?> }, 'var(--chart-4)'));
  g('role-chart', el => EdunexChart.donut(el, { labels: <?= json_encode(array_column($roleDist, 'role')) ?>, values: <?= json_encode(array_column($roleDist, 'n')) ?> }));
  g('grade-chart', el => EdunexChart.line(el, { labels: <?= json_encode($gradeLabels) ?>, values: <?= json_encode($gradeDist) ?> }, 'var(--chart-5)'));
  g('reg-chart', el => EdunexChart.line(el, { labels: <?= json_encode($months) ?>, values: <?= json_encode($regs) ?> }, 'var(--chart-1)'));
  g('comp-chart', el => EdunexChart.line(el, { labels: <?= json_encode($compLabels) ?>, values: <?= json_encode($compRates) ?> }, 'var(--chart-6)'));
  g('transfer-chart', el => EdunexChart.donut(el, { labels: <?= json_encode($tLabels) ?>, values: <?= json_encode($tDist) ?> }));
  g('activity-chart', el => EdunexChart.line(el, { labels: <?= json_encode($hours) ?>, values: <?= json_encode($hourAct) ?> }, 'var(--chart-3)'));
});
</script>
