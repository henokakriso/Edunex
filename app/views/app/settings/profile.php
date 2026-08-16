<?php /* Profile settings */
?>
<div class="page-head">
  <div>
    <h1><?= icon('user') ?> Profile Settings</h1>
    <p class="sub">Update your personal information</p>
  </div>
</div>

<form method="post" enctype="multipart/form-data" class="card" style="max-width:640px">
  <?= csrf_field() ?>
  <div class="flex gap-16" style="align-items:center;margin-bottom:16px">
    <img class="avatar" src="<?= e(avatar_url($__u)) ?>" alt="avatar" style="width:64px;height:64px;border-radius:50%;object-fit:cover" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
    <div class="avatar" style="width:64px;height:64px;font-size:26px;display:none"><?= e(initials($__u)) ?></div>
    <div>
      <label class="small faint">Avatar</label>
      <input type="file" name="avatar" accept="image/*" class="input" style="margin-top:4px">
      <p class="tiny faint">JPG, PNG, GIF or WebP · current photo shown above</p>
    </div>
  </div>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">First name *</label><input class="input" name="first_name" value="<?= e($__u['first_name']) ?>" required></div>
    <div class="flex-col"><label class="small faint">Last name *</label><input class="input" name="last_name" value="<?= e($__u['last_name']) ?>" required></div>
    <div class="flex-col"><label class="small faint">Phone</label><input class="input" name="phone" value="<?= e($__u['phone']) ?>"></div>
    <div class="flex-col"><label class="small faint">Language</label>
      <select class="input" name="language"><?php foreach (['en' => 'English', 'am' => 'አማርኛ (Amharic)', 'om' => 'Afaan Oromoo', 'ti' => 'ትግርኛ (Tigrinya)', 'so' => 'Soomaali'] as $k => $v): ?><option value="<?= $k ?>" <?= ($__u['language'] ?? 'en') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Birth date</label><input class="input" type="date" name="birth_date" value="<?= e($__u['birth_date'] ?? '') ?>"></div>
    <div class="flex-col"><label class="small faint">Gender</label>
      <select class="input" name="gender"><option value="">—</option><option value="m" <?= ($__u['gender'] ?? '') === 'm' ? 'selected' : '' ?>>Male</option><option value="f" <?= ($__u['gender'] ?? '') === 'f' ? 'selected' : '' ?>>Female</option><option value="o" <?= ($__u['gender'] ?? '') === 'o' ? 'selected' : '' ?>>Other</option></select>
    </div>
  </div>
  <label class="small faint" style="margin-top:14px;display:block">Bio</label>
  <textarea class="input" name="bio" rows="3"><?= e($__u['bio'] ?? '') ?></textarea>
  <button class="btn btn-primary" style="margin-top:14px"><?= icon('save') ?> Save</button>
</form>
