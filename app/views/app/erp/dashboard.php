<?php /* ERP dashboard — 8 module overview */ ?>
<div class="page-head">
  <div>
    <h1><?= icon('briefcase') ?> ERP Dashboard</h1>
    <p class="sub"><?= e($school['name']) ?> · <?= e($school['education_level']) ?> · all modules flagged <?= demo_badge(['is_demo' => 1]) ?></p>
  </div>
  <?php if ($schools): ?>
    <form method="get" class="flex gap-6">
      <input type="hidden" name="r" value="erp/dashboard">
      <select class="input" name="school_id" onchange="this.form.submit()">
        <?php foreach ($schools as $sc): ?>
          <option value="<?= (int)$sc['id'] ?>" <?= (int)$sc['id'] === (int)$school['id'] ? 'selected' : '' ?>><?= e($sc['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  <?php endif; ?>
</div>

<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px">
  <?php $cards = [
      'hr'          => ['HR', 'users', count($stats['hr'] ? ['x'] : []), 'erp/hr', 'hr'],
      'payroll'     => ['Payroll', 'banknote', (int)$data['payroll_draft'] . ' draft', 'erp/payroll', 'payroll'],
      'recruitment' => ['Recruitment', 'flag', (int)$data['openings'] . ' open · ' . (int)$data['applications'] . ' apps', 'erp/recruitment', 'recruitment'],
      'projects'    => ['Projects & Services', 'briefcase', (int)$data['projects'] . ' active · ' . (int)$data['over_budget'] . ' over budget', 'erp/projects', 'projects'],
      'documents'   => ['Documents', 'file', (int)$data['docs'] . ' stored', 'erp/documents', 'documents'],
      'helpdesk'    => ['Help Desk', 'chat', (int)$data['tickets_open'] . ' open · ' . (int)$data['tickets_urgent'] . ' urgent', 'erp/helpdesk', 'helpdesk'],
      'assets'      => ['Fixed Assets', 'box', (int)$data['assets'] . ' in use · ' . (int)$data['assets_maint'] . ' in maintenance', 'erp/assets', 'assets'],
      'fleet'       => ['Fleet', 'truck', (int)$data['vehicles'] . ' active · ' . (int)$data['vehicles_maint'] . ' in workshop', 'erp/fleet', 'fleet'],
  ]; ?>
  <?php foreach ($cards as $key => [$label, $ic, $sub, $link]): ?>
    <a class="card stat-card" href="<?= e(url($link)) ?>" style="text-decoration:none;padding:16px 14px;display:flex;gap:12px;align-items:flex-start">
      <div class="stat-icon" style="font-size:20px"><?= icon($ic) ?></div>
      <div style="min-width:0">
        <div class="stat-value" style="font-size:1.05rem"><?= e($label) ?></div>
        <div class="small faint"><?= e($sub) ?></div>
        <?php if (!$stats[$key]): ?><span class="badge badge-danger">not installed</span><?php endif; ?>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="grid2" style="margin-top:18px">
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('trend-up') ?> Operations summary</h3>
    <table class="table">
      <tbody>
        <tr><td>Active staff</td><td><b><?= (int)$data['hr'] ?></b></td></tr>
        <tr><td>Pending leave requests</td><td><b><?= (int)$data['leave_pending'] ?></b></td></tr>
        <tr><td>Current month payroll (net)</td><td><b><?= number_format((float)$data['payroll_month']) ?> ETB</b></td></tr>
        <tr><td>Fleet cost (30 days)</td><td><b><?= number_format((float)$data['fleet_cost']) ?> ETB</b></td></tr>
        <tr><td>Total fleet kilometres</td><td><b><?= number_format((int)$data['total_km']) ?> km</b></td></tr>
        <tr><td>Asset portfolio value (purchase)</td><td><b><?= number_format((float)$data['asset_value']) ?> ETB</b></td></tr>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h3 class="card-title" style="margin-top:0"><?= icon('shield') ?> Demo mode</h3>
    <p class="small">Every ERP module ships with seeded demo records (<?= demo_badge(['is_demo' => 1]) ?>) so the system can be tested end-to-end: staff records, a payroll run, a recruitment pipeline, live projects, documents, tickets, assets and fleet logs. Demo rows are marked with a yellow <b>DEMO</b> badge and can be deleted from each module page like normal rows.</p>
  </div>
</div>
