/**
 * Knowledge Tree - Node Editor
 */

const NodeEditor = {
    // State
    isOpen: false,
    currentNodeId: null,
    parentNodeId: null,
    isEditing: false,

    /**
     * Initialize editor
     */
    init() {
        this.bindEvents();
    },

    /**
     * Bind editor events
     */
    bindEvents() {
        // Close button
        const closeBtn = document.getElementById('editorClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeEditor());
        }

        // Cancel button
        const cancelBtn = document.getElementById('editorCancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.closeEditor());
        }

        // Save button
        const saveBtn = document.getElementById('editorSave');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveNode());
        }

        // Overlay click to close
        const overlay = document.getElementById('nodeEditorOverlay');
        if (overlay) {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    this.closeEditor();
                }
            });
        }

        // Color presets
        const colorPresets = document.querySelectorAll('.color-preset');
        colorPresets.forEach(preset => {
            preset.addEventListener('click', () => {
                const color = preset.dataset.color;
                const colorInput = document.getElementById('nodeColor');
                if (colorInput) {
                    colorInput.value = color;
                }
                colorPresets.forEach(p => p.classList.remove('active'));
                preset.classList.add('active');
            });
        });

        // Icon picker
        const iconOptions = document.querySelectorAll('.icon-option');
        iconOptions.forEach(option => {
            option.addEventListener('click', () => {
                const icon = option.dataset.icon;
                const iconInput = document.getElementById('nodeIcon');
                if (iconInput) {
                    iconInput.value = icon;
                }
                iconOptions.forEach(o => o.classList.remove('active'));
                option.classList.add('active');
            });
        });

        // Toggle markdown preview
        const toggleMarkdown = document.getElementById('toggleMarkdown');
        if (toggleMarkdown) {
            toggleMarkdown.addEventListener('click', () => this.toggleMarkdownPreview());
        }

        // Toolbar buttons
        const toolbarBtns = document.querySelectorAll('.toolbar-btn[data-action]');
        toolbarBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                this.handleToolbarAction(btn.dataset.action);
            });
        });

        // Keyboard shortcuts in editor
        document.addEventListener('keydown', (e) => {
            if (!this.isOpen) return;

            // Ctrl+S or Cmd+S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.saveNode();
            }

            // Escape to close
            if (e.key === 'Escape') {
                this.closeEditor();
            }
        });
    },

    /**
     * Open editor for new node
     */
    openEditor(nodeId = null, parentId = null) {
        this.currentNodeId = nodeId;
        this.parentNodeId = parentId;
        this.isEditing = nodeId !== null;

        const overlay = document.getElementById('nodeEditorOverlay');
        const title = document.getElementById('editorTitle');

        if (title) {
            title.textContent = this.isEditing ? 'Edit Node' : 'New Node';
        }

        // Reset form
        this.resetForm();

        // If editing, load node data
        if (this.isEditing) {
            this.loadNodeData(nodeId);
        }

        // Show editor
        if (overlay) {
            overlay.classList.add('active');
            this.isOpen = true;
        }

        // Focus title input
        setTimeout(() => {
            const titleInput = document.getElementById('nodeTitle');
            if (titleInput) titleInput.focus();
        }, 100);
    },

    /**
     * Close editor
     */
    closeEditor() {
        const overlay = document.getElementById('nodeEditorOverlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
        this.isOpen = false;
        this.currentNodeId = null;
        this.parentNodeId = null;
        this.isEditing = false;
    },

    /**
     * Reset form to defaults
     */
    resetForm() {
        const form = document.getElementById('nodeForm');
        if (form) form.reset();

        // Reset hidden fields
        const nodeId = document.getElementById('nodeId');
        const nodeColor = document.getElementById('nodeColor');
        const nodeIcon = document.getElementById('nodeIcon');

        if (nodeId) nodeId.value = '';
        if (nodeColor) nodeColor.value = '#6366f1';
        if (nodeIcon) nodeIcon.value = 'fa-circle';

        // Reset color presets
        const colorPresets = document.querySelectorAll('.color-preset');
        colorPresets.forEach(p => p.classList.remove('active'));
        if (colorPresets[0]) colorPresets[0].classList.add('active');

        // Reset icon options
        const iconOptions = document.querySelectorAll('.icon-option');
        iconOptions.forEach(o => o.classList.remove('active'));
        if (iconOptions[0]) iconOptions[0].classList.add('active');

        // Hide preview
        const preview = document.getElementById('contentPreview');
        const content = document.getElementById('nodeContent');
        if (preview) preview.style.display = 'none';
        if (content) content.style.display = 'block';
    },

    /**
     * Load node data for editing
     */
    async loadNodeData(nodeId) {
        try {
            const response = await API.getNode(nodeId);
            const node = response.data;

            const titleInput = document.getElementById('nodeTitle');
            const contentInput = document.getElementById('nodeContent');
            const tagsInput = document.getElementById('nodeTags');
            const colorInput = document.getElementById('nodeColor');
            const iconInput = document.getElementById('nodeIcon');

            if (titleInput) titleInput.value = node.title || '';
            if (contentInput) contentInput.value = node.content || '';
            if (tagsInput) tagsInput.value = node.tags || '';
            if (colorInput) colorInput.value = node.color || '#6366f1';
            if (iconInput) iconInput.value = node.icon || 'fa-circle';

            // Update color preset selection
            const colorPresets = document.querySelectorAll('.color-preset');
            colorPresets.forEach(p => {
                p.classList.toggle('active', p.dataset.color === node.color);
            });

            // Update icon selection
            const iconOptions = document.querySelectorAll('.icon-option');
            iconOptions.forEach(o => {
                o.classList.toggle('active', o.dataset.icon === node.icon);
            });

        } catch (error) {
            console.error('Failed to load node:', error);
            App.showToast('Failed to load node data', 'error');
        }
    },

    /**
     * Save node (create or update)
     */
    async saveNode() {
        const titleInput = document.getElementById('nodeTitle');
        const contentInput = document.getElementById('nodeContent');
        const tagsInput = document.getElementById('nodeTags');
        const colorInput = document.getElementById('nodeColor');
        const iconInput = document.getElementById('nodeIcon');

        const title = titleInput?.value?.trim();
        if (!title) {
            App.showToast('Title is required', 'error');
            titleInput?.focus();
            return;
        }

        const data = {
            title: title,
            content: contentInput?.value || '',
            tags: tagsInput?.value || '',
            color: colorInput?.value || '#6366f1',
            icon: iconInput?.value || 'fa-circle'
        };

        try {
            if (this.isEditing) {
                await API.updateNode(this.currentNodeId, data);
                App.showToast('Node updated successfully', 'success');
            } else {
                data.parent_id = this.parentNodeId;
                await API.createNode(data);
                App.showToast('Node created successfully', 'success');
            }

            this.closeEditor();

            // Refresh tree and page
            window.location.reload();

        } catch (error) {
            console.error('Failed to save node:', error);
            App.showToast('Failed to save node', 'error');
        }
    },

    /**
     * Edit existing node
     */
    editNode(nodeId) {
        this.openEditor(nodeId);
    },

    /**
     * Toggle markdown preview
     */
    toggleMarkdownPreview() {
        const content = document.getElementById('nodeContent');
        const preview = document.getElementById('contentPreview');
        const toggleBtn = document.getElementById('toggleMarkdown');

        if (!content || !preview) return;

        if (preview.style.display === 'none') {
            // Show preview
            preview.innerHTML = Utils.parseMarkdown(content.value);
            preview.style.display = 'block';
            content.style.display = 'none';
            if (toggleBtn) toggleBtn.classList.add('active');
        } else {
            // Hide preview
            preview.style.display = 'none';
            content.style.display = 'block';
            if (toggleBtn) toggleBtn.classList.remove('active');
        }
    },

    /**
     * Handle toolbar actions
     */
    handleToolbarAction(action) {
        const content = document.getElementById('nodeContent');
        if (!content) return;

        const start = content.selectionStart;
        const end = content.selectionEnd;
        const selectedText = content.value.substring(start, end);

        let replacement = '';
        let cursorOffset = 0;

        switch (action) {
            case 'bold':
                replacement = `**${selectedText || 'bold text'}**`;
                cursorOffset = selectedText ? 0 : -2;
                break;
            case 'italic':
                replacement = `*${selectedText || 'italic text'}*`;
                cursorOffset = selectedText ? 0 : -1;
                break;
            case 'heading':
                replacement = `## ${selectedText || 'Heading'}`;
                cursorOffset = 0;
                break;
            case 'link':
                replacement = `[${selectedText || 'link text'}](url)`;
                cursorOffset = selectedText ? -1 : -4;
                break;
            case 'list':
                replacement = `- ${selectedText || 'list item'}`;
                cursorOffset = 0;
                break;
            case 'code':
                if (selectedText.includes('\n')) {
                    replacement = `\`\`\`\n${selectedText}\n\`\`\``;
                } else {
                    replacement = `\`${selectedText || 'code'}\``;
                }
                cursorOffset = selectedText ? 0 : -1;
                break;
            default:
                return;
        }

        // Insert text
        content.value = content.value.substring(0, start) + replacement + content.value.substring(end);

        // Set cursor position
        const newPos = start + replacement.length + cursorOffset;
        content.setSelectionRange(newPos, newPos);
        content.focus();
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    NodeEditor.init();
});

// Make NodeEditor globally available
window.NodeEditor = NodeEditor;
