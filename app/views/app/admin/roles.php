<?php /* Admin roles & permissions view */
$roles = $roles ?? ['regional', 'principal', 'teacher', 'student', 'parent'];
$all = $all ?? [];
$perms = $perms ?? [];
?>
<div class="page-head page-head-flex">
  <div>
    <h1><?= icon('lock') ?> Roles & Permissions</h1>
    <p class="sub">Fine-grained control over what each role can do</p>
  </div>
  <form method="post" style="display:inline" onsubmit="return confirm('Reset all roles to the default permission matrix? This overwrites your current grants.');">
    <?= csrf_field() ?>
    <input type="hidden" name="seed_defaults" value="1">
    <button class="btn btn-outline"><?= icon('rotate') ?> Restore defaults</button>
  </form>
</div>

<?php $total = array_sum(array_map('count', $all)); ?>
<div class="card" style="margin-bottom:14px">
  <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;padding:14px 18px">
    <div class="faint small"><?= $total ?> permissions across <?= count($all) ?> modules</div>
    <div style="display:flex;gap:16px;flex-wrap:wrap">
      <?php foreach ($roles as $r): ?>
        <label class="small muted"><input type="checkbox" onchange="toggleAllPerms('<?= e($r) ?>', this.checked)" style="vertical-align:-2px"> Select all · <?= e(ucfirst($r)) ?></label>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<form method="post" class="card">
  <?= csrf_field() ?>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="min-width:180px">Permission</th>
          <?php foreach ($roles as $r): ?><th style="text-align:center"><?= e(ucfirst($r)) ?></th><?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($all as $cat => $list): ?>
          <tr>
            <td colspan="<?= count($roles) + 1 ?>" class="small faint" style="background:var(--bg);padding:7px 12px">
              <b style="letter-spacing:.3em;text-transform:uppercase;font-size:11px"><?= e($cat) ?></b>
            </td>
          </tr>
          <?php foreach ($list as $p): ?>
            <tr>
              <td class="small mono"><?= e($p) ?></td>
              <?php foreach ($roles as $r): ?>
                <td style="text-align:center" data-role="<?= e($r) ?>">
                  <input type="checkbox" class="perm-cb" name="perm[<?= e($r) ?>][]" value="<?= e($p) ?>"
                         <?= in_array($p, $perms[$r] ?? [], true) ? 'checked' : '' ?>>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="display:flex;align-items:center;gap:12px;padding:14px 18px">
    <button class="btn btn-primary"><?= icon('save') ?> Save permissions</button>
    <span class="small faint" id="perm-count"></span>
  </div>
</form>

<script>
var PERM_ROLES = <?= json_encode($roles) ?>;
function toggleAllPerms(role, on) {
  document.querySelectorAll('.perm-cb').forEach(function (cb) {
    if ((cb.closest('[data-role]') || {}).dataset && cb.closest('[data-role]').dataset.role === role) cb.checked = on;
  });
  updatePermCount();
}
function countFor(role) {
  return document.querySelectorAll('.perm-cb[data-role="' + role + '"]:checked').length;
}
function updatePermCount() {
  var el = document.getElementById('perm-count');
  if (!el) return;
  el.textContent = PERM_ROLES.map(function (r) { return r + ' · ' + countFor(r); }).join('   ');
}
document.querySelectorAll('.perm-cb').forEach(function (cb) { cb.addEventListener('change', updatePermCount); });
updatePermCount();
</script>