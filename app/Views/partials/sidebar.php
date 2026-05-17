<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Explorer</h3>
        <button class="btn-icon btn-sm" id="addRootNodeSidebar" title="Add root node">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <div class="sidebar-content">
        <div class="sidebar-section">
            <div class="sidebar-section-header">
                <i class="fas fa-clock"></i>
                <span>Recent</span>
            </div>
            <div class="recent-nodes" id="recentNodes">
                <?php if (!empty($recentNodes)): ?>
                    <?php foreach ($recentNodes as $recent): ?>
                        <a href="#" class="sidebar-node" data-node-id="<?= $recent['id'] ?>" onclick="selectNode(<?= $recent['id'] ?>); return false;">
                            <i class="fas fa-circle" style="color: <?= htmlspecialchars($recent['color'] ?? '#6366f1') ?>"></i>
                            <span><?= htmlspecialchars($recent['title']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="sidebar-empty">No recent nodes</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-header">
                <i class="fas fa-project-diagram"></i>
                <span>Tree Structure</span>
            </div>
            <div class="tree-sidebar" id="treeSidebar">
                <?php if (!empty($tree)): ?>
                    <?= renderSidebarTree($tree) ?>
                <?php else: ?>
                    <div class="sidebar-empty">
                        <p>No nodes yet</p>
                        <button class="btn btn-sm btn-primary" onclick="createRootNode()">
                            <i class="fas fa-plus"></i> Create First Node
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-stats">
            <span><i class="fas fa-circle-nodes"></i> <?= $totalNodes ?? 0 ?> nodes</span>
        </div>
    </div>
</aside>

<?php
function renderSidebarTree(array $nodes, int $depth = 0): string {
    $html = '<ul class="tree-list' . ($depth > 0 ? ' tree-nested' : '') . '">';

    foreach ($nodes as $node) {
        $hasChildren = !empty($node['children']);
        $isCollapsed = $node['is_collapsed'] ?? false;

        $html .= '<li class="tree-item">';
        $html .= '<div class="tree-node-row" data-node-id="' . $node['id'] . '">';

        if ($hasChildren) {
            $html .= '<button class="tree-toggle' . ($isCollapsed ? ' collapsed' : '') . '" onclick="toggleTreeNode(this, ' . $node['id'] . ')">';
            $html .= '<i class="fas fa-chevron-down"></i>';
            $html .= '</button>';
        } else {
            $html .= '<span class="tree-spacer"></span>';
        }

        $html .= '<a href="#" class="tree-label" onclick="selectNode(' . $node['id'] . '); return false;">';
        $html .= '<i class="fas ' . htmlspecialchars($node['icon'] ?? 'fa-circle') . '" style="color: ' . htmlspecialchars($node['color'] ?? '#6366f1') . '"></i>';
        $html .= '<span>' . htmlspecialchars($node['title']) . '</span>';
        $html .= '</a>';
        $html .= '</div>';

        if ($hasChildren && !$isCollapsed) {
            $html .= renderSidebarTree($node['children'], $depth + 1);
        }

        $html .= '</li>';
    }

    $html .= '</ul>';
    return $html;
}
?>
