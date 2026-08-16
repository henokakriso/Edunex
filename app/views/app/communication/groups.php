<?php /* Groups view */
$roleCls = ['admin' => 'badge-red', 'director' => 'badge accent', 'teacher' => 'badge-info', 'student' => 'badge-success', 'parent' => 'badge-muted'];
$roleCounts = array_count_values(array_column($candidates, 'role'));
?>
<div class="page-head page-head-flex">
  <div>
    <h1><?= icon('users') ?> Groups</h1>
    <p class="sub">Create group conversations using role-based filters & select-all</p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('messages')) ?>">← Messages</a>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:22px;align-items:start">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('plus') ?> Create group</h3>
    <form method="post">
      <?= csrf_field() ?>
      <label class="small faint">Group name</label>
      <input class="input" name="title" required placeholder="All teachers — weekly briefing">

      <div class="d-flex gap-8" style="margin-top:12px;align-items:center;justify-content:space-between">
        <label class="small faint">Members</label>
        <div style="display:flex;gap:6px;flex-wrap:wrap" class="role-filters">
          <button type="button" class="btn btn-xs btn-ghost active" data-filter="all">All</button>
          <?php $shownRoles = []; foreach ($candidates as $c): if (in_array($c['role'], $shownRoles, true)) continue; $shownRoles[] = $c['role']; ?>
            <button type="button" class="btn btn-xs btn-ghost" data-filter="<?= e($c['role']) ?>"><?= e(ucfirst($c['role'])) ?> (<?= (int)($roleCounts[$c['role']] ?? 0) ?>)</button>
          <?php endforeach; ?>
        </div>
      </div>

<div style="margin-top:10px;display:flex;gap:16px;flex-wrap:wrap;align-items:center">
        <label class="muted small" style="user-select:none">
          <input type="checkbox" class="select-all-cb" data-role="*"> <?= icon('check') ?> Select all visible
        </label>
        <?php foreach (['student' => 'Students', 'teacher' => 'Teachers', 'director' => 'Directors', 'admin' => 'Admins', 'parent' => 'Parents'] as $roleKey => $roleLabel): ?>
          <label class="muted small" style="user-select:none">
            <input type="checkbox" class="select-role-cb" data-role="<?= $roleKey ?>"> <?= e($roleLabel) ?>
          </label>
        <?php endforeach; ?>
      </div>

      <div class="member-pick" style="max-height:320px;overflow-y:auto;border:1px solid var(--border);border-radius:10px;padding:8px;margin-top:8px">
        <?php foreach ($candidates as $cand): ?>
          <label class="flex gap-8 member-row" data-role="<?= e($cand['role']) ?>" style="padding:6px 8px;border-radius:8px;cursor:pointer">
            <input type="checkbox" class="member-cb" name="members[]" value="<?= (int)$cand['id'] ?>">
            <span class="small"><?= e($cand['name']) ?></span>
            <span class="<?= $roleCls[$cand['role']] ?? 'badge-muted' ?>" style="margin-left:auto"><?= e(ucfirst($cand['role'])) ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <div class="d-flex gap-8" style="margin-top:14px;align-items:center">
        <button class="btn btn-primary" name="create_group" value="1"><?= icon('users') ?> Create group</button>
        <span class="small faint" id="member-count">0 selected</span>
      </div>
    </form>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('users') ?> My groups</h3>
    <?php foreach ($myGroups as $g): ?>
      <a class="list-row" href="<?= e(url('messages&conv=' . $g['id'])) ?>" style="text-decoration:none">
        <div class="avatar" style="background:var(--accent-soft)"><?= icon('users') ?></div>
        <div class="flex-1"><b class="small"><?= e($g['title']) ?></b><p class="tiny faint"><?= (int)$g['members'] ?> members · created <?= e(time_ago($g['created_at'])) ?></p></div>
        <span class="small accent">Open →</span>
      </a>
    <?php endforeach; ?>
    <?php if (!$myGroups): ?><p class="muted small">You are not in any group yet.</p><?php endif; ?>
  </div>
</div>

<script>
(function () {
  var filters = document.querySelectorAll('.role-filters [data-filter]');
  var search = document.createElement('input');
  search.type = 'text';
  search.placeholder = 'Search members…';
  search.className = 'input';
  search.style.cssText = 'margin-top:10px;font-size:13px';
  document.querySelector('.member-pick').before(search);

  function getRows() { return document.querySelectorAll('.member-row'); }
  function applyFilter() {
    var active = document.querySelector('.role-filters button.active');
    var f = active ? active.getAttribute('data-filter') : 'all';
    getRows().forEach(function (r) {
      var q = search.value.trim().toLowerCase();
      var okRole = f === 'all' || r.getAttribute('data-role') === f;
      var okName = !q || r.textContent.toLowerCase().indexOf(q) !== -1;
      r.style.display = (okRole && okName) ? '' : 'none';
    });
    updateCount();
  }
  filters.forEach(function (b) {
    b.addEventListener('click', function () {
      filters.forEach(function (x) { x.classList.remove('active'); });
      b.classList.add('active');
      applyFilter();
    });
  });
  search.addEventListener('input', applyFilter);

  document.querySelectorAll('.select-all-cb, .select-role-cb').forEach(function (c) {
    c.addEventListener('change', function () {
      var trigger = c.getAttribute('data-role');
      var on = c.checked;
      getRows().forEach(function (r) {
        if (trigger === '*' || r.getAttribute('data-role') === trigger) {
          var cb = r.querySelector('.member-cb');
          if (trigger !== '*') cb.checked = on; // role box directly toggles
        }
      });
      if (trigger === '*') {
        // select all *visible* rows
        getRows().forEach(function (r) {
          if (getComputedStyle(r).display !== 'none') r.querySelector('.member-cb').checked = on;
        });
      }
      updateCount();
    });
  });

  function updateCount() {
    var n = document.querySelectorAll('.member-cb:checked').length;
    var el = document.getElementById('selected');
    if (el) el.textContent = n + ' selected';
    ['student','teacher','director','admin','parent'].forEach(function (r) {
      var rows = document.querySelectorAll('.member-row[data-role="' + r + '"]');
      var on = [].filter.call(rows, function (r) { return r.querySelector('.member-cb').checked; }).length;
      // keep global "select all" boxes consistent
    });
  }
  document.querySelectorAll('.member-cb').forEach(function (cb) { cb.addEventListener('change', updateCount); });
  updateCount();
})();
</script>