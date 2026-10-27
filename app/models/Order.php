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
                       k.kitchen_name, k.id AS kitchen_id_ref, k.latitude AS kitchen_lat, k.longitude AS kitchen_lng,
                       u.full_name AS customer_name, u.phone AS customer_phone, u.email AS customer_email,
                       a.street_address, a.landmark, a.city AS delivery_city,
                       a.pincode AS delivery_pincode, a.address_type,
                       du.full_name AS delivery_partner_name, du.phone AS delivery_partner_phone, dp.vehicle_number, dp.vehicle_type
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN customers u ON o.customer_id = u.id
                JOIN addresses a ON o.address_id = a.id
                LEFT JOIN delivery_partners dp ON o.delivery_partner_id = dp.id
                LEFT JOIN delivery_users du ON dp.user_id = du.id
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
    public function findByCustomerId(int $customerId, ?string $status = null, ?string $dateFilter = null): array {
        $sql = "SELECT o.*, k.kitchen_name
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                WHERE o.customer_id = :customer_id";
        $params = ['customer_id' => $customerId];

        if ($status !== null) {
            $sql .= " AND o.order_status = :status";
            $params['status'] = $status;
        }

        if ($dateFilter !== null) {
            $sql .= " AND DATE(o.created_at) = :date_filter";
            $params['date_filter'] = $dateFilter;
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find all orders for a kitchen (chef order management)
     */
    public function findByKitchenId(int $kitchenId, ?string $status = null, ?string $search = null): array {
        $sql = "SELECT o.*, u.full_name AS customer_name, u.phone AS customer_phone,
                       a.street_address, a.landmark, a.city AS delivery_city, a.pincode AS delivery_pincode,
                       du.full_name AS delivery_partner_name, du.phone AS delivery_partner_phone
                FROM {$this->table} o
                JOIN customers u ON o.customer_id = u.id
                JOIN addresses a ON o.address_id = a.id
                LEFT JOIN delivery_partners dp ON o.delivery_partner_id = dp.id
                LEFT JOIN delivery_users du ON dp.user_id = du.id
                WHERE o.kitchen_id = :kitchen_id";
        $params = ['kitchen_id' => $kitchenId];

        if ($status !== null) {
            $sql .= " AND o.order_status = :status";
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (o.order_number LIKE :search1 OR u.full_name LIKE :search2 OR u.phone LIKE :search3)";
            $term = '%' . trim($search) . '%';
            $params['search1'] = $term;
            $params['search2'] = $term;
            $params['search3'] = $term;
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
     * Save the Razorpay payment_id against an order after successful payment verification.
     * This ID is required later to issue automated refunds via the Razorpay Refund API.
     */
    public function saveRazorpayPaymentId(int $id, string $paymentId): bool {
        $sql  = "UPDATE {$this->table} SET razorpay_payment_id = :payment_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['payment_id' => $paymentId, 'id' => $id]);
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
     * Get order counts grouped by status in a single query for chef dashboard/orders tabs
     */
    public function getStatusCountsByKitchenId(int $kitchenId): array {
        $sql = "SELECT order_status, COUNT(*) as cnt FROM {$this->table} WHERE kitchen_id = :kitchen_id GROUP BY order_status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $rows = $stmt->fetchAll();

        $counts = [
            'all'              => 0,
            'pending'          => 0,
            'accepted'         => 0,
            'preparing'        => 0,
            'out_for_delivery' => 0,
            'delivered'        => 0,
            'cancelled'        => 0,
        ];

        foreach ($rows as $row) {
            $st = $row['order_status'];
            $c  = (int) $row['cnt'];
            $counts['all'] += $c;
            if (array_key_exists($st, $counts)) {
                $counts[$st] = $c;
            }
        }

        return $counts;
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
     * Get this month's earnings vs last month's earnings for a kitchen
     */
    public function getMonthlyEarningsComparison(int $kitchenId): array {
        $sql = "SELECT
                    SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN total_amount ELSE 0 END) AS this_month,
                    SUM(CASE WHEN created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01') AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN total_amount ELSE 0 END) AS last_month
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
     * Get filtered order analytics for a kitchen within a date range
     */
    public function getFilteredAnalytics(int $kitchenId, string $startDate, string $endDate): array {
        $sql = "SELECT 
                    COUNT(*) AS total_placed_orders,
                    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) AS delivered_orders,
                    SUM(CASE WHEN order_status = 'delivered' THEN total_amount ELSE 0 END) AS total_earnings,
                    SUM(CASE 
                        WHEN order_status = 'delivered' AND payment_status = 'completed' AND payment_method = 'split' THEN paid_online_amount
                        WHEN order_status = 'delivered' AND payment_status = 'completed' AND payment_method != 'cod' AND payment_method != 'split' THEN total_amount 
                        ELSE 0 
                    END) AS online_settled,
                    SUM(CASE 
                        WHEN order_status = 'delivered' AND payment_status = 'completed' AND payment_method = 'split' THEN paid_cash_amount
                        WHEN order_status = 'delivered' AND (payment_method = 'cod' OR payment_status = 'pending') THEN total_amount 
                        ELSE 0 
                    END) AS cod_pending,
                    SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
                FROM {$this->table}
                WHERE kitchen_id = :kitchen_id
                  AND created_at >= :start_date 
                  AND created_at <= :end_date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'kitchen_id' => $kitchenId,
            'start_date' => $startDate . ' 00:00:00',
            'end_date'   => $endDate . ' 23:59:59'
        ]);
        $res = $stmt->fetch() ?: [];

        $delivered = (int)($res['delivered_orders'] ?? 0);
        $earnings  = (float)($res['total_earnings'] ?? 0.0);
        $aov       = $delivered > 0 ? round($earnings / $delivered, 2) : 0.0;
        $online    = (float)($res['online_settled'] ?? 0.0);
        $cod       = (float)($res['cod_pending'] ?? 0.0);
        // Kravyo operates with 0% platform commission for home chefs (100% chef earnings)
        $platformFee = 0.0;
        $netPayable  = $earnings;

        return [
            'total_placed_orders' => (int)($res['total_placed_orders'] ?? 0),
            'delivered_orders'    => $delivered,
            'total_earnings'      => $earnings,
            'avg_order_value'     => $aov,
            'online_settled'      => $online,
            'cod_pending'         => $cod,
            'platform_fee'        => 0.0,
            'net_payable'         => $netPayable,
            'cancelled_orders'    => (int)($res['cancelled_orders'] ?? 0),
        ];
    }

    /**
     * Get the earliest order date for a kitchen
     */
    public function getEarliestOrderDate(int $kitchenId): ?string {
        $sql = "SELECT MIN(created_at) AS first_date FROM {$this->table} WHERE kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $res = $stmt->fetch();
        return !empty($res['first_date']) ? (string)$res['first_date'] : null;
    }

    /**
     * Get time-series daily analytics (revenue & order volume) for Chart.js
     */
    public function getChartDataByRange(int $kitchenId, string $startDate, string $endDate): array {
        $sql = "SELECT DATE(created_at) AS day, 
                       SUM(CASE WHEN order_status = 'delivered' THEN total_amount ELSE 0 END) AS revenue,
                       SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) AS orders_count
                FROM {$this->table}
                WHERE kitchen_id = :kitchen_id
                  AND created_at >= :start_date 
                  AND created_at <= :end_date
                GROUP BY DATE(created_at)
                ORDER BY day ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'kitchen_id' => $kitchenId,
            'start_date' => $startDate . ' 00:00:00',
            'end_date'   => $endDate . ' 23:59:59'
        ]);
        $rows = $stmt->fetchAll();

        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['day']] = [
                'revenue' => (float)$r['revenue'],
                'orders'  => (int)$r['orders_count']
            ];
        }

        // Fill continuous dates in range
        $start = new DateTime($startDate);
        $end   = new DateTime($endDate);
        $end->modify('+1 day'); // inclusive

        $labels      = [];
        $fullDates   = [];
        $revenues    = [];
        $orderCounts = [];

        $interval = new DateInterval('P1D');
        $period   = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            $dayKey        = $dt->format('Y-m-d');
            $fullDates[]   = $dayKey;
            $labels[]      = $dt->format('d M');
            $revenues[]    = $byDate[$dayKey]['revenue'] ?? 0.0;
            $orderCounts[] = $byDate[$dayKey]['orders'] ?? 0;
        }

        return [
            'labels'       => $labels,
            'full_dates'   => $fullDates,
            'revenues'     => $revenues,
            'orders'       => $orderCounts,
            'total_rev'    => array_sum($revenues),
            'total_orders' => array_sum($orderCounts),
            'max_rev'      => !empty($revenues) ? max($revenues) : 0,
            'max_orders'   => !empty($orderCounts) ? max($orderCounts) : 0,
        ];
    }

    /**
     * Get top-selling menu items for a kitchen within a specific date range
     */
    public function getTopSellingItemsByRange(int $kitchenId, string $startDate, string $endDate, int $limit = 5): array {
        $sql = "SELECT mi.item_name, mi.image, mi.is_veg,
                       SUM(oi.quantity) AS total_qty,
                       SUM(oi.subtotal) AS total_revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE o.kitchen_id = :kitchen_id
                  AND o.order_status = 'delivered'
                  AND o.created_at >= :start_date
                  AND o.created_at <= :end_date
                GROUP BY oi.menu_item_id, mi.item_name, mi.image, mi.is_veg
                ORDER BY total_qty DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'kitchen_id' => $kitchenId,
            'start_date' => $startDate . ' 00:00:00',
            'end_date'   => $endDate . ' 23:59:59'
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Get orders for CSV report export within a date range
     */
    public function getOrdersForExport(int $kitchenId, string $startDate, string $endDate): array {
        $sql = "SELECT o.order_number, o.created_at, o.order_type,
                       COALESCE(u.full_name, 'Guest Customer') AS customer_name,
                       o.total_amount, o.payment_method, o.payment_status, o.order_status
                FROM {$this->table} o
                LEFT JOIN customers u ON o.customer_id = u.id
                WHERE o.kitchen_id = :kitchen_id
                  AND o.created_at >= :start_date
                  AND o.created_at <= :end_date
                ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'kitchen_id' => $kitchenId,
            'start_date' => $startDate . ' 00:00:00',
            'end_date'   => $endDate . ' 23:59:59'
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Platform-wide total revenue (admin analytics)
     */
    public function getPlatformRevenue(): array {
        $sql = "SELECT
                    SUM(total_amount) AS total,
                    SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN total_amount ELSE 0 END) AS this_month,
                    SUM(CASE WHEN created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01') AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN total_amount ELSE 0 END) AS last_month,
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
                JOIN chefs u    ON k.user_id    = u.id
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
     * Admin: Force-update an order's status with an optional admin note.
     * Stores the note in the `notes` column if it exists.
     */
    public function adminUpdateStatus(int $id, string $newStatus, string $adminNote = ''): bool {
        $sql = "UPDATE {$this->table}
                SET order_status = :status
                    -- , notes = :note   (uncomment if you add a notes column)
                WHERE id = :id";
        // Simplified version without notes column (safe for current schema)
        $sql = "UPDATE {$this->table} SET order_status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['status' => $newStatus, 'id' => $id]);
    }

    /**
     * Admin: Find a single order by ID with full customer + kitchen details.
     */
    public function findByIdWithDetails(int $id): ?array {
        $sql = "SELECT o.*, k.kitchen_name, u.full_name AS customer_name, u.email AS customer_email,
                       du.full_name AS delivery_partner_name, dp.vehicle_number
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN customers u ON o.customer_id = u.id
                LEFT JOIN delivery_partners dp ON o.delivery_partner_id = dp.id
                LEFT JOIN delivery_users du ON dp.user_id = du.id
                WHERE o.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * All platform orders for admin listing with details and advanced filtering
     */
    public function findAllWithDetails(
        ?string $status = null,
        ?string $search = null,
        int $limit = 200,
        ?string $city = null,
        ?string $dispatchStatus = null,
        ?int $kitchenId = null,
        ?string $paymentStatus = null,
        ?string $dateRange = null
    ): array {
        $sql = "SELECT o.*, 
                       k.kitchen_name, k.city AS kitchen_city,
                       u.full_name AS customer_name, u.phone AS customer_phone,
                       du.full_name AS delivery_partner_name, du.phone AS delivery_partner_phone,
                       a.city AS delivery_city
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN customers u ON o.customer_id = u.id
                LEFT JOIN addresses a ON o.address_id = a.id
                LEFT JOIN delivery_partners dp ON o.delivery_partner_id = dp.id
                LEFT JOIN delivery_users du ON dp.user_id = du.id
                WHERE 1=1";
        $params = [];

        // Order Status Filter
        if ($status !== null && $status !== '' && $status !== 'all') {
            $sql .= " AND o.order_status = :status";
            $params['status'] = $status;
        }

        // Search Filter (Order #, Customer Name, Customer Phone, Kitchen Name)
        if ($search !== null && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $sql .= " AND (o.order_number LIKE :search1 OR u.full_name LIKE :search2 OR u.phone LIKE :search3 OR k.kitchen_name LIKE :search4)";
            $params['search1'] = $term;
            $params['search2'] = $term;
            $params['search3'] = $term;
            $params['search4'] = $term;
        }

        // City Filter
        if ($city !== null && $city !== '' && $city !== 'all') {
            $sql .= " AND (k.city = :city1 OR a.city = :city2)";
            $params['city1'] = $city;
            $params['city2'] = $city;
        }

        // Delivery / Dispatch Status Filter
        if ($dispatchStatus !== null && $dispatchStatus !== '' && $dispatchStatus !== 'all') {
            if ($dispatchStatus === 'no_rider') {
                $sql .= " AND o.dispatch_status = 'no_rider'";
            } elseif ($dispatchStatus === 'auto_assigning') {
                $sql .= " AND o.dispatch_status IN ('cascading', 'broadcasting')";
            } elseif ($dispatchStatus === 'assigned') {
                $sql .= " AND o.dispatch_status = 'assigned'";
            } elseif ($dispatchStatus === 'out_for_delivery') {
                $sql .= " AND o.delivery_status = 'out_for_delivery'";
            } elseif ($dispatchStatus === 'unassigned') {
                $sql .= " AND (o.delivery_partner_id IS NULL OR o.delivery_partner_id = 0)";
            }
        }

        // Kitchen Filter
        if ($kitchenId !== null && $kitchenId > 0) {
            $sql .= " AND o.kitchen_id = :kitchen_id";
            $params['kitchen_id'] = $kitchenId;
        }

        // Payment Status Filter
        if ($paymentStatus !== null && $paymentStatus !== '' && $paymentStatus !== 'all') {
            $sql .= " AND o.payment_status = :payment_status";
            $params['payment_status'] = $paymentStatus;
        }

        // Date Range Filter
        if ($dateRange !== null && $dateRange !== '' && $dateRange !== 'all') {
            if ($dateRange === 'today') {
                $sql .= " AND DATE(o.created_at) = CURDATE()";
            } elseif ($dateRange === 'yesterday') {
                $sql .= " AND DATE(o.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            } elseif ($dateRange === '7days') {
                $sql .= " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            } elseif ($dateRange === 'this_month') {
                $sql .= " AND o.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00')";
            }
        }

        $sql .= " ORDER BY o.created_at DESC LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─── Delivery Partner Module Methods ──────────────────────────────────────

    /**
     * Assign a delivery partner to an order and generate delivery OTP
     */
    public function assignDeliveryPartner(int $orderId, int $deliveryPartnerId): bool {
        $otp = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $sql = "UPDATE {$this->table}
                SET delivery_partner_id = :dp_id,
                    delivery_otp = :otp,
                    delivery_status = 'assigned',
                    offered_driver_id = NULL,
                    offer_expires_at = NULL
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'dp_id' => $deliveryPartnerId,
            'otp'   => $otp,
            'id'    => $orderId
        ]);
    }

    /**
     * Update delivery status with timestamp tracking
     */
    public function updateDeliveryStatus(int $orderId, string $deliveryStatus, ?string $orderStatus = null, ?string $failReason = null): bool {
        $fields = ['delivery_status = :delivery_status'];
        $params = ['delivery_status' => $deliveryStatus, 'id' => $orderId];

        if ($orderStatus !== null) {
            $fields[] = 'order_status = :order_status';
            $params['order_status'] = $orderStatus;
        }

        if ($deliveryStatus === 'accepted') {
            $fields[] = 'delivery_accepted_at = NOW()';
        } elseif ($deliveryStatus === 'picked_up') {
            $fields[] = 'delivery_picked_up_at = NOW()';
        } elseif ($deliveryStatus === 'delivered' || $deliveryStatus === 'failed') {
            $fields[] = 'delivery_completed_at = NOW()';
        }

        if ($failReason !== null) {
            $fields[] = 'delivery_failed_reason = :fail_reason';
            $params['fail_reason'] = $failReason;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }


    /**
     * Find active (non-completed) delivery orders for a partner
     */
    public function findActiveDeliveries(int $deliveryPartnerId): array {
        $sql = "SELECT o.*, k.kitchen_name, k.address AS kitchen_address, k.city AS kitchen_city,
                       k.latitude AS kitchen_lat, k.longitude AS kitchen_lng,
                       u.full_name AS customer_name, u.phone AS customer_phone,
                       a.street_address, a.landmark, a.city AS delivery_city, a.pincode AS delivery_pincode
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN customers u ON o.customer_id = u.id
                JOIN addresses a ON o.address_id = a.id
                WHERE o.delivery_partner_id = :dp_id
                  AND o.delivery_status IN ('assigned','accepted','picked_up','out_for_delivery')
                ORDER BY
                    FIELD(o.delivery_status, 'out_for_delivery','picked_up','accepted','assigned'),
                    o.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dp_id' => $deliveryPartnerId]);
        return $stmt->fetchAll();
    }

    /**
     * Find completed/failed deliveries for a partner (history)
     */
    public function findDeliveryHistory(int $deliveryPartnerId, int $limit = 50): array {
        $sql = "SELECT o.*, k.kitchen_name, u.full_name AS customer_name
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN customers u ON o.customer_id = u.id
                WHERE o.delivery_partner_id = :dp_id
                  AND o.delivery_status IN ('delivered','failed')
                ORDER BY o.delivery_completed_at DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dp_id' => $deliveryPartnerId]);
        return $stmt->fetchAll();
    }

    /**
     * Count active deliveries for a partner
     */
    public function countActiveDeliveries(int $deliveryPartnerId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE delivery_partner_id = :dp_id
                  AND delivery_status IN ('assigned','accepted','picked_up','out_for_delivery')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dp_id' => $deliveryPartnerId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Count completed deliveries for a partner
     */
    public function countCompletedDeliveries(int $deliveryPartnerId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE delivery_partner_id = :dp_id AND delivery_status = 'delivered'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dp_id' => $deliveryPartnerId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Get total earnings for a delivery partner
     */
    public function getDeliveryEarnings(int $deliveryPartnerId): float {
        $sql = "SELECT SUM(total_amount) as total FROM {$this->table}
                WHERE delivery_partner_id = :dp_id AND delivery_status = 'delivered'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dp_id' => $deliveryPartnerId]);
        $result = $stmt->fetch();
        return round((float) ($result['total'] ?? 0), 2);
    }

    /**
     * Find order with full details including delivery partner info
     */
    public function findWithDeliveryDetails(int $id): ?array {
        $sql = "SELECT o.*,
                       COALESCE(o.delivery_latitude, a.latitude) AS delivery_latitude,
                       COALESCE(o.delivery_longitude, a.longitude) AS delivery_longitude,
                       k.kitchen_name, k.address AS kitchen_address, k.city AS kitchen_city,
                       k.latitude AS kitchen_lat, k.longitude AS kitchen_lng,
                       u.full_name AS customer_name, u.phone AS customer_phone, u.email AS customer_email,
                       a.street_address, a.landmark, a.city AS delivery_city, a.pincode AS delivery_pincode,
                       dp.id AS dp_id, dp.current_latitude AS delivery_partner_lat, dp.current_longitude AS delivery_partner_lng, dp_user.full_name AS delivery_partner_name, dp_user.phone AS delivery_partner_phone
                FROM {$this->table} o
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN customers u ON o.customer_id = u.id
                JOIN addresses a ON o.address_id = a.id
                LEFT JOIN delivery_partners dp ON o.delivery_partner_id = dp.id
                LEFT JOIN delivery_users dp_user ON dp.user_id = dp_user.id
                WHERE o.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }


    // ─── Broadcast Dispatch Methods ───────────────────────────────────────────

    /**
     * Atomically claim an order for a delivery partner
     */
    public function claimForPartner(int $orderId, int $partnerId): bool {
        $sql = "UPDATE {$this->table}
                SET delivery_partner_id = :dp_id,
                    dispatch_status = 'assigned'
                WHERE id = :id AND delivery_partner_id IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dp_id' => $partnerId, 'id' => $orderId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update dispatch tracking fields
     */
    public function setDispatchStatus(int $orderId, string $status, ?int $round = null): bool {
        $fields = ["dispatch_status = :status"];
        $params = ['status' => $status, 'id' => $orderId];

        if ($status === 'broadcasting' || $status === 'cascading') {
            $fields[] = "dispatch_started_at = NOW()";
        }
        
        if ($round !== null) {
            $fields[] = "dispatch_round = :round";
            $params['round'] = $round;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Set a targeted cascade offer for a specific driver with countdown window
     */
    public function setCascadeOffer(int $orderId, int $partnerId, int $round, int $timeoutSeconds = 25): bool {
        $sql = "UPDATE {$this->table}
                SET dispatch_status = 'cascading',
                    dispatch_round = :round,
                    offered_driver_id = :dp_id,
                    offer_expires_at = DATE_ADD(NOW(), INTERVAL :timeout SECOND),
                    dispatch_started_at = IFNULL(dispatch_started_at, NOW())
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'round'   => $round,
            'dp_id'   => $partnerId,
            'timeout' => $timeoutSeconds,
            'id'      => $orderId
        ]);
    }

    /**
     * Atomically add a driver to the order's rejected_by_drivers JSON array.
     * Uses MySQL JSON_ARRAY_APPEND to avoid the PHP read-modify-write race condition
     * that would allow two concurrent decline events to overwrite each other.
     */
    public function addRejectedDriver(int $orderId, int $partnerId): bool {
        // Single atomic SQL using JSON_ARRAY_APPEND for atomic read-modify-write.
        // Compatible with both MariaDB and MySQL without requiring CAST(... AS JSON).
        $sql = "UPDATE {$this->table}
                SET rejected_by_drivers = JSON_ARRAY_APPEND(
                    COALESCE(NULLIF(rejected_by_drivers, ''), JSON_ARRAY()),
                    '$',
                    CAST(:partner_id AS UNSIGNED)
                )
                WHERE id = :id
                  AND (
                      rejected_by_drivers IS NULL
                      OR rejected_by_drivers = ''
                      OR NOT JSON_CONTAINS(rejected_by_drivers, CAST(:partner_id2 AS CHAR))
                  )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'partner_id'  => $partnerId,
            'partner_id2' => $partnerId,
            'id'          => $orderId,
        ]);
    }

    /**
     * Get rejected driver IDs for an order
     */
    public function getRejectedDriverIds(int $orderId): array {
        $sql = "SELECT rejected_by_drivers FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $orderId]);
        $val = $stmt->fetchColumn();
        if (empty($val)) return [];
        $decoded = json_decode($val, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Clear rejected drivers list on fresh manual retry / re-dispatch
     */
    public function clearRejectedDrivers(int $orderId): bool {
        $sql = "UPDATE {$this->table} SET rejected_by_drivers = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $orderId]);
    }

    /**
     * Get active dispatch orders (both cascading and broadcasting)
     */
    public function getActiveDispatchOrders(): array {
        $sql = "SELECT id, order_number, dispatch_status, dispatch_round, dispatch_started_at,
                       offered_driver_id, offer_expires_at, rejected_by_drivers, kitchen_id,
                       TIMESTAMPDIFF(SECOND, NOW(), offer_expires_at) AS seconds_to_expire,
                       TIMESTAMPDIFF(SECOND, dispatch_started_at, NOW()) AS elapsed_seconds
                FROM {$this->table}
                WHERE dispatch_status IN ('cascading', 'broadcasting')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Atomically cancel an order only if it is still 'pending' AND belongs to the customer.
     * Uses SELECT FOR UPDATE inside a transaction to lock the row, eliminating the
     * Time-Of-Check-To-Time-Of-Use (TOCTOU) race that could trigger a double refund.
     *
     * Must be called inside Database::withTransaction().
     * Returns the locked order row on success, or null if already processed / not pending.
     */
    public function cancelIfPending(int $orderId, int $customerId): ?array {
        $db = Database::getInstance();

        // Lock the row so no concurrent cancel request can sneak through
        $stmt = $db->prepare(
            "SELECT * FROM {$this->table}
             WHERE id = :id AND customer_id = :cid AND order_status = :status
             LIMIT 1 FOR UPDATE"
        );
        $stmt->execute([
            'id'     => $orderId,
            'cid'    => $customerId,
            'status' => ORDER_STATUS_PENDING,
        ]);
        $order = $stmt->fetch();

        if (!$order) {
            return null; // Not pending, not found, or not owned by this customer
        }

        // Safe to cancel — we hold the row lock
        $updateStmt = $db->prepare(
            "UPDATE {$this->table} SET order_status = :cancelled WHERE id = :id"
        );
        $updateStmt->execute([
            'cancelled' => ORDER_STATUS_CANCELLED,
            'id'        => $orderId,
        ]);

        return $order; // Return the original order data (payment info needed for refund)
    }
}
