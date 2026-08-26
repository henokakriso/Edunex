<?php /* Admin users view — superadmin interactive list */
$roleCls = ['regional' => 'badge-danger', 'principal' => 'badge-accent', 'teacher' => 'badge-accent', 'student' => 'badge-success', 'parent' => 'badge-warning', 'guest' => 'badge-muted', 'ministry' => 'badge-danger', 'registrar' => 'badge-accent', 'dean' => 'badge-accent', 'vice_dean' => 'badge-accent', 'hod' => 'badge-accent', 'lecturer' => 'badge-accent', 'bursar' => 'badge-warning', 'student_affairs' => 'badge-warning', 'librarian' => 'badge-muted', 'zonal' => 'badge-danger', 'woreda' => 'badge-danger', 'it_admin' => 'badge-muted'];
$roleIco = ['regional' => icon('shield'), 'principal' => icon('graduation'), 'teacher' => icon('user') . '‍' . icon('school'), 'student' => icon('users-card'), 'parent' => icon('users'), 'guest' => icon('user'), 'ministry' => icon('shield'), 'registrar' => icon('note'), 'dean' => icon('graduation'), 'vice_dean' => icon('graduation'), 'hod' => icon('folder'), 'lecturer' => icon('book'), 'bursar' => icon('dollar'), 'student_affairs' => icon('users'), 'librarian' => icon('university'), 'zonal' => icon('shield'), 'woreda' => icon('shield'), 'it_admin' => icon('wrench')];
$statusCls = ['active' => 'badge-success', 'pending' => 'badge-warning', 'suspended' => 'badge-danger', 'banned' => 'badge-danger'];
$f = fn(string $k) => e($_GET[$k] ?? '');
$mk = fn(string $k, string $v = '') => url('admin/users?' . http_build_query(array_filter(array_merge(
    ['role' => $role, 'status' => $status, 'q' => $q], [$k => $v]
), fn($x) => $x !== '')));
?>
<div class="page-head flex-between" style="flex-wrap:wrap;gap:12px;margin-bottom:22px">
  <div>
    <h1><?= icon('users') ?> Users</h1>
    <p class="sub"><?= number_format($total) ?> user<?= $total === 1 ? '' : 's' ?> · <?= $pages > 1 ? 'page ' . $page . ' of ' . $pages : 'all on one page' ?></p>
  </div>
  <div class="flex gap-10">
    <a class="btn btn-ghost" href="<?= e(url('admin/import')) ?>"><?= icon('download') ?> Import</a>
    <button class="btn btn-primary" data-open-modal="new-user-modal">+ New user</button>
  </div>
</div>

<!-- Stats -->
<div class="stat-grid" style="margin-bottom:22px;gap:14px">
  <a class="stat-box clickable ajax-nav" href="<?= e($mk('')) ?>"><span class="tiny faint">Total users</span><b class="h2"><?= number_format($stats['total']) ?></b><span class="tiny faint"><?= $stats['new_month'] ?> new this month</span></a>
  <a class="stat-box clickable ajax-nav" href="<?= e($mk('status', 'active')) ?>"><span class="tiny faint">Active</span><b class="h2" style="color:var(--success)"><?= number_format($stats['active']) ?></b><span class="tiny faint">online & enrolled</span></a>
  <a class="stat-box clickable ajax-nav" href="<?= e($mk('status', 'suspended')) ?>"><span class="tiny faint">Suspended</span><b class="h2" style="color:var(--danger)"><?= number_format($stats['suspended']) ?></b><span class="tiny faint"><?= number_format($stats['banned']) ?> banned</span></a>
  <a class="stat-box clickable ajax-nav" href="<?= e($mk('status', 'pending')) ?>"><span class="tiny faint">Pending</span><b class="h2" style="color:var(--warning)"><?= number_format($stats['pending']) ?></b><span class="tiny faint">awaiting activation</span></a>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:18px;padding:16px">
  <form method="get" class="ajax-nav flex gap-10" style="flex-wrap:wrap;align-items:end">
    <input type="hidden" name="r" value="admin/users">
    <div class="flex-col flex-1" style="min-width:200px"><label class="small faint">Search</label>
      <div class="input-icon-wrap"><input class="input" name="q" value="<?= e($q) ?>" placeholder="Name, email or ID" style="min-width:220px;padding-right:36px"><button type="submit" class="input-icon-btn" title="Search"><?= icon('search') ?></button></div>
    </div>
    <div class="flex-col"><label class="small faint">Status</label>
      <select class="input" name="status" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <?php foreach (['active', 'pending', 'suspended', 'banned'] as $st): ?><option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option><?php endforeach; ?>
      </select>
    </div>
    <?php if ($q !== '' || $role !== '' || $status !== ''): ?><a class="ajax-nav btn btn-ghost" href="<?= e(url('admin/users')) ?>">✕ Reset</a><?php endif; ?>
  </form>
  <div class="chips" style="margin-top:14px;padding:4px 0">
    <a class="ajax-nav chip <?= $role === '' && $status === '' ? 'on' : '' ?>" href="<?= e($mk('role')) ?>">All · <?= number_format($stats['total']) ?></a>
    <?php
    $chipRoles = ['regional', 'principal', 'teacher', 'student', 'parent', 'registrar', 'dean', 'vice_dean', 'hod', 'lecturer', 'bursar', 'student_affairs', 'librarian', 'zonal', 'woreda', 'it_admin'];
    foreach ($chipRoles as $r):
      if (empty($roleCounts[$r])) continue;
    ?>
      <a class="ajax-nav chip <?= $role === $r ? 'on' : '' ?>" href="<?= e($mk('role', $r)) ?>"><?= $roleIco[$r] ?? '' ?> <?= ucfirst($r) ?> · <?= (int)($roleCounts[$r] ?? 0) ?></a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Bulk actions bar -->
<form method="post" id="bulk-form">
  <?= csrf_field() ?>
  <input type="hidden" name="ids" id="bulk-ids" value="">
  <div id="bulk-bar" class="bulk-bar">
    <span id="bulk-count" class="small">0 selected</span>
    <span class="spacer"></span>
    <button class="btn btn-sm btn-success" name="bulk_status" value="active">▶ Activate</button>
    <button class="btn btn-sm btn-warning" name="bulk_status" value="suspended"><?= icon('pause') ?> Suspend</button>
    <button class="btn btn-sm btn-danger" name="bulk_delete" value="1" data-confirm="Delete the selected users? This removes all their data."><?= icon('trash') ?> Delete</button>
  </div>
</form>

<!-- Users list -->
<div class="card" style="padding:0;overflow:visible">
  <!-- Table header -->
  <?php $sortUrl = fn($col, $curSort, $curDir) => url('admin/users?' . http_build_query(array_merge(array_filter(['role'=>$role,'status'=>$status,'q'=>$q,'page'=>$page], fn($x)=>$x!==''), ['sort'=>$col, 'dir'=> $curSort===$col && $curDir==='asc' ? 'desc' : 'asc']))); ?>
  <div class="users-list-head">
    <div class="ul-col ul-col-chk"><label class="chk"><input type="checkbox" id="chk-all"><span></span></label></div>
    <div class="ul-col ul-col-user"><a class="ajax-nav sort-link" href="<?= e($sortUrl('name',$sort,$dir)) ?>">User<?php if($sort==='name'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></div>
    <div class="ul-col ul-col-role"><a class="ajax-nav sort-link" href="<?= e($sortUrl('role',$sort,$dir)) ?>">Role<?php if($sort==='role'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></div>
    <div class="ul-col ul-col-number"><a class="ajax-nav sort-link" href="<?= e($sortUrl('number',$sort,$dir)) ?>">Number<?php if($sort==='number'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></div>
    <div class="ul-col ul-col-id"><a class="ajax-nav sort-link" href="<?= e($sortUrl('id',$sort,$dir)) ?>">ID<?php if($sort==='id'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></div>
    <div class="ul-col ul-col-school"><a class="ajax-nav sort-link" href="<?= e($sortUrl('school',$sort,$dir)) ?>">School<?php if($sort==='school'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></div>
    <div class="ul-col ul-col-status"><a class="ajax-nav sort-link" href="<?= e($sortUrl('status',$sort,$dir)) ?>">Status<?php if($sort==='status'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></div>
    <div class="ul-col ul-col-joined"><a class="ajax-nav sort-link" href="<?= e($sortUrl('created_at',$sort,$dir)) ?>">Joined<?php if($sort==='created_at'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></div>
    <div class="ul-col ul-col-actions">Actions</div>
  </div>

  <?php foreach ($users as $us):
    $protected = (int)$us['id'] === 1 || (int)$us['id'] === (int)$__u['id'];
    $initials = e(mb_substr($us['first_name'], 0, 1) . mb_substr($us['last_name'], 0, 1));
    $row = [
        'id' => (int)$us['id'], 'name' => $us['first_name'] . ' ' . $us['last_name'],
        'email' => $us['email'], 'phone' => $us['phone'] ?? '', 'role' => $us['role'],
        'student_id' => $us['student_id'] ?? '', 'school' => $us['school_name'] ?? '',
        'group' => $us['group_name'] ?? '', 'status' => $us['status'], 'level' => (int)($us['level'] ?? 0),
        'xp' => (int)($us['xp'] ?? 0), 'joined' => date('M j, Y', strtotime($us['created_at'])),
        'initials' => $initials, 'protected' => $protected,
    ];
  ?>
    <div class="user-list-row" data-user='<?= e(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'>
      <div class="ul-col ul-col-chk"><label class="chk"><input type="checkbox" class="row-chk" value="<?= (int)$us['id'] ?>" <?= $protected ? 'disabled' : '' ?>><span></span></label></div>
      <div class="ul-col ul-col-user">
        <div class="flex gap-10" style="align-items:center">
          <div class="avatar" style="width:34px;height:34px;font-size:.72rem;flex-shrink:0"><?= $initials ?></div>
          <div class="min-0">
            <b class="small"><?= e($us['first_name'] . ' ' . $us['last_name']) ?><?= $protected ? ' <span class="tiny faint">(you)</span>' : '' ?></b>
            <p class="tiny faint ellipsis"><?= e($us['email']) ?></p>
          </div>
        </div>
      </div>
      <div class="ul-col ul-col-role"><span class="badge <?= $roleCls[$us['role']] ?? 'badge-muted' ?>"><?= $roleIco[$us['role']] ?? '' ?> <?= e(ucfirst($us['role'])) ?></span></div>
      <div class="ul-col ul-col-number small mono ellipsis" title="<?= e($us['student_id'] ?? '') ?>"><?= e($us['student_id'] ?: '—') ?></div>
      <div class="ul-col ul-col-id small mono">#<?= str_pad((int)$us['id'], 6, '0', STR_PAD_LEFT) ?></div>
      <div class="ul-col ul-col-school small ellipsis"><?= e($us['school_name']) ?></div>
      <div class="ul-col ul-col-status"><span class="badge <?= $statusCls[$us['status']] ?? 'badge-muted' ?>"><?= e($us['status']) ?></span></div>
      <div class="ul-col ul-col-joined small faint"><?= e(date('M j, Y', strtotime($us['created_at']))) ?></div>
      <div class="ul-col ul-col-actions">
        <div class="row-act" style="justify-content:flex-end">
          <a class="icon-btn" title="View profile" href="<?= e(url('admin/user&id=' . $us['id'])) ?>"><?= icon('eye') ?></a>
          <?php if (!$protected): ?>
            <?php if ($us['status'] === 'active'): ?>
              <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="set_status" value="<?= (int)$us['id'] ?>"><input type="hidden" name="new_status" value="suspended"><button class="icon-btn warn" title="Suspend" data-confirm="Suspend <?= e($us['first_name'] . ' ' . $us['last_name']) ?>?"><?= icon('pause') ?></button></form>
            <?php else: ?>
              <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="set_status" value="<?= (int)$us['id'] ?>"><input type="hidden" name="new_status" value="active"><button class="icon-btn success" title="Activate" data-confirm="Activate <?= e($us['first_name'] . ' ' . $us['last_name']) ?>?"><?= icon('check') ?></button></form>
            <?php endif; ?>
            <form method="post" class="inline" data-confirm="Delete <?= e($us['first_name'] . ' ' . $us['last_name']) ?>? All their data is removed.">
              <?= csrf_field() ?><input type="hidden" name="delete_user" value="<?= (int)$us['id'] ?>"><button class="icon-btn danger" title="Delete user"><?= icon('trash') ?></button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (!$users): ?>
    <div class="empty" style="padding:34px"><span class="empty-ico"><?= icon('search') ?></span><b>No users found</b><p class="tiny faint">Try a different search, role or status filter.</p></div>
  <?php endif; ?>

  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php if ($page > 1): ?><a class="pager-btn" href="<?= e($pager(1)) ?>">«</a><a class="pager-btn" href="<?= e($pager($page - 1)) ?>">‹</a><?php endif; ?>
      <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?>
        <a class="ajax-nav pager-btn <?= $p === $page ? 'on' : '' ?>" href="<?= e($pager($p)) ?>"><?= $p ?></a>
      <?php endfor; ?>
      <?php if ($page < $pages): ?><a class="pager-btn" href="<?= e($pager($page + 1)) ?>">›</a><a class="pager-btn" href="<?= e($pager($pages)) ?>">»</a><?php endif; ?>
    </div>
  <?php endif; ?>
</div>

