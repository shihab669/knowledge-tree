<div class="auth-container">
    <div class="auth-bg"></div>

    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <svg width="40" height="40" viewBox="0 0 48 48" fill="none">
                    <circle cx="24" cy="12" r="6" fill="#6366f1"/>
                    <circle cx="12" cy="32" r="5" fill="#818cf8"/>
                    <circle cx="24" cy="32" r="5" fill="#818cf8"/>
                    <circle cx="36" cy="32" r="5" fill="#818cf8"/>
                    <line x1="24" y1="18" x2="12" y2="27" stroke="#4f46e5" stroke-width="2"/>
                    <line x1="24" y1="18" x2="24" y2="27" stroke="#4f46e5" stroke-width="2"/>
                    <line x1="24" y1="18" x2="36" y2="27" stroke="#4f46e5" stroke-width="2"/>
                </svg>
            </div>
            <h1>Knowledge Tree</h1>
            <p>Sign in</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Username" value="<?= htmlspecialchars($username ?? '') ?>" required autocomplete="username" autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input">
                    <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Sign In</button>
        </form>

        <div class="auth-footer">
            <p><a href="/setup">Set up Knowledge Tree</a></p>
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
