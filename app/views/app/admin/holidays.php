<?php /* Admin holidays view — national, religious, memorial days */
$statusColors = $statusColors ?? [];
$eventTypeLabels = [
    'holiday'=>'Holiday','national_celebration'=>'National Celebration',
    'memorial_day'=>'Memorial Day','religious'=>'Religious Holiday',
];
$scopeIcons = ['national'=>'&#127987;','regional'=>'&#127963;','zonal'=>'&#127970;','woreda'=>'&#127966;','school'=>'&#127979;'];
?>
<div class="page-head" style="margin-bottom:18px">
  <div>
    <h1><?= icon('calendar') ?> Holidays &amp; Observances</h1>
    <p class="sub">National holidays, religious observances, memorial days, and official celebrations</p>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('new-holiday').style.display='block';this.style.display='none'">+ New Holiday</button>
</div>

<!-- Create Holiday Form -->
<div id="new-holiday" style="display:none;margin-bottom:20px">
  <form method="post" style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden">
    <?= csrf_field() ?>
    <input type="hidden" name="create_holiday" value="1">

    <div style="padding:20px;border-bottom:1px solid var(--border)">
      <h3 style="margin:0 0 14px;font-size:15px">Holiday Information</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px">
        <div class="flex-col"><label class="small faint">Holiday Name *</label><input class="input" name="title" required placeholder="Ethiopian New Year"></div>
        <div class="flex-col"><label class="small faint">Name (Amharic)</label><input class="input" name="title_am" placeholder="የኢትዮጵያ አዲስ ዓመት"></div>
        <div class="flex-col"><label class="small faint">Name (Afaan Oromo)</label><input class="input" name="title_om"></div>
        <div class="flex-col"><label class="small faint">Type *</label>
          <select class="input" name="event_type" required>
            <option value="holiday">Holiday</option><option value="national_celebration">National Celebration</option>
            <option value="memorial_day">Memorial Day</option><option value="religious">Religious Holiday</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Category</label>
          <select class="input" name="category">
            <option value="national">National</option><option value="regional">Regional</option>
            <option value="zonal">Zonal</option><option value="woreda">Woreda</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Scope</label>
          <select class="input" name="scope_type">
            <option value="national">National</option><option value="regional">Regional</option>
            <option value="zonal">Zonal</option><option value="woreda">Woreda</option><option value="school">School</option>
          </select>
        </div>
        <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Description</label><textarea class="input" name="description" rows="2"></textarea></div>
      </div>
    </div>

    <div style="padding:20px;border-bottom:1px solid var(--border)">
      <h3 style="margin:0 0 14px;font-size:15px">Date</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px">
        <div class="flex-col"><label class="small faint">Ethiopian Date</label><input class="input" name="ethiopian_date" placeholder="Meskerem 1, 2019 E.C."></div>
        <div class="flex-col"><label class="small faint">Gregorian Date *</label><input class="input" type="date" name="gregorian_start" required></div>
        <div class="flex-col"><label class="small faint">End Date (if multi-day)</label><input class="input" type="date" name="gregorian_end"></div>
      </div>
    </div>

    <div style="padding:20px;border-bottom:1px solid var(--border)">
      <h3 style="margin:0 0 14px;font-size:15px">Authority</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px">
        <div class="flex-col"><label class="small faint">Issuing Authority</label>
          <select class="input" name="issuing_authority">
            <option value="federal">Federal Government</option><option value="ministry">Ministry of Education</option>
            <option value="regional_bureau">Regional Bureau</option><option value="zone">Zone</option>
            <option value="woreda">Woreda</option>
          </select>
        </div>
        <div class="flex-col"><label class="small faint">Authority Name</label><input class="input" name="authority_name" placeholder="Ministry of Culture and Sport"></div>
        <div class="flex-col"><label class="small faint">Directive Number</label><input class="input" name="directive_number" placeholder="No. 1334/2024"></div>
        <div class="flex-col"><label class="small faint">Academic Year</label>
          <select class="input" name="academic_year_id"><option value="">— None —</option><?php foreach ($years as $y): ?><option value="<?= (int)$y['id'] ?>"><?= e($y['name']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="flex-col"><label class="small faint">School</label>
          <select class="input" name="school_id"><option value="">— National (all schools) —</option><?php foreach ($schools as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="flex-col"><label class="small faint">Status</label>
          <select class="input" name="event_status">
            <option value="draft">Draft</option><option value="pending_approval">Pending Approval</option>
            <option value="published">Published</option>
          </select>
        </div>
      </div>
    </div>

    <div style="padding:16px 20px;display:flex;gap:10px;justify-content:flex-end">
      <button type="button" class="btn" onclick="document.getElementById('new-holiday').style.display='none'">Cancel</button>
      <button class="btn btn-success"><?= icon('plus') ?> Create Holiday</button>
    </div>
  </form>
</div>

<!-- Holidays List -->
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden">
  <table class="table" style="margin:0">
    <thead>
      <tr>
        <th style="width:50px;text-align:center">#</th>
        <th>Holiday</th>
        <th style="width:120px">Type</th>
        <th style="width:100px">Scope</th>
        <th style="width:140px">Date</th>
        <th style="width:80px">Directive</th>
        <th style="width:100px;text-align:center">Status</th>
        <th style="width:100px">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0; foreach ($holidays as $h): ?>
      <tr>
        <td style="text-align:center;font-family:var(--font-mono,monospace);font-size:12px;color:var(--text-faint)"><?= $i + 1 ?></td>
        <td>
          <b class="small"><?= e($h['title']) ?></b>
          <?php if ($h['title_am']): ?><br><span class="tiny faint"><?= e($h['title_am']) ?></span><?php endif; ?>
          <?php if ($h['school_name']): ?><br><span class="badge badge-muted" style="font-size:10px"><?= e($h['school_name']) ?></span><?php endif; ?>
        </td>
        <td><span class="badge badge-muted" style="font-size:10px"><?= e($eventTypeLabels[$h['event_type']] ?? $h['event_type']) ?></span></td>
        <td style="font-size:12px"><?= ($scopeIcons[$h['scope_type']] ?? '') . ' ' . e(ucfirst($h['scope_type'])) ?></td>
        <td class="small">
          <?= e(date('M j, Y', strtotime($h['gregorian_start']))) ?>
          <?php if ($h['ethiopian_date']): ?><br><span class="tiny faint"><?= e($h['ethiopian_date']) ?></span><?php endif; ?>
        </td>
        <td class="tiny faint"><?= e($h['directive_number'] ?: '—') ?></td>
        <td style="text-align:center"><span class="badge <?= $statusColors[$h['status']] ?? 'badge-muted' ?>"><?= e(ucfirst(str_replace('_',' ',$h['status']))) ?></span></td>
        <td>
          <div style="display:flex;gap:4px">
            <?php if ($h['status'] === 'draft'): ?>
              <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-success" name="approve_event" value="<?= (int)$h['id'] ?>">Approve</button></form>
            <?php endif; ?>
            <?php if ($h['status'] === 'approved'): ?>
              <form method="post" class="inline"><?= csrf_field() ?><button class="btn btn-sm btn-primary" name="publish_event" value="<?= (int)$h['id'] ?>">Publish</button></form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php $i++; endforeach; ?>
      <?php if (!$holidays): ?>
      <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">No holidays yet. Click <b>+ New Holiday</b> to create one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
