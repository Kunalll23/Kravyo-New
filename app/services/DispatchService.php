<?php
/**
 * Kravyo - Smart Cascade Dispatch Service
 * 
 * Implements an intelligent 4-stage hybrid delivery dispatch engine:
 * 1. Batching Check: Checks if a driver is already at the same kitchen heading in same corridor.
 * 2. Best-Match Single Driver Ping: Scores distance, experience, and recency to ping #1 match (25s timer).
 * 3. Instant Cascade: Cascades immediately on skip/timeout to #2 and #3 candidates (0s pause).
 * 4. Radius Broadcast Fallback: Alerts all available riders in radius/city if targeted matches pass.
 */

require_once APP_PATH . '/models/Order.php';
require_once APP_PATH . '/models/DeliveryOffer.php';
require_once APP_PATH . '/models/DeliveryPartner.php';
require_once APP_PATH . '/models/Kitchen.php';
require_once APP_PATH . '/models/Notification.php';

class DispatchService {
    private Order $orderModel;
    private DeliveryOffer $offerModel;
    private DeliveryPartner $partnerModel;
    private Kitchen $kitchenModel;

    public function __construct() {
        $this->orderModel   = new Order();
        $this->offerModel   = new DeliveryOffer();
        $this->partnerModel = new DeliveryPartner();
        $this->kitchenModel = new Kitchen();
    }

    /**
     * Primary dispatch entry point
     */
    public function dispatch(int $orderId): bool {
        $order = $this->orderModel->find($orderId);
        if (!$order || !in_array($order['order_status'], ['preparing', 'ready_for_pickup']) || $order['delivery_partner_id'] !== null) {
            return false;
        }

        // Clean up any stale offers for a fresh dispatch
        $this->offerModel->deleteForOrder($orderId);

        // Stage 1: Batch Opportunity Check
        if ($this->checkBatchOpportunity($orderId)) {
            return true;
        }

        // Stage 2: Target Ping Best-Match Driver (Round 1)
        if ($this->pingBestMatch($orderId, 1)) {
            return true;
        }

        // Stage 4 Fallback: If zero candidates available for targeted ping, broadcast immediately
        return $this->broadcastFallback($orderId);
    }

    /**
     * Backward-compatible alias for existing callers
     */
    public function broadcast(int $orderId, int $round = 1): bool {
        if ($round === 1) {
            return $this->dispatch($orderId);
        }
        return $this->broadcastFallback($orderId);
    }

    /**
     * Stage 1: Check if an active driver is at the same kitchen and heading in the same direction
     */
    public function checkBatchOpportunity(int $orderId): bool {
        $order = $this->orderModel->find($orderId);
        if (!$order || empty($order['kitchen_id'])) {
            return false;
        }

        // Single Tiffin Delivery Policy: Tiffin subscriptions are assigned 1-to-1
        // to a dedicated single delivery partner (bypasses multi-order batching)
        if (($order['order_type'] ?? 'standard') === 'tiffin') {
            return false;
        }

        $kitchenId = (int)$order['kitchen_id'];

        // Get drop-off coordinates
        $dropLat = 0.0;
        $dropLng = 0.0;
        if (!empty($order['address_id'])) {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT latitude, longitude FROM addresses WHERE id = :id");
            $stmt->execute(['id' => $order['address_id']]);
            $addr = $stmt->fetch();
            if ($addr && !empty($addr['latitude']) && !empty($addr['longitude'])) {
                $dropLat = (float)$addr['latitude'];
                $dropLng = (float)$addr['longitude'];
            }
        }

        $batchCandidate = $this->partnerModel->findBatchOpportunity($kitchenId, $dropLat, $dropLng);
        if (!$batchCandidate) {
            return false;
        }

        $partnerId = (int)$batchCandidate['delivery_partner_id'];
        $timeoutSeconds = 25;

        // Set order cascade state
        $this->orderModel->setCascadeOffer($orderId, $partnerId, 1, $timeoutSeconds);
        $this->offerModel->createSingle($orderId, $partnerId, 1, 'batch');

        // Notify rider of batch opportunity
        try {
            $notification = new Notification();
            if (!empty($batchCandidate['user_id'])) {
                $notification->createForUser(
                    (int)$batchCandidate['user_id'],
                    'delivery',
                    "Batch Delivery Opportunity! (+Bonus)",
                    "Pick up Order #{$order['order_number']} while at the same kitchen. Deliver along your route!",
                    'order_update',
                    '/delivery/dashboard'
                );
            }
        } catch (Throwable $e) {
            error_log('[DispatchService] Batch notification error: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Stage 2: Find the single highest-ranking driver and ping them directly
     */
    public function pingBestMatch(int $orderId, int $round = 1): bool {
        $order = $this->orderModel->find($orderId);
        if (!$order || !empty($order['delivery_partner_id'])) {
            return false;
        }

        $kitchen = $this->kitchenModel->find((int)$order['kitchen_id']);
        if (!$kitchen) {
            return false;
        }

        $city       = $kitchen['city'] ?? 'Surat';
        $kitchenLat = !empty($kitchen['latitude']) ? (float)$kitchen['latitude'] : 0.0;
        $kitchenLng = !empty($kitchen['longitude']) ? (float)$kitchen['longitude'] : 0.0;

        $excludedRiders = $this->orderModel->getRejectedDriverIds($orderId);

        $candidates = $this->partnerModel->findRankedCandidatesForKitchen($kitchenLat, $kitchenLng, $city, $excludedRiders);
        if (empty($candidates)) {
            return false;
        }

        // Select the top-ranked candidate
        $bestMatch = $candidates[0];
        $partnerId = (int)$bestMatch['id'];
        $timeoutSeconds = 25;

        $this->orderModel->setCascadeOffer($orderId, $partnerId, $round, $timeoutSeconds);
        $this->offerModel->createSingle($orderId, $partnerId, $round, 'cascade');

        // Notify selected driver
        try {
            $notification = new Notification();
            if (!empty($bestMatch['user_id'])) {
                $distText = isset($bestMatch['distance_km']) ? " ({$bestMatch['distance_km']} km away)" : "";
                $notification->createForUser(
                    (int)$bestMatch['user_id'],
                    'delivery',
                    "New Delivery Request (Round $round)",
                    "Order #{$order['order_number']} is ready{$distText}. Tap to accept within 25 seconds!",
                    'order_update',
                    '/delivery/dashboard'
                );
            }
        } catch (Throwable $e) {
            error_log('[DispatchService] Target ping notification error: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Stage 3: Instant Cascade to the next best driver
     */
    public function cascadeNext(int $orderId, ?int $declinedByPartnerId = null): bool {
        $order = $this->orderModel->find($orderId);
        if (!$order || !empty($order['delivery_partner_id']) || !in_array($order['dispatch_status'], ['cascading', 'broadcasting'])) {
            return false;
        }

        // Record declining partner so they are not pinged again for this order
        if ($declinedByPartnerId !== null) {
            $this->orderModel->addRejectedDriver($orderId, $declinedByPartnerId);
            $this->offerModel->markDeclined($orderId, $declinedByPartnerId);
        }

        // If this order is currently in 'broadcasting' state:
        // A rider declining only marks their offer as declined. Do NOT re-trigger broadcast!
        if ($order['dispatch_status'] === 'broadcasting') {
            $timeline = $this->offerModel->getTimelineForOrder($orderId);
            $hasPending = false;
            foreach ($timeline as $t) {
                if ($t['status'] === 'pending') {
                    $hasPending = true;
                    break;
                }
            }
            if (!$hasPending) {
                // Every rider has declined or timed out in Round 4
                $this->orderModel->setDispatchStatus($orderId, 'no_rider', 4);
                try {
                    $kitchenCity = '';
                    if (!empty($order['kitchen_id'])) {
                        $k = $this->kitchenModel->find((int)$order['kitchen_id']);
                        $kitchenCity = $k['city'] ?? '';
                    }
                    $notification = new Notification();
                    $notification->notifyAdmins(
                        'No Riders Available',
                        "All riders in {$kitchenCity} declined Order #{$order['order_number']} during Round 4 Broadcast.",
                        'system_alert',
                        '/admin/dispatch-monitor?order=' . urlencode($order['order_number']) . ($kitchenCity ? '&city=' . urlencode($kitchenCity) : '')
                    );
                } catch (Throwable $e) {}
            }
            return true;
        }

        $currentRound = (int)$order['dispatch_round'];
        $nextRound    = $currentRound + 1;

        // Try up to 3 targeted driver rounds before falling back
        if ($nextRound <= 3) {
            $success = $this->pingBestMatch($orderId, $nextRound);
            if ($success) {
                return true;
            }
        }

        // All targeted matches exhausted -> Trigger Radius Broadcast Fallback
        return $this->broadcastFallback($orderId);
    }

    /**
     * Stage 4: Radius Broadcast Fallback
     */
    public function broadcastFallback(int $orderId): bool {
        $order = $this->orderModel->find($orderId);
        if (!$order || !empty($order['delivery_partner_id'])) {
            return false;
        }

        $kitchen = $this->kitchenModel->find((int)$order['kitchen_id']);
        $city = $kitchen['city'] ?? 'Surat';
        $kitchenLat = !empty($kitchen['latitude']) ? (float)$kitchen['latitude'] : 0.0;
        $kitchenLng = !empty($kitchen['longitude']) ? (float)$kitchen['longitude'] : 0.0;
        $maxRadius = defined('MAX_DISPATCH_RADIUS_KM') ? (float)MAX_DISPATCH_RADIUS_KM : 15.0;

        $excludedRiders = $this->orderModel->getRejectedDriverIds($orderId);
        
        // 1. First look for fresh riders within 15 km who haven't received a targeted ping yet
        $eligiblePartners = $this->partnerModel->findEligibleInCity($city, $kitchenLat, $kitchenLng, $excludedRiders, $maxRadius);

        // 2. If all online riders within 15 km were already pinged 1-by-1 (e.g. Bardoli with 3 riders),
        // open Round 4 broadcast to ALL active/online riders within 15 km as a shared priority pool!
        if (empty($eligiblePartners)) {
            $eligiblePartners = $this->partnerModel->findEligibleInCity($city, $kitchenLat, $kitchenLng, [], $maxRadius);
        }

        if (empty($eligiblePartners)) {
            // Truly no riders online in the city at all
            $this->orderModel->setDispatchStatus($orderId, 'no_rider', 4);
            try {
                $notification = new Notification();
                $notification->notifyAdmins(
                    'No Riders Available',
                    "Order #{$order['order_number']} could not find any active riders in {$city} (zero riders online or available).",
                    'system_alert',
                    '/admin/dispatch-monitor?order=' . urlencode($order['order_number']) . '&city=' . urlencode($city)
                );
            } catch (Throwable $e) {}
            return true;
        }

        // Create broadcast offers for all eligible partners
        $this->offerModel->createBulk($orderId, $eligiblePartners, 4, 'broadcast');
        $this->orderModel->setDispatchStatus($orderId, 'broadcasting', 4);

        // Notify riders of priority broadcast
        try {
            $notification = new Notification();
            foreach ($eligiblePartners as $dpId) {
                $dp = $this->partnerModel->find((int)$dpId);
                if ($dp && !empty($dp['user_id'])) {
                    $notification->createForUser(
                        (int)$dp['user_id'],
                        'delivery',
                        "Priority Delivery Available!",
                        "Order #{$order['order_number']} is open for immediate claim in {$city}.",
                        'order_update',
                        '/delivery/dashboard'
                    );
                }
            }
        } catch (Throwable $e) {
            error_log('[DispatchService] Broadcast fallback notification error: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Atomic claim of an order by a driver.
     * All 6 state-change writes are wrapped in a single transaction so a server
     * crash or concurrent claim cannot leave the order in a half-assigned limbo state.
     */
    public function claim(int $orderId, int $partnerId): bool {
        $claimed = false;

        Database::withTransaction(function() use ($orderId, $partnerId, &$claimed) {
            // Atomic gate: only succeeds when delivery_partner_id IS NULL
            $claimed = $this->orderModel->claimForPartner($orderId, $partnerId);

            if ($claimed) {
                $this->offerModel->markAccepted($orderId, $partnerId);
                $this->offerModel->markLostExcept($orderId, $partnerId);
                $this->partnerModel->setIsBusy($partnerId, 1);

                // Assign partner details and generate OTP
                $this->orderModel->assignDeliveryPartner($orderId, $partnerId);
                $this->orderModel->updateDeliveryStatus($orderId, 'accepted');
                $this->orderModel->setDispatchStatus($orderId, 'assigned');
            }
            // If not claimed, all writes above are skipped; transaction commits cleanly
        });

        // Notifications are intentionally OUTSIDE the transaction:
        // - They are non-critical (tolerate failure)
        // - External calls (Pusher etc.) must never block a DB transaction
        if ($claimed) {
            try {
                $order = $this->orderModel->find($orderId);
                $dp = $this->partnerModel->findWithUser($partnerId);
                if ($dp && !empty($dp['user_id'])) {
                    (new Notification())->createForUser(
                        (int)$dp['user_id'],
                        'delivery',
                        "You claimed Order #{$order['order_number']}",
                        "Order #{$order['order_number']} is assigned to you. Head to the kitchen.",
                        'order_update',
                        '/delivery/dashboard'
                    );
                }
            } catch (Throwable $e) {
                error_log('[DispatchService] Claim notification error: ' . $e->getMessage());
            }
        }

        return $claimed;
    }

    /**
     * Driver declines offer — triggers immediate cascade to the next driver
     */
    public function decline(int $orderId, int $partnerId): bool {
        return $this->cascadeNext($orderId, $partnerId);
    }

    /**
     * Sweep expired offers.
     * Protected by a MySQL advisory lock so only one process (delivery polling,
     * admin panel, or run_sweep.php) can sweep at a time — prevents double-cascade
     * of the same expired offer when two requests fire simultaneously.
     */
    public function sweepExpired(): void {
        // Non-blocking lock: if another process is already sweeping, skip immediately
        if (!Database::acquireLock('kravyo_dispatch_sweep', 0)) {
            return;
        }

        try {
            $activeOrders = $this->orderModel->getActiveDispatchOrders();

            foreach ($activeOrders as $order) {
                $orderId = (int)$order['id'];
                $status  = $order['dispatch_status'];

                if ($status === 'cascading') {
                    $secondsToExpire = (int)($order['seconds_to_expire'] ?? 0);
                    // If targeted 25s window has expired
                    if ($secondsToExpire <= 0) {
                        $offeredDriver = !empty($order['offered_driver_id']) ? (int)$order['offered_driver_id'] : null;
                        $round = (int)$order['dispatch_round'];

                        $this->offerModel->markExpired($orderId, $round);
                        // Cascade to the next best driver without delay
                        $this->cascadeNext($orderId, $offeredDriver);
                    }
                } elseif ($status === 'broadcasting') {
                    $elapsed = (int)($order['elapsed_seconds'] ?? 0);
                    // 120 seconds total broadcast limit
                    if ($elapsed >= 120) {
                        $round = (int)$order['dispatch_round'];
                        $orderNo = $order['order_number'] ?? ('#' . $orderId);
                        $this->offerModel->markExpired($orderId, $round);
                        $this->orderModel->setDispatchStatus($orderId, 'no_rider', 4);

                        // Escalate to Admin
                        try {
                            $notification = new Notification();
                            $kitchenCity = '';
                            if (!empty($order['kitchen_id'])) {
                                $k = $this->kitchenModel->find((int)$order['kitchen_id']);
                                $kitchenCity = $k['city'] ?? '';
                            }
                            $notification->notifyAdmins(
                                'No Delivery Partner Found',
                                "Order #{$orderNo} timed out after cascading and broadcast dispatch with no rider accepting.",
                                'system_alert',
                                '/admin/dispatch-monitor?order=' . urlencode($orderNo) . ($kitchenCity ? '&city=' . urlencode($kitchenCity) : '')
                            );
                            if (!empty($order['kitchen_id'])) {
                                $notification->notifyKitchenOwner(
                                    (int)$order['kitchen_id'],
                                    'No Rider Found Yet',
                                    "No delivery partner accepted order #{$orderNo}. Admin has been alerted."
                                );
                            }
                        } catch (Throwable $e) {
                            error_log('[DispatchService] Admin notification failed: ' . $e->getMessage());
                        }
                    }
                }
            }
        } finally {
            // Always release the lock, even if an exception occurred
            Database::releaseLock('kravyo_dispatch_sweep');
        }
    }

    /**
     * Cancel an active dispatch
     */
    public function cancel(int $orderId): bool {
        $this->orderModel->setDispatchStatus($orderId, 'none');
        return $this->offerModel->markAllCancelled($orderId);
    }

    /**
     * Instantly ping a rider who just came online if there are active broadcasts in their city
     */
    public function pingNewRiderForActiveBroadcasts(int $partnerId, string $city): void {
        $dp = $this->partnerModel->find($partnerId);
        if (!$dp) return;

        $db = Database::getInstance();
        $sql = "SELECT o.id, o.dispatch_round, k.latitude, k.longitude 
                FROM orders o 
                JOIN kitchens k ON o.kitchen_id = k.id 
                WHERE k.city = :city AND o.dispatch_status IN ('cascading', 'broadcasting')";
        $stmt = $db->prepare($sql);
        $stmt->execute(['city' => $city]);
        $orders = $stmt->fetchAll();

        $maxRadius = defined('MAX_DISPATCH_RADIUS_KM') ? (float)MAX_DISPATCH_RADIUS_KM : 15.0;

        foreach ($orders as $order) {
            $orderId = (int)$order['id'];
            $round   = (int)$order['dispatch_round'];

            // Strict 15 km cap: do not ping if newly online rider is further than 15 km from kitchen
            if (!empty($order['latitude']) && !empty($order['longitude']) && !empty($dp['current_latitude']) && !empty($dp['current_longitude'])) {
                $distKm = DeliveryPartner::calculateDistanceKm(
                    (float)$order['latitude'],
                    (float)$order['longitude'],
                    (float)$dp['current_latitude'],
                    (float)$dp['current_longitude']
                );
                if ($distKm > $maxRadius) {
                    continue; // Exclude rider outside 15 km limit
                }
            }

            $excludedRiders = $this->orderModel->getRejectedDriverIds($orderId);
            if (in_array($partnerId, $excludedRiders, true)) {
                continue; // Rider already rejected this order
            }

            $checkSql = "SELECT id FROM delivery_offers WHERE order_id = :order_id AND delivery_partner_id = :dp_id";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute(['order_id' => $orderId, 'dp_id' => $partnerId]);

            if (!$checkStmt->fetch()) {
                $this->offerModel->createBulk($orderId, [$partnerId], $round, 'broadcast');
                try {
                    if (!empty($dp['user_id'])) {
                        (new Notification())->createForUser(
                            (int)$dp['user_id'],
                            'delivery',
                            "New Active Delivery Opportunity",
                            "New pickup available in {$city} — tap to accept!",
                            'order_update',
                            '/delivery/dashboard'
                        );
                    }
                } catch (Throwable $e) {}
            }
        }
    }
}
