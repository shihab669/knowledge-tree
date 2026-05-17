/**
 * Knowledge Tree - API Client
 */

const API = {
    baseUrl: '/api',

    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * GET request
     */
    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },

    /**
     * POST request
     */
    async post(endpoint, body) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(body)
        });
    },

    /**
     * PUT request
     */
    async put(endpoint, body) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(body)
        });
    },

    /**
     * DELETE request
     */
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    },

    // =============================================
    // Tree API Methods
    // =============================================

    /**
     * Get full tree structure
     */
    async getTree() {
        return this.get('/tree');
    },

    /**
     * Get single node
     */
    async getNode(id) {
        return this.get(`/nodes/${id}`);
    },

    /**
     * Create new node
     */
    async createNode(data) {
        return this.post('/nodes', data);
    },

    /**
     * Update node
     */
    async updateNode(id, data) {
        return this.put(`/nodes/${id}`, data);
    },

    /**
     * Delete node
     */
    async deleteNode(id) {
        return this.delete(`/nodes/${id}`);
    },

    /**
     * Move node to new parent
     */
    async moveNode(id, parentId) {
        return this.post(`/nodes/${id}/move`, { parent_id: parentId });
    },

    /**
     * Toggle node collapse state
     */
    async toggleCollapse(id) {
        return this.post(`/nodes/${id}/toggle`);
    },

    /**
     * Duplicate node
     */
    async duplicateNode(id) {
        return this.post(`/nodes/${id}/duplicate`);
    },

    /**
     * Search nodes
     */
    async search(query) {
        return this.get(`/search?q=${encodeURIComponent(query)}`);
    }
};

// Make API globally available
window.API = API;
