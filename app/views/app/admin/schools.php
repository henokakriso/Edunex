<?php /* Admin schools — page shell (static parts) */
$regions = array_column(Database::all("SELECT name FROM regions WHERE status = 'active' ORDER BY name"), 'name');
$zones = Database::all("SELECT id, name FROM zones ORDER BY name");
$woredas = Database::all("SELECT id, name FROM woredas ORDER BY name");
$__role = $__u['role'] ?? '';
$isMinistry = $__role === 'ministry';
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
  <form method="post" class="modal-box" id="school-wizard-form" style="max-height:95vh;max-width:860px;width:95vw;overflow:hidden;padding:0;border-radius:16px">
    <?= csrf_field() ?>
    <input type="hidden" name="create_school" value="1">
    <input type="hidden" name="wizard_step" id="wizard_step" value="1">

    <!-- Header + Progress -->
    <div style="padding:20px 28px 0;border-bottom:1px solid var(--border)">
      <div class="flex-between" style="margin-bottom:12px">
        <h3 class="card-title" style="margin:0;font-size:16px" id="wizard-title"><?= icon('plus') ?> Create School — Step 1 of 6</h3>
        <button type="button" class="btn btn-ghost btn-sm" data-close-modal="new-school-modal" style="font-size:20px;line-height:1;padding:4px 8px">✕</button>
      </div>
      <div style="display:flex;gap:5px;margin-bottom:14px">
        <?php for ($s = 1; $s <= 6; $s++): ?>
          <div class="wizard-pip" data-step="<?= $s ?>" style="flex:1;height:5px;border-radius:3px;background:<?= $s === 1 ? 'var(--accent)' : 'var(--border)' ?>;transition:background .2s"></div>
        <?php endfor; ?>
      </div>
      <div class="flex gap-8" style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:14px;flex-wrap:wrap;align-items:center">
        <span id="step-label-1" style="font-weight:700;color:var(--accent)">1. Identity</span>
        <span style="opacity:.4;font-size:13px">→</span><span id="step-label-2">2. Location</span>
        <span style="opacity:.4;font-size:13px">→</span><span id="step-label-3">3. Contact</span>
        <span style="opacity:.4;font-size:13px">→</span><span id="step-label-4">4. Administration</span>
        <span style="opacity:.4;font-size:13px">→</span><span id="step-label-5">5. Academic</span>
        <span style="opacity:.4;font-size:13px">→</span><span id="step-label-6">6. Modules & Review</span>
      </div>
    </div>

    <!-- Scrollable body -->
    <div style="padding:24px 28px;max-height:62vh;overflow-y:auto">

    <!-- STEP 1: Identity -->
    <div class="wizard-step" data-step="1">
      <h4 style="margin:0 0 16px;font-size:14px;color:var(--text)"><?= icon('building') ?> School Identity</h4>
      <div class="grid2" style="gap:16px">
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">School Name *</label><input class="input" name="name" required placeholder="e.g. Addis Ababa University" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">School Code * <span class="tiny faint">(auto-generated if blank)</span></label><input class="input" name="code" maxlength="10" placeholder="Auto-generated" id="school-code-input" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">School Type *</label>
          <select class="input" name="school_type" required style="padding:10px 14px">
            <option value="public">Public</option><option value="private">Private</option><option value="government">Government</option><option value="ngo">NGO</option><option value="international">International</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Education Level *</label>
          <select class="input" name="education_level" required id="edu-level-select" style="padding:10px 14px">
            <option value="kg">Kindergarten</option><option value="primary">Primary (Gr 1–8)</option>
            <option value="secondary">Secondary / Preparatory (Gr 9–12)</option>
            <option value="kg-12">KG–12 (Full)</option>
            <option value="university" selected>University</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Established Year</label><input class="input" name="established_year" type="number" min="1900" max="2099" placeholder="e.g. 1950" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">School Logo</label><input class="input" type="file" name="logo" accept="image/*" style="padding:8px 14px"></div>
        <div class="flex-col" style="grid-column:1/-1"><label class="small faint" style="margin-bottom:5px">School Description</label><textarea class="input" name="school_description" rows="3" placeholder="Brief description of the school..." style="padding:10px 14px;resize:vertical"></textarea></div>
      </div>
    </div>

    <!-- STEP 2: Location -->
    <div class="wizard-step" data-step="2" style="display:none">
      <h4 style="margin:0 0 16px;font-size:14px;color:var(--text)"><?= icon('map-pin') ?> Location</h4>
      <div class="grid2" style="gap:16px">
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Region *</label>
          <select class="input" name="region" id="sw-region" required style="padding:10px 14px">
            <option value="">— Select Region —</option>
            <?php foreach ($regions as $rg): ?><option value="<?= e($rg) ?>"><?= e($rg) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Zone</label>
          <select class="input" name="zone_id" id="sw-zone" style="padding:10px 14px">
            <option value="">— Select Region first —</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Woreda</label>
          <select class="input" name="woreda_id" id="sw-woreda" style="padding:10px 14px">
            <option value="">— Select Zone first —</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Kebele</label><input class="input" name="kebele" placeholder="e.g. Kebele 01" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">City / Town</label><input class="input" name="city" placeholder="e.g. Bahir Dar" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Street / Address</label><input class="input" name="street_address" placeholder="Street name and number" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Full Address</label><input class="input" name="address" placeholder="Full postal address" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">GPS Latitude</label><input class="input" name="gps_lat" type="number" step="0.0000001" placeholder="e.g. 9.0249" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">GPS Longitude</label><input class="input" name="gps_lng" type="number" step="0.0000001" placeholder="e.g. 38.7468" style="padding:10px 14px"></div>
      </div>
    </div>

    <!-- STEP 3: Contact -->
    <div class="wizard-step" data-step="3" style="display:none">
      <h4 style="margin:0 0 16px;font-size:14px;color:var(--text)"><?= icon('phone') ?> Contact Information</h4>
      <div class="grid2" style="gap:16px">
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Official Phone *</label><input class="input" name="phone" required placeholder="+251-11-xxx-xxxx" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Alternative Phone</label><input class="input" name="alt_phone" placeholder="+251-91-xxx-xxxx" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Official Email</label><input class="input" name="email" type="email" placeholder="info@school.edu.et" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Website</label><input class="input" name="website" placeholder="https://school.edu.et" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Emergency Contact</label><input class="input" name="emergency_contact" placeholder="Name and phone" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Postal Address</label><input class="input" name="postal_address" placeholder="P.O. Box" style="padding:10px 14px"></div>
      </div>
    </div>

    <!-- STEP 4: Administration -->
    <div class="wizard-step" data-step="4" style="display:none">
      <h4 style="margin:0 0 16px;font-size:14px;color:var(--text)"><?= icon('user') ?> School Administration</h4>
      <p class="tiny faint" style="margin:0 0 16px;padding:10px 14px;background:var(--bg-soft);border-radius:8px">You can assign directors and administrators later from Users & Roles. Fill in known details now for a quick setup.</p>
      <div class="grid2" style="gap:16px">
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Director / Principal Name</label><input class="input" name="director_name" placeholder="Full name" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Director Phone</label><input class="input" name="director_phone" placeholder="+251-xx-xxx-xxxx" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Director Email</label><input class="input" name="director_email" type="email" placeholder="director@school.edu.et" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">School Administrator</label><input class="input" name="admin_name" placeholder="Full name" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Administrator Phone</label><input class="input" name="admin_phone" placeholder="+251-xx-xxx-xxxx" style="padding:10px 14px"></div>
      </div>
    </div>

    <!-- STEP 5: Academic Setup -->
    <div class="wizard-step" data-step="5" style="display:none">
      <h4 style="margin:0 0 16px;font-size:14px;color:var(--text)"><?= icon('books') ?> Academic Configuration</h4>
      <div class="grid2" style="gap:16px">
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Academic Year *</label>
          <select class="input" name="academic_year" required style="padding:10px 14px">
            <option value="">— Select —</option>
            <option value="2018">2018 (E.C.)</option><option value="2019">2019 (E.C.)</option>
            <option value="2020">2020 (E.C.)</option><option value="2021">2021 (E.C.)</option>
            <option value="2022">2022 (E.C.)</option><option value="2023">2023 (E.C.)</option>
            <option value="2024" selected>2024 (E.C.)</option><option value="2025">2025 (E.C.)</option>
            <option value="2026">2026 (E.C.)</option><option value="2027">2027 (E.C.)</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Current Grade Levels</label><input class="input" name="grade_levels" placeholder="e.g. 1-8, 9-12, KG" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Sections</label><input class="input" name="sections" placeholder="e.g. A, B, C" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Max Student Capacity</label><input class="input" name="max_capacity" type="number" placeholder="e.g. 3000" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Teaching Language *</label>
          <select class="input" name="teaching_language" required style="padding:10px 14px">
            <option value="Amharic" selected>Amharic</option><option value="English">English</option>
            <option value="Afaan Oromo">Afaan Oromo</option><option value="Tigrinya">Tigrinya</option>
            <option value="Somali">Somali</option><option value="Bilingual (Amharic+English)">Bilingual (Amharic+English)</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Second Language</label><input class="input" name="second_language" placeholder="e.g. English" style="padding:10px 14px"></div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Grading System</label>
          <select class="input" name="grading_system" style="padding:10px 14px">
            <option value="percentage">Percentage (0–100%)</option><option value="gpa">GPA (4.0 scale)</option>
            <option value="letter">Letter Grades (A–F)</option><option value="ethiopian">Ethiopian (1–5)</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">Attendance System</label>
          <select class="input" name="attendance_system" style="padding:10px 14px">
            <option value="daily">Daily</option><option value="per-period">Per Period</option><option value="none">None</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint" style="margin-bottom:5px">School Calendar</label>
          <select class="input" name="school_calendar" style="padding:10px 14px">
            <option value="ethiopian">Ethiopian Calendar</option><option value="gregorian">Gregorian Calendar</option>
          </select>
        </div>
      </div>
    </div>

    <!-- STEP 6: Modules & Review -->
    <div class="wizard-step" data-step="6" style="display:none">
      <h4 style="margin:0 0 14px;font-size:14px;color:var(--text)"><?= icon('box') ?> Enabled Modules</h4>
      <p class="tiny faint" style="margin:0 0 14px">Select the modules to enable for this school. You can change these later.</p>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:24px">
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
          <label class="chk" style="display:flex;gap:10px;align-items:center;padding:10px 14px;border:1px solid var(--border);border-radius:10px;cursor:pointer;transition:border-color .15s,background .15s">
            <input type="checkbox" name="modules[]" value="<?= $mk ?>" checked style="width:17px;height:17px;accent-color:var(--accent)">
            <span class="small"><?= $ml ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <h4 style="margin:0 0 12px;font-size:14px;color:var(--text)"><?= icon('checklist') ?> Review School</h4>
      <div style="background:var(--bg-soft);border:1px solid var(--border);border-radius:12px;padding:20px;font-size:12.5px;line-height:1.8" id="review-box">
        <p class="faint">Review will appear here. Click "Back" to edit any step.</p>
      </div>
    </div>

    </div><!-- /scrollable body -->

    <!-- Validation error banner -->
    <div id="wizard-error" style="display:none;padding:10px 28px;background:#fef2f2;border-top:1px solid #fecaca;color:#dc2626;font-size:12.5px;font-weight:600">
      Please fill all required fields (*) marked in red before continuing.
    </div>

    <!-- Navigation buttons -->
    <div style="padding:16px 28px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:var(--bg-soft);border-radius:0 0 16px 16px">
      <button type="button" class="btn btn-ghost" id="wizard-prev" style="display:none" onclick="wizardNav(-1)"><?= icon('arrow-left') ?> Back</button>
      <div style="flex:1"></div>
      <div class="flex gap-10">
        <button type="button" class="btn btn-ghost" data-close-modal="new-school-modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="wizard-next" onclick="wizardNav(1)" style="padding:10px 22px">Next <?= icon('arrow-right') ?></button>
        <button type="submit" class="btn btn-success" id="wizard-submit" style="display:none;padding:10px 22px"><?= icon('rocket') ?> <?= $isMinistry ? 'Create School' : 'Submit for Approval' ?></button>
      </div>
    </div>
  </form>
</div>

<style>
.wizard-step .input.error,
.wizard-step select.error {
  border-color: #dc2626 !important;
  box-shadow: 0 0 0 2px rgba(220,38,38,.15) !important;
  background: #fef2f2 !important;
}
.wizard-step .input.error::placeholder { color: #fca5a5; }
.wizard-step label.error-label { color: #dc2626 !important; font-weight: 600; }
</style>

<script>
(function(){
  const total = 6;
  let step = 1;
  const stepInput = document.getElementById('wizard_step');
  const prevBtn = document.getElementById('wizard-prev');
  const nextBtn = document.getElementById('wizard-next');
  const submitBtn = document.getElementById('wizard-submit');
  const title = document.getElementById('wizard-title');
  const errBar = document.getElementById('wizard-error');
  const icons = ['<?= icon('building') ?>','<?= icon('map-pin') ?>','<?= icon('phone') ?>','<?= icon('user') ?>','<?= icon('books') ?>','<?= icon('box') ?>'];

  // Required fields per step (name attributes)
  const requiredByStep = {
    1: ['name'],
    2: ['region'],
    3: ['phone'],
    4: [],
    5: ['academic_year', 'teaching_language'],
    6: []
  };

  function validateStep(s) {
    const fields = requiredByStep[s] || [];
    let valid = true;
    // Clear previous errors
    document.querySelectorAll('.wizard-step[data-step="'+s+'"] .input, .wizard-step[data-step="'+s+'"] select').forEach(el => {
      el.classList.remove('error');
    });
    document.querySelectorAll('.wizard-step[data-step="'+s+'"] .error-label').forEach(el => el.classList.remove('error-label'));

    fields.forEach(name => {
      const el = document.querySelector('.wizard-step[data-step="'+s+'"] [name="'+name+'"]');
      if (!el) return;
      const val = el.value.trim();
      if (!val || (el.tagName === 'SELECT' && val === '')) {
        valid = false;
        el.classList.add('error');
        // Highlight label
        const lbl = el.closest('.flex-col')?.querySelector('label');
        if (lbl) lbl.classList.add('error-label');
      }
    });
    return valid;
  }

  function clearErrors() {
    errBar.style.display = 'none';
    document.querySelectorAll('.wizard-step .error').forEach(el => el.classList.remove('error'));
    document.querySelectorAll('.wizard-step .error-label').forEach(el => el.classList.remove('error-label'));
  }

  function show(s) {
    step = s;
    stepInput.value = s;
    clearErrors();
    document.querySelectorAll('.wizard-step').forEach(el => el.style.display = el.dataset.step == s ? '' : 'none');
    document.querySelectorAll('.wizard-pip').forEach(el => el.style.background = parseInt(el.dataset.step) <= s ? 'var(--accent)' : 'var(--border)');
    for (let i = 1; i <= 6; i++) {
      const lbl = document.getElementById('step-label-' + i);
      if (lbl) { lbl.style.fontWeight = i == s ? '700' : '600'; lbl.style.color = i == s ? 'var(--accent)' : 'var(--text)'; }
    }
    title.innerHTML = icons[s-1] + ' Create School — Step ' + s + ' of 6';
    prevBtn.style.display = s > 1 ? '' : 'none';
    nextBtn.style.display = s < total ? '' : 'none';
    submitBtn.style.display = s === total ? '' : 'none';
    if (s === total) buildReview();
    // Scroll form body to top
    const body = document.querySelector('#school-wizard-form [style*="overflow-y"]');
    if (body) body.scrollTop = 0;
  }

  function buildReview() {
    const f = document.getElementById('school-wizard-form');
    const g = (n) => { const el = f.querySelector('[name="'+n+'"]'); return el ? (el.tagName==='SELECT'? el.options[el.selectedIndex]?.text : el.value) : '—'; };
    const gc = (n) => { const cbs = f.querySelectorAll('[name="'+n+'[]"]:checked'); return Array.from(cbs).map(c=>c.parentElement.textContent.trim()).join(', ') || 'None'; };
    document.getElementById('review-box').innerHTML = `
      <div style="display:grid;grid-template-columns:160px 1fr;gap:8px 18px;line-height:1.9">
        <b>School Name</b><span>${g('name') || '<span style="color:#dc2626">— missing</span>'}</span>
        <b>School Code</b><span>${g('code') || '<span style="color:var(--text-faint)">Auto-generated</span>'}</span>
        <b>School Type</b><span>${g('school_type')}</span>
        <b>Education Level</b><span>${g('education_level')}</span>
        <b>Region</b><span>${g('region') || '<span style="color:#dc2626">— missing</span>'}</span>
        <b>City</b><span>${g('city') || '—'}</span>
        <b>Phone</b><span>${g('phone') || '<span style="color:#dc2626">— missing</span>'}</span>
        <b>Email</b><span>${g('email') || '—'}</span>
        <b>Director</b><span>${g('director_name') || '—'}</span>
        <b>Academic Year</b><span>${g('academic_year') || '—'}</span>
        <b>Teaching Language</b><span>${g('teaching_language')}</span>
        <b>Calendar</b><span>${g('school_calendar')}</span>
        <b>Modules</b><span>${gc('modules')}</span>
      </div>`;
  }

  window.wizardNav = function(dir) {
    const next = step + dir;
    if (next < 1 || next > total) return;
    // Validate current step before going forward
    if (dir > 0 && !validateStep(step)) {
      errBar.style.display = 'block';
      return;
    }
    errBar.style.display = 'none';
    show(next);
  };

  // On modal open, reset to step 1
  const observer = new MutationObserver(() => {
    const modal = document.getElementById('new-school-modal');
    if (modal && modal.classList.contains('open')) { show(1); }
  });
  const modal = document.getElementById('new-school-modal');
  if (modal) observer.observe(modal, { attributes: true, attributeFilter: ['class'] });

  // --- Cascading Region → Zone → Woreda for school wizard ---
  var swRegion = document.getElementById('sw-region');
  var swZone = document.getElementById('sw-zone');
  var swWoreda = document.getElementById('sw-woreda');
  if (swRegion && swZone && swWoreda) {
    swRegion.addEventListener('change', function(){
      var regionName = this.value;
      swZone.innerHTML = '<option value="">Loading...</option>';
      swWoreda.innerHTML = '<option value="">— Select Zone first —</option>';
      if (!regionName) { swZone.innerHTML = '<option value="">— Select Region first —</option>'; return; }
      // Find region_id by name from the DB via API (use name lookup)
      fetch('api/geo?action=zones&region_name=' + encodeURIComponent(regionName))
        .then(function(r){ return r.json(); })
        .then(function(d){
          swZone.innerHTML = '<option value="">— Select Zone —</option>';
          (d.zones||[]).forEach(function(z){
            swZone.innerHTML += '<option value="' + z.id + '">' + z.name + '</option>';
          });
        })
        .catch(function(){ swZone.innerHTML = '<option value="">— Error loading zones —</option>'; });
    });
    swZone.addEventListener('change', function(){
      var zid = this.value;
      swWoreda.innerHTML = '<option value="">Loading...</option>';
      if (!zid) { swWoreda.innerHTML = '<option value="">— Select Zone first —</option>'; return; }
      fetch('api/geo?action=woredas&zone_id=' + encodeURIComponent(zid))
        .then(function(r){ return r.json(); })
        .then(function(d){
          swWoreda.innerHTML = '<option value="">— Select Woreda —</option>';
          (d.woredas||[]).forEach(function(w){
            swWoreda.innerHTML += '<option value="' + w.id + '">' + w.name + '</option>';
          });
        })
        .catch(function(){ swWoreda.innerHTML = '<option value="">— Error loading woredas —</option>'; });
    });
  }
})();
</script>
