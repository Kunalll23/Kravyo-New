<?php
/**
 * Kravyo - SubscriptionDelivery Model
 * Tracks daily deliveries for active tiffin subscriptions
 */

class SubscriptionDelivery extends Model {
    protected string $table = 'subscription_deliveries';

    /**
     * Get today's deliveries for a specific kitchen
     */
    public function getTodaysDeliveries(int $kitchenId): array {
        $today = date('Y-m-d');
        // First, auto-generate missing delivery records for today for all active subscriptions
        $this->generateTodaysDeliveries($kitchenId, $today);

        // Fetch them
        $sql = "SELECT sd.*, 
                       cs.id as sub_id, cs.special_instructions, cs.start_date, cs.end_date,
                       u.full_name as customer_name, u.phone as customer_phone,
                       a.street_address, a.landmark, a.city as delivery_city,
                       s.plan_name, s.meals_per_day, s.plan_type
                FROM {$this->table} sd
                JOIN customer_subscriptions cs ON sd.customer_subscription_id = cs.id
                JOIN users u ON cs.customer_id = u.id
                JOIN addresses a ON cs.address_id = a.id
                JOIN tiffin_subscriptions s ON cs.subscription_plan_id = s.id
                WHERE sd.kitchen_id = :kitchen_id 
                  AND sd.delivery_date = :today
                  AND cs.status = 'active'
                ORDER BY sd.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId, 'today' => $today]);
        return $stmt->fetchAll();
    }

    /**
     * Auto-generate missing delivery records for today for all active subscriptions
     */
    private function generateTodaysDeliveries(int $kitchenId, string $date): void {
        // Find all active subscriptions for this kitchen that span across today
        $sql = "SELECT id FROM customer_subscriptions 
                WHERE kitchen_id = ? 
                  AND status = 'active'
                  AND ? BETWEEN start_date AND end_date
                  AND id NOT IN (
                      SELECT customer_subscription_id FROM {$this->table} 
                      WHERE delivery_date = ? AND kitchen_id = ?
                  )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$kitchenId, $date, $date, $kitchenId]);
        $missingSubs = $stmt->fetchAll();

        // Create pending delivery records for them
        if (!empty($missingSubs)) {
            $insertSql = "INSERT INTO {$this->table} (customer_subscription_id, kitchen_id, delivery_date, status) VALUES ";
            $values = [];
            $params = [];
            foreach ($missingSubs as $sub) {
                $values[] = "(?, ?, ?, 'pending')";
                $params[] = $sub['id'];
                $params[] = $kitchenId;
                $params[] = $date;
            }
            $insertSql .= implode(', ', $values);
            $insertStmt = $this->db->prepare($insertSql);
            $insertStmt->execute($params);
        }
    }

    /**
     * Update delivery status
     */
    public function updateStatus(int $id, string $status, int $kitchenId): bool {
        $sql = "UPDATE {$this->table} 
                SET status = :status 
                WHERE id = :id AND kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'id' => $id,
            'kitchen_id' => $kitchenId
        ]);
    }

    /**
     * Get today's delivery status for a specific customer subscription
     */
    public function getTodaysStatusForSubscription(int $subscriptionId): ?string {
        $today = date('Y-m-d');
        $sql = "SELECT status FROM {$this->table} 
                WHERE customer_subscription_id = :sub_id AND delivery_date = :today 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sub_id' => $subscriptionId, 'today' => $today]);
        $result = $stmt->fetch();
        return $result ? $result['status'] : null;
    }
}
