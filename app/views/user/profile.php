<?php /* Public profile view */
$p = $profile;
$roleBadge = ['admin' => 'badge danger', 'director' => 'badge danger', 'teacher' => 'badge accent',
              'student' => 'badge warning', 'parent' => 'badge muted'][$p['role']] ?? 'badge muted';
$schoolType = $schoolType ?? [];
?>
<div class="page-head page-head-flex">
  <div>
    <h1><?= icon('user') ?> Profile</h1>
    <p class="sub">Who created this — <?= e(ucfirst($p['role'])) ?></p>
  </div>
  <div class="d-flex" style="gap:8px">
    <a class="btn btn-ghost" href="<?= e(url('messages&to=' . (int)$p['id'])) ?>"><?= icon('chat') ?> Message</a>
  </div>
</div>

<div class="grid" style="grid-template-columns:1.4fr 1fr;gap:18px;align-items:start">
  <div class="card" style="padding:22px">
    <div class="d-flex" style="gap:16px;align-items:center;flex-wrap:wrap">
      <img class="avatar" src="<?= e(avatar_url($p)) ?>" alt="avatar"
           style="width:72px;height:72px;border-radius:50%;object-fit:cover"
           onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
      <div class="avatar" style="width:72px;height:72px;font-size:30px;display:none"><?= e(initials($p)) ?></div>
      <div class="flex-1">
        <div class="d-flex" style="align-items:center;gap:10px;flex-wrap:wrap">
          <h3 style="margin:0"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></h3>
          <span class="<?= $roleBadge ?>"><?= e(ucfirst($p['role'])) ?></span>
        </div>
        <p class="muted small" style="margin-top:4px">
          <?= $p['role'] === 'student' && $p['student_id'] ? 'Student ID ' . e($p['student_id']) . ' · ' : '' ?>
          Member since <?= e(date('M Y', strtotime($p['created_at']))) ?>
          <?= $p['last_login'] ? ' · Last seen ' . e(date('M j', strtotime($p['last_login']))) : '' ?>
        </p>
        <?php if (($p['xp'] ?? 0) > 0 || ($p['level'] ?? 0) > 0): ?>
          <div class="d-flex" style="gap:10px;margin-top:6px;flex-wrap:wrap">
            <span class="badge accent">Level <?= (int)$p['level'] ?></span>
            <span class="badge warning"><?= (int)$p['xp'] ?> XP</span>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php if (!empty($p['bio'])): ?>
      <p class="muted" style="margin-top:16px"><?= e($p['bio']) ?></p>
    <?php endif; ?>
  </div>

  <div class="card" style="padding:18px">
    <h3 class="card-title" style="margin-top:0"><?= icon('building') ?> Where they teach</h3>
    <?php if ($school): ?>
      <div class="d-flex" style="gap:12px;align-items:center;flex-wrap:wrap">
        <div class="avatar" style="background:color-mix(in srgb, var(--accent) 12%, transparent);color:var(--accent)"><?= icon('school') ?></div>
        <div>
          <b style="font-size:15px"><?= e($school['name']) ?></b>
          <p class="tiny faint"><?= e($schoolType[$school['type']] ?? ucfirst($school['type'])) ?><?= $school['city'] ? ' · ' . e($school['city']) : '' ?></p>
          <?php if ($school['address']): ?><p class="tiny faint"><?= e($school['address']) ?></p><?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <p class="muted small">Not assigned to any school.</p>
    <?php endif; ?>
  </div>
</div>

<?php if ($courses): ?>
  <div class="card" style="margin-top:18px;padding:18px">
    <h3 class="card-title" style="margin-top:0">
      <?= icon($p['role'] === 'teacher' ? 'note' : 'book') ?>
      <?= $p['role'] === 'teacher' ? 'Courses they teach' : 'Courses they take' ?>
      <span class="badge accent" style="vertical-align:middle"><?= count($courses) ?></span>
    </h3>
    <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px">
      <?php foreach ($courses as $c): ?>
        <div class="list-row" style="padding:10px;border:1px solid var(--border);border-radius:10px">
          <div class="flex-1">
            <b class="small"><?= e($c['title']) ?></b>
            <p class="tiny faint"><?= $c['subject'] ? e($c['subject']) . ' · ' : '' ?><?= e($c['level']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
