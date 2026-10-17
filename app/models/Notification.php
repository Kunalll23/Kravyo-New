<?php
/**
 * Kravyo — Notification Model
 *
 * Handles all read/write operations on the `notifications` table.
 * Used by: OrderController, ChefController, AdminController, NotificationController
 *
 * Modules covered:
 *   1.13 — Customer Order & Platform Notifications
 *   2.8  — Home Chef Order & Kitchen Notifications
 *   3.11 — Admin Broadcast Announcements
 */
class Notification extends Model {
    protected string $table = 'notifications';

    /** Allowed values — must match the ENUM columns in the `notifications` table. */
    public const USER_TYPES = ['customer', 'chef', 'delivery', 'admin'];
    public const TYPES      = ['order_update', 'promotion', 'system_alert', 'kitchen_update'];

    // ─── Write ────────────────────────────────────────────────────────────────

    /**
     * Create a notification for a specific user.
     *
     * @param int    $userId   Target user ID
     * @param string $userType One of: customer, chef, delivery, admin
     * @param string $title    Short heading (max 100 chars)
     * @param string $message  Body text (max 500 chars)
     * @param string $type     One of: order_update, promotion, system_alert, kitchen_update
     * @param string|null $link Optional relative URL e.g. '/order/track/5'
     */
    public function createForUser(int $userId, string $userType, string $title, string $message, string $type = 'system_alert', ?string $link = null): void {
        // Guard against mis-ordered arguments (e.g. forgetting $userType), which
        // previously saved rows with a blank user_type that no inbox ever displayed.
        if (!in_array($userType, self::USER_TYPES, true)) {
            throw new InvalidArgumentException("Notification::createForUser — invalid userType '{$userType}'. Expected one of: " . implode(', ', self::USER_TYPES));
        }
        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException("Notification::createForUser — invalid type '{$type}'. Expected one of: " . implode(', ', self::TYPES));
        }

        $sql = "INSERT INTO notifications (user_id, user_type, title, message, type, link)
                VALUES (:user_id, :user_type, :title, :message, :type, :link)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId, 'user_type' => $userType,
            'title'   => substr($title,   0, 100),
            'message' => substr($message, 0, 500),
            'type'    => $type,
            'link'    => $link,
        ]);
    }

    /**
     * Notify every admin account (admins live in the dedicated `admins` table,
     * so their notifications use user_type = 'admin' and user_id = admins.id).
     */
    public function notifyAdmins(string $title, string $message, string $type = 'order_update', ?string $link = '/admin/orders'): void {
        try {
            $ids = $this->db->query("SELECT id FROM admins")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($ids as $id) {
                $this->createForUser((int) $id, 'admin', $title, $message, $type, $link);
            }
        } catch (Throwable $e) {
            error_log('[Kravyo] notifyAdmins failed: ' . $e->getMessage());
        }
    }

    /**
     * Notify the chef who owns a kitchen.
     */
    public function notifyKitchenOwner(int $kitchenId, string $title, string $message, string $type = 'order_update', ?string $link = '/chef/orders'): void {
        try {
            require_once APP_PATH . '/models/Kitchen.php';
            $kitchen = (new Kitchen())->find($kitchenId);
            if ($kitchen && !empty($kitchen['user_id'])) {
                $this->createForUser((int) $kitchen['user_id'], 'chef', $title, $message, $type, $link);
            }
        } catch (Throwable $e) {
            error_log('[Kravyo] notifyKitchenOwner failed: ' . $e->getMessage());
        }
    }

    /**
     * Module 3.11 — Targeted Broadcast (Swiggy / Zomato style)
     * Broadcasts notifications to specific audience (customers, chefs, delivery, or all)
     * and specific city or all cities.
     */
    public function broadcastTargeted(string $title, string $message, string $type = 'promotion', string $audience = 'all', string $city = 'all'): int {
        $count = 0;
        $insert = "INSERT INTO notifications (user_id, user_type, title, message, type)
                   VALUES (:user_id, :user_type, :title, :message, :type)";
        $stmt = $this->db->prepare($insert);

        // 1. Customers
        if ($audience === 'all' || $audience === 'customers') {
            if ($city === 'all') {
                $ids = $this->db->query("SELECT id FROM customers WHERE status = 'active'")->fetchAll(\PDO::FETCH_COLUMN);
            } else {
                $q = "SELECT DISTINCT c.id FROM customers c
                      LEFT JOIN addresses a ON a.user_id = c.id
                      WHERE c.status = 'active' AND (c.city = :city1 OR a.city = :city2)";
                $s = $this->db->prepare($q);
                $s->execute(['city1' => $city, 'city2' => $city]);
                $ids = $s->fetchAll(\PDO::FETCH_COLUMN);
            }
            foreach ($ids as $id) {
                $stmt->execute([
                    'user_id'   => $id,
                    'user_type' => 'customer',
                    'title'     => substr($title,   0, 100),
                    'message'   => substr($message, 0, 500),
                    'type'      => $type,
                ]);
                $count++;
            }
        }

        // 2. Chefs
        if ($audience === 'all' || $audience === 'chefs') {
            if ($city === 'all') {
                $ids = $this->db->query("SELECT id FROM chefs WHERE status = 'active'")->fetchAll(\PDO::FETCH_COLUMN);
            } else {
                $q = "SELECT DISTINCT c.id FROM chefs c
                      LEFT JOIN kitchens k ON k.user_id = c.id
                      WHERE c.status = 'active' AND (c.city = :city1 OR k.city = :city2)";
                $s = $this->db->prepare($q);
                $s->execute(['city1' => $city, 'city2' => $city]);
                $ids = $s->fetchAll(\PDO::FETCH_COLUMN);
            }
            foreach ($ids as $id) {
                $stmt->execute([
                    'user_id'   => $id,
                    'user_type' => 'chef',
                    'title'     => substr($title,   0, 100),
                    'message'   => substr($message, 0, 500),
                    'type'      => $type,
                ]);
                $count++;
            }
        }

        // 3. Delivery Partners
        if ($audience === 'all' || $audience === 'delivery') {
            if ($city === 'all') {
                $ids = $this->db->query("SELECT id FROM delivery_users WHERE status = 'active'")->fetchAll(\PDO::FETCH_COLUMN);
            } else {
                $q = "SELECT DISTINCT u.id FROM delivery_users u
                      LEFT JOIN delivery_partners dp ON dp.user_id = u.id
                      WHERE u.status = 'active' AND (u.city = :city1 OR dp.city = :city2)";
                $s = $this->db->prepare($q);
                $s->execute(['city1' => $city, 'city2' => $city]);
                $ids = $s->fetchAll(\PDO::FETCH_COLUMN);
            }
            foreach ($ids as $id) {
                $stmt->execute([
                    'user_id'   => $id,
                    'user_type' => 'delivery',
                    'title'     => substr($title,   0, 100),
                    'message'   => substr($message, 0, 500),
                    'type'      => $type,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Backward-compatible broadcast alias
     */
    public function broadcast(string $title, string $message, string $type = 'promotion'): int {
        return $this->broadcastTargeted($title, $message, $type, 'all', 'all');
    }

    // ─── Read ─────────────────────────────────────────────────────────────────

    /**
     * Get the unread notification count for a user (used for the bell badge).
     */
    public function countUnread(int $userId, string $userType): int {
        $sql  = "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND user_type = :utype AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId, 'utype' => $userType]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get the latest N notifications for a user (for the dropdown).
     * Returns both read and unread; ordered newest first.
     */
    public function getLatest(int $userId, string $userType, int $limit = 8): array {
        $sql  = "SELECT *, TIMESTAMPDIFF(SECOND, created_at, NOW()) AS seconds_ago
                 FROM notifications WHERE user_id = :uid AND user_type = :utype
                 ORDER BY id DESC LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':utype', $userType, \PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit,  \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all notifications for a user (full notification page), paginated.
     */
    public function getAll(int $userId, string $userType, int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT * FROM notifications WHERE user_id = :uid AND user_type = :utype
                   ORDER BY created_at DESC LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId,  \PDO::PARAM_INT);
        $stmt->bindValue(':utype', $userType, \PDO::PARAM_STR);
        $stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total notifications for a user (for pagination).
     */
    public function countAll(int $userId, string $userType): int {
        $sql  = "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND user_type = :utype";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId, 'utype' => $userType]);
        return (int) $stmt->fetchColumn();
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    /**
     * Mark a single notification as read.
     */
    public function markRead(int $notificationId, int $userId, string $userType): void {
        $sql  = "UPDATE notifications SET is_read = 1
                 WHERE id = :id AND user_id = :uid AND user_type = :utype";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $notificationId, 'uid' => $userId, 'utype' => $userType]);
    }

    /**
     * Mark ALL notifications for a user as read.
     */
    public function markAllRead(int $userId, string $userType): void {
        $sql  = "UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND user_type = :utype AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId, 'utype' => $userType]);
    }
}
