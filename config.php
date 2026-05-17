<?php
/**
 * Knowledge Tree - Configuration
 */

define('APP_NAME', 'Knowledge Tree');
define('APP_VERSION', '1.0.0');
define('APP_ROOT', __DIR__);

// Database
define('DB_PATH', APP_ROOT . '/data/knowledge.db');

// Security
define('SESSION_LIFETIME', 3600 * 8); // 8 hours
define('BCRYPT_COST', 12);
define('CSRF_TOKEN_LENGTH', 32);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// App Settings
define('DEFAULT_THEME', 'dark');
define('NODES_PER_PAGE', 50);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Registration
define('ALLOW_REGISTRATION', true);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', APP_ROOT . '/data/error.log');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
