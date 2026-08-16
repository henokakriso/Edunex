<?php /* Flashcards view (Leitner) — picture mode + flip cards */
$current = null;
foreach ($decks as $d) if ((int)$d['id'] === (int)$deckId) $current = $d;
$picFiles = [];
foreach (glob(FLASH_CARDS_PATH . '/*.{webp,avif,jpg,jpeg,png,gif}', GLOB_BRACE) as $f) $picFiles[] = basename($f);
sort($picFiles);
?>
<div class="page-head">
  <div>
    <h1><?= icon('game') ?> Flashcards</h1>
    <p class="sub">Spaced repetition (Leitner boxes) — picture cards stamp the question on the image (C backend)</p>
  </div>
  <form method="post" class="inline flex gap-8">
    <?= csrf_field() ?>
    <input class="input" style="width:170px" name="new_deck" placeholder="New deck name" required>
    <button class="btn btn-primary">+ Deck</button>
  </form>
</div>

<div class="grid" style="grid-template-columns:220px 1fr;gap:18px;align-items:start">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('books') ?> Decks</h3>
    <?php foreach ($decks as $d): ?>
      <a class="list-row" href="<?= e(url('ai/flashcards&deck=' . $d['id'])) ?>" style="padding:8px 0;border-bottom:1px solid var(--border);color:inherit;text-decoration:none">
        <span class="flex-1 small"><?= e($d['title']) ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (!$decks): ?><p class="muted small">No decks yet. Create one!</p><?php endif; ?>
  </div>

  <div>
    <?php if ($current): ?>
      <div class="card" style="margin-bottom:16px">
        <h3 class="card-title" style="margin-top:0"><?= icon('users-card') ?> <?= e($current['title']) ?> — <?= count($cards) ?> cards</h3>
        <div class="flex gap-8" style="flex-wrap:wrap">
          <form method="post" class="inline flex gap-8"><?= csrf_field() ?>
            <input type="hidden" name="deck_id" value="<?= (int)$deckId ?>">
            <input class="input" name="topic" placeholder="Generate cards from topic" style="width:240px">
            <button class="btn btn-ghost" name="gen_cards" value="1"><?= icon('spark') ?> Auto-generate</button>
          </form>
          <form method="post" class="inline" data-confirm="Delete this deck?"><?= csrf_field() ?>
            <button class="btn btn-sm btn-danger" name="delete_deck" value="<?= (int)$deckId ?>"><?= icon('trash') ?> Deck</button>
          </form>
        </div>
      </div>

      <div class="flex gap-8" style="margin-bottom:14px;align-items:center">
        <button class="btn btn-ghost btn-sm" type="button" onclick="fcPicMode(this)"><?= icon('image') ?> Picture mode</button>
        <span class="tiny faint">Picture cards stamp the question onto the image — flip to reveal the answer stamped on the back.</span>
      </div>

      <div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px">
        <?php foreach ($cards as $i => $card): ?>
          <?php if ($card['image']): $imgFront = url('ai/flashcard-image&card=' . (int)$card['id'] . '&side=front'); $imgBack = url('ai/flashcard-image&card=' . (int)$card['id'] . '&side=back'); ?>
          <div class="card fl-card has-img" onclick="this.classList.toggle('flipped')" style="cursor:pointer;min-height:150px">
            <div class="fl-inner">
              <div class="fl-face fl-front" style="background-image:url(<?= e($imgFront) ?>)">
                <span class="tiny faint fl-badge">Box <?= (int)$card['box'] ?> · <?= $i + 1 ?></span>
                <p class="tiny faint" style="margin-top:8px;pointer-events:none">(click to flip)</p>
              </div>
              <div class="fl-face fl-back" style="background-image:url(<?= e($imgBack) ?>)">
                <span class="tiny faint fl-badge">Answer</span>
              </div>
            </div>
          </div>
          <div class="flex gap-8" style="justify-content:center">
            <button class="btn btn-sm btn-ghost" type="button" onclick="fcEditCard(<?= (int)$card['id'] ?>, this)"><?= icon('edit') ?> Edit</button>
            <a class="btn btn-sm btn-ghost" href="<?= e($imgFront . '&dl=1') ?>"><?= icon('download') ?> Image</a>
            <form method="post" class="inline" data-confirm="Delete this card?"><?= csrf_field() ?>
              <button class="btn btn-sm btn-danger" name="delete_card" value="<?= (int)$card['id'] ?>"><?= icon('trash') ?></button>
            </form>
          </div>
          <?php else: ?>
          <div class="card fl-card" onclick="this.classList.toggle('flipped')" style="cursor:pointer;min-height:150px">
            <div class="fl-inner">
              <div class="fl-face fl-front">
                <span class="tiny faint fl-badge">Box <?= (int)$card['box'] ?> · <?= $i + 1 ?></span>
                <p class="small" style="margin-top:8px;white-space:pre-wrap;pointer-events:none"><?= e($card['front']) ?></p>
                <p class="tiny faint" style="margin-top:8px;pointer-events:none">(click to flip)</p>
              </div>
              <div class="fl-face fl-back">
                <p class="small" style="white-space:pre-wrap"><?= e($card['back']) ?></p>
              </div>
            </div>
          </div>
          <div class="flex gap-8" style="justify-content:center">
            <button class="btn btn-sm btn-ghost" type="button" onclick="fcEditCard(<?= (int)$card['id'] ?>, this)"><?= icon('edit') ?> Edit</button>
            <form method="post" class="inline" data-confirm="Delete this card?"><?= csrf_field() ?>
              <button class="btn btn-sm btn-danger" name="delete_card" value="<?= (int)$card['id'] ?>"><?= icon('trash') ?></button>
            </form>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!$cards): ?><p class="muted small">No cards in this deck.</p><?php endif; ?>
      </div>

      <div class="card" style="margin-top:16px">
        <h3 class="card-title" style="margin-top:0"><?= icon('plus') ?> Add card</h3>
        <form method="post" class="grid2">
          <?= csrf_field() ?>
          <input type="hidden" name="deck_id" value="<?= (int)$deckId ?>">
          <input class="input" name="front" placeholder="Front (question)" required>
          <input class="input" name="back" placeholder="Back (answer)" required>
          <div class="flex-col">
            <label class="small faint">Picture (optional — question &amp; answer are stamped on it)</label>
            <select class="input" name="image">
              <option value="">— none —</option>
              <?php foreach ($picFiles as $f): ?><option value="<?= e($f) ?>"><?= e($f) ?></option><?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-success" name="add_card" value="1" style="grid-column:1/-1">+ Add card</button>
        </form>
      </div>
    <?php else: ?>
      <div class="alert alert-info">Select a deck to start studying. <?= icon('game') ?></div>
    <?php endif; ?>
  </div>
</div>

<div id="fc-edit-modal" class="modal" style="display:none">
  <div class="modal-dialog modal-lg">
    <form method="post" class="card" style="margin:0">
      <?= csrf_field() ?>
      <h3 class="card-title" style="margin-top:0"><?= icon('edit') ?> Edit card</h3>
      <input type="hidden" name="edit_card" id="fce-id" value="">
      <input type="hidden" name="deck_id" value="<?= (int)$deckId ?>">
      <div class="grid2">
        <div class="flex-col"><label class="small faint">Question (stamped on front image)</label><input class="input" name="front" id="fce-front" required></div>
        <div class="flex-col"><label class="small faint">Answer (stamped on back image)</label><input class="input" name="back" id="fce-back" required></div>
      </div>
      <div class="flex gap-8" style="margin-top:12px">
        <button class="btn btn-success"><?= icon('save') ?> Save</button>
        <button class="btn btn-ghost" type="button" onclick="document.getElementById('fc-edit-modal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function fcPicMode(btn) {
  document.querySelectorAll('.fl-card').forEach(function (c) { c.classList.toggle('pic-mode'); });
  btn.classList.toggle('btn-primary');
  btn.classList.toggle('btn-ghost');
}
function fcEditCard(id, btn) {
  const card = btn.closest('.fl-card');
  const frontEl = card ? card.querySelector('.fl-front') : null;
  const backEl = card ? card.querySelector('.fl-back') : null;
  document.getElementById('fce-id').value = id;
  const pre = document.getElementById('fce-front');
  if (pre && frontEl && !card.classList.contains('has-img')) {
    pre.value = (frontEl.querySelector('p.small') || {}).textContent || '';
  } else { pre.value = ''; }
  document.getElementById('fce-back').value = backEl ? backEl.textContent.replace(/Answer/g, '').trim() : '';
  document.getElementById('fc-edit-modal').style.display = 'flex';
}
</script>
