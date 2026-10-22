<?php
declare(strict_types=1);
namespace App\Controllers\Teacher;
use App\Core\Controller;
use App\Core\Session;
class AnnouncementsController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $announcements = $this->db->get('SELECT * FROM announcements WHERE author_id = ? ORDER BY published_at DESC', [$this->auth->user()['id']]);
        echo $this->view->renderWithLayout('teacher/announcements', 'layouts/app', [
            'title' => 'Announcements', 'content' => $this->view->render('teacher/announcements', ['announcements' => $announcements]),
        ]);
    }
    public function store(): void {
        $this->requireRole('teacher');
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $audience = $_POST['audience'] ?? 'all';
        if ($title === '' || $content === '') { Session::error('Title and content required.'); $this->redirect(url('teacher/announcements')); }
        $this->db->insert('INSERT INTO announcements (title, content, audience, author_id) VALUES (?, ?, ?, ?)',
            [$title, $content, $audience, $this->auth->user()['id']]);
        Session::success('Published.');
        $this->redirect(url('teacher/announcements'));
    }
}
