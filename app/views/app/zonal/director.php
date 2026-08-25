<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($director['first_name'].' '.$director['last_name']) ?></h1>
  <a href="<?= url('index.php?r=zonal/directors') ?>" class="btn btn-ghost">← Back</a>
</div>
<div class="grid" style="grid-template-columns:1fr 1fr;gap:1.5rem">
  <div class="card">
    <div class="card-head"><h2>Info</h2></div>
    <p><strong>Email:</strong> <?= e($director['email']) ?></p>
    <p><strong>Phone:</strong> <?= e($director['phone'] ?? '—') ?></p>
    <p><strong>School:</strong> <?= e($director['school_name']) ?></p>
    <p><strong>Status:</strong> <span class="badge badge-<?= e($director['status'] ?? 'active') ?>"><?= e($director['status'] ?? 'active') ?></span></p>
    <p><strong>Last Login:</strong> <?= e($director['last_login'] ?? 'Never') ?></p>
  </div>
  <div class="card">
    <div class="card-head"><h2>Reset Password</h2></div>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="reset_password" value="1">
      <button class="btn btn-warning" type="submit" onclick="return confirm('Reset password?')">Generate New Password</button>
    </form>
  </div>
</div>
<div class="card" style="margin-top:1.5rem">
  <div class="card-head"><h2>Activity Log</h2></div>
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>Action</th><th>Detail</th><th>Time</th></tr></thead>
      <tbody>
      <?php if (empty($activity)): ?>
        <tr><td colspan="3" style="text-align:center;color:var(--muted)">No activity.</td></tr>
      <?php else: foreach ($activity as $a): ?>
        <tr>
          <td><span class="badge"><?= e($a['action']) ?></span></td>
          <td><?= e($a['detail'] ?? '') ?></td>
          <td><?= e($a['created_at']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
