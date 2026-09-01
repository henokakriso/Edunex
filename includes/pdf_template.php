<?php
/**
 * Shared PDF template — glassmorphism style with logo, watermark, and license.
 * Include this file in any PDF viewer page.
 *
 * Variables available after include:
 *   $pdf_title     — Document title (string)
 *   $pdf_subtitle  — Subtitle below title (string)
 *   $pdf_doc_id    — Document ID like EDU-2026-000001 (string)
 *   $pdf_stamp     — Generated date string (string)
 *   $pdf_filename  — Download filename (string)
 *   $pdf_record_count — Number of records (int)
 *   $pdf_user_name — Who generated it (string)
 *   $pdf_orientation — 'landscape' or 'portrait' (string)
 */
$pdf_logo_black = url('public/images/logo-black.jpeg');
$pdf_logo_white = url('public/images/logo-white.jpeg');
$pdf_ministry_logo = url('public/images/ministry-logo.png');
$pdf_ethiopian_flag = url('public/images/ethiopian-flag.jpeg');
$pdf_doc_id = $pdf_doc_id ?? 'EDU-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
$pdf_stamp = $pdf_stamp ?? date('F j, Y \a\t g:i A');
$pdf_filename = $pdf_filename ?? 'edunex_report_' . date('Ymd_His') . '.pdf';
$pdf_orientation = $pdf_orientation ?? 'landscape';
$pdf_user_name = $pdf_user_name ?? full_name($__u ?? []);
?>
<style>
/* ── PDF Viewer ─────────────────────────────────── */
.pdf-viewer{max-width:1100px;margin:0 auto}

/* ── Toolbar ────────────────────────────────────── */
.pdf-toolbar{display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap}
.pdf-toolbar-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 24px;border-radius:14px;font-size:13px;font-weight:600;
  border:none;cursor:pointer;transition:all .2s ease;
  text-decoration:none;line-height:1.1;white-space:nowrap;
}
.pdf-toolbar-btn:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
.pdf-toolbar-btn--back{
  background:rgba(255,255,255,.5);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  color:var(--text-secondary);border:1px solid rgba(255,255,255,.5);
  box-shadow:0 2px 8px rgba(0,0,0,.04);
}
.pdf-toolbar-btn--back:hover{background:rgba(255,255,255,.7);color:var(--text)}
[data-theme="dark"] .pdf-toolbar-btn--back{background:rgba(30,41,59,.5);border-color:rgba(255,255,255,.08)}
.pdf-toolbar-btn--dl{
  background:linear-gradient(135deg,#6366f1 0%,#818cf8 100%);color:#fff;
  box-shadow:0 4px 16px rgba(99,102,241,.35);
}
.pdf-toolbar-btn--dl:hover{box-shadow:0 6px 24px rgba(99,102,241,.5);transform:translateY(-1px)}
.pdf-toolbar-btn--dl:active{transform:translateY(0);box-shadow:0 2px 8px rgba(99,102,241,.3)}
.pdf-toolbar-btn--print{
  background:rgba(255,255,255,.5);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  color:var(--accent);border:1px solid rgba(99,102,241,.2);
  box-shadow:0 2px 8px rgba(0,0,0,.04);
}
.pdf-toolbar-btn--print:hover{background:rgba(99,102,241,.06);border-color:transparent}
[data-theme="dark"] .pdf-toolbar-btn--print{background:rgba(30,41,59,.5);border-color:rgba(99,102,241,.15)}
.pdf-toolbar-btn svg{width:16px;height:16px;flex-shrink:0}

/* ── Paper (Apple glassmorphism) ────────────────── */
.pdf-paper{
  position:relative;overflow:hidden;
  background:rgba(255,255,255,.55);
  backdrop-filter:blur(20px) saturate(180%);
  -webkit-backdrop-filter:blur(20px) saturate(180%);
  border:1px solid rgba(255,255,255,.6);
  border-radius:20px;
  padding:36px 40px 28px;margin-bottom:20px;
  box-shadow:0 8px 32px rgba(0,0,0,.06),inset 0 1px 0 rgba(255,255,255,.7);
}
[data-theme="dark"] .pdf-paper{
  background:rgba(30,41,59,.55);
  border-color:rgba(255,255,255,.08);
  box-shadow:0 8px 32px rgba(0,0,0,.25),inset 0 1px 0 rgba(255,255,255,.06);
}

/* ── Watermark ──────────────────────────────────── */
.pdf-watermark{
  position:absolute;top:50%;left:50%;
  transform:translate(-50%,-50%) rotate(-30deg);
  text-align:center;pointer-events:none;
  opacity:.08;z-index:0;
  -webkit-user-select:none;user-select:none;
  display:flex;flex-direction:column;align-items:center;gap:6px;
}
.pdf-watermark .wm-logo{height:140px;object-fit:contain;filter:grayscale(1)}
.pdf-watermark .wm-edunex{font-size:36px;font-weight:900;letter-spacing:8px;color:#1e293b;text-transform:uppercase}
.pdf-watermark .wm-url{font-size:14px;font-weight:500;letter-spacing:2px;color:#64748b}

/* ── Header ─────────────────────────────────────── */
.pdf-header{
  position:relative;z-index:1;
  text-align:center;border-bottom:1px solid rgba(0,0,0,.06);
  padding-bottom:20px;margin-bottom:22px;
}
[data-theme="dark"] .pdf-header{border-bottom-color:rgba(255,255,255,.06)}
.pdf-header .logos-row{
  display:grid;grid-template-columns:1fr auto 1fr;align-items:center;
  margin-bottom:8px;
}
.pdf-header .logos-row .flag-wrap{justify-self:start}
.pdf-header .logos-row .text-center{text-align:center;padding:0 20px}
.pdf-header .logos-row .ministry-wrap{justify-self:end}
.pdf-header .logo-img{height:60px;object-fit:contain}
.pdf-header .flag-img{height:50px;border-radius:4px;object-fit:cover}
.pdf-header h2{margin:0 0 4px;font-size:1.05rem;text-transform:uppercase;letter-spacing:.6px}
.pdf-header .pdf-sub{font-size:13px;color:var(--text-secondary);font-weight:500;margin-top:4px}

/* ── Meta row ───────────────────────────────────── */
.pdf-meta{
  position:relative;z-index:1;
  display:flex;gap:28px;font-size:12px;color:var(--text-secondary);
  margin-bottom:18px;flex-wrap:wrap;
}
.pdf-meta span{display:inline-flex;align-items:center;gap:5px}
.pdf-meta .meta-dot{width:4px;height:4px;border-radius:50%;background:var(--accent);opacity:.5}

/* ── Table ──────────────────────────────────────── */
.pdf-paper table{position:relative;z-index:1;width:100%;border-collapse:separate;border-spacing:0;font-size:12.5px}
.pdf-paper thead th{
  background:rgba(99,102,241,.08);color:var(--accent);font-weight:600;
  text-align:left;padding:10px 14px;border-bottom:2px solid rgba(99,102,241,.2);white-space:nowrap;
}
.pdf-paper tbody td{padding:9px 14px;border-bottom:1px solid rgba(0,0,0,.04);color:var(--text)}
.pdf-paper tbody tr:nth-child(even){background:rgba(99,102,241,.02)}
[data-theme="dark"] .pdf-paper tbody td{border-bottom-color:rgba(255,255,255,.04)}
[data-theme="dark"] .pdf-paper thead th{background:rgba(99,102,241,.12);border-bottom-color:rgba(99,102,241,.15)}

/* ── Footer ─────────────────────────────────────── */
.pdf-footer{
  position:relative;z-index:1;
  border-top:1px solid rgba(0,0,0,.06);padding-top:14px;margin-top:22px;
  display:flex;justify-content:space-between;font-size:11px;color:var(--text-secondary);
}
[data-theme="dark"] .pdf-footer{border-top-color:rgba(255,255,255,.06)}

/* ── Section headers inside paper ───────────────── */
.pdf-section{position:relative;z-index:1;padding:0 0 12px}
.pdf-section h3{font-size:15px;margin-bottom:12px;color:var(--accent)}

/* ── Progress bars ──────────────────────────────── */
.pdf-progress{height:8px;background:rgba(99,102,241,.1);border-radius:4px;overflow:hidden;width:120px;display:inline-block;vertical-align:middle;margin-right:6px}
.pdf-progress-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,#6366f1,#818cf8)}

/* ── Print overrides ────────────────────────────── */
@media print{
  .pdf-toolbar,.no-print{display:none!important}
  .pdf-paper{
    background:#fff!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important;
    border:none!important;border-radius:0!important;padding:24px 32px!important;margin:0!important;
    box-shadow:none!important;
  }
  body{background:#fff!important;color:#000!important}
  .pdf-paper thead th{background:#e5e7eb!important;color:#111!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .pdf-paper tbody tr:nth-child(even){background:#f9fafb!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .pdf-watermark{opacity:.03!important}
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF(){
  var el = document.getElementById('pdf-content');
  var fname = <?= json_encode($pdf_filename) ?>;
  var orientation = <?= json_encode($pdf_orientation) ?>;
  var docId = <?= json_encode($pdf_doc_id) ?>;
  var stamp = <?= json_encode($pdf_stamp) ?>;
  var footerLeft = 'EDUNEX LMS \u00b7 henockakriso.com \u00b7 ARWE-PL Licensed [<?= date('Y') ?>]';

  var opt = {
    margin: [12, 12, 18, 12],
    filename: fname,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: 'mm', format: 'a4', orientation: orientation },
    pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
  };

  html2pdf().set(opt).from(el).then(function(pdf) {
    var total = pdf.internal.getNumberOfPages();
    var w = pdf.internal.pageSize.getWidth();
    var h = pdf.internal.pageSize.getHeight();
    for (var i = 1; i <= total; i++) {
      pdf.setPage(i);
      /* Watermark on every page */
      pdf.setTextColor(200);
      pdf.setFontSize(42);
      pdf.setFont('helvetica', 'bold');
      pdf.text('EDUNEX', w / 2, h / 2, { align: 'center', angle: -30 });
      pdf.setFontSize(10);
      pdf.setFont('helvetica', 'normal');
      pdf.text('www.henokakriso.com', w / 2, h / 2 + 12, { align: 'center', angle: -30 });
      /* Footer on every page */
      pdf.setFontSize(8);
      pdf.setTextColor(150);
      pdf.text(footerLeft, 12, h - 8);
      pdf.text('Page ' + i + ' of ' + total, w - 12, h - 8, { align: 'right' });
      /* Header line on every page */
      pdf.setDrawColor(220);
      pdf.line(12, 12, w - 12, 12);
    }
    pdf.save(fname);
  });
}
</script>
