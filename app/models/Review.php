<?php
/**
 * Kravyo - Review Model (Customer Reviews & Ratings)
 */

class Review extends Model {
    protected string $table = 'reviews';

    /**
     * Get all reviews for a specific kitchen with customer name
     */
    public function findByKitchenId(int $kitchenId, int $limit = 0): array {
        $sql = "SELECT r.*, u.full_name AS customer_name
                FROM {$this->table} r
                JOIN users u ON r.customer_id = u.id
                WHERE r.kitchen_id = :kitchen_id
                ORDER BY r.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Get average rating for a kitchen
     */
    public function getAverageRating(int $kitchenId): float {
        $sql = "SELECT AVG(rating) as avg_rating FROM {$this->table} WHERE kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return round((float) ($result['avg_rating'] ?? 0), 1);
    }

    /**
     * Count total reviews for a kitchen
     */
    public function countByKitchenId(int $kitchenId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }
}
