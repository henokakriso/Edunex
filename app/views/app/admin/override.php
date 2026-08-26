<?php /* Sysadmin: emergency override panel */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('shield') ?> Emergency Override</h1>
    <p class="sub">Unlock accounts, reset passwords, revoke sessions, impersonate users</p>
  </div>
  <form method="get" class="flex gap-6" action="<?= e(url('admin/override')) ?>">
    <input class="input" name="q" value="<?= e($q) ?>" placeholder="Search name or email…" style="min-width:240px">
    <button class="btn btn-primary"><?= icon('search') ?> Search</button>
  </form>
</div>

<div class="card pad-0">
  <?php $oSortUrl = fn($col) => url('admin/override?' . http_build_query(array_filter(['q'=>$q,'sort'=>$col, 'dir'=> $sort===$col && $dir==='asc' ? 'desc' : 'asc'], fn($x)=>$x!==''))); ?>
  <table class="table">
    <thead><tr>
      <th><a class="ajax-nav sort-link" href="<?= e($oSortUrl('user')) ?>">User<span class="sort-arrow<?= $sort==='user' ? ' active' : '' ?>"><?= $sort==='user' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($oSortUrl('role')) ?>">Role<span class="sort-arrow<?= $sort==='role' ? ' active' : '' ?>"><?= $sort==='role' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($oSortUrl('school')) ?>">School<span class="sort-arrow<?= $sort==='school' ? ' active' : '' ?>"><?= $sort==='school' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($oSortUrl('status')) ?>">Status<span class="sort-arrow<?= $sort==='status' ? ' active' : '' ?>"><?= $sort==='status' && $dir==='desc' ? '&#9660;' : '&#9650;' ?></span></a></th>
      <th style="width:430px">Emergency actions</th>
    </tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b><div class="tiny faint"><?= e($r['email']) ?></div></td>
          <td><span class="badge badge-info"><?= e($r['role']) ?></span></td>
          <td class="small"><?= e($r['school_name'] ?: '—') ?></td>
          <td><span class="badge <?= $r['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= e($r['status']) ?></span></td>
          <td>
            <div class="flex gap-6" style="flex-wrap:wrap">
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-xs btn-ghost" name="unlock" value="1"><?= icon('unlock') ?> Unlock</button>
              </form>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="password" value="">
                <button class="btn btn-xs btn-ghost" name="reset_password" value="1" onclick="var p=prompt('New password (leave empty for random):');this.form.password.value=p||''"><?= icon('key') ?> Reset pass</button>
              </form>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-xs btn-ghost" name="revoke_sessions" value="1" onclick="return confirm('Revoke ALL sessions for this user?')"><?= icon('close') ?> Revoke sessions</button>
              </form>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-xs btn-danger" name="impersonate" value="1" onclick="return confirm('Impersonate this user? This is logged.')"><?= icon('user') ?> Impersonate</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="muted">No users found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
