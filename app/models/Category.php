<?php
/**
 * Kravyo - Category Model (Food Category Management)
 */

class Category extends Model {
    protected string $table = 'categories';

    /**
     * Get all categories ordered by name
     */
    public function getAllOrdered(): array {
        $sql = "SELECT * FROM {$this->table} ORDER BY category_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total categories
     */
    public function countAll(): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Check if category has any linked menu items
     */
    public function hasMenuItems(int $categoryId): bool {
        $sql = "SELECT COUNT(*) as total FROM menu_items WHERE category_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $categoryId]);
        $result = $stmt->fetch();
        return ((int) ($result['total'] ?? 0)) > 0;
    }

    /**
     * Count menu items in a category
     */
    public function countMenuItems(int $categoryId): int {
        $sql = "SELECT COUNT(*) as total FROM menu_items WHERE category_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $categoryId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }
}
