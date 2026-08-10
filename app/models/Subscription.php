<?php
/**
 * Kravyo - Subscription Model (Chef's Tiffin Plans)
 * Phase 7: Manages tiffin subscription plans created by home chefs
 */

class Subscription extends Model {
    protected string $table = 'tiffin_subscriptions';

    /**
     * Get all subscription plans for a specific kitchen
     */
    public function findByKitchenId(int $kitchenId): array {
        $sql = "SELECT * FROM {$this->table}
                WHERE kitchen_id = :kitchen_id
                ORDER BY is_active DESC, created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Get only active subscription plans for a kitchen (customer-facing)
     */
    public function findActiveByKitchenId(int $kitchenId): array {
        $sql = "SELECT * FROM {$this->table}
                WHERE kitchen_id = :kitchen_id AND is_active = 1
                ORDER BY plan_type ASC, price ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Find a single plan with full kitchen and chef details (for customer detail page)
     */
    public function findWithKitchenDetails(int $id): ?array {
        $sql = "SELECT s.*, k.kitchen_name, k.city, k.pincode, k.is_open,
                       k.hygiene_badge, k.banner_image, k.personal_story,
                       u.full_name AS chef_name
                FROM {$this->table} s
                JOIN kitchens k ON s.kitchen_id = k.id
                JOIN users u ON k.user_id = u.id
                WHERE s.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Browse all active plans across all approved & open kitchens
     */
    public function findAllActivePlans(array $filters = []): array {
        $sql = "SELECT s.*, k.kitchen_name, k.city, k.pincode,
                       k.hygiene_badge, u.full_name AS chef_name
                FROM {$this->table} s
                JOIN kitchens k ON s.kitchen_id = k.id
                JOIN users u ON k.user_id = u.id
                WHERE s.is_active = 1
                  AND k.approval_status = 'approved'
                  AND k.is_open = 1";
        $params = [];

        // Plan type filter
        if (!empty($filters['plan_type'])) {
            $sql .= " AND s.plan_type = :plan_type";
            $params['plan_type'] = $filters['plan_type'];
        }

        // City filter
        if (!empty($filters['city'])) {
            $sql .= " AND k.city = :city";
            $params['city'] = $filters['city'];
        }

        // Keyword search
        if (!empty($filters['keyword'])) {
            $sql .= " AND (s.plan_name LIKE :keyword OR s.description LIKE :keyword2 OR k.kitchen_name LIKE :keyword3)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
            $params['keyword2'] = '%' . $filters['keyword'] . '%';
            $params['keyword3'] = '%' . $filters['keyword'] . '%';
        }

        // Sorting
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_low':
                $sql .= " ORDER BY s.price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY s.price DESC";
                break;
            default:
                $sql .= " ORDER BY s.created_at DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Toggle plan active/inactive status
     */
    public function toggleActive(int $id, int $isActive): bool {
        $sql = "UPDATE {$this->table} SET is_active = :is_active WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['is_active' => $isActive, 'id' => $id]);
    }

    /**
     * Count plans for a specific kitchen
     */
    public function countByKitchenId(int $kitchenId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Check if a plan belongs to a specific kitchen (security check)
     */
    public function belongsToKitchen(int $planId, int $kitchenId): bool {
        $sql = "SELECT id FROM {$this->table} WHERE id = :id AND kitchen_id = :kitchen_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $planId, 'kitchen_id' => $kitchenId]);
        return $stmt->fetch() !== false;
    }
}
