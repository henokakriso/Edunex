<?php /* Super admin: bulk import directors/teachers/students */ ?>
<div class="page-head">
  <div><h1><?= icon('download') ?> Bulk Import</h1><p class="sub">Super admin — create many accounts at once</p></div>
</div>

<div class="card" style="max-width:680px">
  <div class="tabs" style="margin-bottom:16px">
    <a class="tab <?= $target === 'principal' ? 'active' : '' ?>" href="<?= e(url('admin/import&type=director')) ?>">Directors</a>
    <a class="tab <?= $target === 'teacher' ? 'active' : '' ?>" href="<?= e(url('admin/import&type=teacher')) ?>">Teachers</a>
    <a class="tab <?= $target === 'student' ? 'active' : '' ?>" href="<?= e(url('admin/import&type=student')) ?>">Students</a>
  </div>

  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= e($target) ?>">
    <?php if ($target !== 'principal'): ?>
      <div class="field">
        <label>School</label>
        <select class="select" name="school_id" required>
          <option value="">— Select school —</option>
          <?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>
    <div class="field">
      <label>Spreadsheet file
        <?php if ($target === 'principal'): ?>(columns: first_name, last_name, email, phone)
        <?php elseif ($target === 'teacher'): ?>(columns: first_name, last_name, email, phone, department)
        <?php else: ?>(columns: first_name, last_name, email, phone, class)<?php endif; ?>
      </label>
      <input class="input" type="file" name="file" accept=".csv,.xlsx" required>
    </div>
    <div class="field">
      <label>Initial password (blank = unique random password per account)</label>
      <input class="input" name="password" placeholder="leave blank to auto-generate">
    </div>
    <button class="btn btn-primary" style="width:100%">Import <?= e($target) ?>s</button>
  </form>
  <p class="help" style="margin-top:12px"><?= icon('bulb') ?> Directors are created without a school binding — assign their school when needed.</p>
</div>

<?php if ($result): ?>
  <div class="card" style="margin-top:18px;padding:0;overflow:hidden">
    <div class="card-head"><b>Result</b></div>
    <?php if ($result['created']): ?>
      <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Password</th></tr></thead>
        <tbody>
          <?php foreach ($result['created'] as $c): ?>
            <tr><td><?= e($c['name']) ?></td><td><?= e($c['email']) ?></td><td class="mono"><?= e($c['password']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="small faint" style="padding:10px 16px">✔ Created <?= count($result['created']) ?> account(s).</p>
    <?php endif; ?>
    <?php if (!empty($result['errors'])): ?>
      <div class="alert alert-danger" style="margin:12px 16px">
        <?php foreach ($result['errors'] as $e): ?><div>• <?= e($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
<?php elseif ($msg): ?>
  <div class="alert alert-danger" style="margin-top:16px"><?= e($msg) ?></div>
<?php endif; ?>
