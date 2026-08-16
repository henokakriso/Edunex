<?php /* AI history */
?>
<div class="page-head">
  <div>
    <h1><?= icon('folder') ?> AI History</h1>
    <p class="sub">All your conversations with the AI tutor</p>
  </div>
</div>

<div class="flex-col gap-10">
  <?php foreach ($chats as $c): ?>
    <div class="card list-row" style="padding:12px 16px">
      <div class="flex-1">
        <a class="small" href="<?= e(url('ai/tutor&chat=' . $c['id'])) ?>"><?= e($c['title']) ?></a>
        <p class="tiny faint" style="margin-top:4px">
          <?= (int)$c['msg_count'] ?> messages · last <?= $c['last_msg'] ? e(date('M j, H:i', strtotime($c['last_msg']['created_at']))) : 'never' ?>
          <?php if ($c['last_msg']): ?>· <i><?= e(mb_strimwidth((string)$c['last_msg']['content'], 0, 70, '…')) ?></i><?php endif; ?>
        </p>
      </div>
      <a class="btn btn-sm btn-ghost" href="<?= e(url('ai/tutor&chat=' . $c['id'])) ?>">Open →</a>
      <form method="post" class="inline" data-confirm="Delete this chat and all its messages?">
        <?= csrf_field() ?><input type="hidden" name="delete_chat" value="<?= (int)$c['id'] ?>">
        <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (!$chats): ?><div class="alert alert-info">No chats yet — try the <a class="accent" href="<?= e(url('ai/tutor')) ?>">AI Tutor</a>!</div><?php endif; ?>
</div>
