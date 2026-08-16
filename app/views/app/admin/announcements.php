<?php /* Admin announcements view */
$audCls = ['all' => 'badge-accent', 'students' => 'badge-success', 'teachers' => 'badge-warning', 'parents' => 'badge-muted', 'course' => 'badge-danger'];
?>
<div class="page-head">
  <div>
    <h1><?= icon('megaphone') ?> Announcements</h1>
    <p class="sub">Broadcast to everyone or a group</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('new-ann').style.display='block';this.style.display='none'">+ New announcement</button>
</div>

<form method="post" class="card" id="new-ann" style="display:none;margin-bottom:18px">
  <?= csrf_field() ?>
  <h3 class="card-title"><?= icon('megaphone') ?> Post announcement</h3>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" required placeholder="School closed on Friday"></div>
    <div class="flex-col"><label class="small faint">Audience</label>
      <select class="input" name="audience" onchange="document.getElementById('course-pick').style.display=this.value==='course'?'':'none'">
        <option value="all">Everyone</option><option value="students">Students</option>
        <option value="teachers">Teachers</option><option value="parents">Parents</option><option value="course">Course members</option>
      </select>
    </div>
    <div class="flex-col" id="course-pick" style="display:none"><label class="small faint">Course</label>
      <select class="input" name="course_id"><option value="0">— Select —</option><?php foreach ($courses as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">School</label>
      <select class="input" name="school_id"><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Content *</label><textarea class="input" name="content" rows="4" required></textarea></div>
    <div class="flex-col"><label class="small faint">Pin to top</label><input type="checkbox" name="pinned" value="1"></div>
  </div>
  <button class="btn btn-success" name="create_ann" value="1"><?= icon('megaphone') ?> Post</button>
</form>

<div class="flex-col gap-16">
  <?php foreach ($anns as $a): ?>
    <div class="card" style="<?= $a['pinned'] ? 'border-left:4px solid var(--accent)' : '' ?>">
      <div class="flex-between" style="flex-wrap:wrap;gap:10px">
        <div>
          <b><?= e($a['title']) ?></b>
          <?php if ($a['pinned']): ?><span class="badge badge-accent"><?= icon('pin') ?> Pinned</span><?php endif; ?>
          <span class="badge <?= $audCls[$a['audience']] ?? 'badge-muted' ?>"><?= e($a['audience']) ?></span>
          <p class="small" style="margin-top:6px"><?= e($a['content']) ?></p>
          <p class="tiny faint" style="margin-top:6px">by <?= e($a['author_name']) ?> · <?= e($a['school_name']) ?><?= $a['course_title'] ? ' · ' . e($a['course_title']) : '' ?> · <?= e(time_ago($a['created_at'])) ?></p>
        </div>
        <form method="post" class="inline" data-confirm="Delete this announcement?">
          <?= csrf_field() ?><input type="hidden" name="delete_ann" value="<?= (int)$a['id'] ?>">
          <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$anns): ?><div class="alert alert-info">No announcements yet.</div><?php endif; ?>
</div>
