<?php /* Admin users — page shell (static parts) */
$isUni = $creatorSchoolId && (Database::scalar("SELECT education_level FROM schools WHERE id = ?", [(int)$creatorSchoolId]) === 'university');

// Scope-based school list
$scopeSchools = $schools;
if ($creatorRole === 'regional') {
    $zoneId = (int)($__u['zone_id'] ?? 0);
    if ($zoneId) {
        $wIds = array_column(Database::all("SELECT id FROM woredas WHERE zone_id = ?", [$zoneId]), 'id');
        if ($wIds) { $ph = implode(',', array_fill(0, count($wIds), '?')); $scopeSchools = Database::all("SELECT id, name FROM schools WHERE woreda_id IN ($ph) ORDER BY name", $wIds); }
    }
} elseif ($creatorRole === 'zonal') {
    $zoneId = (int)($__u['zone_id'] ?? 0);
    if ($zoneId) {
        $wIds = array_column(Database::all("SELECT id FROM woredas WHERE zone_id = ?", [$zoneId]), 'id');
        if ($wIds) { $ph = implode(',', array_fill(0, count($wIds), '?')); $scopeSchools = Database::all("SELECT id, name FROM schools WHERE woreda_id IN ($ph) ORDER BY name", $wIds); }
    }
} elseif ($creatorRole === 'woreda') {
    $woredaId = (int)($__u['woreda_id'] ?? 0);
    if ($woredaId) { $scopeSchools = Database::all("SELECT id, name FROM schools WHERE woreda_id = ? ORDER BY name", [$woredaId]); }
}

$uniStaff = ['registrar','dean','vice_dean','hod','lecturer','bursar','student_affairs','librarian'];
$regions = ['Addis Ababa','Afar','Amhara','Benishangul-Gumuz','Dire Dawa','Gambela','Harari','Oromia','Sidama','SNNPR','Somali','Tigray','South West Ethiopia People\'s Region'];
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
  <form method="post" class="modal-box" id="create-user-form" style="max-height:95vh;max-width:800px;width:95vw;overflow:hidden;padding:0;border-radius:16px">
    <?= csrf_field() ?>
    <input type="hidden" name="create_user" value="1">

    <!-- Header -->
    <div style="padding:20px 28px 0;border-bottom:1px solid var(--border)">
      <div class="flex-between" style="margin-bottom:12px">
        <h3 class="card-title" style="margin:0;font-size:16px"><?= icon('plus') ?> Create user</h3>
        <button type="button" class="btn btn-ghost btn-sm" data-close-modal="new-user-modal" style="font-size:20px;line-height:1;padding:4px 8px">✕</button>
      </div>
      <div class="flex gap-8" style="font-size:11.5px;color:var(--text-faint);margin-bottom:14px;flex-wrap:wrap">
        <span id="form-role-label" style="font-weight:700;color:var(--accent)">Select a role</span>
      </div>
    </div>

    <!-- Scrollable body -->
    <div style="padding:20px 28px;max-height:65vh;overflow-y:auto">

      <!-- ROLE SELECT -->
      <div style="margin-bottom:18px">
        <label class="small faint" style="margin-bottom:5px;display:block">Role *</label>
        <select class="input" name="role" id="nu-role" style="padding:10px 14px;width:100%">
          <?php if ($creatorRole === 'ministry'): ?>
            <option value="regional">Regional Admin</option>
          <?php elseif ($creatorRole === 'regional'): ?>
            <option value="zonal">Zonal Admin</option>
            <option value="woreda">Woreda Admin</option>
            <option value="principal">Principal / Director</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
            <option value="parent">Parent</option>
            <?php if ($isUni): foreach ($uniStaff as $sr): ?><option value="<?= $sr ?>"><?= ucfirst(str_replace('_', ' ', $sr)) ?></option><?php endforeach; endif; ?>
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
            <?php if ($isUni): foreach ($uniStaff as $sr): ?><option value="<?= $sr ?>"><?= ucfirst(str_replace('_', ' ', $sr)) ?></option><?php endforeach; endif; ?>
          <?php elseif ($creatorRole === 'registrar'): ?>
            <option value="student">Student</option>
          <?php endif; ?>
        </select>
      </div>

      <!-- ============ PERSONAL INFORMATION (all roles) ============ -->
      <div class="form-section" data-roles="all">
        <h4 style="margin:0 0 12px;font-size:13px;color:var(--text)"><?= icon('user') ?> Personal Information</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col"><label class="small faint">First Name *</label><input class="input" name="first_name" required style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Middle Name</label><input class="input" name="middle_name" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Last Name *</label><input class="input" name="last_name" required style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Gender *</label>
            <select class="input" name="gender" required style="padding:10px 14px"><option value="">— Select —</option><option value="male">Male</option><option value="female">Female</option></select>
          </div>
          <div class="flex-col"><label class="small faint">Date of Birth</label><input class="input" name="birth_date" type="date" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Profile Photo</label><input class="input" type="file" name="avatar" accept="image/*" style="padding:8px 14px"></div>
        </div>
      </div>

      <!-- ============ IDENTITY (all roles) ============ -->
      <div class="form-section" data-roles="all">
        <h4 style="margin:18px 0 12px;font-size:13px;color:var(--text)"><?= icon('id-card') ?> Identity</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col"><label class="small faint">National ID / Fayda ID</label><input class="input" name="national_id" style="padding:10px 14px"></div>
          <div class="flex-col" data-show-for="regional,zonal,woreda,principal,teacher,lecturer,hod"><label class="small faint">Employee ID</label><input class="input" name="employee_id" style="padding:10px 14px"></div>
          <div class="flex-col" data-show-for="student"><label class="small faint">Student ID <span class="tiny faint">(auto-generated)</span></label><input class="input" name="student_id_display" disabled style="padding:10px 14px;background:var(--bg-soft)"></div>
          <div class="flex-col" data-show-for="student"><label class="small faint">Birth Certificate Number</label><input class="input" name="birth_cert_number" style="padding:10px 14px"></div>
        </div>
      </div>

      <!-- ============ CONTACT (all roles) ============ -->
      <div class="form-section" data-roles="all">
        <h4 style="margin:18px 0 12px;font-size:13px;color:var(--text)"><?= icon('phone') ?> Contact</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col"><label class="small faint">Phone *</label><input class="input" name="phone" required style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Alternative Phone</label><input class="input" name="alt_phone" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Email *</label><input class="input" type="email" name="email" required style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Emergency Contact</label><input class="input" name="emergency_contact" placeholder="Name and phone" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Address</label><input class="input" name="address" style="padding:10px 14px"></div>
          <div class="flex-col" data-show-for="student,parent"><label class="small faint">Kebele</label><input class="input" name="kebele" style="padding:10px 14px"></div>
          <div class="flex-col" data-show-for="student,parent"><label class="small faint">Region</label>
            <select class="input" name="region" style="padding:10px 14px"><option value="">— Select —</option><?php foreach ($regions as $rg): ?><option value="<?= e($rg) ?>"><?= e($rg) ?></option><?php endforeach; ?></select>
          </div>
        </div>
      </div>

      <!-- ============ ADMIN ASSIGNMENT (regional only) ============ -->
      <div class="form-section" data-roles="regional">
        <h4 style="margin:18px 0 12px;font-size:13px;color:var(--text)"><?= icon('briefcase') ?> Assignment</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col"><label class="small faint">Admin Type *</label>
            <select class="input" name="admin_type" required style="padding:10px 14px"><option value="">— Select —</option><option value="regional">Regional</option><option value="zonal">Zonal</option><option value="woreda">Woreda</option></select>
          </div>
          <div class="flex-col"><label class="small faint">Assigned Region *</label>
            <select class="input" name="assigned_region" required style="padding:10px 14px"><option value="">— Select —</option><?php foreach ($regions as $rg): ?><option value="<?= e($rg) ?>"><?= e($rg) ?></option><?php endforeach; ?></select>
          </div>
          <div class="flex-col"><label class="small faint">Assigned Zone</label><input class="input" name="assigned_zone" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Assigned Woreda</label><input class="input" name="assigned_woreda" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Start Date</label><input class="input" name="start_date" type="date" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">End Date</label><input class="input" name="end_date" type="date" style="padding:10px 14px"></div>
        </div>
      </div>

      <!-- ============ EMPLOYMENT (principal, teacher, staff) ============ -->
      <div class="form-section" data-roles="principal,teacher,lecturer,hod,registrar,dean,vice_dean,bursar,student_affairs,librarian">
        <h4 style="margin:18px 0 12px;font-size:13px;color:var(--text)"><?= icon('briefcase') ?> Employment</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col"><label class="small faint">Position</label><input class="input" name="position" placeholder="e.g. Head of Department" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Employment Type</label>
            <select class="input" name="employment_type" style="padding:10px 14px"><option value="">— Select —</option><option value="full-time">Full-time</option><option value="part-time">Part-time</option><option value="contract">Contract</option><option value="temporary">Temporary</option></select>
          </div>
          <div class="flex-col" data-show-for="teacher,lecturer,hod"><label class="small faint">Qualification *</label>
            <select class="input" name="qualification" required style="padding:10px 14px"><option value="">— Select —</option><option value="bed">B.Ed</option><option value="bed-hons">B.Ed (Hons)</option><option value="med">M.Ed</option><option value="ma">M.A</option><option value="msc">M.Sc</option><option value="phd">Ph.D</option><option value="bed-sub">B.Ed (Subsidiary)</option><option value="dte">DTE</option><option value="other">Other</option></select>
          </div>
          <div class="flex-col" data-show-for="teacher,lecturer,hod"><label class="small faint">Specialization *</label><input class="input" name="specialization" required placeholder="e.g. Mathematics" style="padding:10px 14px"></div>
          <div class="flex-col" data-show-for="teacher,lecturer,hod"><label class="small faint">Certification</label><input class="input" name="certification" placeholder="Teaching license number" style="padding:10px 14px"></div>
          <div class="flex-col" data-show-for="teacher,lecturer,hod"><label class="small faint">Years of Experience</label><input class="input" name="experience_years" type="number" min="0" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Hire Date</label><input class="input" name="hire_date" type="date" style="padding:10px 14px"></div>
        </div>
      </div>

      <!-- ============ SCHOOL ASSIGNMENT (teacher, staff, student) ============ -->
      <div class="form-section" data-roles="regional,zonal,woreda,principal,teacher,student,parent,lecturer,hod,registrar,dean,vice_dean,bursar,student_affairs,librarian">
        <h4 style="margin:18px 0 12px;font-size:13px;color:var(--text)"><?= icon('school') ?> School Assignment</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col" style="grid-column:1/-1"><label class="small faint">School *</label>
            <?php if (in_array($creatorRole, ['ministry','regional','zonal','woreda'])): ?>
              <select class="input" name="school_id" id="nu-school" required style="padding:10px 14px">
                <?php if (empty($scopeSchools)): ?><option value="">— No schools in your scope —</option><?php else: ?>
                  <?php foreach ($scopeSchools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
                <?php endif; ?>
              </select>
            <?php else: ?>
              <input type="hidden" name="school_id" value="<?= (int)($u['school_id'] ?? 0) ?>">
              <input class="input" value="<?= e($u['school_name'] ?? '') ?>" disabled style="padding:10px 14px">
            <?php endif; ?>
          </div>
          <div class="flex-col" data-show-for="teacher,lecturer,hod"><label class="small faint">Department</label>
            <select class="input" name="department_id" style="padding:10px 14px"><option value="0">— None —</option><?php foreach ($depts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select>
          </div>
          <div class="flex-col" data-show-for="teacher,lecturer,hod"><label class="small faint">Grade Levels</label><input class="input" name="grade_levels" placeholder="e.g. 9, 10, 11" style="padding:10px 14px"></div>
          <div class="flex-col" data-show-for="teacher,lecturer,hod"><label class="small faint">Sections</label><input class="input" name="sections" placeholder="e.g. A, B" style="padding:10px 14px"></div>
          <div class="flex-col" data-show-for="student"><label class="small faint">Class *</label>
            <select class="input" name="group_id" required style="padding:10px 14px"><option value="0">— Select —</option><?php foreach ($groups as $g): ?><option value="<?= (int)$g['id'] ?>"><?= e($g['name']) ?></option><?php endforeach; ?></select>
          </div>
        </div>
      </div>

      <!-- ============ PARENT / GUARDIAN (student only) ============ -->
      <div class="form-section" data-roles="student">
        <h4 style="margin:18px 0 12px;font-size:13px;color:var(--text)"><?= icon('users') ?> Parent / Guardian</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col"><label class="small faint">Parent/Guardian Name *</label><input class="input" name="parent_name" required style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Relationship *</label>
            <select class="input" name="relationship" required style="padding:10px 14px"><option value="">— Select —</option><option value="father">Father</option><option value="mother">Mother</option><option value="guardian">Guardian</option><option value="sibling">Sibling</option><option value="other">Other</option></select>
          </div>
          <div class="flex-col"><label class="small faint">Parent Phone *</label><input class="input" name="parent_phone" required style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Parent ID / Fayda ID</label><input class="input" name="parent_national_id" style="padding:10px 14px"></div>
        </div>
      </div>

      <!-- ============ RELATIONSHIP (parent only) ============ -->
      <div class="form-section" data-roles="parent">
        <h4 style="margin:18px 0 12px;font-size:13px;color:var(--text)"><?= icon('link') ?> Relationship</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col"><label class="small faint">Relationship to Student *</label>
            <select class="input" name="relationship" required style="padding:10px 14px"><option value="">— Select —</option><option value="father">Father</option><option value="mother">Mother</option><option value="guardian">Guardian</option><option value="sibling">Sibling</option><option value="other">Other</option></select>
          </div>
          <div class="flex-col"><label class="small faint">Student ID(s) *</label><input class="input" name="linked_student_ids" required placeholder="Student ID(s), comma-separated" style="padding:10px 14px"></div>
        </div>
      </div>

      <!-- ============ SPECIAL INFO (student only) ============ -->
      <div class="form-section" data-roles="student">
        <h4 style="margin:18px 0 12px;font-size:13px;color:var(--text)"><?= icon('info') ?> Special Information</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col"><label class="small faint">Teaching Language</label>
            <select class="input" name="language" style="padding:10px 14px"><option value="amharic">Amharic</option><option value="english">English</option><option value="afaan_oromo">Afaan Oromo</option><option value="tigrinya">Tigrinya</option><option value="somali">Somali</option></select>
          </div>
          <div class="flex-col"><label class="small faint">Previous School</label><input class="input" name="previous_school" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Previous Grade</label><input class="input" name="previous_grade" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Student Type</label>
            <select class="input" name="student_type" style="padding:10px 14px"><option value="regular">Regular</option><option value="boarding">Boarding</option><option value="extension">Extension</option><option value="summer">Summer</option></select>
          </div>
          <div class="flex-col"><label class="small faint">Disability Support</label>
            <select class="input" name="disability_support" style="padding:10px 14px"><option value="0">No</option><option value="1">Yes</option></select>
          </div>
          <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Special Educational Needs</label><textarea class="input" name="special_needs" rows="2" style="padding:10px 14px;resize:vertical"></textarea></div>
        </div>
      </div>

      <!-- ============ ACCOUNT (all roles) ============ -->
      <div class="form-section" data-roles="all">
        <h4 style="margin:18px 0 12px;font-size:13px;color:var(--text)"><?= icon('lock') ?> Account</h4>
        <div class="grid2" style="gap:14px">
          <div class="flex-col"><label class="small faint">Username</label><input class="input" name="username" placeholder="Auto-generated from email if blank" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Password (blank = random)</label><input class="input" type="password" name="password" autocomplete="new-password" style="padding:10px 14px"></div>
          <div class="flex-col"><label class="small faint">Account Status</label>
            <select class="input" name="status" style="padding:10px 14px"><option value="active">Active</option><option value="pending">Pending</option><option value="suspended">Suspended</option></select>
          </div>
          <div class="flex-col" data-show-for="regional,zonal,woreda,principal"><label class="small faint">2FA Required</label>
            <select class="input" name="twofa_required" style="padding:10px 14px"><option value="0">No</option><option value="1">Yes</option></select>
          </div>
        </div>
      </div>

    </div><!-- /scrollable body -->

    <!-- Validation error banner -->
    <div id="form-error" style="display:none;padding:10px 28px;background:#fef2f2;border-top:1px solid #fecaca;color:#dc2626;font-size:12.5px;font-weight:600">
      Please fill all required fields (*) before submitting.
    </div>

    <!-- Navigation -->
    <div style="padding:16px 28px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px;background:var(--bg-soft);border-radius:0 0 16px 16px">
      <button type="button" class="btn btn-ghost" data-close-modal="new-user-modal">Cancel</button>
      <button type="submit" class="btn btn-success" style="padding:10px 22px"><?= icon('rocket') ?> Create</button>
    </div>
  </form>
</div>

<script>
(function(){
  var roleSel = document.getElementById('nu-role');
  var label = document.getElementById('form-role-label');
  var sections = document.querySelectorAll('.form-section');
  var showFields = document.querySelectorAll('[data-show-for]');
  var roleNames = {
    regional:'Regional Admin',zonal:'Zonal Admin',woreda:'Woreda Admin',principal:'Principal / Director',
    teacher:'Teacher',student:'Student',parent:'Parent',registrar:'Registrar',dean:'Dean',
    vice_dean:'Vice Dean',hod:'Head of Department',lecturer:'Lecturer',bursar:'Bursar',
    student_affairs:'Student Affairs',librarian:'Librarian'
  };

  function updateForm(){
    var role = roleSel.value;
    label.textContent = role ? 'Creating: ' + (roleNames[role]||role) : 'Select a role';
    label.style.color = role ? 'var(--accent)' : 'var(--text-faint)';

    // Show/hide sections
    sections.forEach(function(s){
      var showFor = s.dataset.roles;
      if (showFor === 'all' || !showFor) { s.style.display = ''; return; }
      s.style.display = showFor.split(',').indexOf(role) >= 0 ? '' : 'none';
    });

    // Show/hide individual fields
    showFields.forEach(function(el){
      var showFor = el.dataset.showFor;
      if (!showFor) { el.style.display = ''; return; }
      el.style.display = showFor.split(',').indexOf(role) >= 0 ? '' : 'none';
    });
  }

  roleSel.addEventListener('change', updateForm);
  updateForm();
})();
</script>
