<?php
/**
 * Kravyo - MenuItem Model (Chef's Dish / Food Item Management)
 */

class MenuItem extends Model {
    protected string $table = 'menu_items';

    /**
     * Get all menu items for a specific kitchen with category name
     */
    public function findByKitchenId(int $kitchenId): array {
        $sql = "SELECT m.*, c.category_name
                FROM {$this->table} m
                JOIN categories c ON m.category_id = c.id
                WHERE m.kitchen_id = :kitchen_id
                ORDER BY c.category_name ASC, m.item_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Find a single menu item with category name
     */
    public function findWithCategory(int $id): ?array {
        $sql = "SELECT m.*, c.category_name
                FROM {$this->table} m
                JOIN categories c ON m.category_id = c.id
                WHERE m.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Count menu items for a specific kitchen
     */
    public function countByKitchenId(int $kitchenId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Toggle dish availability (available / unavailable)
     */
    public function toggleAvailability(int $id, int $isAvailable): bool {
        $sql = "UPDATE {$this->table} SET is_available = :is_available WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['is_available' => $isAvailable, 'id' => $id]);
    }

    /**
     * Verify that a menu item belongs to a specific kitchen (security check)
     */
    public function belongsToKitchen(int $itemId, int $kitchenId): bool {
        $sql = "SELECT id FROM {$this->table} WHERE id = :id AND kitchen_id = :kitchen_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $itemId, 'kitchen_id' => $kitchenId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Count total menu items across all kitchens
     */
    public function countAll(): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Search menu items with filters (for customer food discovery)
     * Joins kitchen & category info, only returns items from approved+open kitchens
     */
    public function searchWithFilters(array $filters = []): array {
        $sql = "SELECT m.*, c.category_name, k.kitchen_name, k.city, k.pincode, k.id AS kitchen_id,
                       k.hygiene_badge, u.full_name AS chef_name
                FROM {$this->table} m
                JOIN categories c ON m.category_id = c.id
                JOIN kitchens k ON m.kitchen_id = k.id
                JOIN users u ON k.user_id = u.id
                WHERE k.approval_status = 'approved'
                  AND k.is_open = 1
                  AND m.is_available = 1";
        $params = [];

        // Category filter
        if (!empty($filters['category_id'])) {
            $sql .= " AND m.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        // Dietary filters
        if (!empty($filters['is_veg'])) {
            $sql .= " AND m.is_veg = 1";
        }
        if (!empty($filters['is_jain_available'])) {
            $sql .= " AND m.is_jain_available = 1";
        }
        if (!empty($filters['is_diabetic_friendly'])) {
            $sql .= " AND m.is_diabetic_friendly = 1";
        }

        // Location filters
        if (!empty($filters['city'])) {
            $sql .= " AND k.city = :city";
            $params['city'] = $filters['city'];
        }
        if (!empty($filters['pincode'])) {
            $sql .= " AND k.pincode = :pincode";
            $params['pincode'] = $filters['pincode'];
        }

        // Keyword search (dish name or description)
        if (!empty($filters['keyword'])) {
            $sql .= " AND (m.item_name LIKE :keyword OR m.description LIKE :keyword2)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
            $params['keyword2'] = '%' . $filters['keyword'] . '%';
        }

        // Sorting
        $sortOption = $filters['sort'] ?? 'newest';
        switch ($sortOption) {
            case 'price_low':
                $sql .= " ORDER BY m.price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY m.price DESC";
                break;
            case 'name':
                $sql .= " ORDER BY m.item_name ASC";
                break;
            default:
                $sql .= " ORDER BY m.created_at DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a single menu item with full kitchen and category details (for dish detail page)
     */
    public function findWithKitchenAndCategory(int $id): ?array {
        $sql = "SELECT m.*, c.category_name,
                       k.id AS kitchen_id, k.kitchen_name, k.city, k.pincode,
                       k.hygiene_badge, k.is_open, k.banner_image AS kitchen_banner,
                       k.personal_story, u.full_name AS chef_name
                FROM {$this->table} m
                JOIN categories c ON m.category_id = c.id
                JOIN kitchens k ON m.kitchen_id = k.id
                JOIN users u ON k.user_id = u.id
                WHERE m.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }
}
