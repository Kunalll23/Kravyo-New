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
}
