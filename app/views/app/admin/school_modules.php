<?php /* Sysadmin: per-school module installer */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('chip') ?> School Modules</h1>
    <p class="sub">Install and uninstall modules per institution</p>
  </div>
  <form method="get" class="flex gap-6" action="<?= e(url('admin/school-modules')) ?>">
    <select class="input" name="id" style="min-width:280px" onchange="this.form.submit()">
      <option value="0">— Choose a school —</option>
      <?php foreach ($schools as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= $school && (int)$school['id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> (<?= e($s['code']) ?> · <?= e($s['education_level']) ?>)</option>
      <?php endforeach; ?>
    </select>
    <noscript><button class="btn"><?= icon('search') ?> Load</button></noscript>
  </form>
</div>

<?php if (!$school): ?>
  <div class="card muted" style="padding:28px">Select a school above to manage its installed modules.</div>
<?php else: ?>
  <div class="card" style="margin-bottom:16px">
    <div class="flex gap-10" style="align-items:center;flex-wrap:wrap">
      <div class="flex-1">
        <b><?= e($school['name']) ?></b>
        <span class="badge badge-info"><?= e($school['education_level']) ?> level</span>
        <span class="tiny faint"><?= count($map) ?> modules installed</span>
      </div>
      <form method="post" action="<?= e(url('admin/school-modules')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="school_id" value="<?= (int)$school['id'] ?>">
        <button class="btn btn-sm btn-ghost" name="reset_defaults" value="1" onclick="return confirm('Reset to education-level defaults?')"><?= icon('refresh') ?> Reset to level defaults</button>
      </form>
    </div>
  </div>

  <div class="grid2">
    <?php foreach ($modules as $m): ?>
      <?php $state = $map[$m['module_key']] ?? null; ?>
      <div class="card" style="padding:12px 14px">
        <div class="flex gap-10" style="align-items:center">
          <div class="flex-1">
            <b><?= e($m['name']) ?></b>
            <?php if ((int)$m['is_core']): ?><span class="badge badge-accent">core</span><?php endif; ?>
            <span class="badge"><?= e($m['category']) ?></span>
            <?php if ($m['education_type'] !== 'all'): ?><span class="badge badge-info"><?= e($m['education_type']) ?></span><?php endif; ?>
            <p class="tiny faint" style="margin:2px 0 0"><?= e($m['description'] ?: '') ?></p>
          </div>
          <form method="post" action="<?= e(url('admin/school-modules')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="school_id" value="<?= (int)$school['id'] ?>">
            <input type="hidden" name="module_key" value="<?= e($m['module_key']) ?>">
            <?php if ((int)$m['is_core']): ?>
              <span class="badge badge-success"><?= icon('check') ?> always</span>
            <?php elseif ($state === 1): ?>
              <button class="btn btn-sm btn-success" name="set_module" value="0"><?= icon('check') ?> Installed</button>
            <?php else: ?>
              <button class="btn btn-sm btn-ghost" name="set_module" value="1"><?= icon('plus') ?> Install</button>
            <?php endif; ?>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
