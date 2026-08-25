<div class="flex-between" style="margin-bottom:1.5rem">
  <h1 style="margin:0"><?= e($title) ?></h1>
</div>
<div class="grid" style="grid-template-columns:1fr 1fr;gap:1.5rem">
  <div class="card">
    <div class="card-head"><h2>Post Announcement</h2></div>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group"><label>Title</label><input type="text" name="title" required class="form-control"></div>
      <div class="form-group"><label>Body</label><textarea name="body" required class="form-control" rows="4"></textarea></div>
      <div class="form-group"><label>Target</label>
        <select name="target" class="form-control">
          <option value="all">All Users</option>
          <option value="student">Students Only</option>
          <option value="teacher">Teachers Only</option>
          <option value="director">Directors Only</option>
        </select>
      </div>
      <button class="btn btn-primary" type="submit">Post</button>
    </form>
  </div>
  <div class="card">
    <div class="card-head"><h2>Recent Announcements</h2></div>
    <?php if (empty($announcements)): ?>
      <p style="color:var(--muted);padding:1rem">No announcements yet.</p>
    <?php else: foreach ($announcements as $a): ?>
      <div style="padding:1rem;border-bottom:1px solid var(--border)">
        <strong><?= e($a['title']) ?></strong>
        <p style="margin:0.25rem 0;color:var(--muted);font-size:0.85rem"><?= e(mb_substr($a['body'], 0, 120)) ?>…</p>
        <small style="color:var(--muted)">by <?= e($a['posted_by_name'] ?? 'System') ?> · <?= e($a['created_at']) ?></small>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>
