<?php /* Admin schools view — dynamic region (AJAX-swappable) */
$typeIco = ['school' => icon('school'), 'university' => icon('graduation'), 'college' => icon('university'), 'training' => icon('wrench'), 'other' => icon('building')];
$mk = fn(string $k, string $v = '') => url('admin/schools?' . http_build_query(array_filter(array_merge(
    ['q' => $q, 'type' => $type, 'status' => $status], [$k => $v]
), fn($x) => $x !== '')));
?>
<div class="page-head flex-between" style="flex-wrap:wrap;gap:12px">
  <div>
    <h1><?= icon('school') ?> Schools</h1>
    <p class="sub"><?= number_format($total) ?> school<?= $total === 1 ? '' : 's' ?><?= $pages > 1 ? ' · page ' . $page . ' of ' . $pages : '' ?></p>
  </div>
  <button class="btn btn-primary" data-open-modal="new-school-modal">+ New school</button>
</div>

<!-- Stats -->
<div class="stat-grid" style="margin-bottom:18px">
  <a class="stat-box clickable ajax-nav" href="<?= e($mk('')) ?>"><span class="tiny faint">Total schools</span><b class="h2"><?= number_format($stats['total']) ?></b><span class="tiny faint"><?= number_format($stats['students']) ?> students platform-wide</span></a>
  <a class="stat-box clickable ajax-nav" href="<?= e($mk('status', 'active')) ?>"><span class="tiny faint">Active</span><b class="h2" style="color:var(--success)"><?= number_format($stats['active']) ?></b><span class="tiny faint">running schools</span></a>
  <a class="stat-box clickable ajax-nav" href="<?= e($mk('status', 'suspended')) ?>"><span class="tiny faint">Suspended</span><b class="h2" style="color:var(--danger)"><?= number_format($stats['suspended']) ?></b><span class="tiny faint">paused schools</span></a>
  <a class="stat-box clickable ajax-nav" href="<?= e($mk('type', 'university')) ?>"><span class="tiny faint">Universities</span><b class="h2" style="color:var(--accent-3)"><?= number_format((int)($typeCounts['university'] ?? 0)) ?></b><span class="tiny faint"><?= number_format((int)($typeCounts['school'] ?? 0)) ?> schools, <?= number_format((int)($typeCounts['college'] ?? 0)) ?> colleges</span></a>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:18px">
  <form method="get" class="ajax-nav flex gap-10" style="flex-wrap:wrap;align-items:end">
    <input type="hidden" name="r" value="admin/schools">
    <div class="flex-col flex-1" style="min-width:200px"><label class="small faint">Search</label><input class="input" name="q" value="<?= e($q) ?>" placeholder="Name, code or city" style="min-width:220px"></div>
    <div class="flex-col"><label class="small faint">Status</label>
      <select class="input" name="status" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
      </select>
    </div>
    <button class="btn"><?= icon('search') ?> Search</button>
    <?php if ($q !== '' || $type !== '' || $status !== ''): ?><a class="ajax-nav btn btn-ghost" href="<?= e(url('admin/schools')) ?>">✕ Reset</a><?php endif; ?>
  </form>
  <div class="chips" style="margin-top:14px">
    <a class="ajax-nav chip <?= $type === '' && $status === '' ? 'on' : '' ?>" href="<?= e($mk('type')) ?>">All · <?= number_format($stats['total']) ?></a>
    <?php foreach (['school', 'university', 'college', 'training'] as $t): ?>
      <a class="ajax-nav chip <?= $type === $t ? 'on' : '' ?>" href="<?= e($mk('type', $t)) ?>"><?= ($typeIco[$t] ?? icon('building')) ?> <?= ucfirst($t) ?> · <?= (int)($typeCounts[$t] ?? 0) ?></a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Schools list -->
<div class="card" style="padding:0;overflow:visible">
  <!-- Header -->
  <?php $sortUrl = fn($col, $curSort, $curDir) => url('admin/schools?' . http_build_query(array_filter(array_merge(['q'=>$q,'type'=>$type,'status'=>$status], ['sort'=>$col, 'dir'=> $curSort===$col && $curDir==='asc' ? 'desc' : 'asc']), fn($x)=>$x!==''))); ?>
  <div class="schools-list-head">
    <div class="sl-col sl-col-school"><a class="ajax-nav sort-link" href="<?= e($sortUrl('name',$sort,$dir)) ?>">School<span class="sort-arrow<?= $sort==='name' ? ' active' : '' ?>"><?= $sort==='name' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></div>
    <div class="sl-col sl-col-type"><a class="ajax-nav sort-link" href="<?= e($sortUrl('type',$sort,$dir)) ?>">Type<span class="sort-arrow<?= $sort==='type' ? ' active' : '' ?>"><?= $sort==='type' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></div>
    <div class="sl-col sl-col-city"><a class="ajax-nav sort-link" href="<?= e($sortUrl('city',$sort,$dir)) ?>">City<span class="sort-arrow<?= $sort==='city' ? ' active' : '' ?>"><?= $sort==='city' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></div>
    <div class="sl-col sl-col-users"><a class="ajax-nav sort-link" href="<?= e($sortUrl('users',$sort,$dir)) ?>">Users<span class="sort-arrow<?= $sort==='users' ? ' active' : '' ?>"><?= $sort==='users' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></div>
    <div class="sl-col sl-col-status"><a class="ajax-nav sort-link" href="<?= e($sortUrl('status',$sort,$dir)) ?>">Status<span class="sort-arrow<?= $sort==='status' ? ' active' : '' ?>"><?= $sort==='status' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></div>
    <div class="sl-col sl-col-actions">Actions</div>
  </div>

  <?php foreach ($schools as $s):
    $row = [
        'id' => (int)$s['id'], 'name' => $s['name'], 'code' => $s['code'], 'type' => $s['type'],
        'city' => $s['city'] ?? '', 'phone' => $s['phone'] ?? '', 'email' => $s['email'] ?? '',
        'status' => $s['status'], 'created' => date('M j, Y', strtotime($s['created_at'])),
        'total_users' => (int)$s['total_users'], 'students' => (int)$s['students'], 'teachers' => (int)$s['teachers'],
        'directors' => (int)$s['directors'], 'parents' => (int)$s['parents'],
    ];
  ?>
    <div class="school-list-row" data-drawer-url="<?= e(url('admin/school&id=' . $s['id'] . '&partial=1')) ?>">
      <div class="sl-col sl-col-school">
        <div class="flex gap-10" style="align-items:center">
          <div class="avatar school-avatar" style="width:34px;height:34px;font-size:.72rem;flex-shrink:0"><?= $typeIco[$s['type']] ?? icon('school') ?></div>
          <div class="min-0">
            <b class="small"><?= e($s['name']) ?></b>
            <p class="tiny faint ellipsis"><?= e($s['code']) ?> <?= $s['address'] ? '· ' . e($s['address']) : '' ?></p>
          </div>
        </div>
      </div>
      <div class="sl-col sl-col-type small"><?= e(ucfirst($s['type'])) ?></div>
      <div class="sl-col sl-col-city small"><?= e($s['city'] ?: '—') ?></div>
      <div class="sl-col sl-col-users small mono"><?= number_format((int)$s['total_users']) ?></div>
      <div class="sl-col sl-col-status"><span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= e($s['status']) ?></span></div>
      <div class="sl-col sl-col-actions">
        <div class="row-act" style="justify-content:flex-end">
          <a class="icon-btn" title="View profile" href="<?= e(url('admin/school&id=' . $s['id'])) ?>"><?= icon('eye') ?></a>
          <?php if ($s['status'] === 'active'): ?>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="update_school" value="<?= (int)$s['id'] ?>"><input type="hidden" name="name" value="<?= e($s['name']) ?>"><input type="hidden" name="type" value="<?= e($s['type']) ?>"><input type="hidden" name="education_level" value="<?= e($s['education_level'] ?: 'secondary') ?>"><input type="hidden" name="city" value="<?= e($s['city']) ?>"><input type="hidden" name="address" value="<?= e($s['address'] ?? '') ?>"><input type="hidden" name="phone" value="<?= e($s['phone'] ?? '') ?>"><input type="hidden" name="email" value="<?= e($s['email'] ?? '') ?>"><input type="hidden" name="zone_id" value="<?= (int)($s['zone_id'] ?? 0) ?>"><input type="hidden" name="woreda_id" value="<?= (int)($s['woreda_id'] ?? 0) ?>"><input type="hidden" name="status" value="suspended"><button class="icon-btn warn" title="Suspend" data-confirm="Suspend <?= e($s['name']) ?>?"><?= icon('pause') ?></button></form>
          <?php else: ?>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="update_school" value="<?= (int)$s['id'] ?>"><input type="hidden" name="name" value="<?= e($s['name']) ?>"><input type="hidden" name="type" value="<?= e($s['type']) ?>"><input type="hidden" name="education_level" value="<?= e($s['education_level'] ?: 'secondary') ?>"><input type="hidden" name="city" value="<?= e($s['city']) ?>"><input type="hidden" name="address" value="<?= e($s['address'] ?? '') ?>"><input type="hidden" name="phone" value="<?= e($s['phone'] ?? '') ?>"><input type="hidden" name="email" value="<?= e($s['email'] ?? '') ?>"><input type="hidden" name="zone_id" value="<?= (int)($s['zone_id'] ?? 0) ?>"><input type="hidden" name="woreda_id" value="<?= (int)($s['woreda_id'] ?? 0) ?>"><input type="hidden" name="status" value="active"><button class="icon-btn success" title="Activate" data-confirm="Activate <?= e($s['name']) ?>?"><?= icon('check') ?></button></form>
          <?php endif; ?>
          <form method="post" class="inline" data-confirm="Delete <?= e($s['name']) ?>? This cascades to its users and courses.">
            <?= csrf_field() ?><input type="hidden" name="delete_school" value="<?= (int)$s['id'] ?>"><button class="icon-btn danger" title="Delete school"><?= icon('trash') ?></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (!$schools): ?>
    <div class="empty" style="padding:34px"><span class="empty-ico"><?= icon('school') ?></span><b>No schools found</b><p class="tiny faint">Try a different search, type or status filter.</p></div>
  <?php endif; ?>
</div>

<?php if ($pages > 1): ?>
  <div class="pager">
    <?php if ($page > 1): ?><a class="ajax-nav pager-btn" href="<?= e($pager(1)) ?>">«</a><a class="ajax-nav pager-btn" href="<?= e($pager($page - 1)) ?>">‹</a><?php endif; ?>
    <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?>
      <a class="ajax-nav pager-btn <?= $p === $page ? 'on' : '' ?>" href="<?= e($pager($p)) ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($page < $pages): ?><a class="ajax-nav pager-btn" href="<?= e($pager($page + 1)) ?>">›</a><a class="ajax-nav pager-btn" href="<?= e($pager($pages)) ?>">»</a><?php endif; ?>
  </div>
<?php endif; ?>
