<?php /* Auth layout - centered card on gradient background */ ?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e(current_theme()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Sign in') ?> — <?= e(APP_NAME) ?></title>
  <link rel="icon" href="<?= url('public/images/favicon.svg') ?>">
  <link rel="stylesheet" href="<?= url('public/css/app.css?v=21') ?>">
  <style>
    .auth-bg { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 30px 18px;
      background:
        radial-gradient(800px 400px at 15% 10%, var(--accent-soft), transparent 60%),
        radial-gradient(700px 420px at 90% 90%, var(--info-soft), transparent 60%),
        var(--bg);
    }
    .auth-card { width: 100%; max-width: 430px; }
  </style>
</head>
<body>
  <div class="auth-bg">
    <div class="auth-card">
      <div class="flex-col gap-8 text-center" style="margin-bottom:26px">
        <span class="brand-logo" style="width:52px;height:52px;font-size:26px;margin:0 auto">E</span>
        <div>
          <h1 style="font-size:24px"><?= e($title ?? APP_NAME) ?></h1>
          <p class="muted small"><?= e(APP_TAGLINE) ?></p>
        </div>
      </div>
      <div class="card" style="padding:30px">
        <?php include BASE_PATH . '/app/views/partials/flashes.php'; ?>
        <?php include BASE_PATH . '/app/views/' . $__view . '.php'; ?>
      </div>
      <p class="text-center tiny faint" style="margin-top:18px">
        © <?= date('Y') ?> Edunex · <a href="<?= url('index.php?r=landing') ?>">Back to homepage</a>
      </p>
    </div>
  </div>
  <script>window.EDUNEX = { URL: <?= json_encode(APP_URL) ?>, API: <?= json_encode(APP_URL) ?> };</script>
  <script src="<?= url('public/js/app.js?v=12') ?>"></script>
</body>
</html>
