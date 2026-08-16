<?php /* Register view */ ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger" style="margin-bottom:16px">
    <?php foreach ($errors as $e): ?><div>• <?= e($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" id="reg-form">
  <?= csrf_field() ?>
  <div class="form-row">
    <div class="field"><label>First name</label><input class="input" name="first_name" value="<?= e($_POST['first_name'] ?? '') ?>" required></div>
    <div class="field"><label>Last name</label><input class="input" name="last_name" value="<?= e($_POST['last_name'] ?? '') ?>" required></div>
  </div>
  <div class="alert alert-info" style="margin-bottom:16px">
    Student accounts are created here and verified by your homeroom teacher within 24 hours.
    Teachers and parents get their accounts from their school — ask your school director or teacher.
  </div>
  <div class="field">
    <label>School</label>
    <select class="select" name="school_id" id="reg-school" required>
      <option value="">— Select your school —</option>
      <?php foreach ($schools as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= ($_POST['school_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
      <?php endforeach; ?>
    </select>
    <p class="help">Your school isn't listed? Ask your administrator to register it.</p>
  </div>
  <div class="field" id="reg-group-field">
    <label>Class / section</label>
    <select class="select" name="group_id">
      <option value="0">— Assign later —</option>
      <?php foreach ($groups as $g): ?>
        <option value="<?= (int)$g['id'] ?>"><?= e($g['school_name']) ?> — <?= e($g['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-row">
    <div class="field"><label>Email</label><input class="input" type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></div>
    <div class="field"><label>Phone (optional)</label><input class="input" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+2519…"></div>
  </div>
  <div class="form-row">
    <div class="field"><label>Password (min 8, uppercase + number)</label><input class="input" type="password" name="password" required></div>
    <div class="field"><label>Confirm password</label><input class="input" type="password" name="password2" required></div>
  </div>

  <details style="margin-bottom:16px;border:1px solid var(--border);border-radius:10px;padding:12px">
    <summary class="small muted" style="cursor:pointer"><?= icon('refresh') ?> Transferring from another school? Have a referral code?</summary>
    <div class="field" style="margin-top:12px">
      <label>Transfer referral code</label>
      <input class="input" name="referral" value="<?= e($_POST['referral'] ?? '') ?>" placeholder="TRF-XXXX-XXXX">
      <p class="help">The code issued by your previous school. It connects this account to your old records.</p>
    </div>
    <div class="field">
      <label>Your previous student ID (special ID)</label>
      <input class="input" name="student_id" value="<?= e($_POST['student_id'] ?? '') ?>" placeholder="e.g. AAIS-2026-000001">
      <p class="help">Your special student ID follows you between schools — grades and certificates stay attached to it.</p>
    </div>
  </details>

  <button class="btn btn-primary btn-lg" style="width:100%">Create my account</button>
</form>
<p class="text-center small muted" style="margin-top:14px">Already registered? <a href="<?= url('index.php?r=auth/login') ?>">Sign in</a></p>
