<?php /* EDUNEX Module Registry — national-level module management */
$groupLabels = ['core' => 'Core', 'standard' => 'Standard', 'optional' => 'Optional', 'advanced' => 'Advanced'];
$groupColors = ['core' => '#6366f1', 'standard' => '#22c55e', 'optional' => '#f59e0b', 'advanced' => '#8b5cf6'];
$groupIcons = ['core' => 'shield-check', 'standard' => 'box', 'optional' => 'settings', 'advanced' => 'zap'];
$securityLabels = ['verified' => 'Edunex Verified', 'community' => 'Community', 'unverified' => 'Unverified'];
$securityIcons = ['verified' => 'shield-check', 'community' => 'users', 'unverified' => 'alert-triangle'];

if ($view === 'detail' && !empty($mod)): ?>
<?php
  $cfg = json_decode($mod['config_json'] ?? '{}', true) ?: [];
  $scopeMap = [];
  foreach ($scopes as $s) $scopeMap[$s['scope_type']][] = (int)$s['scope_id'];
?>
<div class="page-head">
  <div>
    <a href="<?= e(url('admin/modules')) ?>" class="tiny faint" style="text-decoration:none">&larr; Back to Module Registry</a>
    <h1><?= icon($mod['icon']) ?> <?= e($mod['name']) ?></h1>
    <p class="sub"><?= e($mod['module_key']) ?> · v<?= e($mod['version']) ?> · <?= e($mod['author']) ?></p>
  </div>
  <div class="flex gap-6">
    <?php if (!$mod['is_core']): ?>
      <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="module_key" value="<?= e($mod['module_key']) ?>">
        <?php if ($mod['enabled']): ?>
          <button class="btn btn-warning" name="toggle" value="0"><?= icon('pause') ?> Disable</button>
        <?php else: ?>
          <button class="btn btn-success" name="toggle" value="1"><?= icon('play') ?> Enable</button>
        <?php endif; ?>
      </form>
    <?php endif; ?>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
  <div>
    <!-- Status + Meta -->
    <div class="card" style="margin-bottom:16px">
      <div class="flex-between" style="margin-bottom:14px">
        <h3 style="margin:0">Module Information</h3>
        <span class="badge <?= $mod['enabled'] ? 'badge-success' : 'badge-warning' ?>" style="font-size:12px;padding:4px 12px"><?= $mod['enabled'] ? 'ENABLED' : 'DISABLED' ?></span>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
        <div><span class="tiny faint">Group</span><div><span class="badge" style="background:<?= $groupColors[$mod['mod_group']] ?>22;color:<?= $groupColors[$mod['mod_group']] ?>"><?= $groupLabels[$mod['mod_group']] ?></span></div></div>
        <div><span class="tiny faint">Version</span><div class="small"><b>v<?= e($mod['version']) ?></b></div></div>
        <div><span class="tiny faint">License</span><div class="small"><?= e($mod['license']) ?></div></div>
        <div><span class="tiny faint">Author</span><div class="small"><?= e($mod['author']) ?></div></div>
        <div><span class="tiny faint">Security</span><div class="small"><?= icon($securityIcons[$mod['security_status']]) ?> <?= $securityLabels[$mod['security_status']] ?></div></div>
        <div><span class="tiny faint">Scope</span><div class="small"><?= e(ucfirst($mod['scope_type'])) ?></div></div>
      </div>
      <?php if ($mod['description']): ?>
        <p class="small muted" style="margin-top:12px"><?= e($mod['description']) ?></p>
      <?php endif; ?>
    </div>

    <!-- Dependencies -->
    <div class="card" style="margin-bottom:16px">
      <h3 class="card-title" style="margin-top:0"><?= icon('git-branch') ?> Dependencies</h3>
      <?php if ($depModules): ?>
        <div class="flex gap-6" style="flex-wrap:wrap">
          <?php foreach ($depModules as $d): ?>
            <span class="badge <?= $d['enabled'] ? 'badge-success' : 'badge-danger' ?>" style="padding:4px 10px">
              <?= $d['enabled'] ? icon('check-circle') : icon('x-circle') ?> <?= e($d['name']) ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="small muted">No dependencies</p>
      <?php endif; ?>

      <?php if ($dependents): ?>
        <h4 style="margin:16px 0 8px" class="small faint">MODULES THAT DEPEND ON THIS</h4>
        <div class="flex gap-6" style="flex-wrap:wrap">
          <?php foreach ($dependents as $d): ?>
            <span class="badge <?= $d['enabled'] ? 'badge-success' : 'badge-warning' ?>" style="padding:4px 10px">
              <?= e($d['name']) ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Scope -->
    <div class="card" style="margin-bottom:16px">
      <h3 class="card-title" style="margin-top:0"><?= icon('globe') ?> Activation Scope</h3>
      <p class="small faint" style="margin-bottom:12px">Control where this module is available. Lower-level scopes inherit from higher levels.</p>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="module_key" value="<?= e($mod['module_key']) ?>">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:16px">
          <?php foreach ([
            'all' => ['National', 'Available everywhere'],
            'regional' => ['Regional', 'By region, zone, or woreda'],
            'selective' => ['School-level', 'Specific schools only'],
          ] as $sv => [$sl, $desc]): ?>
            <label style="display:flex;gap:10px;padding:12px;border:2px solid var(--border);border-radius:10px;cursor:pointer;transition:all .15s" class="scope-option <?= $mod['scope_type'] === $sv ? 'active' : '' ?>">
              <input type="radio" name="scope_type" value="<?= $sv ?>" <?= $mod['scope_type'] === $sv ? 'checked' : '' ?> onchange="toggleScopeType(this)" style="margin-top:2px">
              <div><div class="small" style="font-weight:600"><?= $sl ?></div><div class="tiny faint"><?= $desc ?></div></div>
            </label>
          <?php endforeach; ?>
        </div>

        <div id="scope-selectors" style="display:<?= $mod['scope_type'] !== 'all' ? 'block' : 'none' ?>">
          <!-- Region / Zone / Woreda -->
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
            <div>
              <div class="small" style="font-weight:600;margin-bottom:6px"><?= icon('map') ?> Regions</div>
              <div style="max-height:180px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;padding:8px">
                <?php foreach ($regions as $r): ?>
                  <label class="flex gap-4 small" style="padding:4px 0;cursor:pointer"><input type="checkbox" name="scopes[]" value="region:<?= $r['id'] ?>" <?= in_array((int)$r['id'], $scopeMap['region'] ?? []) ? 'checked' : '' ?>> <?= e($r['name']) ?></label>
                <?php endforeach; ?>
              </div>
            </div>
            <div>
              <div class="small" style="font-weight:600;margin-bottom:6px"><?= icon('map-pin') ?> Zones</div>
              <div style="max-height:180px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;padding:8px">
                <?php foreach ($zones as $z): ?>
                  <label class="flex gap-4 small" style="padding:4px 0;cursor:pointer"><input type="checkbox" name="scopes[]" value="zone:<?= $z['id'] ?>" <?= in_array((int)$z['id'], $scopeMap['zone'] ?? []) ? 'checked' : '' ?>> <?= e($z['name']) ?> <span class="faint">(<?= e($z['region_name']) ?>)</span></label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Woreda -->
          <div style="margin-bottom:14px">
            <div class="small" style="font-weight:600;margin-bottom:6px"><?= icon('map-pin') ?> Woredas</div>
            <div style="max-height:150px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;padding:8px;display:grid;grid-template-columns:1fr 1fr;gap:0 16px">
              <?php foreach ($woredas as $w): ?>
                <label class="flex gap-4 small" style="padding:4px 0;cursor:pointer"><input type="checkbox" name="scopes[]" value="woreda:<?= $w['id'] ?>" <?= in_array((int)$w['id'], $scopeMap['woreda'] ?? []) ? 'checked' : '' ?>> <?= e($w['name']) ?> <span class="faint">(<?= e($w['zone_name']) ?>)</span></label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Schools -->
          <div>
            <div class="flex-between" style="margin-bottom:6px">
              <div class="small" style="font-weight:600"><?= icon('school') ?> Schools</div>
              <input type="text" class="input" id="school-search" placeholder="Search schools…" oninput="filterSchools()" style="width:200px;padding:5px 10px;font-size:12px">
            </div>
            <div id="school-list" style="max-height:200px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;padding:8px;display:grid;grid-template-columns:1fr 1fr;gap:0 16px">
              <?php foreach ($schools as $s): ?>
                <label class="flex gap-4 small school-item" style="padding:4px 0;cursor:pointer" data-name="<?= strtolower(e($s['name'])) ?>"><input type="checkbox" name="scopes[]" value="school:<?= $s['id'] ?>" <?= in_array((int)$s['id'], $scopeMap['school'] ?? []) ? 'checked' : '' ?>> <?= e($s['name']) ?></label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div style="margin-top:14px">
          <button class="btn btn-primary" name="save_scope" value="1"><?= icon('check') ?> Save Scope</button>
          <a href="<?= e(url('admin/modules')) ?>" class="btn btn-ghost" style="margin-left:6px">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Right sidebar -->
  <div>
    <!-- Configuration -->
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('gear') ?> Configuration</h3>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="module_key" value="<?= e($mod['module_key']) ?>">
        <?php if ($cfg): ?>
          <?php foreach ($cfg as $ck => $cv): ?>
            <div class="flex-col" style="margin-bottom:10px">
              <label class="tiny faint"><?= e(ucwords(str_replace('_', ' ', $ck))) ?></label>
              <?php if (is_bool($cv)): ?>
                <select class="input" name="config[<?= e($ck) ?>]">
                  <option value="1" <?= $cv ? 'selected' : '' ?>>Enabled</option>
                  <option value="0" <?= !$cv ? 'selected' : '' ?>>Disabled</option>
                </select>
              <?php elseif (is_array($cv)): ?>
                <input class="input" name="config[<?= e($ck) ?>]" value="<?= e(json_encode($cv)) ?>" placeholder="JSON array">
              <?php else: ?>
                <input class="input" name="config[<?= e($ck) ?>]" value="<?= e((string)$cv) ?>">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="small muted">No configurable options</p>
        <?php endif; ?>
        <button class="btn btn-primary" name="save_config" value="1"><?= icon('check') ?> Save Configuration</button>
        <a href="<?= e(url('admin/modules')) ?>" class="btn btn-ghost" style="margin-left:6px">Cancel</a>
      </form>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ============= REGISTRY VIEW ============= -->
<div class="page-head">
  <div>
    <h1><?= icon('box') ?> Module Registry</h1>
    <p class="sub">National education platform modules — <?= (int)$counts['all'] ?> registered · <?= (int)$counts['on'] ?> enabled · <?= (int)$counts['off'] ?> disabled</p>
  </div>
</div>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:18px">
  <?php
  $stats = [
    ['icon' => 'layers', 'label' => 'Total', 'value' => $counts['all'], 'color' => '#6366f1'],
    ['icon' => 'shield-check', 'label' => 'Core', 'value' => $counts['core'], 'color' => '#6366f1'],
    ['icon' => 'box', 'label' => 'Standard', 'value' => $counts['standard'], 'color' => '#22c55e'],
    ['icon' => 'settings', 'label' => 'Optional', 'value' => $counts['optional'], 'color' => '#f59e0b'],
    ['icon' => 'zap', 'label' => 'Advanced', 'value' => $counts['advanced'], 'color' => '#8b5cf6'],
    ['icon' => 'check-circle', 'label' => 'Enabled', 'value' => $counts['on'], 'color' => '#22c55e'],
    ['icon' => 'x-circle', 'label' => 'Disabled', 'value' => $counts['off'], 'color' => '#ef4444'],
  ];
  foreach ($stats as $s): ?>
    <div class="card" style="padding:14px;text-align:center">
      <div style="color:<?= $s['color'] ?>;margin-bottom:4px"><?= icon($s['icon']) ?></div>
      <div style="font-size:20px;font-weight:700;color:<?= $s['color'] ?>"><?= $s['value'] ?></div>
      <div class="tiny faint"><?= $s['label'] ?></div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="flex gap-8" style="margin-bottom:18px;flex-wrap:wrap;align-items:center">
  <a href="<?= e(url('admin/modules')) ?>" class="btn <?= !$group ? 'btn-primary' : 'btn-ghost' ?>">All</a>
  <?php foreach ($groupLabels as $gk => $gl): ?>
    <a href="<?= e(url('admin/modules&group=' . $gk)) ?>" class="btn <?= $group === $gk ? 'btn-primary' : 'btn-ghost' ?>" style="<?= $group === $gk ? 'background:' . $groupColors[$gk] : '' ?>"><?= icon($groupIcons[$gk]) ?> <?= $gl ?> <span class="badge" style="margin-left:4px;background:rgba(255,255,255,.2)"><?= $counts[$gk] ?></span></a>
  <?php endforeach; ?>
  <div style="flex:1"></div>
  <form method="get" class="flex gap-6">
    <input type="hidden" name="r" value="admin/modules">
    <?php if ($group): ?><input type="hidden" name="group" value="<?= e($group) ?>"><?php endif; ?>
    <input class="input" name="q" value="<?= e($q) ?>" placeholder="Search modules…" style="width:200px">
    <select class="input" name="only">
      <option value="">All status</option>
      <option value="on" <?= $only === 'on' ? 'selected' : '' ?>>Enabled</option>
      <option value="off" <?= $only === 'off' ? 'selected' : '' ?>>Disabled</option>
    </select>
    <button class="btn btn-sm"><?= icon('search') ?></button>
  </form>
</div>

<!-- Module cards grouped by section -->
<?php
$grouped = [];
foreach ($modules as $m) $grouped[$m['mod_group']][] = $m;
$groupOrder = ['core', 'standard', 'optional', 'advanced'];
?>

<?php foreach ($groupOrder as $gk): ?>
  <?php if (empty($grouped[$gk])) continue; ?>
  <div style="margin-bottom:22px">
    <div class="flex gap-8" style="align-items:center;margin-bottom:12px">
      <div style="width:4px;height:20px;border-radius:2px;background:<?= $groupColors[$gk] ?>"></div>
      <h2 style="font-size:15px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:<?= $groupColors[$gk] ?>"><?= $groupLabels[$gk] ?></h2>
      <span class="tiny faint">— <?= count($grouped[$gk]) ?> modules</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:12px">
      <?php foreach ($grouped[$gk] as $m): ?>
        <div class="card" style="padding:16px;position:relative;overflow:hidden;transition:all .15s;<?= $m['enabled'] ? '' : 'opacity:.6' ?>">
          <?php if ($m['is_core']): ?><div style="position:absolute;top:12px;right:12px"><?= icon('shield-check') ?></div><?php endif; ?>
          <div class="flex gap-10" style="align-items:flex-start;margin-bottom:10px">
            <div style="width:40px;height:40px;border-radius:10px;background:<?= $groupColors[$gk] ?>15;display:flex;align-items:center;justify-content:center;color:<?= $groupColors[$gk] ?>;flex-shrink:0;font-size:18px">
              <?= icon($m['icon']) ?>
            </div>
            <div style="flex:1;min-width:0">
              <div class="flex-between" style="align-items:flex-start">
                <div>
                  <b class="small"><?= e($m['name']) ?></b>
                  <div class="tiny faint" style="margin-top:2px"><?= e($m['module_key']) ?> · v<?= e($m['version']) ?></div>
                </div>
                <span class="badge <?= $m['enabled'] ? 'badge-success' : 'badge-warning' ?>" style="font-size:10px;padding:2px 8px"><?= $m['enabled'] ? 'ON' : 'OFF' ?></span>
              </div>
            </div>
          </div>
          <div class="flex-between" style="margin-top:8px">
            <div class="flex gap-6" style="align-items:center">
              <span class="tiny faint" style="display:flex;align-items:center;gap:3px"><?= icon($securityIcons[$m['security_status']]) ?> <?= $securityLabels[$m['security_status']] ?></span>
              <?php if ($m['scope_type'] !== 'all'): ?><span class="tiny" style="color:var(--accent)"><?= icon('globe') ?> <?= e(ucfirst($m['scope_type'])) ?></span><?php endif; ?>
            </div>
            <div class="flex gap-4">
              <a href="<?= e(url('admin/modules&view=detail&key=' . $m['module_key'])) ?>" class="btn btn-sm btn-ghost" style="padding:4px 8px;font-size:11px"><?= icon('settings') ?></a>
              <?php if (!$m['is_core']): ?>
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="module_key" value="<?= e($m['module_key']) ?>">
                  <?php if ($m['enabled']): ?>
                    <button class="btn btn-sm btn-ghost" name="toggle" value="0" style="padding:4px 8px;font-size:11px;color:#ef4444" title="Disable"><?= icon('pause') ?></button>
                  <?php else: ?>
                    <button class="btn btn-sm btn-success" name="toggle" value="1" style="padding:4px 8px;font-size:11px" title="Enable"><?= icon('play') ?></button>
                  <?php endif; ?>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php if (!$modules): ?>
  <div class="card" style="padding:40px;text-align:center">
    <p class="muted"><?= icon('search') ?> No modules match your filters.</p>
  </div>
<?php endif; ?>
<?php endif; ?>

<script>
function toggleScopeType(radio) {
  document.getElementById('scope-selectors').style.display = radio.value === 'all' ? 'none' : 'block';
  document.querySelectorAll('.scope-option').forEach(el => {
    el.style.borderColor = 'var(--border)';
    el.style.boxShadow = '';
  });
  radio.closest('.scope-option').style.borderColor = 'transparent';
  radio.closest('.scope-option').style.boxShadow = '0 0 0 1px rgba(13,148,136,.4), inset 0 1px 1px rgba(255,255,255,.25), 0 0 12px rgba(13,148,136,.1)';
}
function filterSchools() {
  var q = document.getElementById('school-search').value.toLowerCase();
  document.querySelectorAll('.school-item').forEach(el => {
    el.style.display = el.dataset.name.includes(q) ? '' : 'none';
  });
}
// Init active scope border
document.querySelectorAll('input[name=scope_type]:checked').forEach(el => {
  el.closest('.scope-option').style.borderColor = 'transparent';
  el.closest('.scope-option').style.boxShadow = '0 0 0 1px rgba(13,148,136,.4), inset 0 1px 1px rgba(255,255,255,.25), 0 0 12px rgba(13,148,136,.1)';
});
</script>
