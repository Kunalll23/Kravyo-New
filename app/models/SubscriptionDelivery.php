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
                       s.plan_name, s.meals_per_day, s.plan_type,
                       s.breakfast_time, s.lunch_time, s.dinner_time
                FROM {$this->table} sd
                JOIN customer_subscriptions cs ON sd.customer_subscription_id = cs.id
                JOIN customers u ON cs.customer_id = u.id
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
    public function generateTodaysDeliveries(int $kitchenId, string $date): void {
        // Find all active subscriptions for this kitchen that span across today
        $sql = "SELECT cs.id, s.meals_per_day, s.breakfast_time, s.lunch_time, s.dinner_time 
                FROM customer_subscriptions cs
                JOIN tiffin_subscriptions s ON cs.subscription_plan_id = s.id
                WHERE cs.kitchen_id = ? 
                  AND cs.status = 'active'
                  AND ? BETWEEN cs.start_date AND cs.end_date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$kitchenId, $date]);
        $activeSubs = $stmt->fetchAll();

        if (empty($activeSubs)) {
            return;
        }

        // Fetch already generated deliveries for today to avoid duplicates
        $existingSql = "SELECT customer_subscription_id, meal_type FROM {$this->table} 
                        WHERE kitchen_id = ? AND delivery_date = ?";
        $existingStmt = $this->db->prepare($existingSql);
        $existingStmt->execute([$kitchenId, $date]);
        $existingRows = $existingStmt->fetchAll();
        
        $existingMap = [];
        foreach ($existingRows as $row) {
            $existingMap[$row['customer_subscription_id']][] = $row['meal_type'];
        }

        $values = [];
        $params = [];
        foreach ($activeSubs as $sub) {
            $subId = $sub['id'];
            $meals = (int)$sub['meals_per_day'];
            $existingMeals = $existingMap[$subId] ?? [];
            
            $mealTypesToGenerate = [];
            if ($meals == 1) {
                $mealTypesToGenerate = ['lunch'];
            } elseif ($meals == 2) {
                $mealTypesToGenerate = ['lunch', 'dinner'];
            } elseif ($meals >= 3) {
                $mealTypesToGenerate = ['breakfast', 'lunch', 'dinner'];
            }
            
            foreach ($mealTypesToGenerate as $mealType) {
                if (!in_array($mealType, $existingMeals)) {
                    $delivTime = match($mealType) {
                        'breakfast' => !empty($sub['breakfast_time']) ? $sub['breakfast_time'] : '08:00 AM - 09:00 AM',
                        'lunch'     => !empty($sub['lunch_time']) ? $sub['lunch_time'] : '12:30 PM - 01:30 PM',
                        'dinner'    => !empty($sub['dinner_time']) ? $sub['dinner_time'] : '07:30 PM - 08:30 PM',
                        default     => '12:30 PM - 01:30 PM'
                    };

                    $values[] = "(?, ?, ?, ?, ?, 'pending')";
                    $params[] = $subId;
                    $params[] = $kitchenId;
                    $params[] = $date;
                    $params[] = $mealType;
                    $params[] = $delivTime;
                }
            }
        }

        // Create pending delivery records for them
        if (!empty($values)) {
            $insertSql = "INSERT IGNORE INTO {$this->table} (customer_subscription_id, kitchen_id, delivery_date, meal_type, delivery_time, status) VALUES ";
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
