<?php
/**
 * Kravyo - OrderItem Model (Order Line Items with Meal Customization)
 * Phase 6: Stores individual items within an order
 */

class OrderItem extends Model {
    protected string $table = 'order_items';

    /**
     * Get all items for a specific order with menu item details
     */
    public function findByOrderId(int $orderId): array {
        $sql = "SELECT oi.*, mi.item_name, mi.image, mi.is_veg, mi.is_jain_available, mi.is_diabetic_friendly
                FROM {$this->table} oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id = :order_id
                ORDER BY oi.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Batch-fetch all items for multiple orders in a single database query.
     * Returns an associative array mapping order_id => array of items.
     */
    public function findByOrderIds(array $orderIds): array {
        if (empty($orderIds)) {
            return [];
        }
        $orderIds = array_filter(array_map('intval', $orderIds), fn($id) => $id > 0);
        if (empty($orderIds)) {
            return [];
        }
        $placeholders = implode(',', $orderIds);
        $sql = "SELECT oi.*, mi.item_name, mi.image, mi.is_veg, mi.is_jain_available, mi.is_diabetic_friendly
                FROM {$this->table} oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id IN ({$placeholders})
                ORDER BY oi.id ASC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        $grouped = [];
        foreach ($orderIds as $oid) {
            $grouped[$oid] = [];
        }
        foreach ($rows as $row) {
            $grouped[(int)$row['order_id']][] = $row;
        }
        return $grouped;
    }


    /**
     * Create all order items from cart data in a batch
     */
    public function createFromCart(int $orderId, array $cartItems): bool {
        $sql = "INSERT INTO {$this->table}
                (order_id, menu_item_id, quantity, unit_price, subtotal, spice_level, oil_level, is_jain, item_notes, is_zero_waste, zero_waste_id)
                VALUES (:order_id, :menu_item_id, :quantity, :unit_price, :subtotal, :spice_level, :oil_level, :is_jain, :item_notes, :is_zero_waste, :zero_waste_id)";
        $stmt = $this->db->prepare($sql);

        foreach ($cartItems as $item) {
            $subtotal = round($item['price'] * $item['quantity'], 2);
            $stmt->execute([
                'order_id'       => $orderId,
                'menu_item_id'   => $item['menu_item_id'],
                'quantity'       => $item['quantity'],
                'unit_price'     => $item['price'],
                'subtotal'       => $subtotal,
                'spice_level'    => $item['spice_level'] ?? 'Medium',
                'oil_level'      => $item['oil_level'] ?? 'Normal',
                'is_jain'        => $item['is_jain'] ?? 0,
                'item_notes'     => $item['item_notes'] ?? null,
                'is_zero_waste'  => !empty($item['is_zero_waste']) ? 1 : 0,
                'zero_waste_id'  => !empty($item['zero_waste_id']) ? (int) $item['zero_waste_id'] : null,
            ]);
        }

        return true;
    }

    /**
     * Count items in a specific order
     */
    public function countByOrderId(int $orderId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Batch-count items for multiple orders in a single database query.
     * Returns an associative array mapping order_id => count.
     */
    public function countByOrderIds(array $orderIds): array {
        if (empty($orderIds)) {
            return [];
        }
        $orderIds = array_filter(array_map('intval', $orderIds), fn($id) => $id > 0);
        if (empty($orderIds)) {
            return [];
        }
        $placeholders = implode(',', $orderIds);
        $sql = "SELECT order_id, COUNT(*) as total FROM {$this->table} WHERE order_id IN ({$placeholders}) GROUP BY order_id";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        $counts = [];
        foreach ($orderIds as $oid) {
            $counts[$oid] = 0;
        }
        foreach ($rows as $r) {
            $counts[(int)$r['order_id']] = (int)$r['total'];
        }
        return $counts;
    }
}
