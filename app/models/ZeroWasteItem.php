<?php
/**
 * Kravyo - ZeroWasteItem Model (Phase 8: Zero Food Waste Module)
 * Manages end-of-day discounted meal listings by home chefs
 */

class ZeroWasteItem extends Model {
    protected string $table = 'zero_waste_items';

    /**
     * Create a new zero waste listing
     */
    public function createListing(array $data): int|string {
        return $this->create($data);
    }

    /**
     * Get all ACTIVE, non-expired listings for the customer showcase.
     * Joins menu_items, kitchens, categories and users for full display context.
     * Also auto-expires any stale listings before fetching.
     */
    public function findActiveAll(array $filters = []): array {
        $this->markExpired();

        $sql = "SELECT
                    z.*,
                    m.item_name, m.description AS dish_description, m.image AS dish_image,
                    m.is_veg, m.is_jain_available, m.is_diabetic_friendly,
                    c.category_name,
                    k.kitchen_name, k.city, k.pincode, k.hygiene_badge, k.is_open,
                    u.full_name AS chef_name,
                    ROUND(((z.original_price - z.discounted_price) / z.original_price) * 100) AS discount_pct,
                    TIMESTAMPDIFF(MINUTE, NOW(), z.expiry_time) AS minutes_remaining
                FROM {$this->table} z
                JOIN menu_items m  ON z.menu_item_id = m.id
                JOIN categories c  ON m.category_id  = c.id
                JOIN kitchens k    ON z.kitchen_id    = k.id
                JOIN users u       ON k.user_id       = u.id
                WHERE z.status = 'active'
                  AND z.expiry_time > NOW()
                  AND k.approval_status = 'approved'
                  AND k.is_open = 1";

        $params = [];

        if (!empty($filters['is_veg'])) {
            $sql .= " AND m.is_veg = 1";
        }
        if (!empty($filters['city'])) {
            $sql .= " AND k.city = :city";
            $params['city'] = $filters['city'];
        }

        // Sorting
        $sort = $filters['sort'] ?? 'expiry';
        switch ($sort) {
            case 'discount':
                $sql .= " ORDER BY discount_pct DESC";
                break;
            case 'price_low':
                $sql .= " ORDER BY z.discounted_price ASC";
                break;
            default:
                $sql .= " ORDER BY z.expiry_time ASC"; // Expiring soonest first
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get a limited set of active listings for the homepage teaser (max 3).
     */
    public function findHomepageTeaser(int $limit = 3): array {
        $this->markExpired();

        $sql = "SELECT
                    z.*,
                    m.item_name, m.image AS dish_image, m.is_veg,
                    k.kitchen_name, k.city,
                    ROUND(((z.original_price - z.discounted_price) / z.original_price) * 100) AS discount_pct,
                    TIMESTAMPDIFF(MINUTE, NOW(), z.expiry_time) AS minutes_remaining
                FROM {$this->table} z
                JOIN menu_items m ON z.menu_item_id = m.id
                JOIN kitchens k   ON z.kitchen_id   = k.id
                WHERE z.status = 'active'
                  AND z.expiry_time > NOW()
                  AND k.approval_status = 'approved'
                  AND k.is_open = 1
                ORDER BY z.expiry_time ASC
                LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all listings created by a specific kitchen (for chef's management panel).
     */
    public function findByKitchenId(int $kitchenId): array {
        $sql = "SELECT
                    z.*,
                    m.item_name, m.image AS dish_image, m.is_veg,
                    ROUND(((z.original_price - z.discounted_price) / z.original_price) * 100) AS discount_pct,
                    TIMESTAMPDIFF(MINUTE, NOW(), z.expiry_time) AS minutes_remaining
                FROM {$this->table} z
                JOIN menu_items m ON z.menu_item_id = m.id
                WHERE z.kitchen_id = :kitchen_id
                ORDER BY z.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Auto-expire listings where expiry_time has passed.
     * Called on every page load — no cron required.
     */
    public function markExpired(): void {
        $sql = "UPDATE {$this->table}
                SET status = 'expired'
                WHERE status = 'active'
                  AND expiry_time <= NOW()";
        $this->db->exec($sql);
    }

    /**
     * Verify that a listing belongs to a specific kitchen (security check).
     */
    public function belongsToKitchen(int $itemId, int $kitchenId): bool {
        $sql = "SELECT id FROM {$this->table}
                WHERE id = :id AND kitchen_id = :kitchen_id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $itemId, 'kitchen_id' => $kitchenId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Toggle listing status between active and sold_out.
     */
    public function toggleStatus(int $id, string $newStatus): bool {
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['status' => $newStatus, 'id' => $id]);
    }

    /**
     * Count stats for chef's management panel.
     */
    public function getStatsByKitchenId(int $kitchenId): array {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'active' AND expiry_time > NOW() THEN 1 ELSE 0 END) AS active_count,
                    SUM(CASE WHEN status = 'sold_out' THEN 1 ELSE 0 END)                       AS sold_out_count,
                    SUM(CASE WHEN status = 'expired' OR expiry_time <= NOW() THEN 1 ELSE 0 END) AS expired_count
                FROM {$this->table}
                WHERE kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return $result ?: ['total' => 0, 'active_count' => 0, 'sold_out_count' => 0, 'expired_count' => 0];
    }

    /**
     * Count total active zero waste listings (for homepage and admin stats).
     */
    public function countActive(): int {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table}
                WHERE status = 'active' AND expiry_time > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }
}
