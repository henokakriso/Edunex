<div class="page-head">
  <div><h1>Active sessions</h1><p class="sub">Devices where you are currently signed in.</p></div>
</div>
<div class="table-wrap">
  <table class="data">
    <thead><tr><th>Device</th><th>IP address</th><th>Expires</th></tr></thead>
    <tbody>
      <?php foreach ($sessions as $s): ?>
        <tr>
          <td><?= e($s['user_agent'] ?: 'Unknown device') ?></td>
          <td><?= e($s['ip']) ?></td>
          <td><?= e(date('M j, Y', strtotime($s['expires_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<a class="btn btn-danger" style="margin-top:16px" href="<?= url('index.php?r=auth/logout&all=1') ?>" data-confirm="Sign out of ALL devices?">Sign out everywhere</a>
