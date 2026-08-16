<?php /* Dept head — thesis review */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('book') ?> Theses</h1>
    <p class="sub">Student thesis submissions for <?= e($dept['name']) ?></p>
  </div>
</div>

<div class="card pad-0">
  <table class="table">
    <thead><tr><th>Student</th><th>Title</th><th>Advisor</th><th>Submitted</th><th>Status</th><th style="width:280px">Review</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><b><?= e($r['student']) ?></b><div class="tiny faint"><?= e($r['student_id']) ?></div></td>
          <td>
            <b><?= e($r['title']) ?></b>
            <?php if ($r['abstract']): ?><p class="tiny faint" style="margin:2px 0 0"><?= e(mb_substr($r['abstract'], 0, 120)) ?>…</p><?php endif; ?>
            <?php if ($r['feedback']): ?><p class="tiny" style="margin:2px 0 0">Feedback: <?= e($r['feedback']) ?></p><?php endif; ?>
          </td>
          <td class="small"><?= e($r['advisor'] ?: '—') ?></td>
          <td class="tiny"><?= e(date('M j, Y', strtotime($r['submitted_at']))) ?></td>
          <td><span class="badge <?= $r['status'] === 'approved' ? 'badge-success' : ($r['status'] === 'rejected' ? 'badge-danger' : '') ?>"><?= e($r['status']) ?></span></td>
          <td>
            <?php if ($r['status'] === 'submitted'): ?>
              <form method="post" class="flex-col gap-6">
                <?= csrf_field() ?>
                <div class="flex gap-6">
                  <select class="input" name="status" style="min-width:110px">
                    <option value="approved">Approve</option>
                    <option value="rejected">Reject</option>
                  </select>
                  <button class="btn btn-xs btn-success" name="decide_thesis" value="<?= (int)$r['id'] ?>"><?= icon('check') ?> Go</button>
                </div>
                <input class="input" name="feedback" placeholder="Feedback (optional)" maxlength="255">
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="muted">No theses yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
