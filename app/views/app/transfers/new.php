<?php /* New transfer request */
?>
<div class="page-head">
  <div>
    <h1><?= icon('refresh') ?> Request Transfer</h1>
    <p class="sub">Move your account (and student ID) to another school</p>
  </div>
</div>

<form method="post" class="card" style="max-width:560px">
  <?= csrf_field() ?>
  <label class="small faint">Target school *</label>
  <select class="input" name="to_school" required style="margin-top:6px">
    <option value="">— choose a school —</option>
    <?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?> — <?= e($s['city'] ?? '') ?></option><?php endforeach; ?>
  </select>
  <label class="small faint" style="display:block;margin-top:14px">Referral code (optional — instant approval)</label>
  <input class="input" name="referral_code" placeholder="TRF-XXXX-XXXX" style="margin-top:6px;font-family:monospace">
  <label class="small faint" style="display:block;margin-top:14px">Reason (optional)</label>
  <textarea class="input" name="reason" rows="3" style="margin-top:6px" placeholder="Why are you transferring?"></textarea>
  <button class="btn btn-primary" style="margin-top:16px"><?= icon('rocket') ?> Submit request</button>
  <p class="tiny faint" style="margin-top:10px">Your progress, XP, badges and certificates move with your student ID.</p>
</form>
