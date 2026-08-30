<?php /* Search results view */
$total = array_sum(array_map('count', $results));
$sub = function (array $r): string {
    foreach (['course_title', 'code', 'type', 'role', 'original_name'] as $k) if (!empty($r[$k])) return (string)$r[$k];
    if (!empty($r['first_name'])) return $r['first_name'] . ' ' . ($r['last_name'] ?? '');
    return (string)($r['description'] ?? '');
};
$highlight = function (string $text) use ($q): string {
    if ($q === '') return e($text);
    return preg_replace('/(' . preg_quote(e($q), '/') . ')/iu', '<mark>$1</mark>', e($text));
};
?>
<div class="page-head">
  <div>
    <h1><?= icon('search') ?> Search</h1>
    <p class="sub"><?= (int)$total ?> result<?= $total === 1 ? '' : 's' ?> for "<?= e($q) ?>"</p>
  </div>
</div>

<form method="get" class="flex gap-8" style="margin-bottom:20px">
  <div class="input-icon-wrap" style="flex:1">
    <span class="input-ico"><?= icon('search') ?></span>
    <input class="input has-ico" style="width:100%" type="search" name="q" id="gl-search" placeholder="Search courses, lessons, exams, people, forum…" value="<?= e($q) ?>" autofocus oninput="document.getElementById('gl-clear').style.display=this.value?'flex':'none'">
    <button type="button" class="input-icon-btn" id="gl-clear" style="display:<?= $q ? 'flex' : 'none' ?>" onclick="document.getElementById('gl-search').value='';this.style.display='none';this.form.submit()"><?= icon('x') ?></button>
  </div>
  <button class="btn btn-primary">Search</button>
</form>

<?php if (mb_strlen($q) < 2): ?>
  <div class="alert alert-info">Type at least 2 characters to search across courses, lessons, exams, library items, people, forum topics, announcements and files.</div>
<?php else: ?>
  <?php foreach ([
    ['courses', icon('books') . ' Courses', 'courses/view&id='],
    ['lessons', icon('book') . ' Lessons', 'courses/learn&id=&lesson='],
    ['exams', icon('note') . ' Exams', 'exams/take&id='],
    ['library', icon('books') . ' Library', 'library/item&id='],
    ['topics', icon('chat') . ' Forum', 'courses/discuss&course=&topic='],
    ['announcements', icon('megaphone') . ' Announcements', 'communication/announcements&id='],
    ['users', icon('users') . ' People', 'messages&to='],
    ['files', icon('folder') . ' Files', 'files/view&id='],
  ] as [$key, $label, $link]): ?>
    <?php if ($results[$key]): ?>
      <h3 class="small" style="margin:18px 0 8px"><?= $label ?> <span class="faint">(<?= count($results[$key]) ?>)</span></h3>
      <div class="card" style="padding:6px 16px">
        <?php foreach ($results[$key] as $r): ?>
          <?php
          $href = $link . $r['id'];
          if ($key === 'lessons') $href = 'courses/learn&id=' . $r['course_id'] . '&lesson=' . $r['id'];
          if ($key === 'topics') $href = 'courses/discuss&course=' . $r['course_id'] . '&topic=' . $r['id'];
          if ($key === 'exams' && in_array($__u['role'] ?? '', ['regional', 'teacher'], true)) $href = 'exams/result&id=' . $r['id'];
          ?>
          <a class="list-row" href="<?= e(url($href)) ?>" style="padding:9px 0;border-bottom:1px solid var(--border);color:inherit;text-decoration:none">
            <div class="flex-1">
              <b class="small"><?= $highlight($r['title'] ?? ($r['name'] ?? '')) ?></b>
              <p class="tiny faint"><?= e($sub($r)) ?></p>
            </div>
            <span class="tiny faint">→</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if (!$total): ?><div class="alert alert-info">No results for "<?= e($q) ?>". Try different keywords.</div><?php endif; ?>
<?php endif; ?>
