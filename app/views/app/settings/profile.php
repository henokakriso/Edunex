<?php /* Settings — tabbed for ALL roles */
$__tabs = [
    ['profile', icon('user') . ' Profile', 'profile'],
    ['display', icon('palette') . ' Display', 'display'],
    ['security', icon('shield') . ' Security', 'security'],
    ['fayda', icon('badge') . ' Fayda ID', 'fayda'],
    ['sessions', icon('monitor') . ' Sessions', 'sessions'],
];
$__tab = $activeTab ?? 'profile';
?>
<style>
.settings-tabs{display:flex;gap:6px;margin-bottom:24px;flex-wrap:wrap}
.settings-tab{padding:10px 18px;border-radius:12px;font-size:13.5px;font-weight:600;color:var(--text-dim);border:1px solid var(--glass-border);background:var(--glass-bg);backdrop-filter:blur(12px);cursor:pointer;transition:all .25s cubic-bezier(.25,.46,.45,.94);text-decoration:none;display:inline-flex;align-items:center;gap:6px;position:relative;overflow:hidden}
.settings-tab::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.04) 0%,transparent 50%);pointer-events:none}
.settings-tab:hover{background:var(--glass-hover-bg);border-color:var(--glass-hover-border);color:var(--text);box-shadow:inset 0 1px 0 rgba(255,255,255,.3),var(--glass-hover-shadow)}
.settings-tab.active{background:var(--accent);border-color:transparent;color:#fff;box-shadow:0 4px 16px rgba(13,148,136,.35),inset 0 1px 0 rgba(255,255,255,.2)}
.settings-tab.active::before{background:linear-gradient(135deg,rgba(255,255,255,.15) 0%,transparent 40%)}
.settings-tab:active{transform:scale(.96)}
.settings-section{animation:iOSslideIn .35s cubic-bezier(.25,.46,.45,.94)}
.settings-card{padding:24px 28px;max-width:680px}
.settings-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.settings-row.full{grid-template-columns:1fr}
.theme-card{padding:18px 20px;border-radius:14px;border:2px solid var(--glass-border);background:var(--glass-bg);cursor:pointer;transition:all .25s cubic-bezier(.25,.46,.45,.94);text-align:center;position:relative;overflow:hidden}
.theme-card::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.04) 0%,transparent 50%);pointer-events:none}
.theme-card.active{border-color:var(--accent);box-shadow:0 0 0 2px rgba(13,148,136,.2),0 8px 24px rgba(13,148,136,.15)}
.theme-card:hover{border-color:var(--glass-hover-border);box-shadow:var(--glass-hover-shadow);transform:translateY(-2px)}
.theme-card:active{transform:scale(.97)}
.fayda-card{padding:24px;border-radius:16px;border:1px solid var(--glass-border);background:linear-gradient(135deg,var(--accent-soft),rgba(13,148,136,.05));position:relative;overflow:hidden}
.fayda-card::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.06) 0%,transparent 40%);pointer-events:none}
.color-swatch{width:36px;height:36px;border-radius:10px;border:2px solid var(--glass-border);cursor:pointer;transition:all .2s cubic-bezier(.25,.46,.45,.94);position:relative}
.color-swatch:hover{transform:scale(1.12);box-shadow:0 4px 12px rgba(0,0,0,.2)}
.color-swatch.active{border-color:var(--text);box-shadow:0 0 0 2px var(--bg),0 0 0 4px var(--text)}
.color-swatch.active::after{content:'✓';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:#fff;text-shadow:0 1px 2px rgba(0,0,0,.4)}
.display-option{padding:14px 16px;border-radius:12px;border:1px solid var(--glass-border);background:var(--glass-bg);display:flex;align-items:center;justify-content:space-between;gap:12px;transition:all .25s cubic-bezier(.25,.46,.45,.94);position:relative;overflow:hidden}
.display-option::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.04) 0%,transparent 50%);pointer-events:none}
.display-option:hover{border-color:var(--glass-hover-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.3),var(--glass-hover-shadow)}
.font-size-preview{width:100%;height:48px;border-radius:10px;border:1px solid var(--glass-border);background:var(--glass-bg);display:flex;align-items:center;justify-content:center;transition:all .25s cubic-bezier(.25,.46,.45,.94)}
.session-item{display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:12px;border:1px solid var(--glass-border);background:var(--glass-bg);transition:all .25s cubic-bezier(.25,.46,.45,.94);position:relative;overflow:hidden}
.session-item::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.04) 0%,transparent 50%);pointer-events:none}
.session-item:hover{border-color:var(--glass-hover-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.3),var(--glass-hover-shadow)}
.session-item.current{border-color:var(--accent);box-shadow:0 0 0 1px rgba(13,148,136,.2)}
.security-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:700}
.security-badge.on{background:rgba(22,163,74,.15);color:var(--success)}
.security-badge.off{background:rgba(239,68,68,.15);color:var(--danger)}
</style>

<div class="page-head">
  <div>
    <h1><?= icon('settings') ?> Settings</h1>
    <p class="sub">Manage your account, appearance and security</p>
  </div>
</div>

<nav class="settings-tabs">
  <?php foreach ($__tabs as $t): ?>
    <a class="settings-tab<?= $t[2] === $__tab ? ' active' : '' ?>" href="<?= e(url('settings/profile&tab=' . $t[2])) ?>"><?= $t[1] ?></a>
  <?php endforeach; ?>
</nav>

<?php if ($__tab === 'profile'): ?>
<div class="settings-section">
  <form method="post" enctype="multipart/form-data" class="card settings-card">
    <?= csrf_field() ?>
    <div class="flex gap-16" style="align-items:center;margin-bottom:20px">
      <img class="avatar" src="<?= e(avatar_url($__u)) ?>" alt="avatar" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid var(--glass-border)" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
      <div class="avatar" style="width:72px;height:72px;font-size:28px;display:none;border:2px solid var(--glass-border)"><?= e(initials($__u)) ?></div>
      <div>
        <label class="small faint">Avatar</label>
        <input type="file" name="avatar" accept="image/*" class="input" style="margin-top:4px">
        <p class="tiny faint">JPG, PNG, GIF or WebP</p>
      </div>
    </div>
    <div class="settings-row">
      <div class="field"><label>First name *</label><input class="input" name="first_name" value="<?= e($__u['first_name']) ?>" required></div>
      <div class="field"><label>Last name *</label><input class="input" name="last_name" value="<?= e($__u['last_name']) ?>" required></div>
      <div class="field"><label>Phone</label><input class="input" name="phone" value="<?= e($__u['phone']) ?>"></div>
      <div class="field"><label>Alt Phone</label><input class="input" name="alt_phone" value="<?= e($__u['alt_phone'] ?? '') ?>"></div>
      <div class="field"><label>Language</label>
        <select class="input" name="language"><?php foreach (['en' => 'English', 'am' => 'አማርኛ (Amharic)', 'om' => 'Afaan Oromoo', 'ti' => 'ትግርኛ (Tigrinya)', 'so' => 'Soomaali'] as $k => $v): ?><option value="<?= $k ?>" <?= ($__u['language'] ?? 'en') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
      </div>
      <div class="field"><label>Birth date</label><input class="input" type="date" name="birth_date" value="<?= e($__u['birth_date'] ?? '') ?>"></div>
      <div class="field"><label>Gender</label>
        <select class="input" name="gender"><option value="">—</option><option value="m" <?= ($__u['gender'] ?? '') === 'm' ? 'selected' : '' ?>>Male</option><option value="f" <?= ($__u['gender'] ?? '') === 'f' ? 'selected' : '' ?>>Female</option></select>
      </div>
      <div class="field"><label>Emergency Contact</label><input class="input" name="emergency_contact" value="<?= e($__u['emergency_contact'] ?? '') ?>" placeholder="Name / Phone"></div>
    </div>
    <div class="settings-row full">
      <div class="field"><label>Address</label><input class="input" name="address" value="<?= e($__u['address'] ?? '') ?>" placeholder="City, Region"></div>
    </div>
    <div class="settings-row full">
      <div class="field"><label>Bio</label><textarea class="input" name="bio" rows="3"><?= e($__u['bio'] ?? '') ?></textarea></div>
    </div>
    <button class="btn btn-primary" style="margin-top:14px"><?= icon('save') ?> Save Profile</button>
  </form>
</div>

<?php elseif ($__tab === 'display'): ?>
<div class="settings-section">
  <form method="post" class="card settings-card" style="max-width:720px">
    <?= csrf_field() ?>
    <input type="hidden" name="tab_save" value="display">

    <!-- Theme Mode -->
    <h3 style="margin-top:0;margin-bottom:6px"><?= icon('palette') ?> Appearance</h3>
    <p class="small faint" style="margin-bottom:14px">Choose your preferred look</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px">
      <label class="theme-card<?= ($__u['theme'] ?? 'dark') === 'dark' ? ' active' : '' ?>">
        <input type="radio" name="theme" value="dark" style="display:none" <?= ($__u['theme'] ?? 'dark') === 'dark' ? 'checked' : '' ?>>
        <div style="font-size:28px;margin-bottom:8px"><?= icon('moon') ?></div>
        <div style="font-weight:700;font-size:14px">Dark</div>
        <div class="tiny faint" style="margin-top:4px">Easy on the eyes</div>
      </label>
      <label class="theme-card<?= ($__u['theme'] ?? 'dark') === 'light' ? ' active' : '' ?>">
        <input type="radio" name="theme" value="light" style="display:none" <?= ($__u['theme'] ?? 'dark') === 'light' ? 'checked' : '' ?>>
        <div style="font-size:28px;margin-bottom:8px"><?= icon('sun') ?></div>
        <div style="font-weight:700;font-size:14px">Light</div>
        <div class="tiny faint" style="margin-top:4px">Clean and bright</div>
      </label>
    </div>

    <!-- Accent Color -->
    <h3 style="margin:0 0 6px"><?= icon('droplet') ?> Accent Color</h3>
    <p class="small faint" style="margin-bottom:12px">Customize the primary color across the interface</p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px">
      <?php
      $colors = [
        'teal' => '#0d9488', 'blue' => '#0284c7', 'indigo' => '#4f46e5', 'purple' => '#7c3aed',
        'pink' => '#db2777', 'red' => '#ef4444', 'orange' => '#f97316', 'amber' => '#f59e0b',
        'emerald' => '#059669', 'cyan' => '#06b6d4', 'rose' => '#f43f5e', 'violet' => '#8b5cf6',
      ];
      $currentAccent = $__u['accent_color'] ?? 'teal';
      foreach ($colors as $name => $hex): ?>
        <div class="color-swatch<?= $currentAccent === $name ? ' active' : '' ?>" style="background:<?= $hex ?>" title="<?= ucfirst($name) ?>">
          <input type="radio" name="accent_color" value="<?= $name ?>" style="display:none" <?= $currentAccent === $name ? 'checked' : '' ?>>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Font Size -->
    <h3 style="margin:0 0 6px"><?= icon('type') ?> Font Size</h3>
    <p class="small faint" style="margin-bottom:12px">Adjust the base text size</p>
    <?php $currentSize = $__u['font_size'] ?? '14'; ?>
    <div class="font-size-preview" style="margin-bottom:10px">
      <span id="font-preview-text" style="font-size:<?= $currentSize ?>px;font-weight:600;color:var(--text)">The quick brown fox jumps over the lazy dog</span>
    </div>
    <div style="display:flex;gap:8px;align-items:center;margin-bottom:24px">
      <span class="tiny faint">A</span>
      <input type="range" name="font_size" id="font-size-slider" min="12" max="18" value="<?= e($currentSize) ?>" step="1" style="flex:1;accent-color:var(--accent)" oninput="document.getElementById('font-preview-text').style.fontSize=this.value+'px';document.getElementById('font-size-val').textContent=this.value+'px'">
      <span class="tiny faint" style="font-size:16px;font-weight:700">A</span>
      <span class="tiny faint" id="font-size-val"><?= e($currentSize) ?>px</span>
    </div>

    <!-- Sidebar Style -->
    <h3 style="margin:0 0 6px"><?= icon('sidebar') ?> Sidebar</h3>
    <p class="small faint" style="margin-bottom:12px">Customize sidebar appearance</p>
    <?php $sideStyle = $__u['sidebar_style'] ?? 'default'; ?>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:24px">
      <label class="display-option" style="cursor:pointer;flex-direction:column;gap:6px;text-align:center<?= $sideStyle === 'default' ? ';border-color:var(--accent);box-shadow:0 0 0 1px rgba(13,148,136,.2)' : '' ?>">
        <input type="radio" name="sidebar_style" value="default" style="display:none" <?= $sideStyle === 'default' ? 'checked' : '' ?>>
        <div style="width:40px;height:28px;border-radius:6px;background:var(--bg-elev);border:1px solid var(--border)"></div>
        <span class="small" style="font-weight:600">Default</span>
      </label>
      <label class="display-option" style="cursor:pointer;flex-direction:column;gap:6px;text-align:center<?= $sideStyle === 'compact' ? ';border-color:var(--accent);box-shadow:0 0 0 1px rgba(13,148,136,.2)' : '' ?>">
        <input type="radio" name="sidebar_style" value="compact" style="display:none" <?= $sideStyle === 'compact' ? 'checked' : '' ?>>
        <div style="width:20px;height:28px;border-radius:6px;background:var(--bg-elev);border:1px solid var(--border)"></div>
        <span class="small" style="font-weight:600">Compact</span>
      </label>
      <label class="display-option" style="cursor:pointer;flex-direction:column;gap:6px;text-align:center<?= $sideStyle === 'icons' ? ';border-color:var(--accent);box-shadow:0 0 0 1px rgba(13,148,136,.2)' : '' ?>">
        <input type="radio" name="sidebar_style" value="icons" style="display:none" <?= $sideStyle === 'icons' ? 'checked' : '' ?>>
        <div style="width:16px;height:28px;border-radius:6px;background:var(--bg-elev);border:1px solid var(--border)"></div>
        <span class="small" style="font-weight:600">Icons Only</span>
      </label>
    </div>

    <!-- Display Options -->
    <h3 style="margin:0 0 6px"><?= icon('eye') ?> Display Options</h3>
    <p class="small faint" style="margin-bottom:12px">Fine-tune the interface</p>
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px">
      <?php
      $opts = [
        ['animations', 'Animations', 'Enable smooth transitions and animations', $__u['show_animations'] ?? '1'],
        ['compact_mode', 'Compact Mode', 'Reduce spacing for more content on screen', $__u['compact_mode'] ?? '0'],
        ['show_avatars', 'Show Avatars', 'Display user avatars in lists and cards', $__u['show_avatars'] ?? '1'],
        ['show_borders', 'Glass Borders', 'Show glass border effects on cards', $__u['show_borders'] ?? '1'],
        ['gradients', 'Gradient Effects', 'Enable gradient backgrounds on elements', $__u['show_gradients'] ?? '1'],
        ['blur_effects', 'Blur Effects', 'Enable backdrop blur glass effects', $__u['blur_effects'] ?? '1'],
        ['reduce_motion', 'Reduce Motion', 'Minimize animations for accessibility', $__u['reduce_motion'] ?? '0'],
      ];
      foreach ($opts as [$key, $label, $desc, $val]): ?>
        <div class="display-option">
          <div style="flex:1">
            <div style="font-weight:600;font-size:13.5px"><?= $label ?></div>
            <div class="tiny faint" style="margin-top:2px"><?= $desc ?></div>
          </div>
          <label class="ios-toggle<?= $val === '1' ? ' active' : '' ?>" onclick="this.classList.toggle('active')">
            <input type="hidden" name="<?= $key ?>" value="<?= $val === '1' ? '1' : '0' ?>">
            <input type="checkbox" <?= $val === '1' ? 'checked' : '' ?> style="display:none" onchange="this.previousElementSibling.value=this.checked?'1':'0'">
          </label>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Content Width -->
    <h3 style="margin:0 0 6px"><?= icon('maximize') ?> Content Width</h3>
    <p class="small faint" style="margin-bottom:12px">Maximum width of the main content area</p>
    <?php $cw = $__u['content_width'] ?? '1400'; ?>
    <div style="display:flex;gap:8px;margin-bottom:24px">
      <?php foreach (['1000' => 'Narrow', '1200' => 'Medium', '1400' => 'Wide', '1600' => 'Full'] as $w => $label): ?>
        <button type="button" class="btn <?= $cw === $w ? 'btn-primary' : 'btn-ghost' ?> btn-sm" onclick="this.parentElement.querySelectorAll('.btn').forEach(b=>{b.className='btn btn-ghost btn-sm'});this.className='btn btn-primary btn-sm';this.form.content_width.value='<?= $w ?>'"><?= $label ?></button>
      <?php endforeach; ?>
      <input type="hidden" name="content_width" value="<?= e($cw) ?>">
    </div>

    <!-- Language -->
    <h3 style="margin:0 0 6px"><?= icon('globe') ?> Language</h3>
    <p class="small faint" style="margin-bottom:12px">Interface language</p>
    <select class="input" name="language" style="max-width:300px"><?php foreach (['en' => 'English', 'am' => 'አማርኛ (Amharic)', 'om' => 'Afaan Oromoo', 'ti' => 'ትግርኛ (Tigrinya)', 'so' => 'Soomaali'] as $k => $v): ?><option value="<?= $k ?>" <?= ($__u['language'] ?? 'en') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>

    <button class="btn btn-primary" style="margin-top:24px"><?= icon('save') ?> Save Display Settings</button>
  </form>
</div>

<?php elseif ($__tab === 'security'): ?>
<div class="settings-section">
  <?php if (!empty($hena_new)): ?>
  <div class="card settings-card" style="border-color:var(--accent);margin-bottom:18px">
    <h3 style="margin-top:0;color:var(--accent)"><?= icon('download') ?> USB Key Ready</h3>
    <p class="small" style="margin:8px 0">Your .hena key file has been generated. Download and save it to your USB stick.</p>
    <a class="btn btn-primary" href="<?= e(url('settings/hena_download')) ?>"><?= icon('download') ?> Download .hena file</a>
  </div>
  <?php endif; ?>

  <div class="card settings-card">
    <h3 style="margin-top:0;margin-bottom:16px"><?= icon('shield') ?> Two-Factor Authentication</h3>
    <div class="d-flex" style="align-items:center;gap:12px;margin-bottom:18px">
      <span>Status:</span>
      <?php if ($mode === 'hena'): ?>
        <span class="security-badge on"><?= icon('check') ?> USB Key (.hena)</span>
      <?php elseif ($mode === 'totp'): ?>
        <span class="security-badge on"><?= icon('check') ?> TOTP Active</span>
      <?php else: ?>
        <span class="security-badge off"><?= icon('x') ?> Not configured</span>
      <?php endif; ?>
    </div>

    <?php if ($mode === 'off'): ?>
      <p class="small faint" style="margin-bottom:14px">Protect your account with a second layer of security.</p>
      <form method="post" style="display:flex;gap:8px">
        <?= csrf_field() ?>
        <button class="btn btn-primary" name="enable_2fa" value="1"><?= icon('key') ?> Enable USB 2FA (.hena)</button>
      </form>
    <?php elseif ($mode === 'totp'): ?>
      <form method="post">
        <?= csrf_field() ?>
        <p class="small faint" style="margin-bottom:10px">Enter your 6-digit code to disable TOTP:</p>
        <div class="d-flex" style="gap:8px;align-items:end">
          <div class="field" style="margin:0"><input class="input" name="code" placeholder="000000" maxlength="6" style="width:140px"></div>
          <button class="btn btn-danger" name="disable_2fa" value="1">Disable</button>
        </div>
      </form>
    <?php else: ?>
      <form method="post">
        <?= csrf_field() ?>
        <p class="small faint" style="margin-bottom:10px">USB 2FA is active. Disable by clicking below.</p>
        <button class="btn btn-danger" name="disable_2fa" value="1"><?= icon('x') ?> Disable USB 2FA</button>
      </form>
    <?php endif; ?>
  </div>

  <div class="card settings-card" style="margin-top:18px">
    <h3 style="margin-top:0;margin-bottom:6px"><?= icon('lock') ?> Change Password</h3>
    <p class="small faint" style="margin-bottom:14px">Use a strong, unique password.</p>
    <form method="post" action="<?= e(url('settings/password')) ?>">
      <?= csrf_field() ?>
      <div class="settings-row">
        <div class="field"><label>Current password</label><input class="input" type="password" name="current" required></div>
      </div>
      <div class="settings-row">
        <div class="field"><label>New password</label><input class="input" type="password" name="new" required></div>
        <div class="field"><label>Confirm new password</label><input class="input" type="password" name="confirm" required></div>
      </div>
      <button class="btn btn-primary" style="margin-top:10px"><?= icon('save') ?> Change Password</button>
    </form>
  </div>
</div>

<?php elseif ($__tab === 'fayda'): ?>
<div class="settings-section">
  <div class="fayda-card" style="margin-bottom:20px">
    <div style="position:relative;z-index:1">
      <h3 style="margin-top:0;margin-bottom:6px"><?= icon('badge') ?> Fayda Digital ID Integration</h3>
      <p class="small" style="margin:0 0 6px">Link your Ethiopian Fayda National Digital ID for official verification.</p>
      <p class="tiny faint" style="margin:0">Fayda ID is Ethiopia's national digital identity system. Linking it enables instant verification of your credentials across institutions.</p>
    </div>
  </div>

  <form method="post" class="card settings-card">
    <?= csrf_field() ?>
    <input type="hidden" name="tab_save" value="fayda">
    <div class="settings-row">
      <div class="field">
        <label>Fayda ID Number</label>
        <input class="input" name="fayda_id" value="<?= e($__u['fayda_id'] ?? '') ?>" placeholder="e.g. FD-1234567890">
        <p class="tiny faint" style="margin-top:4px">Your 10-digit Fayda identification number</p>
      </div>
      <div class="field">
        <label>National ID (FAN)</label>
        <input class="input" name="national_id" value="<?= e($__u['national_id'] ?? '') ?>" placeholder="e.g. ETH-123456789">
        <p class="tiny faint" style="margin-top:4px">Federal Authentication Network number</p>
      </div>
    </div>
    <div class="settings-row full">
      <div class="field">
        <label>Employee ID</label>
        <input class="input" name="employee_id" value="<?= e($__u['employee_id'] ?? '') ?>" placeholder="School/institution employee number">
      </div>
    </div>
    <div class="settings-row full">
      <div class="field">
        <label>Birth Certificate Number</label>
        <input class="input" name="birth_cert_number" value="<?= e($__u['birth_cert_number'] ?? '') ?>" placeholder="Official birth certificate number">
      </div>
    </div>
    <?php if (!empty($__u['fayda_id'])): ?>
      <div class="d-flex" style="gap:8px;align-items:center;margin-top:12px;padding:10px 14px;border-radius:10px;background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.2)">
        <span style="color:var(--success)"><?= icon('check-circle') ?></span>
        <span class="small" style="color:var(--success)">Fayda ID linked: <?= e($__u['fayda_id']) ?></span>
      </div>
    <?php endif; ?>
    <button class="btn btn-primary" style="margin-top:16px"><?= icon('save') ?> Save ID Information</button>
  </form>
</div>

<?php elseif ($__tab === 'sessions'): ?>
<div class="settings-section">
  <div class="card settings-card">
    <div class="d-flex" style="align-items:center;justify-content:space-between;margin-bottom:18px">
      <div>
        <h3 style="margin:0"><?= icon('monitor') ?> Active Sessions</h3>
        <p class="tiny faint" style="margin-top:4px">Manage devices logged into your account</p>
      </div>
      <form method="post">
        <?= csrf_field() ?>
        <button class="btn btn-danger btn-sm" name="kill_all" value="1" onclick="return confirm('Revoke ALL sessions?')"><?= icon('power') ?> Revoke All</button>
      </form>
    </div>
    <div style="display:flex;flex-direction:column;gap:8px">
      <?php $currentSelector = $_COOKIE['remember'] ? explode(':', $_COOKIE['remember'])[0] : ''; ?>
      <?php foreach ($sessions as $i => $s): ?>
        <?php $isCurrent = ($s['selector'] ?? '') === $currentSelector; ?>
        <div class="session-item<?= $isCurrent ? ' current' : '' ?>">
          <span style="font-size:24px"><?= $isCurrent ? icon('smartphone') : icon('monitor') ?></span>
          <div style="flex:1;min-width:0">
            <div class="d-flex" style="gap:8px;align-items:center">
              <b class="small"><?= e(mb_strimwidth($s['user_agent'] ?? 'Unknown', 0, 50, '…')) ?></b>
              <?php if ($isCurrent): ?><span class="security-badge on" style="font-size:10px;padding:2px 8px">Current</span><?php endif; ?>
            </div>
            <p class="tiny faint" style="margin-top:2px">Expires: <?= e($s['expires_at'] ?? '—') ?></p>
          </div>
          <?php if (!$isCurrent): ?>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <button class="btn btn-ghost btn-sm" name="kill" value="<?= (int)$s['id'] ?>" style="color:var(--danger);padding:4px 8px" onclick="return confirm('Revoke this session?')"><?= icon('x') ?></button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (empty($sessions)): ?>
        <p class="muted small" style="text-align:center;padding:20px 0">No active sessions found.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
