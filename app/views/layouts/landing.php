<?php /* Landing layout */ ?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e(current_theme()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? APP_NAME . ' — ' . APP_TAGLINE) ?></title>
  <link rel="icon" href="<?= url('public/images/favicon.svg') ?>">
  <link rel="stylesheet" href="<?= url('public/css/app.css?v=21') ?>">
</head>
<body>
  <div class="container" style="max-width:1180px;margin:0 auto;padding:0 22px;">

    <nav class="landing-nav">
      <a href="<?= url('index.php?r=landing') ?>" class="flex gap-8" style="text-decoration:none;color:var(--text)">
        <img class="brand-logo" src="<?= url('public/images/logo-black.jpeg') ?>" alt="Edunex">
        <span class="brand-name">Edunex</span>
      </a>
      <div class="flex gap-16 small" style="margin-left:auto">
        <a href="<?= url('index.php?r=landing/features') ?>" class="muted" style="font-size:13.5px">Features</a>
        <a href="<?= url('index.php?r=landing/ai') ?>" class="muted" style="font-size:13.5px">AI Tutor</a>
        <a href="<?= url('index.php?r=landing/pricing') ?>" class="muted" style="font-size:13.5px">Pricing</a>
        <a href="<?= url('index.php?r=landing/faq') ?>" class="muted" style="font-size:13.5px">FAQ</a>
        <button class="btn btn-ghost btn-sm" data-theme-toggle onclick="EdunexTheme.toggle()"><?= icon('sun') ?></button>
        <?php if (me()): ?>
          <a class="btn btn-primary btn-sm" href="<?= url('index.php?r=dashboard') ?>">Go to Dashboard</a>
        <?php else: ?>
          <a class="btn btn-ghost btn-sm" href="<?= url('index.php?r=auth/login') ?>">Log in</a>
          <a class="btn btn-primary btn-sm" href="<?= url('index.php?r=auth/register') ?>">Get started</a>
        <?php endif; ?>
      </div>
    </nav>

    <?php include BASE_PATH . '/app/views/partials/flashes.php'; ?>
    <?php include BASE_PATH . '/app/views/' . $__view . '.php'; ?>

    <footer class="landing-footer">
      <div class="flex-between flex-wrap">
        <div>
          <div class="flex gap-8" style="margin-bottom:10px">
            <img class="brand-logo" src="<?= url('public/images/logo-black.jpeg') ?>" alt="Edunex" style="width:30px;height:30px">
            <b>Edunex</b>
          </div>
          <p class="small faint">AI-Powered Ethiopian Learning Platform — for students, teachers and schools.</p>
          <p class="tiny faint" style="margin-top:8px">© <?= date('Y') ?> Edunex. Built in Ethiopia <?= icon('flag') ?> · Amharic, Afaan Oromo, Tigrinya, Somali ready</p>
        </div>
        <div class="flex gap-24 small">
          <div class="flex-col gap-8">
            <b>Product</b>
            <a class="muted" href="<?= url('index.php?r=landing/features') ?>">Features</a>
            <a class="muted" href="<?= url('index.php?r=landing/pricing') ?>">Pricing</a>
            <a class="muted" href="<?= url('index.php?r=landing/ai') ?>">AI Tutor</a>
          </div>
          <div class="flex-col gap-8">
            <b>Platform</b>
            <a class="muted" href="<?= url('index.php?r=auth/login') ?>">Student login</a>
            <a class="muted" href="<?= url('index.php?r=auth/login') ?>">Teacher login</a>
            <a class="muted" href="<?= url('index.php?r=auth/login') ?>">School admin</a>
          </div>
          <div class="flex-col gap-8">
            <b>Help</b>
            <a class="muted" href="<?= url('index.php?r=landing/faq') ?>">FAQ</a>
            <a class="muted" href="<?= url('index.php?r=landing/contact') ?>">Contact</a>
            <a class="muted" href="<?= url('index.php?r=certificates/verify') ?>">Verify certificate</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
  <script>window.EDUNEX = { URL: <?= json_encode(APP_URL) ?>, API: <?= json_encode(APP_URL) ?> };</script>
  <script src="<?= url('public/js/app.js?v=12') ?>"></script>
</body>
</html>
