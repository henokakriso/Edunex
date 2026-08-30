<?php /* Admin announcements view */
$asBadge = ['none'=>'badge-muted','pending'=>'badge-warning','approved'=>'badge-success','rejected'=>'badge-danger'];
?>
<style>
.ann-row{display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:12px;border:1px solid var(--glass-border);margin-bottom:6px;background:var(--glass-bg);backdrop-filter:blur(20px) saturate(150%);-webkit-backdrop-filter:blur(20px) saturate(150%);transition:all .25s cubic-bezier(.4,0,.2,1);cursor:default;position:relative;overflow:hidden}
.ann-row::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.06) 0%,transparent 40%,rgba(255,255,255,.02) 100%);pointer-events:none;transition:background .3s ease}
.ann-row:hover{background:var(--glass-hover-bg);border-color:var(--glass-hover-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.45),inset 0 -1px 0 rgba(255,255,255,.06),inset 1px 0 0 rgba(255,255,255,.2),inset -1px 0 0 rgba(255,255,255,.06),var(--glass-hover-shadow)}
.ann-row:hover::before{background:linear-gradient(135deg,rgba(255,255,255,.12) 0%,rgba(255,255,255,.03) 50%,rgba(255,255,255,.06) 100%)}
.ann-row:focus-visible{outline:none;border-color:rgba(255,255,255,.25);box-shadow:0 0 0 2px var(--bg),0 0 0 4px rgba(255,255,255,.12),inset 0 1px 0 rgba(255,255,255,.4),0 0 16px rgba(255,255,255,.04)}
.ann-row:active{transform:scale(.998)}
.ann-title{font-size:14px;font-weight:600;color:var(--text);line-height:1.3}
.ann-content{font-size:12.5px;color:var(--text-secondary);margin-top:3px;line-height:1.5}
.ann-meta{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:5px}
.ann-actions{margin-left:auto;display:flex;gap:6px;flex-shrink:0}
</style>

<div class="page-head">
  <div>
    <h1><?= icon('megaphone') ?> Announcements</h1>
    <p class="sub">Broadcast globally, to a region, or a zone — regional admins approve targeted announcements</p>
  </div>
  <button class="btn btn-primary" data-open-modal="new-ann-modal">+ New announcement</button>
</div>

<!-- Create Announcement Modal -->
<div class="modal-backdrop" id="new-ann-modal">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <h3>New Announcement</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="create_ann" value="1">
        <input type="hidden" name="audience" id="ann-audience" value="all">

        <div class="flex-col" style="margin-bottom:14px"><label class="small faint">Title *</label><input class="input" name="title" required placeholder="Announcement title"></div>
        <div class="flex-col" id="course-wrap" style="display:none;margin-bottom:14px"><label class="small faint">Course</label>
          <select class="input" name="course_id"><option value="0">— Select course —</option><?php foreach ($courses as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
          <div class="flex-col"><label class="small faint">Target Region <span class="tiny faint">(optional)</span></label>
            <select class="input" name="target_region" id="ann-region" onchange="onTargetChange()">
              <option value="">— All regions —</option>
              <?php foreach ($regions as $r): ?><option value="<?= e($r['region']) ?>"><?= e($r['region']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="flex-col"><label class="small faint">Target Zone <span class="tiny faint">(optional)</span></label>
            <select class="input" name="target_zone" id="ann-zone" onchange="onTargetChange()">
              <option value="">— All zones —</option>
              <?php foreach ($zones as $z): ?>
                <option value="<?= e($z['zone_name']) ?>" data-region="<?= e($z['region_name']) ?>"><?= e($z['zone_name']) ?> (<?= e($z['region_name']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="flex-col" style="margin-bottom:14px">
          <label class="small faint">Content *</label>
          <textarea class="input" name="content" rows="5" required placeholder="Write your announcement..."></textarea>
        </div>
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;margin-bottom:16px"><input type="checkbox" name="pinned" value="1"> Pin to top</label>

        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" type="submit"><?= icon('megaphone') ?> Post</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card" style="padding:0">
  <div style="padding:18px 20px;border-bottom:1px solid var(--border)">
    <h3 style="font-size:15px;font-weight:700;margin:0"><?= icon('megaphone') ?> Recent announcements (<?= count($anns) ?>)</h3>
  </div>
  <?php if ($anns): ?>
    <div style="padding:8px 12px">
      <?php foreach ($anns as $a): ?>
        <div class="ann-row" tabindex="0">
          <div style="flex:1;min-width:0">
            <div class="ann-title"><?= e($a['title']) ?></div>
            <div class="ann-content"><?= e(mb_strimwidth($a['content'], 0, 160, '…')) ?></div>
            <div class="ann-meta">
              <?php if ($a['pinned']): ?><span class="badge badge-accent" style="font-size:10px">PINNED</span><?php endif; ?>
              <?php if ($a['target_region']): ?><span class="badge badge-info" style="font-size:10px"><?= icon('map') ?> <?= e($a['target_region']) ?></span><?php endif; ?>
              <?php if ($a['target_zone']): ?><span class="badge badge-info" style="font-size:10px"><?= icon('pin') ?> <?= e($a['target_zone']) ?></span><?php endif; ?>
              <?php if ($a['approval_status'] !== 'none'): ?><span class="badge <?= $asBadge[$a['approval_status']] ?? 'badge-muted' ?>" style="font-size:10px"><?= e(ucfirst($a['approval_status'])) ?></span><?php endif; ?>
              <?php if ($a['audience'] !== 'all'): ?><span class="badge badge-muted" style="font-size:10px"><?= e(ucfirst($a['audience'])) ?></span><?php endif; ?>
              <span class="tiny faint"><b><?= e($a['author_name']) ?></b> · <?= e(date('M j, g:i A', strtotime($a['created_at']))) ?></span>
            </div>
          </div>
          <div class="ann-actions">
            <form method="post" class="inline" data-confirm="Delete this announcement?">
              <?= csrf_field() ?><input type="hidden" name="delete_ann" value="<?= (int)$a['id'] ?>">
              <button class="btn btn-sm btn-ghost" style="font-size:11px;color:var(--danger)"><?= icon('trash') ?></button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div style="padding:40px;text-align:center;color:var(--muted);font-size:13px">No announcements yet.</div>
  <?php endif; ?>
</div>

<script>
function onTargetChange(){
  const r=document.getElementById('ann-region').value;
  const z=document.getElementById('ann-zone').value;
  const hasScope=r!==''||z!=='';
  document.getElementById('ann-audience').value=hasScope?'all':'all';
  document.getElementById('course-wrap').style.display='none';
  const zoneOpts=document.getElementById('ann-zone').querySelectorAll('option[data-region]');
  zoneOpts.forEach(o=>{o.style.display=!r||o.dataset.region===r?'':'none'});
  if(r&&document.getElementById('ann-zone').value)document.getElementById('ann-zone').value='';
}
</script>
