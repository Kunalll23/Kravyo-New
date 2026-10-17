<?php
/**
 * Kravyo - Address Model (Customer Delivery Addresses)
 * Phase 6: Address management for checkout
 */

class Address extends Model {
    protected string $table = 'addresses';

    /**
     * Find all addresses for a specific user (excluding soft-deleted)
     */
    public function findByUserId(int $userId): array {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id AND is_deleted = 0
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Find a specific address belonging to a user (security check)
     */
    public function findByIdAndUserId(int $id, int $userId): ?array {
        $sql = "SELECT * FROM {$this->table}
                WHERE id = :id AND user_id = :user_id AND is_deleted = 0
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Count addresses for a user (excluding soft-deleted)
     */
    public function countByUserId(int $userId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = :user_id AND is_deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }
}
