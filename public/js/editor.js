/**
 * Knowledge Tree - Node Editor
 */

const NodeEditor = {
    isOpen: false,
    currentNodeId: null,
    parentNodeId: null,
    isEditing: false,

    init() {
        this.bindEvents();
    },

    bindEvents() {
        const closeBtn = document.getElementById('editorClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.closeEditor();
            });
        }

        const cancelBtn = document.getElementById('editorCancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.closeEditor();
            });
        }

        const saveBtn = document.getElementById('editorSave');
        if (saveBtn) {
            saveBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.saveNode();
            });
        }

        const overlay = document.getElementById('nodeEditorOverlay');
        if (overlay) {
            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) {
                    this.closeEditor();
                }
            });
        }

        const colorPresets = document.querySelectorAll('.color-preset');
        colorPresets.forEach((preset) => {
            preset.addEventListener('click', () => {
                const color = preset.dataset.color;
                const colorInput = document.getElementById('nodeColor');
                if (colorInput && color) {
                    colorInput.value = color;
                }
                colorPresets.forEach((item) => item.classList.remove('active'));
                preset.classList.add('active');
            });
        });

        const iconOptions = document.querySelectorAll('.icon-option');
        iconOptions.forEach((option) => {
            option.addEventListener('click', () => {
                const icon = option.dataset.icon;
                const iconInput = document.getElementById('nodeIcon');
                if (iconInput && icon) {
                    iconInput.value = icon;
                }
                iconOptions.forEach((item) => item.classList.remove('active'));
                option.classList.add('active');
            });
        });

        const toggleMarkdown = document.getElementById('toggleMarkdown');
        if (toggleMarkdown) {
            toggleMarkdown.addEventListener('click', () => this.toggleMarkdownPreview());
        }

        const toolbarBtns = document.querySelectorAll('.toolbar-btn[data-action]');
        toolbarBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                this.handleToolbarAction(btn.dataset.action);
            });
        });

        document.addEventListener('keydown', (event) => {
            if (!this.isOpen) return;

            if ((event.ctrlKey || event.metaKey) && event.key === 's') {
                event.preventDefault();
                this.saveNode();
            }

            if (event.key === 'Escape') {
                this.closeEditor();
            }
        });
    },

    openEditor(nodeId = null, parentId = null) {
        this.currentNodeId = nodeId;
        this.parentNodeId = parentId;
        this.isEditing = nodeId !== null;

        const overlay = document.getElementById('nodeEditorOverlay');
        const title = document.getElementById('editorTitle');

        if (title) {
            title.textContent = this.isEditing ? 'Edit Node' : 'New Node';
        }

        this.resetForm();

        if (this.isEditing) {
            this.loadNodeData(nodeId);
        }

        if (overlay) {
            overlay.classList.add('active');
            this.isOpen = true;
        }

        setTimeout(() => {
            const titleInput = document.getElementById('nodeTitle');
            if (titleInput) titleInput.focus();
        }, 100);
    },

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

    resetForm() {
        const form = document.getElementById('nodeForm');
        if (form) form.reset();

        const nodeId = document.getElementById('nodeId');
        const nodeColor = document.getElementById('nodeColor');
        const nodeIcon = document.getElementById('nodeIcon');

        if (nodeId) nodeId.value = '';
        if (nodeColor) nodeColor.value = '#6366f1';
        if (nodeIcon) nodeIcon.value = 'fa-circle';

        const colorPresets = document.querySelectorAll('.color-preset');
        colorPresets.forEach((item) => item.classList.remove('active'));
        if (colorPresets[0]) colorPresets[0].classList.add('active');

        const iconOptions = document.querySelectorAll('.icon-option');
        iconOptions.forEach((item) => item.classList.remove('active'));
        if (iconOptions[0]) iconOptions[0].classList.add('active');

        const preview = document.getElementById('contentPreview');
        const content = document.getElementById('nodeContent');
        if (preview) preview.style.display = 'none';
        if (content) content.style.display = 'block';
    },

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

            const colorPresets = document.querySelectorAll('.color-preset');
            colorPresets.forEach((preset) => {
                preset.classList.toggle('active', preset.dataset.color === node.color);
            });

            const iconOptions = document.querySelectorAll('.icon-option');
            iconOptions.forEach((option) => {
                option.classList.toggle('active', option.dataset.icon === node.icon);
            });
        } catch (error) {
            console.error('Failed to load node:', error);
            if (window.App) {
                window.App.showToast('Failed to load node data', 'error');
            }
        }
    },

    async saveNode() {
        const titleInput = document.getElementById('nodeTitle');
        const contentInput = document.getElementById('nodeContent');
        const tagsInput = document.getElementById('nodeTags');
        const colorInput = document.getElementById('nodeColor');
        const iconInput = document.getElementById('nodeIcon');

        const title = titleInput?.value?.trim();
        if (!title) {
            if (window.App) {
                window.App.showToast('Title is required', 'error');
            }
            titleInput?.focus();
            return;
        }

        const data = {
            title,
            content: contentInput?.value || '',
            tags: tagsInput?.value || '',
            color: colorInput?.value || '#6366f1',
            icon: iconInput?.value || 'fa-circle'
        };

        try {
            let response;

            if (this.isEditing) {
                response = await API.updateNode(this.currentNodeId, data);
                if (window.App) {
                    window.App.showToast('Node updated successfully', 'success');
                }
            } else {
                data.parent_id = this.parentNodeId;
                response = await API.createNode(data);
                if (window.App) {
                    window.App.showToast('Node created successfully', 'success');
                }
            }

            const savedNode = response?.data;
            this.closeEditor();

            if (window.App && typeof window.App.refreshTreeView === 'function') {
                await window.App.refreshTreeView(savedNode?.id ?? null);
            }
        } catch (error) {
            console.error('Failed to save node:', error);
            if (window.App) {
                window.App.showToast('Failed to save node', 'error');
            }
        }
    },

    editNode(nodeId) {
        this.openEditor(nodeId);
    },

    toggleMarkdownPreview() {
        const content = document.getElementById('nodeContent');
        const preview = document.getElementById('contentPreview');
        const toggleBtn = document.getElementById('toggleMarkdown');

        if (!content || !preview) return;

        if (preview.style.display === 'none') {
            preview.innerHTML = Utils.parseMarkdown(content.value);
            preview.style.display = 'block';
            content.style.display = 'none';
            if (toggleBtn) toggleBtn.classList.add('active');
        } else {
            preview.style.display = 'none';
            content.style.display = 'block';
            if (toggleBtn) toggleBtn.classList.remove('active');
        }
    },

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

        content.value = content.value.substring(0, start) + replacement + content.value.substring(end);

        const newPos = start + replacement.length + cursorOffset;
        content.setSelectionRange(newPos, newPos);
        content.focus();
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        NodeEditor.init();
    });
} else {
    NodeEditor.init();
}

window.NodeEditor = NodeEditor;
