<?php /* Registrar admissions — applications, admit/reject */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('flag') ?> Admissions</h1>
    <p class="sub">Applications, admit → student account, or reject</p>
  </div>
  <button class="btn btn-primary" data-open-modal="apply-modal"><?= icon('user-plus') ?> New application</button>
</div>

<div class="card pad-0">
  <div class="flex gap-10" style="padding:10px 14px 0">
    <?php foreach (['pending', 'admitted', 'rejected'] as $st): ?>
      <a class="chip <?= $filter === $st ? 'on' : '' ?>" href="<?= e(url('registrar/admissions&status=' . $st)) ?>"><?= ucfirst($st) ?></a>
    <?php endforeach; ?>
  </div>
  <table class="table">
    <thead>
      <tr><th>Applicant</th><th>Email</th><th>Dept / Program</th><th>Semester</th><th>Applied</th><th style="width:240px">Decision</th></tr>
    </thead>
    <tbody>
      <?php foreach ($apps as $a): ?>
        <tr>
          <td>
            <b><?= e($a['applicant']) ?></b>
            <div class="tiny faint"><?= $a['national_id'] ? 'NID ' . e($a['national_id']) : '' ?> · <?= $a['prior_institution'] ? e($a['prior_institution']) : '—' ?></div>
          </td>
          <td class="tiny"><?= e($a['email']) ?><br><?= e($a['phone'] ?: '') ?></td>
          <td class="tiny"><?= e($a['dept_name'] ?: '—') ?><?= $a['program'] ? '<br>' . e($a['program']) : '' ?></td>
          <td class="tiny"><?= e($a['sem_name'] ?: '—') ?></td>
          <td class="tiny"><?= e(date('Y-m-d', strtotime($a['created_at']))) ?></td>
          <td>
            <?php if ($a['status'] === 'pending'): ?>
              <form method="post" action="<?= e(url('registrar/admissions')) ?>" class="flex gap-6">
                <?= csrf_field() ?>
                <select class="input" name="decision" style="min-width:110px">
                  <option value="admitted">Admit</option>
                  <option value="rejected">Reject</option>
                </select>
                <button class="btn btn-xs btn-success" name="decide_admission" value="<?= (int)$a['id'] ?>"><?= icon('check') ?> Go</button>
              </form>
            <?php else: ?>
              <span class="badge <?= $a['status'] === 'admitted' ? 'badge-success' : 'badge-danger' ?>"><?= $a['status'] === 'admitted' ? 'Admitted' : 'Rejected' ?></span>
              <?php if ($a['user_id']): ?><div class="tiny faint">user #<?= (int)$a['user_id'] ?></div><?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$apps): ?><tr><td colspan="6" class="tiny faint">No <?= $filter ?> applications.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<dialog id="apply-modal" class="modal">
  <div class="modal-box">
    <button class="modal-x" data-close-modal="apply-modal">×</button>
    <h3 class="card-title"><?= icon('user-plus') ?> New application</h3>
    <form method="post" action="<?= e(url('registrar/admissions')) ?>" class="flex-col gap-6" style="margin-top:6px">
      <?= csrf_field() ?>
      <div class="grid2">
        <input class="input" name="first_name" required placeholder="First name">
        <input class="input" name="last_name" required placeholder="Last name">
      </div>
      <div class="grid2">
        <input class="input" type="email" name="email" required placeholder="Email">
        <input class="input" name="phone" placeholder="Phone">
      </div>
      <div class="grid2">
        <select class="input" name="department_id">
          <option value="0">— Department —</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <select class="input" name="semester_id">
          <option value="0">— Intake semester —</option>
          <?php foreach ($semesters as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?> · <?= e($s['year_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <input class="input" name="national_id" placeholder="National ID (optional)">
      <input class="input" name="prior_institution" placeholder="Prior institution (optional)">
      <input class="input" name="program" placeholder="Program (e.g. BSc Computer Science)">
      <button class="btn btn-success" name="apply_admission" value="1"><?= icon('save') ?> Register application</button>
    </form>
  </div>
</dialog>
