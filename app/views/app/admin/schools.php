<?php /* Admin schools — page shell (static parts) */
?>
<div id="schools-root" class="list-root">
<?php include __DIR__ . '/schools_partial.php'; ?>
</div>

<!-- Detail drawer -->
<div class="drawer" id="item-drawer">
  <div class="drawer-head"><b>School details</b><button class="btn btn-sm btn-ghost" id="drawer-close">✕</button></div>
  <div id="drawer-body" class="drawer-body"></div>
</div>
<div class="drawer-backdrop" id="drawer-backdrop"></div>

<!-- Create school modal -->
<div class="modal-dialog" id="new-school-modal">
  <form method="post" class="modal-box" style="max-height:92vh;overflow:auto;padding:22px">
    <?= csrf_field() ?>
    <h3 class="card-title"><?= icon('plus') ?> Create school</h3>
    <div class="grid2" style="margin-top:6px">
      <div class="flex-col"><label class="small faint">Name *</label><input class="input" name="name" required placeholder="Addis Ababa University"></div>
      <div class="flex-col"><label class="small faint">Code * (3 letters used in student IDs)</label><input class="input" name="code" required maxlength="10" placeholder="AAU"></div>
      <div class="flex-col"><label class="small faint">Type</label>
        <select class="input" name="type"><option>school</option><option>university</option><option>college</option><option>training</option><option>other</option></select>
      </div>
      <div class="flex-col"><label class="small faint">Education level</label>
        <select class="input" name="education_level">
          <option value="kg">Kindergarten</option><option value="primary">Primary (Gr 1–8)</option>
          <option value="secondary" selected>Secondary / Preparatory (Gr 9–12)</option>
          <option value="university">University</option><option value="college">College</option>
          <option value="training">TVET / Training</option><option value="other">Other</option>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">City</label><input class="input" name="city" placeholder="Addis Ababa"></div>
      <div class="flex-col" style="grid-column:1/-1"><label class="small faint">Address</label><input class="input" name="address"></div>
      <div class="flex-col"><label class="small faint">Phone</label><input class="input" name="phone"></div>
      <div class="flex-col"><label class="small faint">Email</label><input class="input" type="email" name="email"></div>
      <div class="flex-col"><label class="small faint">Zone</label>
        <select class="input" name="zone_id"><option value="">— None —</option>
          <?php foreach (Database::all("SELECT id, name FROM zones ORDER BY name") as $z): ?>
            <option value="<?= (int)$z['id'] ?>"><?= e($z['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-col"><label class="small faint">Woreda</label>
        <select class="input" name="woreda_id"><option value="">— None —</option>
          <?php foreach (Database::all("SELECT id, name FROM woredas ORDER BY name") as $w): ?>
            <option value="<?= (int)$w['id'] ?>"><?= e($w['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="flex gap-10" style="margin-top:16px">
      <button class="btn btn-success" name="create_school" value="1"><?= icon('rocket') ?> Create</button>
      <button type="button" class="btn btn-ghost" data-close-modal="new-school-modal">Cancel</button>
    </div>
  </form>
</div>
