<?php /* Bonus grading page */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('spark') ?> Bonus Grading</h1>
    <p class="sub"><?= e($course['title']) ?></p>
  </div>
  <a class="btn btn-ghost" href="<?= e(url('teacher/grading&course=' . $course['id'])) ?>">← Back</a>
</div>

<!-- Add bonus form -->
<div class="card" style="margin-bottom:18px">
  <h4 class="card-title" style="margin-top:0">Add Bonus</h4>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="add_bonus" value="1">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
      <div class="flex-col" style="flex:2;min-width:200px">
        <label class="small faint">Student *</label>
        <select class="input" name="student_id" required>
          <option value="">— Select Student —</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= e($s['last_name'] . ', ' . $s['first_name']) ?> (<?= e($s['sid'] ?? '') ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col" style="flex:2;min-width:200px">
        <label class="small faint">Title *</label>
        <input class="input" name="title" required placeholder="e.g. Excellent Project">
      </div>
      <div class="flex-col" style="flex:1;min-width:140px">
        <label class="small faint">Points *</label>
        <input class="input" type="number" name="points" min="0.1" max="10" step="0.1" required placeholder="+2">
      </div>
      <div class="flex-col" style="flex:2;min-width:200px">
        <label class="small faint">Reason</label>
        <input class="input" name="reason" placeholder="Optional reason">
      </div>
      <button class="btn btn-primary" type="submit"><?= icon('plus') ?> Add</button>
    </div>
  </form>
</div>

<!-- Bonuses list -->
<div class="card">
  <h4 class="card-title" style="margin-top:0">Bonus History</h4>
  <?php if ($bonuses): ?>
    <table class="table">
      <thead>
        <tr>
          <th>Student</th>
          <th>Title</th>
          <th>Points</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bonuses as $b): ?>
          <tr>
            <td class="small" style="font-weight:600">
              <?php
                $sname = '—';
                foreach ($students as $s) { if ((int)$s['id'] === (int)$b['student_id']) { $sname = e($s['last_name'] . ', ' . $s['first_name']); break; } }
                echo $sname;
              ?>
            </td>
            <td class="small"><?= e($b['title']) ?></td>
            <td><span class="badge badge-success">+<?= e($b['points']) ?></span></td>
            <td class="small faint"><?= e($b['reason'] ?: '—') ?></td>
            <td>
              <span class="badge <?= $b['status'] === 'approved' ? 'badge-success' : ($b['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>"><?= e(ucfirst($b['status'])) ?></span>
            </td>
            <td class="tiny faint"><?= date('M j, Y', strtotime($b['created_at'])) ?></td>
            <td>
              <?php if ($b['status'] === 'pending'): ?>
                <form method="post" class="inline" data-confirm="Delete this bonus?">
                  <?= csrf_field() ?><input type="hidden" name="delete_bonus" value="<?= (int)$b['id'] ?>">
                  <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="muted small" style="padding:12px">No bonus entries yet.</p>
  <?php endif; ?>
</div>
