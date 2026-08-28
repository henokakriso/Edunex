<?php
/**
 * Geo API: cascading Region→Zone→Woreda lookups
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';
$u = api_user();

$action = $_GET['action'] ?? '';

if ($action === 'zones') {
    $regionId = (int)($_GET['region_id'] ?? 0);
    $regionName = trim($_GET['region_name'] ?? '');
    if ($regionId) {
        $zones = Database::all("SELECT id, name FROM zones WHERE region_id = ? AND status = 'active' ORDER BY name", [$regionId]);
    } elseif ($regionName) {
        $rid = Database::scalar("SELECT id FROM regions WHERE name = ? AND status = 'active'", [$regionName]);
        if (!$rid) api_out(['ok' => false, 'error' => 'region_not_found'], 404);
        $zones = Database::all("SELECT id, name FROM zones WHERE region_id = ? AND status = 'active' ORDER BY name", [(int)$rid]);
    } else {
        api_out(['ok' => false, 'error' => 'region_id or region_name required'], 400);
    }
    api_out(['ok' => true, 'zones' => $zones]);
}

if ($action === 'woredas') {
    $zoneId = (int)($_GET['zone_id'] ?? 0);
    if (!$zoneId) api_out(['ok' => false, 'error' => 'zone_id required'], 400);
    $woredas = Database::all("SELECT id, name FROM woredas WHERE zone_id = ? AND status = 'active' ORDER BY name", [$zoneId]);
    api_out(['ok' => true, 'woredas' => $woredas]);
}

api_out(['ok' => false, 'error' => 'invalid_action'], 400);
