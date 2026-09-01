<?php /* Student grades view — grouped by grade/level and semester */
$group = function (array $rows): array {
    $out = [];
    foreach ($rows as $r) {
        $level = $r['course_level'] ?: 'General';
        $sem = $r['semester'] ?? 'Other';
        $out[$level][$sem][] = $r;
    }
    ksort($out);
    return $out;
};
$examsBy = $group($exams);
$assignsBy = $group($assigns);
?>
<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> My Grades</h1>
    <p class="sub">Exam scores, assignment results and course progress — grouped by grade level and semester</p>
  </div>
  <a class="btn btn-sm" href="<?= url('student/grades&print=1') ?>" target="_blank"><?= icon('printer') ?> Print Report Card</a>
</div>

<?php foreach ($examsBy as $level => $sems): foreach ($sems as $sem => $rows): ?>
  <div class="card" style="margin-top:18px">
    <h3 class="card-title" style="margin-top:0"><?= icon('note') ?> <?= e($level) ?> · <?= e($sem) ?> <span class="badge badge-muted">Exams</span></h3>
    <?php foreach ($rows as $ex): $pct = $ex['total_points'] > 0 ? round($ex['score'] / $ex['total_points'] * 100) : 0; ?>
      <a class="list-row" href="<?= e(url('student/grades/subject&id=' . (int)$ex['course_id'])) ?>" style="padding:10px 0;color:inherit;text-decoration:none;border-radius:8px;display:flex;align-items:center;gap:10px" onmouseover="this.style.background='color-mix(in srgb,var(--accent) 6%,transparent)'" onmouseout="this.style.background='transparent'">
        <div class="flex-1">
          <b class="small"><?= e($ex['title']) ?></b>
          <p class="tiny faint"><?= e($ex['course_title']) ?> · <?= e(date('M j, Y', strtotime($ex['submitted_at']))) ?></p>
        </div>
        <div class="flex gap-8" style="align-items:center">
          <div class="progress" style="width:90px"><div style="width:<?= $pct ?>%;background:<?= $pct >= (float)$ex['passing_score'] ? 'var(--success)' : 'var(--danger)' ?>"></div></div>
          <b class="small"><?= rtrim(rtrim((string)$ex['score'], '0'), '.') ?>/<?= rtrim(rtrim((string)$ex['total_points'], '0'), '.') ?></b>
          <span class="tiny faint" title="View subject history"><?= icon('arrow-right') ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endforeach; endforeach; ?>
<?php if (!$exams): ?><div class="card"><h3 class="card-title" style="margin-top:0"><?= icon('note') ?> Exam results</h3><p class="muted small">No graded exams yet.</p></div><?php endif; ?>

<?php foreach ($assignsBy as $level => $sems): foreach ($sems as $sem => $rows): ?>
  <div class="card" style="margin-top:18px">
    <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> <?= e($level) ?> · <?= e($sem) ?> <span class="badge badge-muted">Assignments</span></h3>
    <?php foreach ($rows as $a): ?>
      <a class="list-row" href="<?= e(url('student/grades/subject&id=' . (int)$a['course_id'])) ?>" style="padding:10px 0;color:inherit;text-decoration:none;border-radius:8px;display:flex;align-items:center;gap:10px" onmouseover="this.style.background='color-mix(in srgb,var(--accent) 6%,transparent)'" onmouseout="this.style.background='transparent'">
        <div class="flex-1">
          <b class="small"><?= e($a['title']) ?></b>
          <p class="tiny faint"><?= e($a['course_title']) ?><?= $a['feedback'] ? ' — ' . e($a['feedback']) : '' ?></p>
        </div>
        <b class="small"><?= rtrim(rtrim((string)$a['score'], '0'), '.') ?>/<?= rtrim(rtrim((string)$a['max_score'], '0'), '.') ?></b>
        <span class="tiny faint"><?= icon('arrow-right') ?></span>
      </a>
    <?php endforeach; ?>
  </div>
<?php endforeach; endforeach; ?>
<?php if (!$assigns): ?><div class="card" style="margin-top:18px"><h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Assignment grades</h3><p class="muted small">No graded assignments yet.</p></div><?php endif; ?>

<div class="card" style="margin-top:22px">
  <h3 class="card-title"><?= icon('books') ?> Course progress</h3>
  <?php foreach ($courses as $c): $pct = $c['total'] ? round($c['done'] / $c['total'] * 100) : 0; ?>
    <div class="list-row" style="padding:10px 0">
      <div class="flex-1"><b class="small"><?= e($c['title']) ?></b></div>
      <div class="flex gap-8" style="align-items:center">
        <div class="progress" style="width:160px"><div style="width:<?= (float)$c['progress'] ?>%"></div></div>
        <span class="small faint"><?= (float)$c['progress'] ?>%</span>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if (!empty($_GET['print'])): ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Report Card — <?= e($__u['first_name'] . ' ' . $__u['last_name']) ?></title>
<style>body{margin:0;padding:20px;background:var(--bg,#f5f5f5);color:var(--text,#222)}</style>
</head><body>
<?php
$pdf_title = 'Report Card — ' . $__u['first_name'] . ' ' . $__u['last_name'];
$pdf_subtitle = setting('site_name') ?? 'Edunex';
$pdf_filename = 'edunex_report_card_' . e($__u['student_id']) . '_' . date('Ymd') . '.pdf';
$pdf_doc_id = 'EDU-' . date('Y') . '-RC-' . str_pad($__u['id'], 4, '0', STR_PAD_LEFT);
$pdf_user_name = full_name($__u);
$pdf_orientation = 'portrait';
require_once BASE_PATH . '/includes/pdf_template.php';
?>
<div class="pdf-toolbar no-print">
<button class="pdf-toolbar-btn pdf-toolbar-btn--back" onclick="history.back()">← Back</button>
<button class="pdf-toolbar-btn pdf-toolbar-btn--dl" onclick="downloadPDF()">⬇ Download PDF</button>
<button class="pdf-toolbar-btn pdf-toolbar-btn--print" onclick="window.print()">🖨 Print</button>
</div>
<div class="pdf-paper" id="pdf-content">
<div class="pdf-watermark"><img class="wm-logo" src="<?= $pdf_logo_black ?>" alt="EDUNEX"><div class="wm-edunex">EDUNEX</div><div class="wm-url">www.henokakriso.com</div></div>
<div class="pdf-header"><div class="logos-row">
<div class="flag-wrap"><img class="logo-img flag-img" src="<?= $pdf_ethiopian_flag ?>" alt="Ethiopia"></div>
<div class="text-center"><h2>Federal Democratic Republic of Ethiopia</h2><div style="font-size:10px;color:var(--text-secondary);letter-spacing:.3px">Ministry of Education</div></div>
<div class="ministry-wrap"><img class="logo-img" src="<?= $pdf_ministry_logo ?>" alt="Ministry"></div>
</div><div class="pdf-sub">Report Card</div></div>
<div class="pdf-meta">
<span>Document: <strong><?= e($pdf_doc_id) ?></strong></span>
<span class="meta-dot"></span>
<span>Generated: <strong><?= e(date('F j, Y')) ?></strong></span>
<span class="meta-dot"></span>
<span>Student: <strong><?= e($__u['first_name'] . ' ' . $__u['last_name']) ?></strong></span>
<span class="meta-dot"></span>
<span>ID: <strong><?= e($__u['student_id']) ?></strong></span>
</div>
<?php foreach ($examsBy as $level => $sems): foreach ($sems as $sem => $rows): ?>
<div class="pdf-section"><h3><?= e($level) ?> · <?= e($sem) ?> — Exams</h3>
<table><thead><tr><th>#</th><th>Exam</th><th>Course</th><th>Score</th><th>Date</th></tr></thead><tbody>
<?php $rn=0; foreach ($rows as $ex): $rn++; $pct = $ex['total_points'] > 0 ? round($ex['score'] / $ex['total_points'] * 100) : 0; ?>
<tr><td><?= $rn ?></td><td><?= e($ex['title']) ?></td><td><?= e($ex['course_title']) ?></td>
<td><?= rtrim(rtrim((string)$ex['score'],'0'),'.') ?>/<?= rtrim(rtrim((string)$ex['total_points'],'0'),'.') ?> (<?= $pct ?>%)</td>
<td style="color:var(--text-secondary)"><?= e(date('M j, Y', strtotime($ex['submitted_at']))) ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endforeach; endforeach; ?>
<?php foreach ($assignsBy as $level => $sems): foreach ($sems as $sem => $rows): ?>
<div class="pdf-section"><h3><?= e($level) ?> · <?= e($sem) ?> — Assignments</h3>
<table><thead><tr><th>#</th><th>Assignment</th><th>Course</th><th>Score</th><th>Feedback</th></tr></thead><tbody>
<?php $rn=0; foreach ($rows as $a): $rn++; ?>
<tr><td><?= $rn ?></td><td><?= e($a['title']) ?></td><td><?= e($a['course_title']) ?></td>
<td><?= rtrim(rtrim((string)$a['score'],'0'),'.') ?>/<?= rtrim(rtrim((string)$a['max_score'],'0'),'.') ?></td>
<td style="color:var(--text-secondary)"><?= e($a['feedback'] ?: '—') ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endforeach; endforeach; ?>
<?php if ($courses): ?>
<div class="pdf-section"><h3>Course Progress</h3>
<table><thead><tr><th>#</th><th>Course</th><th>Progress</th></tr></thead><tbody>
<?php $rn=0; foreach ($courses as $c): $rn++; ?>
<tr><td><?= $rn ?></td><td><?= e($c['title']) ?></td>
<td><div class="pdf-progress"><div class="pdf-progress-fill" style="width:<?= (float)$c['progress'] ?>%"></div></div> <?= (float)$c['progress'] ?>%</td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>
<div class="pdf-footer"><span>EDUNEX LMS · henockakriso.com · GitHub @henokakriso · ARWE-PL Licensed [<?= date('Y') ?>]</span><span>Page 1 of 1</span></div>
</div>
</body></html>
<?php endif; ?>
