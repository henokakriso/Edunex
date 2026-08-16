<?php /* Regional directors — list + create (assigned schools only) */
?>
<div class="page-head">
  <div>
    <h1><?= icon('target') ?> Directors</h1>
    <p class="sub">Across your assigned schools</p>
  </div>
  <button class="btn btn-primary" data-open-modal="new-director-modal"><?= icon('user-plus') ?> Add director</button>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr><th>Director</th><th>School</th><th>Phone</th><th>Last login</th><th>Status</th><th style="width:150px">Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b><p class="tiny faint"><?= e($r['email']) ?></p></td>
          <td><?= e($r['school_name']) ?></td>
          <td><?= e($r['phone'] ?: '—') ?></td>
          <td><?= $r['last_login'] ? e(time_ago($r['last_login'])) : 'never' ?></td>
          <td><span class="badge <?= $r['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($r['status']) ?></span></td>
          <td>
            <div class="flex gap-6">
              <a class="btn btn-sm btn-ghost" href="<?= e(url('regional/director&id=' . (int)$r['id'])) ?>"><?= icon('eye') ?> View</a>
              <form method="post" class="inline">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-sm <?= $r['status'] === 'active' ? '' : 'btn-success' ?>" name="toggle_director" value="1"
                  onclick="return confirm('<?= $r['status'] === 'active' ? 'Suspend' : 'Reactivate' ?> this director?')">
                  <?= $r['status'] === 'active' ? icon('pause') . ' Suspend' : icon('check') . ' Reactivate' ?>
                </button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="muted">No directors yet in your schools.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal-dialog" id="new-director-modal">
  <form method="post" class="modal-box" style="padding:22px">
    <?= csrf_field() ?>
    <h3 class="card-title"><?= icon('user-plus') ?> Create director</h3>
    <div class="grid2" style="margin-top:6px">
      <div class="flex-col"><label class="small faint">School *</label>
        <select class="input" name="school_id" required>
          <?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">First name *</label><input class="input" name="first_name" required></div>
      <div class="flex-col"><label class="small faint">Last name *</label><input class="input" name="last_name" required></div>
      <div class="flex-col"><label class="small faint">Email *</label><input class="input" type="email" name="email" required></div>
      <div class="flex-col"><label class="small faint">Phone</label><input class="input" name="phone"></div>
      <div class="flex-col"><label class="small faint">Password (blank = random)</label><input class="input" type="password" name="password" autocomplete="new-password"></div>
    </div>
    <div class="flex gap-10" style="margin-top:16px">
      <button class="btn btn-success" name="create_director" value="1"><?= icon('rocket') ?> Create</button>
      <button type="button" class="btn btn-ghost" data-close-modal="new-director-modal">Cancel</button>
    </div>
  </form>
</div>
