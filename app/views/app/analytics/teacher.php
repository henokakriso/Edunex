<?php /* Teacher/Director analytics view — C-powered, interactive */
  $analysis = $analysis ?? [];
  $aseries  = $analysis['series'] ?? [];
  $aitems   = $analysis['items'] ?? [];
  $growth   = (float)($aseries['growth'] ?? 0);
  $gUp      = $growth > 0.05; $gDown = $growth < -0.05;
  $pctBy    = array_column($aitems, 'pct', 'label');
  $totStudents = (int)array_sum(array_map('intval', array_column($courses, 'students')));
  $totLessons  = (int)array_sum(array_map('intval', array_column($courses, 'lessons')));
  $totGraded   = (int)array_sum(array_map('intval', array_column($courses, 'graded')));
  $totAvg      = $courses ? (int)round(array_sum(array_map('floatval', array_column($courses, 'avg_progress'))) / count($courses)) : 0;
  $colors      = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)', 'var(--chart-6)'];
  $studentsPg  = $is_director ? 'director/students' : 'teacher/students';
  $examsPg     = $is_director ? 'courses' : 'teacher/exams';
?>
<style>
  .an-grid { display: grid; gap: 18px; }
  .an-2 { grid-template-columns: 1.6fr 1fr; }
  .an-2b { grid-template-columns: 1.3fr 1fr; margin-top: 18px; }
  @media (max-width: 980px) { .an-2, .an-2b { grid-template-columns: 1fr; } }
  .an-row { display: flex; align-items: center; gap: 12px; padding: 13px 0; border-bottom: 1px solid var(--border); }
  .an-row:last-child { border-bottom: none; padding-bottom: 2px; }
  .perf-row { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border: 1px solid var(--border); border-radius: 13px; background: var(--bg-elev); margin-bottom: 10px; transition: border-color .15s ease, transform .15s ease; text-decoration: none; color: inherit; }
  .perf-row:last-child { margin-bottom: 0; }
  .perf-row:hover { border-color: transparent; box-shadow: 0 0 0 1px rgba(255,255,255,.15), inset 0 1px 1px rgba(255,255,255,.2), 0 4px 12px rgba(0,0,0,.06); transform: translateY(-1px); }
  .perf-ic { width: 40px; height: 40px; border-radius: 11px; flex: none; display: inline-flex; align-items: center; justify-content: center; background: var(--accent-soft); color: var(--accent); }
  .perf-ic .ico { width: 19px; height: 19px; }
  .rank-row { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border: 1px solid var(--border); border-radius: 12px; background: var(--bg-elev); margin-bottom: 9px; transition: border-color .15s ease, transform .15s ease; text-decoration: none; color: inherit; }
  .rank-row:last-child { margin-bottom: 0; }
  .rank-row:hover { border-color: transparent; box-shadow: 0 0 0 1px rgba(255,255,255,.15), inset 0 1px 1px rgba(255,255,255,.2), 0 4px 12px rgba(0,0,0,.06); transform: translateX(2px); }
  .rank-badge { width: 26px; height: 26px; border-radius: 8px; flex: none; display: inline-flex; align-items: center; justify-content: center; font-size: 11.5px; font-weight: 800; }
  .rank-badge .ico { width: 14px; height: 14px; }
  .legend-dot { width: 10px; height: 10px; border-radius: 3px; display: inline-block; flex: none; }
  .legend-row { border-radius: 9px; transition: background .12s ease; padding: 5px 8px; margin: 0 -8px; text-decoration: none; color: inherit; }
  .legend-row:hover { background: var(--bg-hover); }
  .click-card { display: block; text-decoration: none; color: inherit; cursor: pointer; }
  .tstat-card .stat-text { display: flex; flex-direction: column; gap: 4px; min-width: 0; padding: 3px 0; }
  .tstat-card .stat-text b { font-size: 1.55rem; line-height: 1.1; letter-spacing: -.02em; color: var(--text); }
  .tstat-card .stat-text span { font-size: 12.5px; color: var(--text-dim); line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .enroll-row { display: flex; align-items: center; gap: 11px; padding: 10px 0; border-bottom: 1px solid var(--border); }
  .enroll-row:last-child { border-bottom: none; padding-bottom: 0; }
</style>

<div class="page-head">
  <div>
    <h1><?= icon('chart-bar') ?> <?= $is_director ? 'School Analytics' : 'Teacher Analytics' ?></h1>
    <p class="sub"><?= $is_director ? 'Whole-school performance — every course in your school' : 'Course performance and engagement — only your authorised subject courses' ?></p>
  </div>
</div>

<?php if (!$courses): ?>
  <div class="empty-state">
    <div class="empty-ic"><?= icon('chart-bar') ?></div>
    <h3>No analytics yet</h3>
    <p class="small"><?= $is_director ? 'Create courses first — charts and student results appear here.' : 'Your analytics cover the courses in the subjects assigned to you by the director. Ask the director to assign you subjects, then create courses — charts and student results appear here.' ?></p>
  </div>
<?php else: ?>

  <!-- Course-level stats (clickable) -->
  <div class="grid grid-4" style="margin-bottom:20px">
    <a class="card tstat-card click-card" href="<?= url('index.php?r=courses') ?>"><span class="stat-ico"><?= icon('users') ?></span>
      <div class="stat-text"><b><?= (int)$totStudents ?></b><span>Students in your courses</span></div></a>
    <a class="card tstat-card click-card" href="<?= url('index.php?r=courses') ?>"><span class="stat-ico"><?= icon('books') ?></span>
      <div class="stat-text"><b><?= (int)$totLessons ?></b><span>Lessons</span></div></a>
    <a class="card tstat-card click-card" href="<?= url('index.php?r=' . $examsPg) ?>"><span class="stat-ico"><?= icon('note') ?></span>
      <div class="stat-text"><b><?= (int)$totGraded ?></b><span>Exams graded</span></div></a>
    <a class="card tstat-card click-card" href="#performance"><span class="stat-ico"><?= icon('trend-up') ?></span>
      <div class="stat-text"><b><?= (int)$totAvg ?>%</b><span>Avg course progress</span></div></a>
  </div>

  <?php if ($is_director && !empty($extra['school'])): $s = $extra['school']; ?>
    <!-- School-wide stats (director only, clickable) -->
    <div class="grid grid-4" style="margin-bottom:20px">
      <a class="card tstat-card click-card" href="<?= url('index.php?r=director/students') ?>"><span class="stat-ico"><?= icon('users') ?></span>
        <div class="stat-text"><b><?= (int)$s['students'] ?></b><span>School students</span></div></a>
      <a class="card tstat-card click-card" href="<?= url('index.php?r=director/teachers') ?>"><span class="stat-ico"><?= icon('users-badge') ?></span>
        <div class="stat-text"><b><?= (int)$s['teachers'] ?></b><span>Teachers</span></div></a>
      <a class="card tstat-card click-card" href="<?= url('index.php?r=courses') ?>"><span class="stat-ico"><?= icon('courses') ?></span>
        <div class="stat-text"><b><?= (int)$s['courses'] ?></b><span>Courses</span></div></a>
      <a class="card tstat-card click-card" href="#performance"><span class="stat-ico"><?= icon('download') ?></span>
        <div class="stat-text"><b><?= (int)$s['enroll30'] ?></b><span>Enrollments — 30 days</span></div></a>
    </div>
  <?php endif; ?>

  <div class="an-grid an-2">
    <div class="card">
      <div class="flex-between" style="margin-bottom:2px">
        <h3 class="card-title" style="margin-top:0"><?= icon('download') ?> Enrollments — last 30 days</h3>
        <span class="badge <?= $gUp ? 'badge-success' : ($gDown ? 'badge-danger' : 'badge-info') ?>"><?= $gUp ? '▲ +' . $growth . '%' : ($gDown ? '▼ ' . $growth . '%' : '– stable') ?> vs first half</span>
      </div>
      <p class="small faint" style="margin-top:6px">New students joining your courses per day · dashed line = 3-day average <span class="badge" title="Derived stats computed by the native analytics engine (storage/bin/analytics_c)"><?= icon('chip') ?> native engine</span></p>
      <div id="enroll-chart"></div>
    </div>
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('users') ?> Students per course</h3>
      <?php if ($totStudents === 0): ?>
        <div class="empty-state" style="padding:28px 18px">
          <div class="empty-ic" style="width:46px;height:46px;margin-bottom:10px"><?= icon('users') ?></div>
          <h3 style="font-size:.95rem">No enrollments yet</h3>
          <p class="small">Students will appear here once they enroll in your courses.</p>
        </div>
      <?php else: ?>
        <div class="flex gap-16" style="align-items:center">
          <div id="donut-chart" style="width:110px;flex:none"></div>
          <div class="flex-col" style="flex:1;min-width:0">
            <div class="flex gap-8" style="align-items:center;padding:2px 8px 6px;border-bottom:1px solid var(--border)">
              <span class="tiny faint" style="flex:1">Course</span>
              <span class="tiny faint" style="width:40px;text-align:right">Students</span>
            </div>
            <?php foreach ($courses as $i => $c): ?>
              <a class="legend-row flex gap-8" href="<?= url('index.php?r=courses/view&id=' . (int)$c['id']) ?>" style="align-items:center">
                <span class="legend-dot" style="background:<?= $colors[$i % 6] ?>"></span>
                <span class="small" style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-dim)"><?= e($c['title']) ?></span>
                <b class="small" style="width:26px;text-align:right;font-variant-numeric:tabular-nums"><?= (int)$c['students'] ?></b>
                <span class="tiny faint" style="width:40px;text-align:right"><?= round((float)($pctBy[$c['title']] ?? 0), 1) ?>%</span>
              </a>
            <?php endforeach; ?>
            <div class="flex gap-8" style="align-items:center;padding:8px 8px 0;margin-top:8px">
              <span class="small faint" style="flex:1">Total</span>
              <b class="small" style="width:26px;text-align:right;color:var(--accent);font-variant-numeric:tabular-nums"><?= (int)$totStudents ?></b>
              <span class="tiny faint" style="width:40px;text-align:right">100%</span>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="an-grid an-2b">
    <div class="card" id="performance">
      <h3 class="card-title" style="margin-top:0"><?= icon('graduation') ?> Course performance</h3>
      <p class="small faint" style="margin-top:-8px">Average student progress per course — click a course for details</p>
      <div class="flex-col" style="margin-top:10px">
        <?php foreach ($courses as $c): ?>
          <a class="perf-row" href="<?= url('index.php?r=courses/view&id=' . (int)$c['id']) ?>">
            <span class="perf-ic"><?= icon('graduation') ?></span>
            <div class="flex-1" style="min-width:0">
              <div class="flex gap-8" style="align-items:center;flex-wrap:wrap">
                <b class="small"><?= e($c['title']) ?></b>
                <span class="badge badge-accent" style="font-size:11px"><?= e($c['subject_name']) ?></span>
              </div>
              <p class="tiny faint" style="margin-top:3px"><?= (int)$c['students'] ?> students · <?= (int)$c['lessons'] ?> lessons · <?= (int)$c['graded'] ?> exams graded</p>
            </div>
            <div class="flex gap-8" style="align-items:center;flex-shrink:0">
              <div class="progress" style="width:100px"><div style="width:<?= min(100, (float)($c['avg_progress'] ?? 0)) ?>%"></div></div>
              <b class="small" style="width:44px;text-align:right;color:var(--accent)"><?= (float)($c['avg_progress'] ?? 0) ?>%</b>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('trophy') ?> Top students</h3>
      <p class="small faint" style="margin-top:-8px">Lessons completed &amp; exam average in your courses</p>
      <?php if (!$topStudents): ?>
        <div class="empty-state" style="padding:26px 18px">
          <div class="empty-ic" style="width:46px;height:46px;margin-bottom:10px"><?= icon('trophy') ?></div>
          <h3 style="font-size:.95rem">No activity yet</h3>
          <p class="small">Students who complete lessons or get graded exams in your courses will be ranked here.</p>
        </div>
      <?php else: ?>
        <div style="margin-top:10px">
          <?php foreach ($topStudents as $i => $s): ?>
            <a class="rank-row" href="<?= url('index.php?r=' . $studentsPg) ?>">
              <span class="rank-badge" style="background:<?= $i === 0 ? 'var(--warning-soft);color:var(--warning)' : ($i === 1 ? 'var(--info-soft);color:var(--info)' : ($i === 2 ? 'var(--danger-soft);color:var(--danger)' : 'var(--bg-hover);color:var(--text-faint)')) ?>">
                <?= $i < 3 ? icon(['trophy', 'medal', 'medal'][$i]) : ($i + 1) ?>
              </span>
              <div class="flex-1" style="min-width:0">
                <b class="small"><?= e($s['name']) ?></b>
                <p class="tiny faint" style="margin-top:2px"><?= e($s['student_id']) ?></p>
              </div>
              <div class="flex gap-6" style="align-items:center;flex-shrink:0">
                <span class="badge badge-accent"><?= (int)$s['lessons_done'] ?> lessons</span>
                <?php if ($s['exam_avg'] !== null): ?><span class="badge badge-info"><?= (float)$s['exam_avg'] ?>% avg</span><?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($is_director && !empty($extra['recent'])): ?>
    <!-- Director-only deep dive -->
    <div class="an-grid an-2" style="margin-top:18px">
      <div class="card">
        <h3 class="card-title" style="margin-top:0"><?= icon('chart-bar') ?> Students per course — all courses</h3>
        <p class="small faint" style="margin-top:-8px">Enrollment size per course · hover a bar for details</p>
        <div id="bars-chart" style="margin-top:6px"></div>
      </div>
      <div class="card">
        <h3 class="card-title" style="margin-top:0"><?= icon('download') ?> Recent enrollments</h3>
        <p class="small faint" style="margin-top:-8px">Latest students joining your school's courses</p>
        <div style="margin-top:8px">
          <?php foreach ($extra['recent'] as $r): ?>
            <div class="enroll-row">
              <div class="avatar"><?= e(mb_substr((string)($r['first_name'] ?? '?'), 0, 1)) ?></div>
              <div class="flex-1" style="min-width:0">
                <b class="small"><?= e(trim((string)($r['first_name'] ?? '') . ' ' . (string)($r['last_name'] ?? ''))) ?></b>
                <p class="tiny faint" style="margin-top:2px"><?= e((string)$r['course']) ?> · <?= e((string)$r['date']) ?></p>
              </div>
              <div class="flex gap-6" style="align-items:center;flex-shrink:0">
                <div class="progress" style="width:52px"><div style="width:<?= min(100, (float)($r['progress'] ?? 0)) ?>%"></div></div>
                <span class="tiny faint" style="width:34px;text-align:right"><?= (float)($r['progress'] ?? 0) ?>%</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const line = document.getElementById('enroll-chart');
    if (line && window.EdunexChart) {
      EdunexChart.line(line, {
        labels: <?= json_encode(array_column($series, 'date')) ?>,
        values: <?= json_encode(array_column($series, 'enrollments')) ?>
      }, 'var(--chart-1)', { ma: <?= json_encode($aseries['ma'] ?? []) ?> });
    }
    const donut = document.getElementById('donut-chart');
    if (donut && window.EdunexChart) {
      EdunexChart.donut(donut, {
        labels: <?= json_encode(array_column($courses, 'title')) ?>,
        values: <?= json_encode(array_column($courses, 'students')) ?>
      });
    }
    <?php if ($is_director): ?>
    const bars = document.getElementById('bars-chart');
    if (bars && window.EdunexChart) {
      EdunexChart.bars(bars, {
        labels: <?= json_encode(array_column($courses, 'title')) ?>,
        values: <?= json_encode(array_column($courses, 'students')) ?>
      });
    }
    <?php endif; ?>
  });
  </script>

<?php endif; ?>
