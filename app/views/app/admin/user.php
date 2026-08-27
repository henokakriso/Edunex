<?php /* Admin user detail view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('user') ?> <?= e($target['first_name'] . ' ' . $target['last_name']) ?></h1>
    <p class="sub"><?= e($target['email']) ?> · <?= e($target['school_name']) ?> · <span class="badge"><?= e($target['role']) ?></span> <span class="badge <?= $target['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= e($target['status']) ?></span></p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('admin/users')) ?>">← Back</a>
</div>

<div class="grid" style="grid-template-columns:1.2fr 1fr;gap:22px;align-items:start">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('gear') ?> Edit user</h3>
    <form method="post">
      <?= csrf_field() ?>
      <div class="grid2">
        <div class="flex-col"><label class="small faint">First name</label><input class="input" name="first_name" value="<?= e($target['first_name']) ?>"></div>
        <div class="flex-col"><label class="small faint">Last name</label><input class="input" name="last_name" value="<?= e($target['last_name']) ?>"></div>
        <div class="flex-col"><label class="small faint">Email</label><input class="input" type="email" name="email" value="<?= e($target['email']) ?>"></div>
        <div class="flex-col"><label class="small faint">Phone</label><input class="input" name="phone" value="<?= e($target['phone']) ?>"></div>
        <?php if ($target['role'] === 'student'): ?>
          <div class="flex-col"><label class="small faint">Student ID (special transferable ID)</label><input class="input" name="student_id" value="<?= e($target['student_id'] ?? '') ?>"></div>
        <?php endif; ?>
        <div class="flex-col"><label class="small faint">Role</label>
          <select class="input" name="role">
            <?php foreach (['student', 'teacher', 'parent', 'principal', 'registrar', 'dean', 'regional', 'ministry'] as $r): ?><option value="<?= $r ?>" <?= $target['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Status</label>
          <select class="input" name="status">
            <?php foreach (['active', 'pending', 'suspended', 'banned'] as $st): ?><option value="<?= $st ?>" <?= $target['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">New password (blank = keep)</label><input class="input" type="password" name="password" autocomplete="new-password"></div>
      </div>
      <button class="btn btn-primary" name="update_user" value="1"><?= icon('save') ?> Save</button>
    </form>

    <?php if ($target['role'] === 'student'): ?>
      <h3 class="card-title" style="margin-top:22px"><?= icon('user') ?>‍<?= icon('user') ?>‍<?= icon('user') ?> Linked parent</h3>
      <?php if ($parent): ?><p class="small"><?= icon('user') ?> <?= e($parent['name']) ?> <a class="accent" href="<?= e(url('admin/user&id=' . $parent['id'])) ?>">(view)</a></p><?php else: ?><p class="muted small">No parent linked.</p><?php endif; ?>
    <?php elseif ($target['role'] === 'parent'): ?>
      <h3 class="card-title" style="margin-top:22px"><?= icon('user') ?> Children</h3>
      <?php foreach ($children as $ch): ?>
        <p class="small"><?= icon('user') ?>‍<?= icon('graduation') ?> <?= e($ch['name']) ?> <span class="mono faint"><?= e($ch['student_id']) ?></span> <a class="accent" href="<?= e(url('admin/user&id=' . $ch['id'])) ?>">(view)</a></p>
      <?php endforeach; ?>
      <?php if (!$children): ?><p class="muted small">No children linked. Use "Link parent" on a student's page.</p><?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="flex-col gap-16">
    <?php if ($target['role'] === 'student'): ?>
      <div class="card">
        <h3 class="card-title" style="margin-top:0"><?= icon('chart-bar') ?> Performance</h3>
        <?php if ($gpa): ?>
          <div class="flex gap-16" style="flex-wrap:wrap">
            <?php foreach ($gpa as $avg): ?>
              <div class="stat-box" style="flex:1;min-width:130px">
                <span class="tiny faint"><?= e(mb_strimwidth($avg['title'], 0, 22, '…')) ?></span>
                <b><?= round((float)$avg['avg_percent']) ?>%</b>
                <span class="tiny faint"><?= (int)$avg['attempts'] ?> exams</span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?><p class="muted small">No graded exams yet.</p><?php endif; ?>
        <?php if ($grades): ?>
          <div class="table-wrap" style="margin-top:10px">
            <table class="table"><thead><tr><th class="col-num">#</th><th>Exam</th><th>Course</th><th>Score</th><th>When</th></tr></thead><tbody>
              <?php $i = 0; foreach (array_slice($grades, 0, 10) as $g): ?>
                <tr>
                  <td class="col-num"><?= $i + 1 ?></td>
                  <td class="small"><?= e($g['exam_title']) ?></td>
                  <td class="small faint"><?= e(mb_strimwidth($g['course_title'], 0, 20, '…')) ?></td>
                  <td class="small">
                    <?= number_format((float)$g['score'], 1) ?> / <?= number_format((float)$g['total_points'], 1) ?>
                    <span class="tiny <?= ((float)$g['total_points'] > 0 && (float)$g['score'] / (float)$g['total_points'] >= 0.5) ? 'success' : 'danger' ?>">(<?= (int)round((float)$g['total_points'] > 0 ? (float)$g['score'] / (float)$g['total_points'] * 100 : 0) ?>%)</span>
                  </td>
                  <td class="tiny faint"><?= e(date('M j, Y', strtotime($g['submitted_at']))) ?></td>
                </tr>
              <?php $i++; endforeach; ?>
            </tbody></table>
          </div>
        <?php endif; ?>
      </div>
      <?php if ($badges): ?>
        <div class="card">
          <h3 class="card-title" style="margin-top:0"><?= icon('medal') ?> Badges</h3>
          <div class="flex gap-10" style="flex-wrap:wrap">
            <?php foreach (array_slice($badges, 0, 10) as $b): ?>
              <span class="badge-ico" title="<?= e($b['description']) ?> · earned <?= e(date('M j, Y', strtotime($b['earned_at']))) ?>"><?= icon($b['icon']) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('books') ?> Enrollments</h3>
      <?php foreach ($enrollments as $en): ?>
        <div class="list-row" style="padding:8px 0">
          <div class="flex-1 small"><b><?= e($en['title']) ?></b></div>
          <div class="progress" style="width:80px"><div style="width:<?= (float)$en['progress'] ?>%"></div></div>
          <span class="tiny faint"><?= (float)$en['progress'] ?>%</span>
        </div>
      <?php endforeach; ?>
      <?php if (!$enrollments): ?><p class="muted small">No enrollments.</p><?php endif; ?>
    </div>
    <?php if ($attendance): ?>
      <div class="card">
        <h3 class="card-title" style="margin-top:0"><?= icon('doc') ?> Recent attendance</h3>
        <?php foreach (array_slice($attendance, 0, 8) as $at): ?>
          <div class="list-row" style="padding:6px 0">
            <span class="small flex-1"><?= e($at['course_title']) ?></span>
            <span class="tiny faint"><?= e(date('M j', strtotime($at['date']))) ?></span>
            <span class="badge <?= $at['status'] === 'present' ? 'badge-success' : ($at['status'] === 'absent' ? 'badge-danger' : 'badge-warning') ?>"><?= e($at['status']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="card">
      <h3 class="card-title" style="margin-top:0"><?= icon('key') ?> Recent logins</h3>
      <?php foreach ($logins as $lg): ?>
        <div class="list-row" style="padding:6px 0">
          <span class="small flex-1"><?= e($lg['ip'] ?? '—') ?> <?= ($lg['status'] ?? 'success') !== 'failed' ? '' : ' <b class="danger">(failed)</b>' ?></span>
          <span class="tiny faint"><?= e(time_ago($lg['created_at'])) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$logins): ?><p class="muted small">No login history.</p><?php endif; ?>
    </div>
  </div>
</div>
