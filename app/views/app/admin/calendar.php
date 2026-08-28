<?php /* Admin academic calendar — events list with create form */
$eventTypeLabels = $eventTypeLabels ?? [];
$statusColors = $statusColors ?? [];
$scopeIcons = $scopeIcons ?? [];
$hasFilter = $region || $schoolId || $yearId || $type || $status;
?>
<div class="page-head" style="margin-bottom:18px">
  <div>
    <h1><?= icon('calendar') ?> Academic Calendar</h1>
    <p class="sub">Manage events, holidays, and official celebrations</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('new-event').style.display='block';this.style.display='none'">+ New Event</button>
</div>

<!-- Create Event Form -->
<div id="new-event" style="display:none;margin-bottom:20px">
  <form method="post" style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden">
    <?= csrf_field() ?>
    <input type="hidden" name="create_event" value="1">

    <!-- Event Information -->
    <div style="padding:20px;border-bottom:1px solid var(--border)">
      <h3 style="margin:0 0 14px;font-size:15px">Event Information</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px">
        <div class="flex-col"><label class="small faint">Event Name *</label><input class="input" name="title" required placeholder="Ethiopian New Year"></div>
        <div class="flex-col"><label class="small faint">Name (Amharic)</label><input class="input" name="title_am" placeholder="የኢትዮጵያ አዲስ ዓመት"></div>
        <div class="flex-col"><label class="small faint">Name (Afaan Oromo)</label><input class="input" name="title_om" placeholder="Fuundee Affan Oromoo"></div>
        <div class="flex-col"><label class="small faint">Event Type *</label>
          <select class="input" name="event_type" required>
            <?php foreach ($eventTypeLabels as $k => $v): ?><option value="<?= $k ?>"><?= e($v) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Category</label>
          <select class="input" name="category">
            <option value="national">National</option><option value="regional">Regional</option>
            <option value="zonal">Zonal</option><option value="woreda">Woreda</option><option value="school">School</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Priority</label>
          <select class="input" name="priority">
            <option value="low">Low</option><option value="normal" selected>Normal</option>
            <option value="high">High</option><option value="critical">Critical</option>
          </select>
        </div>
        <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Description</label><textarea class="input" name="description" rows="2"></textarea></div>
      </div>
    </div>

    <!-- Dates -->
    <div style="padding:20px;border-bottom:1px solid var(--border)">
      <h3 style="margin:0 0 14px;font-size:15px">Date &amp; Time</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px">
        <div class="flex-col"><label class="small faint">Ethiopian Date</label><input class="input" name="ethiopian_date" placeholder="Meskerem 1, 2019 E.C."></div>
        <div class="flex-col"><label class="small faint">Gregorian Start *</label><input class="input" type="date" name="gregorian_start" required></div>
        <div class="flex-col"><label class="small faint">Gregorian End</label><input class="input" type="date" name="gregorian_end"></div>
        <div class="flex-col"><label class="small faint">Start Time</label><input class="input" type="time" name="start_time"></div>
        <div class="flex-col"><label class="small faint">End Time</label><input class="input" type="time" name="end_time"></div>
        <div class="flex-col"><label class="small faint">&nbsp;</label>
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 0"><input type="checkbox" name="all_day" checked> All Day Event</label>
        </div>
      </div>
    </div>

    <!-- Scope & Authority -->
    <div style="padding:20px;border-bottom:1px solid var(--border)">
      <h3 style="margin:0 0 14px;font-size:15px">Scope &amp; Authority</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px">
        <div class="flex-col"><label class="small faint">Scope</label>
          <select class="input" name="scope_type">
            <option value="national">National</option><option value="regional">Regional</option>
            <option value="zonal">Zonal</option><option value="woreda">Woreda</option>
            <option value="school">School</option><option value="grade">Grade</option><option value="section">Section</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">School (if school scope)</label>
          <select class="input" name="school_id"><option value="">— None —</option><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="flex-col"><label class="small faint">Issuing Authority</label>
          <select class="input" name="issuing_authority">
            <option value="federal">Federal Government</option><option value="ministry">Ministry of Education</option>
            <option value="regional_bureau">Regional Education Bureau</option><option value="zone">Zone</option>
            <option value="woreda">Woreda</option><option value="school">School</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Authority Name</label><input class="input" name="authority_name" placeholder="Ministry of Education"></div>
        <div class="flex-col"><label class="small faint">Directive Number</label><input class="input" name="directive_number" placeholder="No. 1334/2024"></div>
        <div class="flex-col"><label class="small faint">Academic Year</label>
          <select class="input" name="academic_year_id"><option value="">— None —</option><?php foreach ($years as $y): ?><option value="<?= (int)$y['id'] ?>"><?= e($y['name']) ?></option><?php endforeach; ?></select>
        </div>
      </div>
    </div>

    <!-- Academic Effects -->
    <div style="padding:20px;border-bottom:1px solid var(--border)">
      <h3 style="margin:0 0 14px;font-size:15px">Academic Effects</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:8px">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 10px;border-radius:6px;background:var(--bg-hover)"><input type="checkbox" name="school_closed"> School Closed</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 10px;border-radius:6px;background:var(--bg-hover)"><input type="checkbox" name="teaching_suspended"> Teaching Suspended</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 10px;border-radius:6px;background:var(--bg-hover)"><input type="checkbox" name="examination_suspended"> Examination Suspended</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 10px;border-radius:6px;background:var(--bg-hover)"><input type="checkbox" name="attendance_required"> Attendance Required</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 10px;border-radius:6px;background:var(--bg-hover)"><input type="checkbox" name="is_academic_day" checked> Is Academic Day</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 10px;border-radius:6px;background:var(--bg-hover)"><input type="checkbox" name="makeup_day_required"> Makeup Day Required</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 10px;border-radius:6px;background:var(--bg-hover)"><input type="checkbox" name="affects_academic_days"> Affects Academic Days</label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:6px 10px;border-radius:6px;background:var(--bg-hover)"><input type="checkbox" name="affects_semester"> Affects Semester</label>
      </div>
    </div>

    <!-- Publication -->
    <div style="padding:20px;border-bottom:1px solid var(--border)">
      <h3 style="margin:0 0 14px;font-size:15px">Publication</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px">
        <div class="flex-col"><label class="small faint">Status</label>
          <select class="input" name="event_status">
            <option value="draft">Draft</option><option value="pending_approval">Pending Approval</option>
            <option value="approved">Approved</option><option value="published">Published</option>
          </select>
        </div>
      </div>
    </div>

    <div style="padding:16px 20px;display:flex;gap:10px;justify-content:flex-end">
      <button type="button" class="btn" onclick="document.getElementById('new-event').style.display='none'">Cancel</button>
      <button class="btn btn-success"><?= icon('plus') ?> Create Event</button>
    </div>
  </form>
</div>

<!-- Events List -->
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden">
  <table class="table" style="margin:0">
    <thead>
      <tr>
        <th style="width:50px;text-align:center">#</th>
        <th>Event</th>
        <th style="width:120px">Type</th>
        <th style="width:100px">Scope</th>
        <th style="width:160px">Date</th>
        <th style="width:100px;text-align:center">Status</th>
        <th style="width:140px">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0; foreach ($events as $ev): ?>
      <tr>
        <td style="text-align:center;font-family:var(--font-mono,monospace);font-size:12px;color:var(--text-faint)"><?= $i + 1 ?></td>
        <td>
          <b class="small"><?= e($ev['title']) ?></b>
          <?php if ($ev['title_am']): ?><br><span class="tiny faint"><?= e($ev['title_am']) ?></span><?php endif; ?>
          <?php if ($ev['school_name']): ?><br><span class="badge badge-muted" style="font-size:10px"><?= e($ev['school_name']) ?></span><?php endif; ?>
        </td>
        <td><span class="badge badge-muted" style="font-size:10px"><?= e($eventTypeLabels[$ev['event_type']] ?? $ev['event_type']) ?></span></td>
        <td style="font-size:12px"><?= ($scopeIcons[$ev['scope_type']] ?? '') . ' ' . e(ucfirst($ev['scope_type'])) ?></td>
        <td class="small"><?= e(date('M j, Y', strtotime($ev['gregorian_start']))) ?><?= $ev['gregorian_end'] ? ' → ' . e(date('M j', strtotime($ev['gregorian_end']))) : '' ?></td>
        <td style="text-align:center"><span class="badge <?= $statusColors[$ev['status']] ?? 'badge-muted' ?>"><?= e(ucfirst(str_replace('_',' ',$ev['status']))) ?></span></td>
        <td>
          <div style="display:flex;gap:4px">
            <?php if ($ev['status'] === 'draft'): ?>
              <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-success" name="approve_event" value="<?= (int)$ev['id'] ?>">Approve</button></form>
            <?php endif; ?>
            <?php if ($ev['status'] === 'approved'): ?>
              <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-primary" name="publish_event" value="<?= (int)$ev['id'] ?>">Publish</button></form>
            <?php endif; ?>
            <?php if ($ev['status'] !== 'cancelled' && $ev['status'] !== 'published'): ?>
              <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-ghost" name="cancel_event" value="<?= (int)$ev['id'] ?>">Cancel</button></form>
            <?php endif; ?>
            <form method="post" class="inline" data-confirm="Delete this event?"><?= csrf_field() ?><input type="hidden" name="delete_event" value="<?= (int)$ev['id'] ?>"><button class="icon-btn danger" title="Delete"><?= icon('trash') ?></button></form>
          </div>
        </td>
      </tr>
      <?php $i++; endforeach; ?>
      <?php if (!$events): ?>
      <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">No calendar events yet. Click <b>+ New Event</b> to create one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
