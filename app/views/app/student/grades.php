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
<style>
*{box-sizing:border-box;margin:0;padding:0}body{font-family:system-ui,-apple-system,sans-serif;background:#f5f5f5;color:#222}
.viewer-bar{position:sticky;top:0;z-index:100;background:#1a1a2e;color:#fff;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.viewer-bar h1{font-size:15px;font-weight:600}.viewer-bar .btns{display:flex;gap:10px}
.viewer-bar a,.viewer-bar button{background:#4361ee;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.viewer-bar a:hover,.viewer-bar button:hover{background:#3a56d4}.viewer-bar .btn-secondary{background:#555}.viewer-bar .btn-secondary:hover{background:#444}
.report{max-width:900px;margin:24px auto;background:#fff;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden}
.report-header{padding:28px 32px 20px;border-bottom:2px solid #eee;text-align:center}
.report-header h2{font-size:20px;margin-bottom:4px}.report-header .meta{color:#666;font-size:12px}
.section{padding:20px 32px}.section h3{font-size:15px;margin-bottom:12px;color:#4361ee}
table{width:100%;border-collapse:collapse}th,td{padding:8px 12px;text-align:left;border-bottom:1px solid #eee;font-size:12px}
th{background:#f8f9fa;font-weight:600;color:#444}.row-num{color:#999;width:36px;text-align:center}
.progress-cell{width:120px}.progress-bar{height:8px;background:#eee;border-radius:4px;overflow:hidden}.progress-fill{height:100%;border-radius:4px;background:#4361ee}
.footer{padding:16px 32px;border-top:2px solid #eee;text-align:center;color:#888;font-size:11px;line-height:1.6}
.footer a{color:#4361ee;text-decoration:none}
@media print{.viewer-bar{display:none!important}.report{box-shadow:none;margin:0;border-radius:0}body{background:#fff}}
</style></head><body>
<div class="viewer-bar"><h1>Report Card — <?= e($__u['first_name'] . ' ' . $__u['last_name']) ?></h1>
<div class="btns"><button class="btn-secondary" onclick="history.back()">Back</button><a href="javascript:window.print()">Print</a><a href="javascript:downloadPDF()">Download PDF</a></div></div>
<div class="report">
<div class="report-header">
<h2><?= e(setting('site_name') ?? 'Edunex') ?> — Report Card</h2>
<p class="meta">Student: <?= e($__u['first_name'] . ' ' . $__u['last_name']) ?> (<?= e($__u['student_id']) ?>) · Roll: <?= e($__u['student_id']) ?></p>
<p class="meta">Generated: <?= e(date('F j, Y')) ?></p>
</div>
<?php foreach ($examsBy as $level => $sems): foreach ($sems as $sem => $rows): ?>
<div class="section"><h3><?= e($level) ?> · <?= e($sem) ?> — Exams</h3>
<table><thead><tr><th class="row-num">#</th><th>Exam</th><th>Course</th><th>Score</th><th>Date</th></tr></thead><tbody>
<?php $rn=0; foreach ($rows as $ex): $rn++; $pct = $ex['total_points'] > 0 ? round($ex['score'] / $ex['total_points'] * 100) : 0; ?>
<tr><td class="row-num"><?= $rn ?></td><td><?= e($ex['title']) ?></td><td><?= e($ex['course_title']) ?></td>
<td><?= rtrim(rtrim((string)$ex['score'],'0'),'.') ?>/<?= rtrim(rtrim((string)$ex['total_points'],'0'),'.') ?> (<?= $pct ?>%)</td>
<td class="faint"><?= e(date('M j, Y', strtotime($ex['submitted_at']))) ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endforeach; endforeach; ?>
<?php foreach ($assignsBy as $level => $sems): foreach ($sems as $sem => $rows): ?>
<div class="section"><h3><?= e($level) ?> · <?= e($sem) ?> — Assignments</h3>
<table><thead><tr><th class="row-num">#</th><th>Assignment</th><th>Course</th><th>Score</th><th>Feedback</th></tr></thead><tbody>
<?php $rn=0; foreach ($rows as $a): $rn++; ?>
<tr><td class="row-num"><?= $rn ?></td><td><?= e($a['title']) ?></td><td><?= e($a['course_title']) ?></td>
<td><?= rtrim(rtrim((string)$a['score'],'0'),'.') ?>/<?= rtrim(rtrim((string)$a['max_score'],'0'),'.') ?></td>
<td class="faint"><?= e($a['feedback'] ?: '—') ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endforeach; endforeach; ?>
<?php if ($courses): ?>
<div class="section"><h3>Course Progress</h3>
<table><thead><tr><th class="row-num">#</th><th>Course</th><th>Progress</th></tr></thead><tbody>
<?php $rn=0; foreach ($courses as $c): $rn++; ?>
<tr><td class="row-num"><?= $rn ?></td><td><?= e($c['title']) ?></td>
<td><div class="progress-bar"><div class="progress-fill" style="width:<?= (float)$c['progress'] ?>%"></div></div> <?= (float)$c['progress'] ?>%</td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>
<div class="footer"><p><b>Henok Akriso</b> · henokakriso.com</p><p>All system is opensourced under <a href="https://github.com/henokakriso/Edunex" target="_blank">ARWE-PL License</a></p></div>
</div>
<script>
function downloadPDF(){var opt={margin:[10,10],filename:"edunex_report_card_<?= e($__u['student_id']) ?>_<?= date('Ymd') ?>.pdf",html2canvas:{scale:2},jsPDF:{unit:"mm",format:"a4",orientation:"portrait"}};if(typeof html2pdf!=="undefined"){html2pdf().set(opt).from(document.querySelector(".report")).save();}else{var s=document.createElement("script");s.src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js";s.onload=function(){html2pdf().set(opt).from(document.querySelector(".report")).save();};document.head.appendChild(s);}}
</script></body></html>
<?php endif; ?>
