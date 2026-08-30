<?php /* Admin: Badges & Achievements — colorful medal styles */
$badgeIcons = ['medal-gold','medal-silver','medal-bronze','trophy','crown','star','rocket','bolt','fire','gem','heart','thumbs-up','graduation','handshake','courses'];
$catColors = ['learning'=>'#6366f1','streak'=>'#f59e0b','quiz'=>'#8b5cf6','attendance'=>'#22c55e','community'=>'#ec4899','level'=>'#ef4444'];
// Medal color palette per icon
$medalStyles = [
  'medal-gold'   => ['bg'=>'linear-gradient(135deg,#fbbf24,#f59e0b,#d97706)','border'=>'#f59e0b','shadow'=>'rgba(245,158,11,.4)','ribbon'=>'#ef4444'],
  'medal-silver' => ['bg'=>'linear-gradient(135deg,#e5e7eb,#d1d5db,#9ca3af)','border'=>'#9ca3af','shadow'=>'rgba(156,163,175,.4)','ribbon'=>'#6366f1'],
  'medal-bronze' => ['bg'=>'linear-gradient(135deg,#f97316,#ea580c,#c2410c)','border'=>'#ea580c','shadow'=>'rgba(234,88,12,.4)','ribbon'=>'#22c55e'],
  'trophy'       => ['bg'=>'linear-gradient(135deg,#fbbf24,#f59e0b)','border'=>'#d97706','shadow'=>'rgba(217,119,6,.4)','ribbon'=>'#ef4444'],
  'crown'        => ['bg'=>'linear-gradient(135deg,#fbbf24,#f59e0b,#b45309)','border'=>'#b45309','shadow'=>'rgba(180,83,9,.4)','ribbon'=>'#7c3aed'],
  'star'         => ['bg'=>'linear-gradient(135deg,#fbbf24,#f59e0b)','border'=>'#f59e0b','shadow'=>'rgba(245,158,11,.4)','ribbon'=>'#3b82f6'],
  'rocket'       => ['bg'=>'linear-gradient(135deg,#6366f1,#818cf8)','border'=>'#6366f1','shadow'=>'rgba(99,102,241,.4)','ribbon'=>'#f59e0b'],
  'bolt'         => ['bg'=>'linear-gradient(135deg,#f59e0b,#eab308)','border'=>'#eab308','shadow'=>'rgba(234,179,8,.4)','ribbon'=>'#ef4444'],
  'fire'         => ['bg'=>'linear-gradient(135deg,#ef4444,#f97316,#f59e0b)','border'=>'#ef4444','shadow'=>'rgba(239,68,68,.4)','ribbon'=>'#fbbf24'],
  'gem'          => ['bg'=>'linear-gradient(135deg,#8b5cf6,#a78bfa,#c084fc)','border'=>'#8b5cf6','shadow'=>'rgba(139,92,246,.4)','ribbon'=>'#f59e0b'],
  'heart'        => ['bg'=>'linear-gradient(135deg,#ec4899,#f472b6,#f9a8d4)','border'=>'#ec4899','shadow'=>'rgba(236,72,153,.4)','ribbon'=>'#8b5cf6'],
  'thumbs-up'    => ['bg'=>'linear-gradient(135deg,#22c55e,#10b981)','border'=>'#22c55e','shadow'=>'rgba(34,197,94,.4)','ribbon'=>'#f59e0b'],
  'graduation'   => ['bg'=>'linear-gradient(135deg,#1e293b,#334155,#475569)','border'=>'#334155','shadow'=>'rgba(51,65,85,.4)','ribbon'=>'#fbbf24'],
  'handshake'    => ['bg'=>'linear-gradient(135deg,#6366f1,#818cf8)','border'=>'#6366f1','shadow'=>'rgba(99,102,241,.4)','ribbon'=>'#22c55e'],
  'courses'      => ['bg'=>'linear-gradient(135deg,#3b82f6,#60a5fa)','border'=>'#3b82f6','shadow'=>'rgba(59,130,246,.4)','ribbon'=>'#f59e0b'],
];
?>
<style>
.badge-card{position:relative;border-radius:16px;overflow:hidden;transition:all .2s}
.badge-card:hover{transform:translateY(-3px);backdrop-filter: blur(40px) saturate(200%); -webkit-backdrop-filter: blur(40px) saturate(200%);box-shadow: 0 0 0 1px rgba(255,255,255,.2), inset 0 1px 1px rgba(255,255,255,.35), 0 8px 32px rgba(0,0,0,.1);}
.badge-medal{position:relative;width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;color:#fff;border:3px solid rgba(255,255,255,.4);box-shadow:0 4px 12px var(--medal-shadow);margin:0 auto 10px}
.badge-medal::after{content:'';position:absolute;bottom:-8px;left:50%;transform:translateX(-50%);width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-top:10px solid var(--medal-ribbon)}
.badge-medal .medal-shine{position:absolute;top:4px;left:8px;width:12px;height:6px;background:rgba(255,255,255,.4);border-radius:50%;transform:rotate(-30deg)}
.badge-xp{display:inline-block;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(99,102,241,.1);color:#6366f1}
.badge-earned{display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;background:rgba(34,197,94,.1);color:#22c55e}
.badge-cat{display:inline-block;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600}
</style>

<div class="page-head">
  <div>
    <h1><?= icon('medal') ?> Badges & Achievements</h1>
    <p class="sub">Create, edit and award badges that students earn</p>
  </div>
  <button class="btn btn-primary" data-open-modal="new-badge-modal"><?= icon('plus') ?> New badge</button>
</div>

<!-- Create Badge Modal -->
<div class="modal-backdrop" id="new-badge-modal">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <h3 id="badge-form-title">New Badge</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post" id="badge-form">
        <?= csrf_field() ?>
        <input type="hidden" name="badge_id" id="badge_id" value="">

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:14px">
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

        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" type="submit" name="save_badge" value="1"><?= icon('check') ?> Save Badge</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
  <?php foreach ($all as $b):
    $ms = $medalStyles[$b['icon']] ?? $medalStyles['medal-gold'];
    $cc = $catColors[$b['category']] ?? '#6366f1';
    $earned = (int)($earnedCount[$b['id']] ?? 0);
  ?>
    <div class="card badge-card" style="padding:20px;text-align:center;--medal-shadow:<?= $ms['shadow'] ?>;--medal-ribbon:<?= $ms['ribbon'] ?>">
      <!-- Medal icon -->
      <div class="badge-medal" style="background:<?= $ms['bg'] ?>;border-color:<?= $ms['border'] ?>">
        <span class="medal-shine"></span>
        <?= icon($b['icon']) ?>
      </div>
      <!-- Name -->
      <h4 style="margin:14px 0 4px;font-size:14px;font-weight:700"><?= e($b['name']) ?></h4>
      <!-- Description -->
      <p class="tiny muted" style="margin:0 0 10px;line-height:1.4"><?= e($b['description']) ?></p>
      <!-- Tags -->
      <div class="flex-center gap-6" style="flex-wrap:wrap;margin-bottom:10px">
        <span class="badge-cat" style="background:<?= $cc ?>15;color:<?= $cc ?>"><?= e($cats[$b['category']] ?? $b['category']) ?></span>
        <span class="badge-xp"><?= (int)$b['xp_required'] ?> XP</span>
        <span class="badge-earned"><?= icon('check-circle') ?> <?= $earned ?> earned</span>
      </div>
      <!-- Actions -->
      <div class="flex-between" style="margin-top:8px;padding-top:8px;border-top:1px solid var(--border)">
        <span class="tiny faint"><?= $earned ?> students</span>
        <div class="flex gap-4">
          <button class="btn btn-sm btn-ghost" onclick="editBadge(<?= (int)$b['id'] ?>, '<?= e(addslashes($b['name'])) ?>', '<?= e($b['icon']) ?>', <?= (int)$b['xp_required'] ?>, '<?= e(addslashes($b['description'])) ?>')"><?= icon('edit') ?></button>
          <form method="post" class="inline" data-confirm="Delete this badge permanently?"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" name="delete_badge" value="<?= (int)$b['id'] ?>" title="Delete"><?= icon('trash') ?></button></form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$all): ?><p class="muted small" style="grid-column:1/-1;text-align:center;padding:40px">No badges yet. Create your first badge above.</p><?php endif; ?>
</div>

<script>
function toggleBadgeForm() {
  const m = document.getElementById('new-badge-modal');
  if (m.classList.contains('open')) { cancelBadgeForm(); } else { m.classList.add('open'); document.getElementById('badge-form-title').textContent = 'New Badge'; document.getElementById('badge_id').value = ''; }
}
function cancelBadgeForm() {
  document.getElementById('new-badge-modal').classList.remove('open');
  document.getElementById('badge_id').value = '';
  document.getElementById('badge-form-title').textContent = 'New Badge';
  document.querySelector('form#badge-form input[name=name]').value = '';
  document.querySelector('form#badge-form textarea[name=description]').value = '';
  document.querySelector('form#badge-form input[name=xp_required]').value = '100';
}
function editBadge(id, name, icon, xp, desc) {
  const m = document.getElementById('new-badge-modal');
  m.classList.add('open');
  document.getElementById('badge_id').value = id;
  document.getElementById('badge-form-title').textContent = 'Edit Badge';
  document.querySelector('form#badge-form input[name=name]').value = name;
  document.getElementById('f_icon').value = icon;
  document.querySelector('form#badge-form textarea[name=description]').value = desc;
  document.querySelector('form#badge-form input[name=xp_required]').value = xp;
}
</script>
