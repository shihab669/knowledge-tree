<?php $pageTitle = 'Dashboard - Knowledge Tree'; ?>

<div class="app-layout">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="app-body">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <main class="main-content" id="mainContent">
            <!-- Toolbar Section -->
            <div class="toolbar-section">
                <div class="toolbar-left">
                    <span class="toolbar-title"><?= htmlspecialchars($username ?? 'User') ?></span>
                    <span class="toolbar-subtitle">Knowledge Base</span>
                </div>
                <div class="toolbar-actions">
                    <button class="toolbar-action" onclick="createRootNode(event)" title="New Node">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="toolbar-action" onclick="expandAllNodes()" title="Expand All">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button class="toolbar-action" onclick="collapseAllNodes()" title="Collapse All">
                        <i class="fas fa-compress"></i>
                    </button>
                    <button class="toolbar-action" onclick="resetView()" title="Reset View">
                        <i class="fas fa-redo"></i>
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
                    <button class="btn btn-primary btn-lg" onclick="createRootNode(event)">
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
    <button class="context-item" data-action="edit">Edit</button>
    <button class="context-item" data-action="addChild">Add Child</button>
    <button class="context-item" data-action="duplicate">Duplicate</button>
    <div class="context-divider"></div>
    <button class="context-item context-danger" data-action="delete">Delete</button>
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
<script src="/public/js/tree.js"></script>
<script src="/public/js/editor.js"></script>
