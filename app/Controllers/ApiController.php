<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\Node;

class ApiController
{
    private Node $node;

    public function __construct()
    {
        $this->node = new Node();
        Auth::requireAuth();
    }

    public function getTree(): void
    {
        $userId = Auth::getUserId();
        $tree = $this->node->getTree($userId);
        Response::success($tree);
    }

    public function getNode(int $id): void
    {
        $userId = Auth::getUserId();
        $node = $this->node->findById($id, $userId);

        if (!$node) {
            Response::error('Node not found', 404);
            return;
        }

        Response::success($node);
    }

    public function createNode(): void
    {
        $data = $this->getJsonInput();

        if (empty($data['title'])) {
            Response::error('Title is required');
            return;
        }

        $userId = Auth::getUserId();

        // Verify parent belongs to user
        if (!empty($data['parent_id'])) {
            $parent = $this->node->findById($data['parent_id'], $userId);
            if (!$parent) {
                Response::error('Parent node not found', 404);
                return;
            }
        }

        $nodeId = $this->node->create([
            'user_id' => $userId,
            'parent_id' => $data['parent_id'] ?? null,
            'title' => htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8'),
            'content' => $data['content'] ?? '',
            'tags' => $data['tags'] ?? '',
            'color' => $data['color'] ?? '#6366f1',
            'icon' => $data['icon'] ?? 'fa-circle'
        ]);

        $node = $this->node->findById($nodeId, $userId);
        Response::success($node, 'Node created successfully');
    }

    public function updateNode(int $id): void
    {
        $data = $this->getJsonInput();
        $userId = Auth::getUserId();

        $node = $this->node->findById($id, $userId);
        if (!$node) {
            Response::error('Node not found', 404);
            return;
        }

        $updateData = [];
        if (isset($data['title'])) {
            $updateData['title'] = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
        }
        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
        }
        if (isset($data['tags'])) {
            $updateData['tags'] = $data['tags'];
        }
        if (isset($data['color'])) {
            $updateData['color'] = $data['color'];
        }
        if (isset($data['icon'])) {
            $updateData['icon'] = $data['icon'];
        }

        if (empty($updateData)) {
            Response::error('No data to update');
            return;
        }

        $this->node->update($id, $userId, $updateData);
        $updatedNode = $this->node->findById($id, $userId);

        Response::success($updatedNode, 'Node updated successfully');
    }

    public function deleteNode(int $id): void
    {
        $userId = Auth::getUserId();

        $node = $this->node->findById($id, $userId);
        if (!$node) {
            Response::error('Node not found', 404);
            return;
        }

        $this->node->delete($id, $userId);
        Response::success(null, 'Node deleted successfully');
    }

    public function moveNode(int $id): void
    {
        $data = $this->getJsonInput();
        $userId = Auth::getUserId();

        $node = $this->node->findById($id, $userId);
        if (!$node) {
            Response::error('Node not found', 404);
            return;
        }

        $newParentId = $data['parent_id'] ?? null;

        if ($newParentId !== null) {
            $parent = $this->node->findById($newParentId, $userId);
            if (!$parent) {
                Response::error('Target parent not found', 404);
                return;
            }
        }

        $result = $this->node->move($id, $userId, $newParentId);

        if ($result) {
            Response::success(null, 'Node moved successfully');
        } else {
            Response::error('Cannot move node to this location');
        }
    }

    public function toggleCollapse(int $id): void
    {
        $userId = Auth::getUserId();
        $result = $this->node->toggleCollapse($id, $userId);

        if ($result) {
            Response::success(null, 'Node toggled');
        } else {
            Response::error('Node not found', 404);
        }
    }

    public function search(): void
    {
        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            Response::error('Search query must be at least 2 characters');
            return;
        }

        $userId = Auth::getUserId();
        $results = $this->node->search($userId, $query);

        Response::success($results);
    }

    public function duplicateNode(int $id): void
    {
        $userId = Auth::getUserId();

        $node = $this->node->findById($id, $userId);
        if (!$node) {
            Response::error('Node not found', 404);
            return;
        }

        $newId = $this->node->duplicate($id, $userId);

        if ($newId) {
            $newNode = $this->node->findById($newId, $userId);
            Response::success($newNode, 'Node duplicated successfully');
        } else {
            Response::error('Failed to duplicate node');
        }
    }

    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return $data ?? [];
    }
}
