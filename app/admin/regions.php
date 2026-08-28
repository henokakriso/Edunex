<?php
/**
 * Admin module: regions / zones / woredas management
 */

class Ctl_regions {
    public function run(): void {
        $u = require_role('ministry');
        $tab = $_GET['tab'] ?? 'regions';
        if (!in_array($tab, ['regions', 'zones', 'woredas'])) $tab = 'regions';

        $q = trim($_GET['q'] ?? '');
        $regionFilter = (int)($_GET['region_id'] ?? 0);
        $zoneFilter = (int)($_GET['zone_id'] ?? 0);

        $regions = Database::all("SELECT r.*, (SELECT COUNT(*) FROM zones z WHERE z.region_id = r.id) AS zone_count, (SELECT COUNT(*) FROM woredas w JOIN zones z ON z.id = w.zone_id WHERE z.region_id = r.id) AS woreda_count FROM regions r WHERE r.status = 'active' ORDER BY r.name");
        $zones = Database::all("SELECT z.*, r.name AS region_name FROM zones z JOIN regions r ON r.id = z.region_id WHERE z.status = 'active' ORDER BY r.name, z.name");
        $woredas = Database::all("SELECT w.*, z.name AS zone_name, r.name AS region_name FROM woredas w JOIN zones z ON z.id = w.zone_id JOIN regions r ON r.id = z.region_id WHERE w.status = 'active' ORDER BY r.name, z.name, w.name");

        if ($regionFilter) $zones = array_filter($zones, fn($z) => (int)$z['region_id'] === $regionFilter);
        if ($zoneFilter) $woredas = array_filter($woredas, fn($w) => (int)$w['zone_id'] === $zoneFilter);
        if ($q !== '') {
            $zones = array_filter($zones, fn($z) => stripos($z['name'], $q) !== false || stripos($z['region_name'], $q) !== false);
            $woredas = array_filter($woredas, fn($w) => stripos($w['name'], $q) !== false || stripos($w['zone_name'], $q) !== false);
        }
        $zones = array_values($zones);
        $woredas = array_values($woredas);

        $stats = [
            'regions' => count($regions),
            'zones' => count(Database::all("SELECT id FROM zones WHERE status = 'active'")),
            'woredas' => count(Database::all("SELECT id FROM woredas WHERE status = 'active'")),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            if (isset($_POST['create_region'])) {
                $name = trim($_POST['name'] ?? '');
                $code = strtoupper(trim($_POST['code'] ?? ''));
                if (!$name) { flash('danger', 'Region name required.'); redirect('admin/regions'); }
                Database::insert('regions', ['name' => $name, 'code' => $code, 'lat' => $_POST['lat'] ?: null, 'lng' => $_POST['lng'] ?: null]);
                log_activity('region', "Created region: $name", (int)$u['id']);
                flash('success', 'Region created.');
                redirect('admin/regions');
            }
            if (($rid = (int)($_POST['delete_region'] ?? 0))) {
                Database::update('regions', ['status' => 'archived'], 'id = ?', [$rid]);
                log_activity('region', "Archived region #$rid", (int)$u['id']);
                flash('success', 'Region archived.');
                redirect('admin/regions');
            }
            if (isset($_POST['create_zone'])) {
                $name = trim($_POST['name'] ?? '');
                $regionId = (int)($_POST['region_id'] ?? 0);
                if (!$name || !$regionId) { flash('danger', 'Zone name and region required.'); redirect('admin/regions?tab=zones'); }
                Database::insert('zones', ['name' => $name, 'region_id' => $regionId]);
                log_activity('zone', "Created zone: $name", (int)$u['id']);
                flash('success', 'Zone created.');
                redirect('admin/regions?tab=zones');
            }
            if (($zid = (int)($_POST['delete_zone'] ?? 0))) {
                Database::update('zones', ['status' => 'archived'], 'id = ?', [$zid]);
                log_activity('zone', "Archived zone #$zid", (int)$u['id']);
                flash('success', 'Zone archived.');
                redirect('admin/regions?tab=zones');
            }
            if (isset($_POST['create_woreda'])) {
                $name = trim($_POST['name'] ?? '');
                $zoneId = (int)($_POST['zone_id'] ?? 0);
                if (!$name || !$zoneId) { flash('danger', 'Woreda name and zone required.'); redirect('admin/regions?tab=woredas'); }
                Database::insert('woredas', ['name' => $name, 'zone_id' => $zoneId]);
                log_activity('woreda', "Created woreda: $name", (int)$u['id']);
                flash('success', 'Woreda created.');
                redirect('admin/regions?tab=woredas');
            }
            if (($wid = (int)($_POST['delete_woreda'] ?? 0))) {
                Database::update('woredas', ['status' => 'archived'], 'id = ?', [$wid]);
                log_activity('woreda', "Archived woreda #$wid", (int)$u['id']);
                flash('success', 'Woreda archived.');
                redirect('admin/regions?tab=woredas');
            }
        }

        Router::render('app/admin/regions', [
            'title' => 'Regions & Zones', 'tab' => $tab,
            'regions' => $regions, 'zones' => $zones, 'woredas' => $woredas,
            'stats' => $stats, 'q' => $q, 'regionFilter' => $regionFilter, 'zoneFilter' => $zoneFilter,
        ]);
    }
}
