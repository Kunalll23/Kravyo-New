<?php
/**
 * Kravyo - Kitchen Model (Home Chef Kitchen Profiles)
 */

class Kitchen extends Model {
    protected string $table = 'kitchens';

    /**
     * Find kitchen by user (chef) ID
     */
    public function findByUserId(int $userId): ?array {
        $sql = "SELECT k.*, u.full_name AS chef_name, u.email AS chef_email, u.phone AS chef_phone
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id
                WHERE k.user_id = :user_id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Get all kitchens pending admin approval
     */
    public function findPendingKitchens(): array {
        $sql = "SELECT k.*, u.full_name AS chef_name, u.email AS chef_email, u.phone AS chef_phone
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id
                WHERE k.approval_status = :status
                ORDER BY k.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => KITCHEN_STATUS_PENDING]);
        return $stmt->fetchAll();
    }


    /**
     * Get all approved kitchens in a city with valid coordinates
     */
    public function findApprovedByCity(string $city): array {
        $sql = "SELECT k.id, k.kitchen_name, k.address, k.city, k.latitude, k.longitude, k.is_open, u.full_name AS chef_name
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id
                WHERE LOWER(k.city) = LOWER(:city)
                  AND k.approval_status = :status
                  AND k.latitude IS NOT NULL 
                  AND k.longitude IS NOT NULL
                ORDER BY k.is_open DESC, k.kitchen_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['city' => $city, 'status' => KITCHEN_STATUS_APPROVED]);
        return $stmt->fetchAll();
    }

    /**
     * Get all kitchens with chef info, optionally filtered by approval status
     */
    public function findAllWithChef(?string $status = null): array {
        $sql = "SELECT k.*, u.full_name AS chef_name, u.email AS chef_email, u.phone AS chef_phone
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id";
        $params = [];

        if ($status !== null) {
            $sql .= " WHERE k.approval_status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY k.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Toggle kitchen open/closed availability
     */
    public function toggleAvailability(int $id, int $isOpen): bool {
        $sql = "UPDATE {$this->table} SET is_open = :is_open WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['is_open' => $isOpen, 'id' => $id]);
    }

    /**
     * Update kitchen approval status with admin notes
     */
    public function updateApprovalStatus(int $id, string $status, string $notes = ''): bool {
        $sql = "UPDATE {$this->table} SET approval_status = :status, admin_notes = :notes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'notes' => $notes,
            'id' => $id
        ]);
    }

    /**
     * Update hygiene badge status
     */
    public function updateHygieneBadge(int $id, string $badge): bool {
        $sql = "UPDATE {$this->table} SET hygiene_badge = :badge WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['badge' => $badge, 'id' => $id]);
    }

    /**
     * Count kitchens by approval status
     */
    public function countByStatus(string $status): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE approval_status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status]);
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Count all kitchens
     */
    public function countAll(): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Find approved kitchens with optional filters (for customer kitchen discovery)
     */
    public function findApprovedWithFilters(array $filters = []): array {
        $sql = "SELECT k.*, u.full_name AS chef_name,
                       (SELECT COUNT(*) FROM menu_items mi WHERE mi.kitchen_id = k.id AND mi.is_available = 1) AS dish_count
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id
                WHERE k.approval_status = 'approved'";
        $params = [];

        // City filter
        if (!empty($filters['city'])) {
            $sql .= " AND k.city = :city";
            $params['city'] = $filters['city'];
        }

        // Pincode filter (matches full 6-digit or prefix)
        if (!empty($filters['pincode'])) {
            $sql .= " AND k.pincode LIKE :pincode";
            $params['pincode'] = $filters['pincode'] . '%';
        }

        // Open-only filter
        if (!empty($filters['open_only'])) {
            $sql .= " AND k.is_open = 1";
        }

        // Keyword search (kitchen name or personal story)
        if (!empty($filters['keyword'])) {
            $sql .= " AND (k.kitchen_name LIKE :keyword OR k.personal_story LIKE :keyword2 OR u.full_name LIKE :keyword3)";
            $params['keyword'] = '%' . $filters['keyword'] . '%';
            $params['keyword2'] = '%' . $filters['keyword'] . '%';
            $params['keyword3'] = '%' . $filters['keyword'] . '%';
        }

        $sql .= " ORDER BY k.is_open DESC, k.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a single approved kitchen with full chef details (for public profile view)
     */
    public function findWithChefById(int $id): ?array {
        $sql = "SELECT k.*, u.full_name AS chef_name, u.email AS chef_email, u.phone AS chef_phone,
                       (SELECT COUNT(*) FROM menu_items mi WHERE mi.kitchen_id = k.id AND mi.is_available = 1) AS dish_count
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id
                WHERE k.id = :id AND k.approval_status = 'approved'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Get distinct cities from approved kitchens (for filter dropdown)
     */
    public function getDistinctCities(): array {
        $sql = "SELECT DISTINCT city FROM {$this->table} WHERE approval_status = 'approved' ORDER BY city ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Find approved kitchens near given latitude/longitude within radius (km)
     * Uses bounding-box pre-filtering + Haversine formula for exact distance.
     *
     * @param float $lat User latitude (-90 to 90)
     * @param float $lng User longitude (-180 to 180)
     * @param float $radiusKm Search radius in kilometers (default 10)
     * @param array $filters Optional filters: keyword, open_only, sort
     * @param int $limit Maximum results to return
     * @return array List of nearby approved kitchens with distance (km) and rating
     */
    public function findNearby(float $lat, float $lng, float $radiusKm = 10.0, array $filters = [], int $limit = 50): array {
        $radiusKm = max(1.0, min(200.0, (float) $radiusKm));

        // Bounding box: 1 deg lat ≈ 111.045 km
        $latDelta = $radiusKm / 111.045;
        $latMin = max(-90.0, $lat - $latDelta);
        $latMax = min(90.0, $lat + $latDelta);

        // 1 deg lng ≈ 111.045 * cos(lat) km
        $cosLat = cos(deg2rad($lat));
        if (abs($cosLat) < 0.0001) {
            $cosLat = 0.0001;
        }
        $lngDelta = $radiusKm / (111.045 * $cosLat);
        $lngMin = max(-180.0, $lng - $lngDelta);
        $lngMax = min(180.0, $lng + $lngDelta);

        $latFloat = (float) $lat;
        $lngFloat = (float) $lng;
        $latMinFloat = (float) $latMin;
        $latMaxFloat = (float) $latMax;
        $lngMinFloat = (float) $lngMin;
        $lngMaxFloat = (float) $lngMax;
        $radiusFloat = (float) $radiusKm;

        $sql = "SELECT k.id, k.user_id, k.kitchen_name, k.personal_story, k.address, k.city, k.pincode,
                       k.fssai_license, k.hygiene_badge, k.is_open, k.banner_image, k.latitude, k.longitude,
                       u.full_name AS chef_name,
                       (SELECT COUNT(*) FROM menu_items mi WHERE mi.kitchen_id = k.id AND mi.is_available = 1) AS dish_count,
                       (SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.kitchen_id = k.id) AS avg_rating,
                       (SELECT COUNT(*) FROM reviews r WHERE r.kitchen_id = k.id) AS review_count,
                       ROUND(
                           6371 * ACOS(
                               LEAST(1.0, GREATEST(-1.0,
                                   COS(RADIANS({$latFloat})) * COS(RADIANS(k.latitude)) *
                                   COS(RADIANS(k.longitude) - RADIANS({$lngFloat})) +
                                   SIN(RADIANS({$latFloat})) * SIN(RADIANS(k.latitude))
                               ))
                           ), 2
                       ) AS distance
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id
                WHERE k.approval_status = 'approved'
                  AND k.latitude IS NOT NULL
                  AND k.longitude IS NOT NULL
                  AND k.latitude BETWEEN {$latMinFloat} AND {$latMaxFloat}
                  AND k.longitude BETWEEN {$lngMinFloat} AND {$lngMaxFloat}";

        $params = [];

        // Keyword search
        if (!empty($filters['keyword'])) {
            $sql .= " AND (k.kitchen_name LIKE :keyword OR k.address LIKE :keyword2 OR k.city LIKE :keyword3 OR u.full_name LIKE :keyword4)";
            $kw = '%' . $filters['keyword'] . '%';
            $params['keyword']  = $kw;
            $params['keyword2'] = $kw;
            $params['keyword3'] = $kw;
            $params['keyword4'] = $kw;
        }

        // Open only filter
        if (!empty($filters['open_only'])) {
            $sql .= " AND k.is_open = 1";
        }

        $sql .= " HAVING distance <= {$radiusFloat}";

        // Sorting
        $sort = $filters['sort'] ?? 'distance';
        switch ($sort) {
            case 'rating':
                $sql .= " ORDER BY avg_rating DESC, distance ASC";
                break;
            case 'open':
                $sql .= " ORDER BY k.is_open DESC, distance ASC";
                break;
            case 'distance':
            default:
                $sql .= " ORDER BY distance ASC";
                break;
        }

        $sql .= " LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all approved kitchens with non-null coordinates (for initial map overview or browse)
     */
    public function findAllWithCoordinates(array $filters = [], int $limit = 60): array {
        $sql = "SELECT k.id, k.user_id, k.kitchen_name, k.personal_story, k.address, k.city, k.pincode,
                       k.fssai_license, k.hygiene_badge, k.is_open, k.banner_image, k.latitude, k.longitude,
                       u.full_name AS chef_name,
                       (SELECT COUNT(*) FROM menu_items mi WHERE mi.kitchen_id = k.id AND mi.is_available = 1) AS dish_count,
                       (SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.kitchen_id = k.id) AS avg_rating,
                       (SELECT COUNT(*) FROM reviews r WHERE r.kitchen_id = k.id) AS review_count
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id
                WHERE k.approval_status = 'approved'
                  AND k.latitude IS NOT NULL
                  AND k.longitude IS NOT NULL";
        
        $params = [];

        if (!empty($filters['city'])) {
            $sql .= " AND k.city = :city";
            $params['city'] = $filters['city'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (k.kitchen_name LIKE :keyword OR k.address LIKE :keyword2 OR k.city LIKE :keyword3 OR u.full_name LIKE :keyword4)";
            $kw = '%' . $filters['keyword'] . '%';
            $params['keyword']  = $kw;
            $params['keyword2'] = $kw;
            $params['keyword3'] = $kw;
            $params['keyword4'] = $kw;
        }

        if (!empty($filters['open_only'])) {
            $sql .= " AND k.is_open = 1";
        }

        $sql .= " ORDER BY k.is_open DESC, k.created_at DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get featured kitchens for a specific city
     */
    public function getFeaturedKitchens(int $limit = 4, string $city = '', string $keyword = '', ?float $lat = null, ?float $lng = null): array {
        $distanceSelect = "NULL AS distance";
        if ($lat !== null && $lng !== null) {
            $distanceSelect = "ROUND(6371 * ACOS(LEAST(1.0, GREATEST(-1.0, COS(RADIANS({$lat})) * COS(RADIANS(k.latitude)) * COS(RADIANS(k.longitude) - RADIANS({$lng})) + SIN(RADIANS({$lat})) * SIN(RADIANS(k.latitude))))), 1) AS distance";
        }

        $sql = "SELECT k.*, u.full_name AS chef_name, {$distanceSelect}
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id
                WHERE k.approval_status = 'approved'
                  AND k.is_open = 1";
        
        $params = [];
        if (!empty($city)) {
            $sql .= " AND k.city = :city";
            $params['city'] = $city;
        }
        if (!empty($keyword)) {
            $sql .= " AND (k.kitchen_name LIKE :keyword OR u.full_name LIKE :keyword2 OR k.personal_story LIKE :keyword3)";
            $params['keyword']  = '%' . $keyword . '%';
            $params['keyword2'] = '%' . $keyword . '%';
            $params['keyword3'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY k.id DESC LIMIT " . (int)$limit; // Simple sorting for now

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get live autocomplete suggestions for kitchen names and chef names
     * Scoped strictly by city if city is provided
     */
    public function getKitchenAndChefSuggestions(string $query, string $city = '', int $limit = 8): array {
        $sql = "SELECT k.id, k.kitchen_name, k.city, k.pincode, k.banner_image, k.is_open,
                       u.full_name AS chef_name,
                       (SELECT COUNT(*) FROM menu_items mi WHERE mi.kitchen_id = k.id AND mi.is_available = 1) AS dish_count
                FROM {$this->table} k
                JOIN chefs u ON k.user_id = u.id
                WHERE k.approval_status = 'approved'";

        $params = [];
        if (!empty($city)) {
            $sql .= " AND k.city = :city";
            $params['city'] = $city;
        }

        if (!empty($query)) {
            $sql .= " AND (k.kitchen_name LIKE :kw1 OR u.full_name LIKE :kw2)";
            $params['kw1'] = '%' . $query . '%';
            $params['kw2'] = '%' . $query . '%';
        }

        $sql .= " ORDER BY k.is_open DESC, k.kitchen_name ASC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decorate match type for each row
        $lowerQuery = strtolower($query);
        foreach ($rows as &$row) {
            $kitchenLower = strtolower($row['kitchen_name'] ?? '');
            $chefLower    = strtolower($row['chef_name'] ?? '');

            if (str_contains($kitchenLower, $lowerQuery) && str_contains($chefLower, $lowerQuery)) {
                $row['match_type'] = 'both';
            } elseif (str_contains($chefLower, $lowerQuery)) {
                $row['match_type'] = 'chef';
            } else {
                $row['match_type'] = 'kitchen';
            }
        }
        unset($row);

        return $rows;
    }
}

