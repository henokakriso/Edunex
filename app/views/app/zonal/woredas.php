<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:1.5rem">
  <div class="card">
    <div class="card-head"><h2>Woredas</h2></div>
    <div style="overflow-x:auto">
      <table class="table">
        <thead><tr><th>Name</th><th>Schools</th><th>Admin</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($woredas)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--muted)">No woredas.</td></tr>
        <?php else: foreach ($woredas as $w): ?>
          <tr>
            <td><a href="<?= url('index.php?r=zonal/woreda&id='.$w['id']) ?>"><?= e($w['name']) ?></a></td>
            <td><?= e($w['schools'] ?? 0) ?></td>
            <td><?= $w['admin_id'] ? '<span class="badge badge-success">Assigned</span>' : '<span class="badge badge-warning">Unassigned</span>' ?></td>
            <td>
              <form method="POST" style="display:inline" onsubmit="return confirm('Assign admin?')">
                <?= csrf_field() ?>
                <input type="hidden" name="assign_woreda" value="1">
                <input type="hidden" name="woreda_id" value="<?= (int)$w['id'] ?>">
                <select name="admin_id" required style="max-width:160px">
                  <option value="">Select admin…</option>
                  <?php foreach ($admins as $a): ?>
                    <option value="<?= (int)$a['id'] ?>"><?= e($a['first_name'].' '.$a['last_name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-primary" type="submit">Assign</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2>Create Woreda</h2></div>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="create_woreda" value="1">
      <div class="form-group">
        <label>Woreda Name</label>
        <input type="text" name="name" required class="form-control" placeholder="e.g. Bahir Dar">
      </div>
      <button class="btn btn-primary" type="submit">Create</button>
    </form>
  </div>
</div>
