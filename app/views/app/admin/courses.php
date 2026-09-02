<?php /* Admin courses — accordion catalog, click course → shows teachers by zone/woreda */ ?>
<div class="page-head" style="margin-bottom:18px">
  <div>
    <h1><?= icon('graduation') ?> Course catalog</h1>
    <p class="sub"><?= count($courses) ?> course<?= count($courses) === 1 ? '' : 's' ?> published in your school</p>
  </div>
</div>

<!-- Search -->
<div style="margin-bottom:18px">
  <form method="get" class="ajax-nav" style="display:flex;gap:10px;align-items:center">
    <input type="hidden" name="r" value="admin/courses">
    <div class="input-icon-wrap" style="width:300px">
      <span class="input-ico"><?= icon('search') ?></span>
      <input class="input has-ico" type="text" name="q" value="<?= e($q) ?>" placeholder="Search courses…">
    </div>
  </form>
</div>

<!-- Region / Zone cascading filter -->
<div style="margin-bottom:18px;display:flex;gap:10px;flex-wrap:wrap">
  <form method="get" class="ajax-nav" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <input type="hidden" name="r" value="admin/courses">
    <select class="input" name="region" onchange="this.form.submit()" style="min-width:160px">
      <option value="">All Regions</option>
      <?php foreach ($regions as $rg): ?>
        <option value="<?= e($rg) ?>" <?= $region === $rg ? 'selected' : '' ?>><?= e($rg) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if ($region): ?>
      <select class="input" name="zone" onchange="this.form.submit()" style="min-width:160px">
        <option value="">All Zones</option>
        <?php foreach ($allZones as $z): ?>
          <option value="<?= e($z) ?>" <?= $zone === $z ? 'selected' : '' ?>><?= e($z) ?></option>
        <?php endforeach; ?>
      </select>
    <?php endif; ?>
    <?php if ($region || $zone): ?>
      <a class="btn btn-ghost" href="<?= e(url('admin/courses')) ?>"><?= icon('x') ?> Reset</a>
    <?php endif; ?>
  </form>
</div>

<!-- Course list -->
<div class="course-list">
  <?php if (!$courses): ?>
    <div class="card" style="text-align:center;padding:40px;color:var(--text-faint)">
      <?= icon('search') ?> <?= $region || $zone ? 'No courses match the selected filters.' : 'Select a region to browse courses.' ?>
    </div>
  <?php endif; ?>

  <?php foreach ($courses as $c): $cid = (int)$c['id']; ?>
    <div class="course-item" data-course="<?= $cid ?>">
      <div class="course-row" onclick="toggleCourse(<?= $cid ?>)">
        <span class="course-chevron" id="chevron-<?= $cid ?>"><?= icon('chevron-down') ?></span>
        <div class="course-info">
          <b class="course-name"><?= e($c['title']) ?></b>
          <?php if ($c['code']): ?><span class="course-code"><?= e($c['code']) ?></span><?php endif; ?>
        </div>
        <span class="course-meta"><?= (int)$c['students'] ?> students · <?= (int)$c['lessons'] ?> lessons</span>
        <span class="badge badge-success" style="margin-left:auto"><?= e($c['status']) ?></span>
      </div>

      <!-- Expandable detail panel -->
      <div class="course-detail" id="detail-<?= $cid ?>" style="display:none">
        <div class="course-detail-inner">
          <p class="small faint" style="margin-bottom:12px"><?= e($c['description'] ?? 'No description.') ?></p>

          <!-- Teacher info -->
          <div class="course-detail-section">
            <h4 class="small" style="margin-bottom:8px"><?= icon('users') ?> Teacher</h4>
            <div class="list-row" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px">
              <div class="avatar" style="width:32px;height:32px;font-size:12px;flex-shrink:0;background:linear-gradient(135deg,#0d9488,#059669)"><?= e(mb_strtoupper(mb_substr($c['tfirst'], 0, 1))) ?></div>
              <div>
                <div class="small" style="font-weight:600"><?= e($c['tfirst'] . ' ' . $c['tlast']) ?></div>
                <div class="tiny faint"><?= e($c['school_name']) ?> · <?= e($c['region'] ?? '') ?><?= $c['zone_name'] ? ' · ' . e($c['zone_name']) : '' ?></div>
              </div>
            </div>
          </div>

          <!-- Teachers by zone/woreda -->
          <?php if (!empty($courseTeachers[$cid])): ?>
            <div class="course-detail-section" style="margin-top:14px">
              <h4 class="small" style="margin-bottom:8px"><?= icon('building') ?> Teachers by Zone & Woreda</h4>
              <?php foreach ($courseTeachers[$cid] as $zoneName => $woredas): ?>
                <div class="zone-group">
                  <div class="zone-label"><?= icon('map') ?> <?= e($zoneName) ?></div>
                  <?php foreach ($woredas as $wName => $teachers): ?>
                    <div class="woreda-group">
                      <div class="woreda-label"><?= e($wName) ?></div>
                      <?php foreach ($teachers as $t): ?>
                        <div class="list-row" style="padding:8px 12px;border:1px solid var(--border);border-radius:8px;margin-bottom:6px">
                          <div class="avatar" style="width:28px;height:28px;font-size:11px;flex-shrink:0;background:linear-gradient(135deg,#8b5cf6,#d946ef)"><?= e(mb_strtoupper(mb_substr($t['name'], 0, 1))) ?></div>
                          <div style="min-width:0;flex:1">
                            <div class="tiny" style="font-weight:600"><?= e($t['name']) ?></div>
                            <div class="tiny faint"><?= e($t['school_name']) ?></div>
                          </div>
                          <span class="badge badge-muted tiny"><?= e($t['role'] ?? 'teacher') ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Stats -->
          <div style="display:flex;gap:16px;margin-top:14px">
            <div style="text-align:center;padding:10px 16px;border-radius:10px;background:color-mix(in srgb, var(--accent) 6%, transparent);flex:1">
              <div class="small" style="font-weight:700;color:var(--accent)"><?= (int)$c['students'] ?></div>
              <div class="tiny faint">Students</div>
            </div>
            <div style="text-align:center;padding:10px 16px;border-radius:10px;background:color-mix(in srgb, var(--info) 6%, transparent);flex:1">
              <div class="small" style="font-weight:700;color:var(--info)"><?= (int)$c['lessons'] ?></div>
              <div class="tiny faint">Lessons</div>
            </div>
            <div style="text-align:center;padding:10px 16px;border-radius:10px;background:color-mix(in srgb, var(--success) 6%, transparent);flex:1">
              <div class="small" style="font-weight:700;color:var(--success)"><?= e($c['level'] ?? '—') ?></div>
              <div class="tiny faint">Level</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<style>
.course-list { display:flex; flex-direction:column; gap:4px; }
.course-item { background:var(--bg-elev); border:1px solid var(--border); border-radius:12px; overflow:hidden; transition:box-shadow .15s ease; }
.course-item:hover { box-shadow:0 2px 8px rgba(0,0,0,.04); }
.course-row { display:flex; align-items:center; gap:12px; padding:14px 18px; cursor:pointer; user-select:none; }
.course-row:hover { background:color-mix(in srgb, var(--accent) 3%, transparent); }
.course-chevron { flex-shrink:0; width:18px; height:18px; color:var(--text-faint); transition:transform .2s ease; }
.course-item.open .course-chevron { transform:rotate(180deg); }
.course-info { flex:1; min-width:0; display:flex; align-items:center; gap:10px; }
.course-name { font-size:14px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.course-code { font-size:11px; font-family:ui-monospace,monospace; color:var(--text-faint); background:color-mix(in srgb, var(--accent) 8%, transparent); padding:2px 8px; border-radius:6px; flex-shrink:0; }
.course-meta { font-size:11.5px; color:var(--text-faint); flex-shrink:0; white-space:nowrap; }
.course-detail { border-top:1px solid var(--border); }
.course-detail-inner { padding:16px 18px; }
.course-detail-section { }
.zone-group { margin-bottom:10px; }
.zone-label { font-size:12px; font-weight:700; color:var(--accent); display:flex; align-items:center; gap:6px; margin-bottom:6px; padding:6px 10px; background:color-mix(in srgb, var(--accent) 5%, transparent); border-radius:8px; }
.zone-label .ico { width:14px; height:14px; }
.woreda-group { margin-left:18px; margin-bottom:8px; }
.woreda-label { font-size:11px; font-weight:600; color:var(--text-dim); margin-bottom:4px; padding-left:4px; }
</style>

<script>
function toggleCourse(id) {
  const el = document.getElementById('detail-' + id);
  const item = el.closest('.course-item');
  const open = item.classList.contains('open');
  // close all others
  document.querySelectorAll('.course-item.open').forEach(c => {
    c.classList.remove('open');
    c.querySelector('.course-detail').style.display = 'none';
  });
  if (!open) {
    item.classList.add('open');
    el.style.display = 'block';
  }
}
</script>
