<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>
<div class="card" style="margin-bottom:1.5rem">
  <div class="card-head"><h2>Create Director</h2></div>
  <form method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="create_director" value="1">
    <div class="grid" style="grid-template-columns:1fr 1fr;gap:1rem">
      <div class="form-group">
        <label>School</label>
        <select name="school_id" required class="form-control">
          <option value="">Select school…</option>
          <?php foreach ($schools as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>First Name</label><input type="text" name="first_name" required class="form-control"></div>
      <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required class="form-control"></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" required class="form-control"></div>
      <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
      <div class="form-group"><label>Password (blank=random)</label><input type="text" name="password" class="form-control"></div>
    </div>
    <button class="btn btn-primary" type="submit">Create Director</button>
  </form>
</div>
<div class="card">
  <div class="card-head"><h2>Directors</h2></div>
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>Name</th><th>Email</th><th>School</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--muted)">No directors found.</td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><a href="<?= url('index.php?r=zonal/director&id='.$r['id']) ?>"><?= e($r['first_name'].' '.$r['last_name']) ?></a></td>
          <td><?= e($r['email']) ?></td>
          <td><?= e($r['school_name']) ?></td>
          <td><span class="badge badge-<?= e($r['status'] ?? 'active') ?>"><?= e($r['status'] ?? 'active') ?></span></td>
          <td><?= e($r['last_login'] ?? 'Never') ?></td>
          <td>
            <form method="POST" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="toggle_director" value="1">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-sm btn-ghost" type="submit"><?= ($r['status'] ?? 'active') === 'active' ? 'Suspend' : 'Activate' ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
