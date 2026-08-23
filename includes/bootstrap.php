<?php
/**
 * EDUNEX Bootstrap - loads config, core, session, router
 */

require_once __DIR__ . '/../config/config.php';

// Security headers
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if ($isSecure) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
// CSP — allow inline styles/scripts for existing UI, block external
$csp = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com",
    "style-src 'self' 'unsafe-inline'",
    "img-src 'self' data: blob:",
    "font-src 'self'",
    "connect-src 'self'",
    "frame-ancestors 'none'",
    "base-uri 'self'",
    "form-action 'self'",
];
header('Content-Security-Policy: ' . implode('; ', $csp));

// --- core classes ---
require_once INC_PATH . '/functions.php';
require_once INC_PATH . '/icons.php';
require_once INC_PATH . '/transfer.php';
require_once INC_PATH . '/Database.php';
require_once INC_PATH . '/Auth.php';
require_once INC_PATH . '/Pdf.php';
require_once INC_PATH . '/Qr.php';
require_once INC_PATH . '/AiTutor.php';
require_once INC_PATH . '/SubjectAuth.php';
require_once INC_PATH . '/Ledger.php';
require_once INC_PATH . '/CWorker.php';
require_once INC_PATH . '/FcardTool.php';
require_once INC_PATH . '/AiJob.php';
require_once INC_PATH . '/AiRouter.php';
require_once INC_PATH . '/Router.php';

// AI model providers (models/ folder)
foreach (glob(BASE_PATH . '/models/*.php') as $modelFile) {
    require_once $modelFile;
}

Auth::start();
require_once INC_PATH . '/routes.php';

// --- router ---
$route = $_GET['r'] ?? (($_SERVER['PATH_INFO'] ?? '') !== '' && ($_SERVER['PATH_INFO'] ?? '/') !== '/' ? ltrim($_SERVER['PATH_INFO'], '/') : 'landing');
$route = trim($route, '/');

Router::dispatch($route);

// --- response ---
Router::respond();
