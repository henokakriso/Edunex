<?php /* Admin modules — module registry & installer */
?>
<div class="page-head">
  <div>
    <h1><?= icon('box') ?> Modules</h1>
    <p class="sub">Modular installation — <?= (int)$counts['all'] ?> registered · <b><?= (int)$counts['on'] ?></b> enabled · <?= (int)$counts['off'] ?> disabled</p>
  </div>
  <div class="flex gap-6">
    <a class="btn <?= ($_GET['view'] ?? '') === 'levels' ? 'btn-ghost' : 'btn-primary' ?>" href="<?= e(url('admin/modules')) ?>">Registry</a>
    <a class="btn <?= ($_GET['view'] ?? '') === 'levels' ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('admin/modules&view=levels')) ?>"><?= icon('graduation') ?> By education level</a>
  </div>
</div>

<div class="flex gap-8" style="margin-bottom:16px;flex-wrap:wrap;align-items:center">
  <form method="get" class="flex gap-6">
    <input type="hidden" name="r" value="admin/modules">
    <input class="input" name="q" value="<?= e($q) ?>" placeholder="Search modules…">
    <select class="input" name="cat">
      <option value="">All categories</option>
      <?php foreach (['core' => 'Core', 'education' => 'Education', 'portal' => 'Portal', 'service' => 'Service'] as $k => $v): ?>
        <option value="<?= $k ?>" <?= $cat === $k ? 'selected' : '' ?>><?= $v ?></option>
      <?php endforeach; ?>
    </select>
    <select class="input" name="only">
      <option value="">All status</option>
      <option value="on" <?= $only === 'on' ? 'selected' : '' ?>>Enabled</option>
      <option value="off" <?= $only === 'off' ? 'selected' : '' ?>>Disabled</option>
    </select>
    <button class="btn"><?= icon('search') ?> Filter</button>
  </form>
</div>

<div class="table-wrap">
  <?php $mSortUrl = fn($col) => url('admin/modules?' . http_build_query(array_filter(['q'=>$q,'cat'=>$cat,'only'=>$only,'sort'=>$col, 'dir'=> $sort===$col && $dir==='asc' ? 'desc' : 'asc'], fn($x)=>$x!==''))); ?>
  <table class="table">
    <thead><tr>
      <th><a class="ajax-nav sort-link" href="<?= e($mSortUrl('name')) ?>">Module<?php if($sort==='name'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($mSortUrl('category')) ?>">Category<?php if($sort==='category'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($mSortUrl('level')) ?>">Level<?php if($sort==='level'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th><a class="ajax-nav sort-link" href="<?= e($mSortUrl('installed_at')) ?>">Installed<?php if($sort==='installed_at'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th style="width:140px"><a class="ajax-nav sort-link" href="<?= e($mSortUrl('enabled')) ?>">Status<?php if($sort==='enabled'): ?><span class="sort-arrow"><?= $dir==='asc'?'&#9650;':'&#9660;' ?></span><?php endif; ?></a></th>
      <th style="width:110px">Action</th>
    </tr></thead>
    <tbody>
      <?php foreach ($modules as $m): ?>
        <tr>
          <td>
            <b><?= $m['is_core'] ? icon('shield-check') . ' ' : '' ?><?= e($m['name']) ?></b>
            <p class="tiny faint"><?= e($m['module_key']) ?><?= $m['is_core'] ? ' · core' : '' ?></p>
          </td>
          <td><span class="badge"><?= e($m['category']) ?></span></td>
          <td class="small"><?= e(ucfirst((string)$m['education_type'])) ?></td>
          <td class="small faint"><?= e(date('M j, Y', strtotime($m['installed_at']))) ?></td>
          <td><span class="badge <?= $m['enabled'] ? 'badge-success' : 'badge-warning' ?>"><?= $m['enabled'] ? 'ENABLED' : 'DISABLED' ?></span></td>
          <td>
            <?php if ($m['is_core']): ?>
              <span class="tiny faint">always on</span>
            <?php elseif ($m['enabled']): ?>
              <form method="post" class="inline">
                <?= csrf_field() ?><input type="hidden" name="module_key" value="<?= e($m['module_key']) ?>">
                <button class="btn btn-sm" name="toggle" value="0" onclick="return confirm('Disable <?= e($m['name']) ?>? Affected features hide for that level.')"><?= icon('pause') ?> Disable</button>
              </form>
            <?php else: ?>
              <form method="post" class="inline">
                <?= csrf_field() ?><input type="hidden" name="module_key" value="<?= e($m['module_key']) ?>">
                <button class="btn btn-sm btn-success" name="install" value="1"><?= icon('plus') ?> Install</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$modules): ?><tr><td colspan="6" class="muted">No modules match your filters.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
