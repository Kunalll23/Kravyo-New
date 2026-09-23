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

    // ─── Write ────────────────────────────────────────────────────────────────

    /**
     * Create a notification for a specific user.
     *
     * @param int    $userId   Target user ID
     * @param string $title    Short heading (max 100 chars)
     * @param string $message  Body text (max 500 chars)
     * @param string $type     One of: order_update, promotion, system_alert, kitchen_update
     * @param string|null $link Optional relative URL e.g. '/order/track/5'
     */
    public function createForUser(int $userId, string $title, string $message, string $type = 'system_alert', ?string $link = null): void {
        $sql = "INSERT INTO notifications (user_id, title, message, type, link)
                VALUES (:user_id, :title, :message, :type, :link)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'title'   => mb_substr($title,   0, 100),
            'message' => mb_substr($message, 0, 500),
            'type'    => $type,
            'link'    => $link,
        ]);
    }

    /**
     * Module 3.11 — Admin broadcasts a message to ALL active users.
     * Inserts one row per user for reliable per-user read tracking.
     */
    public function broadcast(string $title, string $message, string $type = 'promotion'): int {
        $sql = "SELECT id FROM users WHERE status = 'active'";
        $ids = $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($ids)) {
            return 0;
        }

        $count = 0;
        $insert = "INSERT INTO notifications (user_id, title, message, type)
                   VALUES (:user_id, :title, :message, :type)";
        $stmt = $this->db->prepare($insert);
        foreach ($ids as $id) {
            $stmt->execute([
                'user_id' => $id,
                'title'   => mb_substr($title,   0, 100),
                'message' => mb_substr($message, 0, 500),
                'type'    => $type,
            ]);
            $count++;
        }
        return $count;
    }

    // ─── Read ─────────────────────────────────────────────────────────────────

    /**
     * Get the unread notification count for a user (used for the bell badge).
     */
    public function countUnread(int $userId): int {
        $sql  = "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get the latest N notifications for a user (for the dropdown).
     * Returns both read and unread; ordered newest first.
     */
    public function getLatest(int $userId, int $limit = 8): array {
        $sql  = "SELECT * FROM notifications WHERE user_id = :uid
                 ORDER BY created_at DESC LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,  \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all notifications for a user (full notification page), paginated.
     */
    public function getAll(int $userId, int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT * FROM notifications WHERE user_id = :uid
                   ORDER BY created_at DESC LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId,  \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset,  \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total notifications for a user (for pagination).
     */
    public function countAll(int $userId): int {
        $sql  = "SELECT COUNT(*) FROM notifications WHERE user_id = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    /**
     * Mark a single notification as read.
     */
    public function markRead(int $notificationId, int $userId): void {
        $sql  = "UPDATE notifications SET is_read = 1
                 WHERE id = :id AND user_id = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $notificationId, 'uid' => $userId]);
    }

    /**
     * Mark ALL notifications for a user as read.
     */
    public function markAllRead(int $userId): void {
        $sql  = "UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
    }
}
