<?php
/**
 * Kravyo - Order Model (Customer Order Management)
 * Phase 6: Order placement, tracking, and lifecycle management
 */

class Order extends Model {
    protected string $table = 'orders';

    /**
     * Generate a unique order number (KRV-YYYYMMDD-XXXXX)
     */
    public function generateOrderNumber(): string {
        $prefix = 'KRV-' . date('Ymd') . '-';
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        $orderNumber = $prefix . $random;

        // Ensure uniqueness
        while ($this->findByOrderNumber($orderNumber)) {
            $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $orderNumber = $prefix . $random;
        }

        return $orderNumber;
    }

    /**
     * Find order by order number
     */
    public function findByOrderNumber(string $orderNumber): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE order_number = :order_number LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_number' => $orderNumber]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Find order with full details (kitchen name, customer name, address)
     */
    public function findWithDetails(int $id): ?array {
        $sql = "SELECT o.*,
                       k.kitchen_name, k.id AS kitchen_id_ref,
                       u.full_name AS customer_name, u.phone AS customer_phone, u.email AS customer_email,
                       a.street_address, a.landmark, a.city AS delivery_city,
                       a.pincode AS delivery_pincode, a.address_type
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN users u ON o.customer_id = u.id
                JOIN addresses a ON o.address_id = a.id
                WHERE o.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Find all orders for a customer (order history)
     */
    public function findByCustomerId(int $customerId, ?string $status = null): array {
        $sql = "SELECT o.*, k.kitchen_name
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                WHERE o.customer_id = :customer_id";
        $params = ['customer_id' => $customerId];

        if ($status !== null) {
            $sql .= " AND o.order_status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find all orders for a kitchen (chef order management)
     */
    public function findByKitchenId(int $kitchenId, ?string $status = null): array {
        $sql = "SELECT o.*, u.full_name AS customer_name, u.phone AS customer_phone,
                       a.street_address, a.landmark, a.city AS delivery_city, a.pincode AS delivery_pincode
                FROM {$this->table} o
                JOIN users u ON o.customer_id = u.id
                JOIN addresses a ON o.address_id = a.id
                WHERE o.kitchen_id = :kitchen_id";
        $params = ['kitchen_id' => $kitchenId];

        if ($status !== null) {
            $sql .= " AND o.order_status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Update order status
     */
    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE {$this->table} SET order_status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $id, string $status): bool {
        $sql = "UPDATE {$this->table} SET payment_status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * Count orders for a kitchen, optionally by status
     */
    public function countByKitchenId(int $kitchenId, ?string $status = null): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE kitchen_id = :kitchen_id";
        $params = ['kitchen_id' => $kitchenId];

        if ($status !== null) {
            $sql .= " AND order_status = :status";
            $params['status'] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Count orders for a customer
     */
    public function countByCustomerId(int $customerId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE customer_id = :customer_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Calculate total earnings for a kitchen
     */
    public function totalEarnings(int $kitchenId): float {
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table}
                WHERE kitchen_id = :kitchen_id AND order_status = 'delivered'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return round((float) ($result['total'] ?? 0), 2);
    }

    /**
     * Check if an order belongs to a specific customer
     */
    public function belongsToCustomer(int $orderId, int $customerId): bool {
        $sql = "SELECT id FROM {$this->table} WHERE id = :id AND customer_id = :customer_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $orderId, 'customer_id' => $customerId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Check if an order belongs to a specific kitchen
     */
    public function belongsToKitchen(int $orderId, int $kitchenId): bool {
        $sql = "SELECT id FROM {$this->table} WHERE id = :id AND kitchen_id = :kitchen_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $orderId, 'kitchen_id' => $kitchenId]);
        return $stmt->fetch() !== false;
    }

    // ─── Phase 9: Analytics Methods ───────────────────────────────────────────

    /**
     * Get top-selling menu items for a kitchen (by quantity ordered across delivered orders)
     */
    public function getTopSellingItems(int $kitchenId, int $limit = 5): array {
        $sql = "SELECT mi.item_name, mi.image, mi.is_veg,
                       SUM(oi.quantity) AS total_qty,
                       SUM(oi.subtotal) AS total_revenue
                FROM order_items oi
                JOIN orders o  ON oi.order_id = o.id
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE o.kitchen_id = :kitchen_id
                  AND o.order_status = 'delivered'
                GROUP BY oi.menu_item_id, mi.item_name, mi.image, mi.is_veg
                ORDER BY total_qty DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Get daily earnings for the last 7 days for a kitchen (for mini chart)
     */
    public function getEarningsLast7Days(int $kitchenId): array {
        $sql = "SELECT DATE(created_at) AS day, SUM(total_amount) AS daily_total
                FROM {$this->table}
                WHERE kitchen_id = :kitchen_id
                  AND order_status = 'delivered'
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(created_at)
                ORDER BY day ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $rows = $stmt->fetchAll();

        // Build a full 7-day indexed array (fill missing days with 0)
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $result[date('Y-m-d', strtotime("-{$i} days"))] = 0;
        }
        foreach ($rows as $row) {
            $result[$row['day']] = (float) $row['daily_total'];
        }
        return $result;
    }

    /**
     * Get this month's earnings vs last month's earnings for a kitchen
     */
    public function getMonthlyEarningsComparison(int $kitchenId): array {
        $sql = "SELECT
                    SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN total_amount ELSE 0 END) AS this_month,
                    SUM(CASE WHEN MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN total_amount ELSE 0 END) AS last_month
                FROM {$this->table}
                WHERE kitchen_id = :kitchen_id AND order_status = 'delivered'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return [
            'this_month' => (float) ($result['this_month'] ?? 0),
            'last_month' => (float) ($result['last_month'] ?? 0),
        ];
    }

    /**
     * Platform-wide total revenue (admin analytics)
     */
    public function getPlatformRevenue(): array {
        $sql = "SELECT
                    SUM(total_amount) AS total,
                    SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN total_amount ELSE 0 END) AS this_month,
                    SUM(CASE WHEN MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN total_amount ELSE 0 END) AS last_month,
                    COUNT(*) AS total_orders,
                    AVG(total_amount) AS avg_order_value
                FROM {$this->table}
                WHERE order_status = 'delivered'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return [
            'total'           => (float)  ($result['total']           ?? 0),
            'this_month'      => (float)  ($result['this_month']      ?? 0),
            'last_month'      => (float)  ($result['last_month']      ?? 0),
            'total_orders'    => (int)    ($result['total_orders']    ?? 0),
            'avg_order_value' => (float)  ($result['avg_order_value'] ?? 0),
        ];
    }

    /**
     * Platform-wide order status breakdown (admin funnel)
     */
    public function getPlatformOrderStats(): array {
        $sql = "SELECT order_status, COUNT(*) AS cnt FROM {$this->table} GROUP BY order_status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) { $map[$row['order_status']] = (int) $row['cnt']; }
        return $map;
    }

    /**
     * Top kitchens by revenue (admin analytics)
     */
    public function getTopKitchensByRevenue(int $limit = 5): array {
        $sql = "SELECT k.kitchen_name, k.city, u.full_name AS chef_name,
                       COUNT(o.id) AS order_count,
                       SUM(o.total_amount) AS total_revenue
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN users u    ON k.user_id    = u.id
                WHERE o.order_status = 'delivered'
                GROUP BY o.kitchen_id, k.kitchen_name, k.city, u.full_name
                ORDER BY total_revenue DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Monthly order counts for last N months (admin trend chart)
     */
    public function getMonthlyOrderTrend(int $months = 6): array {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                       DATE_FORMAT(created_at, '%b %Y')   AS month_label,
                       COUNT(*) AS order_count,
                       SUM(total_amount) AS revenue
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)
                GROUP BY month_key, month_label
                ORDER BY month_key ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * All platform orders for admin listing with details
     */
    public function findAllWithDetails(?string $status = null, int $limit = 100): array {
        $sql = "SELECT o.*, k.kitchen_name, u.full_name AS customer_name
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN users u    ON o.customer_id = u.id";
        $params = [];

        if ($status !== null) {
            $sql .= " WHERE o.order_status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY o.created_at DESC LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

