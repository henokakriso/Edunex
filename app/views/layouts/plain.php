<?php /* Plain layout (errors, embeds) */ ?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e(current_theme()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? APP_NAME) ?></title>
  <link rel="icon" href="<?= url('public/images/favicon.svg') ?>">
  <link rel="stylesheet" href="<?= url('public/css/app.css?v=21') ?>">
</head>
<body>
  <div style="max-width:760px;margin:0 auto;padding:48px 22px">
    <?php include BASE_PATH . '/app/views/partials/flashes.php'; ?>
    <?php include BASE_PATH . '/app/views/' . $__view . '.php'; ?>
  </div>
  <script>window.EDUNEX = { URL: <?= json_encode(APP_URL) ?>, API: <?= json_encode(APP_URL) ?> };</script>
  <script src="<?= url('public/js/app.js?v=12') ?>"></script>
</body>
</html>
