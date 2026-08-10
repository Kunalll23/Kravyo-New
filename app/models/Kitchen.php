<?php
/**
 * Kravyo - Kitchen Model (Home Chef Kitchen Profiles)
 */

class Kitchen extends Model {
    protected string $table = 'kitchens';

    /**
     * Find kitchen by user (chef) ID
     */
    public function findByUserId(int $userId): ?array {
        $sql = "SELECT k.*, u.full_name AS chef_name, u.email AS chef_email, u.phone AS chef_phone
                FROM {$this->table} k
                JOIN users u ON k.user_id = u.id
                WHERE k.user_id = :user_id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Get all kitchens pending admin approval
     */
    public function findPendingKitchens(): array {
        $sql = "SELECT k.*, u.full_name AS chef_name, u.email AS chef_email, u.phone AS chef_phone
                FROM {$this->table} k
                JOIN users u ON k.user_id = u.id
                WHERE k.approval_status = :status
                ORDER BY k.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => KITCHEN_STATUS_PENDING]);
        return $stmt->fetchAll();
    }

    /**
     * Get all approved kitchens (for public browsing)
     */
    public function findAllApproved(): array {
        $sql = "SELECT k.*, u.full_name AS chef_name
                FROM {$this->table} k
                JOIN users u ON k.user_id = u.id
                WHERE k.approval_status = :status
                ORDER BY k.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => KITCHEN_STATUS_APPROVED]);
        return $stmt->fetchAll();
    }

    /**
     * Get all kitchens with chef info, optionally filtered by approval status
     */
    public function findAllWithChef(?string $status = null): array {
        $sql = "SELECT k.*, u.full_name AS chef_name, u.email AS chef_email, u.phone AS chef_phone
                FROM {$this->table} k
                JOIN users u ON k.user_id = u.id";
        $params = [];

        if ($status !== null) {
            $sql .= " WHERE k.approval_status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY k.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Toggle kitchen open/closed availability
     */
    public function toggleAvailability(int $id, int $isOpen): bool {
        $sql = "UPDATE {$this->table} SET is_open = :is_open WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['is_open' => $isOpen, 'id' => $id]);
    }

    /**
     * Update kitchen approval status with admin notes
     */
    public function updateApprovalStatus(int $id, string $status, string $notes = ''): bool {
        $sql = "UPDATE {$this->table} SET approval_status = :status, admin_notes = :notes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'notes' => $notes,
            'id' => $id
        ]);
    }

    /**
     * Update hygiene badge status
     */
    public function updateHygieneBadge(int $id, string $badge): bool {
        $sql = "UPDATE {$this->table} SET hygiene_badge = :badge WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['badge' => $badge, 'id' => $id]);
    }

    /**
     * Count kitchens by approval status
     */
    public function countByStatus(string $status): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE approval_status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Count all kitchens
     */
    public function countAll(): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Find approved kitchens with optional filters (for customer kitchen discovery)
     */
    public function findApprovedWithFilters(array $filters = []): array {
        $sql = "SELECT k.*, u.full_name AS chef_name,
                       (SELECT COUNT(*) FROM menu_items mi WHERE mi.kitchen_id = k.id AND mi.is_available = 1) AS dish_count
                FROM {$this->table} k
                JOIN users u ON k.user_id = u.id
                WHERE k.approval_status = 'approved'";
        $params = [];

        // City filter
        if (!empty($filters['city'])) {
            $sql .= " AND k.city = :city";
            $params['city'] = $filters['city'];
        }

        // Pincode filter
        if (!empty($filters['pincode'])) {
            $sql .= " AND k.pincode = :pincode";
            $params['pincode'] = $filters['pincode'];
        }

        // Open-only filter
        if (!empty($filters['open_only'])) {
            $sql .= " AND k.is_open = 1";
        }

        // Keyword search (kitchen name or personal story)
        if (!empty($filters['keyword'])) {
            $sql .= " AND (k.kitchen_name LIKE :keyword OR k.personal_story LIKE :keyword2 OR u.full_name LIKE :keyword3)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
            $params['keyword2'] = '%' . $filters['keyword'] . '%';
            $params['keyword3'] = '%' . $filters['keyword'] . '%';
        }

        $sql .= " ORDER BY k.is_open DESC, k.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a single approved kitchen with full chef details (for public profile view)
     */
    public function findWithChefById(int $id): ?array {
        $sql = "SELECT k.*, u.full_name AS chef_name, u.email AS chef_email, u.phone AS chef_phone,
                       (SELECT COUNT(*) FROM menu_items mi WHERE mi.kitchen_id = k.id AND mi.is_available = 1) AS dish_count
                FROM {$this->table} k
                JOIN users u ON k.user_id = u.id
                WHERE k.id = :id AND k.approval_status = 'approved'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Get distinct cities from approved kitchens (for filter dropdown)
     */
    public function getDistinctCities(): array {
        $sql = "SELECT DISTINCT city FROM {$this->table} WHERE approval_status = 'approved' ORDER BY city ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
