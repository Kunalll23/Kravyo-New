<?php
/**
 * Kravyo - Delivery Offer Model
 */

class DeliveryOffer extends Model {
    protected string $table = 'delivery_offers';

    public function createSingle(int $orderId, int $partnerId, int $round = 1, string $offerType = 'cascade'): bool {
        $sql = "INSERT INTO {$this->table} (order_id, delivery_partner_id, round, offer_type, status, sent_at, seen_at, responded_at)
                VALUES (:order_id, :dp_id, :round, :offer_type, 'pending', NOW(), NULL, NULL)
                ON DUPLICATE KEY UPDATE offer_type = :offer_type_up, status = 'pending', sent_at = NOW(), seen_at = NULL, responded_at = NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'order_id'       => $orderId,
            'dp_id'          => $partnerId,
            'round'          => $round,
            'offer_type'     => $offerType,
            'offer_type_up'  => $offerType
        ]);
    }

    public function createBulk(int $orderId, array $partnerIds, int $round = 1, string $offerType = 'broadcast'): bool {
        if (empty($partnerIds)) return true;

        $values = [];
        $params = [];
        $i = 0;
        foreach ($partnerIds as $partnerId) {
            $values[] = "(:order_id_$i, :dp_id_$i, :round_$i, :offer_type_$i, 'pending', NOW(), NULL, NULL)";
            $params["order_id_$i"]   = $orderId;
            $params["dp_id_$i"]      = $partnerId;
            $params["round_$i"]      = $round;
            $params["offer_type_$i"] = $offerType;
            $i++;
        }

        $sql = "INSERT INTO {$this->table} (order_id, delivery_partner_id, round, offer_type, status, sent_at, seen_at, responded_at)
                VALUES " . implode(', ', $values) . "
                ON DUPLICATE KEY UPDATE status = 'pending', sent_at = NOW(), seen_at = NULL, responded_at = NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteForOrder(int $orderId): bool {
        $sql = "DELETE FROM {$this->table} WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['order_id' => $orderId]);
    }

    public function getPendingForPartner(int $partnerId): array {
        $sql = "SELECT do.*,
                       GREATEST(0, CASE 
                           WHEN o.dispatch_status = 'cascading' AND o.offer_expires_at IS NOT NULL 
                               THEN TIMESTAMPDIFF(SECOND, NOW(), o.offer_expires_at)
                           ELSE 60 - TIMESTAMPDIFF(SECOND, do.sent_at, NOW())
                       END) AS seconds_left,
                       o.order_number, o.created_at as order_created_at,
                       o.total_amount, o.dispatch_status,
                       k.kitchen_name, k.address AS kitchen_address,
                       a.street_address AS delivery_address, a.landmark, a.city AS delivery_city
                FROM {$this->table} do
                JOIN orders o ON do.order_id = o.id
                JOIN kitchens k ON o.kitchen_id = k.id
                JOIN addresses a ON o.address_id = a.id
                WHERE do.delivery_partner_id = :dp_id 
                  AND do.status = 'pending'
                  AND o.dispatch_status IN ('cascading', 'broadcasting')
                ORDER BY do.sent_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dp_id' => $partnerId]);
        return $stmt->fetchAll();
    }

    public function markLostExcept(int $orderId, int $acceptedPartnerId): bool {
        $sql = "UPDATE {$this->table}
                SET status = 'lost', responded_at = NOW()
                WHERE order_id = :order_id AND delivery_partner_id != :dp_id AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['order_id' => $orderId, 'dp_id' => $acceptedPartnerId]);
    }
    
    public function markAccepted(int $orderId, int $partnerId): bool {
        $sql = "UPDATE {$this->table}
                SET status = 'accepted', responded_at = NOW()
                WHERE order_id = :order_id AND delivery_partner_id = :dp_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['order_id' => $orderId, 'dp_id' => $partnerId]);
    }
    
    public function markDeclined(int $orderId, int $partnerId): bool {
        $sql = "UPDATE {$this->table}
                SET status = 'declined', responded_at = NOW()
                WHERE order_id = :order_id AND delivery_partner_id = :dp_id AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['order_id' => $orderId, 'dp_id' => $partnerId]);
    }

    public function markExpired(int $orderId, int $round): bool {
        $sql = "UPDATE {$this->table}
                SET status = 'expired'
                WHERE order_id = :order_id AND round = :round AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['order_id' => $orderId, 'round' => $round]);
    }
    
    public function markAllCancelled(int $orderId): bool {
        $sql = "UPDATE {$this->table}
                SET status = 'cancelled'
                WHERE order_id = :order_id AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['order_id' => $orderId]);
    }

    public function getTimelineForOrder(int $orderId): array {
        $sql = "SELECT do.*, du.full_name as partner_name, dp.vehicle_number
                FROM {$this->table} do
                JOIN delivery_partners dp ON do.delivery_partner_id = dp.id
                JOIN delivery_users du ON dp.user_id = du.id
                WHERE do.order_id = :order_id
                ORDER BY do.sent_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }
    
    public function touchSeen(int $orderId, int $partnerId): bool {
        $sql = "UPDATE {$this->table}
                SET seen_at = NOW()
                WHERE order_id = :order_id AND delivery_partner_id = :dp_id AND seen_at IS NULL AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['order_id' => $orderId, 'dp_id' => $partnerId]);
    }
}
