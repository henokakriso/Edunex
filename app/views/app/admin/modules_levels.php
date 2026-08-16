<?php /* Admin modules — by education level (installer view) */
$levels = ['kg' => 'Kindergarten', 'primary' => 'Primary (Gr 1–8)', 'secondary' => 'Secondary / Preparatory (Gr 9–12)', 'university' => 'University', 'all' => 'All levels'];
?>
<div class="page-head">
  <div>
    <h1><?= icon('box') ?> Modules by Level</h1>
    <p class="sub">Recommended module sets per institution type</p>
  </div>
  <div class="flex gap-6">
    <a class="btn btn-ghost" href="<?= e(url('admin/modules')) ?>"><?= icon('list') ?> Registry</a>
  </div>
</div>

<?php foreach ($levels as $lvl => $lvlName): ?>
  <?php $list = array_values(array_filter($rows, fn($r) => $r['education_type'] === $lvl)); ?>
  <div class="card" style="margin-bottom:16px">
    <h3 class="card-title" style="margin-top:0"><?= icon('graduation') ?> <?= $lvlName ?></h3>
    <p class="tiny faint" style="margin-top:-6px">
      <?php if ($lvl === 'kg'): ?>No student login — teachers &amp; parents use attendance, photo updates, activity &amp; growth reports.
      <?php elseif ($lvl === 'primary'): ?>Limited student access (grades, simple homework, reading) — parent approval for important actions.
      <?php elseif ($lvl === 'secondary'): ?>Full access: AI tutor, exams, national exam preparation, certificates.
      <?php elseif ($lvl === 'university'): ?>Separate university architecture: faculties, deans, registrar, transcripts, research.
      <?php else: ?>Shared services used by every institution type.<?php endif; ?>
    </p>
    <div class="flex gap-8" style="flex-wrap:wrap">
      <?php foreach ($list as $m): ?>
        <span class="badge <?= $m['enabled'] ? 'badge-success' : 'badge-warning' ?>"><?= $m['enabled'] ? icon('check') : '' ?> <?= e($m['name']) ?></span>
      <?php endforeach; ?>
      <?php if (!$list): ?><span class="muted small">No level-specific modules.</span><?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<div class="card muted">
  <h3 class="card-title" style="margin-top:0"><?= icon('info') ?> How installation works</h3>
  <p class="small" style="margin:0">
    Every institution runs on the same unified database. An institution's <b>education level</b>
    (set on its profile) activates the matching level modules; optional modules (finance, transport,
    hostel, health, research, thesis, admissions…) can be enabled per platform needs from the
    <a class="btn btn-link" href="<?= e(url('admin/modules')) ?>">module registry</a>. The installer
    auto-detects installed modules — only compatible features are shown.
  </p>
</div>
