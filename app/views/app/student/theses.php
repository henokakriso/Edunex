<?php /* Student theses — submit + track */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('book') ?> My Theses</h1>
    <p class="sub">Submit your thesis for department review</p>
  </div>
  <button class="btn btn-primary" data-open-modal="thesis-modal"><?= icon('plus') ?> New thesis</button>
</div>

<div class="card pad-0">
  <table class="table">
    <thead><tr><th>Title</th><th>Department</th><th>Advisor</th><th>Submitted</th><th>Status</th><th>Feedback</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['title']) ?></b></td>
          <td class="small"><?= e($r['dept_name'] ?: '—') ?></td>
          <td class="small"><?= e($r['advisor'] ?: '—') ?></td>
          <td class="tiny"><?= e(date('M j, Y', strtotime($r['submitted_at']))) ?></td>
          <td><span class="badge <?= $r['status'] === 'approved' ? 'badge-success' : ($r['status'] === 'rejected' ? 'badge-danger' : '') ?>"><?= e($r['status']) ?></span></td>
          <td class="tiny"><?= e($r['feedback'] ?: '—') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="muted">You have not submitted any theses.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<dialog id="thesis-modal" class="modal">
  <div class="modal-box">
    <button class="modal-x" data-close-modal="thesis-modal">×</button>
    <h3 class="card-title"><?= icon('plus') ?> Submit thesis</h3>
    <form method="post" action="<?= e(url('student/theses')) ?>" class="flex-col gap-6" style="margin-top:6px">
      <?= csrf_field() ?>
      <input class="input" name="title" required placeholder="Thesis title" maxlength="200">
      <textarea class="input" name="abstract" rows="4" placeholder="Abstract (optional)"></textarea>
      <div class="grid2">
        <input class="input" name="advisor" placeholder="Advisor name (optional)">
      </div>
      <button class="btn btn-success" value="1"><?= icon('send') ?> Submit for review</button>
    </form>
  </div>
</dialog>
