<?php /* Course catalog + view + learn views */

/* ---------- Browse ---------- */
$role = $role ?? '';
?>
<div class="page-head">
  <div>
    <h1>Course catalog</h1>
    <p class="sub"><?= $role === 'principal' ? 'Courses published in your school' : 'Explore courses published across your schools.' ?></p>
  </div>
  <form class="search-box" style="max-width:280px" method="get">
    <input type="hidden" name="r" value="courses">
    <span><?= icon('search') ?></span>
    <input name="q" id="crs-search" placeholder="Search courses…" value="<?= e($_GET['q'] ?? '') ?>" oninput="document.getElementById('crs-clear').style.display=this.value?'flex':'none'">
    <button type="button" class="input-icon-btn" id="crs-clear" style="display:<?= ($_GET['q'] ?? '') ? 'flex' : 'none' ?>;position:absolute;right:6px" onclick="document.getElementById('crs-search').value='';this.style.display='none';this.form.submit()"><?= icon('x') ?></button>
  </form>
  <?php if ($role === 'principal'): ?>
    <button class="btn btn-primary" data-open-modal="new-course"><?= icon('plus') ?> New course</button>
  <?php endif; ?>
</div>
<?php
  $q = mb_strtolower($_GET['q'] ?? '');
  $catalog = array_values(array_filter($catalog, fn($c) => !$q || str_contains(mb_strtolower($c['title']), $q) || str_contains(mb_strtolower($c['description'] ?? ''), $q)));
?>

<?php if ($role === 'principal'): ?>
  <!-- Director: column/row table -->
  <?php if (!$catalog): ?><div class="empty"><span class="empty-ico"><?= icon('search') ?></span>No courses found</div><?php endif; ?>
  <div class="table-wrap dir-table" style="background:var(--bg-elev)">
    <table class="data">
      <thead>
        <tr><th>Course</th><th>Code</th><th>Teacher</th><th>Level</th><th>Lessons</th><th>Students</th><th>Status</th><th class="actions"></th></tr>
      </thead>
      <tbody>
        <?php foreach ($catalog as $c): ?>
          <tr>
            <td style="min-width:280px">
              <div class="c-course">
                <span class="c-thumb"><?= icon('graduation') ?></span>
                <div class="c-main">
                  <b><?= e($c['title']) ?></b>
                  <span class="tiny faint"><?= e(mb_substr($c['description'] ?? '', 0, 62)) ?><?= mb_strlen($c['description'] ?? '') > 62 ? '…' : '' ?></span>
                </div>
              </div>
            </td>
            <td><span class="badge badge-muted"><?= e($c['code'] ?: '—') ?></span></td>
            <td class="tiny"><?= e($c['tfirst']) ?> <?= e($c['tlast']) ?></td>
            <td class="tiny faint"><?= e($c['level'] ?: '—') ?></td>
            <td><b><?= (int)$c['total_lessons'] ?></b></td>
            <td><b><?= (int)$c['students'] ?></b></td>
            <td>
              <?php if ($c['status'] === 'published'): ?><span class="badge badge-success"><?= icon('check') ?> published</span>
              <?php else: ?><span class="badge badge-muted"><?= icon('clock') ?> draft</span><?php endif; ?>
            </td>
            <td class="actions">
              <div class="row-act">
                <a class="btn btn-sm" href="<?= e(url('index.php?r=courses/view&id=' . (int)$c['id'])) ?>" title="View"><?= icon('eye') ?></a>
                <button class="btn btn-sm" data-open-modal="edit-course" data-edit='<?= e(json_encode([
                    'id' => (int)$c['id'], 'title' => $c['title'], 'code' => $c['code'] ?? '',
                    'teacher' => (int)$c['teacher_id'], 'subject' => (int)($c['subject_id'] ?? 0),
                    'level' => $c['level'] ?? '', 'description' => $c['description'] ?? '',
                    'price' => (float)$c['price'],
                ])) ?>' title="Edit"><?= icon('edit') ?></button>
                <?php if ($c['status'] === 'published'): ?>
                  <form method="post" class="inline" data-confirm="Move '<?= e($c['title']) ?>' back to drafts? Students keep current progress.">
                    <?= csrf_field() ?><input type="hidden" name="unpublish_course" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-sm" title="Unpublish"><?= icon('folder') ?></button>
                  </form>
                <?php else: ?>
                  <form method="post" class="inline" data-confirm="Publish '<?= e($c['title']) ?>'?">
                    <?= csrf_field() ?><input type="hidden" name="publish_course" value="<?= (int)$c['id'] ?>">
                    <button class="btn btn-sm btn-success" title="Publish"><?= icon('send') ?></button>
                  </form>
                <?php endif; ?>
                <form method="post" class="inline" data-confirm="Delete '<?= e($c['title']) ?>'? Lessons, enrollments and progress are removed. This cannot be undone.">
                  <?= csrf_field() ?><input type="hidden" name="delete_course" value="<?= (int)$c['id'] ?>">
                  <button class="btn btn-sm btn-danger" title="Delete"><?= icon('trash') ?></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <style>
  .dir-table { margin-top: 4px; }
  .dir-table table.data td { padding: 13px 16px; }
  .dir-table table.data th { padding: 13px 16px; }
  .dir-table .c-course { display: flex; gap: 12px; align-items: center; min-width: 0; }
  .dir-table .c-thumb {
    width: 38px; height: 38px; border-radius: 10px; flex: none;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 22%, transparent), color-mix(in srgb, var(--info) 22%, transparent));
    color: var(--accent);
  }
  .dir-table .c-thumb .ico { width: 19px; height: 19px; }
  .dir-table .c-main { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
  .dir-table .c-main b { font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .dir-table .c-main span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .dir-table td b { font-size: 13.5px; }
  .dir-table .row-act { display: inline-flex; gap: 6px; align-items: center; }
  </style>

  <!-- ============ Create modal ============ -->
  <div class="modal-backdrop" id="new-course">
    <div class="modal">
      <div class="modal-head"><h3><?= icon('plus') ?> New course</h3><button type="button" class="modal-close" data-close-modal="new-course">✕</button></div>
      <form method="post">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="grid2">
            <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" required></div>
            <div class="flex-col"><label class="small faint">Code</label><input class="input" name="code" placeholder="e.g. MATH-101"></div>
            <div class="flex-col"><label class="small faint">Teacher *</label>
              <select class="input" name="teacher_id" required><option value="">— Choose teacher —</option>
                <?php foreach ($teachers as $t): ?><option value="<?= (int)$t['id'] ?>"><?= e($t['first_name'] . ' ' . $t['last_name']) ?></option><?php endforeach; ?>
              </select></div>
            <div class="flex-col"><label class="small faint">Subject</label>
              <select class="input" name="subject_id"><option value="0">— None —</option>
                <?php foreach ($subjects as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
              </select></div>
            <div class="flex-col"><label class="small faint">Level</label><input class="input" name="level" placeholder="e.g. Grade 9"></div>
            <div class="flex-col"><label class="small faint">Price (ETB)</label><input class="input" type="number" min="0" step="0.01" name="price" value="0"></div>
          </div>
          <div class="flex-col" style="margin-top:12px"><label class="small faint">Description</label>
            <textarea class="input" name="description" rows="3" placeholder="What will students learn?"></textarea></div>
          <p class="tiny faint" style="margin-top:8px">Courses start as drafts — the teacher adds modules and lessons, then you publish.</p>
        </div>
        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal="new-course">Cancel</button>
          <button class="btn btn-primary" name="create_course" value="1">Create course</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ============ Edit modal ============ -->
  <div class="modal-backdrop" id="edit-course">
    <div class="modal">
      <div class="modal-head"><h3><?= icon('edit') ?> Edit course</h3><button type="button" class="modal-close" data-close-modal="edit-course">✕</button></div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="edit_course" id="ec-id" value="0">
        <div class="modal-body">
          <div class="grid2">
            <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" id="ec-title" required></div>
            <div class="flex-col"><label class="small faint">Code</label><input class="input" name="code" id="ec-code"></div>
            <div class="flex-col"><label class="small faint">Teacher *</label>
              <select class="input" name="teacher_id" id="ec-teacher" required><option value="">— Choose teacher —</option>
                <?php foreach ($teachers as $t): ?><option value="<?= (int)$t['id'] ?>"><?= e($t['first_name'] . ' ' . $t['last_name']) ?></option><?php endforeach; ?>
              </select></div>
            <div class="flex-col"><label class="small faint">Subject</label>
              <select class="input" name="subject_id" id="ec-subject"><option value="0">— None —</option>
                <?php foreach ($subjects as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
              </select></div>
            <div class="flex-col"><label class="small faint">Level</label><input class="input" name="level" id="ec-level"></div>
            <div class="flex-col"><label class="small faint">Price (ETB)</label><input class="input" type="number" min="0" step="0.01" name="price" id="ec-price"></div>
          </div>
          <div class="flex-col" style="margin-top:12px"><label class="small faint">Description</label>
            <textarea class="input" name="description" id="ec-desc" rows="3"></textarea></div>
        </div>
        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal="edit-course">Cancel</button>
          <button class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  document.querySelectorAll('[data-open-modal="edit-course"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const d = JSON.parse(btn.dataset.edit);
      document.getElementById('ec-id').value = d.id;
      document.getElementById('ec-title').value = d.title;
      document.getElementById('ec-code').value = d.code;
      document.getElementById('ec-teacher').value = d.teacher;
      document.getElementById('ec-subject').value = d.subject;
      document.getElementById('ec-level').value = d.level;
      document.getElementById('ec-desc').value = d.description;
      document.getElementById('ec-price').value = d.price;
    });
  });
  </script>
<?php else: ?>
  <?php if (!$catalog): ?><div class="empty"><span class="empty-ico"><?= icon('search') ?></span>No courses found</div><?php endif; ?>
  <div class="grid-3">
    <?php foreach ($catalog as $c): ?>
      <div class="card card-hover" style="padding:0;overflow:hidden">
        <div class="cover-art"><?= icon('graduation') ?></div>
        <div style="padding:18px">
          <div class="flex-between" style="margin-bottom:8px">
            <span class="badge badge-accent"><?= e($c['code'] ?: 'Course') ?></span>
            <span class="tiny faint"><?= (int)$c['total_lessons'] ?> lessons</span>
          </div>
          <b style="font-size:15px"><?= e($c['title']) ?></b>
          <p class="muted tiny" style="margin:8px 0"><?= e(mb_substr($c['description'] ?? '', 0, 90)) ?>…</p>
          <div class="flex-between" style="margin-top:12px">
            <span class="tiny faint"><?= icon('user') ?>‍<?= icon('school') ?> <?= e($c['tfirst']) ?> <?= e($c['tlast']) ?> · <?= e($c['school_name']) ?></span>
            <a class="btn btn-sm btn-primary" href="<?= url('index.php?r=courses/view&id=' . $c['id']) ?>">View</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
