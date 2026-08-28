<?php /* Admin academic years — shared calendars for school/university levels */
$sc = ['draft'=>'badge-muted','active'=>'badge-success','closed'=>'badge-warning','archived'=>'badge-muted'];
$roman = ['','I','II','III','IV','V','VI'];
$eduLabels = ['school'=>'Schools (K-12)','university'=>'Universities'];
?>
<style>
.yr-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:box-shadow .2s}
.yr-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.06)}
.yr-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:start;gap:16px;flex-wrap:wrap}
.yr-title{font-size:16px;font-weight:700;letter-spacing:-.01em}
.yr-meta{font-size:13px;color:var(--text-secondary);margin-top:4px;line-height:1.5}
.yr-section{padding:16px 24px}
.yr-section+.yr-section{border-top:1px solid var(--border)}
.yr-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-faint);margin-bottom:10px}
.sem-row{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:var(--bg-hover);margin-bottom:6px;transition:background .15s}
.sem-row:hover{background:var(--border)}
.sem-name{font-size:14px;font-weight:600;min-width:140px}
.sem-dates{font-size:13px;color:var(--text-secondary);flex:1}
.sem-actions{display:flex;gap:4px;opacity:.5;transition:opacity .15s}
.sem-row:hover .sem-actions{opacity:1}
.add-sem{display:flex;gap:8px;align-items:end;margin-top:10px;flex-wrap:wrap}
.yr-actions{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
.yr-wizard{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:20px}
.yr-wiz-section{padding:20px 24px;border-bottom:1px solid var(--border)}
.yr-wiz-title{font-size:14px;font-weight:700;margin:0 0 14px;color:var(--text)}
.yr-wiz-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}
.yr-wiz-grid .flex-col label{font-size:12px;font-weight:600;margin-bottom:4px}
.empty-state{text-align:center;color:var(--muted);padding:48px 24px;font-size:14px}
.shared-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(99,102,241,.12);color:#6366f1}
.section-title{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-faint);margin-bottom:14px;padding-bottom:8px;border-bottom:2px solid var(--border)}
</style>

<div class="page-head" style="margin-bottom:20px">
  <div>
    <h1 style="font-size:22px;font-weight:700;letter-spacing:-.02em"><?= icon('calendar') ?> Academic Years &amp; Semesters</h1>
    <p style="font-size:13px;color:var(--text-secondary);margin-top:2px">Create shared calendars per education level — applies to all matching schools automatically</p>
  </div>
  <button class="btn btn-primary" onclick="toggleYearForm()" id="btn-new-year">+ New Year</button>
</div>

<!-- Create Year Form -->
<div id="year-form" style="display:none;margin-bottom:20px">
  <form method="post" class="yr-wizard">
    <?= csrf_field() ?>
    <input type="hidden" name="create_year" value="1">

    <div class="yr-wiz-section">
      <div class="yr-wiz-title">Calendar Type</div>
      <div style="display:flex;gap:16px;flex-wrap:wrap">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:12px 16px;border-radius:10px;border:2px solid var(--border);transition:border-color .15s" id="lbl-shared" onclick="setShared(true)">
          <input type="checkbox" name="is_shared" id="is-shared" checked onchange="setShared(this.checked)">
          <div><b style="font-size:13px">Shared Calendar</b><br><span style="font-size:12px;color:var(--text-secondary)">Applies to all schools/universities of the same level</span></div>
        </label>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:12px 16px;border-radius:10px;border:2px solid var(--border);transition:border-color .15s" id="lbl-individual" onclick="setShared(false)">
          <input type="radio" name="is_shared" value="0" id="is-individual" onchange="setShared(false)">
          <div><b style="font-size:13px">Individual School</b><br><span style="font-size:12px;color:var(--text-secondary)">Create for one specific school only</span></div>
        </label>
      </div>
    </div>

    <div class="yr-wiz-section">
      <div class="yr-wiz-title">Basic Information</div>
      <div class="yr-wiz-grid">
        <div class="flex-col"><label>Education Level *</label>
          <select class="input" name="education_level" required><option value="school">Schools (K-12)</option><option value="university">Universities</option></select>
        </div>
        <div class="flex-col" id="school-select-wrap" style="display:none"><label>School *</label>
          <select class="input" name="school_id"><option value="0">— Select —</option><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="flex-col"><label>Year Name *</label><input class="input" name="name" required placeholder="2026/27"></div>
        <div class="flex-col"><label>Ethiopian Year</label><input class="input" name="ethiopian_year" placeholder="2019 E.C."></div>
        <div class="flex-col"><label>Status</label>
          <select class="input" name="status"><option value="active">Active</option><option value="draft">Draft</option><option value="closed">Closed</option><option value="archived">Archived</option></select>
        </div>
      </div>
    </div>

    <div class="yr-wiz-section">
      <div class="yr-wiz-title">Calendar Dates</div>
      <div class="yr-wiz-grid">
        <div class="flex-col"><label>Gregorian Start *</label><input class="input" type="date" name="start_date" required></div>
        <div class="flex-col"><label>Gregorian End *</label><input class="input" type="date" name="end_date" required></div>
        <div class="flex-col"><label>Ethiopian Start</label><input class="input" name="ethiopian_start" placeholder="Meskerem 1, 2019 E.C."></div>
        <div class="flex-col"><label>Ethiopian End</label><input class="input" name="ethiopian_end" placeholder="Pagume 6, 2019 E.C."></div>
        <div class="flex-col"><label>Primary Calendar</label>
          <select class="input" name="primary_calendar"><option value="ethiopian">Ethiopian</option><option value="gregorian">Gregorian</option><option value="both">Both</option></select>
        </div>
      </div>
    </div>

    <div class="yr-wiz-section">
      <div class="yr-wiz-title">Academic Structure</div>
      <div class="yr-wiz-grid">
        <div class="flex-col"><label>Semesters *</label>
          <select class="input" name="num_semesters" id="num-sem" onchange="updateSemFields()"><option value="2">2</option><option value="3">3</option><option value="4">4</option></select>
        </div>
        <div class="flex-col"><label>Working Days/Week</label><input class="input" type="number" name="working_days_per_week" value="5" min="1" max="7"></div>
        <div class="flex-col"><label>Weekend Days</label><input class="input" name="weekend_days" value="fri,sat"></div>
        <div class="flex-col"><label>Days Target</label><input class="input" type="number" name="school_days_target" placeholder="180"></div>
      </div>
      <div id="sem-fields" style="margin-top:14px"></div>
    </div>

    <div style="padding:16px 24px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border)">
      <button type="button" class="btn" onclick="toggleYearForm()">Cancel</button>
      <button class="btn btn-success"><?= icon('plus') ?> Create Calendar</button>
    </div>
  </form>
</div>

<!-- Shared Calendars -->
<?php if ($sharedYears): ?>
<div style="margin-bottom:28px">
  <div class="section-title">Shared Calendars (Education Level)</div>
  <div style="display:flex;flex-direction:column;gap:14px">
    <?php foreach ($sharedYears as $y): ?>
      <div class="yr-card">
        <div class="yr-header">
          <div style="flex:1;min-width:200px">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
              <span class="yr-title"><?= e($y['name']) ?></span>
              <span class="shared-badge">&#128279; Shared</span>
              <span class="badge badge-info" style="font-size:11px"><?= e($eduLabels[$y['education_level']] ?? $y['education_level']) ?></span>
              <?php if ($y['is_current']): ?><span class="badge badge-success" style="font-size:11px;font-weight:700">&#10003; Current</span><?php endif; ?>
              <span class="badge <?= $sc[$y['status']] ?? 'badge-muted' ?>" style="font-size:11px"><?= e(ucfirst($y['status'])) ?></span>
            </div>
            <?php if ($y['ethiopian_year']): ?><div style="font-size:12px;color:var(--text-faint);margin-top:4px"><?= e($y['ethiopian_year']) ?></div><?php endif; ?>
            <div class="yr-meta">
              <?= $y['start_date'] ? e(date('M j, Y', strtotime($y['start_date']))) . ' → ' . e(date('M j, Y', strtotime($y['end_date']))) : 'Dates not set' ?>
              · <?= count($y['semesters']) ?> semester<?= count($y['semesters']) === 1 ? '' : 's' ?>
              · Applied to <?= (int)$y['applied_count'] ?> school<?= (int)$y['applied_count'] === 1 ? '' : 's' ?>
            </div>
          </div>
          <div class="yr-actions">
            <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm <?= $y['is_current'] ? 'btn-ghost' : 'btn-success' ?>" name="set_current" value="<?= (int)$y['id'] ?>" style="font-size:12px"><?= $y['is_current'] ? '&#10003; Current' : 'Set current' ?></button></form>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="apply_shared" value="<?= (int)$y['id'] ?>"><button class="btn btn-sm btn-primary" style="font-size:12px"><?= icon('refresh') ?> Apply to <?= e($eduLabels[$y['education_level']] ?? '') ?></button></form>
            <form method="post" class="inline" data-confirm="Delete <?= e($y['name']) ?>?"><?= csrf_field() ?><input type="hidden" name="delete_year" value="<?= (int)$y['id'] ?>"><button class="icon-btn danger" title="Delete" style="font-size:14px"><?= icon('trash') ?></button></form>
          </div>
        </div>
        <div class="yr-section">
          <div class="yr-label">Semesters</div>
          <?php foreach ($y['semesters'] as $i => $sem): ?>
            <div class="sem-row">
              <span style="font-size:12px;font-weight:700;color:var(--text-faint);width:20px"><?= $roman[$i+1] ?? ($i+1) ?></span>
              <span class="sem-name"><?= e($sem['name']) ?></span>
              <span class="sem-dates">
                <?= $sem['start_date'] ? date('M j', strtotime($sem['start_date'])) . ' → ' . date('M j, Y', strtotime($sem['end_date'])) : '<span class="faint">Dates not set</span>' ?>
              </span>
              <div class="sem-actions">
                <button class="icon-btn" title="Edit" onclick="editSem(<?= (int)$sem['id'] ?>,'<?= e(addslashes($sem['name'])) ?>','<?= e($sem['start_date'] ?? '') ?>','<?= e($sem['end_date'] ?? '') ?>')" style="font-size:13px"><?= icon('edit') ?></button>
                <form method="post" class="inline" data-confirm="Delete <?= e($sem['name']) ?>?"><?= csrf_field() ?><input type="hidden" name="delete_semester" value="<?= (int)$sem['id'] ?>"><button class="icon-btn danger" title="Delete" style="font-size:13px"><?= icon('trash') ?></button></form>
              </div>
            </div>
          <?php endforeach; ?>
          <form method="post" class="add-sem">
            <?= csrf_field() ?>
            <input type="hidden" name="year_id" value="<?= (int)$y['id'] ?>">
            <input class="input" name="name" placeholder="Semester name" style="width:200px">
            <input class="input" type="date" name="start_date" style="width:150px">
            <input class="input" type="date" name="end_date" style="width:150px">
            <button class="btn btn-sm" name="create_semester" value="1" style="font-size:12px"><?= icon('plus') ?> Add</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Individual Calendars -->
<?php if ($individualYears): ?>
<div>
  <div class="section-title">Individual School Calendars</div>
  <div style="display:flex;flex-direction:column;gap:14px">
    <?php foreach ($individualYears as $y): ?>
      <div class="yr-card">
        <div class="yr-header">
          <div style="flex:1;min-width:200px">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
              <span class="yr-title"><?= e($y['name']) ?></span>
              <span class="badge badge-muted" style="font-size:11px"><?= e($y['school_name']) ?></span>
              <?php if ($y['is_current']): ?><span class="badge badge-success" style="font-size:11px;font-weight:700">&#10003; Current</span><?php endif; ?>
              <span class="badge <?= $sc[$y['status']] ?? 'badge-muted' ?>" style="font-size:11px"><?= e(ucfirst($y['status'])) ?></span>
            </div>
            <div class="yr-meta">
              <?= $y['start_date'] ? e(date('M j, Y', strtotime($y['start_date']))) . ' → ' . e(date('M j, Y', strtotime($y['end_date']))) : 'Dates not set' ?>
              · <?= count($y['semesters']) ?> semester<?= count($y['semesters']) === 1 ? '' : 's' ?>
            </div>
          </div>
          <div class="yr-actions">
            <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm <?= $y['is_current'] ? 'btn-ghost' : 'btn-success' ?>" name="set_current" value="<?= (int)$y['id'] ?>" style="font-size:12px"><?= $y['is_current'] ? '&#10003; Current' : 'Set current' ?></button></form>
            <form method="post" class="inline">
              <?= csrf_field() ?><input type="hidden" name="set_status" value="<?= (int)$y['id'] ?>">
              <select class="input" name="status" onchange="this.form.submit()" style="padding:6px 10px;font-size:12px;width:auto">
                <?php foreach (['draft','active','closed','archived'] as $st): ?><option value="<?= $st ?>" <?= $y['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option><?php endforeach; ?>
              </select>
            </form>
            <button class="icon-btn" title="Edit" onclick='editYear(<?= json_encode($y) ?>)' style="font-size:14px"><?= icon('edit') ?></button>
            <form method="post" class="inline" data-confirm="Delete <?= e($y['name']) ?>?"><?= csrf_field() ?><input type="hidden" name="delete_year" value="<?= (int)$y['id'] ?>"><button class="icon-btn danger" title="Delete" style="font-size:14px"><?= icon('trash') ?></button></form>
          </div>
        </div>
        <div class="yr-section">
          <div class="yr-label">Semesters</div>
          <?php foreach ($y['semesters'] as $i => $sem): ?>
            <div class="sem-row">
              <span style="font-size:12px;font-weight:700;color:var(--text-faint);width:20px"><?= $roman[$i+1] ?? ($i+1) ?></span>
              <span class="sem-name"><?= e($sem['name']) ?></span>
              <span class="sem-dates">
                <?= $sem['start_date'] ? date('M j', strtotime($sem['start_date'])) . ' → ' . date('M j, Y', strtotime($sem['end_date'])) : '<span class="faint">Dates not set</span>' ?>
              </span>
              <div class="sem-actions">
                <button class="icon-btn" title="Edit" onclick="editSem(<?= (int)$sem['id'] ?>,'<?= e(addslashes($sem['name'])) ?>','<?= e($sem['start_date'] ?? '') ?>','<?= e($sem['end_date'] ?? '') ?>')" style="font-size:13px"><?= icon('edit') ?></button>
                <form method="post" class="inline" data-confirm="Delete <?= e($sem['name']) ?>?"><?= csrf_field() ?><input type="hidden" name="delete_semester" value="<?= (int)$sem['id'] ?>"><button class="icon-btn danger" title="Delete" style="font-size:13px"><?= icon('trash') ?></button></form>
              </div>
            </div>
          <?php endforeach; ?>
          <form method="post" class="add-sem">
            <?= csrf_field() ?>
            <input type="hidden" name="year_id" value="<?= (int)$y['id'] ?>">
            <input class="input" name="name" placeholder="Semester name" style="width:200px">
            <input class="input" type="date" name="start_date" style="width:150px">
            <input class="input" type="date" name="end_date" style="width:150px">
            <button class="btn btn-sm" name="create_semester" value="1" style="font-size:12px"><?= icon('plus') ?> Add</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!$sharedYears && !$individualYears): ?>
  <div class="yr-card"><div class="empty-state">No academic years yet.<br>Click <b>+ New Year</b> to create a shared calendar for your education level.</div></div>
<?php endif; ?>

<!-- Edit Semester Modal -->
<div class="modal-dialog" id="edit-sem-modal">
  <div class="modal-box" style="max-width:460px">
    <div class="modal-head">
      <h3>Edit Semester</h3>
      <p>Update the semester details below</p>
    </div>
    <form method="post">
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="update_semester" id="esm-id" value="">
        <div style="margin-bottom:16px">
          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Name *</label>
          <input class="input" name="name" id="esm-name" required placeholder="e.g. Semester I" style="width:100%">
        </div>
        <div style="display:flex;gap:14px">
          <div class="flex-col flex-1">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Start Date</label>
            <input class="input" type="date" name="start_date" id="esm-start" style="width:100%">
          </div>
          <div class="flex-col flex-1">
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">End Date</label>
            <input class="input" type="date" name="end_date" id="esm-end" style="width:100%">
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn" onclick="document.getElementById('edit-sem-modal').classList.remove('open')">Cancel</button>
        <button class="btn btn-primary"><?= icon('save') ?> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Year Modal -->
<div class="modal-dialog" id="edit-year-modal">
  <div class="modal-box" style="max-width:640px">
    <div class="modal-head">
      <h3>Edit Academic Year</h3>
      <p>Update the academic year details</p>
    </div>
    <form method="post">
      <div class="modal-body" style="max-height:65vh;overflow-y:auto">
        <?= csrf_field() ?>
        <input type="hidden" name="update_year" id="ey-id" value="">
        <div class="yr-wiz-grid">
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">School *</label>
            <select class="input" name="school_id" id="ey-school" required style="width:100%"><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
          </div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Name *</label><input class="input" name="name" id="ey-name" required style="width:100%"></div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Ethiopian Year</label><input class="input" name="ethiopian_year" id="ey-eth-year" style="width:100%"></div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Status</label>
            <select class="input" name="status" id="ey-status" style="width:100%"><option value="draft">Draft</option><option value="active">Active</option><option value="closed">Closed</option><option value="archived">Archived</option></select>
          </div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Start</label><input class="input" type="date" name="start_date" id="ey-start" style="width:100%"></div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">End</label><input class="input" type="date" name="end_date" id="ey-end" style="width:100%"></div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Ethiopian Start</label><input class="input" name="ethiopian_start" id="ey-eth-start" style="width:100%"></div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Ethiopian End</label><input class="input" name="ethiopian_end" id="ey-eth-end" style="width:100%"></div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Calendar</label>
            <select class="input" name="primary_calendar" id="ey-cal" style="width:100%"><option value="ethiopian">Ethiopian</option><option value="gregorian">Gregorian</option><option value="both">Both</option></select>
          </div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Working Days</label><input class="input" type="number" name="working_days_per_week" id="ey-work" value="5" style="width:100%"></div>
          <div class="flex-col"><label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px">Weekend</label><input class="input" name="weekend_days" id="ey-weekend" value="fri,sat" style="width:100%"></div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn" onclick="document.getElementById('edit-year-modal').classList.remove('open')">Cancel</button>
        <button class="btn btn-primary"><?= icon('save') ?> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleYearForm(){const f=document.getElementById('year-form'),b=document.getElementById('btn-new-year');if(f.style.display==='none'){f.style.display='block';b.style.display='none';updateSemFields()}else{f.style.display='none';b.style.display=''}}
function setShared(v){document.getElementById('is-shared').checked=v;document.getElementById('is-individual').checked=!v;document.getElementById('school-select-wrap').style.display=v?'none':'';document.getElementById('lbl-shared').style.borderColor=v?'#6366f1':'var(--border)';document.getElementById('lbl-individual').style.borderColor=!v?'#6366f1':'var(--border)'}
function updateSemFields(){const n=parseInt(document.getElementById('num-sem').value),c=document.getElementById('sem-fields');let h='<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:10px">';for(let i=1;i<=n;i++){h+=`<div style="display:flex;gap:8px;align-items:end"><div class="flex-col flex-1"><label style="font-size:12px;font-weight:600;margin-bottom:4px">Semester ${i}</label><input class="input" name="semester_${i}_name" placeholder="Semester ${['I','II','III','IV'][i-1]||i}" style="font-size:13px"></div><div class="flex-col" style="width:130px"><label style="font-size:12px;font-weight:600;margin-bottom:4px">Start</label><input class="input" type="date" name="semester_${i}_start" style="font-size:13px"></div><div class="flex-col" style="width:130px"><label style="font-size:12px;font-weight:600;margin-bottom:4px">End</label><input class="input" type="date" name="semester_${i}_end" style="font-size:13px"></div></div>`}c.innerHTML=h+'</div>'}
function editSem(id,name,start,end){document.getElementById('esm-id').value=id;document.getElementById('esm-name').value=name;document.getElementById('esm-start').value=start;document.getElementById('esm-end').value=end;document.getElementById('edit-sem-modal').classList.add('open')}
function editYear(y){document.getElementById('ey-id').value=y.id;document.getElementById('ey-name').value=y.name;document.getElementById('ey-school').value=y.school_id;document.getElementById('ey-eth-year').value=y.ethiopian_year||'';document.getElementById('ey-status').value=y.status;document.getElementById('ey-start').value=y.start_date||'';document.getElementById('ey-end').value=y.end_date||'';document.getElementById('ey-eth-start').value=y.ethiopian_start||'';document.getElementById('ey-eth-end').value=y.ethiopian_end||'';document.getElementById('ey-cal').value=y.primary_calendar||'ethiopian';document.getElementById('ey-work').value=y.working_days_per_week||5;document.getElementById('ey-weekend').value=y.weekend_days||'fri,sat';document.getElementById('edit-year-modal').classList.add('open')}
document.querySelectorAll('.modal-dialog').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
setShared(true);
</script>
