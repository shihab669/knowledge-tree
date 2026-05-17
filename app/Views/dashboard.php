<?php $pageTitle = 'Dashboard - Knowledge Tree'; ?>

<div class="app-layout">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="app-body">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="main-content" id="mainContent">
            <!-- Welcome Section -->
            <div class="welcome-section" id="welcomeSection">
                <div class="welcome-card">
                    <div class="welcome-text">
                        <h1>Welcome back, <span class="gradient-text"><?= htmlspecialchars($username ?? 'User') ?></span></h1>
                        <p>Manage and organize your knowledge base.</p>
                    </div>
                    <div class="welcome-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-circle-nodes"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?= $totalNodes ?? 0 ?></span>
                                <span class="stat-label">Total Nodes</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?= count($recentNodes ?? []) ?></span>
                                <span class="stat-label">Recent</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="quick-actions">
                    <button class="action-card" onclick="createRootNode()">
                        <i class="fas fa-plus-circle"></i>
                        <span>New Node</span>
                    </button>
                    <button class="action-card" onclick="expandAllNodes()">
                        <i class="fas fa-expand-arrows-alt"></i>
                        <span>Expand All</span>
                    </button>
                    <button class="action-card" onclick="collapseAllNodes()">
                        <i class="fas fa-compress-arrows-alt"></i>
                        <span>Collapse All</span>
                    </button>
                    <button class="action-card" onclick="resetView()">
                        <i class="fas fa-sync-alt"></i>
                        <span>Reset View</span>
                    </button>
                </div>
            </div>

            <!-- Tree Visualization -->
            <div class="tree-container" id="treeContainer">
                <div class="tree-controls">
                    <div class="zoom-controls">
                        <button class="btn-icon" id="zoomIn" title="Zoom In">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <span class="zoom-level" id="zoomLevel">100%</span>
                        <button class="btn-icon" id="zoomOut" title="Zoom Out">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <button class="btn-icon" id="zoomReset" title="Reset Zoom">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>

                <div class="tree-viewport" id="treeViewport">
                    <svg id="treeSvg" class="tree-svg"></svg>
                </div>

                <!-- Empty State -->
                <div class="empty-state" id="emptyState" style="display: none;">
                    <div class="empty-icon">
                        <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                            <circle cx="60" cy="30" r="15" fill="#6366f1" opacity="0.3"/>
                            <circle cx="30" cy="80" r="12" fill="#818cf8" opacity="0.3"/>
                            <circle cx="60" cy="80" r="12" fill="#818cf8" opacity="0.3"/>
                            <circle cx="90" cy="80" r="12" fill="#818cf8" opacity="0.3"/>
                            <line x1="60" y1="45" x2="30" y2="68" stroke="#4f46e5" stroke-width="3" opacity="0.3"/>
                            <line x1="60" y1="45" x2="60" y2="68" stroke="#4f46e5" stroke-width="3" opacity="0.3"/>
                            <line x1="60" y1="45" x2="90" y2="68" stroke="#4f46e5" stroke-width="3" opacity="0.3"/>
                        </svg>
                    </div>
                    <h2>Start Your Knowledge Tree</h2>
                    <p>Create your first node to begin organizing your thoughts and ideas.</p>
                    <button class="btn btn-primary btn-lg" onclick="createRootNode()">
                        <i class="fas fa-plus"></i>
                        Create First Node
                    </button>
                </div>
            </div>

            <!-- Node Detail Panel -->
            <div class="node-panel" id="nodePanel">
                <div class="panel-header">
                    <div class="panel-title">
                        <i class="fas fa-info-circle"></i>
                        <span id="panelNodeTitle">Node Details</span>
                    </div>
                    <button class="btn-icon btn-sm" id="panelClose">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="panel-body" id="panelBody">
                    <div class="panel-loading">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Node Editor Modal -->
<?php include __DIR__ . '/partials/node-editor.php'; ?>

<!-- Context Menu -->
<div class="context-menu" id="contextMenu">
    <button class="context-item" data-action="edit">
        <i class="fas fa-edit"></i>
        Edit Node
    </button>
    <button class="context-item" data-action="addChild">
        <i class="fas fa-plus"></i>
        Add Child
    </button>
    <button class="context-item" data-action="duplicate">
        <i class="fas fa-copy"></i>
        Duplicate
    </button>
    <div class="context-divider"></div>
    <button class="context-item" data-action="expand">
        <i class="fas fa-expand"></i>
        Expand Branch
    </button>
    <button class="context-item" data-action="collapse">
        <i class="fas fa-compress"></i>
        Collapse Branch
    </button>
    <div class="context-divider"></div>
    <button class="context-item context-danger" data-action="delete">
        <i class="fas fa-trash"></i>
        Delete Node
    </button>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Hidden Data -->
<script>
window.APP_DATA = {
    tree: <?= json_encode($tree ?? []) ?>,
    userId: <?= $userId ?? 'null' ?>
};
</script>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script src="/js/tree.js"></script>
<script src="/js/editor.js"></script>
