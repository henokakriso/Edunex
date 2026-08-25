<?php /* Admin users — page shell (static parts) */
?>
<div id="users-root" class="list-root">
<?php include __DIR__ . '/users_partial.php'; ?>
</div>
<!-- Detail drawer -->
<div class="drawer" id="item-drawer">
  <div class="drawer-head"><b>User details</b><button class="btn btn-sm btn-ghost" id="drawer-close">✕</button></div>
  <div id="drawer-body" class="drawer-body"></div>
</div>
<div class="drawer-backdrop" id="drawer-backdrop"></div>

<!-- Create user modal -->
<div class="modal-dialog" id="new-user-modal">
  <form method="post" class="modal-box" style="max-height:92vh;overflow:auto;padding:22px">
    <?= csrf_field() ?>
    <h3 class="card-title"><?= icon('plus') ?> Create user</h3>
    <div class="grid2" style="margin-top:6px">
      <div class="flex-col"><label class="small faint">Role</label>
        <select class="input" name="role" id="nu-role">
          <option value="student">Student</option><option value="teacher">Teacher</option>
          <option value="parent">Parent</option><option value="principal">Principal</option>
          <option value="registrar">Registrar</option><option value="dean">Dean</option>
          <option value="regional">Regional Admin</option><option value="ministry">Ministry (Super Admin)</option>
          <option value="zonal">Zonal Admin</option><option value="woreda">Woreda Admin</option>
          <option value="hod">Head of Department</option>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">School *</label>
        <select class="input" name="school_id" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="flex-col"><label class="small faint">First name *</label><input class="input" name="first_name" required></div>
      <div class="flex-col"><label class="small faint">Last name *</label><input class="input" name="last_name" required></div>
      <div class="flex-col"><label class="small faint">Email *</label><input class="input" type="email" name="email" required></div>
      <div class="flex-col"><label class="small faint">Phone</label><input class="input" name="phone"></div>
      <div class="flex-col"><label class="small faint">Password (blank = random)</label><input class="input" type="password" name="password" autocomplete="new-password"></div>
      <div class="flex-col"><label class="small faint">Class (students)</label>
        <select class="input" name="group_id"><option value="0">— None —</option><?php foreach ($groups as $g): ?><option value="<?= (int)$g['id'] ?>"><?= e($g['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Department (teachers)</label>
        <select class="input" name="department_id"><option value="0">— None —</option><?php foreach ($depts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select>
      </div>
    </div>
    <div class="flex gap-10" style="margin-top:16px">
      <button class="btn btn-success" name="create_user" value="1"><?= icon('rocket') ?> Create</button>
      <button type="button" class="btn btn-ghost" data-close-modal="new-user-modal">Cancel</button>
    </div>
  </form>
</div>

<script>
window.EDUNEX_CSRF = <?= json_encode(csrf_field()) ?>;
window.EDUNEX_USERS = { total: <?= (int)$total ?>, page: <?= (int)$page ?>, pages: <?= (int)$pages ?> };
</script>
