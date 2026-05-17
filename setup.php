<?php
/**
 * Knowledge Tree - Installation Wizard
 */

require_once __DIR__ . '/config.php';

// Check if already installed
$installed = false;
try {
    $dsn = sprintf('mysql:host=%s;charset=%s', DB_HOST, DB_CHARSET);
    $testDb = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $testDb->query("USE " . DB_NAME);
    $result = $testDb->query("SELECT COUNT(*) FROM users");
    if ($result && $result->fetchColumn() > 0) {
        $installed = true;
    }
    $testDb = null;
} catch (Exception $e) {
    $installed = false;
}

if ($installed) {
    header('Location: /');
    exit;
}

// Handle AJAX request for step 1
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'setup_database') {
    header('Content-Type: application/json');

    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = $_POST['db_pass'] ?? '';

    if (empty($dbName)) {
        echo json_encode(['success' => false, 'error' => 'Database name is required']);
        exit;
    }
    if (empty($dbUser)) {
        echo json_encode(['success' => false, 'error' => 'Database username is required']);
        exit;
    }

    try {
        $dsn = sprintf('mysql:host=%s;charset=%s', $dbHost, 'utf8mb4');
        $db = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Create database
        $db->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $db->exec("USE `{$dbName}`");

        // Create tables
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_login DATETIME DEFAULT NULL,
                INDEX idx_username (username),
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS nodes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                parent_id INT DEFAULT NULL,
                title VARCHAR(255) NOT NULL DEFAULT 'New Node',
                content TEXT DEFAULT '',
                tags VARCHAR(500) DEFAULT '',
                color VARCHAR(20) DEFAULT '#6366f1',
                icon VARCHAR(50) DEFAULT 'fa-circle',
                position INT DEFAULT 0,
                is_collapsed TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (parent_id) REFERENCES nodes(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_parent_id (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Save config file
        $configContent = <<<PHP
<?php
/**
 * Knowledge Tree - Configuration
 */

define('APP_NAME', 'Knowledge Tree');
define('APP_VERSION', '1.0.0');
define('APP_ROOT', __DIR__);

// Database - MySQL
define('DB_HOST', '{$dbHost}');
define('DB_NAME', '{$dbName}');
define('DB_USER', '{$dbUser}');
define('DB_PASS', '{$dbPass}');
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
PHP;

        file_put_contents(__DIR__ . '/config.php', $configContent);
        $db = null;

        echo json_encode(['success' => true, 'message' => 'Database configured successfully']);
        exit;

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $e->getMessage()]);
        exit;
    }
}

// Handle step 2 form submission
$step = 2;
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];
    if (empty($username)) $errors[] = 'Username is required';
    elseif (strlen($username) < 3 || strlen($username) > 50) $errors[] = 'Username must be 3-50 characters';
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Letters, numbers, and underscores only';

    if (empty($password)) $errors[] = 'Password is required';
    elseif (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';

    if (empty($errors)) {
        try {
            require_once __DIR__ . '/config.php';
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
            $db = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $passwordHash]);
            $userId = $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO nodes (user_id, parent_id, title, content, color, icon) VALUES (?, NULL, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                'Getting Started',
                "# Welcome to Knowledge Tree\n\nStart organizing your knowledge by creating nodes.\n\n## Quick Tips\n\n- Use the sidebar to navigate your tree\n- Press Ctrl+K to search across all nodes\n- Right-click any node for more options",
                '#6366f1',
                'fa-book-open'
            ]);

            $db = null;
            $success = true;
        } catch (Exception $e) {
            $error = 'Failed to create account: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Knowledge Tree</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/public/css/main.css">
    <style>
        :root {
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a2e;
            --text-primary: #e2e8f0;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-primary: #6366f1;
            --border-color: rgba(148, 163, 184, 0.1);
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --transition-fast: 150ms ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .setup-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        .setup-wrapper::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 30% 20%, rgba(99, 102, 241, 0.12) 0%, transparent 50%),
                        radial-gradient(ellipse at 70% 80%, rgba(139, 92, 246, 0.08) 0%, transparent 50%);
            z-index: 0;
        }

        .setup-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Step Indicator */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            color: var(--text-muted);
            transition: all var(--transition-fast);
        }

        .step-dot.active {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }

        .step-dot.completed {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }

        .step-line {
            width: 40px;
            height: 2px;
            background: var(--border-color);
        }

        .step-line.active {
            background: var(--accent-primary);
        }

        /* Header */
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .setup-logo {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
        }

        .setup-logo svg {
            width: 28px;
            height: 28px;
        }

        .setup-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .setup-header p {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        /* Form */
        .setup-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .form-group input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.875rem;
            font-family: inherit;
            transition: all var(--transition-fast);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        .form-group input::placeholder {
            color: var(--text-muted);
        }

        .form-group small {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .password-input {
            position: relative;
        }

        .password-input input {
            padding-right: 2.5rem;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            padding: 0.25rem;
            cursor: pointer;
        }

        .password-toggle:hover {
            color: var(--text-secondary);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            font-family: inherit;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .btn-full {
            width: 100%;
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        /* Error */
        .setup-error {
            padding: 0.75rem 1rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: var(--radius-md);
            color: #fca5a5;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        /* Success */
        .success-state {
            text-align: center;
            padding: 1rem 0;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.25rem;
            background: rgba(16, 185, 129, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-icon i {
            font-size: 1.75rem;
            color: #10b981;
        }

        .success-state h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .success-state p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        /* Footer */
        .setup-footer {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .setup-footer p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: absolute;
            inset: 0;
            background: rgba(15, 15, 35, 0.9);
            backdrop-filter: blur(4px);
            border-radius: var(--radius-xl);
            z-index: 10;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-color);
            border-top-color: var(--accent-primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .loading-status {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        @media (max-width: 480px) {
            .setup-wrapper { padding: 1.25rem; }
            .setup-card { padding: 1.5rem; }
            .setup-header h1 { font-size: 1.35rem; }
            .btn-group { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="setup-wrapper">
        <div class="setup-card">
            <!-- Loading Overlay -->
            <div class="loading-overlay" id="loadingOverlay">
                <div class="loading-spinner"></div>
                <div class="loading-text">Setting up database...</div>
                <div class="loading-status" id="loadingStatus">Connecting to server</div>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step-dot active" id="step1Dot">1</div>
                <div class="step-line" id="stepLine"></div>
                <div class="step-dot" id="step2Dot">2</div>
            </div>

            <!-- Header -->
            <div class="setup-header">
                <div class="setup-logo">
                    <svg viewBox="0 0 48 48" fill="none">
                        <circle cx="24" cy="14" r="6" fill="white" opacity="0.9"/>
                        <circle cx="14" cy="34" r="5" fill="white" opacity="0.7"/>
                        <circle cx="24" cy="34" r="5" fill="white" opacity="0.7"/>
                        <circle cx="34" cy="34" r="5" fill="white" opacity="0.7"/>
                        <line x1="24" y1="20" x2="14" y2="29" stroke="white" stroke-width="2.5" stroke-linecap="round" opacity="0.6"/>
                        <line x1="24" y1="20" x2="24" y2="29" stroke="white" stroke-width="2.5" stroke-linecap="round" opacity="0.6"/>
                        <line x1="24" y1="20" x2="34" y2="29" stroke="white" stroke-width="2.5" stroke-linecap="round" opacity="0.6"/>
                    </svg>
                </div>
                <h1>Knowledge Tree</h1>
                <p id="stepTitle">Database Configuration</p>
            </div>

            <?php if ($success): ?>
                <div class="success-state">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2>Installation Complete</h2>
                    <p>Your Knowledge Tree is ready to use.</p>
                    <a href="/login" class="btn btn-primary btn-full">
                        <i class="fas fa-arrow-right"></i>
                        Go to Login
                    </a>
                </div>
            <?php else: ?>
                <div id="errorContainer" style="<?= $error ? '' : 'display:none' ?>">
                    <?php if ($error): ?>
                        <div class="setup-error">
                            <i class="fas fa-exclamation-circle" style="margin-right:0.5rem"></i>
                            <?= $error ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Step 1: Database -->
                <div id="step1">
                    <form id="dbForm" class="setup-form">
                        <div class="form-group">
                            <label>Database Host</label>
                            <input type="text" name="db_host" value="localhost" placeholder="localhost">
                        </div>
                        <div class="form-group">
                            <label>Database Name</label>
                            <input type="text" name="db_name" id="dbName" placeholder="knowledge_tree" required>
                        </div>
                        <div class="form-group">
                            <label>Database Username</label>
                            <input type="text" name="db_user" value="root" placeholder="root" required>
                        </div>
                        <div class="form-group">
                            <label>Database Password</label>
                            <div class="password-input">
                                <input type="password" name="db_pass" id="dbPass" placeholder="Leave empty if no password">
                                <button type="button" class="password-toggle" onclick="togglePassword('dbPass')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full" id="dbSubmitBtn">
                            <i class="fas fa-database"></i>
                            Connect & Configure
                        </button>
                    </form>
                </div>

                <!-- Step 2: Admin Account -->
                <div id="step2" style="display:none">
                    <form method="POST" action="/setup.php" class="setup-form">
                        <div class="form-group">
                            <label>Admin Username</label>
                            <input type="text" name="username" placeholder="Choose a username" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+" autofocus>
                            <small>Letters, numbers, and underscores only</small>
                        </div>
                        <div class="form-group">
                            <label>Admin Password</label>
                            <div class="password-input">
                                <input type="password" name="password" id="adminPass" placeholder="Min 8 characters" required minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('adminPass')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary" id="backBtn" style="flex:1">
                                <i class="fas fa-arrow-left"></i>
                                Back
                            </button>
                            <button type="submit" class="btn btn-primary" style="flex:2">
                                <i class="fas fa-user-plus"></i>
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="setup-footer">
                <p>Knowledge Tree v1.0.0</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentElement.querySelector('.password-toggle i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Step 1: Database setup with AJAX
        const dbForm = document.getElementById('dbForm');
        if (dbForm) {
            dbForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const overlay = document.getElementById('loadingOverlay');
                const statusEl = document.getElementById('loadingStatus');
                const submitBtn = document.getElementById('dbSubmitBtn');
                const errorContainer = document.getElementById('errorContainer');

                // Show loading
                overlay.classList.add('active');
                submitBtn.disabled = true;
                errorContainer.style.display = 'none';

                const formData = new FormData(dbForm);
                formData.append('action', 'setup_database');

                try {
                    statusEl.textContent = 'Connecting to server...';
                    await new Promise(r => setTimeout(r, 500));

                    statusEl.textContent = 'Creating database...';
                    await new Promise(r => setTimeout(r, 500));

                    const response = await fetch('/setup.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        statusEl.textContent = 'Database configured!';
                        await new Promise(r => setTimeout(r, 800));

                        // Move to step 2
                        document.getElementById('step1').style.display = 'none';
                        document.getElementById('step2').style.display = 'block';
                        document.getElementById('stepTitle').textContent = 'Create Admin Account';
                        document.getElementById('step1Dot').classList.remove('active');
                        document.getElementById('step1Dot').classList.add('completed');
                        document.getElementById('step1Dot').innerHTML = '<i class="fas fa-check" style="font-size:0.7rem"></i>';
                        document.getElementById('stepLine').classList.add('active');
                        document.getElementById('step2Dot').classList.add('active');
                    } else {
                        throw new Error(result.error || 'Unknown error');
                    }
                } catch (err) {
                    errorContainer.innerHTML = '<div class="setup-error"><i class="fas fa-exclamation-circle" style="margin-right:0.5rem"></i>' + err.message + '</div>';
                    errorContainer.style.display = 'block';
                } finally {
                    overlay.classList.remove('active');
                    submitBtn.disabled = false;
                }
            });
        }

        // Back button
        const backBtn = document.getElementById('backBtn');
        if (backBtn) {
            backBtn.addEventListener('click', function() {
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step1').style.display = 'block';
                document.getElementById('stepTitle').textContent = 'Database Configuration';
                document.getElementById('step1Dot').classList.add('active');
                document.getElementById('step1Dot').classList.remove('completed');
                document.getElementById('step1Dot').textContent = '1';
                document.getElementById('stepLine').classList.remove('active');
                document.getElementById('step2Dot').classList.remove('active');
            });
        }
    </script>
</body>
</html>
