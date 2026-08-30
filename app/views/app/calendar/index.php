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
<style>
.cal-day{position:relative;min-height:84px;border:1px solid var(--glass-border);border-radius:14px;padding:8px;background:var(--glass-bg);backdrop-filter:blur(16px) saturate(140%);-webkit-backdrop-filter:blur(16px) saturate(140%);transition:all .25s cubic-bezier(.4,0,.2,1);cursor:pointer;overflow:hidden}
.cal-day::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.07) 0%,transparent 40%,rgba(255,255,255,.02) 100%);pointer-events:none;transition:background .3s ease}
.cal-day:hover{background:var(--glass-hover-bg);border-color:var(--glass-hover-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.45),inset 0 -1px 0 rgba(255,255,255,.06),inset 1px 0 0 rgba(255,255,255,.2),inset -1px 0 0 rgba(255,255,255,.06),var(--glass-hover-shadow);transform:translateY(-1px)}
.cal-day:hover::before{background:linear-gradient(135deg,rgba(255,255,255,.14) 0%,rgba(255,255,255,.04) 50%,rgba(255,255,255,.08) 100%)}
.cal-day:active{transform:scale(.97);box-shadow:inset 0 2px 4px rgba(0,0,0,.06)}
.cal-day.today{background:rgba(13,148,136,.08);border-color:rgba(13,148,136,.3)}
.cal-day.today::before{background:linear-gradient(135deg,rgba(13,148,136,.12) 0%,rgba(255,255,255,.03) 50%,rgba(13,148,136,.06) 100%)}
.cal-day.today:hover{box-shadow:inset 0 1px 0 rgba(255,255,255,.4),inset 0 -1px 0 rgba(255,255,255,.05),0 0 20px rgba(13,148,136,.1)}
.cal-date-num{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;font-weight:800;font-size:12px;color:var(--text)}
.cal-day.today .cal-date-num{background:var(--accent);color:#fff}
.cal-event{margin-bottom:3px;border-left:3px solid;border-radius:6px;padding:2px 6px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size:12px}
.cal-dow{text-align:center;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:6px 0}
.cal-popup{position:fixed;z-index:2000;width:320px;max-height:400px;overflow:auto;background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:18px;backdrop-filter:blur(40px) saturate(180%);-webkit-backdrop-filter:blur(40px) saturate(180%);box-shadow:0 20px 60px rgba(0,0,0,.18),0 0 0 1px rgba(255,255,255,.06);display:none;padding:0}
.cal-popup::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.1) 0%,transparent 40%,rgba(255,255,255,.03) 100%);pointer-events:none}
.cal-popup.show{display:block;animation:calPopIn .25s cubic-bezier(.4,0,.2,1)}
@keyframes calPopIn{from{opacity:0;transform:scale(.92) translateY(8px)}to{opacity:1;transform:scale(1) translateY(0)}}
.cal-popup-head{padding:14px 16px 10px;border-bottom:1px solid var(--glass-border);display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1}
.cal-popup-head h4{margin:0;font-size:14px;font-weight:700;color:var(--text)}
.cal-popup-close{width:26px;height:26px;border-radius:50%;border:none;background:var(--bg-hover);color:var(--text-dim);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;transition:all .2s}
.cal-popup-close:hover{background:var(--border);color:var(--text)}
.cal-popup-body{padding:10px 14px 14px;position:relative;z-index:1}
.cal-popup-ev{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:1px solid var(--glass-border);margin-bottom:6px;background:var(--glass-bg);backdrop-filter:blur(10px);transition:all .2s ease;position:relative;overflow:hidden}
.cal-popup-ev::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.05) 0%,transparent 50%);pointer-events:none}
.cal-popup-ev:hover{background:var(--glass-hover-bg);border-color:var(--glass-hover-border);box-shadow:inset 0 1px 0 rgba(255,255,255,.3),var(--glass-hover-shadow)}
.cal-popup-ev-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.cal-popup-ev-info{flex:1;min-width:0}
.cal-popup-ev-info b{font-size:13px;color:var(--text);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cal-popup-ev-info span{font-size:11px;color:var(--text-dim)}
.cal-popup-empty{text-align:center;padding:20px 10px;color:var(--text-dim);font-size:13px}
.cal-popup-empty .ico{font-size:28px;margin-bottom:6px;display:block;opacity:.5}
.cal-toast{position:fixed;top:20px;right:20px;z-index:3000;padding:14px 20px;border-radius:14px;background:var(--glass-bg);border:1px solid var(--glass-border);backdrop-filter:blur(40px) saturate(180%);-webkit-backdrop-filter:blur(40px) saturate(180%);box-shadow:0 12px 40px rgba(0,0,0,.12),0 0 0 1px rgba(255,255,255,.06);display:flex;align-items:center;gap:10px;max-width:340px;font-size:13px;color:var(--text);animation:toastIn .3s cubic-bezier(.4,0,.2,1)}
@keyframes toastIn{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:translateX(0)}}
@keyframes toastOut{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(40px)}}
.cal-toast::before{content:'';position:absolute;inset:0;border-radius:inherit;background:linear-gradient(135deg,rgba(255,255,255,.1) 0%,transparent 40%);pointer-events:none}
</style>
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
        <div class="cal-day<?= $isToday ? ' today' : '' ?>" data-day="<?= $d ?>" data-events='<?= e(json_encode(array_map(fn($e) => ['title'=>$e['title'],'type'=>$e['type']??'event','time'=>$e['start_at']?date('H:i',strtotime($e['start_at'])):'','all_day'=>(int)($e['all_day']??0)], $dayEvents))) ?>'>
          <div class="d-flex" style="align-items:center;justify-content:space-between;margin-bottom:4px">
            <span class="cal-date-num"><?= $d ?></span>
            <?php if ($dayEvents): ?><span class="tiny faint" style="margin-left:auto"><?= count($dayEvents) ?><?= count($dayEvents)===1 ? '' : 's' ?></span><?php endif; ?>
          </div>
          <div>
            <?php foreach (array_slice($dayEvents, 0, 3) as $ev): ?>
              <div class="cal-event" style="border-left-color:<?= $typeCls[$ev['type']] ?? 'var(--muted)' ?>;background:color-mix(in srgb, <?= $typeCls[$ev['type']] ?? 'var(--muted)' ?> 12%, transparent)">
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
                · <button type="button" class="event-profile-link" style="font-weight:700;border:none;background:none;cursor:pointer;color:var(--accent);padding:0" onclick="openProfileDrawer(<?= (int)($ev['created_by'] ?? 0) ?>)"><?= icon('user') ?> View profile</button></p>
            <?php else: ?>
              <p class="tiny faint" style="margin-top:3px">Created by <b>School</b></p>
            <?php endif; ?>
          </div>
          <?php if (empty($ev['created_by'])): ?><span class="badge badge-muted">school</span>
          <?php elseif ((int)$ev['created_by'] === (int)$__u['id']): ?>
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
  const typeColors = <?= json_encode($typeCls) ?>;
  const monthName = '<?= e(date('F Y', mktime(0,0,0,$month,1,$year))) ?>';
  let activePopup = null;

  function closePopup() {
    if (activePopup) { activePopup.remove(); activePopup = null; }
    document.removeEventListener('click', outsideClick);
  }
  function outsideClick(e) {
    if (activePopup && !activePopup.contains(e.target) && !e.target.closest('.cal-day')) closePopup();
  }

  function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'cal-toast';
    t.innerHTML = '<span style="font-size:18px">📅</span><span>' + msg + '</span>';
    document.body.appendChild(t);
    setTimeout(() => { t.style.animation = 'toastOut .3s forwards'; setTimeout(() => t.remove(), 300); }, 2500);
  }

  document.querySelectorAll('.cal-day').forEach(day => {
    day.addEventListener('click', function(e) {
      if (e.target.closest('.cal-event')) return;
      closePopup();
      const d = parseInt(this.dataset.day);
      const evs = JSON.parse(this.dataset.events || '[]');

      const popup = document.createElement('div');
      popup.className = 'cal-popup';

      let body = '';
      if (evs.length) {
        evs.forEach(ev => {
          const c = typeColors[ev.type] || 'var(--muted)';
          const time = ev.all_day ? 'All day' : (ev.time || '—');
          body += '<div class="cal-popup-ev"><div class="cal-popup-ev-dot" style="background:' + c + '"></div><div class="cal-popup-ev-info"><b>' + esc(ev.title) + '</b><span>' + esc(time) + ' · ' + esc(ev.type.charAt(0).toUpperCase() + ev.type.slice(1)) + '</span></div></div>';
        });
      } else {
        body = '<div class="cal-popup-empty"><span class="ico">📭</span>No events on ' + esc(monthName.split(' ')[0]) + ' ' + d + '</div>';
      }

      popup.innerHTML = '<div class="cal-popup-head"><h4>' + esc(monthName.split(' ')[0]) + ' ' + d + '</h4><button class="cal-popup-close">&times;</button></div><div class="cal-popup-body">' + body + '</div>';
      document.body.appendChild(popup);
      activePopup = popup;

      const rect = this.getBoundingClientRect();
      let left = rect.left + rect.width / 2 - 160;
      let top = rect.bottom + 8;
      if (left < 10) left = 10;
      if (left + 320 > window.innerWidth) left = window.innerWidth - 330;
      if (top + 400 > window.innerHeight) top = rect.top - 8 - popup.offsetHeight;
      popup.style.left = left + 'px';
      popup.style.top = top + 'px';

      popup.querySelector('.cal-popup-close').addEventListener('click', closePopup);
      setTimeout(() => document.addEventListener('click', outsideClick), 0);

      if (!evs.length) {
        showToast('No events on ' + esc(monthName.split(' ')[0]) + ' ' + d);
      }
    });
  });

  function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
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