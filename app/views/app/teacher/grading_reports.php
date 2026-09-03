<?php /* Grading Reports — PDF generation for student/class/course/teacher/exam */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('file') ?> Grading Reports</h1>
    <p class="sub">Generate PDF reports for grades, results, and analytics</p>
  </div>
</div>

<div class="card">
  <table class="table" style="margin:0">
    <thead>
      <tr>
        <th style="width:40px"></th>
        <th>Report Type</th>
        <th>Description</th>
        <th style="width:220px">Course</th>
        <th style="width:220px;display:none" id="assess-col-th">Assessment</th>
        <th style="width:120px">Action</th>
      </tr>
    </thead>
    <tbody>
      <!-- Student Result -->
      <tr>
        <td style="text-align:center;color:var(--accent)"><?= icon('user') ?></td>
        <td><b class="small">Student Result Report</b></td>
        <td class="tiny faint">Individual student results with rounds and final grade</td>
        <td>
          <select class="input" id="rpt-student-course" style="width:100%;margin:0">
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </td>
        <td id="assess-col-student"></td>
        <td><button class="btn btn-primary btn-sm" onclick="openPDF('student', document.getElementById('rpt-student-course').value)">⬇ PDF</button></td>
      </tr>
      <!-- Class Result -->
      <tr>
        <td style="text-align:center;color:var(--success)"><?= icon('users') ?></td>
        <td><b class="small">Class Result Report</b></td>
        <td class="tiny faint">Full class results with rankings and statistics</td>
        <td>
          <select class="input" id="rpt-class-course" style="width:100%;margin:0">
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </td>
        <td></td>
        <td><button class="btn btn-primary btn-sm" onclick="openPDF('class', document.getElementById('rpt-class-course').value)">⬇ PDF</button></td>
      </tr>
      <!-- Exam Result -->
      <tr>
        <td style="text-align:center;color:var(--warning)"><?= icon('doc') ?></td>
        <td><b class="small">Exam Results Report</b></td>
        <td class="tiny faint">Specific exam/assessment results</td>
        <td>
          <select class="input" id="rpt-exam-course" style="width:100%;margin:0" onchange="loadExamAssessments(this.value)">
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </td>
        <td>
          <select class="input" id="rpt-exam-assessment" style="width:100%;margin:0">
            <option value="">— Select course first —</option>
          </select>
        </td>
        <td><button class="btn btn-primary btn-sm" onclick="openPDF('exam', document.getElementById('rpt-exam-assessment').value, true)">⬇ PDF</button></td>
      </tr>
      <!-- Teacher Summary -->
      <tr>
        <td style="text-align:center;color:var(--info)"><?= icon('graduation') ?></td>
        <td><b class="small">Teacher Summary</b></td>
        <td class="tiny faint">Your teaching performance overview across all courses</td>
        <td><span class="tiny faint">All courses</span></td>
        <td></td>
        <td><button class="btn btn-primary btn-sm" onclick="openPDF('teacher', 0)">⬇ PDF</button></td>
      </tr>
    </tbody>
  </table>
</div>

<script>
async function loadExamAssessments(courseId) {
  var sel = document.getElementById('rpt-exam-assessment');
  sel.innerHTML = '<option value="">Loading...</option>';
  if (!courseId) { sel.innerHTML = '<option value="">— Select course first —</option>'; return; }
  try {
    var resp = await fetch('<?= url("api/grading_assessments") ?>&course=' + courseId);
    var data = await resp.json();
    sel.innerHTML = '<option value="">— Select Assessment —</option>';
    (data.assessments || []).forEach(function(a) {
      sel.innerHTML += '<option value="' + a.id + '">' + a.title + ' (' + (a.type_label || a.type_slug) + ')</option>';
    });
  } catch(e) { sel.innerHTML = '<option value="">Error loading</option>'; }
}

function openPDF(type, id, isAssessment) {
  if (type !== 'teacher' && !id) { alert('Please select a course first.'); return; }
  if (isAssessment && !id) { alert('Please select an assessment.'); return; }
  var url = '<?= url("teacher/grading/pdf") ?>&type=' + type;
  if (type === 'exam') url += '&id=' + id;
  else if (type !== 'teacher') url += '&course=' + id;
  url += '&_t=' + Date.now();
  window.location.href = url;
}
</script>
