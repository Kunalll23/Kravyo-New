<?php
/**
 * Kravyo - Review Model (Customer Reviews & Ratings)
 * Phase 9: Full CRUD + analytics methods added
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

    // ─── Phase 9 additions ────────────────────────────────────────────────────

    /**
     * Check whether a review already exists for a specific order
     */
    public function hasReviewedOrder(int $orderId): bool {
        $sql = "SELECT id FROM {$this->table} WHERE order_id = :order_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Find the review for a given order (returns null if not reviewed yet)
     */
    public function findByOrderId(int $orderId): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = :order_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Submit a new customer review
     */
    public function submitReview(array $data): int|string {
        return $this->create($data);
    }

    /**
     * Get recent reviews for a kitchen (chef dashboard panel)
     */
    public function findRecentByKitchenId(int $kitchenId, int $limit = 3): array {
        $sql = "SELECT r.*, u.full_name AS customer_name
                FROM {$this->table} r
                JOIN users u ON r.customer_id = u.id
                WHERE r.kitchen_id = :kitchen_id
                ORDER BY r.created_at DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Get star-rating distribution for a kitchen (1–5 star counts for display)
     */
    public function getRatingDistribution(int $kitchenId): array {
        $sql = "SELECT rating, COUNT(*) as count
                FROM {$this->table}
                WHERE kitchen_id = :kitchen_id
                GROUP BY rating
                ORDER BY rating DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $rows = $stmt->fetchAll();

        // Normalize to 5-star indexed array
        $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($rows as $row) {
            $dist[(int) $row['rating']] = (int) $row['count'];
        }
        return $dist;
    }
}
