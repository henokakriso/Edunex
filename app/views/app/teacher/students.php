<?php /* Teacher students view */
$reExam = fn($s) => ($s['enrollment_status'] ?? 'active') === 'inactive';
?>
<style>
  .stu-row { transition: background .15s; }
  .stu-row:hover { background: var(--glass-nav-hover); backdrop-filter: blur(16px) saturate(140%); -webkit-backdrop-filter: blur(16px) saturate(140%); box-shadow: inset 0 1px 1px rgba(255,255,255,.15); }
  .parent-chip { display: inline-flex; align-items: center; gap: 8px; padding: 5px 10px; border: 1px solid var(--border); border-radius: 10px; background: var(--bg); }
  .parent-chip .avatar-sm { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .62rem; font-weight: 700; background: var(--accent-soft, rgba(29,105,201,.15)); color: var(--accent); }
  .course-badge { display: inline-flex; align-items: center; gap: 5px; background: var(--bg); border: 1px solid var(--border); padding: 3px 10px; border-radius: 99px; font-size: .74rem; }
  .progress-cell { min-width: 170px; }
</style>

<div class="page-head">
  <div>
    <h1><?= icon('users') ?> Students</h1>
    <p class="sub"><?= count($students) ?> student<?= count($students) === 1 ? '' : 's' ?> across your authorised courses · manage parent access</p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('teacher/import')) ?>"><?= icon('download') ?> Bulk import parents</a>
</div>

<div class="card" style="margin-bottom:18px;padding:16px 18px">
  <form method="get" class="flex gap-16" style="align-items:end;flex-wrap:wrap">
    <input type="hidden" name="r" value="teacher/students">
    <div class="flex-col" style="flex:1;max-width:300px;min-width:210px">
      <label class="small faint">Course filter</label>
      <select class="input" name="course" onchange="this.form.submit()" style="width:100%">
        <option value="">All courses</option>
        <?php foreach ($courses as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?> (<?= e($c['subject_name']) ?>)</option><?php endforeach; ?>
      </select>
    </div>
    <div class="flex-col" style="flex:1;max-width:360px;min-width:210px">
      <label class="small faint">Search</label>
      <input class="input" id="stu-search" placeholder="Name, email or student ID…" style="width:100%">
    </div>
  </form>
  <?php if (!$courses): ?>
    <p class="tiny faint" style="margin:14px 0 0;padding:9px 12px;background:var(--bg-hover);border-radius:8px;line-height:1.5"><?= icon('info') ?> No authorised courses yet — ask your director to assign subjects, then your students will show up here.</p>
  <?php endif; ?>
</div>

<div class="card" style="padding:0;overflow:hidden">
  <?php if (!$students): ?>
    <div class="flex-col" style="align-items:center;text-align:center;padding:64px 32px">
      <div style="width:56px;height:56px;border-radius:50%;background:var(--bg-hover);display:flex;align-items:center;justify-content:center;margin:0 0 16px;color:var(--text-faint)"><span style="width:26px;height:26px;display:inline-flex"><?= icon('users') ?></span></div>
      <b class="small" style="font-size:.9rem"><?= $courses ? 'No students in this course yet' : 'No authorised courses yet' ?></b>
      <p class="tiny faint" style="margin-top:6px;max-width:340px;line-height:1.5"><?= $courses ? 'Students will appear here once they enrol in the selected course.' : 'Ask your director to assign you subjects — then your students will show up here.' ?></p>
    </div>
  <?php else: ?>
  <div style="overflow-x:auto">
  <table class="table">
    <thead><tr><th>Student</th><th>Course</th><th>Progress</th><th>Parent</th><th style="width:90px"></th></tr></thead>
    <tbody>
      <?php foreach ($students as $s): ?>
        <tr class="stu-row" data-search="<?= e(strtolower($s['name'] . ' ' . $s['email'] . ' ' . ($s['student_id'] ?? ''))) ?>">
          <td>
            <div class="flex gap-10" style="align-items:center">
              <div class="avatar" style="width:34px;height:34px;font-size:.75rem"><?= e(mb_substr($s['name'], 0, 1)) ?></div>
                <div>
                  <b class="small"><?= e($s['name']) ?></b>
                  <?php if ($reExam($s)): ?><span class="badge badge-warning">re-exam</span><?php endif; ?>
                </div>
                <p class="tiny faint" style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?= e($s['email']) ?>"><?= e($s['email']) ?></p>
            </div>
          </td>
          <td>
            <span class="course-badge"><?= icon('book') ?> <?= e($s['course_title']) ?></span>
            <p class="tiny faint" style="margin-top:3px"><?= e($s['subject_name']) ?></p>
          </td>
          <td class="progress-cell">
            <div class="flex gap-8" style="align-items:center">
              <div class="progress" style="width:110px"><div style="width:<?= (float)$s['progress'] ?>%"></div></div>
              <span class="tiny faint"><?= (float)$s['progress'] ?>% (<?= (int)$s['done_lessons'] ?>/<?= (int)$s['total_lessons'] ?>)</span>
            </div>
            <?php if ((int)$s['completed']): ?><p class="tiny" style="color:var(--success);margin-top:3px"><?= icon('check-circle') ?> Course completed</p><?php endif; ?>
          </td>
          <td>
            <?php if ($s['parent_id']): ?>
              <div class="parent-chip" title="Linked parent">
                <span class="avatar-sm"><?= e(mb_substr($s['parent_name'] ?? '?', 0, 1)) ?></span>
                <div>
                  <b class="tiny"><?= e($s['parent_name']) ?></b>
                  <p class="tiny faint" style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?= e($s['parent_email']) ?>"><?= e($s['parent_email']) ?></p>
                </div>
                <form method="post" class="inline" data-confirm="Unlink <?= e($s['parent_name']) ?> from <?= e($s['name']) ?>?">
                  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
                  <button class="btn btn-sm btn-ghost" name="unlink_parent" value="1" title="Unlink parent"><?= icon('x') ?></button>
                </form>
              </div>
            <?php else: ?>
              <span class="parent-chip faint"><?= icon('user') ?> not linked</span>
            <?php endif; ?>
          </td>
          <td>
            <button class="btn btn-sm btn-ghost" onclick="openParentModal(this)"
                    data-sid="<?= (int)$s['id'] ?>"
                    data-name="<?= e($s['name']) ?>"
                    data-pname="<?= e($s['parent_name'] ?? '') ?>"
                    data-pemail="<?= e($s['parent_email'] ?? '') ?>"><?= icon('users') ?> Parent…</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>

<div class="modal-dialog" id="parent-modal">
  <div class="modal-box" style="max-width:460px">
    <div class="modal-head">
      <h3><?= icon('users') ?> Parent access</h3>
      <button class="modal-close" type="button" onclick="closeParentModal()">✕</button>
    </div>
    <div class="modal-body flex-col gap-14">
      <div class="flex gap-10" style="align-items:center">
        <div class="avatar" style="width:36px;height:36px">S</div>
        <div>
          <b class="small" id="pm-name"></b>
          <p class="tiny faint">Manage who can follow this student's learning</p>
        </div>
      </div>

      <div id="pm-current"></div>

      <div style="height:16px"></div>

      <form method="post" class="flex-col gap-8" id="pm-link-form">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="student_id" id="pm-sid" value="">
        <label class="small faint">Link an existing parent</label>
        <div class="flex gap-8">
          <select class="input" name="link_parent" id="pm-select" required>
            <option value="">— Choose parent —</option>
            <?php foreach ($parents as $p): ?>
              <option value="<?= (int)$p['id'] ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?> (<?= e($p['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-primary btn-sm"><?= icon('link') ?> Link</button>
        </div>
      </form>

      <div class="tiny faint" style="text-align:center">— or create a new parent account —</div>

      <form method="post" class="flex-col gap-8" id="pm-create-form">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="student_id" value="">
        <div class="grid2 gap-8">
          <input class="input" name="p_first_name" placeholder="First name" required>
          <input class="input" name="p_last_name" placeholder="Last name" required>
        </div>
        <input class="input" type="email" name="p_email" placeholder="Parent email" required>
        <div class="grid2 gap-8">
          <input class="input" name="p_phone" placeholder="Phone (optional)">
          <input class="input" name="p_password" placeholder="Password (blank = auto)">
        </div>
        <button class="btn btn-success btn-sm" name="create_parent" value="1"><?= icon('user-plus') ?> Create &amp; link parent</button>
      </form>
    </div>
    <div class="modal-foot">
      <button class="btn btn-ghost" type="button" onclick="closeParentModal()">Close</button>
    </div>
  </div>
</div>

<script>
(function () {
  const modal = document.getElementById('parent-modal');
  const stName = document.getElementById('pm-name');
  const stSid = document.getElementById('pm-sid');
  const current = document.getElementById('pm-current');

  window.openParentModal = function (btn) {
    stSid.value = btn.dataset.sid;
    stName.textContent = btn.dataset.name + ' — ' + (btn.dataset.sid ? '' : '');
    modal.querySelectorAll('#pm-create-form input[type=hidden][name=student_id]').forEach(h => h.value = btn.dataset.sid);
    modal.querySelector('.modal-body .avatar').textContent = (btn.dataset.name || 'S').charAt(0).toUpperCase();
    if (btn.dataset.pname) {
      current.innerHTML = '<div class="parent-chip" style="border-color:var(--success)"><?= icon('check-circle') ?> <span><b class="small">' + btn.dataset.pname + '</b><p class="tiny faint">' + btn.dataset.pemail + '</p></span></div>';
    } else {
      current.innerHTML = '<div class="alert alert-info" style="padding:10px 14px;margin:0"><?= icon('info') ?> No parent linked yet — use the options below.</div>';
    }
    modal.classList.add('open');
  };
  window.closeParentModal = function () {
    modal.classList.remove('open');
  };
  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeParentModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeParentModal();
  });

  const search = document.getElementById('stu-search');
  if (search) {
    search.addEventListener('input', function () {
      const q = search.value.trim().toLowerCase();
      document.querySelectorAll('.stu-row').forEach(row => {
        row.style.display = (!q || row.dataset.search.includes(q)) ? '' : 'none';
      });
    });
  }
})();
</script>
