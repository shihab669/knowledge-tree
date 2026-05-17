<?php

namespace App\Models;

use App\Core\Database;

class Node
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id, int $userId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM nodes WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public function getRootNodes(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM nodes WHERE user_id = ? AND parent_id IS NULL ORDER BY position ASC, created_at ASC",
            [$userId]
        );
    }

    public function getChildren(int $parentId, int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM nodes WHERE parent_id = ? AND user_id = ? ORDER BY position ASC, created_at ASC",
            [$parentId, $userId]
        );
    }

    public function getTree(int $userId): array
    {
        $nodes = $this->db->fetchAll(
            "SELECT * FROM nodes WHERE user_id = ? ORDER BY position ASC, created_at ASC",
            [$userId]
        );

        return $this->buildTree($nodes);
    }

    private function buildTree(array $nodes, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($nodes as $node) {
            if ($node['parent_id'] == $parentId) {
                $children = $this->buildTree($nodes, $node['id']);
                if ($children) {
                    $node['children'] = $children;
                }
                $tree[] = $node;
            }
        }
        return $tree;
    }

    public function create(array $data): int
    {
        $position = $this->getNextPosition($data['user_id'], $data['parent_id'] ?? null);

        return $this->db->insert('nodes', [
            'user_id' => $data['user_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'title' => $data['title'] ?? 'New Node',
            'content' => $data['content'] ?? '',
            'tags' => $data['tags'] ?? '',
            'color' => $data['color'] ?? '#6366f1',
            'icon' => $data['icon'] ?? 'fa-circle',
            'position' => $position,
            'is_collapsed' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $rows = $this->db->update('nodes', $data, 'id = ? AND user_id = ?', [$id, $userId]);
        return $rows > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        // Get all descendants
        $descendants = $this->getAllDescendantIds($id, $userId);
        $descendants[] = $id;

        $placeholders = implode(',', array_fill(0, count($descendants), '?'));

        $this->db->beginTransaction();
        try {
            $this->db->query(
                "DELETE FROM nodes WHERE id IN ({$placeholders}) AND user_id = ?",
                array_merge($descendants, [$userId])
            );
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    private function getAllDescendantIds(int $parentId, int $userId): array
    {
        $children = $this->db->fetchAll(
            "SELECT id FROM nodes WHERE parent_id = ? AND user_id = ?",
            [$parentId, $userId]
        );

        $ids = [];
        foreach ($children as $child) {
            $ids[] = $child['id'];
            $ids = array_merge($ids, $this->getAllDescendantIds($child['id'], $userId));
        }

        return $ids;
    }

    public function move(int $id, int $userId, ?int $newParentId): bool
    {
        // Prevent moving to self or own descendant
        if ($newParentId === $id) {
            return false;
        }

        if ($newParentId !== null) {
            $descendants = $this->getAllDescendantIds($id, $userId);
            if (in_array($newParentId, $descendants)) {
                return false;
            }
        }

        $position = $this->getNextPosition($userId, $newParentId);

        return $this->update($id, $userId, [
            'parent_id' => $newParentId,
            'position' => $position
        ]);
    }

    public function toggleCollapse(int $id, int $userId): bool
    {
        $node = $this->findById($id, $userId);
        if (!$node) {
            return false;
        }

        return $this->update($id, $userId, [
            'is_collapsed' => $node['is_collapsed'] ? 0 : 1
        ]);
    }

    private function getNextPosition(int $userId, ?int $parentId): int
    {
        $result = $this->db->fetch(
            "SELECT COALESCE(MAX(position), -1) + 1 as next_pos FROM nodes WHERE user_id = ? AND parent_id " .
            ($parentId === null ? "IS NULL" : "= ?"),
            $parentId === null ? [$userId] : [$userId, $parentId]
        );

        return $result['next_pos'] ?? 0;
    }

    public function search(int $userId, string $query): array
    {
        $searchTerm = '%' . $query . '%';
        return $this->db->fetchAll(
            "SELECT * FROM nodes WHERE user_id = ? AND (title LIKE ? OR content LIKE ? OR tags LIKE ?) ORDER BY updated_at DESC LIMIT 50",
            [$userId, $searchTerm, $searchTerm, $searchTerm]
        );
    }

    public function getRecent(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM nodes WHERE user_id = ? ORDER BY updated_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public function getCount(int $userId): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM nodes WHERE user_id = ?",
            [$userId]
        );
        return $result['count'] ?? 0;
    }

    public function duplicate(int $id, int $userId, ?int $newParentId = null): ?int
    {
        $node = $this->findById($id, $userId);
        if (!$node) {
            return null;
        }

        $newId = $this->create([
            'user_id' => $userId,
            'parent_id' => $newParentId ?? $node['parent_id'],
            'title' => $node['title'] . ' (Copy)',
            'content' => $node['content'],
            'tags' => $node['tags'],
            'color' => $node['color'],
            'icon' => $node['icon']
        ]);

        // Duplicate children recursively
        $children = $this->getChildren($id, $userId);
        foreach ($children as $child) {
            $this->duplicate($child['id'], $userId, $newId);
        }

        return $newId;
    }
}
