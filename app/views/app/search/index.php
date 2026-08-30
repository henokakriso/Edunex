<?php /* Search results view — Apple Liquid Glass */
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

<form method="get" class="search-box" style="max-width:none;margin-bottom:20px">
  <input type="hidden" name="r" value="search">
  <span class="input-ico"><?= icon('search') ?></span>
  <input id="gl-search" type="text" name="q" placeholder="Search courses, lessons, exams, people, forum…" value="<?= e($q) ?>" autofocus oninput="document.getElementById('gl-clear').style.display=this.value?'flex':'none';clearTimeout(this._t);if(this.value.length>=2)this._t=setTimeout(()=>this.form.submit(),400)">
  <button type="button" class="input-icon-btn" id="gl-clear" style="display:<?= $q ? 'flex' : 'none' ?>" onclick="document.getElementById('gl-search').value='';this.style.display='none'"><?= icon('x') ?></button>
  <button type="submit" class="search-submit" title="Search"><?= icon('search') ?></button>
</form>

<?php if (mb_strlen($q) < 2): ?>
  <div class="card" style="padding:24px;text-align:center">
    <p style="color:var(--text-dim)">Type at least 2 characters to search across courses, lessons, exams, library items, people, forum topics, announcements and files.</p>
  </div>
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
      <div class="glass-search-section" style="margin-bottom:20px">
        <h3 class="small" style="margin:0 0 10px;padding:0 4px"><?= $label ?> <span class="faint">(<?= count($results[$key]) ?>)</span></h3>
        <div class="glass-search-results">
          <?php foreach ($results[$key] as $r): ?>
            <?php
            $href = $link . $r['id'];
            if ($key === 'lessons') $href = 'courses/learn&id=' . $r['course_id'] . '&lesson=' . $r['id'];
            if ($key === 'topics') $href = 'courses/discuss&course=' . $r['course_id'] . '&topic=' . $r['id'];
            if ($key === 'exams' && in_array($__u['role'] ?? '', ['regional', 'teacher'], true)) $href = 'exams/result&id=' . $r['id'];
            ?>
            <a class="glass-search-row" href="<?= e(url($href)) ?>">
              <div class="glass-search-row-ico"><?= icon($key === 'courses' ? 'books' : ($key === 'lessons' ? 'book' : ($key === 'exams' ? 'note' : ($key === 'library' ? 'books' : ($key === 'topics' ? 'chat' : ($key === 'announcements' ? 'megaphone' : ($key === 'users' ? 'users' : 'folder'))))))) ?></div>
              <div class="glass-search-row-body">
                <b class="small"><?= $highlight($r['title'] ?? ($r['name'] ?? '')) ?></b>
                <p class="tiny faint"><?= e($sub($r)) ?></p>
              </div>
              <span class="glass-search-row-arrow">→</span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if (!$total): ?>
    <div class="card" style="padding:40px;text-align:center">
      <p style="color:var(--text-dim);font-size:15px"><?= icon('search') ?></p>
      <p style="color:var(--text-dim);margin-top:8px">No results for "<?= e($q) ?>". Try different keywords.</p>
    </div>
  <?php endif; ?>
<?php endif; ?>
