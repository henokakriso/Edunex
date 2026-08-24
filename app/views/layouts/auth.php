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
          <h1 style="font-size:24px">
            <?= e($title ?? APP_NAME) ?>
            <?php if (is_demo_mode()): ?>
              <span style="position:relative;display:inline-flex;align-items:center;margin-left:6px;vertical-align:middle">
                <span style="width:18px;height:18px;border-radius:50%;background:var(--warning,#f59e0b);color:#000;font-size:11px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;cursor:help;line-height:1">?</span>
                <span style="display:none;position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);background:var(--bg-elev,#1e293b);color:var(--text,#e5e9f2);padding:10px 14px;border-radius:10px;font-size:12px;font-weight:400;white-space:nowrap;box-shadow:0 4px 14px rgba(0,0,0,.2);z-index:10;text-align:left;line-height:1.5" class="demo-login-tip">
                  This is <b>DEMO mode</b> — sample data shown.<br>Go to <b>Settings</b> to switch to Normal mode.
                </span>
              </span>
              <script>
                (function(){
                  var el = document.currentScript.previousElementSibling;
                  var tip = el.querySelector('.demo-login-tip');
                  el.addEventListener('mouseenter', function(){ tip.style.display='block'; });
                  el.addEventListener('mouseleave', function(){ tip.style.display='none'; });
                })();
              </script>
            <?php endif; ?>
          </h1>
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
