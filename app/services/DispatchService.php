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

        $eligiblePartners = $this->partnerModel->findEligibleInCity($city);

        if (empty($eligiblePartners)) {
            // No riders available in the city
            $this->orderModel->setDispatchStatus($orderId, 'broadcasting', 4);
            try {
                $notification = new Notification();
                $notification->notifyAdmins(
                    'No Riders Available',
                    "Order #{$order['order_number']} could not find any active riders in {$city}.",
                    'system_alert',
                    '/admin/dispatch-monitor'
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
     * Atomic claim of an order by a driver
     */
    public function claim(int $orderId, int $partnerId): bool {
        $claimed = $this->orderModel->claimForPartner($orderId, $partnerId);

        if ($claimed) {
            $this->offerModel->markAccepted($orderId, $partnerId);
            $this->offerModel->markLostExcept($orderId, $partnerId);
            $this->partnerModel->setIsBusy($partnerId, 1);

            // Assign delivery partner details and OTP
            $this->orderModel->assignDeliveryPartner($orderId, $partnerId);
            $this->orderModel->updateDeliveryStatus($orderId, 'accepted');
            $this->orderModel->setDispatchStatus($orderId, 'assigned');

            // Send confirmation notification
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

            return true;
        }

        return false;
    }

    /**
     * Driver declines offer — triggers immediate cascade to the next driver
     */
    public function decline(int $orderId, int $partnerId): bool {
        return $this->cascadeNext($orderId, $partnerId);
    }

    /**
     * Sweep expired offers (checks both cascading single-driver timers and broadcast timeouts)
     */
    public function sweepExpired(): void {
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
                    $this->offerModel->markExpired($orderId, $round);
                    $this->orderModel->setDispatchStatus($orderId, 'no_rider');

                    // Escalate to Admin
                    try {
                        $notification = new Notification();
                        $orderNo = $order['order_number'] ?? $orderId;
                        $notification->notifyAdmins(
                            'No Delivery Partner Found',
                            "Order #{$orderNo} timed out after cascading and broadcast dispatch with no rider accepting.",
                            'system_alert',
                            '/admin/dispatch-monitor'
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
        $db = Database::getInstance();
        $sql = "SELECT o.id, o.dispatch_round 
                FROM orders o 
                JOIN kitchens k ON o.kitchen_id = k.id 
                WHERE k.city = :city AND o.dispatch_status IN ('cascading', 'broadcasting')";
        $stmt = $db->prepare($sql);
        $stmt->execute(['city' => $city]);
        $orders = $stmt->fetchAll();

        foreach ($orders as $order) {
            $orderId = (int)$order['id'];
            $round   = (int)$order['dispatch_round'];

            $checkSql = "SELECT id FROM delivery_offers WHERE order_id = :order_id AND delivery_partner_id = :dp_id";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute(['order_id' => $orderId, 'dp_id' => $partnerId]);

            if (!$checkStmt->fetch()) {
                $this->offerModel->createBulk($orderId, [$partnerId], $round, 'broadcast');
                try {
                    $dp = $this->partnerModel->find($partnerId);
                    if ($dp && !empty($dp['user_id'])) {
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
