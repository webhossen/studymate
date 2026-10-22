<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\Session;
class DepartmentsController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $departments = $this->db->get('SELECT * FROM departments ORDER BY name');
        echo $this->view->renderWithLayout('admin/departments', 'layouts/app', [
            'title' => 'Departments', 'content' => $this->view->render('admin/departments', ['departments' => $departments]),
        ]);
    }
    public function store(): void {
        $this->requireRole('admin');
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        if ($name === '' || $code === '') { Session::error('Name and code required.'); $this->redirect(url('admin/departments')); }
        $id = $this->db->insert('INSERT INTO departments (name, code) VALUES (?, ?)', [$name, $code]);
        $this->audit('department.create', 'departments', $id, [], ['name' => $name, 'code' => $code]);
        Session::success('Department created.');
        $this->redirect(url('admin/departments'));
    }
}
