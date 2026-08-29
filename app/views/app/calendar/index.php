<?php /* Calendar view — redesigned with distinctive date styling */
$typeCls = ['class' => 'var(--accent)', 'exam' => 'var(--danger)', 'assignment' => 'var(--warning)', 'event' => 'var(--muted)', 'meeting' => 'var(--success)', 'deadline' => 'var(--danger)', 'birthday' => 'var(--warning)', 'reminder' => 'var(--muted)'];
$typeBadge = ['class' => 'badge accent', 'exam' => 'badge danger', 'assignment' => 'badge warning', 'event' => 'badge muted', 'meeting' => 'badge success', 'deadline' => 'badge danger', 'birthday' => 'badge warning', 'reminder' => 'badge muted'];
$prev = $month === 1 ? 12 : $month - 1;
$prevY = $month === 1 ? $year - 1 : $year;
$next = $month === 12 ? 1 : $month + 1;
$nextY = $month === 12 ? $year + 1 : $year;
$todayDay = (int)date('j'); $todayMon = (int)date('n'); $todayYr = (int)date('Y');
$isThisMonth = $todayMon === $month && $todayYr === $year;
$canCreate = in_array($__u['role'] ?? '', ['regional', 'principal', 'teacher'], true);
?>
<div class="page-head page-head-flex">
  <div>
    <h1><?= icon('calendar') ?> Calendar <span class="faint" style="font-weight:400">· <?= e(date('F Y', mktime(0, 0, 0, $month, 1, $year))) ?></span></h1>
    <p class="sub">Your schedule, classes, exams and deadlines at a glance</p>
  </div>
  <div class="d-flex" style="gap:8px">
    <a class="btn btn-ghost" href="<?= e(url('calendar&month=' . $prev . '&year=' . $prevY)) ?>" title="Previous month">←</a>
    <a class="btn btn-ghost" href="<?= e(url('calendar')) ?>">Today</a>
    <a class="btn btn-ghost" href="<?= e(url('calendar&month=' . $next . '&year=' . $nextY)) ?>" title="Next month">→</a>
    <?php if ($canCreate): ?><button class="btn btn-primary" data-open-modal="new-event-modal">+ Event</button><?php endif; ?>
  </div>
</div>

<?php if ($canCreate): ?>
<div class="modal-backdrop" id="new-event-modal">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <h3><?= icon('plus') ?> Add event</h3>
      <button class="btn btn-ghost btn-sm" data-close-modal><?= icon('x') ?></button>
    </div>
    <div class="modal-body">
      <form method="post">
        <?= csrf_field() ?>
        <div class="grid2">
          <div class="flex-col"><label class="small faint">Title *</label><input class="input" name="title" required placeholder="Math club meeting"></div>
          <div class="flex-col"><label class="small faint">Type</label>
            <select class="input" name="type"><?php foreach (['event','class','exam','assignment','meeting','deadline','birthday','reminder'] as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?></select>
          </div>
          <div class="flex-col"><label class="small faint">Starts</label><input class="input" type="datetime-local" name="start_at" value="<?= e(date('Y-m-d\TH:i', mktime(9, 0, 0, $month, min(28, $isThisMonth ? $todayDay : 1), $year))) ?>" required></div>
          <div class="flex-col"><label class="small faint">Ends (optional)</label><input class="input" type="datetime-local" name="end_at"></div>
          <div class="flex-col"><label class="small faint">Location</label><input class="input" name="location" placeholder="Room 12 / online"></div>
          <div class="flex-col"><label class="small faint"><input type="checkbox" name="all_day" value="1"> All day</label></div>
          <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Description</label><input class="input" name="description"></div>
        </div>
        <div class="modal-foot">
          <button type="button" class="btn btn-ghost" data-close-modal>Cancel</button>
          <button class="btn btn-primary" name="create_event" value="1"><?= icon('plus') ?> Add event</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="grid" style="grid-template-columns:1.55fr 1fr;gap:18px;align-items:start">
  <div class="card" style="padding:16px 18px">
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px">
      <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $d): ?>
        <div class="cal-dow tiny faint" style="text-align:center;font-weight:700;letter-spacing:.08em;text-transform:uppercase"><?= $d ?></div>
      <?php endforeach; ?>
      <?php for ($i = 0; $i < $startDow; $i++): ?><div></div><?php endfor; ?>
      <?php for ($d = 1; $d <= $daysInMonth; $d++): $isToday = $d === $todayDay && $isThisMonth; $dayEvents = $byDay[$d] ?? []; ?>
        <div class="cal-day" data-day="<?= $d ?>" style="position:relative;min-height:84px;border:1px solid var(--border);border-radius:12px;padding:7px;background:<?= $isToday ? 'linear-gradient(140deg, color-mix(in srgb, var(--accent) 16%, transparent), transparent)' : 'transparent' ?>">
          <div class="d-flex" style="align-items:center;justify-content:space-between;margin-bottom:4px">
            <span class="cal-date-num" style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;font-weight:800;font-size:12px;<?= $isToday ? 'background:var(--accent);color:var(--bg)' : 'color:var(--ink)' ?>"><?= $d ?></span>
            <?php if ($dayEvents): ?><span class="tiny faint" style="margin-left:auto"><?= count($dayEvents) ?><?= count($dayEvents)===1 ? ' event' : ' events' ?></span><?php endif; ?>
          </div>
          <div>
            <?php foreach (array_slice($dayEvents, 0, 3) as $ev): ?>
              <div class="cal-event" data-event="<?= (int)$ev['id'] ?>" title="<?= e($ev['title']) ?>"
                   style="margin-bottom:3px;border-left:3px solid <?= $typeCls[$ev['type']] ?? 'var(--muted)' ?>;background:color-mix(in srgb, <?= $typeCls[$ev['type']] ?? 'var(--muted)' ?> 12%, transparent);border-radius:6px;padding:2px 6px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;cursor:pointer">
                <?php if (!$ev['all_day']): ?><b class="tiny" style="font-size:11.5px;color:<?= $typeCls[$ev['type']] ?? 'var(--muted)' ?>"><?= e(date('H:i', strtotime($ev['start_at']))) ?></b> <?php endif; ?>
                <span style="font-size:12.5px;font-weight:600"><?= e(mb_strimwidth((string)$ev['title'], 0, 13, '…')) ?></span>
              </div>
            <?php endforeach; ?>
            <?php if (count($dayEvents) > 3): ?>
              <div class="tiny faint" style="padding:2px 6px">+<?= count($dayEvents) - 3 ?> more</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endfor; ?>
    </div>
    <div class="d-flex" style="gap:18px;margin-top:14px;flex-wrap:wrap">
      <?php foreach (array_unique($typeCls) as $tk => $tc): ?>
        <span class="cal-legend" style="display:inline-flex;align-items:center;gap:7px;font-size:14px;font-weight:700;color:var(--text)"><span style="display:inline-block;width:13px;height:13px;border-radius:50%;background:<?= $tc ?>"></span><?= e(ucfirst($tk)) ?></span>
      <?php endforeach; ?>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card" style="padding:16px 18px">
      <h3 class="card-title" style="margin-top:0"><?= icon('calendar') ?> This month's events</h3>
      <?php foreach ($events as $ev): ?>
        <div class="list-row" style="padding:9px 0;border-bottom:1px solid var(--border)" data-event="<?= (int)$ev['id'] ?>">
          <span style="width:4px;height:36px;border-radius:99px;background:<?= $typeCls[$ev['type']] ?? 'var(--muted)' ?>;flex:none"></span>
          <div class="flex-1">
            <div class="d-flex" style="align-items:center;gap:8px;flex-wrap:wrap">
              <b class="small event-link" style="font-size:14px"><?= e($ev['title']) ?></b>
              <span class="<?= $typeBadge[$ev['type']] ?? 'badge muted' ?>" style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em"><?= e(ucfirst($ev['type'])) ?></span>
            </div>
            <p class="tiny faint"><?= e(date('M j, H:i', strtotime($ev['start_at']))) ?><?= $ev['location'] ? ' · ' . e($ev['location']) : '' ?></p>
            <?php if (!empty($ev['description'])): ?><p class="tiny muted event-desc"><?= e(mb_strimwidth((string)$ev['description'], 0, 90, '…')) ?></p><?php endif; ?>
            <?php if (!empty($ev['creator_first'])): ?>
              <p class="tiny faint" style="margin-top:3px">Created by <b><?= e($ev['creator_first'] . ' ' . $ev['creator_last']) ?></b> <?= e(ucfirst($ev['creator_role'])) ?>
                · <button type="button" class="event-profile-link" style="font-weight:700;border:none;background:none;cursor:pointer;color:var(--accent);padding:0" onclick="openProfileDrawer(<?= (int)$ev['user_id'] ?>)"><?= icon('user') ?> View profile</button></p>
            <?php else: ?>
              <p class="tiny faint" style="margin-top:3px">Created by <b>School</b></p>
            <?php endif; ?>
          </div>
          <?php if ($ev['user_id'] === null): ?><span class="badge badge-muted">school</span>
          <?php elseif ((int)$ev['user_id'] === (int)$__u['id']): ?>
            <form method="post" class="inline" data-confirm="Delete this event?">
              <?= csrf_field() ?><input type="hidden" name="delete_event" value="<?= (int)$ev['id'] ?>">
              <button class="btn btn-sm btn-danger"><?= icon('trash') ?></button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (!$events): ?><p class="muted small">No events this month.</p><?php endif; ?>
    </div>

    <?php if ($exams): ?>
    <div class="card" style="padding:16px 18px">
      <h3 class="card-title" style="margin-top:0"><?= icon('note') ?> Upcoming exams <span class="badge danger" style="vertical-align:middle">auto</span></h3>
      <?php foreach ($exams as $ex): ?>
        <div class="list-row" style="padding:9px 0;border-bottom:1px solid var(--border)">
          <span style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;background:color-mix(in srgb, var(--danger) 13%, transparent);color:var(--danger);flex:none"><?= icon('note') ?></span>
          <div class="flex-1">
            <b class="small"><?= e($ex['title']) ?></b>
            <p class="tiny faint"><?= e(date('M j, H:i', strtotime($ex['start_at']))) ?> · <?= e($ex['course_title']) ?></p>
            <?php if (!empty($ex['description'])): ?><p class="tiny faint"><?= e(mb_strimwidth((string)$ex['description'], 0, 120, '…')) ?></p><?php endif; ?>
          </div>
          <span class="<?= $typeBadge['exam'] ?>"><?= e($ex['exam_type']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  const events = <?= json_encode(array_map(fn($e) => [
    'id' => (int)$e['id'], 'title' => $e['title'], 'type' => $e['type'],
    'start' => $e['start_at'], 'end' => $e['end_at'] ?? '',
    'location' => $e['location'] ?? '', 'all_day' => (int)($e['all_day'] ?? 0),
    'description' => $e['description'] ?? '', 'own' => (int)($e['user_id'] ?? 0),
    'creator' => ($e['creator_first'] ?? '') !== '' ? [
      'id' => (int)$e['user_id'], 'name' => $e['creator_first'] . ' ' . ($e['creator_last'] ?? ''),
      'role' => ucfirst($e['creator_role'] ?? ''),
    ] : null,
  ], $events)) ?>;
  const colors = <?= json_encode(array_map(fn($c) => $c, $typeCls)) ?>;
  let last = null;
  const close = () => { const m = document.getElementById('event-modal'); if (m) { m.remove(); last = null; } };
  const open = (id) => {
    const ev = events.find(e => e.id === id);
    if (!ev) return;
    close();
    const m = document.createElement('div');
    m.id = 'event-modal';
    m.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1000;display:flex;align-items:center;justify-content:center;';
    const inside = document.createElement('div');
    inside.className = 'card';
    inside.style.cssText = 'max-width:520px;width:92%;padding:22px;max-height:80vh;overflow:auto;';
    const fmt = (s) => s ? new Date(s.replace(' ', 'T')).toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
    const typeTxt = ev.type.charAt(0).toUpperCase() + ev.type.slice(1);
    inside.innerHTML =
      '<div class="flex-between"><h3 style="margin:0">' + escapeHtml(ev.title) + '</h3>' +
      '<button type="button" class="btn btn-sm btn-ghost" data-close="">✕</button></div>' +
      '<span class="badge" style="margin-top:8px;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;background:color-mix(in srgb, ' + (colors[ev.type] || 'var(--muted)') + ' 15%, transparent);color:' + (colors[ev.type] || 'var(--muted)') + '">' + typeTxt + '</span>' +
      (ev.description ? '<p style="margin:10px 0">' + escapeHtml(ev.description) + '</p>' : '<p class="muted small" style="margin-top:10px">No description.</p>') +
      '<div class="tiny faint" style="margin-top:12px;line-height:1.7">' +
      '<div>' + (ev.all_day ? 'All day · ' + fmt(ev.start).split(',')[0] : fmt(ev.start) + (ev.end ? ' → ' + fmt(ev.end) : '')) + '</div>' +
      (ev.location ? '<div>' + escapeHtml(ev.location) + '</div>' : '') +
      '</div>' +
      '<div style="margin-top:14px;padding-top:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap">' +
      (ev.creator
        ? '<span style="font-size:14px"><b>' + escapeHtml(ev.creator.name) + '</b> <span class="tiny faint">· ' + escapeHtml(ev.creator.role) + ' · created this event</span></span>' +
          '<button type="button" class="btn btn-sm btn-ghost" onclick="openProfileDrawer(' + ev.creator.id + ')">' + escapeHtml('View profile') + '</button>'
        : '<span class="tiny faint">Created by <b>School</b></span>') +
      '</div>';
    m.appendChild(inside);
    document.body.appendChild(m);
    setTimeout(() => { const b = m.querySelector('[data-close]'); if (b) b.addEventListener('click', close); }, 0);
    m.addEventListener('click', e => { if (e.target === m) close(); });
  };
  window.closeCalendarModal = close;
  document.addEventListener('click', e => {
    const row = e.target.closest('[data-event]');
    if (row && !row.closest('form')) open(parseInt(row.dataset.event, 10));
  });
})();
</script>

<script>
(function () {
  let open = false;
  const roleBadge = { ministry: 'badge danger', regional: 'badge danger', principal: 'badge danger', teacher: 'badge accent', student: 'badge warning', parent: 'badge muted' };
  const typeName = { school: 'School', university: 'University', college: 'College', training: 'Training centre', other: 'Institution' };

  function build(p) {
    const b = roleBadge[p.role_key] || 'badge muted';
    const school = p.school ? '<div class="profile-drawer-school"><div class="avatar" style="background:color-mix(in srgb, var(--accent) 12%, transparent);color:var(--accent)"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 13 9-6 9 6"/><path d="M6 11v8h12v-8"/><path d="M6 19h12M10 15h4"/></svg></div>' +
      '<div><b style="font-size:14px">' + escapeHtml(p.school.name) + '</b>' +
      '<p class="tiny faint">' + escapeHtml(typeName[p.school.type] || p.school.type) + (p.school.city ? ' · ' + escapeHtml(p.school.city) : '') + '</p>' +
      (p.school.address ? '<p class="tiny faint">' + escapeHtml(p.school.address) + '</p>' : '') + '</div></div>' : '<p class="muted small">Not assigned to any school.</p>';
    const courses = (p.courses && p.courses.length)
      ? '<div class="profile-drawer-courses">' + p.courses.map(c => '<div class="list-row" style="padding:9px;border:1px solid var(--border);border-radius:9px"><div class="flex-1"><b class="small">' + escapeHtml(c.title) + '</b><p class="tiny faint">' + (c.subject ? escapeHtml(c.subject) + ' · ' : '') + escapeHtml(c.level) + '</p></div></div>').join('') + '</div>'
      : '<p class="tiny faint">No courses listed.</p>';
    const stats = (p.xp > 0 || p.level > 0)
      ? '<div class="d-flex" style="gap:8px;justify-content:center;margin-top:10px"><span class="badge accent">Level ' + p.level + '</span><span class="badge warning">' + p.xp + ' XP</span></div>' : '';
    const seen = p.last_login
      ? 'Last seen ' + new Date(p.last_login.replace(' ', 'T')).toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
      : 'Last seen —';
    return '<div class="profile-drawer-user">' +
      '<img class="avatar profile-drawer-avatar" src="' + escapeHtml(p.avatar) + '" alt="" onerror="this.onerror=null;this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'">' +
      '<div class="avatar profile-drawer-avatar" style="display:none">' + escapeHtml(p.initials) + '</div>' +
      '<h3 style="margin:0;font-size:18px">' + escapeHtml(p.name) + '</h3>' +
      '<span class="' + b + '" style="font-size:12px">' + escapeHtml(p.role) + '</span>' +
      '<p style="margin:7px 0 0;font-size:13px;font-weight:600;color:var(--text)">' + (p.student_id ? 'Student ID ' + escapeHtml(p.student_id) + ' · ' : '') + 'Member since ' + escapeHtml(p.member_since) + '</p>' +
      '<p style="margin:2px 0 0;font-size:13px;font-weight:600;color:var(--text)">' + seen + '</p>' + stats + '</div>' +
      (p.bio ? '<p class="muted small" style="margin-top:14px;text-align:center">' + escapeHtml(p.bio) + '</p>' : '') +
      '<h4 class="small" style="margin:16px 0 8px">Where they teach</h4>' + school +
      '<h4 class="small" style="margin:16px 0 8px">' + (p.role_key === 'teacher' ? 'Courses they teach' : 'Courses they take') + '</h4>' + courses +
      '<div style="margin-top:16px"><a class="btn btn-primary btn-block" style="width:100%" href="' + EDUNEX.URL + '/index.php?r=messages&to=' + p.id + '">Message</a></div>';
  }

  window.openProfileDrawer = function (id) {
    if (open) return;
    open = true;
    const back = document.createElement('div');
    back.className = 'profile-drawer-backdrop';
    const d = document.createElement('aside');
    d.className = 'profile-drawer';
    d.innerHTML = '<div class="profile-drawer-head"><b>Profile</b><button type="button" class="profile-drawer-x" title="Close">✕</button></div><div class="profile-drawer-body"><p class="muted small">Loading…</p></div>';
    document.body.appendChild(back);
    document.body.appendChild(d);
    requestAnimationFrame(() => { back.classList.add('open'); d.classList.add('open'); });
    const close = () => {
      if (!open) return;
      open = false;
      back.classList.remove('open'); d.classList.remove('open');
      setTimeout(() => { back.remove(); d.remove(); }, 240);
    };
    back.addEventListener('click', close);
    d.querySelector('.profile-drawer-x').addEventListener('click', close);
    fetch(EDUNEX.URL + '/index.php?r=api/profile&id=' + encodeURIComponent(id))
      .then(r => r.json())
      .then(j => {
        if (!j.ok) throw new Error(j.error || 'failed');
        d.querySelector('.profile-drawer-body').innerHTML = build(j.profile);
      })
      .catch(() => {
        d.querySelector('.profile-drawer-body').innerHTML = '<p class="muted small">Could not load profile.</p>';
      });
  };
})();
</script>