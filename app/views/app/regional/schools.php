<?php /* Regional schools list — assigned schools only */
?>
<div class="page-head">
  <div>
    <h1><?= icon('building') ?> My Schools</h1>
    <p class="sub">Schools assigned to you — <?= count($rows) ?></p>
  </div>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th>School</th><th>Type</th><th>Directors</th><th>Teachers</th><th>Students</th><th>Status</th><th style="width:210px">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['name']) ?></b><p class="tiny faint"><?= e($r['code']) ?> · <?= e($r['city'] ?: '—') ?></p></td>
          <td><?= e($r['type']) ?></td>
          <td><?= (int)$r['directors'] ?></td>
          <td><?= (int)$r['teachers'] ?></td>
          <td><?= (int)$r['students'] ?></td>
          <td><span class="badge <?= $r['status'] === 'active' ? 'badge-success' : ($r['status'] === 'archived' ? '' : 'badge-warning') ?>"><?= e($r['status']) ?></span></td>
          <td>
            <div class="flex gap-6">
              <a class="btn btn-sm btn-ghost" href="<?= e(url('regional/school&id=' . (int)$r['id'])) ?>"><?= icon('eye') ?> View</a>
              <?php if ($r['status'] === 'active'): ?>
                <form method="post" class="inline">
                  <?= csrf_field() ?><input type="hidden" name="school_id" value="<?= (int)$r['id'] ?>">
                  <button class="btn btn-sm" name="action" value="suspend" onclick="return confirm('Suspend this school? Users will not be able to log in.')"><?= icon('pause') ?> Suspend</button>
                </form>
              <?php else: ?>
                <form method="post" class="inline">
                  <?= csrf_field() ?><input type="hidden" name="school_id" value="<?= (int)$r['id'] ?>">
                  <button class="btn btn-sm btn-success" name="action" value="activate"><?= icon('check') ?> Activate</button>
                </form>
              <?php endif; ?>
              <form method="post" class="inline">
                <?= csrf_field() ?><input type="hidden" name="school_id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-sm btn-danger" name="action" value="archive" onclick="return confirm('Archive this school? This hides it from active use.')"><?= icon('trash') ?> Archive</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="7" class="muted">No schools assigned to you yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
