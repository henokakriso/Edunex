<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>
<div class="card">
  <div style="overflow-x:auto">
    <table class="table">
      <thead><tr><th>School</th><th>Woreda</th><th>Directors</th><th>Students</th><th>Teachers</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--muted)">No schools found.</td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><a href="<?= url('index.php?r=zonal/school&id='.$r['id']) ?>"><?= e($r['name']) ?></a></td>
          <td><?= e($r['woreda_name'] ?? '—') ?></td>
          <td><?= e($r['directors']) ?></td>
          <td><?= e($r['students']) ?></td>
          <td><?= e($r['teachers']) ?></td>
          <td><span class="badge badge-<?= e($r['status'] ?? 'active') ?>"><?= e($r['status'] ?? 'active') ?></span></td>
          <td>
            <?php if (($r['status'] ?? 'active') === 'active'): ?>
              <form method="POST" style="display:inline" onsubmit="return confirm('Suspend this school?')">
                <?= csrf_field() ?>
                <input type="hidden" name="school_id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="action" value="suspend">
                <button class="btn btn-sm btn-warning" type="submit">Suspend</button>
              </form>
            <?php else: ?>
              <form method="POST" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="school_id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="action" value="activate">
                <button class="btn btn-sm btn-success" type="submit">Activate</button>
              </form>
            <?php endif; ?>
            <form method="POST" style="display:inline" onsubmit="return confirm('Archive this school?')">
              <?= csrf_field() ?>
              <input type="hidden" name="school_id" value="<?= (int)$r['id'] ?>">
              <input type="hidden" name="action" value="archive">
              <button class="btn btn-sm btn-ghost" type="submit">Archive</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
