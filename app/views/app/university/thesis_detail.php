<?php /* Thesis detail */ ?>
<div class="page-head">
  <div>
    <h1><?= e($thesis['title'] ?: 'Thesis') ?></h1>
    <p class="sub"><?= e($thesis['student_name']) ?> (<?= e($thesis['sid_no'] ?? '—') ?>) · <?= e(ucfirst($thesis['status'])) ?></p>
  </div>
  <a href="<?= e(url('university/theses')) ?>" class="btn btn-ghost"><?= icon('arrow-left') ?> Theses</a>
</div>

<div class="grid2" style="margin-bottom:18px">
  <div class="card">
    <h3 class="card-title"><?= icon('info') ?> Details</h3>
    <p><b>Title:</b> <?= e($thesis['title']) ?></p>
    <p><b>Abstract:</b> <?= e($thesis['abstract'] ?: '—') ?></p>
    <p><b>Advisor:</b> <?= e($thesis['advisor_name'] ?? 'Not assigned') ?></p>
    <p><b>Status:</b> <span class="badge badge-<?= $thesis['status'] === 'completed' ? 'success' : 'warning' ?>"><?= e(ucfirst($thesis['status'])) ?></span></p>
    <?php if ($thesis['defense_date']): ?>
      <p><b>Defense Date:</b> <?= e($thesis['defense_date']) ?></p>
      <p><b>Result:</b> <?= e(ucfirst($thesis['defense_result'] ?? 'Pending')) ?></p>
    <?php endif; ?>
  </div>

  <?php if (in_array($u['role'], ['dean','hod'])): ?>
  <div class="card">
    <h3 class="card-title"><?= icon('settings') ?> Actions</h3>
    <?php if (!$thesis['advisor_id']): ?>
      <form method="post" action="<?= e(url('university/thesis&id=' . $thesis['id'])) ?>" class="flex-col gap-6">
        <?= csrf_field() ?>
        <select class="input" name="advisor_id" required>
          <option value="">— Select Advisor —</option>
          <?php foreach ($lecturers as $l): ?>
            <option value="<?= (int)$l['id'] ?>"><?= e($l['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-success" name="assign_advisor" value="1"><?= icon('user') ?> Assign Advisor</button>
      </form>
    <?php endif; ?>

    <?php if ($thesis['status'] !== 'completed'): ?>
      <form method="post" action="<?= e(url('university/thesis&id=' . $thesis['id'])) ?>" class="flex-col gap-6" style="margin-top:12px">
        <?= csrf_field() ?>
        <input class="input" type="date" name="defense_date" required>
        <button class="btn btn-primary" name="schedule_defense" value="1"><?= icon('calendar') ?> Schedule Defense</button>
      </form>

      <form method="post" action="<?= e(url('university/thesis&id=' . $thesis['id'])) ?>" class="flex-col gap-6" style="margin-top:12px">
        <?= csrf_field() ?>
        <select class="input" name="result" required>
          <option value="pass">Pass</option>
          <option value="fail">Fail</option>
          <option value="revise">Revise</option>
        </select>
        <textarea class="input" name="notes" rows="2" placeholder="Notes"></textarea>
        <button class="btn btn-warning" name="defense_result" value="1"><?= icon('check') ?> Record Result</button>
      </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<h3 style="margin:18px 0 8px"><?= icon('list') ?> Chapters</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>#</th><th>Title</th><th>Status</th><th>Submitted</th><th>Feedback</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($chapters as $ch): ?>
        <tr>
          <td><?= (int)$ch['chapter_number'] ?></td>
          <td><?= e($ch['title']) ?></td>
          <td><span class="badge badge-<?= $ch['status'] === 'approved' ? 'success' : ($ch['status'] === 'submitted' ? 'warning' : 'ghost') ?>"><?= e(ucfirst($ch['status'])) ?></span></td>
          <td class="tiny"><?= $ch['submitted_at'] ? e($ch['submitted_at']) : '—' ?></td>
          <td class="tiny"><?= e(mb_strimwidth($ch['feedback'] ?: '', 0, 80, '…')) ?></td>
          <td>
            <?php if ((int)$thesis['student_id'] === (int)$u['id'] && $ch['status'] === 'draft'): ?>
              <form method="post" action="<?= e(url('university/thesis&id=' . $thesis['id'])) ?>" style="display:inline">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-primary" name="submit_chapter" value="<?= (int)$ch['id'] ?>"><?= icon('send') ?> Submit</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if (!empty($committee)): ?>
<h3 style="margin:18px 0 8px"><?= icon('users') ?> Committee</h3>
<div class="card pad-0">
  <table class="table">
    <thead><tr><th>Member</th><th>Role</th><th>Approved</th></tr></thead>
    <tbody>
      <?php foreach ($committee as $c): ?>
        <tr>
          <td><?= e($c['member_name']) ?></td>
          <td><?= e(ucfirst(str_replace('_', ' ', $c['role']))) ?></td>
          <td class="tiny"><?= $c['approved_at'] ? e($c['approved_at']) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
