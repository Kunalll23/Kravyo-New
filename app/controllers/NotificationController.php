<?php
/**
 * Kravyo — Notification Controller
 *
 * Routes:
 *   GET  /notifications              — Full inbox page
 *   POST /notifications/mark-read    — Mark one as read (AJAX/form)
 *   POST /notifications/mark-all     — Mark all as read
 *   GET  /notifications/count        — JSON unread count (AJAX polling)
 */

require_once APP_PATH . '/models/Notification.php';

class NotificationController extends Controller {

    private Notification $notifModel;

    public function __construct() {
        Middleware::auth();   // Must be logged in as any user
        $this->notifModel = new Notification();
    }

    /**
     * Resolve who is asking. Admins authenticate via `admin_id` (admins table),
     * everyone else via `user_id` + `user_role`.
     * @return array{0:int,1:string}
     */
    private function identity(): array {
        if (Session::currentContext() === Session::ADMIN_SESSION && Session::has('admin_id')) {
            return [(int) Session::get('admin_id'), 'admin'];
        }
        return [(int) Session::get('user_id'), (string) Session::get('user_role')];
    }

    // ─── Module 1.13 & 2.8 — Full Inbox Page ─────────────────────────────────

    public function index(): void {
        [$userId, $userType] = $this->identity();
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $perPage  = 20;

        $notifications = $this->notifModel->getAll($userId, $userType, $page, $perPage);
        $totalCount    = $this->notifModel->countAll($userId, $userType);
        $totalPages    = (int) ceil($totalCount / $perPage);

        // Auto mark-all-read when opening the inbox
        $this->notifModel->markAllRead($userId, $userType);

        $this->render('customer/notifications', [
            'title'         => 'My Notifications',
            'notifications' => $notifications,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'totalCount'    => $totalCount,
        ]);
    }

    // ─── Mark Single Notification as Read ────────────────────────────────────

    public function markRead(): void {
        Middleware::verifyCsrf();
        [$userId, $userType] = $this->identity();
        $id     = (int) ($_POST['notification_id'] ?? 0);

        if ($id > 0) {
            $this->notifModel->markRead($id, $userId, $userType);
        }

        $redirect = $_POST['redirect'] ?? '/notifications';
        $this->redirect($redirect);
    }

    // ─── Mark All as Read ─────────────────────────────────────────────────────

    public function markAllRead(): void {
        Middleware::verifyCsrf();
        [$userId, $userType] = $this->identity();
        $this->notifModel->markAllRead($userId, $userType);
        Session::setFlash('success', 'All notifications marked as read.');
        $prefix = ['chef' => '/chef', 'admin' => '/admin', 'delivery' => '/delivery'][$userType] ?? '';
        $this->redirect($prefix . '/notifications');
    }

    // ─── AJAX: Unread Count (for bell badge polling) ──────────────────────────

    public function count(): void {
        [$userId, $userType] = $this->identity();
        header('Content-Type: application/json');
        echo json_encode([
            'unread' => $this->notifModel->countUnread($userId, $userType),
        ]);
        exit;
    }

    // ─── AJAX: Latest Dropdown Items ─────────────────────────────────────────

    public function latest(): void {
        [$userId, $userType] = $this->identity();
        $items  = $this->notifModel->getLatest($userId, $userType, 6);
        header('Content-Type: application/json');
        echo json_encode(['notifications' => $items]);
        exit;
    }

    // ─── AJAX: Check for New Notifications ─────────────────────────────────────

    public function apiUnread(): void {
        [$userId, $userType] = $this->identity();
        $lastId = (int) ($_GET['last_id'] ?? 0);
        
        if ($userId <= 0) {
            file_put_contents(APP_PATH . '/../scratch/notif_debug.log', date('H:i:s') . " - No user ID found for ctx: " . Session::currentContext() . "\n", FILE_APPEND);
            header('Content-Type: application/json');
            echo json_encode(['new' => []]);
            exit;
        }

        file_put_contents(APP_PATH . '/../scratch/notif_debug.log', date('H:i:s') . " - Poll from user $userId ($userType) last_id=$lastId\n", FILE_APPEND);

        // Get unread notifications with ID > lastId
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id 
                AND user_type = :user_type 
                AND is_read = 0 
                AND id > :last_id
                ORDER BY id ASC";
                
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'user_type' => $userType,
            'last_id' => $lastId
        ]);
        
        $newNotifs = $stmt->fetchAll();
        $unreadCount = $this->notifModel->countUnread($userId, $userType);
        
        header('Content-Type: application/json');
        echo json_encode([
            'new' => $newNotifs,
            'unread_count' => $unreadCount
        ]);
        exit;
    }
}
