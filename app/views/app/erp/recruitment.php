<?php /* ERP Recruitment — openings, pipeline, applications */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('flag') ?> Recruitment</h1>
    <p class="sub">Job openings and candidate pipeline</p>
  </div>
  <button class="btn btn-primary" data-open-modal="opening-modal"><?= icon('plus') ?> Post opening</button>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title" style="margin-top:0"><?= icon('chart-bar') ?> Pipeline</h3>
  <div class="flex gap-14" style="flex-wrap:wrap">
    <?php foreach (['applied', 'screened', 'interview', 'offered', 'hired', 'rejected'] as $s): ?>
      <div class="flex-col">
        <b style="font-size:1.2rem"><?= (int)($pipeline[$s] ?? 0) ?></b>
        <span class="tiny faint"><?= $s ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="grid2">
  <div class="card pad-0">
    <h3 class="card-title" style="padding:12px 14px 0"><?= icon('briefcase') ?> Openings</h3>
    <table class="table">
      <thead><tr><th>Position</th><th>Type</th><th>Salary</th><th>Deadline</th><th>Apps</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($openings as $o): ?>
          <tr>
            <td><b><?= e($o['title']) ?></b><div class="tiny faint"><?= e($o['dept'] ?: '—') ?></div><?= demo_badge($o) ?></td>
            <td><span class="badge badge-info"><?= e($o['job_type']) ?></span></td>
            <td class="tiny"><?= e($o['salary_range'] ?: '—') ?></td>
            <td class="tiny"><?= e($o['deadline'] ?: '—') ?></td>
            <td><b><?= (int)$o['apps'] ?></b></td>
            <td>
              <?php if ($o['status'] === 'open'): ?>
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="opening_id" value="<?= (int)$o['id'] ?>"><button class="btn btn-xs btn-ghost" name="close_opening" value="1">Close</button></form>
              <?php else: ?><span class="badge badge-danger">closed</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$openings): ?><tr><td colspan="6" class="muted">No openings yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('user-plus') ?> Log application</h3>
    <form method="post" class="flex-col gap-6">
      <?= csrf_field() ?>
      <select class="input" name="opening_id" required><option value="">— Opening —</option><?php foreach ($openings as $o): ?><option value="<?= (int)$o['id'] ?>"><?= e($o['title']) ?></option><?php endforeach; ?></select>
      <input class="input" name="candidate_name" placeholder="Candidate name *" required>
      <div class="grid2"><input class="input" name="email" type="email" placeholder="Email *" required><input class="input" name="phone" placeholder="Phone"></div>
      <textarea class="input" name="summary" placeholder="Qualification summary" rows="2"></textarea>
      <button class="btn btn-primary" name="add_application" value="1"><?= icon('save') ?> Log application</button>
    </form>
  </div>
</div>

<div class="card pad-0" style="margin-top:18px">
  <h3 class="card-title" style="padding:12px 14px 0"><?= icon('users') ?> Applications (<?= count($applications) ?>)</h3>
  <table class="table">
    <thead><tr><th>Candidate</th><th>Opening</th><th>Summary</th><th>Stage</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($applications as $a): ?>
        <tr>
          <td><b><?= e($a['candidate_name']) ?></b><div class="tiny faint"><?= e($a['email']) ?> · <?= e($a['phone'] ?: '—') ?></div></td>
          <td class="tiny"><?= e($a['opening']) ?></td>
          <td class="tiny"><?= e($a['summary'] ?: '—') ?></td>
          <td><span class="badge badge-<?= $a['stage'] === 'hired' ? 'success' : ($a['stage'] === 'rejected' ? 'danger' : 'info') ?>"><?= e($a['stage']) ?></span><?= demo_badge($a) ?></td>
          <td>
            <div class="flex gap-6">
              <?php $next = ['applied' => 'screened', 'screened' => 'interview', 'interview' => 'offered', 'offered' => 'hired'][$a['stage']] ?? null; ?>
              <?php if ($next): ?>
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="app_id" value="<?= (int)$a['id'] ?>"><button class="btn btn-xs btn-primary" name="stage" value="<?= $next ?>">→ <?= $next ?></button></form>
              <?php endif; ?>
              <?php if (!in_array($a['stage'], ['hired', 'rejected'], true)): ?>
                <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="app_id" value="<?= (int)$a['id'] ?>"><button class="btn btn-xs btn-danger" name="reject_app" value="1">Reject</button></form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$applications): ?><tr><td colspan="5" class="muted">No applications yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal" id="opening-modal"><div class="modal-content">
  <h3><?= icon('plus') ?> Post job opening</h3>
  <form method="post" class="flex-col gap-6">
    <?= csrf_field() ?>
    <input class="input" name="title" placeholder="Position title *" required>
    <div class="grid2">
      <select class="input" name="department_id"><option value="0">— Department —</option><?php foreach ($depts as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select>
      <select class="input" name="job_type"><?php foreach (['full', 'part', 'contract'] as $t): ?><option><?= $t ?></option><?php endforeach; ?></select>
      <input class="input" name="salary_range" placeholder="Salary range e.g. 25,000 – 35,000 ETB">
      <input class="input" type="date" name="deadline">
    </div>
    <textarea class="input" name="description" placeholder="Description" rows="3"></textarea>
    <button class="btn btn-primary" name="add_opening" value="1"><?= icon('briefcase') ?> Post opening</button>
  </form>
</div></div>
