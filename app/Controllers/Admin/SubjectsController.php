<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\Session;
class SubjectsController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $subjects = $this->db->get('SELECT * FROM subjects ORDER BY name');
        echo $this->view->renderWithLayout('admin/subjects', 'layouts/app', [
            'title' => 'Subjects', 'content' => $this->view->render('admin/subjects', ['subjects' => $subjects]),
        ]);
    }
    public function store(): void {
        $this->requireRole('admin');
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        if ($name === '' || $code === '') { Session::error('Name and code required.'); $this->redirect(url('admin/subjects')); }
        $id = $this->db->insert('INSERT INTO subjects (name, code) VALUES (?, ?)', [$name, $code]);
        $this->audit('subject.create', 'subjects', $id, [], ['name' => $name, 'code' => $code]);
        Session::success('Subject created.');
        $this->redirect(url('admin/subjects'));
    }
}
