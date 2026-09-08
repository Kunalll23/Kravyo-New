<?php
/**
 * Kravyo - Recommendation Model (Phase 10: AI Engine)
 *
 * Weighted-scoring recommendation engine — fully server-side PHP.
 * No external API or ML library required.
 *
 * Scoring formula per candidate dish:
 *   score = (category_affinity × 3)
 *         + (dietary_match     × 2)
 *         + (platform_popularity × 1)
 *         - (recent_order_penalty × 5)
 *
 * The model builds a preference profile from the customer's
 * completed order history, then scores every available dish
 * against that profile.
 */

class Recommendation extends Model {
    protected string $table = 'menu_items';

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Get top-N personalised recommendations for a logged-in customer.
     * Falls back to platform-wide popularity if the user has no order history.
     *
     * @param int $customerId
     * @param int $limit
     * @return array  Array of menu_item rows, each with an extra `score` field
     */
    public function getForCustomer(int $customerId, int $limit = 8): array {
        $profile = $this->buildProfile($customerId);

        if ($profile['order_count'] === 0) {
            // Cold-start: return platform-popular dishes
            return $this->getPopularDishes($limit);
        }

        return $this->scoredRecommendations($customerId, $profile, $limit);
    }

    /**
     * Upsert the stored preference snapshot for a customer.
     * Called after every successful order placement.
     */
    public function refreshPreferences(int $customerId): void {
        $profile = $this->buildProfile($customerId);

        $sql = "INSERT INTO user_preferences
                    (user_id, preferred_category_id, prefers_veg, prefers_jain,
                     prefers_diabetic, order_count_snapshot)
                VALUES
                    (:uid, :cat, :veg, :jain, :diab, :cnt)
                ON DUPLICATE KEY UPDATE
                    preferred_category_id = VALUES(preferred_category_id),
                    prefers_veg           = VALUES(prefers_veg),
                    prefers_jain          = VALUES(prefers_jain),
                    prefers_diabetic      = VALUES(prefers_diabetic),
                    order_count_snapshot  = VALUES(order_count_snapshot)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uid'  => $customerId,
            'cat'  => $profile['top_category_id'],
            'veg'  => $profile['prefers_veg']      ? 1 : 0,
            'jain' => $profile['prefers_jain']      ? 1 : 0,
            'diab' => $profile['prefers_diabetic']  ? 1 : 0,
            'cnt'  => $profile['order_count'],
        ]);
    }

    /**
     * Get platform-popular dishes (cold-start / guest mode).
     * Returns top-N dishes by platform-wide order count.
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

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Build a lightweight preference profile from a customer's order history.
     */
    private function buildProfile(int $customerId): array {
        // 1. How many delivered orders does this user have?
        $sql = "SELECT COUNT(*) AS cnt FROM orders
                WHERE customer_id = :uid AND order_status = 'delivered'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $customerId]);
        $orderCount = (int) $stmt->fetchColumn();

        if ($orderCount === 0) {
            return [
                'order_count'      => 0,
                'top_category_id'  => null,
                'category_counts'  => [],
                'prefers_veg'      => false,
                'prefers_jain'     => false,
                'prefers_diabetic' => false,
                'recent_item_ids'  => [],
            ];
        }

        // 2. Category affinity — count ordered items per category
        $sql = "SELECT mi.category_id, COUNT(*) AS cnt
                FROM order_items oi
                JOIN orders o     ON oi.order_id     = o.id
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE o.customer_id   = :uid
                  AND o.order_status  = 'delivered'
                GROUP BY mi.category_id
                ORDER BY cnt DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $customerId]);
        $catRows = $stmt->fetchAll();

        $categoryCounts = [];
        $topCategoryId  = null;
        foreach ($catRows as $row) {
            $categoryCounts[(int) $row['category_id']] = (int) $row['cnt'];
            if ($topCategoryId === null) {
                $topCategoryId = (int) $row['category_id'];
            }
        }

        // 3. Dietary preference — majority vote across all ordered items
        $sql = "SELECT
                    SUM(mi.is_veg)              AS veg_count,
                    SUM(mi.is_jain_available)   AS jain_count,
                    SUM(mi.is_diabetic_friendly) AS diab_count,
                    COUNT(*)                    AS total
                FROM order_items oi
                JOIN orders o     ON oi.order_id     = o.id
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE o.customer_id  = :uid
                  AND o.order_status = 'delivered'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $customerId]);
        $diet = $stmt->fetch();

        $total      = max(1, (int) $diet['total']);
        $prefersVeg      = ((int) $diet['veg_count']  / $total) >= 0.6;
        $prefersJain     = ((int) $diet['jain_count'] / $total) >= 0.5;
        $prefersDiabetic = ((int) $diet['diab_count'] / $total) >= 0.5;

        // 4. Items ordered in the last 7 days (to avoid repeating them)
        $sql = "SELECT DISTINCT oi.menu_item_id
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE o.customer_id  = :uid
                  AND o.created_at  >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $customerId]);
        $recentItemIds = array_column($stmt->fetchAll(), 'menu_item_id');

        return [
            'order_count'      => $orderCount,
            'top_category_id'  => $topCategoryId,
            'category_counts'  => $categoryCounts,
            'prefers_veg'      => $prefersVeg,
            'prefers_jain'     => $prefersJain,
            'prefers_diabetic' => $prefersDiabetic,
            'recent_item_ids'  => array_map('intval', $recentItemIds),
        ];
    }

    /**
     * Fetch all available dishes and score each one against the user profile.
     * Returns top-N items sorted by score DESC.
     */
    private function scoredRecommendations(
        int   $customerId,
        array $profile,
        int   $limit
    ): array {
        // Fetch all currently-available dishes with platform popularity count
        $sql = "SELECT m.*, c.category_name,
                       k.kitchen_name, k.city, k.id AS kitchen_id,
                       k.hygiene_badge, u.full_name AS chef_name,
                       COALESCE(pop.order_count, 0) AS platform_popularity
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
                  AND m.is_available   = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $dishes = $stmt->fetchAll();

        // Normalise platform popularity to 0–10 scale for fair weighting
        $maxPop = 1;
        foreach ($dishes as $d) {
            $maxPop = max($maxPop, (int) $d['platform_popularity']);
        }

        // Score each dish
        $scored = [];
        foreach ($dishes as $dish) {
            $catId    = (int) $dish['category_id'];
            $dishId   = (int) $dish['id'];

            // ① Category affinity (0–3 pts × count)
            $catAffinity = isset($profile['category_counts'][$catId])
                ? min($profile['category_counts'][$catId], 10) * 3
                : 0;

            // ② Dietary match (0–2 pts each flag)
            $dietScore = 0;
            if ($profile['prefers_veg']      && $dish['is_veg'])              $dietScore += 2;
            if ($profile['prefers_jain']     && $dish['is_jain_available'])   $dietScore += 2;
            if ($profile['prefers_diabetic'] && $dish['is_diabetic_friendly']) $dietScore += 2;

            // ③ Platform popularity (0–10 pts normalised)
            $popScore = (int) round(
                ((int) $dish['platform_popularity'] / $maxPop) * 10
            );

            // ④ Recency penalty (−5 if ordered in last 7 days)
            $recentPenalty = in_array($dishId, $profile['recent_item_ids'], true) ? 5 : 0;

            $totalScore = $catAffinity + $dietScore + $popScore - $recentPenalty;

            $dish['score'] = $totalScore;
            $scored[]      = $dish;
        }

        // Sort by score DESC, then name ASC as tiebreaker
        usort($scored, static function (array $a, array $b): int {
            if ($b['score'] !== $a['score']) {
                return $b['score'] <=> $a['score'];
            }
            return $a['item_name'] <=> $b['item_name'];
        });

        return array_slice($scored, 0, $limit);
    }
}
