<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
  <a href="<?= url('index.php?r=zonal/woredas') ?>" class="btn btn-ghost">← Back to Woredas</a>
</div>

<div class="card" style="margin-bottom:1.5rem">
  <div class="card-head"><h2>Schools in <?= e($woreda['name']) ?></h2></div>
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>School</th><th>Students</th><th>Teachers</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (empty($schools)): ?>
        <tr><td colspan="4" style="text-align:center;color:var(--muted)">No schools in this woreda.</td></tr>
      <?php else: foreach ($schools as $s): ?>
        <tr>
          <td><a href="<?= url('index.php?r=zonal/school&id='.$s['id']) ?>"><?= e($s['name']) ?></a></td>
          <td><?= e($s['students'] ?? 0) ?></td>
          <td><?= e($s['teachers'] ?? 0) ?></td>
          <td><span class="badge badge-<?= e($s['status'] ?? 'active') ?>"><?= e($s['status'] ?? 'active') ?></span></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
