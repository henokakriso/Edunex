<?php /* Admin: Badges & Achievements */
$badgeIcons = ['medal-gold','medal-silver','medal-bronze','trophy','crown','star','rocket','bolt','fire','gem','heart','thumbs-up','graduation','handshake','courses'];
?>
<div class="page-head">
  <div>
    <h1><?= icon('medal') ?> Badges & Achievements</h1>
    <p class="sub">Create, edit and award badges that students earn</p>
  </div>
  <button class="btn btn-primary" onclick="toggleBadgeForm()"><?= icon('plus') ?> New badge</button>
</div>

<form method="post" class="card" id="badge-form" style="display:none;margin-bottom:18px">
  <?= csrf_field() ?>
  <input type="hidden" name="badge_id" id="badge_id" value="">
  <h3 class="card-title" style="margin-top:0"><?= icon('medal') ?> <span id="badge-form-title">Create badge</span></h3>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">Name *</label><input class="input" name="name" id="f_name" required placeholder="Quiz Champion"></div>
    <div class="flex-col"><label class="small faint">Icon</label>
      <select class="input" name="icon" id="f_icon"><?php foreach ($badgeIcons as $ic): ?><option value="<?= $ic ?>"><?= str_replace('-', ' ', ucfirst($ic)) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Category</label>
      <select class="input" name="category"><?php foreach ($cats as $k => $l): ?><option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">XP required</label><input class="input" type="number" name="xp_required" value="100" min="0"></div>
    <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Description</label><textarea class="input" name="description" rows="2" placeholder="What this badge means…"></textarea></div>
  </div>
  <div class="flex gap-8" style="margin-top:12px">
    <button class="btn btn-success" name="save_badge" value="1"><?= icon('check') ?> Save badge</button>
    <button type="button" class="btn btn-ghost" onclick="cancelBadgeForm()"><?= icon('ban-circle') ?> Cancel</button>
  </div>
</form>

<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:14px">
  <?php foreach ($all as $b): ?>
    <div class="card" style="padding:16px">
      <div class="flex-between" style="align-items:flex-start">
        <div class="flex gap-8" style="align-items:center">
          <span class="badge-ico" style="background:var(--accent-soft)"><?= icon($b['icon']) ?></span>
          <b class="small"><?= e($b['name']) ?></b>
        </div>
        <form method="post" class="inline" data-confirm="Delete this badge permanently?"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" name="delete_badge" value="<?= (int)$b['id'] ?>" title="Delete badge"><?= icon('trash') ?></button></form>
      </div>
      <p class="tiny muted" style="margin:6px 0"><?= e($b['description']) ?></p>
      <div class="tiny faint">
        <?= e($cats[$b['category']] ?? $b['category']) ?> · <?= (int)$b['xp_required'] ?> XP ·
        <?= (int)($earnedCount[$b['id']] ?? 0) ?> earned
      </div>
      <div class="flex-between" style="margin-top:10px">
        <span class="badge badge-muted"><?= icon('check-circle') ?> <?= (int)($earnedCount[$b['id']] ?? 0) ?> students</span>
        <button class="btn btn-sm btn-ghost" onclick="editBadge(<?= (int)$b['id'] ?>, '<?= e(addslashes($b['name'])) ?>', '<?= e($b['icon']) ?>', <?= (int)$b['xp_required'] ?>, '<?= e(addslashes($b['description'])) ?>')"><?= icon('edit') ?> Edit</button>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$all): ?><p class="muted small">No badges yet. Create your first badge above.</p><?php endif; ?>
</div>

<div class="card" style="margin-top:18px">
  <h3 class="card-title" style="margin-top:0"><?= icon('gift') ?> Award a badge</h3>
  <form method="post" class="flex gap-8">
    <?= csrf_field() ?>
    <select class="input" name="badge_id" style="max-width:280px" required>
      <option value="">— Badge —</option>
      <?php foreach ($all as $b): ?><option value="<?= (int)$b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?>
    </select>
    <input class="input flex-1" name="student_id" placeholder="Student ID or email" required>
    <button class="btn btn-primary" name="award_badge" value="1"><?= icon('gift') ?> Award</button>
  </form>
</div>

<script>
function toggleBadgeForm() {
  const f = document.getElementById('badge-form');
  const isVisible = f.style.display !== 'none';
  if (isVisible) { cancelBadgeForm(); } else { f.style.display = 'block'; document.getElementById('badge-form-title').textContent = 'Create badge'; document.getElementById('badge_id').value = ''; window.scrollTo({top:0,behavior:'smooth'}); }
}
function cancelBadgeForm() {
  document.getElementById('badge-form').style.display = 'none';
  document.getElementById('badge_id').value = '';
  document.getElementById('badge-form-title').textContent = 'Create badge';
  document.querySelector('form#badge-form input[name=name]').value = '';
  document.querySelector('form#badge-form textarea[name=description]').value = '';
  document.querySelector('form#badge-form input[name=xp_required]').value = '100';
}
function editBadge(id, name, icon, xp, desc) {
  document.getElementById('badge-form').style.display = 'block';
  document.getElementById('badge_id').value = id;
  document.getElementById('badge-form-title').textContent = 'Edit badge';
  document.querySelector('form#badge-form input[name=name]').value = name;
  document.getElementById('f_icon').value = icon;
  document.querySelector('form#badge-form textarea[name=description]').value = desc;
  document.querySelector('form#badge-form input[name=xp_required]').value = xp;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
