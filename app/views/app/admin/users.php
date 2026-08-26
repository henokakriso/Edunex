<?php /* Admin users — page shell (static parts) */
$isUni = $creatorSchoolId && (Database::scalar("SELECT education_level FROM schools WHERE id = ?", [(int)$creatorSchoolId]) === 'university');
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
      <div class="flex-col"><label class="small faint">Role *</label>
        <select class="input" name="role" id="nu-role">
          <?php if ($creatorRole === 'ministry'): ?>
            <option value="regional">Regional Admin</option>
          <?php elseif ($creatorRole === 'regional'): ?>
            <option value="zonal">Zonal Admin</option>
            <option value="woreda">Woreda Admin</option>
            <option value="principal">Principal / Director</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
            <option value="parent">Parent</option>
            <?php if ($isUni): ?>
              <option value="registrar">Registrar</option>
              <option value="dean">Dean</option>
              <option value="vice_dean">Vice Dean</option>
              <option value="hod">Head of Department</option>
              <option value="lecturer">Lecturer</option>
              <option value="bursar">Bursar</option>
              <option value="student_affairs">Student Affairs</option>
              <option value="librarian">Librarian</option>
            <?php endif; ?>
          <?php elseif ($creatorRole === 'zonal'): ?>
            <option value="woreda">Woreda Admin</option>
            <option value="principal">Principal / Director</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
            <option value="parent">Parent</option>
          <?php elseif ($creatorRole === 'woreda'): ?>
            <option value="principal">Principal / Director</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
            <option value="parent">Parent</option>
          <?php elseif ($creatorRole === 'principal'): ?>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
            <option value="parent">Parent</option>
            <?php if ($isUni): ?>
              <option value="registrar">Registrar</option>
              <option value="dean">Dean</option>
              <option value="vice_dean">Vice Dean</option>
              <option value="hod">Head of Department</option>
              <option value="lecturer">Lecturer</option>
              <option value="bursar">Bursar</option>
              <option value="student_affairs">Student Affairs</option>
              <option value="librarian">Librarian</option>
            <?php endif; ?>
          <?php elseif ($creatorRole === 'registrar'): ?>
            <option value="student">Student</option>
          <?php endif; ?>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">School *</label>
        <?php if (in_array($creatorRole, ['regional','zonal','woreda'])): ?>
          <select class="input" name="school_id" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
        <?php else: ?>
          <input type="hidden" name="school_id" value="<?= (int)($u['school_id'] ?? 0) ?>">
          <input class="input" value="<?= e($u['school_name'] ?? '') ?>" disabled>
        <?php endif; ?>
      </div>
      <div class="flex-col"><label class="small faint">First name *</label><input class="input" name="first_name" required></div>
      <div class="flex-col"><label class="small faint">Last name *</label><input class="input" name="last_name" required></div>
      <div class="flex-col"><label class="small faint">Email *</label><input class="input" type="email" name="email" required></div>
      <div class="flex-col"><label class="small faint">Phone</label><input class="input" name="phone"></div>
      <div class="flex-col"><label class="small faint">Password (blank = random)</label><input class="input" type="password" name="password" autocomplete="new-password"></div>
      <div class="flex-col" id="nu-class-wrap" style="display:none"><label class="small faint">Class *</label>
        <select class="input" name="group_id"><option value="0">— Select —</option><?php foreach ($groups as $g): ?><option value="<?= (int)$g['id'] ?>"><?= e($g['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="flex-col" id="nu-dept-wrap" style="display:none"><label class="small faint">Department</label>
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

(function(){
  var role = document.getElementById('nu-role');
  var clsW = document.getElementById('nu-class-wrap');
  var deptW = document.getElementById('nu-dept-wrap');
  function toggleFields(){
    var v = role.value;
    clsW.style.display = (v === 'student') ? '' : 'none';
    deptW.style.display = (v === 'teacher' || v === 'lecturer' || v === 'hod') ? '' : 'none';
  }
  role.addEventListener('change', toggleFields);
  toggleFields();
})();
</script>
