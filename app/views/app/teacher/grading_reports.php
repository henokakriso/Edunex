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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.2/jspdf.umd.min.js"></script>

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

const FLAG_URL  = '<?= url("public/images/ethiopian-flag.jpeg") ?>';
const MINIS_URL = '<?= url("public/images/ministry-logo.png") ?>';

function toDataURL(url, type) {
  return new Promise(function(resolve) {
    var img = new Image(); img.crossOrigin = 'anonymous';
    img.onload = function() { var c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight; c.getContext('2d').drawImage(img, 0, 0); resolve(c.toDataURL(type)); };
    img.onerror = function() { resolve(null); };
    img.src = url;
  });
}

function stampEveryPage(pdf, flagURI, minisURI) {
  var total = pdf.internal.getNumberOfPages();
  var W = pdf.internal.pageSize.getWidth();
  var H = pdf.internal.pageSize.getHeight();
  for (var i = 1; i <= total; i++) {
    pdf.setPage(i);
    if (flagURI) { try { pdf.addImage(flagURI, 'JPEG', 14, 4, 18, 14); } catch(e) {} }
    pdf.setFontSize(11); pdf.setFont('helvetica', 'bold'); pdf.setTextColor(30, 41, 59);
    pdf.text('FEDERAL DEMOCRATIC REPUBLIC OF ETHIOPIA', W/2, 10, {align:'center'});
    pdf.setFontSize(8); pdf.setFont('helvetica', 'normal'); pdf.setTextColor(100, 116, 139);
    pdf.text('Ministry of Education', W/2, 15, {align:'center'});
    if (minisURI) { try { pdf.addImage(minisURI, 'PNG', W-32, 2, 18, 18); } catch(e) {} }
    pdf.setDrawColor(200); pdf.setLineWidth(0.3); pdf.line(14, 24, W-14, 24);
    pdf.setTextColor(210); pdf.setFontSize(48); pdf.setFont('helvetica', 'bold');
    pdf.text('EDUNEX', W/2, H/2, {align:'center', angle:-30});
    pdf.setFontSize(12); pdf.setFont('helvetica', 'normal');
    pdf.text('www.henokakriso.com', W/2, H/2+14, {align:'center', angle:-30});
    pdf.setDrawColor(200); pdf.setLineWidth(0.3); pdf.line(14, H-12, W-14, H-12);
    pdf.setFontSize(8); pdf.setTextColor(150); pdf.setFont('helvetica', 'normal');
    pdf.text('EDUNEX LMS \u00b7 henockakriso.com \u00b7 ARWE-PL Licensed [<?= date("Y") ?>]', 14, H-7);
    pdf.text('Page ' + i + ' of ' + total, W-14, H-7, {align:'right'});
  }
}

async function downloadGradingPDF(url, filename) {
  var btn = event.target; btn.disabled = true; btn.textContent = '⏳ Generating...';
  try {
    var resp = await fetch(url);
    var html = await resp.text();
    var parser = new DOMParser();
    var doc = parser.parseFromString(html, 'text/html');
    var paper = doc.getElementById('pdf-content');
    if (!paper) { alert('PDF content not found'); return; }
    var container = document.createElement('div');
    container.style.cssText = 'position:fixed;left:-9999px;top:0;width:1100px';
    container.appendChild(paper);
    document.body.appendChild(container);
    var imgs = await Promise.all([toDataURL(FLAG_URL, 'image/jpeg'), toDataURL(MINIS_URL, 'image/png')]);
    var opt = {
      margin: [28, 12, 16, 12], filename: filename,
      image: { type:'jpeg', quality:0.98 },
      html2canvas: { scale:2, useCORS:true },
      jsPDF: { unit:'mm', format:'a4', orientation:'landscape' },
      pagebreak: { mode:['avoid-all','css','legacy'] }
    };
    await html2pdf().set(opt).from(container).then(function(pdf) {
      stampEveryPage(pdf, imgs[0], imgs[1]);
      pdf.save(filename);
    });
    container.remove();
  } catch(e) { console.error(e); alert('PDF generation failed'); }
  finally { btn.disabled = false; btn.textContent = '⬇ Generate PDF'; }
}

function generateStudentReport() {
  var courseId = document.querySelector('#student-report-form [name=course]').value;
  if (!courseId) { alert('Select a course'); return; }
  downloadGradingPDF('<?= url("teacher/grading/pdf") ?>&type=student&course=' + courseId, 'student_report.pdf');
}
function generateClassReport() {
  var courseId = document.querySelector('#class-report-form [name=course]').value;
  if (!courseId) { alert('Select a course'); return; }
  downloadGradingPDF('<?= url("teacher/grading/pdf") ?>&type=class&course=' + courseId, 'class_report.pdf');
}
function generateExamReport() {
  var assessmentId = document.querySelector('#exam-report-form [name=assessment]').value;
  if (!assessmentId) { alert('Select an assessment'); return; }
  downloadGradingPDF('<?= url("teacher/grading/pdf") ?>&type=exam&id=' + assessmentId, 'exam_report.pdf');
}
function generateTeacherReport() {
  downloadGradingPDF('<?= url("teacher/grading/pdf") ?>&type=teacher', 'teacher_summary.pdf');
}
</script>
