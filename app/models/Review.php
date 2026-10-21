<?php
/**
 * Kravyo - Review Model (Customer Reviews & Ratings)
 * Phase 9: Full CRUD + analytics methods added
 */

class Review extends Model {
    protected string $table = 'reviews';

    /**
     * Get all reviews for a specific kitchen with customer name
     */
    public function findByKitchenId(int $kitchenId, int $limit = 0): array {
        $sql = "SELECT r.*, u.full_name AS customer_name
                FROM {$this->table} r
                JOIN customers u ON r.customer_id = u.id
                WHERE r.kitchen_id = :kitchen_id
                ORDER BY r.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Get average rating for a kitchen
     */
    public function getAverageRating(int $kitchenId): float {
        $sql = "SELECT AVG(rating) as avg_rating FROM {$this->table} WHERE kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return round((float) ($result['avg_rating'] ?? 0), 1);
    }

    /**
     * Count total reviews for a kitchen
     */
    public function countByKitchenId(int $kitchenId): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Get review count and average rating for a kitchen in a single query
     */
    public function getRatingSummary(int $kitchenId): array {
        $sql = "SELECT COUNT(*) as total, AVG(rating) as avg_rating FROM {$this->table} WHERE kitchen_id = :kitchen_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $result = $stmt->fetch();
        return [
            'count'      => (int) ($result['total'] ?? 0),
            'avg_rating' => round((float) ($result['avg_rating'] ?? 0), 1),
        ];
    }

    /**
     * Batch-fetch review summary (count and avg_rating) for multiple kitchens in a single query
     */
    public function getRatingSummaryForKitchens(array $kitchenIds): array {
        if (empty($kitchenIds)) {
            return [];
        }
        $kitchenIds = array_filter(array_map('intval', $kitchenIds), fn($id) => $id > 0);
        if (empty($kitchenIds)) {
            return [];
        }
        $placeholders = implode(',', $kitchenIds);
        $sql = "SELECT kitchen_id, COUNT(*) as total, AVG(rating) as avg_rating 
                FROM {$this->table} 
                WHERE kitchen_id IN ({$placeholders}) 
                GROUP BY kitchen_id";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($kitchenIds as $kid) {
            $map[$kid] = ['count' => 0, 'avg_rating' => 0.0];
        }
        foreach ($rows as $r) {
            $map[(int)$r['kitchen_id']] = [
                'count'      => (int) $r['total'],
                'avg_rating' => round((float) ($r['avg_rating'] ?? 0), 1),
            ];
        }
        return $map;
    }


    // ─── Phase 9 additions ────────────────────────────────────────────────────

    /**
     * Check whether a review already exists for a specific order
     */
    public function hasReviewedOrder(int $orderId): bool {
        $sql = "SELECT id FROM {$this->table} WHERE order_id = :order_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Find the review for a given order (returns null if not reviewed yet)
     */
    public function findByOrderId(int $orderId): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = :order_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Submit a new customer review
     */
    public function submitReview(array $data): int|string {
        return $this->create($data);
    }

    /**
     * Get recent reviews for a kitchen (chef dashboard panel)
     */
    public function findRecentByKitchenId(int $kitchenId, int $limit = 3): array {
        $sql = "SELECT r.*, u.full_name AS customer_name
                FROM {$this->table} r
                JOIN customers u ON r.customer_id = u.id
                WHERE r.kitchen_id = :kitchen_id
                ORDER BY r.created_at DESC
                LIMIT {$limit}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Get star-rating distribution for a kitchen (1–5 star counts for display)
     */
    public function getRatingDistribution(int $kitchenId): array {
        $sql = "SELECT rating, COUNT(*) as count
                FROM {$this->table}
                WHERE kitchen_id = :kitchen_id
                GROUP BY rating
                ORDER BY rating DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        $rows = $stmt->fetchAll();

        // Normalize to 5-star indexed array
        $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($rows as $row) {
            $dist[(int) $row['rating']] = (int) $row['count'];
        }
        return $dist;
    }

    /**
     * Get all reviews platform-wide with customer and kitchen names (Admin use)
     */
    public function findAllWithDetails(): array {
        $sql = "SELECT r.*, u.full_name AS customer_name, k.kitchen_name
                FROM {$this->table} r
                JOIN customers u ON r.customer_id = u.id
                JOIN kitchens k ON r.kitchen_id = k.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Advanced Filtered Reviews for Chef Portal
     * Supports: dish_id, rating, time_range, custom dates, text search, sorting
     */
    public function findFilteredForChef(int $kitchenId, array $filters = []): array {
        $params = ['kitchen_id' => $kitchenId];
        $where = ["r.kitchen_id = :kitchen_id"];

        // 1. Dish Filter: Reviews for orders containing a specific dish
        if (!empty($filters['dish_id'])) {
            $where[] = "EXISTS (
                SELECT 1 FROM order_items sub_oi 
                WHERE sub_oi.order_id = r.order_id 
                AND sub_oi.menu_item_id = :dish_id
            )";
            $params['dish_id'] = (int) $filters['dish_id'];
        }

        // 2. Rating Filter
        if (!empty($filters['rating'])) {
            $r = $filters['rating'];
            if (is_numeric($r) && (int) $r >= 1 && (int) $r <= 5) {
                $where[] = "r.rating = :rating";
                $params['rating'] = (int) $r;
            } elseif ($r === 'positive') {
                $where[] = "r.rating >= 4";
            } elseif ($r === 'critical') {
                $where[] = "r.rating <= 2";
            }
        }

        // 3. Time / Date Filter
        if (!empty($filters['time_range'])) {
            switch ($filters['time_range']) {
                case 'today':
                    $where[] = "DATE(r.created_at) = CURDATE()";
                    break;
                case '7days':
                    $where[] = "r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case '30days':
                    $where[] = "r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case 'this_month':
                    $where[] = "r.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00')";
                    break;
                case 'custom':
                    if (!empty($filters['start_date'])) {
                        $where[] = "DATE(r.created_at) >= :start_date";
                        $params['start_date'] = $filters['start_date'];
                    }
                    if (!empty($filters['end_date'])) {
                        $where[] = "DATE(r.created_at) <= :end_date";
                        $params['end_date'] = $filters['end_date'];
                    }
                    break;
            }
        }

        // 4. Keyword Search (Customer name, review text, order number)
        if (!empty($filters['search'])) {
            $where[] = "(u.full_name LIKE :search1 OR r.review_text LIKE :search2 OR o.order_number LIKE :search3)";
            $term = '%' . trim($filters['search']) . '%';
            $params['search1'] = $term;
            $params['search2'] = $term;
            $params['search3'] = $term;
        }

        // 5. Sorting
        $sortOrder = "r.created_at DESC";
        $sort = $filters['sort'] ?? 'recent';
        switch ($sort) {
            case 'oldest':
                $sortOrder = "r.created_at ASC";
                break;
            case 'highest':
                $sortOrder = "r.rating DESC, r.created_at DESC";
                break;
            case 'lowest':
                $sortOrder = "r.rating ASC, r.created_at DESC";
                break;
            case 'recent':
            default:
                $sortOrder = "r.created_at DESC";
                break;
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT r.*, 
                       u.full_name AS customer_name,
                       u.phone AS customer_phone,
                       o.order_number,
                       o.created_at AS order_created_at,
                       o.total_amount AS order_total,
                       (
                           SELECT GROUP_CONCAT(CONCAT(mi.id, '::', mi.item_name, '::', oi.quantity, '::', IFNULL(mi.image, '')) SEPARATOR '||')
                           FROM order_items oi
                           JOIN menu_items mi ON oi.menu_item_id = mi.id
                           WHERE oi.order_id = r.order_id
                       ) AS ordered_items_info
                FROM {$this->table} r
                JOIN customers u ON r.customer_id = u.id
                JOIN orders o ON r.order_id = o.id
                WHERE {$whereClause}
                ORDER BY {$sortOrder}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get review count and average rating per dish for a chef's kitchen
     */
    public function getDishRatingsSummary(int $kitchenId): array {
        $sql = "SELECT mi.id, mi.item_name, mi.image, mi.price, mi.is_veg,
                       COUNT(DISTINCT r.id) AS review_count,
                       ROUND(AVG(r.rating), 1) AS avg_rating
                FROM menu_items mi
                JOIN order_items oi ON mi.id = oi.menu_item_id
                JOIN reviews r ON oi.order_id = r.order_id
                WHERE mi.kitchen_id = :kitchen_id
                GROUP BY mi.id
                ORDER BY review_count DESC, avg_rating DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kitchen_id' => $kitchenId]);
        return $stmt->fetchAll();
    }

    /**
     * Get complete metrics & sentiment breakdown for a chef's kitchen
     */
    public function getKitchenReviewMetrics(int $kitchenId): array {
        $dist = $this->getRatingDistribution($kitchenId);
        $total = array_sum($dist);
        $avg = $this->getAverageRating($kitchenId);

        $positive = ($dist[5] ?? 0) + ($dist[4] ?? 0);
        $critical = ($dist[1] ?? 0) + ($dist[2] ?? 0);
        $positivePct = $total > 0 ? round(($positive / $total) * 100) : 0;

        return [
            'total'          => $total,
            'avg_rating'     => $avg,
            'distribution'   => $dist,
            'positive_count' => $positive,
            'critical_count' => $critical,
            'positive_pct'   => $positivePct,
        ];
    }

    /**
     * Advanced Filtered Reviews for Admin Portal
     * Supports: kitchen_id, city, rating, time_range, custom dates, search, sort
     */
    public function findFilteredForAdmin(array $filters = []): array {
        $params = [];
        $where = ["1=1"];

        // 1. Kitchen Filter
        if (!empty($filters['kitchen_id'])) {
            $where[] = "r.kitchen_id = :kitchen_id";
            $params['kitchen_id'] = (int) $filters['kitchen_id'];
        }

        // 2. City Filter (matches kitchen location)
        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            $where[] = "LOWER(k.city) = LOWER(:city)";
            $params['city'] = trim($filters['city']);
        }

        // 3. Rating Filter
        if (!empty($filters['rating'])) {
            $r = $filters['rating'];
            if (is_numeric($r) && (int) $r >= 1 && (int) $r <= 5) {
                $where[] = "r.rating = :rating";
                $params['rating'] = (int) $r;
            } elseif ($r === 'positive') {
                $where[] = "r.rating >= 4";
            } elseif ($r === 'critical') {
                $where[] = "r.rating <= 2";
            }
        }

        // 4. Time / Date Filter
        if (!empty($filters['time_range'])) {
            switch ($filters['time_range']) {
                case 'today':
                    $where[] = "DATE(r.created_at) = CURDATE()";
                    break;
                case '7days':
                    $where[] = "r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case '30days':
                    $where[] = "r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case 'this_month':
                    $where[] = "r.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01 00:00:00')";
                    break;
                case 'custom':
                    if (!empty($filters['start_date'])) {
                        $where[] = "DATE(r.created_at) >= :start_date";
                        $params['start_date'] = $filters['start_date'];
                    }
                    if (!empty($filters['end_date'])) {
                        $where[] = "DATE(r.created_at) <= :end_date";
                        $params['end_date'] = $filters['end_date'];
                    }
                    break;
            }
        }

        // 5. Keyword Search (Customer name, review text, order number, kitchen name)
        if (!empty($filters['search'])) {
            $where[] = "(u.full_name LIKE :search1 OR r.review_text LIKE :search2 OR o.order_number LIKE :search3 OR k.kitchen_name LIKE :search4)";
            $term = '%' . trim($filters['search']) . '%';
            $params['search1'] = $term;
            $params['search2'] = $term;
            $params['search3'] = $term;
            $params['search4'] = $term;
        }

        // 6. Sorting
        $sortOrder = "r.created_at DESC";
        $sort = $filters['sort'] ?? 'recent';
        switch ($sort) {
            case 'oldest':
                $sortOrder = "r.created_at ASC";
                break;
            case 'highest':
                $sortOrder = "r.rating DESC, r.created_at DESC";
                break;
            case 'lowest':
                $sortOrder = "r.rating ASC, r.created_at DESC";
                break;
            case 'recent':
            default:
                $sortOrder = "r.created_at DESC";
                break;
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT r.*, 
                       u.full_name AS customer_name,
                       u.phone AS customer_phone,
                       u.email AS customer_email,
                       k.id AS kitchen_id,
                       k.kitchen_name,
                       k.city AS kitchen_city,
                       o.order_number,
                       o.created_at AS order_created_at,
                       o.total_amount AS order_total,
                       o.order_status,
                       (
                           SELECT GROUP_CONCAT(CONCAT(mi.id, '::', mi.item_name, '::', oi.quantity) SEPARATOR '||')
                           FROM order_items oi
                           JOIN menu_items mi ON oi.menu_item_id = mi.id
                           WHERE oi.order_id = r.order_id
                       ) AS ordered_items_info
                FROM {$this->table} r
                JOIN customers u ON r.customer_id = u.id
                JOIN kitchens k ON r.kitchen_id = k.id
                LEFT JOIN orders o ON r.order_id = o.id
                WHERE {$whereClause}
                ORDER BY {$sortOrder}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get platform-wide review metrics and rating distribution (Admin dashboard)
     */
    public function getPlatformReviewMetrics(): array {
        $sql = "SELECT COUNT(*) as total_reviews,
                       ROUND(AVG(rating), 1) as avg_rating,
                       SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as count_5,
                       SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as count_4,
                       SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as count_3,
                       SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as count_2,
                       SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as count_1,
                       SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_count,
                       SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as critical_count
                FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();

        $total = (int) ($row['total_reviews'] ?? 0);
        $positive = (int) ($row['positive_count'] ?? 0);
        $critical = (int) ($row['critical_count'] ?? 0);
        $avg = round((float) ($row['avg_rating'] ?? 0.0), 1);
        $posPct = $total > 0 ? round(($positive / $total) * 100) : 0;

        return [
            'total'          => $total,
            'avg_rating'     => $avg,
            'positive_count' => $positive,
            'critical_count' => $critical,
            'positive_pct'   => $posPct,
            'distribution'   => [
                5 => (int) ($row['count_5'] ?? 0),
                4 => (int) ($row['count_4'] ?? 0),
                3 => (int) ($row['count_3'] ?? 0),
                2 => (int) ($row['count_2'] ?? 0),
                1 => (int) ($row['count_1'] ?? 0),
            ],
        ];
    }

    /**
     * Live search suggestions for Admin reviews (customers, kitchens, order numbers, dishes)
     */
    public function getReviewSearchSuggestions(string $query = '', int $limit = 6): array {
        $term = '%' . trim($query) . '%';
        $limit = max(1, min(50, $limit));
        
        // 1. Customers with reviews matching query
        $stmtCust = $this->db->prepare("
            SELECT DISTINCT u.full_name as label, u.phone as subtext
            FROM {$this->table} r
            JOIN customers u ON r.customer_id = u.id
            WHERE u.full_name LIKE :q1 OR u.phone LIKE :q2
            ORDER BY u.full_name ASC
            LIMIT {$limit}
        ");
        $stmtCust->execute(['q1' => $term, 'q2' => $term]);
        $customers = $stmtCust->fetchAll();

        // 2. Kitchens matching query
        $stmtKit = $this->db->prepare("
            SELECT DISTINCT k.kitchen_name as label, k.city as subtext
            FROM kitchens k
            WHERE k.kitchen_name LIKE :q1 OR k.city LIKE :q2
            ORDER BY k.kitchen_name ASC
            LIMIT {$limit}
        ");
        $stmtKit->execute(['q1' => $term, 'q2' => $term]);
        $kitchens = $stmtKit->fetchAll();

        // 3. Orders with reviews matching query
        $stmtOrd = $this->db->prepare("
            SELECT DISTINCT o.order_number as label, CONCAT('₹', ROUND(o.total_amount, 2)) as subtext
            FROM {$this->table} r
            JOIN orders o ON r.order_id = o.id
            WHERE o.order_number LIKE :q
            ORDER BY o.id DESC
            LIMIT {$limit}
        ");
        $stmtOrd->execute(['q' => $term]);
        $orders = $stmtOrd->fetchAll();

        // 4. Dishes matching query
        $stmtDish = $this->db->prepare("
            SELECT DISTINCT mi.item_name as label, 'Dish' as subtext
            FROM menu_items mi
            WHERE mi.item_name LIKE :q
            ORDER BY mi.item_name ASC
            LIMIT {$limit}
        ");
        $stmtDish->execute(['q' => $term]);
        $dishes = $stmtDish->fetchAll();

        return [
            'customers' => $customers,
            'kitchens'  => $kitchens,
            'orders'    => $orders,
            'dishes'    => $dishes,
        ];
    }
}
