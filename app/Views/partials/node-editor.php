<div class="node-editor-overlay" id="nodeEditorOverlay">
    <div class="node-editor" id="nodeEditor">
        <div class="editor-header">
            <div class="editor-title">
                <span id="editorTitle">Edit Node</span>
            </div>
            <button class="btn-icon btn-sm" id="editorClose" title="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="editor-body">
            <form id="nodeForm">
                <input type="hidden" id="nodeId" name="id">

                <div class="form-group">
                    <label for="nodeTitle">Title</label>
                    <input
                        type="text"
                        id="nodeTitle"
                        name="title"
                        placeholder="Enter node title"
                        required
                        maxlength="255"
                    >
                </div>

                <div class="form-group">
                    <label for="nodeContent">Content</label>
                    <textarea
                        id="nodeContent"
                        name="content"
                        placeholder="Write your content here..."
                        rows="8"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="nodeColor">Color</label>
                    <div class="color-picker">
                        <input type="color" id="nodeColor" name="color" value="#6366f1">
                        <div class="color-presets">
                            <button type="button" class="color-preset active" data-color="#6366f1" style="background: #6366f1"></button>
                            <button type="button" class="color-preset" data-color="#8b5cf6" style="background: #8b5cf6"></button>
                            <button type="button" class="color-preset" data-color="#ec4899" style="background: #ec4899"></button>
                            <button type="button" class="color-preset" data-color="#14b8a6" style="background: #14b8a6"></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="editor-footer">
            <button class="btn btn-secondary" id="editorCancel">Cancel</button>
            <button class="btn btn-primary" id="editorSave">Save</button>
        </div>
    </div>
</div>
