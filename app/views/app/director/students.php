<?php /* Director: students list + active/inactive management */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('graduation') ?> Students</h1>
    <p class="sub">Manage student access — active, re-exam (inactive) and pending accounts</p>
  </div>
  <form method="get" class="inline">
    <input type="hidden" name="r" value="director/students">
    <div class="input-icon-wrap">
      <input class="input" style="width:230px;padding-right:30px" name="q" id="stu-search" value="<?= e($q) ?>" placeholder="Search name, ID, email…" oninput="document.getElementById('stu-clear').style.display=this.value?'flex':'none'">
      <button type="button" class="input-icon-btn" id="stu-clear" style="display:<?= $q ? 'flex' : 'none' ?>" onclick="document.getElementById('stu-search').value='';this.style.display='none';this.form.submit()"><?= icon('x') ?></button>
    </div>
  </form>
</div>

<!-- Summary stats — click to filter -->
<?php $qsf = $q !== '' ? 'q=' . urlencode($q) . '&' : ''; ?>
<div class="grid grid-4" style="margin-bottom:20px">
  <a class="card stat-card stat-link<?= $filter === 'all' ? ' on' : '' ?>" href="<?= e(url('director/students?' . rtrim($qsf, '&'))) ?>"><span class="stat-ico" style="background:var(--accent-soft);color:var(--accent)"><?= icon('graduation') ?></span>
    <div class="stat-text"><b><?= (int)$stats['total'] ?></b><span>All students</span></div></a>
  <a class="card stat-card stat-link<?= $filter === 'active' ? ' on' : '' ?>" href="<?= e(url('director/students?' . rtrim('filter=active&' . $qsf, '&'))) ?>"><span class="stat-ico" style="background:var(--success-soft);color:var(--success)"><?= icon('user') ?></span>
    <div class="stat-text"><b><?= (int)$stats['active'] ?></b><span>Active</span></div></a>
  <a class="card stat-card stat-link<?= $filter === 'inactive' ? ' on' : '' ?>" href="<?= e(url('director/students?' . rtrim('filter=inactive&' . $qsf, '&'))) ?>"><span class="stat-ico" style="background:var(--warning-soft);color:var(--warning)"><?= icon('pause') ?></span>
    <div class="stat-text"><b><?= (int)$stats['inactive'] ?></b><span>Inactive (re-exam)</span></div></a>
  <a class="card stat-card stat-link<?= $filter === 'pending' ? ' on' : '' ?>" href="<?= e(url('director/students?' . rtrim('filter=pending&' . $qsf, '&'))) ?>"><span class="stat-ico" style="background:var(--info-soft);color:var(--info)"><?= icon('clock') ?></span>
    <div class="stat-text"><b><?= (int)$stats['pending'] ?></b><span>Pending</span></div></a>
</div>

<!-- Student cards -->
<?php if (!$students): ?>
  <div class="alert alert-info"><?= $filter !== 'all' ? 'No students match this filter.' : 'No students match.' ?> <a class="accent" href="<?= e(url('director/students')) ?>">Clear filter →</a></div>
<?php endif; ?>
<div class="student-grid">
  <?php foreach ($students as $s): ?>
    <div class="card scard" data-id="<?= (int)$s['id'] ?>">
      <div class="shead">
        <span class="savatar"><?= e(mb_strtoupper(mb_substr((string)$s['name'], 0, 1))) ?></span>
        <div class="shead-main">
          <div class="shead-top">
            <b class="sname"><?= e($s['name']) ?></b>
            <?php if ($s['status'] === 'pending'): ?><span class="badge badge-info">pending</span>
            <?php elseif ($s['status'] !== 'active'): ?><span class="badge badge-warning"><?= e($s['status']) ?></span><?php endif; ?>
          </div>
          <a class="semail" href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a>
        </div>
      </div>

      <div class="smeta">
        <span class="smi"><?= icon('hash') ?> <?= e($s['student_id'] ?? 'no ID') ?></span>
        <span class="smi"><?= icon('school') ?> <?= e($s['group_name'] ?? 'no class') ?></span>
      </div>

      <div class="smetrics">
        <span class="sm"><?= icon('books') ?><b><?= (int)$s['courses'] ?></b><span>course<?= (int)$s['courses'] === 1 ? '' : 's' ?></span></span>
        <span class="sm"><?= $s['enrollment_status'] === 'active' ? icon('check-circle') : icon('pause') ?><b><?= $s['enrollment_status'] === 'active' ? 'Full' : 'Re-exam' ?></b><span><?= $s['enrollment_status'] === 'active' ? 'access' : 'track' ?></span></span>
      </div>

      <div class="sactions">
        <?php if ($s['status'] === 'pending'): ?>
          <form method="post" class="inline"><?= csrf_field() ?>
            <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
            <button class="btn btn-sm btn-success" name="activate" value="1"><?= icon('check') ?> Activate</button></form>
        <?php endif; ?>
        <form method="post" class="inline"><?= csrf_field() ?>
          <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
          <?php if ($s['enrollment_status'] === 'active'): ?>
            <button class="btn btn-sm btn-warning" name="set_enrollment" value="inactive" title="Switch to re-exam track — courses & exams only"><?= icon('pause') ?> Inactive</button>
          <?php else: ?>
            <button class="btn btn-sm btn-success" name="set_enrollment" value="active" title="Restore full student access (classes, homeroom, attendance, grades)"><?= icon('play') ?> Active</button>
          <?php endif; ?>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="info-bar">
  <b><?= icon('info') ?> Active vs Inactive — how student access works</b>
  <div class="info-cols">
    <div class="info-col">
      <span class="info-ico ico-ok"><?= icon('check-circle') ?></span>
      <div><b>Active students</b><p>Have full access — classes, homeroom teacher, attendance and grades.</p></div>
    </div>
    <div class="info-col">
      <span class="info-ico ico-warn"><?= icon('books') ?></span>
      <div><b>Inactive students (re-exam track)</b><p>See only courses and exams, so they can prepare and sit re-exams.</p></div>
    </div>
  </div>
</div>

<style>
.student-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; }
.scard { display:flex; flex-direction:column; gap:14px; padding:20px; border-radius:18px; transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
.scard:hover { transform:translateY(-3px); box-shadow: 0 0 0 1px rgba(255,255,255,.15), inset 0 1px 1px rgba(255,255,255,.25), 0 8px 32px rgba(0,0,0,.08); backdrop-filter: blur(40px) saturate(200%); -webkit-backdrop-filter: blur(40px) saturate(200%); border-color:transparent; }
.shead { display:flex; gap:14px; align-items:flex-start; }
.savatar { width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px; color:#fff; flex:none; background:linear-gradient(135deg,#0284c7,#0ea5e9); }
.shead-main { flex:1; min-width:0; display:flex; flex-direction:column; gap:2px; }
.shead-top { display:flex; align-items:center; gap:8px; min-width:0; }
.sname { font-size:15px; font-weight:700; letter-spacing:-.15px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.semail { font-size:12.5px; color:var(--text-dim); text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.semail:hover { color:var(--accent); text-decoration:underline; }
.smeta { display:flex; flex-wrap:wrap; gap:6px 16px; padding:10px 0; margin-top:8px; }
.smi { display:inline-flex; align-items:center; gap:6px; font-size:12.5px; color:var(--text-dim); }
.smi .ico { width:13px; height:13px; color:var(--text-faint); }
.smetrics { display:flex; align-items:center; }
.sm { flex:1; display:flex; align-items:center; gap:7px; font-size:12.5px; }
.sm + .sm { border-left:1px dashed var(--border); padding-left:16px; }
.sm .ico { width:15px; height:15px; color:var(--text-faint); flex:none; }
.sm b { color:var(--text); font-size:15px; line-height:1; }
.sm span { font-size:12px; color:var(--text-faint); }
.sactions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; padding-top:13px; margin-top:auto; }
.sactions form:last-child { margin-left:auto; }
.info-bar { display:flex; flex-direction:column; gap:12px; margin-top:20px; padding:18px 22px; border:1px solid color-mix(in srgb,var(--info) 35%,var(--border)); border-radius:16px; background:linear-gradient(180deg,color-mix(in srgb,var(--info) 7%,transparent),transparent); }
.info-bar > b { display:flex; align-items:center; gap:8px; font-size:14px; color:var(--info); }
.info-bar > b .ico { width:17px; height:17px; }
.info-cols { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.info-col { display:flex; gap:12px; padding:14px 16px; border-radius:12px; background:var(--card); border:1px solid var(--border); }
.info-col .info-ico { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex:none; }
.info-col .info-ico .ico { width:18px; height:18px; }
.info-col b { display:block; font-size:13.5px; margin-bottom:4px; }
.info-col p { font-size:12.8px; line-height:1.65; text-align:justify; color:var(--text-dim); margin:0; }
.ico-ok { background:var(--success-soft); color:var(--success); }
.ico-warn { background:var(--warning-soft); color:var(--warning); }
.info-col:nth-child(1) b { color:var(--success); }
.info-col:nth-child(2) b { color:var(--warning); }
@media (max-width:720px) { .info-cols { grid-template-columns:1fr; } }
</style>

<script>
document.querySelectorAll('.savatar').forEach((a, i) => {
  a.style.background = [
    'linear-gradient(135deg,#0284c7,#0ea5e9)',
    'linear-gradient(135deg,#0d9488,#059669)',
    'linear-gradient(135deg,#8b5cf6,#d946ef)',
    'linear-gradient(135deg,#f59e0b,#f97316)',
    'linear-gradient(135deg,#ef4444,#f97316)',
    'linear-gradient(135deg,#16a34a,#84cc16)',
  ][i % 6];
});
</script>
