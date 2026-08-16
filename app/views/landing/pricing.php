<?php $title = 'Pricing — Edunex for Schools'; ?>
<div class="landing-hero" style="padding:56px 0 32px;text-align:center">
  <h1 style="font-size:2.4rem">Simple, fair <span class="grad">pricing</span> for schools</h1>
  <p class="lead" style="max-width:640px;margin:0 auto">Start free as a student. Schools pay one flat monthly rate — no per-student fees, no surprise bills. Every plan includes the full learning platform.</p>
</div>

<?php
$monthly = [
  ['Free', 0, 'For individual students', ['AI Tutor (basic, offline)', 'Up to 3 courses', 'Exams, quizzes & assignments', 'Study streaks & badges', 'Digital library access', 'Community discussion forums'], false, 'Perfect for trying Edunex on your own device.'],
  ['School', 49, 'per school / month', ['Everything in Free', 'Unlimited courses & users', 'Attendance + reports & analytics', 'Gamification & certificates', 'Excel bulk user import', 'PDF book → course generator', 'School-to-school student transfers', 'Priority support'], true, 'The full classroom toolkit for one school.'],
  ['District', 199, 'per district / month', ['Everything in School', 'Multi-school management console', 'Centralized student transfer hub', 'Advanced analytics across schools', 'Director dashboards for every school', 'Dedicated onboarding & support'], false, 'For Woredas and networks running many schools.'],
];
$yearly = [
  ['Free', 0, 'For individual students', ['Everything in Free — always free'], false, ''],
  ['School', 39, 'per school / month (billed yearly)', ['Everything in School monthly, plus:', '2 months free (≈ $468/year)', 'Priority feature requests'], true, 'Best value for long-running schools.'],
  ['District', 159, 'per district / month (billed yearly)', ['Everything in District monthly, plus:', '2 months free (≈ $1,908/year)', 'Quarterly strategy review'], false, ''],
];
?>
<div class="flex gap-8" style="justify-content:center;margin-bottom:28px">
  <span class="small faint">Monthly</span>
  <label class="switch">
    <input type="checkbox" id="bill-toggle">
    <span class="slider"></span>
  </label>
  <span class="small faint">Yearly <b style="color:var(--success)">−20%</b></span>
</div>

<div class="pricing-row" id="pricing-row">
  <?php foreach ($monthly as [$name, $price, $per, $items, $hot, $tag]): ?>
    <div class="pricing-card<?= $hot ? ' hot' : '' ?>">
      <span class="badge <?= $hot ? 'badge-accent' : '' ?>"><?= e($name) ?></span>
      <div style="margin:14px 0 4px">
        <span class="price-num">$<?= $price ?></span>
        <span class="muted small">/ <?= e($per) ?></span>
      </div>
      <p class="tiny faint" style="min-height:32px;margin-bottom:10px"><?= e($tag) ?></p>
      <ul class="pricing-list">
        <?php foreach ($items as $it): ?><li>✔ <?= e($it) ?></li><?php endforeach; ?>
      </ul>
      <a class="btn <?= $hot ? 'btn-primary' : 'btn-outline' ?>" style="width:100%;margin-top:auto"
         href="<?= e(url('auth/register')) ?>"><?= $hot ? 'Start your school today' : 'Get started' ?></a>
    </div>
  <?php endforeach; ?>
</div>

<div class="card" style="margin-top:26px;padding:22px 26px">
  <div class="flex" style="justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
    <div>
      <b>Why do schools choose Edunex?</b>
      <p class="tiny faint" style="margin-top:4px">Built for Ethiopian classrooms — works offline on school computers, and the AI runs locally with no internet or API costs.</p>
    </div>
    <div class="flex gap-16">
      <div class="stat-box"><span class="tiny faint">Setup time</span><b>&lt; 15 min</b></div>
      <div class="stat-box"><span class="tiny faint">Teacher accounts</span><b>Unlimited</b></div>
      <div class="stat-box"><span class="tiny faint">Data ownership</span><b>100% yours</b></div>
      <div class="stat-box"><span class="tiny faint">Internet needed</span><b>No</b></div>
    </div>
  </div>
</div>

<div class="alert alert-info" style="margin-top:26px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
  <span>ℹ Schools in underserved areas may qualify for the Edunex Access Program.</span>
  <a class="btn btn-sm btn-ghost" href="<?= e(url('landing/contact')) ?>">Contact us</a>
</div>

<style>
  .switch { position: relative; display: inline-block; width: 46px; height: 26px; }
  .switch input { opacity: 0; width: 0; height: 0; }
  .switch .slider { position: absolute; inset: 0; background: var(--border-strong, #555); border-radius: 26px; transition: .25s; cursor: pointer; }
  .switch .slider:before { content: ""; position: absolute; height: 20px; width: 20px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .25s; }
  .switch input:checked + .slider { background: var(--accent); }
  .switch input:checked + .slider:before { transform: translateX(20px); }
  .pricing-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; align-items: stretch; }
  @media (max-width: 900px) { .pricing-row { grid-template-columns: 1fr; } }
  .pricing-card { background: var(--card-bg, #171a21); border: 1px solid var(--border); border-radius: 16px; padding: 28px; display: flex; flex-direction: column; position: relative; }
  .pricing-card.hot { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft, rgba(124,92,255,.25)), var(--shadow); transform: translateY(-6px); }
  .pricing-card.hot .price-num { color: var(--accent); }
  .price-num { font-size: 2.4rem; font-weight: 800; }
  .pricing-list { list-style: none; margin: 6px 0 22px; flex: 1; }
  .pricing-list li { font-size: .82rem; padding: 5px 0; border-bottom: 1px dashed var(--border); }
  .pricing-list li:last-child { border-bottom: 0; }
</style>

<script>
  document.getElementById('bill-toggle').addEventListener('change', function () {
    const row = document.getElementById('pricing-row');
    <?php foreach ($monthly as $i => [$name, $price]): ?>
      const p<?= $i ?>m = <?= (int)$price ?>;
    <?php endforeach; ?>
    <?php foreach ($yearly as $i => [$name, $price]): ?>
      const p<?= $i ?>y = <?= (int)$price ?>;
    <?php endforeach; ?>
    const yearly = this.checked;
    const prices = [<?= implode(',', array_map(fn($p) => $p[1], $yearly)) ?>];
    const cards = row.querySelectorAll('.pricing-card');
    const nums = row.querySelectorAll('.price-num');
    nums.forEach((n, i) => { n.textContent = '$' + prices[i]; });
  });
</script>
