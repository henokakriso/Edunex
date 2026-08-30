<?php /* Director: teachers — card grid + full CRUD modals */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('users') ?> Teachers</h1>
    <p class="sub">Create teacher accounts, assign subjects and homeroom classes</p>
  </div>
  <div class="flex gap-8" style="flex-wrap:wrap">
    <form method="get" class="inline">
      <input type="hidden" name="r" value="director/teachers">
      <div class="input-icon-wrap" style="width:250px">
        <span class="input-ico"><?= icon('search') ?></span>
        <input class="input has-ico" type="text" name="q" id="tchr-search" value="<?= e($q ?? '') ?>" placeholder="Search name, email, phone…" oninput="document.getElementById('tchr-clear').style.display=this.value?'flex':'none'">
        <button type="button" class="input-icon-btn" id="tchr-clear" style="display:<?= ($q ?? '') ? 'flex' : 'none' ?>" onclick="document.getElementById('tchr-search').value='';this.style.display='none';this.form.submit()"><?= icon('x') ?></button>
      </div>
    </form>
    <button class="btn btn-primary" data-open-modal="new-teacher"><?= icon('plus') ?> Add teacher</button>
  </div>
</div>

<!-- Summary stats — click to filter -->
<?php $qsf = $q !== '' ? 'q=' . urlencode($q) . '&' : ''; ?>
<div class="grid grid-4" style="margin-bottom:20px">
  <a class="card stat-card stat-link<?= $filter === '' ? ' on' : '' ?>" href="<?= e(url('director/teachers?' . rtrim($qsf, '&'))) ?>"><span class="stat-ico" style="background:var(--accent-soft);color:var(--accent)"><?= icon('users') ?></span>
    <div class="stat-text"><b><?= (int)$stats['total'] ?></b><span>Teachers</span></div></a>
  <a class="card stat-card stat-link<?= $filter === 'with_courses' ? ' on' : '' ?>" href="<?= e(url('director/teachers?' . rtrim('f=with_courses&' . $qsf, '&'))) ?>"><span class="stat-ico" style="background:var(--info-soft);color:var(--info)"><?= icon('graduation') ?></span>
    <div class="stat-text"><b><?= (int)$stats['with_courses'] ?></b><span>Teach courses</span></div></a>
  <a class="card stat-card stat-link<?= $filter === 'homeroom' ? ' on' : '' ?>" href="<?= e(url('director/teachers?' . rtrim('f=homeroom&' . $qsf, '&'))) ?>"><span class="stat-ico" style="background:var(--success-soft);color:var(--success)"><?= icon('school') ?></span>
    <div class="stat-text"><b><?= (int)$stats['homeroom'] ?></b><span>Homeroom leads</span></div></a>
  <a class="card stat-card stat-link<?= $filter === 'no_subjects' ? ' on' : '' ?>" href="<?= e(url('director/teachers?' . rtrim('f=no_subjects&' . $qsf, '&'))) ?>"><span class="stat-ico" style="background:var(--warning-soft);color:var(--warning)"><?= icon('books') ?></span>
    <div class="stat-text"><b><?= (int)$stats['no_subjects'] ?></b><span>No subjects yet</span></div></a>
</div>

<!-- Teacher cards -->
<?php if (!$teachers): ?><div class="alert alert-info"><?= $filter ? 'No teachers match this filter.' : 'No teachers match.' ?> <a class="accent" href="<?= e(url('director/teachers')) ?>">Clear filter →</a></div><?php endif; ?>
<div class="teacher-grid">
  <?php foreach ($teachers as $t): $mine = $assignMap[(int)$t['id']] ?? []; ?>
    <div class="card tcard" data-id="<?= (int)$t['id'] ?>">
      <div class="thead">
        <span class="tavatar"><?= e(mb_strtoupper(mb_substr((string)$t['name'], 0, 1))) ?></span>
        <div class="thead-main">
          <div class="thead-top">
            <b class="tname"><?= e($t['name']) ?></b>
            <?php if ($t['dept']): ?><span class="badge badge-muted" title="Department"><?= e($t['dept']) ?></span><?php endif; ?>
          </div>
          <a class="temail" href="mailto:<?= e($t['email']) ?>"><?= e($t['email']) ?></a>
          <p class="tlogin"><?= icon('clock') ?><?= $t['last_login'] ? 'last login '.e(date('M j, g:i A', strtotime($t['last_login']))) : 'never logged in' ?></p>
        </div>
      </div>

      <div class="tmetrics">
        <span class="tm"><?= icon('books') ?><b><?= (int)$t['course_count'] ?></b><span>course<?= (int)$t['course_count'] === 1 ? '' : 's' ?></span></span>
        <span class="tm"><?= icon('school') ?><b><?= (int)$t['homeroom_count'] ?></b><span>class<?= (int)$t['homeroom_count'] === 1 ? '' : 'es' ?></span></span>
      </div>

      <div class="tchips">
        <?php if ($mine): ?>
          <?php foreach ($subjects as $s): if (!in_array((int)$s['id'], $mine, true)) continue; ?>
            <span class="tchip"><?= e($s['name']) ?></span>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="tchip-none"><?= icon('shield') ?> No subjects assigned yet</span>
        <?php endif; ?>
      </div>

      <div class="tactions">
        <button class="btn btn-sm btn-ghost" data-open-modal="edit-teacher" data-edit='<?= e(json_encode([
            'id' => (int)$t['id'], 'first' => explode(' ', $t['name'], 2)[0], 'last' => explode(' ', $t['name'], 2)[1] ?? '',
            'email' => $t['email'], 'phone' => $t['phone'], 'dept' => (int)($t['dept_id'] ?? 0),
        ])) ?>'><?= icon('edit') ?> Edit</button>
        <button class="btn btn-sm btn-ghost" data-open-modal="subjects-teacher" data-subjects="<?= implode(',', $mine) ?>" data-tid="<?= (int)$t['id'] ?>" data-tname="<?= e($t['name']) ?>"><?= icon('books') ?> Subjects</button>
        <button class="btn btn-sm btn-ghost" data-open-modal="homeroom-teacher" data-tid="<?= (int)$t['id'] ?>" data-tname="<?= e($t['name']) ?>" data-assigned="<?= (int)($t['homeroom_gid'] ?? 0) ?>"><?= icon('school') ?> Homeroom</button>
        <form method="post" class="inline" data-confirm="Remove <?= e($t['name']) ?>? This cannot be undone.">
          <?= csrf_field() ?><input type="hidden" name="delete_teacher" value="<?= (int)$t['id'] ?>">
          <button class="btn btn-sm btn-danger" title="Delete teacher"><?= icon('trash') ?></button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ============ Create modal ============ -->
<div class="modal-backdrop" id="new-teacher">
  <div class="modal">
    <div class="modal-head"><h3><?= icon('plus') ?> New teacher account</h3><button type="button" class="modal-close" data-close-modal="new-teacher">✕</button></div>
    <form method="post">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="grid2">
          <div class="flex-col"><label class="small faint">First name *</label><input class="input" name="first_name" required></div>
          <div class="flex-col"><label class="small faint">Last name *</label><input class="input" name="last_name" required></div>
          <div class="flex-col"><label class="small faint">Email *</label><input class="input" type="email" name="email" required></div>
          <div class="flex-col"><label class="small faint">Phone</label><input class="input" name="phone" placeholder="+2519…"></div>
          <div class="flex-col"><label class="small faint">Department</label>
            <select class="input" name="department_id"><option value="0">— None —</option>
              <?php foreach ($depts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
            </select></div>
          <div class="flex-col"><label class="small faint">Initial password (blank = auto)</label><input class="input" name="password" placeholder="auto-generated"></div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" data-close-modal="new-teacher">Cancel</button>
        <button class="btn btn-primary" name="create_teacher" value="1">Create teacher</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ Edit modal ============ -->
<div class="modal-backdrop" id="edit-teacher">
  <div class="modal">
    <div class="modal-head"><h3><?= icon('edit') ?> Edit teacher</h3><button type="button" class="modal-close" data-close-modal="edit-teacher">✕</button></div>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="edit_teacher" id="et-id" value="">
      <div class="modal-body">
        <div class="grid2">
          <div class="flex-col"><label class="small faint">First name *</label><input class="input" name="first_name" id="et-first" required></div>
          <div class="flex-col"><label class="small faint">Last name *</label><input class="input" name="last_name" id="et-last" required></div>
          <div class="flex-col"><label class="small faint">Email *</label><input class="input" type="email" name="email" id="et-email" required></div>
          <div class="flex-col"><label class="small faint">Phone</label><input class="input" name="phone" id="et-phone"></div>
          <div class="flex-col"><label class="small faint">Department</label>
            <select class="input" name="department_id" id="et-dept"><option value="0">— None —</option>
              <?php foreach ($depts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
            </select></div>
          <div class="flex-col"><label class="small faint">Reset password (blank = keep)</label><input class="input" name="password" placeholder="keep current"></div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" data-close-modal="edit-teacher">Cancel</button>
        <button class="btn btn-primary">Save changes</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ Subjects modal ============ -->
<div class="modal-backdrop" id="subjects-teacher">
  <div class="modal">
    <div class="modal-head"><h3><?= icon('books') ?> Subjects: <span id="st-name"></span></h3><button type="button" class="modal-close" data-close-modal="subjects-teacher">✕</button></div>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="set_subjects" id="st-id" value="">
      <div class="modal-body">
        <p class="tiny faint">Tick the subjects this teacher is authorised to teach. They can create courses only in these.</p>
        <div class="subject-chips" id="st-chips">
          <?php foreach ($subjects as $s): ?>
            <label class="chip"><input type="checkbox" name="subjects[]" value="<?= (int)$s['id'] ?>"><span><?= e($s['name']) ?></span></label>
          <?php endforeach; ?>
          <?php if (!$subjects): ?><span class="tiny faint">No subjects yet — create them under admin.</span><?php endif; ?>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" data-close-modal="subjects-teacher">Cancel</button>
        <button class="btn btn-primary">Save subjects</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ Homeroom modal ============ -->
<div class="modal-backdrop" id="homeroom-teacher">
  <div class="modal">
    <div class="modal-head"><h3><?= icon('school') ?> Homeroom: <span id="hr-name"></span></h3><button type="button" class="modal-close" data-close-modal="homeroom-teacher">✕</button></div>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="homeroom_teacher_id" id="hr-tid" value="">
      <div class="modal-body">
        <p class="tiny faint">Assign this teacher as the homeroom lead of a class. Students of that class verify with them.</p>
        <div class="flex-col" style="margin-top:6px"><label class="small faint">Class</label>
          <select class="input" name="set_homeroom" id="hr-class"><option value="0">— Remove from homeroom —</option>
            <?php foreach ($groups as $g): ?>
              <option value="<?= (int)$g['id'] ?>"><?= e($g['name'] . ' — ' . $g['grade'] . ' ' . $g['section']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if (!$groups): ?><p class="tiny faint" style="margin-top:8px">No classes yet.</p><?php endif; ?>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-ghost" data-close-modal="homeroom-teacher">Cancel</button>
        <button class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Homeroom classes overview -->
<h3 class="small" style="margin:26px 0 10px">Homeroom classes</h3>
<div class="card" style="padding:0;overflow:hidden">
  <?php if (!$groups): ?><p class="muted small" style="padding:16px 20px">No classes yet — create them under admin.</p><?php endif; ?>
  <?php foreach ($groups as $g): $lead = array_values(array_filter($teachers, fn($t) => (int)$t['homeroom_count'] > 0)); ?>
    <div class="list-row" style="padding:13px 20px;border-bottom:1px solid var(--border);align-items:center">
      <span class="stat-ico" style="background:var(--accent-soft);color:var(--accent)"><?= icon('tag') ?></span>
      <div class="flex-1" style="min-width:0">
        <b class="small"><?= e($g['name']) ?></b>
        <p class="tiny faint"><?= e($g['grade'] . ' ' . $g['section']) ?> · students verify with the homeroom teacher</p>
      </div>
      <form method="post" class="inline">
        <?= csrf_field() ?><input type="hidden" name="set_homeroom" value="<?= (int)$g['id'] ?>">
        <select class="select" name="homeroom_teacher_id" onchange="this.form.submit()">
          <option value="0">— none —</option>
          <?php foreach ($teachers as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= (int)$g['homeroom_teacher_id'] === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<style>
.teacher-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; }
.tcard { display:flex; flex-direction:column; gap:14px; padding:20px; border-radius:18px; transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
.tcard:hover { transform:translateY(-3px); box-shadow: 0 0 0 1px rgba(255,255,255,.15), inset 0 1px 1px rgba(255,255,255,.25), 0 8px 32px rgba(0,0,0,.08); backdrop-filter: blur(40px) saturate(200%); -webkit-backdrop-filter: blur(40px) saturate(200%); border-color:transparent; }
.thead { display:flex; gap:14px; align-items:flex-start; }
.tavatar { width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px; color:#fff; flex:none; }
.thead-main { flex:1; min-width:0; display:flex; flex-direction:column; gap:2px; }
.thead-top { display:flex; align-items:center; gap:8px; min-width:0; }
.tname { font-size:15px; font-weight:700; letter-spacing:-.15px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.temail { font-size:12.5px; color:var(--text-dim); text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.temail:hover { color:var(--accent); text-decoration:underline; }
.tlogin { display:flex; align-items:center; gap:5px; font-size:11px; color:var(--text-faint); margin:0; }
.tlogin .ico { width:12px; height:12px; }
.tmetrics { display:flex; align-items:center; padding:12px 0; margin-top:8px; }
.tm { flex:1; display:flex; align-items:center; gap:7px; font-size:12.5px; }
.tm + .tm { border-left:1px dashed var(--border); padding-left:16px; }
.tm .ico { width:15px; height:15px; color:var(--text-faint); flex:none; }
.tm b { color:var(--text); font-size:15px; line-height:1; }
.tm span { font-size:12px; color:var(--text-faint); }
.tchips { display:flex; flex-wrap:wrap; gap:7px; min-height:26px; align-items:center; }
.tchip { background:var(--accent-soft); color:var(--accent); border-radius:999px; padding:4px 12px; font-size:12px; font-weight:600; }
.tchip-none { display:inline-flex; align-items:center; gap:6px; border:1px dashed var(--border); color:var(--text-faint); border-radius:999px; padding:4px 12px; font-size:12px; }
.tchip-none .ico { width:13px; height:13px; }
.tactions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; padding-top:13px; margin-top:auto; }
.tactions form { margin-left:auto; }
.tactions .btn-danger { background:transparent; border:1px solid color-mix(in srgb,var(--danger) 25%,transparent); color:var(--danger); }
.tactions .btn-danger:hover { background:var(--danger-soft); border-color:var(--danger); }
.stat-link { text-decoration:none; cursor:pointer; transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
.stat-link:hover { transform:translateY(-2px); box-shadow: 0 0 0 1px rgba(255,255,255,.15), inset 0 1px 1px rgba(255,255,255,.2), 0 6px 24px rgba(0,0,0,.07); backdrop-filter: blur(32px) saturate(180%); -webkit-backdrop-filter: blur(32px) saturate(180%); border-color:transparent; }
.stat-link.on { border-color:var(--accent); background:color-mix(in srgb,var(--accent) 5%,var(--card)); }
.stat-link.on::after { content:"✕"; position:absolute; top:8px; right:10px; font-size:11px; color:var(--accent); opacity:.85; }
.subject-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
.chip { display:inline-flex; align-items:center; gap:5px; border:1px solid var(--border); border-radius:999px; padding:5px 12px; font-size:12.5px; cursor:pointer; background:var(--card); transition:all .15s ease; }
.chip:hover { border-color:transparent; box-shadow: 0 0 0 1px rgba(255,255,255,.15), inset 0 1px 1px rgba(255,255,255,.2), 0 4px 12px rgba(0,0,0,.06); backdrop-filter: blur(24px) saturate(160%); -webkit-backdrop-filter: blur(24px) saturate(160%); }
.chip input { accent-color: var(--accent); }
.chip:has(input:checked) { background:var(--accent-soft); color:var(--accent); border-color:var(--accent); font-weight:600; }
</style>

<script>
const TC = [
  'linear-gradient(135deg,#0d9488,#059669)',
  'linear-gradient(135deg,#0284c7,#0ea5e9)',
  'linear-gradient(135deg,#f59e0b,#f97316)',
  'linear-gradient(135deg,#8b5cf6,#d946ef)',
  'linear-gradient(135deg,#ef4444,#f97316)',
  'linear-gradient(135deg,#16a34a,#84cc16)',
];
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.tavatar').forEach((a, i) => { a.style.background = TC[i % TC.length]; });
  // Edit modal: fill fields from data-edit JSON
  document.querySelectorAll('[data-open-modal="edit-teacher"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const d = JSON.parse(btn.dataset.edit);
      document.getElementById('et-id').value = d.id;
      document.getElementById('et-first').value = d.first;
      document.getElementById('et-last').value = d.last;
      document.getElementById('et-email').value = d.email;
      document.getElementById('et-phone').value = d.phone || '';
      document.getElementById('et-dept').value = d.dept || 0;
    });
  });
  // Subjects modal
  document.querySelectorAll('[data-open-modal="subjects-teacher"]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('st-id').value = btn.dataset.tid;
      document.getElementById('st-name').textContent = btn.dataset.tname;
      const sel = (btn.dataset.subjects || '').split(',').map(Number).filter(Boolean);
      document.querySelectorAll('#st-chips input').forEach(cb => { cb.checked = sel.includes(Number(cb.value)); });
    });
  });
  // Homeroom modal
  document.querySelectorAll('[data-open-modal="homeroom-teacher"]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('hr-name').textContent = btn.dataset.tname;
      document.getElementById('hr-tid').value = btn.dataset.tid;
      document.getElementById('hr-class').value = btn.dataset.assigned ? Number(btn.dataset.assigned) : 0;
    });
  });
});
</script>
