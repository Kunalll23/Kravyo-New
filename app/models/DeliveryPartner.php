<?php
/**
 * Kravyo - Delivery Partner Model
 * Manages delivery partner profiles, verification, and delivery operations.
 */

class DeliveryPartner extends Model {
    protected string $table = 'delivery_partners';

    /**
     * Find delivery partner profile by user ID
     */
    public function findByUserId(int $userId): ?array {
        $sql = "SELECT dp.*, u.full_name, u.email, u.phone, u.status AS user_status
                FROM {$this->table} dp
                JOIN delivery_users u ON dp.user_id = u.id
                WHERE dp.user_id = :user_id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Find delivery partner with user info by partner ID
     */
    public function findWithUser(int $id): ?array {
        $sql = "SELECT dp.*, u.full_name, u.email, u.phone, u.status AS user_status
                FROM {$this->table} dp
                JOIN delivery_users u ON dp.user_id = u.id
                WHERE dp.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Get all delivery partners with user info (for admin management) with optional status & city filter
     */
    public function findAllWithUser(?string $status = null, ?string $city = null): array {
        $sql = "SELECT dp.*, u.full_name, u.email, u.phone, u.status AS user_status
                FROM {$this->table} dp
                JOIN delivery_users u ON dp.user_id = u.id";
        $conditions = [];
        $params = [];

        if ($status !== null && $status !== '' && $status !== 'all') {
            $conditions[] = "dp.verification_status = :status";
            $params['status'] = $status;
        }

        if ($city !== null && $city !== '' && $city !== 'all') {
            $conditions[] = "LOWER(dp.city) = LOWER(:city)";
            $params['city'] = $city;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY dp.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get all approved and active delivery partners (for admin order assignment)
     */
    public function findAvailableForAssignment(): array {
        $sql = "SELECT dp.*, u.full_name, u.phone
                FROM {$this->table} dp
                JOIN delivery_users u ON dp.user_id = u.id
                WHERE dp.verification_status = 'approved'
                  AND u.status = 'active'
                ORDER BY dp.is_online DESC, u.full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update verification status with admin notes
     */
    public function updateVerificationStatus(int $id, string $status, string $notes = ''): bool {
        $sql = "UPDATE {$this->table} SET verification_status = :status, admin_notes = :notes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'notes'  => $notes,
            'id'     => $id
        ]);
    }

    /**
     * Toggle online/offline status
     */
    public function toggleOnlineStatus(int $id, int $isOnline): bool {
        $sql = "UPDATE {$this->table} SET is_online = :is_online WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['is_online' => $isOnline, 'id' => $id]);
    }

    /**
     * Automatically reset stale riders whose last heartbeat is older than threshold (default 5 mins)
     * or who have never sent a heartbeat
     */
    public function cleanupStaleOnlinePartners(int $timeoutMinutes = 5): int {
        $sql = "UPDATE {$this->table} 
                SET is_online = 0, is_busy = 0 
                WHERE is_online = 1 
                  AND (last_seen_at IS NULL OR last_seen_at < DATE_SUB(NOW(), INTERVAL :mins MINUTE))";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['mins' => $timeoutMinutes]);
        return $stmt->rowCount();
    }

    public function bulkUpdateOnlineStatus(int $isOnline, ?string $city = null): bool {
        // Mock coordinates for testing to ensure they fall within the 5km radius
        $lat = null;
        $lng = null;
        if ($isOnline === 1) {
            $lat = 21.1702; // Default Surat
            $lng = 72.8311;
            if (strtolower($city) === 'ahmedabad') { $lat = 23.0225; $lng = 72.5714; }
            if (strtolower($city) === 'bardoli') { $lat = 21.1202; $lng = 73.1127; }
        }

        if ($city) {
            if ($isOnline === 1) {
                $sql = "UPDATE {$this->table}
                        SET is_online = 1, current_latitude = :lat, current_longitude = :lng, last_seen_at = NOW()
                        WHERE city = :city";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute(['lat' => $lat, 'lng' => $lng, 'city' => $city]);
            } else {
                $sql = "UPDATE {$this->table}
                        SET is_online = 0
                        WHERE city = :city";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute(['city' => $city]);
            }
        } else {
            if ($isOnline === 1) {
                $sql = "UPDATE {$this->table} SET is_online = 1, current_latitude = :lat, current_longitude = :lng, last_seen_at = NOW()";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute(['lat' => $lat, 'lng' => $lng]);
            } else {
                $sql = "UPDATE {$this->table} SET is_online = 0";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute();
            }
        }
    }

    /**
     * Get unique list of cities where delivery partners (or users) are located
     */
    public function getAvailableCities(): array {
        $sql = "SELECT DISTINCT city FROM delivery_partners WHERE city IS NOT NULL AND city != ''
                UNION
                SELECT DISTINCT city FROM kitchens WHERE city IS NOT NULL AND city != ''
                UNION
                SELECT DISTINCT city FROM addresses WHERE city IS NOT NULL AND city != ''
                ORDER BY city ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $cities = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($cities)) {
                return $cities;
            }
        } catch (\Exception $e) {
            // fallback
        }
        return ['Ahmedabad', 'Bardoli', 'Bengaluru', 'Delhi', 'Mumbai', 'Pune', 'Surat'];
    }

    /**
     * Increment delivery stats after a completed delivery
     */
    public function incrementDeliveryStats(int $id, float $amount): bool {
        $sql = "UPDATE {$this->table}
                SET total_deliveries = total_deliveries + 1,
                    total_earnings = total_earnings + :amount
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['amount' => $amount, 'id' => $id]);
    }

    /**
     * Count delivery partners by verification status
     */
    public function countByStatus(string $status): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE verification_status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Count all delivery partners
     */
    public function countAll(): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Set delivery partner busy status
     */
    public function setIsBusy(int $id, int $isBusy): bool {
        $sql = "UPDATE {$this->table} SET is_busy = :is_busy WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['is_busy' => $isBusy, 'id' => $id]);
    }

    // ─── Broadcast Dispatch Methods ───────────────────────────────────────────

    /**
     * Calculate Haversine distance between two coordinates in kilometers
     */
    public static function calculateDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float {
        if ($lat1 == 0.0 || $lng1 == 0.0 || $lat2 == 0.0 || $lng2 == 0.0) {
            return 999.0;
        }
        $theta = $lng2 - $lng1;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos(max(-1.0, min(1.0, $dist)));
        return round(rad2deg($dist) * 60 * 1.1515 * 1.609344, 2);
    }

    /**
     * Find eligible riders for a broadcast in a specific city within max dispatch radius (strict 15 km cap)
     */
    public function findEligibleInCity(string $city, float $kitchenLat = 0.0, float $kitchenLng = 0.0, array $excludePartnerIds = [], ?float $maxRadiusKm = null): array {
        $maxRadius = $maxRadiusKm ?? (defined('MAX_DISPATCH_RADIUS_KM') ? (float)MAX_DISPATCH_RADIUS_KM : 15.0);
        $excludeSql = '';
        if (!empty($excludePartnerIds)) {
            $placeholders = implode(',', array_map('intval', $excludePartnerIds));
            $excludeSql = " AND dp.id NOT IN ($placeholders)";
        }

        $sql = "SELECT dp.id, dp.current_latitude, dp.current_longitude
                FROM {$this->table} dp
                JOIN delivery_users u ON dp.user_id = u.id
                WHERE dp.city = :city
                  AND dp.is_online = 1
                  AND NOT EXISTS (
                      SELECT 1 FROM orders o 
                      WHERE o.delivery_partner_id = dp.id 
                        AND o.delivery_status IN ('assigned', 'accepted', 'picked_up', 'out_for_delivery')
                  )
                  AND dp.verification_status = 'approved'
                  AND u.status = 'active'
                  AND dp.last_seen_at >= DATE_SUB(NOW(), INTERVAL 300 SECOND)
                  {$excludeSql}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['city' => $city]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return [];
        }

        $eligibleIds = [];
        foreach ($rows as $r) {
            // If kitchen coordinates are available, enforce strict maximum distance
            if ($kitchenLat != 0.0 && $kitchenLng != 0.0 && !empty($r['current_latitude']) && !empty($r['current_longitude'])) {
                $distKm = self::calculateDistanceKm($kitchenLat, $kitchenLng, (float)$r['current_latitude'], (float)$r['current_longitude']);
                if ($distKm > $maxRadius) {
                    continue; // Exclude rider outside strict 15 km limit
                }
            }
            $eligibleIds[] = (int)$r['id'];
        }

        return $eligibleIds;
    }

    /**
     * Update heartbeat to mark rider as active
     */
    public function touchHeartbeat(int $id): bool {
        $sql = "UPDATE {$this->table} SET last_seen_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Find and rank candidate riders for Smart Cascade targeted ping
     * Considers distance, completed deliveries, rating, and recency with strict 15 km limit
     */
    public function findRankedCandidatesForKitchen(float $kitchenLat, float $kitchenLng, string $city, array $excludePartnerIds = [], ?float $maxRadiusKm = null): array {
        $maxRadius = $maxRadiusKm ?? (defined('MAX_DISPATCH_RADIUS_KM') ? (float)MAX_DISPATCH_RADIUS_KM : 15.0);
        $excludeSql = '';
        if (!empty($excludePartnerIds)) {
            $placeholders = implode(',', array_map('intval', $excludePartnerIds));
            $excludeSql = " AND dp.id NOT IN ($placeholders)";
        }

        $sql = "SELECT dp.id, dp.user_id, dp.vehicle_type, dp.total_deliveries,
                       dp.current_latitude, dp.current_longitude, dp.last_seen_at,
                       u.full_name, u.phone
                FROM {$this->table} dp
                JOIN delivery_users u ON dp.user_id = u.id
                WHERE dp.city = :city
                  AND dp.is_online = 1
                  AND dp.is_busy = 0
                  AND dp.verification_status = 'approved'
                  AND u.status = 'active'
                  AND dp.last_seen_at >= DATE_SUB(NOW(), INTERVAL 600 SECOND)
                  AND NOT EXISTS (
                      SELECT 1 FROM orders o 
                      WHERE o.delivery_partner_id = dp.id 
                        AND o.delivery_status IN ('assigned', 'accepted', 'picked_up', 'out_for_delivery')
                  )
                  {$excludeSql}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['city' => $city]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return [];
        }

        $ranked = [];
        foreach ($rows as $r) {
            $distanceKm = 3.0; // default estimated distance
            if (!empty($r['current_latitude']) && !empty($r['current_longitude']) && $kitchenLat != 0.0 && $kitchenLng != 0.0) {
                $distanceKm = self::calculateDistanceKm($kitchenLat, $kitchenLng, (float)$r['current_latitude'], (float)$r['current_longitude']);
            }

            if ($distanceKm > $maxRadius) {
                continue; // Exclude rider outside strict 15 km limit
            }

            // Weighted match score: 60% distance + 25% experience + 15% recency
            $distScore = max(0, 100 - ($distanceKm * 7));
            $expScore  = min(100, ((int)$r['total_deliveries'] * 5) + 50);
            $secondsSinceSeen = !empty($r['last_seen_at']) ? max(0, time() - strtotime($r['last_seen_at'])) : 300;
            $freshScore = max(0, 100 - ($secondsSinceSeen / 6));

            $compositeScore = round(($distScore * 0.60) + ($expScore * 0.25) + ($freshScore * 0.15), 1);

            $r['distance_km'] = $distanceKm;
            $r['match_score'] = $compositeScore;
            $ranked[] = $r;
        }

        // Sort descending by highest match score (closest, most reliable rider first)
        usort($ranked, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        return $ranked;
    }

    /**
     * Check if a rider is already at or assigned to the same kitchen
     * and heading towards the same delivery corridor (Batch Opportunity)
     */
    public function findBatchOpportunity(int $kitchenId, float $dropLat, float $dropLng, float $maxBatchDistanceKm = 2.5): ?array {
        $sql = "SELECT dp.id AS delivery_partner_id, dp.user_id, u.full_name, u.phone,
                       o.id AS existing_order_id, o.order_number AS existing_order_number,
                       a.latitude AS existing_drop_lat, a.longitude AS existing_drop_lng,
                       a.street_address AS existing_drop_address,
                       (SELECT COUNT(*) FROM orders WHERE delivery_partner_id = dp.id 
                        AND delivery_status IN ('assigned','accepted','picked_up','out_for_delivery')) AS active_load
                FROM orders o
                JOIN delivery_partners dp ON o.delivery_partner_id = dp.id
                JOIN delivery_users u ON dp.user_id = u.id
                JOIN addresses a ON o.address_id = a.id
                WHERE o.kitchen_id = :kitchen_id
                  AND o.delivery_status IN ('assigned', 'accepted', 'picked_up')
                  AND (o.order_type IS NULL OR o.order_type != 'tiffin')
                  AND dp.is_online = 1
                  AND dp.verification_status = 'approved'
                  AND u.status = 'active'
                ORDER BY active_load ASC
                LIMIT 5";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $candidates = $stmt->fetchAll();

        foreach ($candidates as $cand) {
            // Maximum 2 concurrent orders for safety & freshness
            if ((int)$cand['active_load'] >= 2) {
                continue;
            }

            // Check corridor proximity if coords available
            if (!empty($cand['existing_drop_lat']) && !empty($cand['existing_drop_lng']) && $dropLat != 0.0 && $dropLng != 0.0) {
                $theta = $dropLng - (float)$cand['existing_drop_lng'];
                $dist = sin(deg2rad($dropLat)) * sin(deg2rad((float)$cand['existing_drop_lat'])) +
                        cos(deg2rad($dropLat)) * cos(deg2rad((float)$cand['existing_drop_lat'])) * cos(deg2rad($theta));
                $dist = acos(max(-1.0, min(1.0, $dist)));
                $corridorDist = round(rad2deg($dist) * 60 * 1.1515 * 1.609344, 2);

                if ($corridorDist <= $maxBatchDistanceKm) {
                    $cand['corridor_distance_km'] = $corridorDist;
                    return $cand;
                }
            } else {
                // If coordinates not configured on addresses, allow batching at same kitchen
                $cand['corridor_distance_km'] = 1.0;
                return $cand;
            }
        }

        return null;
    }
}
