<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <div class="sidebar-section">
            <div class="tree-sidebar" id="treeSidebar">
                <?php if (!empty($tree)): ?>
                    <?= renderSidebarTree($tree) ?>
                <?php else: ?>
                    <div class="sidebar-empty">
                        <p>No nodes</p>
                        <button class="btn btn-sm btn-primary" onclick="createRootNode(event)">
                            Create Node
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <span><?= $totalNodes ?? 0 ?> nodes</span>
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
