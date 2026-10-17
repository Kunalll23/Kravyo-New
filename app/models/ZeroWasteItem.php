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
                JOIN chefs u       ON k.user_id       = u.id
                WHERE z.status IN ('active', 'sold_out')
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

        // Sorting: Always prioritize active deals first, followed by sold-out deals
        $sort = $filters['sort'] ?? 'expiry';
        $orderClause = match ($sort) {
            'discount'  => 'discount_pct DESC',
            'price_low' => 'z.discounted_price ASC',
            default     => 'z.expiry_time ASC',
        };
        $sql .= " ORDER BY (z.status = 'sold_out') ASC, " . $orderClause;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get a limited set of active listings for the homepage teaser (max 3).
     */
    public function findHomepageTeaser(int $limit = 3, string $city = ''): array {
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
                  AND k.is_open = 1";
                  
        $params = [];
        if (!empty($city)) {
            $sql .= " AND k.city = :city";
            $params['city'] = $city;
        }

        $sql .= " ORDER BY z.expiry_time ASC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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
     * Count total active zero waste listings (optionally filtered by city).
     */
    public function countActive(?string $city = null): int {
        if (!empty($city)) {
            $sql = "SELECT COUNT(*) AS total FROM {$this->table} z
                    JOIN kitchens k ON z.kitchen_id = k.id
                    WHERE z.status = 'active' AND z.expiry_time > NOW() AND k.city = :city";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['city' => $city]);
        } else {
            $sql = "SELECT COUNT(*) AS total FROM {$this->table}
                    WHERE status = 'active' AND expiry_time > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    // ─── Admin methods ──────────────────────────────────────────────────────────

    /**
     * Get ALL zero waste listings platform-wide with full context & rich filters (Admin view).
     * Auto-expires stale listings first, then fetches with optional filters.
     */
    public function findAllWithAdminDetails(array $filters = []): array {
        $this->markExpired();

        $sql = "SELECT
                    z.id, z.status, z.quantity_available AS quantity, z.original_price, z.discounted_price,
                    z.expiry_time, z.created_at, z.kitchen_id,
                    m.id AS menu_item_id, m.item_name, m.image AS dish_image, m.is_veg,
                    k.kitchen_name, k.city,
                    ROUND(((z.original_price - z.discounted_price) / z.original_price) * 100) AS discount_pct,
                    TIMESTAMPDIFF(MINUTE, NOW(), z.expiry_time) AS minutes_remaining,
                    COALESCE(claimed.total_claimed_qty, 0) AS claimed_qty,
                    COALESCE(claimed.total_claimed_amount, 0) AS claimed_revenue
                FROM {$this->table} z
                JOIN menu_items m ON z.menu_item_id = m.id
                JOIN kitchens k   ON z.kitchen_id   = k.id
                LEFT JOIN (
                    SELECT zero_waste_id, SUM(quantity) AS total_claimed_qty, SUM(subtotal) AS total_claimed_amount
                    FROM order_items
                    WHERE is_zero_waste = 1 AND zero_waste_id IS NOT NULL
                    GROUP BY zero_waste_id
                ) claimed ON z.id = claimed.zero_waste_id
                WHERE 1=1";

        $params = [];

        // Search (dish name, kitchen name, city, or ID)
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            if (is_numeric(ltrim($search, '#'))) {
                $sql .= " AND (z.id = :search_id OR m.item_name LIKE :search1 OR k.kitchen_name LIKE :search2)";
                $params['search_id'] = (int)ltrim($search, '#');
                $params['search1']   = '%' . $search . '%';
                $params['search2']   = '%' . $search . '%';
            } else {
                $sql .= " AND (m.item_name LIKE :search1 OR k.kitchen_name LIKE :search2 OR k.city LIKE :search3)";
                $params['search1'] = '%' . $search . '%';
                $params['search2'] = '%' . $search . '%';
                $params['search3'] = '%' . $search . '%';
            }
        }

        // Status filter
        if (!empty($filters['status']) && in_array($filters['status'], ['active', 'sold_out', 'expired'], true)) {
            $sql .= " AND z.status = :status";
            $params['status'] = $filters['status'];
        }

        // City filter
        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            $sql .= " AND k.city = :city";
            $params['city'] = $filters['city'];
        }

        // Kitchen filter
        if (!empty($filters['kitchen_id'])) {
            $sql .= " AND z.kitchen_id = :kitchen_id";
            $params['kitchen_id'] = (int)$filters['kitchen_id'];
        }

        // Time range filter
        if (!empty($filters['time_range']) && $filters['time_range'] !== 'all') {
            switch ($filters['time_range']) {
                case 'today':
                    $sql .= " AND DATE(z.created_at) = CURDATE()";
                    break;
                case '7days':
                    $sql .= " AND z.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case '30days':
                    $sql .= " AND z.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
            }
        }

        $sql .= " ORDER BY z.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get platform-wide Zero Waste statistics for Admin reporting.
     */
    public function getPlatformStats(): array {
        $sql = "SELECT
                    COUNT(*) AS total_listings,
                    SUM(CASE WHEN status = 'active' AND expiry_time > NOW() THEN 1 ELSE 0 END) AS active_listings,
                    SUM(CASE WHEN status = 'sold_out' THEN 1 ELSE 0 END) AS sold_out_listings,
                    SUM(CASE WHEN status = 'expired' OR expiry_time <= NOW() THEN 1 ELSE 0 END) AS expired_listings,
                    SUM(CASE WHEN status = 'active' AND expiry_time > NOW() THEN quantity_available ELSE 0 END) AS total_active_qty,
                    SUM(original_price * quantity_available) AS total_original_value,
                    SUM(discounted_price * quantity_available) AS total_discounted_value
                FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch() ?: [];

        $row['potential_savings'] = ($row['total_original_value'] ?? 0) - ($row['total_discounted_value'] ?? 0);

        // Actual orders and meals rescued
        $stmtClaimed = $this->db->query("
            SELECT COUNT(DISTINCT order_id) AS total_orders,
                   COALESCE(SUM(quantity), 0) AS meals_saved_qty,
                   COALESCE(SUM(subtotal), 0) AS realized_revenue
            FROM order_items
            WHERE is_zero_waste = 1
        ");
        $claimedRow = $stmtClaimed->fetch() ?: [];
        $row['meals_saved_qty']   = (int)($claimedRow['meals_saved_qty'] ?? 0);
        $row['realized_revenue']  = (float)($claimedRow['realized_revenue'] ?? 0);
        $row['zero_waste_orders'] = (int)($claimedRow['total_orders'] ?? 0);

        return $row;
    }

    /**
     * Force-expire an active listing (Admin action)
     */
    public function forceExpire(int $id): bool {
        $sql = "UPDATE {$this->table} SET status = 'expired' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Fetch a single active, non-expired zero waste listing by its ID.
     * Returns null if the listing does not exist, is expired, or is not active.
     */
    public function findActiveById(int $id): ?array {
        $sql = "SELECT
                    z.*,
                    m.item_name, m.image AS dish_image, m.is_veg, m.price AS original_menu_price,
                    k.kitchen_name, k.city, k.id AS kitchen_id,
                    TIMESTAMPDIFF(MINUTE, NOW(), z.expiry_time) AS minutes_remaining
                FROM {$this->table} z
                JOIN menu_items m ON z.menu_item_id = m.id
                JOIN kitchens k   ON z.kitchen_id   = k.id
                WHERE z.id = :id
                  AND z.status = 'active'
                  AND z.expiry_time > NOW()
                  AND k.approval_status = 'approved'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Atomically decrement available quantity after a purchase.
     * Automatically marks the listing as 'sold_out' when quantity reaches 0.
     */
    public function decrementQuantity(int $id, int $qty): void {
        $sql = "UPDATE {$this->table}
                SET quantity_available = GREATEST(0, quantity_available - :qty),
                    status = CASE
                        WHEN (quantity_available - :qty2) <= 0 THEN 'sold_out'
                        ELSE status
                    END
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['qty' => $qty, 'qty2' => $qty, 'id' => $id]);
    }

    /**
     * Get per-kitchen Zero Waste listing summary for Admin reporting.
     */
    public function getStatsByKitchen(): array {
        $sql = "SELECT
                    k.kitchen_name,
                    k.city,
                    COUNT(z.id) AS total_listings,
                    SUM(CASE WHEN z.status = 'active' AND z.expiry_time > NOW() THEN 1 ELSE 0 END) AS active_listings,
                    SUM(CASE WHEN z.status = 'sold_out' THEN 1 ELSE 0 END) AS sold_out_listings,
                    SUM(CASE WHEN z.status = 'active' AND z.expiry_time > NOW() THEN z.quantity_available ELSE 0 END) AS active_qty
                FROM {$this->table} z
                JOIN kitchens k ON z.kitchen_id = k.id
                GROUP BY z.kitchen_id, k.kitchen_name, k.city
                ORDER BY total_listings DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
