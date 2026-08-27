<?php /* Admin schools — page shell (static parts) */
$regions = ['Addis Ababa','Afar','Amhara','Benishangul-Gumuz','Dire Dawa','Gambela','Harari','Oromia','Sidama','SNNPR','Somali','Tigray','South West Ethiopia People\'s Region'];
$zones = Database::all("SELECT id, name FROM zones ORDER BY name");
$woredas = Database::all("SELECT id, name FROM woredas ORDER BY name");
?>
<div id="schools-root" class="list-root">
<?php include __DIR__ . '/schools_partial.php'; ?>
</div>

<!-- Detail drawer -->
<div class="drawer" id="item-drawer">
  <div class="drawer-head"><b>School details</b><button class="btn btn-sm btn-ghost" id="drawer-close">✕</button></div>
  <div id="drawer-body" class="drawer-body"></div>
</div>
<div class="drawer-backdrop" id="drawer-backdrop"></div>

<!-- Create school wizard modal -->
<div class="modal-dialog" id="new-school-modal">
  <form method="post" class="modal-box" id="school-wizard-form" style="max-height:92vh;overflow:auto;padding:0">
    <?= csrf_field() ?>
    <input type="hidden" name="create_school" value="1">
    <input type="hidden" name="wizard_step" id="wizard_step" value="1">

    <!-- Progress bar -->
    <div style="padding:18px 22px 0;border-bottom:1px solid var(--border)">
      <div class="flex-between" style="margin-bottom:10px">
        <h3 class="card-title" style="margin:0" id="wizard-title"><?= icon('plus') ?> Create School — Step 1 of 6</h3>
        <button type="button" class="btn btn-ghost btn-sm" data-close-modal="new-school-modal" style="font-size:18px;line-height:1">✕</button>
      </div>
      <div style="display:flex;gap:4px;margin-bottom:14px">
        <?php for ($s = 1; $s <= 6; $s++): ?>
          <div class="wizard-pip" data-step="<?= $s ?>" style="flex:1;height:4px;border-radius:2px;background:<?= $s === 1 ? 'var(--accent)' : 'var(--border)' ?>;transition:background .2s"></div>
        <?php endfor; ?>
      </div>
      <div class="flex gap-6" style="font-size:11px;color:var(--text-faint);margin-bottom:12px">
        <span id="step-label-1" style="font-weight:700;color:var(--accent)">Identity</span>
        <span>→</span><span id="step-label-2">Location</span>
        <span>→</span><span id="step-label-3">Contact</span>
        <span>→</span><span id="step-label-4">Administration</span>
        <span>→</span><span id="step-label-5">Academic</span>
        <span>→</span><span id="step-label-6">Modules & Review</span>
      </div>
    </div>

    <div style="padding:22px;max-height:60vh;overflow:auto">

    <!-- STEP 1: Identity -->
    <div class="wizard-step" data-step="1">
      <h4 style="margin:0 0 14px;font-size:13px;color:var(--text-faint)"><?= icon('building') ?> School Identity</h4>
      <div class="grid2">
        <div class="flex-col"><label class="small faint">School Name *</label><input class="input" name="name" required placeholder="e.g. Addis Ababa University"></div>
        <div class="flex-col"><label class="small faint">School Code * <span class="tiny faint">(auto-generated if left blank)</span></label><input class="input" name="code" maxlength="10" placeholder="Auto-generated" id="school-code-input"></div>
        <div class="flex-col"><label class="small faint">School Type *</label>
          <select class="input" name="school_type" required>
            <option value="public">Public</option><option value="private">Private</option><option value="government">Government</option><option value="ngo">NGO</option><option value="international">International</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Education Level *</label>
          <select class="input" name="education_level" required id="edu-level-select">
            <option value="kg">Kindergarten</option><option value="primary">Primary (Gr 1–8)</option>
            <option value="secondary">Secondary / Preparatory (Gr 9–12)</option>
            <option value="kg-12">KG–12 (Full)</option>
            <option value="university" selected>University</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Established Year</label><input class="input" name="established_year" type="number" min="1900" max="2099" placeholder="e.g. 1950"></div>
        <div class="flex-col"><label class="small faint">School Logo</label><input class="input" type="file" name="logo" accept="image/*"></div>
        <div class="flex-col" style="grid-column:1/-1"><label class="small faint">School Description</label><textarea class="input" name="school_description" rows="3" placeholder="Brief description of the school..."></textarea></div>
      </div>
    </div>

    <!-- STEP 2: Location -->
    <div class="wizard-step" data-step="2" style="display:none">
      <h4 style="margin:0 0 14px;font-size:13px;color:var(--text-faint)"><?= icon('map-pin') ?> Location</h4>
      <div class="grid2">
        <div class="flex-col"><label class="small faint">Region *</label>
          <select class="input" name="region" required>
            <option value="">— Select Region —</option>
            <?php foreach ($regions as $rg): ?><option value="<?= e($rg) ?>"><?= e($rg) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Zone</label>
          <select class="input" name="zone_id">
            <option value="">— None —</option>
            <?php foreach ($zones as $z): ?><option value="<?= (int)$z['id'] ?>"><?= e($z['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Woreda</label>
          <select class="input" name="woreda_id">
            <option value="">— None —</option>
            <?php foreach ($woredas as $w): ?><option value="<?= (int)$w['id'] ?>"><?= e($w['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Kebele</label><input class="input" name="kebele" placeholder="e.g. Kebele 01"></div>
        <div class="flex-col"><label class="small faint">City / Town</label><input class="input" name="city" placeholder="e.g. Bahir Dar"></div>
        <div class="flex-col"><label class="small faint">Street / Address</label><input class="input" name="street_address" placeholder="Street name and number"></div>
        <div class="flex-col"><label class="small faint">Full Address</label><input class="input" name="address" placeholder="Full postal address"></div>
        <div class="flex-col"><label class="small faint">GPS Latitude</label><input class="input" name="gps_lat" type="number" step="0.0000001" placeholder="e.g. 9.0249"></div>
        <div class="flex-col"><label class="small faint">GPS Longitude</label><input class="input" name="gps_lng" type="number" step="0.0000001" placeholder="e.g. 38.7468"></div>
      </div>
    </div>

    <!-- STEP 3: Contact -->
    <div class="wizard-step" data-step="3" style="display:none">
      <h4 style="margin:0 0 14px;font-size:13px;color:var(--text-faint)"><?= icon('phone') ?> Contact Information</h4>
      <div class="grid2">
        <div class="flex-col"><label class="small faint">Official Phone *</label><input class="input" name="phone" required placeholder="+251-11-xxx-xxxx"></div>
        <div class="flex-col"><label class="small faint">Alternative Phone</label><input class="input" name="alt_phone" placeholder="+251-91-xxx-xxxx"></div>
        <div class="flex-col"><label class="small faint">Official Email</label><input class="input" name="email" type="email" placeholder="info@school.edu.et"></div>
        <div class="flex-col"><label class="small faint">Website</label><input class="input" name="website" placeholder="https://school.edu.et"></div>
        <div class="flex-col"><label class="small faint">Emergency Contact</label><input class="input" name="emergency_contact" placeholder="Name and phone"></div>
        <div class="flex-col"><label class="small faint">Postal Address</label><input class="input" name="postal_address" placeholder="P.O. Box"></div>
      </div>
    </div>

    <!-- STEP 4: Administration -->
    <div class="wizard-step" data-step="4" style="display:none">
      <h4 style="margin:0 0 14px;font-size:13px;color:var(--text-faint)"><?= icon('user') ?> School Administration</h4>
      <p class="tiny faint" style="margin:0 0 14px">You can assign directors and administrators later from Users & Roles. Fill in known details now for a quick setup.</p>
      <div class="grid2">
        <div class="flex-col"><label class="small faint">Director / Principal Name</label><input class="input" name="director_name" placeholder="Full name"></div>
        <div class="flex-col"><label class="small faint">Director Phone</label><input class="input" name="director_phone" placeholder="+251-xx-xxx-xxxx"></div>
        <div class="flex-col"><label class="small faint">Director Email</label><input class="input" name="director_email" type="email" placeholder="director@school.edu.et"></div>
        <div class="flex-col"><label class="small faint">School Administrator</label><input class="input" name="admin_name" placeholder="Full name"></div>
        <div class="flex-col"><label class="small faint">Administrator Phone</label><input class="input" name="admin_phone" placeholder="+251-xx-xxx-xxxx"></div>
      </div>
    </div>

    <!-- STEP 5: Academic Setup -->
    <div class="wizard-step" data-step="5" style="display:none">
      <h4 style="margin:0 0 14px;font-size:13px;color:var(--text-faint)"><?= icon('books') ?> Academic Configuration</h4>
      <div class="grid2">
        <div class="flex-col"><label class="small faint">Academic Year *</label>
          <select class="input" name="academic_year" required>
            <option value="">— Select —</option>
            <option value="2018">2018 (E.C.)</option><option value="2019">2019 (E.C.)</option>
            <option value="2020">2020 (E.C.)</option><option value="2021">2021 (E.C.)</option>
            <option value="2022">2022 (E.C.)</option><option value="2023">2023 (E.C.)</option>
            <option value="2024" selected>2024 (E.C.)</option><option value="2025">2025 (E.C.)</option>
            <option value="2026">2026 (E.C.)</option><option value="2027">2027 (E.C.)</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Current Grade Levels</label><input class="input" name="grade_levels" placeholder="e.g. 1-8, 9-12, KG"></div>
        <div class="flex-col"><label class="small faint">Sections</label><input class="input" name="sections" placeholder="e.g. A, B, C"></div>
        <div class="flex-col"><label class="small faint">Max Student Capacity</label><input class="input" name="max_capacity" type="number" placeholder="e.g. 3000"></div>
        <div class="flex-col"><label class="small faint">Teaching Language *</label>
          <select class="input" name="teaching_language" required>
            <option value="Amharic" selected>Amharic</option><option value="English">English</option>
            <option value="Afaan Oromo">Afaan Oromo</option><option value="Tigrinya">Tigrinya</option>
            <option value="Somali">Somali</option><option value="Bilingual (Amharic+English)">Bilingual (Amharic+English)</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Second Language</label><input class="input" name="second_language" placeholder="e.g. English"></div>
        <div class="flex-col"><label class="small faint">Grading System</label>
          <select class="input" name="grading_system">
            <option value="percentage">Percentage (0–100%)</option><option value="gpa">GPA (4.0 scale)</option>
            <option value="letter">Letter Grades (A–F)</option><option value="ethiopian">Ethiopian (1–5)</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Attendance System</label>
          <select class="input" name="attendance_system">
            <option value="daily">Daily</option><option value="per-period">Per Period</option><option value="none">None</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">School Calendar</label>
          <select class="input" name="school_calendar">
            <option value="ethiopian">Ethiopian Calendar</option><option value="gregorian">Gregorian Calendar</option>
          </select>
        </div>
      </div>
    </div>

    <!-- STEP 6: Modules & Review -->
    <div class="wizard-step" data-step="6" style="display:none">
      <h4 style="margin:0 0 14px;font-size:13px;color:var(--text-faint)"><?= icon('box') ?> Enabled Modules</h4>
      <p class="tiny faint" style="margin:0 0 12px">Select the modules to enable for this school. You can change these later.</p>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px">
        <?php
        $allModules = [
          'student-management' => 'Student Management', 'teacher-management' => 'Teacher Management',
          'attendance' => 'Attendance', 'grades-exams' => 'Grades & Exams',
          'parent-portal' => 'Parent Portal', 'communication' => 'Communication',
          'ai-tutor' => 'AI Tutor', 'library' => 'Library',
          'fees' => 'Fees & Payments', 'reports' => 'Reports', 'transport' => 'Transport',
          'lms' => 'LMS / Online Courses', 'hr' => 'HR & Payroll', 'inventory' => 'Inventory',
        ];
        foreach ($allModules as $mk => $ml): ?>
          <label class="chk" style="display:flex;gap:8px;align-items:center;padding:8px 10px;border:1px solid var(--border);border-radius:8px;cursor:pointer;transition:border-color .15s">
            <input type="checkbox" name="modules[]" value="<?= $mk ?>" checked style="width:16px;height:16px;accent-color:var(--accent)">
            <span class="small"><?= $ml ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <h4 style="margin:0 0 10px;font-size:13px;color:var(--text-faint)"><?= icon('checklist') ?> Review School</h4>
      <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:10px;padding:16px;font-size:12px" id="review-box">
        <p class="faint">Review will appear here. Click "Back" to edit any step.</p>
      </div>
    </div>

    </div><!-- /scrollable body -->

    <!-- Navigation buttons -->
    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
      <button type="button" class="btn btn-ghost" id="wizard-prev" style="display:none" onclick="wizardNav(-1)"><?= icon('arrow-left') ?> Back</button>
      <div style="flex:1"></div>
      <div class="flex gap-10">
        <button type="button" class="btn btn-ghost" data-close-modal="new-school-modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="wizard-next" onclick="wizardNav(1)">Next <?= icon('arrow-right') ?></button>
        <button type="submit" class="btn btn-success" id="wizard-submit" style="display:none" name="create_school" value="1"><?= icon('rocket') ?> Submit for approval</button>
      </div>
    </div>
  </form>
</div>

<script>
(function(){
  const total = 6;
  let step = 1;
  const stepInput = document.getElementById('wizard_step');
  const prevBtn = document.getElementById('wizard-prev');
  const nextBtn = document.getElementById('wizard-next');
  const submitBtn = document.getElementById('wizard-submit');
  const title = document.getElementById('wizard-title');
  const labels = ['Identity','Location','Contact','Administration','Academic','Modules & Review'];
  const icons = ['<?= icon('building') ?>','<?= icon('map-pin') ?>','<?= icon('phone') ?>','<?= icon('user') ?>','<?= icon('books') ?>','<?= icon('box') ?>'];

  function show(s) {
    step = s;
    stepInput.value = s;
    document.querySelectorAll('.wizard-step').forEach(el => el.style.display = el.dataset.step == s ? '' : 'none');
    document.querySelectorAll('.wizard-pip').forEach(el => el.style.background = parseInt(el.dataset.step) <= s ? 'var(--accent)' : 'var(--border)');
    for (let i = 1; i <= 6; i++) {
      const lbl = document.getElementById('step-label-' + i);
      if (lbl) { lbl.style.fontWeight = i == s ? '700' : '400'; lbl.style.color = i == s ? 'var(--accent)' : 'var(--text-faint)'; }
    }
    title.innerHTML = icons[s-1] + ' Create School — Step ' + s + ' of 6';
    prevBtn.style.display = s > 1 ? '' : 'none';
    nextBtn.style.display = s < total ? '' : 'none';
    submitBtn.style.display = s === total ? '' : 'none';
    if (s === total) buildReview();
  }

  function buildReview() {
    const f = document.getElementById('school-wizard-form');
    const g = (n) => { const el = f.querySelector('[name="'+n+'"]'); return el ? (el.tagName==='SELECT'? el.options[el.selectedIndex]?.text : el.value) : '—'; };
    const gc = (n) => { const cbs = f.querySelectorAll('[name="'+n+'[]"]:checked'); return Array.from(cbs).map(c=>c.parentElement.textContent.trim()).join(', ') || 'None'; };
    const mods = gc('modules');
    document.getElementById('review-box').innerHTML = `
      <div style="display:grid;grid-template-columns:auto 1fr;gap:6px 14px;line-height:1.7">
        <b>School Name</b><span>${g('name') || '—'}</span>
        <b>School Code</b><span>${g('code') || 'Auto-generated'}</span>
        <b>School Type</b><span>${g('school_type')}</span>
        <b>Education Level</b><span>${g('education_level')}</span>
        <b>Region</b><span>${g('region') || '—'}</span>
        <b>City</b><span>${g('city') || '—'}</span>
        <b>Phone</b><span>${g('phone') || '—'}</span>
        <b>Email</b><span>${g('email') || '—'}</span>
        <b>Director</b><span>${g('director_name') || '—'}</span>
        <b>Academic Year</b><span>${g('academic_year') || '—'}</span>
        <b>Teaching Language</b><span>${g('teaching_language')}</span>
        <b>Calendar</b><span>${g('school_calendar')}</span>
        <b>Modules</b><span>${mods}</span>
      </div>`;
  }

  window.wizardNav = function(dir) { if (step + dir >= 1 && step + dir <= total) show(step + dir); };

  // On modal open, reset to step 1
  const observer = new MutationObserver(() => {
    const modal = document.getElementById('new-school-modal');
    if (modal && modal.classList.contains('open')) { show(1); }
  });
  const modal = document.getElementById('new-school-modal');
  if (modal) observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
})();
</script>
