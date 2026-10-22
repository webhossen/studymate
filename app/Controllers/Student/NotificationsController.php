<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;
use App\Core\Session;
class NotificationsController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $notifications = $this->db->get('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50', [$this->auth->user()['id']]);
        echo $this->view->renderWithLayout('student/notifications', 'layouts/app', [
            'title' => 'Notifications', 'content' => $this->view->render('student/notifications', ['notifications' => $notifications]),
        ]);
    }
    public function read(int $id): void {
        $this->requireRole('student');
        $this->db->update('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?', [$id, $this->auth->user()['id']]);
        Session::success('Marked as read.');
        $this->redirect(url('student/notifications'));
    }
}
