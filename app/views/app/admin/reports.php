<?php /* Admin reports view — Ministry-level oversight */
$reportTypes = [
    'education_performance' => ['Education Performance Report', 'Overall performance across regions, schools, grades, and subjects'],
    'enrollment_stats' => ['Enrollment Statistics', 'Student enrollment trends and totals by region, zone, and institution'],
    'academic_performance' => ['Academic Performance', 'Grade/exam performance, pass rates, averages, subject performance'],
    'attendance_participation' => ['Attendance & Participation', 'Attendance trends and participation rates across institutions'],
    'national_exam' => ['National Exam Performance', 'Exam participation, scores, pass/fail rates, rankings and trends'],
    'school_performance' => ['School Performance', 'Compare institutional performance without exposing operational rosters'],
    'teacher_workforce' => ['Teacher Workforce Statistics', 'Aggregated teacher numbers, workload, subject coverage'],
    'course_curriculum' => ['Course & Curriculum Analytics', 'Course usage, completion, curriculum coverage'],
    'learning_activity' => ['Learning Activity Report', 'Login/activity, assignments, quizzes, learning engagement'],
    'student_progress' => ['Student Progress Report', 'Aggregated progression, completion, dropout/retention indicators'],
    'regional_education' => ['Regional Education Report', 'Compare regions, zones, and cities'],
    'institution_stats' => ['Institution Statistics', 'Number of active institutions, students, teachers, courses'],
    'digital_platform' => ['Digital Platform Usage', 'Edunex adoption and system utilization metrics'],
    'compliance' => ['Compliance Report', 'Ministry policies, reporting compliance, institutional status'],
    'annual_education' => ['Annual Education Report', 'Comprehensive yearly ministry-level report'],
    'system_activity' => ['System Activity Report', 'Ministry-level administrative and audit information'],
];
$currentYear = (int)date('Y');
?>
<style>
.rpt-type{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;border:2px solid var(--border);cursor:pointer;transition:all .2s ease;background:var(--bg-elev)}
.rpt-type:hover{border-color:transparent;box-shadow:0 0 0 1px rgba(255,255,255,.15),inset 0 1px 1px rgba(255,255,255,.2),0 4px 16px rgba(0,0,0,.06)}
.rpt-type.selected{border-color:transparent;background:var(--accent-soft);box-shadow:0 0 0 1px rgba(13,148,136,.4),inset 0 1px 1px rgba(255,255,255,.25),0 0 12px rgba(13,148,136,.1)}
.rpt-type input[type=checkbox]{display:none}
.rpt-type .rpt-name{font-size:13.5px;font-weight:600;color:var(--text)}
.rpt-type .rpt-desc{font-size:11.5px;color:var(--text-secondary);margin-top:1px}
.rpt-type .rpt-dot{width:18px;height:18px;border-radius:5px;border:2px solid var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s}
.rpt-type.selected .rpt-dot{border-color:var(--accent);background:var(--accent)}
.rpt-type.selected .rpt-dot::after{content:'';width:10px;height:6px;border-left:2px solid #fff;border-bottom:2px solid #fff;transform:rotate(-45deg) translateY(-1px)}
.rpt-row{display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;border:1px solid var(--border);margin-bottom:6px;background:var(--bg-elev);transition:all .2s ease;text-decoration:none;color:inherit}
.rpt-row:hover{border-color:transparent;box-shadow:0 0 0 1px rgba(255,255,255,.15),inset 0 1px 1px rgba(255,255,255,.2),0 4px 12px rgba(0,0,0,.06)}
.rpt-row:focus-visible{border-color:var(--accent);box-shadow:0 0 0 3px rgba(99,102,241,.12);outline:none}
</style>

<div class="page-head">
  <div>
    <h1><?= icon('trend-up') ?> Reports</h1>
    <p class="sub">Generate Ministry-level education reports — national oversight, policy, performance &amp; compliance</p>
  </div>
</div>

<form method="post" id="report-form">
  <?= csrf_field() ?>

  <!-- Report Type Selection (checkboxes) -->
  <div class="card" style="margin-bottom:16px">
    <h3 class="card-title" style="margin-top:0"><?= icon('list') ?> Report Type <span class="tiny faint" style="font-weight:400;margin-left:6px">Select one or more</span></h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:8px">
      <?php foreach ($reportTypes as $key => [$name, $desc]): ?>
        <label class="rpt-type" onclick="toggleType(event, this, '<?= $key ?>')">
          <input type="checkbox" name="report_types[]" value="<?= $key ?>">
          <span class="rpt-dot"></span>
          <div><div class="rpt-name"><?= $name ?></div><div class="rpt-desc"><?= $desc ?></div></div>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Filters -->
  <div class="card" style="margin-bottom:16px">
    <h3 class="card-title" style="margin-top:0"><?= icon('filter') ?> Filters</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px">
      <div class="flex-col">
        <label class="small faint">Region</label>
        <select class="input" name="region" id="rpt-region" onchange="filterZones()">
          <option value="">All regions</option>
          <?php foreach ($regions as $r): ?><option value="<?= e($r['region']) ?>"><?= e($r['region']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Zone</label>
        <select class="input" name="zone" id="rpt-zone">
          <option value="">All zones</option>
          <?php foreach ($zones as $z): ?>
            <option value="<?= e($z['zone_name']) ?>" data-region="<?= e($z['region_name']) ?>"><?= e($z['zone_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Institution</label>
        <select class="input" name="school_id">
          <option value="0">All institutions</option>
          <?php foreach ($schools as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Education Level</label>
        <select class="input" name="education_level">
          <option value="">All levels</option>
          <option value="kg">Kindergarten (KG)</option>
          <option value="pre-elementary">Pre-Elementary</option>
          <option value="elementary">Elementary</option>
          <option value="highschool">High School</option>
          <option value="university">University</option>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Academic Year</label>
        <select class="input" name="year">
          <option value="">All years</option>
          <?php for ($y = $currentYear; $y >= 2015; $y--): ?>
            <option value="<?= $y ?>"><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Semester</label>
        <select class="input" name="semester">
          <option value="">All semesters</option>
          <?php foreach ($semesters as $sem): ?><option value="<?= e($sem['name']) ?>"><?= e($sem['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col">
        <label class="small faint">Date From</label>
        <input class="input" type="date" name="date_from">
      </div>
      <div class="flex-col">
        <label class="small faint">Date To</label>
        <input class="input" type="date" name="date_to">
      </div>
    </div>
    <div class="flex-col" style="margin-top:14px;max-width:400px">
      <label class="small faint">Title (optional)</label>
      <input class="input" name="title" placeholder="e.g. Q1 2026 National Performance Report">
    </div>
  </div>

  <div style="display:flex;gap:10px;align-items:center">
    <button class="btn btn-success btn-lg" name="generate" value="1" id="btn-generate" disabled style="opacity:.5"><?= icon('rocket') ?> Generate Report</button>
    <span class="tiny faint" id="rpt-hint">Select one or more report types above</span>
  </div>
</form>

<!-- Recent Reports -->
<?php if ($reports): ?>
<div class="card" style="margin-top:24px">
  <h3 class="card-title" style="margin-top:0"><?= icon('folder') ?> Generated Reports</h3>
  <?php foreach ($reports as $r): ?>
    <a class="rpt-row" href="<?= e(url('admin/reports&action=view&id=' . $r['id'])) ?>">
      <div style="flex:1;min-width:0">
        <div style="font-size:13.5px;font-weight:600;color:var(--text)"><?= e($r['title']) ?></div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">
          <span class="badge badge-muted" style="font-size:10px"><?= e($r['type']) ?></span>
          <?= e(date('M j, Y g:i A', strtotime($r['created_at']))) ?> · <?= e($r['user_name']) ?>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function toggleType(e, el, val) {
  e.preventDefault();
  const cb = el.querySelector('input[type=checkbox]');
  cb.checked = !cb.checked;
  el.classList.toggle('selected', cb.checked);
  const checked = document.querySelectorAll('.rpt-type.selected').length;
  const btn = document.getElementById('btn-generate');
  const hint = document.getElementById('rpt-hint');
  if (checked > 0) {
    btn.disabled = false;
    btn.style.opacity = '1';
    const names = [...document.querySelectorAll('.rpt-type.selected .rpt-name')].map(e => e.textContent);
    hint.textContent = checked === 1 ? 'Generating: ' + names[0] : checked + ' reports selected';
  } else {
    btn.disabled = true;
    btn.style.opacity = '.5';
    hint.textContent = 'Select one or more report types above';
  }
}
function filterZones() {
  const r = document.getElementById('rpt-region').value;
  const z = document.getElementById('rpt-zone');
  z.querySelectorAll('option[data-region]').forEach(o => {
    o.style.display = (!r || o.dataset.region === r) ? '' : 'none';
  });
  z.value = '';
}
</script>
