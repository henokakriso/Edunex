<?php /* Printable certificate view */
$gradeText = match (strtoupper((string)($cert['grade'] ?: 'A'))) {
    'A+' => 'A+ (Excellent)', 'A' => 'A (Excellent)', 'A-' => 'A- (Very Good)',
    'B+' => 'B+ (Very Good)', 'B' => 'B (Good)', 'C' => 'C (Satisfactory)', 'D' => 'D (Pass)',
    default => ($cert['grade'] ?: 'A') . ' (Pass)',
};
?>
<div class="page-head">
  <div>
    <h1><?= icon('graduation') ?> Certificate</h1>
    <p class="sub">Print-friendly · verify at <a href="<?= e(url('certificates/verify')) ?>" class="accent">Verify</a></p>
  </div>
  <button class="btn btn-primary" onclick="window.print()"><?= icon('printer') ?> Print</button>
</div>

<div class="card cert-doc" id="cert-doc">
  <div class="cert-doc-inner">
    <div class="cert-seal"><?= icon('graduation') ?></div>
    <p class="small faint mono" style="text-align:center"><?= e(setting('site_name') ?? 'Edunex') ?> · <?= e($cert['school_name']) ?></p>
    <h1 class="cert-title">CERTIFICATE OF COMPLETION</h1>
    <p class="muted small" style="text-align:center">This is to certify that</p>
    <h2 class="cert-name"><?= e($cert['first_name'] . ' ' . $cert['last_name']) ?></h2>
    <p class="muted small" style="text-align:center">(<?= e($cert['student_id']) ?>)</p>
    <p class="muted small" style="text-align:center;margin-top:16px">has successfully completed the course</p>
    <h3 class="cert-course"><?= e($cert['course_title']) ?></h3>
    <p class="small faint" style="text-align:center">Course code: <?= e($cert['course_code']) ?></p>
    <p class="muted small" style="text-align:center;margin-top:14px">with an overall grade of</p>
    <p class="cert-grade"><?= e($gradeText) ?></p>
    <div class="cert-footer">
      <div>
        <p class="small" style="margin:0"><?= e($cert['school_name']) ?></p>
        <p class="tiny faint" style="margin:0">School Administrator</p>
      </div>
      <div style="text-align:right">
        <p class="small" style="margin:0"><?= e(date('F j, Y', strtotime($cert['issued_at']))) ?></p>
        <p class="tiny faint mono" style="margin:0"><?= e($cert['cert_code']) ?></p>
      </div>
    </div>
  </div>
</div>

<style>
.cert-doc { background:#fbf8f2; border:1px solid #e3d9c6; }
.cert-doc-inner { border:3px double #8a7a5a; border-radius:8px; padding:44px 36px; margin:10px; }
.cert-seal { font-size:56px; text-align:center; margin-bottom:6px; }
.cert-title { text-align:center; letter-spacing:3px; font-size:22px; margin:10px 0 18px; color:var(--accent); }
.cert-name { text-align:center; font-size:30px; margin:4px 0; }
.cert-course { text-align:center; font-size:20px; color:var(--accent); margin:4px 0 10px; }
.cert-grade { text-align:center; font-size:18px; color:var(--success); margin:6px 0 26px; font-weight:600; }
.cert-footer { display:flex; justify-content:space-between; border-top:1px solid #d8ccb4; padding-top:14px; margin-top:8px; }
@media print { body { background:#fff; } .topbar,.sidebar,.page-head,.toast-wrap { display:none !important; } .main-wrap { margin:0 !important; padding:0 !important; } .cert-doc { box-shadow:none; border:none; } }
</style>
