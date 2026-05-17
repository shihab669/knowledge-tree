/**
 * Knowledge Tree - Main Application
 */

const App = {
    // State
    selectedNodeId: null,
    searchTimeout: null,

    /**
     * Initialize application
     */
    init() {
        this.bindEvents();
        this.initKeyboardShortcuts();
        this.initUserMenu();
        this.initSearch();
        console.log('Knowledge Tree initialized');
    },

    /**
     * Bind global events
     */
    bindEvents() {
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => this.toggleSidebar());
        }

        // Close sidebar on overlay click (mobile)
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => this.toggleSidebar());
        }

        // Add root node buttons
        const addRootNode = document.getElementById('addRootNode');
        const addRootNodeSidebar = document.getElementById('addRootNodeSidebar');
        if (addRootNode) addRootNode.addEventListener('click', (e) => this.createRootNode(e));
        if (addRootNodeSidebar) addRootNodeSidebar.addEventListener('click', (e) => this.createRootNode(e));

        // Close node panel
        const panelClose = document.getElementById('panelClose');
        if (panelClose) {
            panelClose.addEventListener('click', () => {
                const nodePanel = document.getElementById('nodePanel');
                if (nodePanel) nodePanel.classList.remove('active');
            });
        }

        // Close panel when clicking on main content area
        const mainContent = document.getElementById('mainContent');
        const treeContainer = document.getElementById('treeContainer');
        if (mainContent) {
            mainContent.addEventListener('click', (e) => {
                const nodePanel = document.getElementById('nodePanel');
                const clickedInPanel = nodePanel && nodePanel.contains(e.target);
                const clickedInTree = treeContainer && treeContainer.contains(e.target);
                const clickedOnNode = e.target.closest('.tree-node');

                if (nodePanel && nodePanel.classList.contains('active') && !clickedInPanel) {
                    if (clickedInTree && !clickedOnNode) {
                        nodePanel.classList.remove('active');
                    }
                }

                // Close node editor when clicking outside
                const editorOverlay = document.getElementById('nodeEditorOverlay');
                const editor = document.getElementById('nodeEditor');
                if (editorOverlay && editorOverlay.classList.contains('active') && !editor.contains(e.target)) {
                    if (window.NodeEditor) {
                        window.NodeEditor.closeEditor();
                    }
                }
            });
        }

        // Close context menu on click outside
        document.addEventListener('click', (e) => {
            const contextMenu = document.getElementById('contextMenu');
            if (contextMenu && !contextMenu.contains(e.target)) {
                contextMenu.classList.remove('active');
            }
        });

        // Close dropdowns on click outside
        document.addEventListener('click', (e) => {
            const userDropdown = document.getElementById('userDropdown');
            const userMenuBtn = document.getElementById('userMenuBtn');
            if (userDropdown && !userMenuBtn.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });
    },

    /**
     * Initialize keyboard shortcuts
     */
    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+K or Cmd+K - Focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('globalSearch');
                if (searchInput) searchInput.focus();
            }

            // Escape - Close modals/menus
            if (e.key === 'Escape') {
                this.closeAllModals();
            }

            // Delete - Delete selected node
            if (e.key === 'Delete' && this.selectedNodeId) {
                if (!e.target.matches('input, textarea')) {
                    this.deleteNode(this.selectedNodeId);
                }
            }
        });
    },

    /**
     * Initialize user menu
     */
    initUserMenu() {
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');

        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
        }
    },

    /**
     * Initialize search functionality
     */
    initSearch() {
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');

        if (!searchInput || !searchResults) return;

        searchInput.addEventListener('input', Utils.debounce(async (e) => {
            const query = e.target.value.trim();

            if (query.length < 2) {
                searchResults.classList.remove('active');
                searchResults.innerHTML = '';
                return;
            }

            try {
                const response = await API.search(query);
                this.renderSearchResults(response.data || []);
            } catch (error) {
                console.error('Search failed:', error);
            }
        }, 300));

        searchInput.addEventListener('blur', () => {
            setTimeout(() => {
                searchResults.classList.remove('active');
            }, 200);
        });
    },

    /**
     * Render search results
     */
    renderSearchResults(results) {
        const searchResults = document.getElementById('searchResults');
        if (!searchResults) return;

        if (results.length === 0) {
            searchResults.innerHTML = '<div class="search-empty">No results found</div>';
            searchResults.classList.add('active');
            return;
        }

        searchResults.innerHTML = results.map(node => `
            <div class="search-result-item" onclick="App.selectNode(${node.id})">
                <div class="result-icon">
                    <i class="fas ${Utils.escapeHtml(node.icon || 'fa-circle')}" style="color: ${node.color || '#6366f1'}"></i>
                </div>
                <div class="result-info">
                    <div class="result-title">${Utils.escapeHtml(node.title)}</div>
                    <div class="result-path">${Utils.truncate(node.content || '', 60)}</div>
                </div>
            </div>
        `).join('');

        searchResults.classList.add('active');
    },

    /**
     * Toggle sidebar
     */
    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) {
            sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        }
    },

    /**
     * Close all modals and menus
     */
    closeAllModals() {
        const contextMenu = document.getElementById('contextMenu');
        const userDropdown = document.getElementById('userDropdown');
        const searchResults = document.getElementById('searchResults');
        const nodePanel = document.getElementById('nodePanel');

        if (contextMenu) contextMenu.classList.remove('active');
        if (userDropdown) userDropdown.classList.remove('active');
        if (searchResults) searchResults.classList.remove('active');
        if (nodePanel) nodePanel.classList.remove('active');

        // Close sidebar on mobile
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
    },

    /**
     * Select a node
     */
    selectNode(nodeId) {
        this.selectedNodeId = nodeId;
        this.closeAllModals();

        // Close search
        const searchInput = document.getElementById('globalSearch');
        if (searchInput) searchInput.value = '';

        // Update URL
        Utils.setUrlParam('node', nodeId);

        // Trigger tree selection
        if (window.TreeVisualization) {
            window.TreeVisualization.selectNode(nodeId);
        }

        // Load node details in panel
        this.loadNodeDetails(nodeId);
    },

    /**
     * Load node details in side panel
     */
    async loadNodeDetails(nodeId) {
        const panel = document.getElementById('nodePanel');
        const panelBody = document.getElementById('panelBody');
        const panelTitle = document.getElementById('panelNodeTitle');

        if (!panel || !panelBody) return;

        panel.classList.add('active');
        panelBody.innerHTML = '<div class="panel-loading"><div class="spinner"></div></div>';

        try {
            const response = await API.getNode(nodeId);
            const node = response.data;

            if (panelTitle) {
                panelTitle.textContent = node.title;
            }

            const tags = node.tags ? node.tags.split(',').map(t => t.trim()).filter(Boolean) : [];

            panelBody.innerHTML = `
                <div class="node-detail-title">${Utils.escapeHtml(node.title)}</div>

                <div class="node-detail-meta">
                    <div class="node-meta-item">
                        <i class="fas fa-clock"></i>
                        <span>Updated ${Utils.formatDate(node.updated_at)}</span>
                    </div>
                    <div class="node-meta-item">
                        <i class="fas fa-font"></i>
                        <span>${Utils.wordCount(node.content)} words</span>
                    </div>
                    <div class="node-meta-item">
                        <i class="fas fa-hourglass-half"></i>
                        <span>${Utils.readingTime(node.content)}</span>
                    </div>
                </div>

                ${tags.length > 0 ? `
                    <div class="node-detail-tags">
                        ${tags.map(tag => `<span class="node-tag">${Utils.escapeHtml(tag)}</span>`).join('')}
                    </div>
                ` : ''}

                <div class="node-detail-content">
                    ${Utils.parseMarkdown(node.content) || '<p style="color: var(--text-muted); font-style: italic;">No content</p>'}
                </div>

                <div class="node-detail-actions">
                    <button class="btn btn-primary btn-sm" data-action="edit" data-node-id="${node.id}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-secondary btn-sm" data-action="addChild" data-node-id="${node.id}">
                        <i class="fas fa-plus"></i> Add Child
                    </button>
                    <button class="btn btn-secondary btn-sm" data-action="duplicate" data-node-id="${node.id}">
                        <i class="fas fa-copy"></i> Duplicate
                    </button>
                    <button class="btn btn-danger btn-sm" data-action="delete" data-node-id="${node.id}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            `;

            // Bind action buttons
            panelBody.querySelectorAll('.node-detail-actions .btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const action = btn.dataset.action;
                    const nodeId = parseInt(btn.dataset.nodeId);
                    this.handlePanelAction(action, nodeId);
                });
            });
        } catch (error) {
            panelBody.innerHTML = `
                <div class="panel-loading">
                    <p style="color: var(--error);">Failed to load node details</p>
                </div>
            `;
        }
    },

    /**
     * Create root node
     */
    createRootNode(event = null) {
        event?.preventDefault();
        event?.stopPropagation();

        if (window.NodeEditor) {
            window.NodeEditor.openEditor(null, null);
        }
    },

    /**
     * Create child node
     */
    createChildNode(parentId, event = null) {
        event?.preventDefault();
        event?.stopPropagation();

        if (window.NodeEditor) {
            window.NodeEditor.openEditor(null, parentId);
        }
    },

    /**
     * Refresh the dashboard tree and sidebar without a full page reload
     */
    async refreshTreeView(selectedNodeId = null) {
        let treeData = [];

        if (window.TreeVisualization) {
            treeData = await window.TreeVisualization.refresh() || [];
        }

        this.renderSidebarTree(treeData);

        if (selectedNodeId !== null && selectedNodeId !== undefined) {
            this.selectNode(selectedNodeId);
        }
    },

    /**
     * Render sidebar tree from live data
     */
    renderSidebarTree(nodes) {
        const sidebar = document.getElementById('treeSidebar');
        if (!sidebar) return;

        if (!Array.isArray(nodes) || nodes.length === 0) {
            sidebar.innerHTML = `
                <div class="sidebar-empty">
                    <p>No nodes</p>
                    <button class="btn btn-sm btn-primary" onclick="createRootNode(event)">
                        Create Node
                    </button>
                </div>
            `;
            this.updateSidebarCount(0);
            return;
        }

        sidebar.innerHTML = this.buildSidebarTree(nodes);
        this.updateSidebarCount(this.countTreeNodes(nodes));
    },

    /**
     * Build nested sidebar tree markup
     */
    buildSidebarTree(nodes, depth = 0) {
        if (!Array.isArray(nodes) || nodes.length === 0) return '';

        let html = `<ul class="tree-list${depth > 0 ? ' tree-nested' : ''}">`;

        nodes.forEach(node => {
            const hasChildren = Array.isArray(node.children) && node.children.length > 0;
            const isCollapsed = Boolean(node.is_collapsed);

            html += '<li class="tree-item">';
            html += `<div class="tree-node-row" data-node-id="${node.id}">`;

            if (hasChildren) {
                html += `
                    <button class="tree-toggle${isCollapsed ? ' collapsed' : ''}" onclick="toggleTreeNode(this, ${node.id})">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                `;
            } else {
                html += '<span class="tree-spacer"></span>';
            }

            html += `
                <a href="#" class="tree-label" onclick="selectNode(${node.id}); return false;">
                    <i class="fas ${Utils.escapeHtml(node.icon || 'fa-circle')}" style="color: ${Utils.escapeHtml(node.color || '#6366f1')}"></i>
                    <span>${Utils.escapeHtml(node.title)}</span>
                </a>
            `;

            html += '</div>';

            if (hasChildren && !isCollapsed) {
                html += this.buildSidebarTree(node.children, depth + 1);
            }

            html += '</li>';
        });

        html += '</ul>';
        return html;
    },

    /**
     * Count nodes for the sidebar footer
     */
    countTreeNodes(nodes) {
        if (!Array.isArray(nodes) || nodes.length === 0) return 0;

        return nodes.reduce((count, node) => {
            return count + 1 + this.countTreeNodes(node.children || []);
        }, 0);
    },

    /**
     * Update the sidebar node count
     */
    updateSidebarCount(count) {
        const sidebarCount = document.querySelector('.sidebar-footer span');
        if (sidebarCount) {
            sidebarCount.textContent = `${count} nodes`;
        }
    },

    /**
     * Delete node with confirmation
     */
    async deleteNode(nodeId) {
        if (!confirm('Are you sure you want to delete this node and all its children?')) {
            return;
        }

        try {
            await API.deleteNode(nodeId);
            this.showToast('Node deleted successfully', 'success');

            // Close panel if viewing this node
            const panel = document.getElementById('nodePanel');
            if (panel) panel.classList.remove('active');

            if (this.selectedNodeId === nodeId) {
                this.selectedNodeId = null;
            }

            await this.refreshTreeView();
        } catch (error) {
            this.showToast('Failed to delete node', 'error');
        }
    },

    /**
     * Duplicate node
     */
    async duplicateNode(nodeId) {
        try {
            const response = await API.duplicateNode(nodeId);
            const duplicatedNode = response.data;
            this.showToast('Node duplicated successfully', 'success');

            await this.refreshTreeView(duplicatedNode?.id ?? null);
        } catch (error) {
            this.showToast('Failed to duplicate node', 'error');
        }
    },

    /**
     * Handle panel action buttons
     */
    handlePanelAction(action, nodeId) {
        switch (action) {
            case 'edit':
                if (window.NodeEditor) {
                    window.NodeEditor.editNode(nodeId);
                }
                break;
            case 'addChild':
                if (window.NodeEditor) {
                    window.NodeEditor.openEditor(null, nodeId);
                }
                break;
            case 'duplicate':
                this.duplicateNode(nodeId);
                break;
            case 'delete':
                this.deleteNode(nodeId);
                break;
        }
    },

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            info: 'fa-info-circle'
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info} toast-icon"></i>
            <span class="toast-message">${Utils.escapeHtml(message)}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        container.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'fadeIn 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    },

    /**
     * Expand all nodes
     */
    expandAllNodes() {
        if (window.TreeVisualization) {
            window.TreeVisualization.expandAll();
        }
    },

    /**
     * Collapse all nodes
     */
    collapseAllNodes() {
        if (window.TreeVisualization) {
            window.TreeVisualization.collapseAll();
        }
    },

    /**
     * Reset tree view
     */
    resetView() {
        if (window.TreeVisualization) {
            window.TreeVisualization.resetView();
        }
    }
};

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        App.init();
    });
} else {
    App.init();
}

// Make App globally available
window.App = App;

// Global function wrappers for onclick handlers
function createRootNode(event) {
    App.createRootNode(event);
}

function createChildNode(parentId, event) {
    App.createChildNode(parentId, event);
}

function expandAllNodes() {
    App.expandAllNodes();
}

function collapseAllNodes() {
    App.collapseAllNodes();
}

function resetView() {
    App.resetView();
}

function selectNode(nodeId) {
    App.selectNode(nodeId);
}

function toggleTreeNode(el, nodeId) {
    if (!el) return;

    const treeItem = el.closest('.tree-item');
    const childList = treeItem
        ? Array.from(treeItem.children).find((child) => child.classList && child.classList.contains('tree-list'))
        : null;
    const shouldCollapse = !el.classList.contains('collapsed');

    el.classList.toggle('collapsed', shouldCollapse);
    el.setAttribute('aria-expanded', String(!shouldCollapse));

    if (childList) {
        childList.hidden = shouldCollapse;
        childList.style.display = shouldCollapse ? 'none' : '';
    }

    if (window.TreeVisualization) {
        window.TreeVisualization.toggleNodeById(nodeId);
    }
}
