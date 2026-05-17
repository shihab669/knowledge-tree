/**
 * Knowledge Tree - D3.js Tree Visualization
 */

const TreeVisualization = {
    // D3 selections
    svg: null,
    g: null,
    zoom: null,
    treeLayout: null,

    // Configuration
    config: {
        nodeRadius: 24,
        nodeSpacing: { x: 200, y: 80 },
        duration: 500,
        zoomExtent: [0.1, 4]
    },

    // State
    treeData: null,
    root: null,
    selectedNode: null,
    nodeIdCounter: 0,

    /**
     * Initialize tree visualization
     */
    init() {
        const container = document.getElementById('treeViewport');
        const svgElement = document.getElementById('treeSvg');

        if (!container || !svgElement) return;

        // Setup SVG
        this.svg = d3.select(svgElement);
        this.g = this.svg.append('g').attr('class', 'tree-group');

        // Setup zoom
        this.zoom = d3.zoom()
            .scaleExtent(this.config.zoomExtent)
            .on('zoom', (event) => {
                this.g.attr('transform', event.transform);
                this.updateZoomLevel(event.transform.k);
            });

        this.svg.call(this.zoom);

        // Setup tree layout
        this.treeLayout = d3.tree()
            .nodeSize([this.config.nodeSpacing.x, this.config.nodeSpacing.y])
            .separation((a, b) => a.parent === b.parent ? 1 : 1.2);

        // Bind zoom controls
        this.bindControls();

        // Load initial data
        this.loadData();

        // Handle window resize
        window.addEventListener('resize', Utils.debounce(() => {
            this.centerTree();
        }, 300));
    },

    /**
     * Bind zoom and view controls
     */
    bindControls() {
        const zoomIn = document.getElementById('zoomIn');
        const zoomOut = document.getElementById('zoomOut');
        const zoomReset = document.getElementById('zoomReset');

        if (zoomIn) {
            zoomIn.addEventListener('click', () => {
                this.svg.transition().duration(300).call(this.zoom.scaleBy, 1.3);
            });
        }

        if (zoomOut) {
            zoomOut.addEventListener('click', () => {
                this.svg.transition().duration(300).call(this.zoom.scaleBy, 0.7);
            });
        }

        if (zoomReset) {
            zoomReset.addEventListener('click', () => {
                this.resetView();
            });
        }
    },

    /**
     * Load tree data from API or initial data
     */
    async loadData() {
        try {
            // Use initial data if available
            if (window.APP_DATA && window.APP_DATA.tree) {
                this.treeData = window.APP_DATA.tree;
            } else {
                const response = await API.getTree();
                this.treeData = response.data || [];
            }

            if (this.treeData.length === 0) {
                this.showEmptyState();
                return;
            }

            this.hideEmptyState();
            this.renderTree();
        } catch (error) {
            console.error('Failed to load tree data:', error);
        }
    },

    /**
     * Render the tree
     */
    renderTree() {
        if (!this.treeData || this.treeData.length === 0) return;

        // Create root hierarchy
        this.root = d3.hierarchy(this.treeData[0] || { title: 'Root', children: this.treeData });

        // Initial collapse for deep nodes
        this.root.descendants().forEach((d, i) => {
            if (d.depth > 2 && d.children) {
                d._children = d.children;
                d.children = null;
            }
        });

        this.update(this.root);

        // Center tree after initial render
        setTimeout(() => this.centerTree(), 100);
    },

    /**
     * Update tree with transitions
     */
    update(source) {
        const treeData = this.treeLayout(this.root);
        const nodes = treeData.descendants();
        const links = treeData.links();

        // Normalize for fixed-depth
        nodes.forEach(d => {
            d.y = d.depth * this.config.nodeSpacing.x;
        });

        // ---- LINKS ----
        const link = this.g.selectAll('.tree-link')
            .data(links, d => d.target.data.id);

        // Enter links
        const linkEnter = link.enter()
            .insert('path', '.tree-node')
            .attr('class', 'tree-link')
            .attr('d', () => {
                const o = { x: source.x0 || 0, y: source.y0 || 0 };
                return this.diagonal(o, o);
            });

        // Update links
        const linkUpdate = linkEnter.merge(link);
        linkUpdate.transition()
            .duration(this.config.duration)
            .attr('d', d => this.diagonal(d.source, d.target));

        // Exit links
        link.exit()
            .transition()
            .duration(this.config.duration)
            .attr('d', () => {
                const o = { x: source.x, y: source.y };
                return this.diagonal(o, o);
            })
            .remove();

        // ---- NODES ----
        const node = this.g.selectAll('.tree-node')
            .data(nodes, d => d.data.id || (d.data.id = ++this.nodeIdCounter));

        // Enter nodes
        const nodeEnter = node.enter()
            .append('g')
            .attr('class', d => `tree-node ${d.data.id === this.selectedNode ? 'selected' : ''}`)
            .attr('transform', () => `translate(${source.y0 || 0},${source.x0 || 0})`)
            .on('click', (event, d) => {
                event.stopPropagation();
                this.onNodeClick(d);
            })
            .on('contextmenu', (event, d) => {
                event.preventDefault();
                event.stopPropagation();
                this.showContextMenu(event, d);
            });

        // Node circle
        nodeEnter.append('circle')
            .attr('class', d => `node-circle ${d.depth === 0 ? 'node-circle-root' : ''}`)
            .attr('r', 0)
            .attr('fill', d => d.data.color || '#6366f1')
            .attr('stroke', 'rgba(255,255,255,0.2)')
            .attr('stroke-width', d => d.depth === 0 ? 3 : 2);

        // Node icon (simplified as text)
        nodeEnter.append('text')
            .attr('class', 'node-icon')
            .attr('text-anchor', 'middle')
            .attr('dy', '0.35em')
            .attr('fill', 'white')
            .attr('font-size', '10px')
            .text(d => this.getNodeIcon(d.data.icon));

        // Node label
        nodeEnter.append('text')
            .attr('class', d => `node-label ${d.depth === 0 ? 'node-label-root' : ''}`)
            .attr('dy', d => d.children || d._children ? '-2em' : '2.5em')
            .attr('text-anchor', 'middle')
            .text(d => this.truncateLabel(d.data.title))
            .style('fill-opacity', 0);

        // Expand/collapse toggle
        const toggleEnter = nodeEnter.filter(d => d.children || d._children)
            .append('g')
            .attr('class', d => `node-toggle ${d._children ? 'collapsed' : ''}`)
            .attr('transform', `translate(${this.config.nodeRadius + 8}, 0)`)
            .on('click', (event, d) => {
                event.stopPropagation();
                this.toggleNode(d);
            });

        toggleEnter.append('circle')
            .attr('class', 'node-toggle-circle')
            .attr('r', 10);

        toggleEnter.append('text')
            .attr('class', 'node-toggle-icon')
            .attr('text-anchor', 'middle')
            .attr('dy', '0.35em')
            .attr('font-size', '8px')
            .attr('fill', '#94a3b8')
            .text(d => d._children ? `+${this.countDescendants(d)}` : '−');

        // Update nodes
        const nodeUpdate = nodeEnter.merge(node);

        nodeUpdate.transition()
            .duration(this.config.duration)
            .attr('transform', d => `translate(${d.y},${d.x})`);

        nodeUpdate.select('.node-circle')
            .transition()
            .duration(this.config.duration)
            .attr('r', this.config.nodeRadius)
            .attr('fill', d => d.data.color || '#6366f1');

        nodeUpdate.select('.node-label')
            .transition()
            .duration(this.config.duration)
            .style('fill-opacity', 1);

        nodeUpdate.select('.node-toggle')
            .attr('class', d => `node-toggle ${d._children ? 'collapsed' : ''}`);

        nodeUpdate.select('.node-toggle-icon')
            .text(d => d._children ? `+${this.countDescendants(d)}` : '−');

        // Exit nodes
        const nodeExit = node.exit()
            .transition()
            .duration(this.config.duration)
            .attr('transform', () => `translate(${source.y},${source.x})`)
            .remove();

        nodeExit.select('.node-circle')
            .attr('r', 0);

        nodeExit.select('.node-label')
            .style('fill-opacity', 0);

        // Store positions for next update
        nodes.forEach(d => {
            d.x0 = d.x;
            d.y0 = d.y;
        });
    },

    /**
     * Generate diagonal path between two points
     */
    diagonal(s, d) {
        return `M ${s.y} ${s.x}
                C ${(s.y + d.y) / 2} ${s.x},
                  ${(s.y + d.y) / 2} ${d.x},
                  ${d.y} ${d.x}`;
    },

    /**
     * Handle node click
     */
    onNodeClick(d) {
        this.selectNode(d.data.id);

        // Call App.selectNode if available
        if (window.App) {
            window.App.selectNode(d.data.id);
        }
    },

    /**
     * Select a node visually
     */
    selectNode(nodeId) {
        this.selectedNode = nodeId;

        this.g.selectAll('.tree-node')
            .classed('selected', d => d.data.id === nodeId);
    },

    /**
     * Toggle node expand/collapse
     */
    toggleNode(d) {
        if (d.children) {
            d._children = d.children;
            d.children = null;
        } else {
            d.children = d._children;
            d._children = null;
        }

        // Update via API
        if (d.data.id) {
            API.toggleCollapse(d.data.id).catch(console.error);
        }

        this.update(d);
    },

    /**
     * Show context menu
     */
    showContextMenu(event, d) {
        const contextMenu = document.getElementById('contextMenu');
        if (!contextMenu) return;

        // Store reference to node
        this._contextNode = d;

        // Position menu
        contextMenu.style.left = `${event.pageX}px`;
        contextMenu.style.top = `${event.pageY}px`;
        contextMenu.classList.add('active');

        // Bind actions
        const items = contextMenu.querySelectorAll('.context-item');
        items.forEach(item => {
            item.onclick = () => {
                const action = item.dataset.action;
                this.handleContextAction(action, d);
                contextMenu.classList.remove('active');
            };
        });
    },

    /**
     * Handle context menu action
     */
    handleContextAction(action, d) {
        switch (action) {
            case 'edit':
                if (window.NodeEditor) {
                    window.NodeEditor.editNode(d.data.id);
                }
                break;
            case 'addChild':
                if (window.NodeEditor) {
                    window.NodeEditor.openEditor(null, d.data.id);
                }
                break;
            case 'duplicate':
                if (window.App) {
                    window.App.duplicateNode(d.data.id);
                }
                break;
            case 'expand':
                this.expandBranch(d);
                break;
            case 'collapse':
                this.collapseBranch(d);
                break;
            case 'delete':
                if (window.App) {
                    window.App.deleteNode(d.data.id);
                }
                break;
        }
    },

    /**
     * Expand all nodes
     */
    expandAll() {
        const expand = (d) => {
            if (d._children) {
                d.children = d._children;
                d._children = null;
            }
            if (d.children) {
                d.children.forEach(expand);
            }
        };

        if (this.root) {
            expand(this.root);
            this.update(this.root);
            setTimeout(() => this.centerTree(), 100);
        }
    },

    /**
     * Collapse all nodes
     */
    collapseAll() {
        const collapse = (d) => {
            if (d.children && d.depth > 0) {
                d._children = d.children;
                d.children = null;
            }
            if (d._children) {
                d._children.forEach(collapse);
            }
        };

        if (this.root) {
            this.root.children?.forEach(collapse);
            this.update(this.root);
            setTimeout(() => this.centerTree(), 100);
        }
    },

    /**
     * Expand a branch
     */
    expandBranch(d) {
        const expand = (node) => {
            if (node._children) {
                node.children = node._children;
                node._children = null;
            }
            if (node.children) {
                node.children.forEach(expand);
            }
        };

        expand(d);
        this.update(d);
    },

    /**
     * Collapse a branch
     */
    collapseBranch(d) {
        if (d.children) {
            d._children = d.children;
            d.children = null;
            this.update(d);
        }
    },

    /**
     * Center tree view
     */
    centerTree() {
        if (!this.root || !this.svg) return;

        const bounds = this.g.node().getBBox();
        const width = this.svg.node().clientWidth;
        const height = this.svg.node().clientHeight;

        const scale = Math.min(
            0.9 * width / bounds.width,
            0.9 * height / bounds.height,
            1.5
        );

        const translateX = width / 2 - scale * (bounds.x + bounds.width / 2);
        const translateY = height / 2 - scale * (bounds.y + bounds.height / 2);

        this.svg.transition()
            .duration(750)
            .call(
                this.zoom.transform,
                d3.zoomIdentity.translate(translateX, translateY).scale(scale)
            );
    },

    /**
     * Reset view to default
     */
    resetView() {
        this.centerTree();
    },

    /**
     * Update zoom level display
     */
    updateZoomLevel(scale) {
        const zoomLevel = document.getElementById('zoomLevel');
        if (zoomLevel) {
            zoomLevel.textContent = `${Math.round(scale * 100)}%`;
        }
    },

    /**
     * Show empty state
     */
    showEmptyState() {
        const emptyState = document.getElementById('emptyState');
        if (emptyState) {
            emptyState.style.display = 'block';
        }
    },

    /**
     * Hide empty state
     */
    hideEmptyState() {
        const emptyState = document.getElementById('emptyState');
        if (emptyState) {
            emptyState.style.display = 'none';
        }
    },

    /**
     * Get icon character (simplified)
     */
    getNodeIcon(icon) {
        const iconMap = {
            'fa-circle': '\u25CF',
            'fa-star': '\u2605',
            'fa-lightbulb': '\u25C6',
            'fa-book': '\u25A3',
            'fa-code': '\u27E8/\u27E9',
            'fa-database': '\u25A7',
            'fa-folder': '\u25A6',
            'fa-tag': '\u25B2',
            'fa-bolt': '\u26A1',
            'fa-puzzle-piece': '\u25E7'
        };
        return iconMap[icon] || '\u25CF';
    },

    /**
     * Truncate label text
     */
    truncateLabel(text, maxLength = 20) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    /**
     * Count descendants
     */
    countDescendants(d) {
        let count = 0;
        const children = d._children || d.children || [];
        count = children.length;
        children.forEach(child => {
            count += this.countDescendants(child);
        });
        return count;
    },

    /**
     * Refresh tree data
     */
    async refresh() {
        try {
            const response = await API.getTree();
            this.treeData = response.data || [];
            this.renderTree();
        } catch (error) {
            console.error('Failed to refresh tree:', error);
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    TreeVisualization.init();
});

// Make TreeVisualization globally available
window.TreeVisualization = TreeVisualization;
