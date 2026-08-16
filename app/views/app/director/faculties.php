<?php /* Director — Faculties & academic staff */
?>
<div class="page-head">
  <div>
    <h1><?= icon('building') ?> Faculties</h1>
    <p class="sub">University structure — faculty → department → courses</p>
  </div>
  <div class="flex gap-8">
    <button class="btn" data-open-modal="new-faculty-modal"><?= icon('plus') ?> New faculty</button>
    <button class="btn btn-primary" data-open-modal="new-staff-modal"><?= icon('user-plus') ?> Add registrar/dean</button>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-bottom:22px">
  <?php foreach ($faculties as $f): ?>
    <div class="card" style="padding:16px">
      <div class="flex gap-10" style="align-items:flex-start">
        <div class="avatar" style="background:var(--accent-soft)"><?= icon('building') ?></div>
        <div class="flex-1">
          <b><?= e($f['name']) ?> <span class="tiny faint"><?= e($f['code'] ?: '') ?></span></b>
          <p class="tiny faint"><?= (int)$f['departments'] ?> departments · <?= (int)$f['teachers'] ?> teachers</p>
          <p class="tiny"><?= icon('crown') ?> Dean: <?= $f['dean_name'] ? e($f['dean_name']) . ' <span class="faint">(' . e($f['dean_email']) . ')</span>' : '<span class="faint">not assigned</span>' ?></p>
        </div>
        <span class="badge <?= $f['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= e($f['status']) ?></span>
      </div>
      <form method="post" class="flex gap-6" style="margin-top:12px">
        <?= csrf_field() ?>
        <input type="hidden" name="faculty_id" value="<?= (int)$f['id'] ?>">
        <select class="input" name="dean_id" style="flex:1">
          <option value="0">— No dean —</option>
          <?php foreach ($deanCandidates as $dc): ?>
            <option value="<?= (int)$dc['id'] ?>" <?= (int)$f['dean_id'] === (int)$dc['id'] ? 'selected' : '' ?>><?= e($dc['first_name'] . ' ' . $dc['last_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-success" name="assign_dean" value="1"><?= icon('crown') ?> Set dean</button>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (!$faculties): ?>
    <div class="card muted" style="padding:24px;grid-column:1/-1">
      No faculties yet. Create one to organize departments and assign a dean.
      (Schools without faculties keep the flat department structure.)
    </div>
  <?php endif; ?>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('users') ?> Deans without a faculty</h3>
    <?php foreach ($unassignedDeans as $d): ?>
      <div class="list-row" style="padding:8px 0">
        <div class="avatar"><?= e(mb_substr((string)$d['name'], 0, 1)) ?></div>
        <div class="flex-1 small"><b><?= e($d['name']) ?></b><p class="tiny faint"><?= e($d['email']) ?></p></div>
      </div>
    <?php endforeach; ?>
    <?php if (!$unassignedDeans): ?><p class="muted small">All deans are assigned. Create a dean above to start.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('info') ?> How it works</h3>
    <ul class="small" style="margin:0;padding-left:18px;line-height:1.9">
      <li>Create <b>faculties</b> (e.g. Science, Business) for your university.</li>
      <li>Add a <b>dean</b> account and assign it to a faculty — the dean manages its departments, teachers, and course approvals.</li>
      <li>Add a <b>registrar</b> account — they manage enrollments and transcripts for the whole school.</li>
      <li>Deans organize <b>departments</b> inside their faculty; teachers belong to departments.</li>
    </ul>
  </div>
</div>

<div class="modal-dialog" id="new-faculty-modal">
  <form method="post" class="modal-box" style="padding:22px">
    <?= csrf_field() ?>
    <h3 class="card-title"><?= icon('plus') ?> Create faculty</h3>
    <div class="grid2" style="margin-top:6px">
      <div class="flex-col"><label class="small faint">Name *</label><input class="input" name="name" required></div>
      <div class="flex-col"><label class="small faint">Code</label><input class="input" name="code" placeholder="SCI"></div>
    </div>
    <div class="flex gap-10" style="margin-top:16px">
      <button class="btn btn-success" name="create_faculty" value="1"><?= icon('rocket') ?> Create</button>
      <button type="button" class="btn btn-ghost" data-close-modal="new-faculty-modal">Cancel</button>
    </div>
  </form>
</div>

<div class="modal-dialog" id="new-staff-modal">
  <form method="post" class="modal-box" style="padding:22px">
    <?= csrf_field() ?>
    <h3 class="card-title"><?= icon('user-plus') ?> Add academic staff</h3>
    <div class="grid2" style="margin-top:6px">
      <div class="flex-col"><label class="small faint">Role *</label>
        <select class="input" name="role">
          <option value="registrar">Registrar (school-wide)</option>
          <option value="dean">Dean (faculty)</option>
          <option value="vice_dean">Vice Dean (faculty)</option>
          <option value="dept_head">Department Head</option>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">Faculty (deans / vice deans)</label>
        <select class="input" name="faculty_id">
          <option value="0">— None —</option>
          <?php foreach ($faculties as $f): ?><option value="<?= (int)$f['id'] ?>"><?= e($f['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">Department (dept heads)</label>
        <select class="input" name="department_id">
          <option value="0">— None —</option>
          <?php foreach ($departments as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?><?= $d['faculty_id'] ? ' · ' . e((string)Database::scalar('SELECT name FROM faculties WHERE id = ?', [(int)$d['faculty_id']], '')) : '' ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">First name *</label><input class="input" name="first_name" required></div>
      <div class="flex-col"><label class="small faint">Last name *</label><input class="input" name="last_name" required></div>
      <div class="flex-col"><label class="small faint">Email *</label><input class="input" type="email" name="email" required></div>
      <div class="flex-col"><label class="small faint">Password (blank = random)</label><input class="input" type="password" name="password" autocomplete="new-password"></div>
    </div>
    <div class="flex gap-10" style="margin-top:16px">
      <button class="btn btn-success" name="create_staff" value="1"><?= icon('rocket') ?> Create</button>
      <button type="button" class="btn btn-ghost" data-close-modal="new-staff-modal">Cancel</button>
    </div>
  </form>
</div>
