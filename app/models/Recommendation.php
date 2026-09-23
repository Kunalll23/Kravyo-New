<?php
/**
 * Kravyo - Recommendation Model (Phase 10: AI Engine)
 *
 * Architecture:
 *   PHP calls Python Flask microservice (http://127.0.0.1:5000/recommend)
 *   The Python server uses Pandas + Scikit-learn + NumPy to compute
 *   content-based TF-IDF cosine-similarity recommendations.
 *
 * Scoring formula (in Python):
 *   final_score = 0.80 × cosine_similarity  +  0.20 × popularity_score
 *   (both components are Min-Max normalised to [0, 1])
 *
 * Fallback:
 *   If the Python server is unavailable (connection refused, timeout,
 *   or invalid JSON), the model falls back to the existing PHP
 *   popularity-ranked query so the website never crashes.
 *
 * Public methods (preserved for existing controllers & views):
 *   getForCustomer(int $customerId, int $limit = 8): array
 *   getPopularDishes(int $limit = 8): array
 *   refreshPreferences(int $customerId): void
 */

class Recommendation extends Model {
    protected string $table = 'menu_items';

    /** Base URL of the Python Flask AI server */
    private const PYTHON_API_URL = 'http://127.0.0.1:5000/recommend';

    /** cURL timeout in seconds — keep short so fallback is fast */
    private const REQUEST_TIMEOUT = 3;

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Get top-N personalised recommendations for a logged-in customer.
     *
     * Workflow:
     *   1. Call Python AI server → receive ranked menu_item_ids
     *   2. Fetch full dish records from MariaDB for those IDs
     *   3. Preserve Python ranking order + attach `score` field
     *
     * Falls back to getPopularDishes() if:
     *   - Python server is unreachable
     *   - Customer has no order history (cold-start — handled by Python too)
     *   - Any error occurs
     *
     * @param int $customerId
     * @param int $limit
     * @return array  Array of menu_item rows, each with an extra `score` field
     */
    public function getForCustomer(int $customerId, int $limit = 8): array {
        // 1. Call Python AI API
        $aiResult = $this->callPythonApi($customerId, $limit);

        if ($aiResult === null || empty($aiResult['recommendations'])) {
            // Python unavailable or cold-start returned nothing → PHP fallback
            return $this->getPopularDishes($limit);
        }

        // 2. Extract ordered list of menu_item_ids and score map
        $recommendations = $aiResult['recommendations'];
        $scoreMap        = [];
        $orderedIds      = [];
        foreach ($recommendations as $rec) {
            $id               = (int) $rec['menu_item_id'];
            $orderedIds[]     = $id;
            $scoreMap[$id]    = (float) $rec['final_score'];
        }

        if (empty($orderedIds)) {
            return $this->getPopularDishes($limit);
        }

        // 3. Fetch full dish records from MariaDB for those IDs
        $dishes = $this->fetchDishesByIds($orderedIds);

        if (empty($dishes)) {
            return $this->getPopularDishes($limit);
        }

        // 4. Build an id→row map
        $dishMap = [];
        foreach ($dishes as $dish) {
            $dishMap[(int) $dish['id']] = $dish;
        }

        // 5. Reorder to match Python ranking, attach `score`
        $ordered = [];
        foreach ($orderedIds as $id) {
            if (isset($dishMap[$id])) {
                $row          = $dishMap[$id];
                $row['score'] = $scoreMap[$id];
                $ordered[]    = $row;
            }
        }

        return empty($ordered) ? $this->getPopularDishes($limit) : $ordered;
    }

    /**
     * Get platform-popular dishes (cold-start / guest mode / PHP fallback).
     * Returns top-N dishes by platform-wide delivered order count.
     */
    public function getPopularDishes(int $limit = 8): array {
        $sql = "SELECT m.*, c.category_name,
                       k.kitchen_name, k.city, k.id AS kitchen_id,
                       k.hygiene_badge, u.full_name AS chef_name,
                       COALESCE(pop.order_count, 0) AS score
                FROM menu_items m
                JOIN categories c ON m.category_id = c.id
                JOIN kitchens k   ON m.kitchen_id  = k.id
                JOIN users u      ON k.user_id      = u.id
                LEFT JOIN (
                    SELECT oi.menu_item_id, COUNT(*) AS order_count
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.order_status = 'delivered'
                    GROUP BY oi.menu_item_id
                ) pop ON pop.menu_item_id = m.id
                WHERE k.approval_status = 'approved'
                  AND k.is_open        = 1
                  AND m.is_available   = 1
                ORDER BY score DESC, m.created_at DESC
                LIMIT :lim";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Upsert the stored preference snapshot for a customer.
     * Called after every successful order placement (OrderController).
     * Preserved exactly — no change needed; Python reads live DB each request.
     */
    public function refreshPreferences(int $customerId): void {
        // Python reads the live kravyo_db on every /recommend call, so no
        // snapshot table is needed. This method is kept for backward
        // compatibility with OrderController which calls it post-order.
        // It is intentionally a no-op now.
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Call the Python Flask AI server via cURL.
     *
     * Returns the decoded JSON array on success, or null on any failure.
     * NEVER throws — all errors are caught and null is returned.
     */
    private function callPythonApi(int $customerId, int $limit): ?array {
        if (!function_exists('curl_init')) {
            return null;
        }

        $url = self::PYTHON_API_URL
             . '?user_id=' . $customerId
             . '&limit='   . $limit;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::REQUEST_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::REQUEST_TIMEOUT,
            CURLOPT_HTTPGET        => true,
            CURLOPT_FAILONERROR    => false,
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $raw === false || $httpCode !== 200) {
            // Server offline or timeout — caller will use PHP fallback
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['success'])) {
            return null;
        }

        return $data;
    }

    /**
     * Fetch full dish rows for a given list of menu_item_ids,
     * preserving kitchen and category join exactly as the view expects.
     *
     * @param int[] $ids
     * @return array
     */
    private function fetchDishesByIds(array $ids): array {
        if (empty($ids)) {
            return [];
        }

        // Build a safe IN clause with integer casting
        $ids      = array_map('intval', $ids);
        $placeholders = implode(',', $ids);   // safe — all integers

        $sql = "SELECT m.*, c.category_name,
                       k.kitchen_name, k.city, k.id AS kitchen_id,
                       k.hygiene_badge, u.full_name AS chef_name
                FROM menu_items m
                JOIN categories c ON m.category_id = c.id
                JOIN kitchens k   ON m.kitchen_id  = k.id
                JOIN users u      ON k.user_id      = u.id
                WHERE m.id IN ($placeholders)
                  AND k.approval_status = 'approved'
                  AND k.is_open        = 1
                  AND m.is_available   = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ─── Admin API Methods (Module 3.7) ────────────────────────────────────────

    /** Base URL of the Python AI server (shared with admin methods) */
    private const PYTHON_BASE_URL  = 'http://127.0.0.1:5000';

    /**
     * Call the Python /health endpoint. Returns status array:
     * ['online' => bool, 'latency_ms' => float|null, 'service' => string]
     */
    public function getAiHealth(): array {
        $start = microtime(true);
        $raw   = $this->curlGet(self::PYTHON_BASE_URL . '/health', 3);
        $ms    = round((microtime(true) - $start) * 1000, 1);

        if ($raw === null) {
            return ['online' => false, 'latency_ms' => null, 'service' => ''];
        }
        $data = json_decode($raw, true);
        return [
            'online'     => (is_array($data) && ($data['status'] ?? '') === 'ok'),
            'latency_ms' => $ms,
            'service'    => $data['service'] ?? 'Unknown',
        ];
    }

    /**
     * Fetch current AI config from Python /config endpoint.
     * Returns assoc array or null if server is offline.
     */
    public function getAiConfig(): ?array {
        $raw = $this->curlGet(self::PYTHON_BASE_URL . '/config', 3);
        if ($raw === null) return null;
        $data = json_decode($raw, true);
        return (is_array($data) && !empty($data['success'])) ? $data['config'] : null;
    }

    /**
     * POST updated config to Python /config endpoint.
     * Returns ['success' => bool, 'error' => string|null, 'config' => array|null]
     */
    public function updateAiConfig(array $cfg): array {
        $url     = self::PYTHON_BASE_URL . '/config';
        $payload = json_encode($cfg);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $raw === false) {
            return ['success' => false, 'error' => 'Python AI server is offline.', 'config' => null];
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ['success' => false, 'error' => 'Invalid response from AI server.', 'config' => null];
        }
        return [
            'success' => !empty($data['success']),
            'error'   => $data['error'] ?? null,
            'config'  => $data['config'] ?? null,
        ];
    }

    /**
     * Run a real recommendation for a given user_id and return enriched dish rows.
     * Used by the Admin Test Recommendations feature.
     */
    public function testRecommendations(int $userId, int $limit = 8): array {
        $aiResult = $this->callPythonApi($userId, $limit);
        if ($aiResult === null) {
            return ['error' => 'Python AI server is offline. Cannot generate test recommendations.', 'items' => []];
        }
        if (empty($aiResult['recommendations'])) {
            return ['error' => null, 'items' => [], 'cold_start' => true];
        }

        $recs      = $aiResult['recommendations'];
        $scoreMap  = [];
        $simMap    = [];
        $popMap    = [];
        $orderedIds = [];
        foreach ($recs as $rec) {
            $id              = (int)$rec['menu_item_id'];
            $orderedIds[]    = $id;
            $scoreMap[$id]   = round((float)$rec['final_score'], 4);
            $simMap[$id]     = round((float)$rec['similarity_score'], 4);
            $popMap[$id]     = round((float)$rec['popularity_score'], 4);
        }

        $dishes   = $this->fetchDishesByIds($orderedIds);
        $dishMap  = [];
        foreach ($dishes as $dish) { $dishMap[(int)$dish['id']] = $dish; }

        $ordered = [];
        foreach ($orderedIds as $id) {
            if (isset($dishMap[$id])) {
                $row                   = $dishMap[$id];
                $row['final_score']    = $scoreMap[$id];
                $row['sim_score']      = $simMap[$id];
                $row['pop_score']      = $popMap[$id];
                $ordered[]             = $row;
            }
        }

        return ['error' => null, 'items' => $ordered, 'cold_start' => false, 'algorithm' => $aiResult['algorithm'] ?? ''];
    }

    /**
     * Return platform-wide AI data statistics for the admin overview panel.
     */
    public function getAdminStats(): array {
        $stats = [];

        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM menu_items m
             JOIN kitchens k ON m.kitchen_id = k.id
             WHERE m.is_available = 1 AND k.approval_status = 'approved' AND k.is_open = 1"
        );
        $stats['available_menu_items'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM orders");
        $stats['total_orders'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM order_items");
        $stats['total_order_items'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query(
            "SELECT COUNT(DISTINCT customer_id) FROM orders WHERE order_status = 'delivered'"
        );
        $stats['customers_with_history'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM kitchens WHERE approval_status = 'approved' AND is_open = 1"
        );
        $stats['active_kitchens'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        $stats['total_customers'] = (int)$stmt->fetchColumn();

        return $stats;
    }

    // ─── Internal cURL Helper ─────────────────────────────────────────────────

    /** Perform a simple GET request. Returns raw body string or null on failure. */
    private function curlGet(string $url, int $timeout): ?string {
        if (!function_exists('curl_init')) return null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_HTTPGET        => true,
        ]);
        $raw   = curl_exec($ch);
        $error = curl_error($ch);
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($error || $raw === false || $code !== 200) ? null : $raw;
    }
}
