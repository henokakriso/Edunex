<?php /* Badges view */
/* Hard-coded icon + colour per badge id so icons can NEVER drift from the DB value. */
$palette = [
    1 => ['leaf',        ['#22c55e', '#15803d'], 'green'],
    2 => ['book',        ['#38bdf8', '#0369a1'], 'blue'],
    6 => ['graduation',  ['#a78bfa', '#6d28d9'], 'violet'],
    3 => ['brain',       ['#f59e0b', '#b45309'], 'amber'],
    4 => ['target',      ['#f87171', '#dc2626'], 'red'],
    5 => ['flame',       ['#fb7185', '#be123c'], 'pink'],
    7 => ['handshake',   ['#2dd4bf', '#0f766e'], 'teal'],
    8 => ['medal',       ['#fb923c', '#c2410c'], 'orange'],
];
$graph = [
    'leaf'       => '<path d="M4 20c1-8 6-13 16-15-2 11.5-7.2 16.5-14 15.5"/><path d="M4 20c4-5 8-8.5 12.5-11"/>',
    'book'       => '<path d="M4 19.5 5.5 6.8l6.2-1.7 1.8 12-6.2 1.7L4 19.5Z"/><path d="M11.5 5.3l1.8-1.8 5.6 1.5 1 6.4-3.2.9"/><path d="M9.8 8.2l4.1-1.1m-4.7 3.8 4.1-1.1"/>',
    'graduation' => '<path d="m2.5 9 9.5-5 9.5 5-9.5 5-9.5-5Z"/><path d="M6.5 11.8v4.2c0 1.7 2.5 3 5.5 3s5.5-1.3 5.5-3v-4.2"/><path d="M22 9v5"/>',
    'brain'      => '<path d="M9.5 5A2.5 2.5 0 0 0 7 7.5 2.5 2.5 0 0 0 4 10c0 .9.5 1.7 1.2 2.2A2.5 2.5 0 0 0 8 17a2.5 2.5 0 0 0 3 1.9V8h-1.5Zm5 0A2.5 2.5 0 0 1 17 7.5 2.5 2.5 0 0 1 20 13c0 .9-.5 1.7-1.2 2.2A2.5 2.5 0 0 1 16 17a2.5 2.5 0 0 1-3 1.9V8h1.5Z"/>',
    'target'     => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none"/>',
    'flame'      => '<path d="M12 21c-4 0-6.5-2.5-4.5-6 0-3 2-5 3.5-7 .5 1 1 2 1.5 2.5.5-3 2.5-5 3.5-7C17.5 6 21 9.5 19.5 15c-.5 4-5 4-7.5 6Z"/>',
    'handshake'  => '<path d="M4 8.5 9 4l6 3 5-1v3l-4.5 3-1.7-1 4.2-2"/><path d="M9.5 9.5 12.5 8 21 12v4l-7 3.5L6 16l2.8-2.6-1-3.4Z"/>',
    'medal'      => '<circle cx="12" cy="8" r="4.5"/><path d="M8.6 11.7 3.5 22h4l4.5-3 4.5 3h4l-5.1-10.3a4.5 4.5 0 0 1-5.8 0Z"/>',
];
$labels = ['learning' => 'Learning', 'quiz' => 'Quizzes', 'attendance' => 'Attendance', 'streak' => 'Streaks', 'community' => 'Community', 'level' => 'Levels'];
$earnedCount = array_sum(array_column($all, 'earned'));
?>
<div class="page-head">
  <div>
    <h1><?= icon('medal') ?> Badges</h1>
    <p class="sub"><?= (int)$me['xp'] ?> XP · Level <?= (int)$me['level'] ?> · <?= icon('flame') ?> <?= (int)$me['streak'] ?> day streak</p>
  </div>
</div>

<div class="card" style="margin-bottom:18px">
  <div class="flex gap-8" style="align-items:center;flex-wrap:wrap">
    <h3 class="card-title" style="margin-top:0"><?= icon('trophy') ?> Collection</h3>
    <span class="badge badge-accent" style="margin-left:auto"><?= $earnedCount ?> / <?= count($all) ?> earned</span>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px">
    <?php foreach ($all as $b):
        $pid = (int)$b['id'];
        $iconName = $palette[$pid][0] ?? 'medal';
        $c1 = $palette[$pid][1][0] ?? '#64748b';
        $c2 = $palette[$pid][1][1] ?? '#334155';
        $glyph = $graph[$iconName] ?? $graph['medal'];
        $earned = (bool)$b['earned']; ?>
      <div class="colab" style="display:flex;gap:14px;align-items:center;padding:16px;border:1px solid var(--border);border-radius:16px;background:var(--bg-card);<?= $earned ? '' : 'filter:grayscale(.85);opacity:.55' ?>">
        <div style="position:relative;flex:0 0 auto">
          <div style="width:74px;height:74px;border-radius:50%;background:radial-gradient(circle at 30% 28%,<?= $c1 ?>,<?= $c2 ?> 70%);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 18px <?= $earned ? $c1 . '55' : 'transparent' ?>;border:2px solid <?= $earned ? '#fff6' : 'var(--border)' ?>;color:#fff">
            <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" style="width:40px;height:40px"><?= $graph[$iconName] ?></svg>
          </div>
        </div>
        <div style="min-width:0">
          <b><?= e($b['name']) ?></b>
          <p class="tiny faint" style="margin:2px 0 0"><?= e($labels[$b['category']] ?? $b['category']) ?></p>
          <p class="tiny" style="margin:4px 0 0;font-weight:700;color:<?= $earned ? 'var(--success)' : 'var(--muted)' ?>">
            <?= $earned ? 'Earned ✓' : (int)$b['xp_required'] . ' XP needed' ?>
          </p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>