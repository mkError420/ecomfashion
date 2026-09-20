<?php
// Site-wide configuration and bootstrap.
declare(strict_types=1);

// Session hardening. Explicit cookie params (path=/) guarantee the session
// cookie is sent to every folder, including /admin, on shared hosts.
// use_strict_mode rejects uninitialised session IDs (anti session-fixation),
// which lets us log in without a fragile session_regenerate_id() delete.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---- Database credentials ----
// PRODUCTION (InfinityFree / cPanel)
define('DB_HOST', 'sql308.infinityfree.com');
define('DB_NAME', 'if0_42963205_efashionbd');
define('DB_USER', 'if0_42963205');
define('DB_PASS', 'hrYV7cuoACRx');
define('DB_CHARSET', 'utf8mb4');

// LOCAL (XAMPP) — to test on your computer instead, comment the 4 lines above
// and uncomment these:
// define('DB_HOST', '127.0.0.1');
// define('DB_NAME', 'bd_fashion');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// ---- Auto-detect base URL so the app works in any sub-folder ----
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// When running from /admin, strip the trailing /admin
$scriptDir = preg_replace('#/admin$#', '', $scriptDir);
$base = rtrim($scriptDir, '/');
define('BASE_URL', $base === '' ? '' : $base);

define('SITE_NAME', 'Rongdhonu Fashion');
define('CURRENCY', '৳');        // Bangladeshi Taka symbol

// ---- Directories ----
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

date_default_timezone_set('Asia/Dhaka');

require_once ROOT_PATH . '/includes/functions.php';
