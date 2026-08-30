<?php /* Report view page — styled HTML with html2pdf.js for PDF download/print */
$typeLabels = [
    'education_performance' => 'Education Performance Report',
    'enrollment_stats' => 'Enrollment Statistics Report',
    'academic_performance' => 'Academic Performance Report',
    'attendance_participation' => 'Attendance & Participation Report',
    'national_exam' => 'National Exam Performance Report',
    'school_performance' => 'School Performance Report',
    'teacher_workforce' => 'Teacher Workforce Statistics Report',
    'course_curriculum' => 'Course & Curriculum Analytics Report',
    'learning_activity' => 'Learning Activity Report',
    'student_progress' => 'Student Progress Report',
    'regional_education' => 'Regional Education Report',
    'institution_stats' => 'Institution Statistics Report',
    'digital_platform' => 'Digital Platform Usage Report',
    'compliance' => 'Compliance Report',
    'annual_education' => 'Annual Education Report',
    'system_activity' => 'System Activity Report',
];
$label = $typeLabels[$report['type']] ?? ucfirst(str_replace('_', ' ', $report['type'])) . ' Report';
$logoBlack = url('public/images/logo-black.jpeg');
$logoWhite = url('public/images/logo-white.jpeg');
$ministryLogo = url('public/images/ministry-logo.png');
$ethiopianFlag = url('public/images/ethiopian-flag.jpeg');
?>
<style>
/* ── Report page ───────────────────────────────────── */
.report-view{max-width:1100px;margin:0 auto}

/* ── Toolbar ───────────────────────────────────────── */
.rv-toolbar{display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap}
.rv-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 24px;border-radius:14px;font-size:13px;font-weight:600;
  border:none;cursor:pointer;transition:all .2s ease;
  text-decoration:none;line-height:1.1;white-space:nowrap;
}
.rv-btn:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
/* Back */
.rv-btn--back{
  background:rgba(255,255,255,.5);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  color:var(--text-secondary);border:1px solid rgba(255,255,255,.5);
  box-shadow:0 2px 8px rgba(0,0,0,.04);
}
.rv-btn--back:hover{background:rgba(255,255,255,.7);color:var(--text)}
[data-theme="dark"] .rv-btn--back{background:rgba(30,41,59,.5);border-color:rgba(255,255,255,.08)}
[data-theme="dark"] .rv-btn--back:hover{background:rgba(30,41,59,.7)}
/* Download */
.rv-btn--dl{
  background:linear-gradient(135deg,#6366f1 0%,#818cf8 100%);color:#fff;
  box-shadow:0 4px 16px rgba(99,102,241,.35);
}
.rv-btn--dl:hover{box-shadow:0 6px 24px rgba(99,102,241,.5);transform:translateY(-1px)}
.rv-btn--dl:active{transform:translateY(0);box-shadow:0 2px 8px rgba(99,102,241,.3)}
/* Print */
.rv-btn--print{
  background:rgba(255,255,255,.5);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  color:var(--accent);border:1px solid rgba(99,102,241,.2);
  box-shadow:0 2px 8px rgba(0,0,0,.04);
}
.rv-btn--print:hover{background:rgba(99,102,241,.06);border-color:transparent;box-shadow:0 0 0 1px rgba(255,255,255,.15),inset 0 1px 1px rgba(255,255,255,.2),0 4px 12px rgba(0,0,0,.06);backdrop-filter: blur(16px) saturate(140%); -webkit-backdrop-filter: blur(16px) saturate(140%)}
[data-theme="dark"] .rv-btn--print{background:rgba(30,41,59,.5);border-color:rgba(99,102,241,.15)}
.rv-btn svg{width:16px;height:16px;flex-shrink:0}

/* ── Paper (Apple glassmorphism) ──────────────────── */
.report-paper{
  position:relative;overflow:hidden;
  background:rgba(255,255,255,.55);
  backdrop-filter:blur(20px) saturate(180%);
  -webkit-backdrop-filter:blur(20px) saturate(180%);
  border:1px solid rgba(255,255,255,.6);
  border-radius:20px;
  padding:36px 40px 28px;margin-bottom:20px;
  box-shadow:
    0 8px 32px rgba(0,0,0,.06),
    inset 0 1px 0 rgba(255,255,255,.7);
}
[data-theme="dark"] .report-paper{
  background:rgba(30,41,59,.55);
  border-color:rgba(255,255,255,.08);
  box-shadow:
    0 8px 32px rgba(0,0,0,.25),
    inset 0 1px 0 rgba(255,255,255,.06);
}

/* ── Watermark (screen only) ───────────────────────── */
.rv-watermark{
  position:absolute;top:50%;left:50%;
  transform:translate(-50%,-50%) rotate(-30deg);
  text-align:center;pointer-events:none;
  opacity:.08;z-index:0;
  -webkit-user-select:none;user-select:none;
  display:flex;flex-direction:column;align-items:center;gap:6px;
}
.rv-watermark .wm-logo{height:140px;object-fit:contain;opacity:1;filter:grayscale(1)}
.rv-watermark .wm-edunex{
  font-size:36px;font-weight:900;letter-spacing:8px;
  color:var(--text,#1e293b);opacity:1;text-transform:uppercase;
}
.rv-watermark .wm-url{
  font-size:14px;font-weight:500;letter-spacing:2px;
  color:var(--text-secondary);opacity:1;
}
[data-theme="dark"] .rv-watermark .wm-logo{filter:brightness(10) grayscale(1)}

/* ── Header ────────────────────────────────────────── */
.rp-header{
  position:relative;z-index:1;
  text-align:center;border-bottom:1px solid rgba(0,0,0,.06);
  padding-bottom:20px;margin-bottom:22px;
}
[data-theme="dark"] .rp-header{border-bottom-color:rgba(255,255,255,.06)}
.rp-header .logos-row{
  display:grid;grid-template-columns:1fr auto 1fr;align-items:center;
  margin-bottom:8px;
}
.rp-header .logos-row .flag-wrap{justify-self:start}
.rp-header .logos-row .text-center{text-align:center;padding:0 20px}
.rp-header .logos-row .ministry-wrap{justify-self:end}
.rp-header .logo-img{height:60px;object-fit:contain}
.rp-header .flag-img{height:50px;border-radius:4px;object-fit:cover}
.rp-header h2{margin:0 0 4px;font-size:1.05rem;text-transform:uppercase;letter-spacing:.6px}
.rp-header .rp-sub{font-size:13px;color:var(--text-secondary);font-weight:500;margin-top:4px}

/* ── Meta row ──────────────────────────────────────── */
.rp-meta{
  position:relative;z-index:1;
  display:flex;gap:28px;font-size:12px;color:var(--text-secondary);
  margin-bottom:18px;flex-wrap:wrap;
}
.rp-meta span{display:inline-flex;align-items:center;gap:5px}
.rp-meta .meta-dot{width:4px;height:4px;border-radius:50%;background:var(--accent);opacity:.5}

/* ── Table ─────────────────────────────────────────── */
.report-paper table{position:relative;z-index:1;width:100%;border-collapse:separate;border-spacing:0;font-size:12.5px}
.report-paper thead th{
  background:rgba(99,102,241,.08);color:var(--accent);font-weight:600;
  text-align:left;padding:10px 14px;border-bottom:2px solid rgba(99,102,241,.2);white-space:nowrap;
}
.report-paper tbody td{padding:9px 14px;border-bottom:1px solid rgba(0,0,0,.04);color:var(--text)}
.report-paper tbody tr{transition:background .15s}
.report-paper tbody tr:hover{background: var(--glass-nav-hover); backdrop-filter: blur(16px) saturate(140%); -webkit-backdrop-filter: blur(16px) saturate(140%);}
.report-paper tbody tr:nth-child(even){background:rgba(99,102,241,.02)}
[data-theme="dark"] .report-paper tbody td{border-bottom-color:rgba(255,255,255,.04)}
[data-theme="dark"] .report-paper thead th{background:rgba(99,102,241,.12);border-bottom-color:rgba(99,102,241,.15)}

/* ── Footer ────────────────────────────────────────── */
.rp-footer{
  position:relative;z-index:1;
  border-top:1px solid rgba(0,0,0,.06);padding-top:14px;margin-top:22px;
  display:flex;justify-content:space-between;font-size:11px;color:var(--text-secondary);
}
[data-theme="dark"] .rp-footer{border-top-color:rgba(255,255,255,.06)}

/* ── Print overrides ───────────────────────────────── */
@media print{
  .rv-toolbar,.no-print{display:none!important}
  .report-paper{
    background:#fff!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important;
    border:none!important;border-radius:0!important;padding:0!important;margin:0!important;
    box-shadow:none!important;
  }
  body{background:#fff!important;color:#000!important}
  .report-paper thead th{background:#e5e7eb!important;color:#111!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .report-paper tbody tr:nth-child(even){background:#f9fafb!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .rv-watermark{opacity:.03!important}
}
</style>

<div class="report-view">

  <!-- ── Toolbar ──────────────────────────────────── -->
  <div class="rv-toolbar no-print">
    <a class="rv-btn rv-btn--back" href="<?= e(url('admin/reports')) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
      Back to Reports
    </a>
    <button class="rv-btn rv-btn--dl" onclick="downloadPDF()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Download PDF
    </button>
    <button class="rv-btn rv-btn--print" onclick="window.print()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Print
    </button>
  </div>

  <!-- ── Report paper ─────────────────────────────── -->
  <div class="report-paper" id="report-content">

    <!-- Watermark -->
    <div class="rv-watermark">
      <img class="wm-logo" src="<?= $logoBlack ?>" alt="EDUNEX">
      <div class="wm-edunex">EDUNEX</div>
      <div class="wm-url">www.henokakriso.com</div>
    </div>

    <!-- Header with logos -->
    <div class="rp-header">
      <div class="logos-row">
        <div class="flag-wrap"><img class="logo-img flag-img" src="<?= $ethiopianFlag ?>" alt="Ethiopia"></div>
        <div class="text-center">
          <h2>Federal Democratic Republic of Ethiopia</h2>
          <div style="font-size:10px;color:var(--text-secondary);letter-spacing:.3px">Ministry of Education</div>
        </div>
        <div class="ministry-wrap"><img class="logo-img" src="<?= $ministryLogo ?>" alt="Ministry of Education"></div>
      </div>
      <div class="rp-sub" style="margin-top:10px"><?= e($label) ?></div>
    </div>

    <!-- Meta -->
    <div class="rp-meta">
      <span>Document: <strong><?= e($report['id'] ? 'EDU-' . date('Y') . '-' . str_pad($report['id'], 6, '0', STR_PAD_LEFT) : '—') ?></strong></span>
      <span class="meta-dot"></span>
      <span>Generated: <strong><?= e(date('M j, Y g:i A', strtotime($report['created_at']))) ?></strong></span>
      <span class="meta-dot"></span>
      <span>By: <strong><?= e($report['user_name']) ?></strong></span>
    </div>

    <!-- Data table -->
    <?php if ($headers && $rows): ?>
    <div style="overflow-x:auto">
      <table>
        <thead><tr>
          <?php foreach ($headers as $h): ?><th><?= e($h) ?></th><?php endforeach; ?>
        </tr></thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($row as $k => $v): ?>
              <td><?= e($v ?? '—') ?></td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <p style="text-align:center;color:var(--text-secondary);padding:40px 0;position:relative;z-index:1">No data available for this report.</p>
    <?php endif; ?>

    <!-- Footer -->
    <div class="rp-footer">
      <span>EDUNEX LMS &middot; henockakriso.com &middot; GitHub @henokakriso &middot; ARWE-PL Licensed [<?= date('Y') ?>]</span>
      <span>Page 1 of 1</span>
    </div>
  </div>
</div>

<!-- html2pdf.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF(){
  var el = document.getElementById('report-content');
  var opt = {
    margin:      [12, 12],
    filename:    <?= json_encode(preg_replace("/[^a-zA-Z0-9_-]/", "_", $report['title']) . '.pdf') ?>,
    image:       { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF:       { unit: 'mm', format: 'a4', orientation: 'landscape' }
  };
  html2pdf().set(opt).from(el).save();
}
</script>
