<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($school['name']) ?></h1>
  <a href="<?= url('index.php?r=zonal/schools') ?>" class="btn btn-ghost">← Back</a>
</div>
<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem">
  <div class="card stat"><div class="stat-num"><?= e($stats['students']) ?></div><div class="stat-lbl">Students</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['teachers']) ?></div><div class="stat-lbl">Teachers</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['directors']) ?></div><div class="stat-lbl">Directors</div></div>
  <div class="card stat"><div class="stat-num"><?= e($stats['courses']) ?></div><div class="stat-lbl">Courses</div></div>
</div>
<div class="card">
  <div class="card-head"><h2>Recent Activity</h2></div>
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Last Login</th></tr></thead>
      <tbody>
      <?php if (empty($recent)): ?>
        <tr><td colspan="4" style="text-align:center;color:var(--muted)">No recent activity.</td></tr>
      <?php else: foreach ($recent as $r): ?>
        <tr>
          <td><?= e($r['name']) ?></td>
          <td><span class="badge"><?= e($r['role']) ?></span></td>
          <td><span class="badge badge-<?= e($r['status'] ?? 'active') ?>"><?= e($r['status'] ?? 'active') ?></span></td>
          <td><?= e($r['last_login'] ?? 'Never') ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
