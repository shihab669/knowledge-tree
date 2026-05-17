<nav class="navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="navbar-brand">
            <svg width="24" height="24" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="12" r="6" fill="#6366f1"/>
                <circle cx="12" cy="32" r="5" fill="#818cf8"/>
                <circle cx="24" cy="32" r="5" fill="#818cf8"/>
                <circle cx="36" cy="32" r="5" fill="#818cf8"/>
                <line x1="24" y1="18" x2="12" y2="27" stroke="#4f46e5" stroke-width="2"/>
                <line x1="24" y1="18" x2="24" y2="27" stroke="#4f46e5" stroke-width="2"/>
                <line x1="24" y1="18" x2="36" y2="27" stroke="#4f46e5" stroke-width="2"/>
            </svg>
            <span>Knowledge Tree</span>
        </div>
    </div>

    <div class="navbar-center">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="globalSearch" placeholder="Search..." autocomplete="off">
            <kbd>Ctrl+K</kbd>
            <div class="search-results" id="searchResults"></div>
        </div>
    </div>

    <div class="navbar-right">
        <div class="user-menu">
            <button class="user-avatar" id="userMenuBtn">
                <span class="avatar-text"><?= strtoupper(substr($username ?? 'U', 0, 1)) ?></span>
            </button>
            <div class="dropdown-menu" id="userDropdown">
                <div class="dropdown-header">
                    <span class="user-name"><?= htmlspecialchars($username ?? 'User') ?></span>
                </div>
                <div class="dropdown-divider"></div>
                <a href="/logout" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i>
                    Sign Out
                </a>
            </div>
        </div>
    </div>
</nav>
