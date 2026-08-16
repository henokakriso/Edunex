<?php /* AI quiz view */
?>
<div class="page-head">
  <div>
    <h1><?= icon('note') ?> AI Quiz</h1>
    <p class="sub">Questions generated from the school question bank</p>
  </div>
</div>

<form method="get" class="flex gap-8" style="margin-bottom:18px;max-width:520px">
  <input class="input" style="flex:1" name="topic" placeholder="Topic: e.g. recursion, history, physics…" value="<?= e($topic) ?>" required>
  <button class="btn btn-primary">Generate quiz</button>
</form>

<?php if ($score !== null): ?>
  <div class="alert <?= $score['total'] > 0 && $score['correct'] / $score['total'] >= 0.6 ? 'alert-success' : 'alert-danger' ?>" style="margin-bottom:18px">
    <b>Quiz result: <?= (int)$score['correct'] ?> / <?= (int)$score['total'] ?></b>
    <?= $score['total'] > 0 && $score['correct'] / $score['total'] >= 0.6 ? icon('spark') . ' Great work!' : icon('bolt') . ' Keep practicing!' ?>
  </div>
<?php endif; ?>

<?php if ($questions): ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="flex-col gap-10">
      <?php foreach ($questions as $i => $q): ?>
        <div class="card">
          <h3 class="small" style="margin-top:0">Q<?= $i + 1 ?>. <?= e($q['question']) ?></h3>
          <?php if ($q['options']): $opts = json_decode($q['options'], true) ?: []; ?>
            <div class="flex-col gap-6">
              <?php foreach ($opts as $oi => $opt): ?>
                <label class="option-item"><input type="radio" name="q[<?= (int)$q['id'] ?>]" value="<?= e((string)$opt) ?>"> <?= e((string)$opt) ?></label>
              <?php endforeach; ?>
            </div>
          <?php elseif ($q['type'] === 'truefalse'): ?>
            <div class="flex gap-8">
              <label class="option-item"><input type="radio" name="q[<?= (int)$q['id'] ?>]" value="true"> True</label>
              <label class="option-item"><input type="radio" name="q[<?= (int)$q['id'] ?>]" value="false"> False</label>
            </div>
          <?php else: ?>
            <input class="input" name="q[<?= (int)$q['id'] ?>]" placeholder="Your answer…">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="btn btn-primary" style="margin-top:16px"><?= icon('check-circle') ?> Submit quiz</button>
  </form>
<?php elseif ($topic !== ''): ?>
  <div class="alert alert-info">No questions found for “<?= e($topic) ?>” — try a broader topic.</div>
<?php endif; ?>
