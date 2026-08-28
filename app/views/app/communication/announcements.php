<?php /* Announcements view — end-user list */
$asBadge = ['none'=>'','pending'=>'badge-warning','approved'=>'badge-success','rejected'=>'badge-danger'];
?>
<style>
.ann-card{display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:12px;border:1px solid var(--border);background:var(--bg-elev);transition:border-color .15s,box-shadow .15s;cursor:pointer;text-decoration:none;color:inherit}
.ann-card:hover{border-color:color-mix(in srgb,var(--accent) 40%,var(--border));box-shadow:0 2px 10px rgba(0,0,0,.04)}
.ann-card:focus-visible{border-color:var(--accent);box-shadow:0 0 0 3px rgba(99,102,241,.15);outline:none}
.ann-card.pinned{border-left:3px solid var(--accent)}
.ann-title{font-size:14.5px;font-weight:600;color:var(--text);line-height:1.3}
.ann-snippet{font-size:12.5px;color:var(--text-secondary);margin-top:3px;line-height:1.5}
.ann-tags{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:6px}
</style>

<div class="page-head">
  <div>
    <h1><?= icon('megaphone') ?> Announcements</h1>
    <p class="sub">Updates from your school, region, and ministry</p>
  </div>
</div>

<div style="display:flex;flex-direction:column;gap:8px">
  <?php foreach ($anns as $a): ?>
    <a href="<?= e(url('communication/announcement&id=' . $a['id'])) ?>" class="ann-card <?= $a['pinned'] ? 'pinned' : '' ?>" tabindex="0">
      <div style="flex:1;min-width:0">
        <div class="ann-title"><?= e($a['title']) ?></div>
        <div class="ann-snippet"><?= e(mb_strimwidth($a['content'], 0, 140, '…')) ?></div>
        <div class="ann-tags">
          <?php if ($a['pinned']): ?><span class="badge badge-accent" style="font-size:10px"><?= icon('pin') ?> Pinned</span><?php endif; ?>
          <?php if ($a['course_title']): ?><span class="badge badge-accent" style="font-size:10px"><?= e($a['course_title']) ?></span><?php endif; ?>
          <span class="tiny faint"><b><?= e($a['author_name']) ?></b> · <?= e($a['school_name']) ?> · <?= e(time_ago($a['created_at'])) ?></span>
        </div>
      </div>
      <span style="color:var(--muted);font-size:14px;flex-shrink:0">&#8250;</span>
    </a>
  <?php endforeach; ?>
  <?php if (!$anns): ?>
    <div style="padding:48px;text-align:center;color:var(--muted);font-size:13px">No announcements for you yet.</div>
  <?php endif; ?>
</div>
