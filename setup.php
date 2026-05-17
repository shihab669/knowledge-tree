<?php
/**
 * Knowledge Tree - Installation Wizard
 */

require_once __DIR__ . '/config.php';

// Redirect if already installed
if (file_exists(DB_PATH)) {
    header('Location: /');
    exit;
}

$error = '';
$success = false;
$step = isset($_POST['step']) ? (int) $_POST['step'] : 1;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // Validate account details
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be between 3 and 50 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }

        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain uppercase, lowercase, and numbers';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $error = implode('<br>', $errors);
            $step = 1;
        } else {
            try {
                // Create data directory
                $dataDir = __DIR__ . '/data';
                if (!is_dir($dataDir)) {
                    mkdir($dataDir, 0755, true);
                }

                // Create database
                $db = new PDO("sqlite:" . DB_PATH);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Create tables
                $db->exec("
                    CREATE TABLE IF NOT EXISTS users (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        username VARCHAR(50) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        email VARCHAR(100),
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        last_login DATETIME
                    )
                ");

                $db->exec("
                    CREATE TABLE IF NOT EXISTS nodes (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER NOT NULL,
                        parent_id INTEGER,
                        title VARCHAR(255) NOT NULL DEFAULT 'New Node',
                        content TEXT DEFAULT '',
                        tags VARCHAR(500) DEFAULT '',
                        color VARCHAR(20) DEFAULT '#6366f1',
                        icon VARCHAR(50) DEFAULT 'fa-circle',
                        position INTEGER DEFAULT 0,
                        is_collapsed INTEGER DEFAULT 0,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        FOREIGN KEY (parent_id) REFERENCES nodes(id) ON DELETE CASCADE
                    )
                ");

                $db->exec("CREATE INDEX IF NOT EXISTS idx_nodes_user_id ON nodes(user_id)");
                $db->exec("CREATE INDEX IF NOT EXISTS idx_nodes_parent_id ON nodes(parent_id)");

                // Create admin user
                $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);

                $stmt = $db->prepare("INSERT INTO users (username, password, created_at, updated_at) VALUES (?, ?, datetime('now'), datetime('now'))");
                $stmt->execute([$username, $passwordHash]);

                $userId = $db->lastInsertId();

                // Create welcome node
                $db->prepare("INSERT INTO nodes (user_id, parent_id, title, content, color, icon, position, created_at, updated_at) VALUES (?, NULL, ?, ?, ?, ?, 0, datetime('now'), datetime('now'))")
                    ->execute([
                        $userId,
                        'Getting Started',
                        "# Welcome to Knowledge Tree\n\nStart organizing your knowledge by creating nodes. Each node can contain text, ideas, notes, or any information you want to structure.\n\n## Quick Tips\n\n- Use the sidebar to navigate your tree\n- Press Ctrl+K to search across all nodes\n- Right-click any node for more options\n- Drag to reorganize your tree structure",
                        '#6366f1',
                        'fa-book-open'
                    ]);

                $success = true;

            } catch (Exception $e) {
                $error = 'Installation failed: ' . $e->getMessage();
                $step = 1;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Knowledge Tree</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .setup-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: var(--bg-primary);
            position: relative;
            overflow: hidden;
        }

        .setup-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 30% 20%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                        radial-gradient(ellipse at 70% 80%, rgba(79, 70, 229, 0.06) 0%, transparent 50%);
            pointer-events: none;
        }

        .setup-card {
            position: relative;
            width: 100%;
            max-width: 480px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .setup-header {
            padding: 2rem 2.5rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .setup-logo {
            width: 56px;
            height: 56px;
            margin: 0 auto 1.25rem;
            background: var(--accent-gradient);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .setup-logo svg {
            width: 32px;
            height: 32px;
        }

        .setup-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .setup-header p {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .setup-body {
            padding: 2rem 2.5rem;
        }

        .setup-footer {
            padding: 1.5rem 2.5rem;
            border-top: 1px solid var(--border-color);
            background: var(--bg-primary);
        }

        .setup-footer p {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
        }

        /* Steps indicator */
        .steps {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .step-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--border-color);
            transition: all var(--transition-fast);
        }

        .step-dot.active {
            background: var(--accent-primary);
            width: 24px;
            border-radius: 4px;
        }

        .step-dot.completed {
            background: var(--accent-primary);
        }

        /* Requirements list */
        .requirements {
            margin-bottom: 1.5rem;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            font-size: 0.9rem;
        }

        .requirement i {
            width: 20px;
            text-align: center;
            font-size: 0.85rem;
        }

        .requirement .fa-check-circle {
            color: var(--success);
        }

        .requirement .fa-times-circle {
            color: var(--error);
        }

        .requirement .fa-spinner {
            color: var(--accent-primary);
        }

        .requirement-label {
            color: var(--text-secondary);
        }

        /* Password strength */
        .password-strength {
            display: flex;
            gap: 4px;
            margin-top: 0.5rem;
        }

        .strength-bar {
            flex: 1;
            height: 3px;
            background: var(--border-color);
            border-radius: 2px;
            transition: background var(--transition-fast);
        }

        .strength-bar.active {
            background: var(--error);
        }

        .strength-bar.active.medium {
            background: var(--warning);
        }

        .strength-bar.active.strong {
            background: var(--success);
        }

        .strength-text {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        /* Success state */
        .success-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-icon i {
            font-size: 1.75rem;
            color: var(--success);
        }

        /* Error alert */
        .setup-error {
            padding: 0.75rem 1rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: var(--radius-md);
            color: #fca5a5;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .setup-error i {
            margin-right: 0.5rem;
        }

        /* Info box */
        .info-box {
            padding: 1rem;
            background: rgba(99, 102, 241, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
        }

        .info-box p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .info-box i {
            color: var(--accent-primary);
            margin-top: 0.1rem;
        }

        @media (max-width: 576px) {
            .setup-container {
                padding: 1rem;
            }

            .setup-header {
                padding: 1.5rem;
            }

            .setup-body {
                padding: 1.5rem;
            }

            .setup-footer {
                padding: 1rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-card">
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
                <p>Installation Wizard</p>
            </div>

            <div class="setup-body">
                <?php if ($success): ?>
                    <!-- Success State -->
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">Installation Complete</h2>
                        <p style="color: var(--text-secondary); font-size: 0.9rem;">Your Knowledge Tree is ready to use.</p>
                    </div>
                    <a href="/login" class="btn btn-primary btn-full">
                        <i class="fas fa-arrow-right"></i>
                        Continue to Login
                    </a>

                <?php elseif ($step === 1): ?>
                    <!-- Step 1: Requirements Check & Account Setup -->
                    <div class="steps">
                        <div class="step-dot active"></div>
                        <div class="step-dot"></div>
                    </div>

                    <?php if ($error): ?>
                        <div class="setup-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <div class="requirements">
                        <div class="requirement">
                            <i class="fas fa-check-circle"></i>
                            <span class="requirement-label">PHP 8.0 or higher</span>
                        </div>
                        <div class="requirement">
                            <i class="fas fa-check-circle"></i>
                            <span class="requirement-label">SQLite3 extension enabled</span>
                        </div>
                        <div class="requirement">
                            <i class="fas fa-check-circle"></i>
                            <span class="requirement-label">Writable data directory</span>
                        </div>
                    </div>

                    <form method="POST" action="/setup.php" id="setupForm">
                        <input type="hidden" name="step" value="2">

                        <div class="form-group">
                            <label for="username">
                                <i class="fas fa-user"></i>
                                Username
                            </label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Choose a username"
                                required
                                minlength="3"
                                maxlength="50"
                                pattern="[a-zA-Z0-9_]+"
                                autofocus
                            >
                            <small>Letters, numbers, and underscores only</small>
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <div class="password-input">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Create a strong password"
                                    required
                                    minlength="8"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="strengthBars">
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                                <div class="strength-bar"></div>
                            </div>
                            <small class="strength-text" id="strengthText">Minimum 8 characters with mixed case and numbers</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-lock"></i>
                                Confirm Password
                            </label>
                            <div class="password-input">
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="Confirm your password"
                                    required
                                    minlength="8"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="info-box">
                            <p>
                                <i class="fas fa-info-circle"></i>
                                <span>Your credentials are stored securely using bcrypt encryption. Choose a strong password you will remember.</span>
                            </p>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-download"></i>
                            Install Knowledge Tree
                        </button>
                    </form>

                <?php endif; ?>
            </div>

            <div class="setup-footer">
                <p>Knowledge Tree v<?= APP_VERSION ?> &middot; Self-hosted knowledge management</p>
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

        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBars = document.querySelectorAll('.strength-bar');
        const strengthText = document.getElementById('strengthText');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;

                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;

                strengthBars.forEach((bar, index) => {
                    bar.classList.remove('active', 'medium', 'strong');
                    if (index < strength) {
                        bar.classList.add('active');
                        if (strength >= 3) bar.classList.add('strong');
                        else if (strength >= 2) bar.classList.add('medium');
                    }
                });

                const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
                strengthText.textContent = password.length === 0
                    ? 'Minimum 8 characters with mixed case and numbers'
                    : labels[strength] || '';
            });
        }

        // Form validation
        const setupForm = document.getElementById('setupForm');
        if (setupForm) {
            setupForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirm = document.getElementById('confirm_password').value;

                if (password !== confirm) {
                    e.preventDefault();
                    alert('Passwords do not match');
                }
            });
        }
    </script>
</body>
</html>
