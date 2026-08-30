<?php
$f = fn(string $k) => e($_GET[$k] ?? '');
$tabUrl = fn(string $t) => url('admin/regions?' . http_build_query(array_filter(['tab' => $t, 'q' => $q, 'region_id' => $regionFilter, 'zone_id' => $zoneFilter], fn($x) => $x !== '' && $x !== '0')));
$mk = fn(string $k, string $v = '') => url('admin/regions?' . http_build_query(array_filter(['tab' => $tab, 'q' => $q, 'region_id' => $regionFilter, 'zone_id' => $zoneFilter, $k => $v], fn($x) => $x !== '' && $x !== '0')));
?>
<div class="page-head flex-between" style="flex-wrap:wrap;gap:12px;margin-bottom:22px">
  <div>
    <h1><?= icon('map') ?> Regions & Zones</h1>
    <p class="sub"><?= $stats['regions'] ?> regions · <?= $stats['zones'] ?> zones · <?= $stats['woredas'] ?> woredas</p>
  </div>
</div>

<!-- Stats -->
<div class="stat-grid" style="margin-bottom:22px;gap:14px">
  <a class="stat-box clickable ajax-nav" href="<?= e($tabUrl('regions')) ?>"><span class="tiny faint">Regions</span><b class="h2"><?= number_format($stats['regions']) ?></b><span class="tiny faint">administrative regions</span></a>
  <a class="stat-box clickable ajax-nav" href="<?= e($tabUrl('zones')) ?>"><span class="tiny faint">Zones</span><b class="h2" style="color:var(--accent)"><?= number_format($stats['zones']) ?></b><span class="tiny faint">zone level</span></a>
  <a class="stat-box clickable ajax-nav" href="<?= e($tabUrl('woredas')) ?>"><span class="tiny faint">Woredas</span><b class="h2" style="color:var(--warning)"><?= number_format($stats['woredas']) ?></b><span class="tiny faint">woreda level</span></a>
</div>

<!-- Tabs -->
<div class="flex gap-0" style="border-bottom:2px solid var(--border);margin-bottom:20px">
  <a class="ajax-nav tab <?= $tab === 'regions' ? 'on' : '' ?>" href="<?= e($tabUrl('regions')) ?>" style="padding:10px 20px;font-weight:600;border-bottom:2px solid <?= $tab === 'regions' ? 'var(--accent)' : 'transparent' ?>;margin-bottom:-2px"><?= icon('map') ?> Regions</a>
  <a class="ajax-nav tab <?= $tab === 'zones' ? 'on' : '' ?>" href="<?= e($tabUrl('zones')) ?>" style="padding:10px 20px;font-weight:600;border-bottom:2px solid <?= $tab === 'zones' ? 'var(--accent)' : 'transparent' ?>;margin-bottom:-2px"><?= icon('folder') ?> Zones</a>
  <a class="ajax-nav tab <?= $tab === 'woredas' ? 'on' : '' ?>" href="<?= e($tabUrl('woredas')) ?>" style="padding:10px 20px;font-weight:600;border-bottom:2px solid <?= $tab === 'woredas' ? 'var(--accent)' : 'transparent' ?>;margin-bottom:-2px"><?= icon('tag') ?> Woredas</a>
</div>

<?php if ($tab === 'regions'): ?>
<!-- Regions -->
<div class="flex-between" style="margin-bottom:16px">
  <form method="get" class="ajax-nav flex gap-10" style="flex-wrap:wrap;align-items:end">
    <input type="hidden" name="r" value="admin/regions">
    <input type="hidden" name="tab" value="regions">
    <div class="flex-col flex-1" style="min-width:200px"><label class="small faint">Search</label>
      <div class="input-icon-wrap" style="min-width:220px"><span class="input-ico"><?= icon('search') ?></span><input class="input has-ico" name="q" id="reg-reg-search" value="<?= e($q) ?>" placeholder="Region name" oninput="document.getElementById('reg-reg-clear').style.display=this.value?'flex':'none'"><button type="button" class="input-icon-btn" id="reg-reg-clear" style="display:<?= $q ? 'flex' : 'none' ?>" onclick="document.getElementById('reg-reg-search').value='';this.style.display='none';this.form.submit()"><?= icon('x') ?></button></div>
    </div>
  </form>
  <button class="btn btn-primary" data-open-modal="new-region-modal">+ New region</button>
</div>

<div class="list-wrap">
  <div class="list-head">
    <div class="col col-num">#</div>
    <div class="col" style="flex:2">Region</div>
    <div class="col" style="flex:1">Code</div>
    <div class="col" style="flex:1">Zones</div>
    <div class="col" style="flex:1">Woredas</div>
    <div class="col" style="flex:1">Coordinates</div>
    <div class="col" style="width:80px">Actions</div>
  </div>
  <?php foreach ($regions as $i => $r): ?>
  <div class="list-row">
    <div class="col col-num"><?= $i + 1 ?></div>
    <div class="col" style="flex:2"><strong><?= e($r['name']) ?></strong></div>
    <div class="col" style="flex:1"><span class="badge badge-muted"><?= e($r['code'] ?: '—') ?></span></div>
    <div class="col" style="flex:1"><?= $r['zone_count'] ?></div>
    <div class="col" style="flex:1"><?= $r['woreda_count'] ?></div>
    <div class="col" style="flex:1;font-size:12px;color:var(--muted)"><?= $r['lat'] ? $r['lat'] . ', ' . $r['lng'] : '—' ?></div>
    <div class="col" style="width:80px">
      <form method="post" style="display:inline" onsubmit="return confirm('Archive this region?')">
        <?= csrf_field() ?>
        <input type="hidden" name="delete_region" value="<?= $r['id'] ?>">
        <button class="btn btn-ghost btn-sm" title="Archive"><?= icon('trash') ?></button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($regions)): ?>
  <div class="list-row" style="justify-content:center;color:var(--muted)">No regions found.</div>
  <?php endif; ?>
</div>

<!-- New Region Modal -->
<div class="modal-backdrop" id="new-region-modal">
  <div class="modal" style="max-width:440px">
    <div class="modal-head">
      <h3>New Region</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="create_region" value="1">
        <div class="form-group"><label class="form-label">Region Name *</label><input class="input" name="name" required placeholder="e.g. Oromia"></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Code</label><input class="input" name="code" placeholder="e.g. ET04"></div>
          <div class="form-group"><label class="form-label">Latitude</label><input class="input" name="lat" type="number" step="0.001" placeholder="7.525"></div>
          <div class="form-group"><label class="form-label">Longitude</label><input class="input" name="lng" type="number" step="0.001" placeholder="40.766"></div>
        </div>
        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" type="submit">Create Region</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php elseif ($tab === 'zones'): ?>
<!-- Zones -->
<div class="flex-between" style="margin-bottom:16px">
  <form method="get" class="ajax-nav flex gap-10" style="flex-wrap:wrap;align-items:end">
    <input type="hidden" name="r" value="admin/regions">
    <input type="hidden" name="tab" value="zones">
    <div class="flex-col flex-1" style="min-width:200px"><label class="small faint">Search</label>
      <div class="input-icon-wrap" style="min-width:220px"><span class="input-ico"><?= icon('search') ?></span><input class="input has-ico" name="q" id="zone-search" value="<?= e($q) ?>" placeholder="Zone name" oninput="document.getElementById('zone-clear').style.display=this.value?'flex':'none'"><button type="button" class="input-icon-btn" id="zone-clear" style="display:<?= $q ? 'flex' : 'none' ?>" onclick="document.getElementById('zone-search').value='';this.style.display='none';this.form.submit()"><?= icon('x') ?></button></div>
    </div>
    <div class="flex-col"><label class="small faint">Region</label>
      <select class="input" name="region_id" onchange="this.form.submit()">
        <option value="">All regions</option>
        <?php foreach ($regions as $r): ?><option value="<?= $r['id'] ?>" <?= $regionFilter == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
  </form>
  <button class="btn btn-primary" data-open-modal="new-zone-modal">+ New zone</button>
</div>

<div class="list-wrap">
  <div class="list-head">
    <div class="col col-num">#</div>
    <div class="col" style="flex:2">Zone</div>
    <div class="col" style="flex:2">Region</div>
    <div class="col" style="flex:1">Woredas</div>
    <div class="col" style="width:80px">Actions</div>
  </div>
  <?php foreach ($zones as $i => $z): ?>
  <div class="list-row">
    <div class="col col-num"><?= $i + 1 ?></div>
    <div class="col" style="flex:2"><strong><?= e($z['name']) ?></strong></div>
    <div class="col" style="flex:2"><span class="badge badge-muted"><?= e($z['region_name']) ?></span></div>
    <div class="col" style="flex:1">
      <?php
      $zc = Database::scalar("SELECT COUNT(*) FROM woredas WHERE zone_id = ? AND status = 'active'", [(int)$z['id']], 0);
      echo $zc;
      ?>
    </div>
    <div class="col" style="width:80px">
      <form method="post" style="display:inline" onsubmit="return confirm('Archive this zone?')">
        <?= csrf_field() ?>
        <input type="hidden" name="delete_zone" value="<?= $z['id'] ?>">
        <button class="btn btn-ghost btn-sm" title="Archive"><?= icon('trash') ?></button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($zones)): ?>
  <div class="list-row" style="justify-content:center;color:var(--muted)">No zones found.</div>
  <?php endif; ?>
</div>

<!-- New Zone Modal -->
<div class="modal-backdrop" id="new-zone-modal">
  <div class="modal" style="max-width:440px">
    <div class="modal-head">
      <h3>New Zone</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="create_zone" value="1">
        <div class="form-group"><label class="form-label">Zone Name *</label><input class="input" name="name" required placeholder="e.g. Jimma"></div>
        <div class="form-group"><label class="form-label">Region *</label>
          <select class="input" name="region_id" required>
            <option value="">Select region</option>
            <?php foreach ($regions as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" type="submit">Create Zone</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php else: ?>
<!-- Woredas -->
<div class="flex-between" style="margin-bottom:16px">
  <form method="get" class="ajax-nav flex gap-10" style="flex-wrap:wrap;align-items:end">
    <input type="hidden" name="r" value="admin/regions">
    <input type="hidden" name="tab" value="woredas">
    <div class="flex-col flex-1" style="min-width:200px"><label class="small faint">Search</label>
      <div class="input-icon-wrap" style="min-width:220px"><span class="input-ico"><?= icon('search') ?></span><input class="input has-ico" name="q" id="wrd-search" value="<?= e($q) ?>" placeholder="Woreda name" oninput="document.getElementById('wrd-clear').style.display=this.value?'flex':'none'"><button type="button" class="input-icon-btn" id="wrd-clear" style="display:<?= $q ? 'flex' : 'none' ?>" onclick="document.getElementById('wrd-search').value='';this.style.display='none';this.form.submit()"><?= icon('x') ?></button></div>
    </div>
    <div class="flex-col"><label class="small faint">Region</label>
      <select class="input" name="region_id" onchange="this.form.submit()">
        <option value="">All regions</option>
        <?php foreach ($regions as $r): ?><option value="<?= $r['id'] ?>" <?= $regionFilter == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col"><label class="small faint">Zone</label>
      <select class="input" name="zone_id" onchange="this.form.submit()">
        <option value="">All zones</option>
        <?php foreach ($zones as $z): ?><option value="<?= $z['id'] ?>" <?= $zoneFilter == $z['id'] ? 'selected' : '' ?>><?= e($z['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
  </form>
  <button class="btn btn-primary" data-open-modal="new-woreda-modal">+ New woreda</button>
</div>

<div class="list-wrap">
  <div class="list-head">
    <div class="col col-num">#</div>
    <div class="col" style="flex:2">Woreda</div>
    <div class="col" style="flex:2">Zone</div>
    <div class="col" style="flex:2">Region</div>
    <div class="col" style="width:80px">Actions</div>
  </div>
  <?php foreach ($woredas as $i => $w): ?>
  <div class="list-row">
    <div class="col col-num"><?= $i + 1 ?></div>
    <div class="col" style="flex:2"><strong><?= e($w['name']) ?></strong></div>
    <div class="col" style="flex:2"><?= e($w['zone_name']) ?></div>
    <div class="col" style="flex:2"><span class="badge badge-muted"><?= e($w['region_name']) ?></span></div>
    <div class="col" style="width:80px">
      <form method="post" style="display:inline" onsubmit="return confirm('Archive this woreda?')">
        <?= csrf_field() ?>
        <input type="hidden" name="delete_woreda" value="<?= $w['id'] ?>">
        <button class="btn btn-ghost btn-sm" title="Archive"><?= icon('trash') ?></button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($woredas)): ?>
  <div class="list-row" style="justify-content:center;color:var(--muted)">No woredas found.</div>
  <?php endif; ?>
</div>

<!-- New Woreda Modal -->
<div class="modal-backdrop" id="new-woreda-modal">
  <div class="modal" style="max-width:440px">
    <div class="modal-head">
      <h3>New Woreda</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="create_woreda" value="1">
        <div class="form-group"><label class="form-label">Woreda Name *</label><input class="input" name="name" required placeholder="e.g. Arba Minch"></div>
        <div class="form-group"><label class="form-label">Zone *</label>
          <select class="input" name="zone_id" required>
            <option value="">Select zone</option>
            <?php foreach ($zones as $z): ?><option value="<?= $z['id'] ?>"><?= e($z['region_name'] . ' → ' . $z['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" type="submit">Create Woreda</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
