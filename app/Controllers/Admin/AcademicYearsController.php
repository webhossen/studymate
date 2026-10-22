<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\Session;
class AcademicYearsController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $years = $this->db->get('SELECT * FROM academic_years ORDER BY name');
        echo $this->view->renderWithLayout('admin/academic_years', 'layouts/app', [
            'title' => 'Academic Years', 'content' => $this->view->render('admin/academic_years', ['years' => $years]),
        ]);
    }
    public function store(): void {
        $this->requireRole('admin');
        $name = trim($_POST['name'] ?? '');
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';
        if ($name === '' || $start === '' || $end === '') { Session::error('All fields required.'); $this->redirect(url('admin/academic-years')); }
        $id = $this->db->insert('INSERT INTO academic_years (name, start_date, end_date) VALUES (?, ?, ?)', [$name, $start, $end]);
        $this->audit('academic_year.create', 'academic_years', $id, [], ['name' => $name, 'start_date' => $start, 'end_date' => $end]);
        Session::success('Academic year created.');
        $this->redirect(url('admin/academic-years'));
    }
}
