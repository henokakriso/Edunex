<?php /* Program detail — enrolled students */ ?>
<div class="page-head">
  <div>
    <h1><?= e($prog['name']) ?></h1>
    <p class="sub"><?= e($prog['code']) ?> · <?= e(ucfirst($prog['degree_type'])) ?> · <?= (int)$prog['total_credits'] ?> credits · <?= (int)$prog['duration_years'] ?> years</p>
  </div>
  <a href="<?= e(url('university/programs')) ?>" class="btn btn-ghost"><?= icon('arrow-left') ?> Programs</a>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title"><?= icon('user-plus') ?> Enroll Student</h3>
  <form method="post" action="<?= e(url('university/program&id=' . $prog['id'])) ?>" class="flex-row gap-6" style="margin-top:6px">
    <?= csrf_field() ?>
    <select class="input" name="student_id" required style="flex:1">
      <option value="">— Select student —</option>
      <?php foreach ($allStudents as $s): ?>
        <option value="<?= (int)$s['id'] ?>"><?= e($s['student_id']) ?> — <?= e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-success" name="enroll_student" value="1"><?= icon('plus') ?> Enroll</button>
  </form>
</div>

<h3 style="margin:18px 0 8px"><?= icon('users') ?> Enrolled Students (<?= count($students) ?>)</h3>
<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Student ID</th><th>Name</th><th>Status</th><th>Enrolled</th><th>Expected Graduation</th></tr>
    </thead>
    <tbody>
      <?php foreach ($students as $s): ?>
        <tr>
          <td><span class="badge"><?= e($s['sid_no'] ?? '—') ?></span></td>
          <td><?= e($s['name']) ?></td>
          <td><span class="badge badge-<?= $s['status'] === 'active' ? 'success' : 'ghost' ?>"><?= e($s['status']) ?></span></td>
          <td class="tiny faint"><?= e($s['enrolled_at']) ?></td>
          <td class="tiny faint"><?= $s['expected_graduation'] ? e($s['expected_graduation']) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$students): ?><tr><td colspan="5" class="tiny faint">No students enrolled.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
