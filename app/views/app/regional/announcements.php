<?php /* Regional announcements — post to assigned schools + approve ministry announcements */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('megaphone') ?> Announcements</h1>
    <p class="sub">Approve ministry announcements for your region · Post to your assigned schools</p>
  </div>
</div>

<?php if ($pending): ?>
<div style="margin-bottom:20px">
  <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-faint);margin-bottom:10px;padding-bottom:6px;border-bottom:2px solid var(--warning);display:flex;align-items:center;gap:8px">
    <?= icon('clock') ?> Pending Ministry Announcements (<?= count($pending) ?>)
  </div>
  <?php foreach ($pending as $a): ?>
    <div class="card" style="border-left:3px solid var(--warning);margin-bottom:10px">
      <div style="display:flex;align-items:start;justify-content:space-between;gap:12px">
        <div style="flex:1">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <b style="font-size:14px"><?= e($a['title']) ?></b>
            <span class="badge badge-warning" style="font-size:10px">PENDING APPROVAL</span>
            <?php if ($a['target_region']): ?><span class="badge badge-info" style="font-size:10px"><?= icon('map') ?> <?= e($a['target_region']) ?></span><?php endif; ?>
            <?php if ($a['target_zone']): ?><span class="badge badge-info" style="font-size:10px"><?= icon('map') ?> <?= e($a['target_zone']) ?></span><?php endif; ?>
          </div>
          <p style="font-size:13px;color:var(--text-secondary);margin-top:6px;line-height:1.5"><?= nl2br(e($a['content'])) ?></p>
          <p class="tiny faint" style="margin-top:6px">By <b><?= e($a['author_name']) ?></b> · <?= e(date('M j, g:i A', strtotime($a['created_at']))) ?></p>
        </div>
        <div style="display:flex;gap:6px;flex-shrink:0">
          <form method="post" data-confirm="Approve this announcement? It will be delivered to all users in <?= e($a['target_region'] ?: $a['target_zone']) ?>.">
            <?= csrf_field() ?><input type="hidden" name="approve_ann" value="<?= (int)$a['id'] ?>">
            <button class="btn btn-sm btn-success" style="font-size:12px">✓ Approve</button>
          </form>
          <form method="post" data-confirm="Reject this announcement?">
            <?= csrf_field() ?><input type="hidden" name="reject_ann" value="<?= (int)$a['id'] ?>">
            <button class="btn btn-sm btn-danger" style="font-size:12px"><?= icon('x') ?> Reject</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('send') ?> New announcement</h3>
    <form method="post" class="flex-col gap-10">
      <?= csrf_field() ?>
      <div class="grid2">
        <div class="flex-col"><label class="small faint">School *</label>
          <select class="input" name="school_id" required>
            <?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Audience</label>
          <select class="input" name="audience">
            <option value="all">Everyone</option><option value="students">Students</option>
            <option value="teachers">Teachers</option><option value="parents">Parents</option>
          </select>
        </div>
      </div>
      <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" required maxlength="200"></div>
      <div class="flex-col"><label class="small faint">Content *</label><textarea class="input" name="content" rows="4" required></textarea></div>
      <div><button class="btn btn-success" type="submit"><?= icon('rocket') ?> Publish</button></div>
    </form>
  </div>

  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('menu') ?> Recent announcements</h3>
    <?php foreach ($rows as $a): ?>
      <div class="list-row" style="padding:9px 0">
        <div class="avatar" style="background:var(--accent-soft)"><?= icon('megaphone') ?></div>
        <div class="flex-1">
          <b class="small"><?= e($a['title']) ?></b>
          <p class="tiny faint"><?= e($a['school_name']) ?> · <?= e(ucfirst($a['audience'])) ?> · <?= e(time_ago($a['created_at'])) ?></p>
          <p class="tiny" style="margin-top:2px"><?= e(mb_strimwidth((string)($a['content'] ?? ''), 0, 90, '…')) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><p class="muted small">No announcements yet.</p><?php endif; ?>
  </div>
</div>
