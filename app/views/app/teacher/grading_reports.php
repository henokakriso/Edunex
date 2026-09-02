<?php /* Grading Reports — PDF generation for student/class/course/teacher/exam */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('file') ?> Grading Reports</h1>
    <p class="sub">Generate PDF reports for grades, results, and analytics</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">

  <!-- Student Result Report -->
  <div class="card">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
      <span style="font-size:20px;color:var(--accent)"><?= icon('user') ?></span>
      <div>
        <h4 class="card-title" style="margin:0">Student Result Report</h4>
        <p class="tiny faint">Individual student results for all courses</p>
      </div>
    </div>
    <form id="student-report-form">
      <div class="flex-col" style="margin-bottom:10px">
        <label class="small faint">Course</label>
        <select class="input" name="course" required>
          <option value="">— Select Course —</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="button" class="btn btn-primary" style="width:100%" onclick="generateStudentReport()"><?= icon('file') ?> Generate PDF</button>
    </form>
  </div>

  <!-- Class Report -->
  <div class="card">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
      <span style="font-size:20px;color:var(--success)"><?= icon('users') ?></span>
      <div>
        <h4 class="card-title" style="margin:0">Class Result Report</h4>
        <p class="tiny faint">Full class results with rankings</p>
      </div>
    </div>
    <form id="class-report-form">
      <div class="flex-col" style="margin-bottom:10px">
        <label class="small faint">Course</label>
        <select class="input" name="course" required>
          <option value="">— Select Course —</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="button" class="btn btn-primary" style="width:100%" onclick="generateClassReport()"><?= icon('file') ?> Generate PDF</button>
    </form>
  </div>

  <!-- Exam-specific Report -->
  <div class="card">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
      <span style="font-size:20px;color:var(--warning) <?= icon('doc') ?>"></span>
      <div>
        <h4 class="card-title" style="margin:0">Exam Results Report</h4>
        <p class="tiny faint">Specific exam/assessment results</p>
      </div>
    </div>
    <form id="exam-report-form">
      <div class="flex-col" style="margin-bottom:10px">
        <label class="small faint">Course</label>
        <select class="input" name="course" required onchange="loadAssessments(this.value)">
          <option value="">— Select Course —</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col" style="margin-bottom:10px">
        <label class="small faint">Assessment</label>
        <select class="input" name="assessment" id="assessment-select" required>
          <option value="">— Select course first —</option>
        </select>
      </div>
      <button type="button" class="btn btn-primary" style="width:100%" onclick="generateExamReport()"><?= icon('file') ?> Generate PDF</button>
    </form>
  </div>

  <!-- Teacher Summary Report -->
  <div class="card">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
      <span style="font-size:20px;color:var(--info)"><?= icon('graduation') ?></span>
      <div>
        <h4 class="card-title" style="margin:0">Teacher Summary</h4>
        <p class="tiny faint">Your teaching performance overview</p>
      </div>
    </div>
    <button type="button" class="btn btn-primary" style="width:100%" onclick="generateTeacherReport()"><?= icon('file') ?> Generate PDF</button>
  </div>

</div>

<script>
async function loadAssessments(courseId) {
  var sel = document.getElementById('assessment-select');
  sel.innerHTML = '<option value="">Loading...</option>';
  if (!courseId) { sel.innerHTML = '<option value="">— Select course first —</option>'; return; }
  try {
    var resp = await fetch('<?= url("api/grading_assessments") ?>&course=' + courseId);
    var data = await resp.json();
    sel.innerHTML = '<option value="">— Select Assessment —</option>';
    (data.assessments || []).forEach(function(a) {
      sel.innerHTML += '<option value="' + a.id + '">' + a.title + ' (' + (a.type_label || a.type_slug) + ') — Max: ' + a.max_mark + '</option>';
    });
  } catch(e) { sel.innerHTML = '<option value="">Error loading</option>'; }
}

function generateStudentReport() {
  var courseId = document.querySelector('#student-report-form [name=course]').value;
  if (!courseId) { alert('Select a course'); return; }
  window.location.href = '<?= url("teacher/grading/pdf") ?>&type=student&course=' + courseId;
}
function generateClassReport() {
  var courseId = document.querySelector('#class-report-form [name=course]').value;
  if (!courseId) { alert('Select a course'); return; }
  window.location.href = '<?= url("teacher/grading/pdf") ?>&type=class&course=' + courseId;
}
function generateExamReport() {
  var assessmentId = document.querySelector('#exam-report-form [name=assessment]').value;
  if (!assessmentId) { alert('Select an assessment'); return; }
  window.location.href = '<?= url("teacher/grading/pdf") ?>&type=exam&id=' + assessmentId;
}
function generateTeacherReport() {
  window.location.href = '<?= url("teacher/grading/pdf") ?>&type=teacher';
}
</script>
