<?php /* Registrar scholarships — create, open/close, award */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('wallet') ?> Scholarships</h1>
    <p class="sub">Scholarships and awards for your university</p>
  </div>
  <button class="btn btn-primary" data-open-modal="scholarship-modal"><?= icon('plus') ?> New scholarship</button>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title" style="margin-top:0"><?= icon('award') ?> Award a scholarship</h3>
  <form method="post" class="grid2" style="margin-top:6px">
    <?= csrf_field() ?>
    <div class="flex-col"><label class="small faint">Scholarship *</label>
      <select class="input" name="scholarship_id" required>
        <option value="">— Select open scholarship —</option>
        <?php foreach ($rows as $r): if ($r['status'] !== 'open') continue; ?>
          <option value="<?= (int)$r['id'] ?>"><?= e($r['name']) ?> · <?= number_format((float)$r['amount']) ?> ETB</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col"><label class="small faint">Student *</label>
      <select class="input" name="student_id" required>
        <option value="">— Select student —</option>
        <?php foreach ($students as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['student_id'] ?: '—') ?>)</option><?php endforeach; ?>
      </select>
    </div>
    <div><button class="btn btn-success" name="award_scholarship" value="1"><?= icon('trophy') ?> Award</button></div>
  </form>
</div>

<div class="grid2">
  <div class="card pad-0">
    <table class="table">
      <thead><tr><th>Scholarship</th><th>Amount</th><th>Deadline</th><th>Awards</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><b><?= e($r['name']) ?></b><p class="tiny faint" style="margin:0"><?= e($r['description'] ?: '') ?></p></td>
            <td class="tiny"><?= number_format((float)$r['amount']) ?> ETB</td>
            <td class="tiny"><?= $r['deadline'] ? e($r['deadline']) : '—' ?></td>
            <td class="tiny"><?= (int)$r['awards'] ?></td>
            <td>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <button class="btn btn-xs <?= $r['status'] === 'open' ? 'btn-success' : 'btn-ghost' ?>" name="toggle_scholarship" value="<?= (int)$r['id'] ?>"><?= $r['status'] === 'open' ? icon('check') . ' Open' : icon('close') . ' Closed' ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="muted">No scholarships yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card pad-0">
    <table class="table">
      <thead><tr><th>Student</th><th>Scholarship</th><th>Awarded</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($awardRows as $a): ?>
          <tr>
            <td><b><?= e($a['student']) ?></b><div class="tiny faint"><?= e($a['student_id']) ?></div></td>
            <td class="small"><?= e($a['sch_name']) ?></td>
            <td class="tiny"><?= e(date('M j, Y', strtotime($a['awarded_at']))) ?></td>
            <td>
              <form method="post" class="inline">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-danger" name="revoke_scholarship" value="<?= (int)$a['id'] ?>"><?= icon('trash') ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$awardRows): ?><tr><td colspan="4" class="muted">No awards yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<dialog id="scholarship-modal" class="modal">
  <div class="modal-box">
    <button class="modal-x" data-close-modal="scholarship-modal">×</button>
    <h3 class="card-title"><?= icon('plus') ?> New scholarship</h3>
    <form method="post" action="<?= e(url('registrar/scholarships')) ?>" class="flex-col gap-6" style="margin-top:6px">
      <?= csrf_field() ?>
      <input class="input" name="name" required placeholder="Scholarship name" maxlength="120">
      <textarea class="input" name="description" rows="2" placeholder="Description (optional)"></textarea>
      <div class="grid2">
        <input class="input" type="number" name="amount" value="0" min="0" step="0.01" placeholder="Amount (ETB)">
        <input class="input" type="date" name="deadline">
      </div>
      <button class="btn btn-success" name="create_scholarship" value="1"><?= icon('save') ?> Create</button>
    </form>
  </div>
</dialog>
