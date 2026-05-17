<?php
/**
 * Knowledge Tree - Configuration
 */

define('APP_NAME', 'Knowledge Tree');
define('APP_VERSION', '1.0.0');
define('APP_ROOT', __DIR__);

// Database - MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'knowledge_tree');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security
define('SESSION_LIFETIME', 3600 * 8);
define('BCRYPT_COST', 12);
define('CSRF_TOKEN_LENGTH', 32);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

// App Settings
define('DEFAULT_THEME', 'dark');
define('NODES_PER_PAGE', 50);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');