<div class="auth-container">
    <div class="auth-bg">
        <div class="auth-particles" id="particles"></div>
    </div>

    <div class="auth-card animate-fade-in">
        <div class="auth-header">
            <div class="auth-logo">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                    <circle cx="24" cy="12" r="6" fill="#6366f1"/>
                    <circle cx="12" cy="32" r="5" fill="#818cf8"/>
                    <circle cx="24" cy="32" r="5" fill="#818cf8"/>
                    <circle cx="36" cy="32" r="5" fill="#818cf8"/>
                    <line x1="24" y1="18" x2="12" y2="27" stroke="#4f46e5" stroke-width="2"/>
                    <line x1="24" y1="18" x2="24" y2="27" stroke="#4f46e5" stroke-width="2"/>
                    <line x1="24" y1="18" x2="36" y2="27" stroke="#4f46e5" stroke-width="2"/>
                </svg>
            </div>
            <h1>Create Account</h1>
            <p>Set up your Knowledge Tree account</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register" class="auth-form">
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
                    value="<?= htmlspecialchars($username ?? '') ?>"
                    required
                    minlength="3"
                    maxlength="50"
                    pattern="[a-zA-Z0-9_]+"
                    autofocus
                >
                <small>Letters, numbers, and underscores only</small>
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Email <span class="optional">(optional)</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="your@email.com"
                    value="<?= htmlspecialchars($email ?? '') ?>"
                >
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
                        placeholder="Create a password"
                        required
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small>Minimum 8 characters</small>
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

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-user-plus"></i>
                Create Account
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="/login">Sign in</a></p>
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
</script>
