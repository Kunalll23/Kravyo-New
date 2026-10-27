<?php
/**
 * Kravyo - Delivery Run Model (Multi-Tiffin Batch Logistics Engine)
 * 
 * Manages scheduled batch delivery runs, multi-kitchen pickup sequencing,
 * multi-stop customer drop-offs, queue-aware progressive tracking,
 * OTP verification, and circular empty tiffin container collection.
 */

class DeliveryRun extends Model {
    protected string $table = 'delivery_runs';

    /**
     * Create optimized batch runs for a given city, date, and meal type
     */
    public function createBatchesForCityDateMeal(string $city, string $date, string $mealType = 'lunch', int $maxPerBatch = 10): array {
        // 1. Fetch pending subscription deliveries for this date and meal in this city
        $sql = "SELECT sd.id as delivery_id, sd.kitchen_id, sd.customer_subscription_id, sd.delivery_time,
                       cs.customer_id, cs.special_instructions,
                       u.full_name as customer_name, u.phone as customer_phone,
                       a.id as address_id, a.street_address, a.landmark, a.city as delivery_city,
                       a.latitude as drop_lat, a.longitude as drop_lng,
                       k.kitchen_name, k.address as kitchen_address, ch.phone as kitchen_phone,
                       k.latitude as kitchen_lat, k.longitude as kitchen_lng
                FROM subscription_deliveries sd
                JOIN customer_subscriptions cs ON sd.customer_subscription_id = cs.id
                JOIN customers u ON cs.customer_id = u.id
                JOIN addresses a ON cs.address_id = a.id
                JOIN kitchens k ON sd.kitchen_id = k.id
                JOIN chefs ch ON k.user_id = ch.id
                WHERE LOWER(TRIM(k.city)) = LOWER(TRIM(:city))
                  AND sd.delivery_date = :deliv_date
                  AND sd.meal_type = :meal_type
                  AND sd.status = 'pending'
                  AND sd.run_id IS NULL
                ORDER BY sd.kitchen_id ASC, a.pincode ASC, a.street_address ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'city'       => $city,
            'deliv_date' => $date,
            'meal_type'  => $mealType
        ]);
        $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($deliveries)) {
            return [];
        }

        // 2. Chunk into batches of up to $maxPerBatch (e.g. 8–10 tiffins per run)
        $chunks = array_chunk($deliveries, $maxPerBatch);
        $createdRunIds = [];

        foreach ($chunks as $chunkIndex => $batchDeliveries) {
            $totalOrders = count($batchDeliveries);
            // Dynamic Payout: Base ₹80 + ₹18 per delivery stop
            $payout = 80.00 + ($totalOrders * 18.00);
            $randomHex = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 4));
            $runCode = sprintf('TR-%s-%02d-%s', date('Ymd', strtotime($date)), $chunkIndex + 1, $randomHex);

            // Insert Delivery Run
            $runSql = "INSERT INTO {$this->table} 
                       (run_code, meal_type, delivery_date, city, status, total_orders, total_payout) 
                       VALUES (:code, :meal, :date, :city, 'draft', :total, :payout)";
            $runStmt = $this->db->prepare($runSql);
            $runStmt->execute([
                'code'   => $runCode,
                'meal'   => $mealType,
                'date'   => $date,
                'city'   => $city,
                'total'  => $totalOrders,
                'payout' => $payout
            ]);
            $runId = (int)$this->db->lastInsertId();
            $createdRunIds[] = $runId;

            // 3. Generate Sequenced Waypoints:
            // STAGE A: Distinct Kitchen Pickup Stops First (Collect all boxes)
            $kitchenMap = [];
            foreach ($batchDeliveries as $deliv) {
                $kId = (int)$deliv['kitchen_id'];
                if (!isset($kitchenMap[$kId])) {
                    $kitchenMap[$kId] = [
                        'kitchen_id'      => $kId,
                        'name'            => $deliv['kitchen_name'],
                        'address'         => $deliv['kitchen_address'],
                        'phone'           => $deliv['kitchen_phone'] ?? '',
                        'lat'             => $deliv['kitchen_lat'],
                        'lng'             => $deliv['kitchen_lng'],
                        'count'           => 0
                    ];
                }
                $kitchenMap[$kId]['count']++;
            }

            $stopSequence = 1;
            $stopInsertSql = "INSERT INTO delivery_run_stops 
                (run_id, stop_sequence, stop_type, subscription_delivery_id, target_id, target_name, target_address, contact_phone, latitude, longitude, items_count, special_instructions, status, delivery_otp)
                VALUES (:run_id, :seq, :type, :sub_id, :target_id, :name, :addr, :phone, :lat, :lng, :count, :notes, 'pending', :otp)";
            $stopStmt = $this->db->prepare($stopInsertSql);

            foreach ($kitchenMap as $kData) {
                $stopStmt->execute([
                    'run_id'    => $runId,
                    'seq'       => $stopSequence++,
                    'type'      => 'pickup',
                    'sub_id'    => null,
                    'target_id' => $kData['kitchen_id'],
                    'name'      => $kData['name'],
                    'addr'      => $kData['address'],
                    'phone'     => $kData['phone'],
                    'lat'       => $kData['lat'],
                    'lng'       => $kData['lng'],
                    'count'     => $kData['count'],
                    'notes'     => "Collect {$kData['count']} packed tiffins from chef",
                    'otp'       => null
                ]);
            }

            // STAGE B: Customer Drop-off Stops (Sequential delivery along corridor)
            $upSubStmt = $this->db->prepare("UPDATE subscription_deliveries SET run_id = :run_id, stop_number = :seq WHERE id = :id");

            foreach ($batchDeliveries as $deliv) {
                $otp = sprintf('%04d', rand(1000, 9999));
                $currSeq = $stopSequence++;

                $stopStmt->execute([
                    'run_id'    => $runId,
                    'seq'       => $currSeq,
                    'type'      => 'dropoff',
                    'sub_id'    => $deliv['delivery_id'],
                    'target_id' => $deliv['customer_id'],
                    'name'      => $deliv['customer_name'],
                    'addr'      => $deliv['street_address'] . ', ' . $deliv['delivery_city'] . (!empty($deliv['landmark']) ? ' (Near ' . $deliv['landmark'] . ')' : ''),
                    'phone'     => $deliv['customer_phone'],
                    'lat'       => $deliv['drop_lat'],
                    'lng'       => $deliv['drop_lng'],
                    'count'     => 1,
                    'notes'     => $deliv['special_instructions'] ?: 'Hand over fresh tiffin and collect clean empty container',
                    'otp'       => $otp
                ]);

                // Link subscription delivery to run and stop number
                $upSubStmt->execute([
                    'run_id' => $runId,
                    'seq'    => $currSeq,
                    'id'     => $deliv['delivery_id']
                ]);
            }
        }

        return $createdRunIds;
    }

    /**
     * Get available draft runs in a specific city for riders to browse and accept
     */
    public function getAvailableRunsForCity(string $city): array {
        $sql = "SELECT dr.*,
                       (SELECT COUNT(*) FROM delivery_run_stops s WHERE s.run_id = dr.id AND s.stop_type = 'pickup') AS pickup_stops_count,
                       (SELECT COUNT(*) FROM delivery_run_stops s WHERE s.run_id = dr.id AND s.stop_type = 'dropoff') AS dropoff_stops_count
                FROM {$this->table} dr
                WHERE LOWER(TRIM(dr.city)) = LOWER(TRIM(:city))
                  AND dr.status = 'draft'
                  AND dr.delivery_date >= CURDATE()
                ORDER BY dr.delivery_date ASC, dr.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['city' => $city]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active run currently assigned to or in progress by a delivery partner
     */
    public function getActiveRunForPartner(int $partnerId): ?array {
        $sql = "SELECT dr.* 
                FROM {$this->table} dr 
                WHERE dr.delivery_partner_id = :dp_id 
                  AND dr.status IN ('assigned', 'in_progress')
                ORDER BY dr.id DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dp_id' => $partnerId]);
        $run = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$run) return null;

        $run['stops'] = $this->getStopsForRun((int)$run['id']);
        return $run;
    }

    /**
     * Get full details of a delivery run including all its ordered stops
     */
    public function getRunDetails(int $runId): ?array {
        $sql = "SELECT dr.*, dp.vehicle_type, dp.vehicle_number, u.full_name as rider_name, u.phone as rider_phone
                FROM {$this->table} dr
                LEFT JOIN delivery_partners dp ON dr.delivery_partner_id = dp.id
                LEFT JOIN delivery_users u ON dp.user_id = u.id
                WHERE dr.id = :id 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $runId]);
        $run = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$run) return null;

        $run['stops'] = $this->getStopsForRun($runId);
        return $run;
    }

    /**
     * Get all stops for a run in sequential order
     */
    public function getStopsForRun(int $runId): array {
        $sql = "SELECT s.*, 
                       sd.delivery_time, sd.status as sub_delivery_status
                FROM delivery_run_stops s
                LEFT JOIN subscription_deliveries sd ON s.subscription_delivery_id = sd.id
                WHERE s.run_id = :run_id 
                ORDER BY s.stop_sequence ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['run_id' => $runId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Assign a draft run to an authenticated delivery partner
     */
    public function assignPartner(int $runId, int $partnerId): bool {
        $sql = "UPDATE {$this->table} 
                SET delivery_partner_id = :dp_id, status = 'assigned' 
                WHERE id = :id AND status = 'draft'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['dp_id' => $partnerId, 'id' => $runId]);
    }

    /**
     * Start the delivery run (rider departs for first kitchen pickup)
     */
    public function startRun(int $runId, int $partnerId): bool {
        $sql = "UPDATE {$this->table} 
                SET status = 'in_progress', started_at = NOW() 
                WHERE id = :id AND delivery_partner_id = :dp_id AND status = 'assigned'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $runId, 'dp_id' => $partnerId]);
    }

    /**
     * Complete a single stop (kitchen pickup or customer drop-off)
     */
    public function completeStop(int $runId, int $stopId, ?string $submittedOtp = null, bool $containerReturned = false): array {
        $stopStmt = $this->db->prepare("SELECT * FROM delivery_run_stops WHERE id = :id AND run_id = :run_id LIMIT 1");
        $stopStmt->execute(['id' => $stopId, 'run_id' => $runId]);
        $stop = $stopStmt->fetch(PDO::FETCH_ASSOC);

        if (!$stop) {
            return ['success' => false, 'message' => 'Stop not found in this run.'];
        }

        if ($stop['status'] === 'completed') {
            return ['success' => true, 'message' => 'Stop already completed.'];
        }

        // If it's a dropoff stop and OTP is required, verify OTP
        if ($stop['stop_type'] === 'dropoff' && !empty($stop['delivery_otp'])) {
            if (empty($submittedOtp) || trim($submittedOtp) !== trim($stop['delivery_otp'])) {
                return ['success' => false, 'message' => 'Invalid delivery OTP entered. Please ask the customer for their 4-digit code.'];
            }
        }

        // Mark stop completed
        $upSql = "UPDATE delivery_run_stops 
                  SET status = 'completed', 
                      container_returned = :ret, 
                      completed_at = NOW() 
                  WHERE id = :id";
        $upStmt = $this->db->prepare($upSql);
        $upStmt->execute([
            'ret' => $containerReturned ? 1 : 0,
            'id'  => $stopId
        ]);

        // If dropoff, update subscription_deliveries record to delivered
        if ($stop['stop_type'] === 'dropoff' && !empty($stop['subscription_delivery_id'])) {
            $sdStmt = $this->db->prepare("UPDATE subscription_deliveries SET status = 'delivered' WHERE id = :id");
            $sdStmt->execute(['id' => $stop['subscription_delivery_id']]);
        }

        // Check if all stops in this run are now completed
        $pendingCheck = $this->db->prepare("SELECT COUNT(*) FROM delivery_run_stops WHERE run_id = :run_id AND status != 'completed'");
        $pendingCheck->execute(['run_id' => $runId]);
        $remainingStops = (int)$pendingCheck->fetchColumn();

        $runCompleted = false;
        if ($remainingStops === 0) {
            $runStmt = $this->db->prepare("UPDATE {$this->table} SET status = 'completed', completed_at = NOW() WHERE id = :id");
            $runStmt->execute(['id' => $runId]);
            $runCompleted = true;

            // Credit payout to delivery partner's total_earnings
            $runData = $this->db->query("SELECT delivery_partner_id, total_payout FROM {$this->table} WHERE id = $runId")->fetch(PDO::FETCH_ASSOC);
            if ($runData && !empty($runData['delivery_partner_id'])) {
                $creditStmt = $this->db->prepare("UPDATE delivery_partners SET total_earnings = total_earnings + :payout, total_deliveries = total_deliveries + 1 WHERE id = :id");
                $creditStmt->execute([
                    'payout' => $runData['total_payout'],
                    'id'     => $runData['delivery_partner_id']
                ]);
            }
        }

        return [
            'success'        => true,
            'message'        => $stop['stop_type'] === 'pickup' ? 'Kitchen pickup confirmed!' : 'Tiffin delivered successfully!',
            'remaining_stops'=> $remainingStops,
            'run_completed'  => $runCompleted
        ];
    }

    /**
     * Customer Tracking Info: Queue position, active leg, ETA, container status
     */
    public function getCustomerTrackingInfo(int $subscriptionDeliveryId): ?array {
        $sql = "SELECT s.id as stop_id, s.run_id, s.stop_sequence as my_stop_sequence, s.status as my_stop_status,
                       s.delivery_otp, s.container_returned, s.completed_at,
                       dr.run_code, dr.meal_type, dr.status as run_status, dr.total_orders,
                       sd.delivery_date, sd.delivery_time, sd.status as delivery_status,
                       u.full_name as customer_name,
                       a.street_address, a.city as delivery_city,
                       k.kitchen_name, k.address as kitchen_address,
                       ru.full_name as rider_name, ru.phone as rider_phone,
                       dp.vehicle_type, dp.vehicle_number, dp.current_latitude as rider_lat, dp.current_longitude as rider_lng
                FROM delivery_run_stops s
                JOIN delivery_runs dr ON s.run_id = dr.id
                JOIN subscription_deliveries sd ON s.subscription_delivery_id = sd.id
                JOIN customer_subscriptions cs ON sd.customer_subscription_id = cs.id
                JOIN customers u ON cs.customer_id = u.id
                JOIN addresses a ON cs.address_id = a.id
                JOIN kitchens k ON sd.kitchen_id = k.id
                LEFT JOIN delivery_partners dp ON dr.delivery_partner_id = dp.id
                LEFT JOIN delivery_users ru ON dp.user_id = ru.id
                WHERE s.subscription_delivery_id = :sd_id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sd_id' => $subscriptionDeliveryId]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$info) return null;

        $runId = (int)$info['run_id'];

        // Determine current active stop sequence in this run
        $activeStopSql = "SELECT stop_sequence, stop_type, target_name 
                          FROM delivery_run_stops 
                          WHERE run_id = :run_id AND status = 'pending' 
                          ORDER BY stop_sequence ASC 
                          LIMIT 1";
        $actStmt = $this->db->prepare($activeStopSql);
        $actStmt->execute(['run_id' => $runId]);
        $currentStop = $actStmt->fetch(PDO::FETCH_ASSOC);

        $currentActiveSequence = $currentStop ? (int)$currentStop['stop_sequence'] : (int)$info['my_stop_sequence'];
        $mySequence = (int)$info['my_stop_sequence'];

        // Total dropoff stops count for friendly queue display (e.g. Stop 3 of 8)
        $totalDropsSql = "SELECT COUNT(*) FROM delivery_run_stops WHERE run_id = :run_id AND stop_type = 'dropoff'";
        $tStmt = $this->db->prepare($totalDropsSql);
        $tStmt->execute(['run_id' => $runId]);
        $totalDrops = (int)$tStmt->fetchColumn();

        // Calculate queue distance
        $stopsAway = max(0, $mySequence - $currentActiveSequence);
        $isNext = ($stopsAway === 0 && $info['my_stop_status'] === 'pending');

        $info['current_active_sequence'] = $currentActiveSequence;
        $info['total_dropoff_stops']     = $totalDrops;
        $info['stops_away']              = $stopsAway;
        $info['is_next']                 = $isNext;
        $info['current_stop_type']       = $currentStop['stop_type'] ?? 'completed';

        return $info;
    }
}
