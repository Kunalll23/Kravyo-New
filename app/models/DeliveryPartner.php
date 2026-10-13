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
     * Get all delivery partners with user info (for admin management)
     */
    public function findAllWithUser(?string $status = null): array {
        $sql = "SELECT dp.*, u.full_name, u.email, u.phone, u.status AS user_status
                FROM {$this->table} dp
                JOIN delivery_users u ON dp.user_id = u.id";
        $params = [];

        if ($status !== null) {
            $sql .= " WHERE dp.verification_status = :status";
            $params['status'] = $status;
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

    public function bulkUpdateOnlineStatus(int $isOnline, ?string $city = null): bool {
        // Mock coordinates for testing to ensure they fall within the 5km radius
        $lat = null;
        $lng = null;
        if ($isOnline === 1) {
            $lat = 21.1702; // Default Surat
            $lng = 72.8311;
            if (strtolower($city) === 'ahmedabad') { $lat = 23.0225; $lng = 72.5714; }
            if (strtolower($city) === 'vadodara') { $lat = 22.3072; $lng = 73.1812; }
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
        $sql = "SELECT DISTINCT city FROM addresses WHERE city IS NOT NULL AND city != '' ORDER BY city ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
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
     * Finds the nearest active delivery partner using Road Distance/ETA (Phase 11)
     * 
     * @param float $lat Kitchen latitude
     * @param float $lng Kitchen longitude
     * @param float $maxDistanceKm Maximum allowed distance in kilometers
     * @param array $excludePartnerIds Array of delivery_partner_id to skip
     * @return array|null Returns the partner ID and calculated distance
     */
    public function findNearestActivePartner(float $lat, float $lng, float $maxDistanceKm = 15.0, array $excludePartnerIds = []): ?array {
        // 1. Get Top 5 nearest active partners using Haversine (straight-line) to avoid pinging API for everyone
        $excludeSql = '';
        if (!empty($excludePartnerIds)) {
            $placeholders = implode(',', array_fill(0, count($excludePartnerIds), '?'));
            $excludeSql = " AND dp.id NOT IN ($placeholders)";
        }

        $sql = "SELECT dp.user_id, dp.id AS delivery_partner_id, dp.current_latitude, dp.current_longitude,
                       (6371 * acos(cos(radians(?)) * cos(radians(dp.current_latitude)) *
                       cos(radians(dp.current_longitude) - radians(?)) +
                       sin(radians(?)) * sin(radians(dp.current_latitude)))) AS distance
                FROM {$this->table} dp
                JOIN delivery_users u ON dp.user_id = u.id
                WHERE dp.is_online = 1
                  AND dp.is_busy = 0
                  AND dp.verification_status = 'approved'
                  AND u.status = 'active'
                  {$excludeSql}
                HAVING distance <= ?
                ORDER BY distance ASC
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [$lat, $lng, $lat];
        if (!empty($excludePartnerIds)) {
            $params = array_merge($params, $excludePartnerIds);
        }
        $params[] = $maxDistanceKm;
        
        $stmt->execute($params);
        
        $candidates = $stmt->fetchAll();
        
        if (empty($candidates)) {
            return null;
        }
        
        // 2. Use OSRM API to find actual Driving ETA
        $bestPartner = null;
        $lowestEta = 9999999;
        
        foreach ($candidates as $candidate) {
            $driverLng = $candidate['current_longitude'];
            $driverLat = $candidate['current_latitude'];
            
            // OSRM requires format: longitude,latitude
            $osrmUrl = "http://router.project-osrm.org/route/v1/driving/{$driverLng},{$driverLat};{$lng},{$lat}?overview=false";
            
            try {
                // Set a timeout of 1.5 seconds so we don't hang the app if OSRM is down
                $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
                $response = @file_get_contents($osrmUrl, false, $ctx);
                if ($response) {
                    $data = json_decode($response, true);
                    if (isset($data['routes'][0]['duration'])) {
                        $etaSeconds = (float)$data['routes'][0]['duration'];
                        $roadDistanceKm = ((float)$data['routes'][0]['distance']) / 1000;
                        
                        // Update the candidate's distance to the real road distance
                        $candidate['distance'] = $roadDistanceKm;
                        
                        if ($etaSeconds < $lowestEta) {
                            $lowestEta = $etaSeconds;
                            $bestPartner = $candidate;
                        }
                    }
                }
            } catch (Exception $e) {
                // Ignore API failures for this loop
            }
        }
        
        // If OSRM failed completely for all candidates (or timed out), fallback to the absolute closest straight-line match
        if (!$bestPartner) {
            return $candidates[0];
        }
        
        return $bestPartner;
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
     * Find eligible riders for a broadcast in a specific city
     */
    public function findEligibleInCity(string $city): array {
        $sql = "SELECT dp.id
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
                  AND dp.last_seen_at >= DATE_SUB(NOW(), INTERVAL 300 SECOND)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['city' => $city]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
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
     * Considers distance, completed deliveries, rating, and recency
     */
    public function findRankedCandidatesForKitchen(float $kitchenLat, float $kitchenLng, string $city, array $excludePartnerIds = [], float $maxRadiusKm = 15.0): array {
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
                // Haversine formula
                $theta = $kitchenLng - (float)$r['current_longitude'];
                $dist = sin(deg2rad($kitchenLat)) * sin(deg2rad((float)$r['current_latitude'])) +
                        cos(deg2rad($kitchenLat)) * cos(deg2rad((float)$r['current_latitude'])) * cos(deg2rad($theta));
                $dist = acos(max(-1.0, min(1.0, $dist)));
                $dist = rad2deg($dist);
                $distanceKm = round($dist * 60 * 1.1515 * 1.609344, 2);
            }

            if ($distanceKm > $maxRadiusKm) {
                continue;
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
