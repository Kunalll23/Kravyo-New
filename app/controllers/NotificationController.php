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

    // ─── Module 1.13 & 2.8 — Full Inbox Page ─────────────────────────────────

    public function index(): void {
        $userId  = (int) Session::get('user_id');
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $notifications = $this->notifModel->getAll($userId, $page, $perPage);
        $totalCount    = $this->notifModel->countAll($userId);
        $totalPages    = (int) ceil($totalCount / $perPage);

        // Auto mark-all-read when opening the inbox
        $this->notifModel->markAllRead($userId);

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
        $userId = (int) Session::get('user_id');
        $id     = (int) ($_POST['notification_id'] ?? 0);

        if ($id > 0) {
            $this->notifModel->markRead($id, $userId);
        }

        $redirect = $_POST['redirect'] ?? '/notifications';
        $this->redirect($redirect);
    }

    // ─── Mark All as Read ─────────────────────────────────────────────────────

    public function markAllRead(): void {
        Middleware::verifyCsrf();
        $userId = (int) Session::get('user_id');
        $this->notifModel->markAllRead($userId);
        Session::setFlash('success', 'All notifications marked as read.');
        $this->redirect('/notifications');
    }

    // ─── AJAX: Unread Count (for bell badge polling) ──────────────────────────

    public function count(): void {
        $userId = (int) Session::get('user_id');
        header('Content-Type: application/json');
        echo json_encode([
            'unread' => $this->notifModel->countUnread($userId),
        ]);
        exit;
    }

    // ─── AJAX: Latest Dropdown Items ─────────────────────────────────────────

    public function latest(): void {
        $userId = (int) Session::get('user_id');
        $items  = $this->notifModel->getLatest($userId, 6);
        header('Content-Type: application/json');
        echo json_encode(['notifications' => $items]);
        exit;
    }
}
