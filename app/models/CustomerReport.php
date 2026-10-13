<?php
/**
 * Kravyo - CustomerReport Model
 * Manages customer reports/complaints for kitchens, dishes, and delivery.
 */

class CustomerReport extends Model {
    protected string $table = 'customer_reports';

    /**
     * Submit a new customer report
     */
    public function createReport(array $data): int|string {
        return $this->create($data);
    }

    /**
     * Check if a duplicate report exists within the last 24 hours
     */
    public function hasDuplicate(int $customerId, int $kitchenId, string $targetType, ?int $targetId, string $reason): bool {
        $sql = "SELECT id FROM {$this->table} 
                WHERE customer_id = :cid 
                  AND kitchen_id = :kid 
                  AND target_type = :type 
                  AND (target_id = :tid OR (target_id IS NULL AND :tid IS NULL))
                  AND reason = :reason
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'cid' => $customerId,
            'kid' => $kitchenId,
            'type' => $targetType,
            'tid' => $targetId,
            'reason' => $reason
        ]);
        return $stmt->fetch() !== false;
    }

    /**
     * Fetch reports for a specific kitchen and target type (e.g. 'dish' for Chef Dashboard)
     */
    public function findByKitchenIdAndType(int $kitchenId, string $type): array {
        $sql = "SELECT r.*, c.full_name as customer_name, m.item_name as dish_name
                FROM {$this->table} r
                JOIN customers c ON r.customer_id = c.id
                LEFT JOIN menu_items m ON (r.target_type = 'dish' AND r.target_id = m.id)
                WHERE r.kitchen_id = :kitchen_id AND r.target_type = :type
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId, 'type' => $type]);
        return $stmt->fetchAll();
    }

    /**
     * Fetch all reports for the Admin Dashboard
     */
    public function findAllReports(): array {
        $sql = "SELECT r.*, c.full_name as customer_name, k.kitchen_name
                FROM {$this->table} r
                JOIN customers c ON r.customer_id = c.id
                JOIN kitchens k ON r.kitchen_id = k.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update the status and admin notes of a report
     */
    public function updateStatus(int $reportId, string $status, string $adminNotes): bool {
        $data = [
            'status' => $status,
            'admin_notes' => $adminNotes,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($status === 'resolved') {
            $data['resolved_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($reportId, $data);
    }
}
