<?php
declare(strict_types=1);
namespace App\Controllers\Api;
use App\Core\Controller;

class NotificationsController extends Controller {
    public function count(): void {
        $this->requireAuth();
        $userId = $this->auth->user()['id'];
        $count = (int) $this->db->first('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0', [$userId])['c'];
        $this->json(['ok' => true, 'count' => $count]);
    }
    public function list(): void {
        $this->requireAuth();
        $notifications = $this->db->get('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20', [$this->auth->user()['id']]);
        $this->json(['ok' => true, 'notifications' => $notifications]);
    }
}
