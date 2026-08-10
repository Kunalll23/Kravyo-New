<?php
/**
 * Kravyo - CustomerSubscription Model
 * Phase 7: Tracks customer subscriptions to tiffin plans
 */

class CustomerSubscription extends Model {
    protected string $table = 'customer_subscriptions';

    /**
     * Find all subscriptions for a customer with plan and kitchen details
     */
    public function findByCustomerId(int $customerId, ?string $status = null): array {
        $sql = "SELECT cs.*, s.plan_name, s.plan_type, s.meals_per_day, s.price AS plan_price,
                       k.kitchen_name, k.city, k.pincode,
                       a.street_address, a.city AS delivery_city
                FROM {$this->table} cs
                JOIN tiffin_subscriptions s ON cs.subscription_plan_id = s.id
                JOIN kitchens k ON cs.kitchen_id = k.id
                JOIN addresses a ON cs.address_id = a.id
                WHERE cs.customer_id = :customer_id";
        $params = ['customer_id' => $customerId];

        if ($status !== null) {
            $sql .= " AND cs.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY cs.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find all subscribers for a kitchen with customer and plan details
     */
    public function findByKitchenId(int $kitchenId, ?string $status = null): array {
        $sql = "SELECT cs.*, s.plan_name, s.plan_type, s.meals_per_day,
                       u.full_name AS customer_name, u.phone AS customer_phone, u.email AS customer_email,
                       a.street_address, a.landmark, a.city AS delivery_city, a.pincode AS delivery_pincode
                FROM {$this->table} cs
                JOIN tiffin_subscriptions s ON cs.subscription_plan_id = s.id
                JOIN users u ON cs.customer_id = u.id
                JOIN addresses a ON cs.address_id = a.id
                WHERE cs.kitchen_id = :kitchen_id";
        $params = ['kitchen_id' => $kitchenId];

        if ($status !== null) {
            $sql .= " AND cs.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY cs.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Check if customer already has an active subscription to a kitchen
     */
    public function findActiveByCustomerAndKitchen(int $customerId, int $kitchenId): ?array {
        $sql = "SELECT cs.*, s.plan_name
                FROM {$this->table} cs
                JOIN tiffin_subscriptions s ON cs.subscription_plan_id = s.id
                WHERE cs.customer_id = :customer_id
                  AND cs.kitchen_id = :kitchen_id
                  AND cs.status = 'active'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'kitchen_id' => $kitchenId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Find a single subscription with full details
     */
    public function findWithDetails(int $id): ?array {
        $sql = "SELECT cs.*, s.plan_name, s.plan_type, s.meals_per_day, s.price AS plan_price, s.description AS plan_description,
                       k.kitchen_name, k.city AS kitchen_city, k.pincode AS kitchen_pincode,
                       u.full_name AS customer_name, u.phone AS customer_phone,
                       a.street_address, a.landmark, a.city AS delivery_city, a.pincode AS delivery_pincode
                FROM {$this->table} cs
                JOIN tiffin_subscriptions s ON cs.subscription_plan_id = s.id
                JOIN kitchens k ON cs.kitchen_id = k.id
                JOIN users u ON cs.customer_id = u.id
                JOIN addresses a ON cs.address_id = a.id
                WHERE cs.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Count active subscriptions for a kitchen
     */
    public function countActiveByKitchenId(int $kitchenId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE kitchen_id = :kitchen_id AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Update subscription status
     */
    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * Check if a subscription belongs to a customer (security check)
     */
    public function belongsToCustomer(int $subId, int $customerId): bool {
        $sql = "SELECT id FROM {$this->table} WHERE id = :id AND customer_id = :customer_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $subId, 'customer_id' => $customerId]);
        return $stmt->fetch() !== false;
    }
}
