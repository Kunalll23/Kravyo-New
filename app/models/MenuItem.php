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
                JOIN chefs u ON k.user_id = u.id
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
        if (isset($filters['is_veg']) && $filters['is_veg'] !== null && $filters['is_veg'] !== '') {
            $sql .= " AND m.is_veg = " . (int)$filters['is_veg'];
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

        // Keyword search (dish name, description, cuisine category, or kitchen name)
        if (!empty($filters['keyword'])) {
            $sql .= " AND (m.item_name LIKE :keyword OR m.description LIKE :keyword2 OR c.category_name LIKE :keyword3 OR k.kitchen_name LIKE :keyword4)";
            $params['keyword']  = '%' . $filters['keyword'] . '%';
            $params['keyword2'] = '%' . $filters['keyword'] . '%';
            $params['keyword3'] = '%' . $filters['keyword'] . '%';
            $params['keyword4'] = '%' . $filters['keyword'] . '%';
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
                JOIN chefs u ON k.user_id = u.id
                WHERE m.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Get top selling dishes across the platform, optionally filtered by city
     */
    public function getTopSellingItems(int $limit = 6, string $city = '', string $keyword = '', ?float $lat = null, ?float $lng = null): array {
        $distanceSelect = "NULL AS distance";
        if ($lat !== null && $lng !== null) {
            $distanceSelect = "ROUND(6371 * ACOS(LEAST(1.0, GREATEST(-1.0, COS(RADIANS({$lat})) * COS(RADIANS(k.latitude)) * COS(RADIANS(k.longitude) - RADIANS({$lng})) + SIN(RADIANS({$lat})) * SIN(RADIANS(k.latitude))))), 1) AS distance";
        }

        $sql = "SELECT m.*, c.category_name, k.kitchen_name, k.city, k.id AS kitchen_id,
                       COALESCE(SUM(CASE WHEN o.id IS NOT NULL THEN oi.quantity ELSE 0 END), 0) as total_sold, {$distanceSelect}
                FROM {$this->table} m
                JOIN categories c ON m.category_id = c.id
                JOIN kitchens k ON m.kitchen_id = k.id
                LEFT JOIN order_items oi ON oi.menu_item_id = m.id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.order_status = 'delivered'
                WHERE k.approval_status = 'approved'
                  AND k.is_open = 1
                  AND m.is_available = 1";
        
        $params = [];
        if (!empty($city)) {
            $sql .= " AND k.city = :city";
            $params['city'] = $city;
        }
        if (!empty($keyword)) {
            $sql .= " AND (m.item_name LIKE :keyword OR m.description LIKE :keyword2 OR c.category_name LIKE :keyword3 OR k.kitchen_name LIKE :keyword4)";
            $params['keyword']  = '%' . $keyword . '%';
            $params['keyword2'] = '%' . $keyword . '%';
            $params['keyword3'] = '%' . $keyword . '%';
            $params['keyword4'] = '%' . $keyword . '%';
        }

        $sql .= " GROUP BY m.id ORDER BY total_sold DESC, m.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get recommended items for a user (placeholder logic: random items from city)
     */
    public function getRecommendedItems(int $limit = 4, string $city = '', string $keyword = '', ?float $lat = null, ?float $lng = null): array {
        $distanceSelect = "NULL AS distance";
        if ($lat !== null && $lng !== null) {
            $distanceSelect = "ROUND(6371 * ACOS(LEAST(1.0, GREATEST(-1.0, COS(RADIANS({$lat})) * COS(RADIANS(k.latitude)) * COS(RADIANS(k.longitude) - RADIANS({$lng})) + SIN(RADIANS({$lat})) * SIN(RADIANS(k.latitude))))), 1) AS distance";
        }

        $sql = "SELECT m.*, c.category_name, k.kitchen_name, k.city, k.id AS kitchen_id, {$distanceSelect}
                FROM {$this->table} m
                JOIN categories c ON m.category_id = c.id
                JOIN kitchens k ON m.kitchen_id = k.id
                WHERE k.approval_status = 'approved'
                  AND k.is_open = 1
                  AND m.is_available = 1";
        
        $params = [];
        if (!empty($city)) {
            $sql .= " AND k.city = :city";
            $params['city'] = $city;
        }
        if (!empty($keyword)) {
            $sql .= " AND (m.item_name LIKE :keyword OR m.description LIKE :keyword2 OR c.category_name LIKE :keyword3 OR k.kitchen_name LIKE :keyword4)";
            $params['keyword']  = '%' . $keyword . '%';
            $params['keyword2'] = '%' . $keyword . '%';
            $params['keyword3'] = '%' . $keyword . '%';
            $params['keyword4'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY RAND() LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get live dish suggestions for autocomplete
     * Strictly queries dishes (menu_items) from approved, open kitchens.
     */
    public function getDishSuggestions(string $keyword, string $city = '', int $limit = 8): array {
        if (trim($keyword) === '') {
            return [];
        }

        $sql = "SELECT m.id, m.item_name, m.price, m.image, m.is_veg,
                       c.category_name, k.kitchen_name, k.city
                FROM {$this->table} m
                JOIN categories c ON m.category_id = c.id
                JOIN kitchens k ON m.kitchen_id = k.id
                WHERE k.approval_status = 'approved'
                  AND k.is_open = 1
                  AND m.is_available = 1
                  AND (m.item_name LIKE :kw1 OR m.description LIKE :kw2 OR c.category_name LIKE :kw3)";

        $params = [
            'kw1' => '%' . $keyword . '%',
            'kw2' => '%' . $keyword . '%',
            'kw3' => '%' . $keyword . '%',
        ];

        // Strictly filter to the selected city only
        if (!empty($city)) {
            $sql .= " AND k.city = :city";
            $params['city'] = $city;
        }

        $sql .= " ORDER BY (m.item_name LIKE :exactStart) DESC, m.item_name ASC LIMIT " . (int)$limit;
        $params['exactStart'] = $keyword . '%';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

