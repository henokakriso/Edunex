<?php /* Transcript management for registrar */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('file-text') ?> Transcript Requests</h1>
    <p class="sub">Process pending transcript requests</p>
  </div>
</div>

<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Student</th><th>ID</th><th>Type</th><th>Requested</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($requests as $r): ?>
        <tr>
          <td><?= e($r['student_name']) ?></td>
          <td><span class="badge"><?= e($r['sid_no'] ?? '—') ?></span></td>
          <td><?= e(ucfirst($r['type'])) ?></td>
          <td class="tiny"><?= e($r['requested_at']) ?></td>
          <td>
            <form method="post" action="<?= e(url('university/transcript/manage')) ?>" style="display:inline">
              <?= csrf_field() ?>
              <button class="btn btn-xs btn-success" name="process_request" value="<?= (int)$r['id'] ?>"><?= icon('check') ?> Generate</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$requests): ?><tr><td colspan="5" class="tiny faint">No pending requests.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
