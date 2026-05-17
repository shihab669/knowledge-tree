<?php
/**
 * Knowledge Tree - Entry Point
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Check if setup is needed
if (!file_exists(DB_PATH)) {
    header('Location: /setup.php');
    exit;
}

// Initialize Router
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ApiController;

$router = new Router();

// Public Routes
$router->get('/', function() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
});

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Protected Routes
$router->get('/dashboard', [DashboardController::class, 'index']);

// API Routes
$router->get('/api/tree', [ApiController::class, 'getTree']);
$router->get('/api/nodes/{id}', [ApiController::class, 'getNode']);
$router->post('/api/nodes', [ApiController::class, 'createNode']);
$router->put('/api/nodes/{id}', [ApiController::class, 'updateNode']);
$router->delete('/api/nodes/{id}', [ApiController::class, 'deleteNode']);
$router->post('/api/nodes/{id}/move', [ApiController::class, 'moveNode']);
$router->post('/api/nodes/{id}/toggle', [ApiController::class, 'toggleCollapse']);
$router->post('/api/nodes/{id}/duplicate', [ApiController::class, 'duplicateNode']);
$router->get('/api/search', [ApiController::class, 'search']);

// Dispatch
$router->dispatch();
