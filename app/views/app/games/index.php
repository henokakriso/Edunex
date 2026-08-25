<?php /* Learning games view */
$mismatched = [];
?>
<div class="page-head">
  <div>
    <h1><?= icon('game') ?> Learning Games</h1>
    <p class="sub">Study with memory match and quiz races using your flashcard decks</p>
  </div>
</div>

<div class="flex gap-8" style="margin-bottom:18px">
  <a class="btn btn-sm <?= $game === 'memory' ? 'btn-primary' : '' ?>" href="<?= url('games&game=memory') ?>"><?= icon('game') ?> Memory Match</a>
  <a class="btn btn-sm <?= $game === 'quiz_race' ? 'btn-primary' : '' ?>" href="<?= url('games&game=quiz_race') ?>"><?= icon('bolt') ?> Quiz Race</a>
</div>

<?php if ($game === 'memory'): ?>
  <?php if (!$deckId): ?>
    <div class="card">
      <h3 class="small" style="margin-bottom:12px">Choose a flashcard deck to play Memory Match</h3>
      <?php if (!$decks): ?>
        <p class="muted">No flashcard decks yet. Create some in the AI Flashcards section first!</p>
      <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
          <?php foreach ($decks as $d): ?>
            <a class="card" href="<?= url('games&game=memory&deck=' . $d['id']) ?>" style="padding:14px;text-decoration:none;color:inherit">
              <b class="small"><?= e($d['title']) ?></b>
              <p class="tiny faint"><?= (int)$d['card_count'] ?> cards</p>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <?php if (count($cards) < 2): ?>
      <div class="alert alert-info">This deck needs at least 2 cards to play.</div>
      <a class="btn" href="<?= url('games&game=memory') ?>">← Back to decks</a>
    <?php else: ?>
      <div id="game-area">
        <div class="flex-between" style="margin-bottom:14px">
          <span class="small">Flip cards to find matching pairs. Moves: <b id="moves">0</b> · Matches: <b id="matches">0</b>/<?= (int)floor(count($cards) / 2) ?></span>
          <div class="flex gap-8">
            <span class="small" id="timer">0:00</span>
            <button class="btn btn-sm" onclick="resetGame()">↻ Reset</button>
            <a class="btn btn-sm" href="<?= url('games&game=memory') ?>">← Back</a>
          </div>
        </div>
        <div id="board" class="card" style="padding:16px;display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px">
        </div>
      </div>
      <script>
      (function() {
        const rawCards = <?= json_encode(array_map(fn($c) => ['f' => $c['front'], 'b' => $c['back']], $cards)) ?>;
        let deck = [], flipped = [], matched = new Set(), moves = 0, timer = 0, interval = null, locked = false;

        function shuffle(a) { for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; }
        function formatTime(s) { return Math.floor(s/60) + ':' + String(s%60).padStart(2,'0'); }

        function render() {
          const board = document.getElementById('board');
          board.innerHTML = '';
          deck.forEach((c, i) => {
            const el = document.createElement('div');
            el.className = 'memory-card';
            el.style.cssText = 'cursor:pointer;height:100px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:12px;text-align:center;padding:8px;border:2px solid var(--border);background:' + (matched.has(i) || flipped.includes(i) ? 'var(--primary)' : 'var(--card)') + ';color:' + (matched.has(i) || flipped.includes(i) ? '#fff' : 'var(--text)') + ';transition:all .2s';
            el.textContent = matched.has(i) || flipped.includes(i) ? c.f : '?';
            el.onclick = () => flip(i);
            board.appendChild(el);
          });
        }

        function flip(i) {
          if (locked || flipped.includes(i) || matched.has(i)) return;
          if (!interval) { interval = setInterval(() => { timer++; document.getElementById('timer').textContent = formatTime(timer); }, 1000); }
          flipped.push(i);
          render();
          if (flipped.length === 2) {
            moves++;
            document.getElementById('moves').textContent = moves;
            locked = true;
            const [a, b] = flipped;
            if (deck[a].f === deck[b].f && deck[a].b === deck[b].b) {
              matched.add(a); matched.add(b);
              document.getElementById('matches').textContent = matched.size / 2;
              flipped = []; locked = false;
              render();
              if (matched.size === deck.length) {
                clearInterval(interval);
                setTimeout(() => alert('You win! ' + moves + ' moves in ' + formatTime(timer)), 300);
              }
            } else {
              setTimeout(() => { flipped = []; locked = false; render(); }, 900);
            }
          }
        }

        window.resetGame = function() {
          clearInterval(interval); interval = null; timer = 0; moves = 0;
          flipped = []; matched = new Set(); locked = false;
          document.getElementById('moves').textContent = '0';
          document.getElementById('matches').textContent = '0';
          document.getElementById('timer').textContent = '0:00';
          deck = shuffle([...rawCards, ...rawCards].map((c, i) => ({...c, _i: i})));
          deck = shuffle(deck);
          render();
        };
        resetGame();
      })();
      </script>
    <?php endif; ?>
  <?php endif; ?>

<?php elseif ($game === 'quiz_race'): ?>
  <?php if (!$courseId): ?>
    <div class="card">
      <h3 class="small" style="margin-bottom:12px">Choose a course for Quiz Race</h3>
      <?php if (!$courses): ?>
        <p class="muted">You're not enrolled in any courses yet.</p>
      <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
          <?php foreach ($courses as $c): ?>
            <a class="card" href="<?= url('games&game=quiz_race&course=' . $c['id']) ?>" style="padding:14px;text-decoration:none;color:inherit">
              <b class="small"><?= e($c['title']) ?></b>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div id="quiz-race">
      <div class="flex-between" style="margin-bottom:14px">
        <span class="small">Answer as fast as you can! Score: <b id="qr-score">0</b> · Time: <b id="qr-time">30</b>s · Streak: <b id="qr-streak">0</b></span>
        <div class="flex gap-8">
          <button class="btn btn-sm btn-primary" id="qr-start" onclick="startRace()">Start Race</button>
          <a class="btn btn-sm" href="<?= url('games&game=quiz_race') ?>">← Back</a>
        </div>
      </div>
      <div class="card" style="min-height:300px" id="qr-board">
        <p class="muted" style="padding:20px;text-align:center">Click "Start Race" to begin!</p>
      </div>
      <script>
      (function() {
        const questions = <?= json_encode($questions) ?>;
        let score = 0, timeLeft = 30, streak = 0, current = 0, timer = null, started = false;

        function parseOpts(opts) {
          if (Array.isArray(opts)) return opts;
          try { return JSON.parse(opts); } catch(e) { return []; }
        }

        function renderQ() {
          if (current >= questions.length || timeLeft <= 0) { endRace(); return; }
          const q = questions[current];
          const opts = parseOpts(q.options);
          const board = document.getElementById('qr-board');
          let html = '<div style="margin-bottom:16px"><span class="badge badge-info">Q' + (current+1) + '/' + questions.length + '</span></div>';
          html += '<h3 class="small" style="margin-bottom:16px">' + escHtml(q.question) + '</h3>';
          html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">';
          opts.forEach((o, i) => {
            html += '<button class="btn btn-block" onclick="answerRace(' + i + ')" style="text-align:left;padding:12px">' + escHtml(o) + '</button>';
          });
          html += '</div>';
          board.innerHTML = html;
        }

        function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

        window.answerRace = function(idx) {
          if (!started) return;
          const q = questions[current];
          const correct = parseInt(q.correct_answer);
          if (idx === correct) {
            score += 10 + streak * 2;
            streak++;
          } else { streak = 0; }
          document.getElementById('qr-score').textContent = score;
          document.getElementById('qr-streak').textContent = streak;
          current++;
          renderQ();
        };

        window.startRace = function() {
          started = true; score = 0; timeLeft = 30; streak = 0; current = 0;
          document.getElementById('qr-score').textContent = '0';
          document.getElementById('qr-streak').textContent = '0';
          document.getElementById('qr-start').style.display = 'none';
          renderQ();
          timer = setInterval(() => {
            timeLeft--;
            document.getElementById('qr-time').textContent = timeLeft;
            if (timeLeft <= 0) endRace();
          }, 1000);
        };

        function endRace() {
          clearInterval(timer); started = false;
          document.getElementById('qr-board').innerHTML =
            '<div style="text-align:center;padding:30px">' +
            '<h2>Race Over!</h2>' +
            '<p class="small" style="margin:12px 0">Final Score: <b>' + score + '</b></p>' +
            '<button class="btn btn-primary" onclick="startRace()">Race Again</button></div>';
          document.getElementById('qr-start').style.display = '';
        }
      })();
      </script>
    </div>
  <?php endif; ?>
<?php endif; ?>
