<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\Session;
class AnnouncementsController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $announcements = $this->db->get('SELECT a.*, u.email AS author_email FROM announcements a LEFT JOIN users u ON u.id = a.author_id ORDER BY a.published_at DESC');
        echo $this->view->renderWithLayout('admin/announcements', 'layouts/app', [
            'title' => 'Announcements', 'content' => $this->view->render('admin/announcements', ['announcements' => $announcements]),
        ]);
    }
    public function store(): void {
        $this->requireRole('admin');
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $audience = $_POST['audience'] ?? 'all';
        if ($title === '' || $content === '') { Session::error('Title and content required.'); $this->redirect(url('admin/announcements')); }
        $id = $this->db->insert('INSERT INTO announcements (title, content, audience, author_id) VALUES (?, ?, ?, ?)',
                [$title, $content, $audience, $this->auth->user()['id']]);
        $this->audit('announcement.create', 'announcements', $id, [], ['title' => $title, 'audience' => $audience]);
        Session::success('Published.');
        $this->redirect(url('admin/announcements'));
    }
}
