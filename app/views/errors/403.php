<?php /* 403 Access Denied — rendered inside require_role(), with logout link */
$u = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 — Access Denied — Edunex</title>
  <link rel="icon" href="<?= url('public/images/favicon.svg') ?>">
  <link rel="stylesheet" href="<?= url('public/css/app.css') ?>">
  <style>
    .err-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}
    .err-box{text-align:center;max-width:480px}
    .err-box h1{font-size:64px;margin:0;color:var(--danger)}
    .err-box h2{font-size:20px;margin:12px 0 8px}
    .err-box p{color:var(--text-muted);margin-bottom:24px;line-height:1.6}
    .err-box .btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
  </style>
</head>
<body>
  <div class="err-wrap">
    <div class="err-box">
      <h1>403</h1>
      <h2>Access Denied</h2>
      <p><?= $error_msg ?? 'Your role does not have permission to access this page.' ?></p>
      <div class="btns">
        <a class="btn btn-primary" href="<?= url('dashboard') ?>">Dashboard</a>
        <?php if ($u): ?>
          <a class="btn" href="<?= url('user/profile') ?>"><?= icon('user') ?> Profile</a>
          <a class="btn btn-danger" href="<?= url('auth/logout') ?>">Logout</a>
        <?php else: ?>
          <a class="btn btn-primary" href="<?= url('auth/login') ?>">Sign in</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
