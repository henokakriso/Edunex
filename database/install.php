<?php
/**
 * EDUNEX Installer
 * Usage:
 *   php database/install.php                     (clean install: super admin only)
 *   php database/install.php --root-pass=XXX     (create db/user with root)
 *   php database/install.php --admin-pass=XXX    (set super admin password)
 *   php database/install.php --demo              (also import demo users/courses)
 *   php database/install.php --db-only           (skip demo seed data)
 */

error_reporting(E_ALL);
require_once __DIR__ . '/../config/config.php';

$opts = getopt('', ['root-pass::', 'admin-pass::', 'db-only', 'keep', 'demo', 'host::', 'port::']);
$rootPass = $opts['root-pass'] ?? null;
$adminPass = $opts['admin-pass'] ?? null;
$dbOnly = isset($opts['db-only']);
$demo = isset($opts['demo']);
$keep = isset($opts['keep']);
$dbHost = $opts['host'] ?? '127.0.0.1';
$dbPort = $opts['port'] ?? '3306';

echo "========================================\n";
echo "  EDUNEX Installer v" . APP_VERSION . "\n";
echo "========================================\n";

// 1) Create database + user
$rootOk = false;
try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;charset=utf8mb4", 'root', $rootPass ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $rootOk = true;
} catch (PDOException $e) {
    if ($rootPass === null) {
        echo "[!] Root MySQL access needs a password.\n";
        echo "    Run: php database/install.php --root-pass=YOUR_ROOT_PASSWORD\n\n";
        exit(1);
    }
    echo "[!] Cannot connect as root: " . $e->getMessage() . "\n";
    exit(1);
}

if ($rootOk) {
    $dbPass = bin2hex(random_bytes(16));
    echo "[*] Creating database 'edunex' and user 'edunex'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS edunex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("CREATE USER IF NOT EXISTS 'edunex'@'localhost' IDENTIFIED BY '" . addslashes($dbPass) . "'");
    $pdo->exec("CREATE USER IF NOT EXISTS 'edunex'@'127.0.0.1' IDENTIFIED BY '" . addslashes($dbPass) . "'");
    $pdo->exec("GRANT ALL PRIVILEGES ON edunex.* TO 'edunex'@'localhost'");
    $pdo->exec("GRANT ALL PRIVILEGES ON edunex.* TO 'edunex'@'127.0.0.1'");
    $pdo->exec("FLUSH PRIVILEGES");

    // Generate cryptographic secrets
    $csrfSecret = bin2hex(random_bytes(32));
    $encryptionKey = bin2hex(random_bytes(32));
    $apiSecret = bin2hex(random_bytes(32));
    $henaSecret = bin2hex(random_bytes(32));

    // Write .env file with generated credentials and secrets
    $envFile = __DIR__ . '/../.env';
    $envContent = "DB_HOST=$dbHost\nDB_PORT=$dbPort\nDB_NAME=edunex\nDB_USER=edunex\nDB_PASS=$dbPass\n";
    $envContent .= "CSRF_SECRET=$csrfSecret\n";
    $envContent .= "ENCRYPTION_KEY=$encryptionKey\n";
    $envContent .= "API_SECRET=$apiSecret\n";
    $envContent .= "HENA_SECRET=$henaSecret\n";
    file_put_contents($envFile, $envContent);
    echo "[*] Credentials and secrets written to .env\n";
}

// 2) Import schema
$sql = file_get_contents(__DIR__ . '/schema.sql');
// split off the demo seed block (starts with the "-- DEMO SEED" comment line)
[$baseSql, $demoSql] = preg_split('/-- DEMO SEED.*$/m', $sql, 2);
$demoSql = ltrim($demoSql ?? '');
$baseSql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

if (!$demo) {
    // clean production install: schema + base seed only
    echo "[*] Importing schema + base seed (no demo data)...\n";
    $connPass = $rootOk ? ($rootPass ?? '') : (getenv('DB_PASS') ?: '');
    $connUser = $rootOk ? 'root' : (getenv('DB_USER') ?: 'edunex');
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=edunex;charset=utf8mb4", $connUser, $connPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec($baseSql);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    } catch (PDOException $e) {
        echo "[!] Schema import failed: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "[*] Importing schema + demo data...\n";
    $connPass = $rootOk ? ($rootPass ?? '') : (getenv('DB_PASS') ?: '');
    $connUser = $rootOk ? 'root' : (getenv('DB_USER') ?: 'edunex');
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=edunex;charset=utf8mb4", $connUser, $connPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec($baseSql . ($demoSql ? $demoSql : ''));
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    } catch (PDOException $e) {
        echo "[!] Schema import failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// 2b) Super admin password (generated if not given)
$adminEmail = 'superadmin@edunex.local';
if (!$adminPass) {
    $adminPass = random_password(14);
}
$hash = password_hash($adminPass, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = 'superadmin@edunex.local'");
$stmt->execute([$hash]);

// 3) Storage dirs
foreach (['uploads', 'assignments', 'profile_photos', 'certificates', 'backups', 'rate'] as $d) {
    $p = STORAGE_PATH . '/' . $d;
    if (!is_dir($p)) mkdir($p, 0775, true);
}

echo "[*] Seeding done.\n";
echo "----------------------------------------\n";
echo "  Super admin -> superadmin@edunex.local\n";
echo "  Password    -> $adminPass\n";
if ($demo) {
    echo "\n  Demo accounts (password: Passw0rd!):\n";
    echo "    admin   -> admin@edunex.local    (Administrator, school 1)\n";
    echo "    teacher -> teacher@edunex.local  (Teacher)\n";
    echo "    student -> student@edunex.local  (Student, ID: AAIS-2026-000001)\n";
    echo "    parent  -> parent@edunex.local   (Parent)\n";
    echo "    admin2  -> admin2@edunex.local   (Admin, school 2 - Bahir Dar)\n";
}
echo "----------------------------------------\n";
echo "INSTALLATION COMPLETE.\n";
