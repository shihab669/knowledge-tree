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
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebar && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Add root node buttons
        const addRootNode = document.getElementById('addRootNode');
        const addRootNodeSidebar = document.getElementById('addRootNodeSidebar');
        if (addRootNode) addRootNode.addEventListener('click', () => this.createRootNode());
        if (addRootNodeSidebar) addRootNodeSidebar.addEventListener('click', () => this.createRootNode());

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
        if (sidebar) {
            sidebar.classList.toggle('active');
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
                    <button class="btn btn-primary btn-sm" onclick="NodeEditor.editNode(${node.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="App.createChildNode(${node.id})">
                        <i class="fas fa-plus"></i> Add Child
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="App.duplicateNode(${node.id})">
                        <i class="fas fa-copy"></i> Duplicate
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="App.deleteNode(${node.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            `;
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
    createRootNode() {
        if (window.NodeEditor) {
            window.NodeEditor.openEditor(null, null);
        }
    },

    /**
     * Create child node
     */
    createChildNode(parentId) {
        if (window.NodeEditor) {
            window.NodeEditor.openEditor(null, parentId);
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

            // Refresh tree
            if (window.TreeVisualization) {
                window.TreeVisualization.refresh();
            }

            // Reload page to update sidebar
            window.location.reload();
        } catch (error) {
            this.showToast('Failed to delete node', 'error');
        }
    },

    /**
     * Duplicate node
     */
    async duplicateNode(nodeId) {
        try {
            await API.duplicateNode(nodeId);
            this.showToast('Node duplicated successfully', 'success');

            // Refresh tree
            if (window.TreeVisualization) {
                window.TreeVisualization.refresh();
            }

            window.location.reload();
        } catch (error) {
            this.showToast('Failed to duplicate node', 'error');
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
document.addEventListener('DOMContentLoaded', () => {
    App.init();
});

// Make App globally available
window.App = App;
