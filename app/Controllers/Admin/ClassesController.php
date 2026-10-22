<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\Session;
class ClassesController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $classes = $this->db->get('SELECT * FROM classes ORDER BY name, section');
        echo $this->view->renderWithLayout('admin/classes', 'layouts/app', [
            'title' => 'Classes', 'content' => $this->view->render('admin/classes', ['classes' => $classes]),
        ]);
    }
    public function store(): void {
        $this->requireRole('admin');
        $name = trim($_POST['name'] ?? '');
        $section = trim($_POST['section'] ?? '');
        if ($name === '' || $section === '') { Session::error('Name and section required.'); $this->redirect(url('admin/classes')); }
        $id = $this->db->insert('INSERT INTO classes (name, section) VALUES (?, ?)', [$name, $section]);
        $this->audit('class.create', 'classes', $id, [], ['name' => $name, 'section' => $section]);
        Session::success('Class created.');
        $this->redirect(url('admin/classes'));
    }
}
