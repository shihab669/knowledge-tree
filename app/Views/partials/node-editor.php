<div class="node-editor-overlay" id="nodeEditorOverlay">
    <div class="node-editor" id="nodeEditor">
        <div class="editor-header">
            <div class="editor-title">
                <i class="fas fa-edit"></i>
                <span id="editorTitle">Edit Node</span>
            </div>
            <div class="editor-actions">
                <button class="btn-icon btn-sm" id="editorClose" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="editor-body">
            <form id="nodeForm">
                <input type="hidden" id="nodeId" name="id">

                <div class="form-group">
                    <label for="nodeTitle">
                        <i class="fas fa-heading"></i>
                        Title
                    </label>
                    <input
                        type="text"
                        id="nodeTitle"
                        name="title"
                        placeholder="Node title"
                        required
                        maxlength="255"
                    >
                </div>

                <div class="form-group">
                    <label for="nodeContent">
                        <i class="fas fa-align-left"></i>
                        Content
                    </label>
                    <div class="editor-toolbar">
                        <button type="button" class="toolbar-btn" data-action="bold" title="Bold">
                            <i class="fas fa-bold"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="italic" title="Italic">
                            <i class="fas fa-italic"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="heading" title="Heading">
                            <i class="fas fa-heading"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="link" title="Link">
                            <i class="fas fa-link"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="list" title="List">
                            <i class="fas fa-list-ul"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="code" title="Code">
                            <i class="fas fa-code"></i>
                        </button>
                        <span class="toolbar-divider"></span>
                        <button type="button" class="toolbar-btn" id="toggleMarkdown" title="Toggle Markdown">
                            <i class="fab fa-markdown"></i>
                        </button>
                    </div>
                    <textarea
                        id="nodeContent"
                        name="content"
                        placeholder="Write your content here... (supports Markdown)"
                        rows="10"
                    ></textarea>
                    <div class="content-preview" id="contentPreview" style="display: none;"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nodeTags">
                            <i class="fas fa-tags"></i>
                            Tags
                        </label>
                        <input
                            type="text"
                            id="nodeTags"
                            name="tags"
                            placeholder="tag1, tag2, tag3"
                        >
                        <small>Separate with commas</small>
                    </div>

                    <div class="form-group">
                        <label for="nodeColor">
                            <i class="fas fa-palette"></i>
                            Color
                        </label>
                        <div class="color-picker">
                            <input
                                type="color"
                                id="nodeColor"
                                name="color"
                                value="#6366f1"
                            >
                            <div class="color-presets">
                                <button type="button" class="color-preset" data-color="#6366f1" style="background: #6366f1"></button>
                                <button type="button" class="color-preset" data-color="#ec4899" style="background: #ec4899"></button>
                                <button type="button" class="color-preset" data-color="#14b8a6" style="background: #14b8a6"></button>
                                <button type="button" class="color-preset" data-color="#f59e0b" style="background: #f59e0b"></button>
                                <button type="button" class="color-preset" data-color="#ef4444" style="background: #ef4444"></button>
                                <button type="button" class="color-preset" data-color="#8b5cf6" style="background: #8b5cf6"></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-icons"></i>
                        Icon
                    </label>
                    <div class="icon-picker" id="iconPicker">
                        <button type="button" class="icon-option active" data-icon="fa-circle">
                            <i class="fas fa-circle"></i>
                        </button>
                        <button type="button" class="icon-option" data-icon="fa-star">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="icon-option" data-icon="fa-lightbulb">
                            <i class="fas fa-lightbulb"></i>
                        </button>
                        <button type="button" class="icon-option" data-icon="fa-book">
                            <i class="fas fa-book"></i>
                        </button>
                        <button type="button" class="icon-option" data-icon="fa-code">
                            <i class="fas fa-code"></i>
                        </button>
                        <button type="button" class="icon-option" data-icon="fa-database">
                            <i class="fas fa-database"></i>
                        </button>
                        <button type="button" class="icon-option" data-icon="fa-folder">
                            <i class="fas fa-folder"></i>
                        </button>
                        <button type="button" class="icon-option" data-icon="fa-tag">
                            <i class="fas fa-tag"></i>
                        </button>
                        <button type="button" class="icon-option" data-icon="fa-bolt">
                            <i class="fas fa-bolt"></i>
                        </button>
                        <button type="button" class="icon-option" data-icon="fa-puzzle-piece">
                            <i class="fas fa-puzzle-piece"></i>
                        </button>
                    </div>
                    <input type="hidden" id="nodeIcon" name="icon" value="fa-circle">
                </div>
            </form>
        </div>

        <div class="editor-footer">
            <button class="btn btn-secondary" id="editorCancel">
                <i class="fas fa-times"></i>
                Cancel
            </button>
            <button class="btn btn-primary" id="editorSave">
                <i class="fas fa-save"></i>
                Save Node
            </button>
        </div>
    </div>
</div>
