<?php /* Director: bulk import teachers/students */ ?>
<div class="page-head">
  <div><h1><?= icon('download') ?> Bulk Import</h1><p class="sub">Create many accounts at once from an Excel (.xlsx) or CSV file</p></div>
</div>

<div class="card" style="max-width:640px">
  <div class="tabs" style="margin-bottom:16px">
    <a class="tab <?= $target === 'teacher' ? 'active' : '' ?>" href="<?= e(url('director/import&type=teacher')) ?>">Teachers</a>
    <a class="tab <?= $target === 'student' ? 'active' : '' ?>" href="<?= e(url('director/import&type=student')) ?>">Students</a>
  </div>

  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= e($target) ?>">
    <div class="field">
      <label>Spreadsheet file (<?= $target === 'teacher' ? 'columns: first_name, last_name, email, phone, department' : 'columns: first_name, last_name, email, phone, class' ?>)</label>
      <input class="input" type="file" name="file" accept=".csv,.xlsx" required>
    </div>
    <div class="field">
      <label>Initial password (blank = unique random password per account)</label>
      <input class="input" name="password" placeholder="leave blank to auto-generate">
    </div>
    <button class="btn btn-primary" style="width:100%">Import <?= e($target) ?>s</button>
  </form>
  <p class="help" style="margin-top:12px"><?= icon('bulb') ?> The first row must contain the column names above. Unknown columns are ignored.</p>
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
      <p class="small faint" style="padding:10px 16px">✔ Created <?= count($result['created']) ?> account(s). Share the passwords securely.</p>
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
