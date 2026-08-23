<?php /* University programs management */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('book') ?> Programs</h1>
    <p class="sub">Degree programs offered by this university</p>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 class="card-title"><?= icon('plus') ?> New Program</h3>
  <form method="post" action="<?= e(url('university/programs')) ?>" class="flex-col gap-6" style="margin-top:6px">
    <?= csrf_field() ?>
    <div class="grid2">
      <input class="input" name="name" required placeholder="BSc Computer Science" maxlength="200">
      <input class="input" name="code" required placeholder="BSC-CS" maxlength="20">
    </div>
    <div class="grid3">
      <select class="input" name="faculty_id">
        <option value="">— Faculty —</option>
        <?php foreach ($faculties as $f): ?>
          <option value="<?= (int)$f['id'] ?>"><?= e($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select class="input" name="department_id">
        <option value="">— Department —</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select class="input" name="degree_type">
        <option value="bachelor">Bachelor</option>
        <option value="master">Master</option>
        <option value="phd">PhD</option>
        <option value="diploma">Diploma</option>
        <option value="certificate">Certificate</option>
      </select>
    </div>
    <div class="grid2">
      <input class="input" type="number" name="total_credits" value="120" min="1" placeholder="Total credits">
      <input class="input" type="number" name="duration_years" value="4" min="1" placeholder="Duration (years)">
    </div>
    <button class="btn btn-success" name="create_program" value="1"><?= icon('save') ?> Create Program</button>
  </form>
</div>

<div class="card pad-0">
  <table class="table">
    <thead>
      <tr><th>Code</th><th>Program</th><th>Degree</th><th>Faculty</th><th>Department</th><th>Credits</th><th>Years</th><th>Students</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($programs as $p): ?>
        <tr>
          <td><span class="badge"><?= e($p['code']) ?></span></td>
          <td><a href="<?= e(url('university/program&id=' . $p['id'])) ?>" style="font-weight:600"><?= e($p['name']) ?></a></td>
          <td><?= e(ucfirst($p['degree_type'])) ?></td>
          <td><?= e($p['faculty_name'] ?? '—') ?></td>
          <td><?= e($p['dept_name'] ?? '—') ?></td>
          <td><?= (int)$p['total_credits'] ?></td>
          <td><?= (int)$p['duration_years'] ?></td>
          <td><?= (int)$p['students'] ?></td>
          <td>
            <form method="post" action="<?= e(url('university/programs')) ?>" style="display:inline">
              <?= csrf_field() ?>
              <button class="btn btn-xs btn-ghost" name="delete_program" value="<?= (int)$p['id'] ?>" onclick="return confirm('Archive this program?')"><?= icon('trash') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$programs): ?><tr><td colspan="9" class="tiny faint">No programs yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
