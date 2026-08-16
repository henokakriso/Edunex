<?php /* School profile view */
$src = $school['logo'] ? url('file?p=' . $school['logo']) : '';
?>
<div class="page-head">
  <div>
    <a class="small faint" href="<?= e(url('admin/schools')) ?>">← All schools</a>
    <h1 style="margin-top:4px"><?= $src ? '<img src="' . e($src) . '" alt="" style="height:36px;vertical-align:middle;margin-right:8px;border-radius:8px">' : icon('school') . ' ' ?><?= e($school['name']) ?> <span class="badge <?= $school['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= e($school['status']) ?></span></h1>
    <p class="sub"><?= e($school['code']) ?><?= $school['city'] ? ' · ' . e($school['city']) : '' ?><?= $school['type'] ? ' · ' . e($school['type']) : '' ?></p>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px">
  <?php
    $cards = [
      ['Teachers', $stats['teachers'], icon('user') . '‍' . icon('school'), 'admin/users&role=teacher'], ['Students', $stats['students'], icon('user') . '‍' . icon('graduation'), 'admin/users&role=student'],
      ['Parents', $stats['parents'], icon('users'), 'admin/users&role=parent'], ['Courses', $stats['courses'], icon('graduation'), 'admin/courses'],
      ['Exams', $stats['exams'], icon('note'), 'admin/courses'], ['Enrollments', $stats['enrollments'], icon('download'), 'admin/courses'],
      ['Attendance records', $stats['attendance'], icon('doc'), 'admin/reports'],
    ];
    foreach ($cards as [$l, $v, $i, $ln]): ?>
    <a class="card stat-card" href="<?= e(url($ln)) ?>"><div class="stat-icon"><?= $i ?></div><div><div class="stat-value"><?= (int)$v ?></div><div class="small faint"><?= $l ?></div></div></a>
  <?php endforeach; ?>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;margin-bottom:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0">ℹ School information</h3>
    <div class="grid2 small" style="gap:8px 16px">
      <div class="flex-col"><span class="faint">Code</span><b><?= e($school['code']) ?></b></div>
      <div class="flex-col"><span class="faint">Type</span><b><?= e($school['type']) ?></b></div>
      <div class="flex-col"><span class="faint">Address</span><b><?= e($school['address'] ?: '—') ?></b></div>
      <div class="flex-col"><span class="faint">City</span><b><?= e($school['city'] ?: '—') ?></b></div>
      <div class="flex-col"><span class="faint">Phone</span><b><?= e($school['phone'] ?: '—') ?></b></div>
      <div class="flex-col"><span class="faint">Email</span><b><?= e($school['email'] ?: '—') ?></b></div>
      <div class="flex-col"><span class="faint">Created</span><b><?= e(date('M j, Y', strtotime($school['created_at']))) ?></b></div>
    </div>
    <details class="card" style="margin-top:14px;padding:12px">
      <summary class="small"><?= icon('gear') ?> Edit school</summary>
      <form method="post" enctype="multipart/form-data" class="grid2" style="margin-top:10px">
        <?= csrf_field() ?>
        <div class="flex-col"><label class="small faint">Name *</label><input class="input" name="name" value="<?= e($school['name']) ?>" required></div>
        <div class="flex-col"><label class="small faint">Type</label>
          <select class="input" name="type"><?php foreach (['school' => 'School', 'university' => 'University', 'college' => 'College', 'training' => 'Training', 'other' => 'Other'] as $k => $v): ?><option value="<?= $k ?>" <?= $school['type'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        <div class="flex-col"><label class="small faint">Education level</label>
          <select class="input" name="education_level">
            <?php foreach (['kg' => 'Kindergarten', 'primary' => 'Primary (Gr 1–8)', 'secondary' => 'Secondary / Preparatory (Gr 9–12)', 'university' => 'University', 'college' => 'College', 'training' => 'TVET / Training', 'other' => 'Other'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= ($school['education_level'] ?? 'secondary') === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="flex-col"><label class="small faint">Address</label><input class="input" name="address" value="<?= e($school['address']) ?>"></div>
        <div class="flex-col"><label class="small faint">City</label><input class="input" name="city" value="<?= e($school['city']) ?>"></div>
        <div class="flex-col"><label class="small faint">Phone</label><input class="input" name="phone" value="<?= e($school['phone']) ?>"></div>
        <div class="flex-col"><label class="small faint">Email</label><input class="input" name="email" value="<?= e($school['email']) ?>"></div>
        <div class="flex-col"><label class="small faint">Status</label>
          <select class="input" name="status"><option value="active" <?= $school['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="suspended" <?= $school['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option></select></div>
        <div class="flex-col"><label class="small faint">Logo</label><input type="file" name="logo" accept="image/*" class="input"></div>
        <div class="flex-col" style="justify-content:flex-end"><button class="btn btn-primary"><?= icon('save') ?> Save</button></div>
      </form>
    </details>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('target') ?> Leadership</h3>
    <div class="flex-col gap-4">
      <?php foreach ($directors as $d): ?>
        <a class="list-row" href="<?= e(url('admin/user&id=' . $d['id'])) ?>" style="text-decoration:none">
          <div class="avatar" style="background:var(--chart-4-soft, var(--accent-soft))"><?= icon('target') ?></div>
          <div class="flex-1"><b class="small"><?= e($d['name']) ?></b><p class="tiny faint">Director · <?= e($d['email']) ?></p></div>
          <span class="badge <?= $d['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($d['status']) ?></span>
        </a>
      <?php endforeach; ?>
      <?php if (!$directors): ?><p class="muted small">No director assigned yet.</p><?php endif; ?>
    </div>
    <h3 class="card-title" style="margin-top:16px"><?= icon('trend-down') ?> Performance charts</h3>
    <p class="small faint">Grade distribution</p>
    <div id="school-grade-chart" ></div></canvas>
    <p class="small faint" style="margin-top:10px">Daily logins — 14 days</p>
    <div id="school-login-chart" ></div></canvas>
    <p class="small faint" style="margin-top:10px">Attendance — 14 days</p>
    <div id="school-att-chart" ></div></canvas>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;margin-bottom:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('user') ?>‍<?= icon('school') ?> Teachers</h3>
    <?php foreach ($teachers as $t): ?>
      <a class="list-row" href="<?= e(url('admin/user&id=' . $t['id'])) ?>" style="text-decoration:none;padding:7px 0">
        <div class="avatar"><?= e(mb_substr($t['name'], 0, 1)) ?></div>
        <div class="flex-1 small"><b><?= e($t['name']) ?></b><p class="tiny faint"><?= e($t['email']) ?></p></div>
        <span class="badge <?= $t['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($t['status']) ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$teachers): ?><p class="muted small">No teachers.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('user') ?>‍<?= icon('graduation') ?> Students (recent)</h3>
    <?php foreach ($students as $st): ?>
      <a class="list-row" href="<?= e(url('admin/user&id=' . $st['id'])) ?>" style="text-decoration:none;padding:7px 0">
        <div class="avatar"><?= e(mb_substr($st['name'], 0, 1)) ?></div>
        <div class="flex-1 small"><b><?= e($st['name']) ?></b><p class="tiny faint"><?= e($st['student_id'] ?? '—') ?></p></div>
        <span class="badge <?= ($st['enrollment_status'] ?? 'active') === 'inactive' ? 'badge-warning' : 'badge-success' ?>"><?= e($st['enrollment_status'] ?? 'active') ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$students): ?><p class="muted small">No students.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('folder') ?> Departments</h3>
    <?php foreach ($depts as $dp): ?>
      <a class="list-row" href="<?= e(url('admin/departments')) ?>" style="text-decoration:none;padding:7px 0">
        <div class="avatar" style="background:var(--accent-soft)"><?= icon('folder') ?></div>
        <div class="flex-1 small"><b><?= e($dp['name']) ?></b><p class="tiny faint">Head: <?= e($dp['head'] ?: '—') ?> · <?= (int)$dp['members'] ?> members</p></div>
      </a>
    <?php endforeach; ?>
    <?php if (!$depts): ?><p class="muted small">No departments.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('books') ?> Subjects</h3>
    <div class="flex gap-6" style="flex-wrap:wrap">
      <?php foreach ($subjects as $sb): ?><span class="badge badge-muted"><?= e($sb['name']) ?></span><?php endforeach; ?>
      <?php if (!$subjects): ?><p class="muted small">No subjects.</p><?php endif; ?>
    </div>
    <h3 class="card-title" style="margin-top:16px"><?= icon('megaphone') ?> Recent announcements</h3>
    <?php foreach ($announcements as $an): ?>
      <div class="list-row" style="padding:6px 0"><div class="flex-1 small"><?= e($an['title']) ?></div><div class="tiny faint"><?= e(time_ago($an['created_at'])) ?></div></div>
    <?php endforeach; ?>
    <?php if (!$announcements): ?><p class="muted small">None.</p><?php endif; ?>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;margin-bottom:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('graduation') ?> Courses</h3>
    <?php foreach ($courses as $c): ?>
      <a class="list-row" href="<?= e(url('courses/view&id=' . $c['id'])) ?>" style="text-decoration:none;padding:7px 0">
        <div class="flex-1 small"><b><?= e($c['title']) ?></b><p class="tiny faint"><?= e($c['tfirst'] . ' ' . $c['tlast']) ?> · <?= (int)$c['students'] ?> students · <?= (int)$c['lessons'] ?> lessons</p></div>
        <span class="badge <?= $c['status'] === 'published' ? 'badge-success' : ($c['status'] === 'archived' ? 'badge-warning' : 'badge-muted') ?>"><?= e($c['status']) ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$courses): ?><p class="muted small">No courses.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('refresh') ?> Transfer history</h3>
    <?php foreach ($transfers as $tr): ?>
      <div class="list-row" style="padding:7px 0">
        <div class="flex-1 small">
          <b><?= e($tr['sfirst'] . ' ' . $tr['slast']) ?></b>
          <p class="tiny faint"><?= e($tr['from_school']) ?> → <?= e($tr['to_school']) ?> · <?= e(time_ago($tr['created_at'])) ?></p>
        </div>
        <span class="badge <?= $tr['status'] === 'completed' ? 'badge-success' : ($tr['status'] === 'pending' ? 'badge-warning' : 'badge-muted') ?>"><?= e($tr['status']) ?></span>
      </div>
    <?php endforeach; ?>
    <?php if (!$transfers): ?><p class="muted small">No transfers.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('note') ?> Audit logs</h3>
    <?php foreach ($logs as $lg): ?>
      <div class="list-row" style="padding:6px 0">
        <div class="flex-1 small"><?= e($lg['user_name'] ?: 'System') ?> — <?= e($lg['detail']) ?></div>
        <div class="tiny faint"><?= e(time_ago($lg['created_at'])) ?></div>
      </div>
    <?php endforeach; ?>
    <?php if (!$logs): ?><p class="muted small">No activity yet.</p><?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const g = (id, fn) => { const el = document.getElementById(id); if (el && window.EdunexChart) fn(el); };
  g('school-grade-chart', el => EdunexChart.line(el, { labels: <?= json_encode($gradeLabels) ?>, values: <?= json_encode($grades) ?> }, 'var(--chart-5)'));
  g('school-login-chart', el => EdunexChart.line(el, { labels: <?= json_encode(array_column($loginSeries, 'date')) ?>, values: <?= json_encode(array_column($loginSeries, 'n')) ?> }, 'var(--chart-1)'));
  g('school-att-chart', el => EdunexChart.line(el, { labels: <?= json_encode(array_column($attSeries, 'date')) ?>, values: <?= json_encode(array_column($attSeries, 'n')) ?> }, 'var(--chart-2)'));
});
</script>
