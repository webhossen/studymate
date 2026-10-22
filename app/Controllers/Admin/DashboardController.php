<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;

class DashboardController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $stats = [
            'students' => (int) $this->db->first('SELECT COUNT(*) AS c FROM users WHERE role = ?', ['student'])['c'],
            'teachers' => (int) $this->db->first('SELECT COUNT(*) AS c FROM users WHERE role = ?', ['teacher'])['c'],
            'classes' => (int) $this->db->first('SELECT COUNT(*) AS c FROM classes')['c'],
            'subjects' => (int) $this->db->first('SELECT COUNT(*) AS c FROM subjects')['c'],
        ];
        echo $this->view->renderWithLayout('admin/dashboard', 'layouts/app', [
            'title' => 'Admin Dashboard', 'stats' => $stats,
            'content' => $this->view->render('admin/dashboard', ['stats' => $stats]),
        ]);
    }
}
