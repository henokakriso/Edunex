<?php /* Teacher courses list view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('books') ?> My Courses</h1>
    <p class="sub"><?= count($courses) ?> course<?= count($courses) === 1 ? '' : 's' ?></p>
  </div>
  <div class="flex gap-8">
    <a class="btn btn-ghost" href="<?= e(url('teacher/book')) ?>"><?= icon('books') ?> From PDF book</a>
    <button class="btn btn-primary" onclick="document.getElementById('new-course').style.display='block';this.style.display='none'">+ New course</button>
  </div>
</div>

<form method="post" class="card" id="new-course" style="display:none;margin-bottom:22px">
  <?= csrf_field() ?>
  <h3 class="card-title"><?= icon('plus') ?> Create course</h3>
  <?php if (!$subjects): ?>
    <div class="alert alert-warning"><?= icon('lock') ?> No subjects are assigned to you yet. Ask your director to authorise the subjects you teach before creating courses.</div>
    <button class="btn btn-success" disabled name="create_course" value="1"><?= icon('rocket') ?> Create course</button>
  <?php else: ?>
  <div class="grid2">
    <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" required placeholder="Mathematics for Grade 9"></div>
    <div class="flex-col"><label class="small faint">Course code</label><input class="input" name="code" placeholder="MATH-9"></div>
    <div class="flex-col"><label class="small faint">School *</label>
      <select class="input" name="school_id" required><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Subject * (only your authorised subjects)</label>
      <select class="input" name="subject_id" required><option value="">— Choose subject —</option><?php foreach ($subjects as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
    </div>
    <div class="flex-col"><label class="small faint">Grade level</label><input class="input" name="level" placeholder="Grade 9"></div>
    <div class="flex-col"><label class="small faint">Credit hours</label><input class="input" type="number" name="credit_hours" value="3" min="0" max="20" step="0.5"></div>
    <div class="flex-col"><label class="small faint">Price (ETB, 0 = free)</label><input class="input" type="number" name="price" value="0" min="0"></div>
    <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Description</label><textarea class="input" name="description" rows="3"></textarea></div>
  </div>
  <button class="btn btn-success" name="create_course" value="1"><?= icon('rocket') ?> Create course</button>
  <?php endif; ?>
</form>

<div class="card" style="margin-bottom:18px;padding:12px 16px">
  <div class="flex gap-8" style="align-items:center">
    <span><?= icon('search') ?></span>
    <input id="course-filter" class="input" style="flex:1" placeholder="Filter your courses by title, code or level…">
    <select id="status-filter" class="select" style="width:auto">
      <option value="">All statuses</option>
      <option value="published">Published</option>
      <option value="draft">Draft</option>
      <option value="archived">Archived</option>
    </select>
  </div>
</div>

<div class="flex-col gap-16">
  <?php foreach ($courses as $c): ?>
    <div class="card course-card" data-title="<?= e(mb_strtolower($c['title'])) ?>" data-code="<?= e(mb_strtolower($c['code'] ?? '')) ?>" data-level="<?= e(mb_strtolower($c['level'] ?? '')) ?>" data-status="<?= e($c['status']) ?>">
      <div class="flex-between" style="flex-wrap:wrap;gap:12px">
        <div class="flex gap-12" style="align-items:center">
          <div class="thumb"><?= e(mb_strtoupper(mb_substr($c['title'], 0, 1))) ?></div>
          <div>
            <b><?= e($c['title']) ?></b>
            <?php if ($c['code']): ?><span class="badge"><?= e($c['code']) ?></span><?php endif; ?>
            <span class="badge <?= $c['status'] === 'published' ? 'badge-success' : ($c['status'] === 'archived' ? 'badge-muted' : 'badge-warning') ?>"><?= $c['status'] ?></span>
            <p class="tiny faint" style="margin-top:4px"><?= (int)$c['lessons'] ?> lessons · <?= (int)$c['students'] ?> students<?= $c['avg_progress'] !== null ? ' · avg progress ' . (int)$c['avg_progress'] . '%' : '' ?> · <?= e($c['school_name']) ?></p>
            <?php if ($c['avg_progress'] !== null): ?>
              <div class="progress" style="width:160px;margin-top:6px"><div style="width:<?= (int)$c['avg_progress'] ?>%"></div></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="flex gap-8">
          <a class="btn btn-sm" href="<?= e(url('teacher/course&id=' . $c['id'])) ?>"><?= icon('gear') ?> Manage</a>
          <a class="btn btn-sm btn-ghost" href="<?= e(url('courses/view&id=' . $c['id'])) ?>"><?= icon('eye') ?> View</a>
          <form method="post" class="inline" data-confirm="Delete this course and all its content?">
            <?= csrf_field() ?><input type="hidden" name="delete_course" value="<?= (int)$c['id'] ?>">
            <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$courses): ?><div class="alert alert-info">No courses yet — create your first course above.</div><?php endif; ?>
</div>

<script>
(function () {
  const filter = document.getElementById('course-filter');
  const status = document.getElementById('status-filter');
  if (!filter) return;
  const apply = () => {
    const q = filter.value.trim().toLowerCase();
    const st = status.value;
    document.querySelectorAll('.course-card').forEach(card => {
      const text = (card.dataset.title + ' ' + card.dataset.code + ' ' + card.dataset.level).toLowerCase();
      const ok = (!q || text.includes(q)) && (!st || card.dataset.status === st);
      card.style.display = ok ? '' : 'none';
    });
  };
  filter.addEventListener('input', apply);
  status.addEventListener('change', apply);
})();
</script>
