<?php /* Student schedule view */
$typeMeta = [
    'exam' => ['label' => 'Exam', 'icon' => icon('note'), 'cls' => 'badge-danger'],
    'assignment' => ['label' => 'Assignment due', 'icon' => icon('doc'), 'cls' => 'badge-warning'],
    'class' => ['label' => 'Class', 'icon' => icon('school'), 'cls' => 'badge-accent'],
    'office_hours' => ['label' => 'Office hours', 'icon' => icon('clock'), 'cls' => 'badge-success'],
    'event' => ['label' => 'Event', 'icon' => icon('spark'), 'cls' => 'badge-muted'],
];
$byDay = [];
foreach ($all as $ev) {
    $day = date('Y-m-d', strtotime($ev['event_date']));
    $byDay[$day][] = $ev;
}
ksort($byDay);
?>
<div class="page-head">
  <div>
    <h1><?= icon('calendar') ?> My Schedule</h1>
    <p class="sub">Classes, exams and deadlines</p>
  </div>
</div>

<div class="flex-col gap-16">
  <?php foreach ($byDay as $day => $evs): ?>
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= e(date('l, M j', strtotime($day))) ?></h3>
      <?php foreach ($evs as $ev): $m = $typeMeta[$ev['event_type']] ?? $typeMeta['event']; ?>
        <div class="list-row" style="padding:10px 0">
          <span style="font-size:1.2rem"><?= $m['icon'] ?></span>
          <div class="flex-1">
            <b class="small"><?= e($ev['title']) ?></b>
            <p class="tiny faint"><?= e($ev['course_title'] ?? '') ?> · <?= e($ev['location'] ?? '') ?></p>
          </div>
          <span class="badge <?= $m['cls'] ?>"><?= $m['label'] ?></span>
          <span class="small" style="width:90px;text-align:right">
            <?= $ev['start_time'] ? e(date('H:i', strtotime($ev['start_time']))) : (str_contains($ev['event_type'], 'assignment') ? 'Before ' . e(date('H:i', strtotime($ev['event_date']))) : 'All day') ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  <?php if (!$byDay): ?><div class="alert alert-info">Nothing scheduled. Enjoy the quiet!</div><?php endif; ?>
</div>
