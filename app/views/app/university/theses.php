<?php /* Theses list */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('book') ?> Theses</h1>
    <p class="sub">Research thesis proposals and tracking</p>
  </div>
</div>

<?php if ($u['role'] === 'student'): ?>
  <?php if ($myThesis): ?>
    <div class="card" style="margin-bottom:18px">
      <div class="flex-row gap-6" style="margin-bottom:8px">
        <span class="badge badge-<?= $myThesis['status'] === 'completed' ? 'success' : 'warning' ?>"><?= e(ucfirst($myThesis['status'])) ?></span>
        <b><?= e($myThesis['title']) ?></b>
      </div>
      <p class="tiny faint"><?= e(mb_strimwidth($myThesis['abstract'] ?: '', 0, 200, '…')) ?></p>
      <?php if ($myThesis['advisor_name']): ?>
        <p class="tiny">Advisor: <b><?= e($myThesis['advisor_name']) ?></b></p>
      <?php endif; ?>
      <a href="<?= e(url('university/thesis&id=' . $myThesis['id'])) ?>" class="btn btn-sm btn-primary" style="margin-top:8px"><?= icon('eye') ?> View Details</a>
    </div>
  <?php else: ?>
    <div class="card" style="margin-bottom:18px">
      <h3 class="card-title"><?= icon('plus') ?> New Thesis Proposal</h3>
      <form method="post" action="<?= e(url('university/theses')) ?>" class="flex-col gap-6" style="margin-top:6px">
        <?= csrf_field() ?>
        <input class="input" name="title" required placeholder="Thesis title" maxlength="500">
        <textarea class="input" name="abstract" rows="4" placeholder="Abstract (optional)"></textarea>
        <button class="btn btn-success" name="create_thesis" value="1"><?= icon('save') ?> Submit Proposal</button>
      </form>
    </div>
  <?php endif; ?>
<?php else: ?>
  <div class="card pad-0">
    <table class="table">
      <thead>
        <tr><th>Student</th><th>ID</th><th>Title</th><th>Status</th><th>Advisor</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($theses as $t): ?>
          <tr>
            <td><?= e($t['student_name']) ?></td>
            <td><span class="badge"><?= e($t['sid_no'] ?? '—') ?></span></td>
            <td><?= e($t['title']) ?></td>
            <td><span class="badge badge-<?= $t['status'] === 'completed' ? 'success' : 'warning' ?>"><?= e(ucfirst($t['status'])) ?></span></td>
            <td class="tiny"><?= e($t['advisor_name'] ?? '—') ?></td>
            <td><a href="<?= e(url('university/thesis&id=' . $t['id'])) ?>" class="btn btn-xs btn-ghost"><?= icon('eye') ?></a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$theses): ?><tr><td colspan="6" class="tiny faint">No theses found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
