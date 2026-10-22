<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\Session;
class EnrollmentsController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $enrollments = $this->db->get('SELECT e.*, CONCAT(sp.first_name, " ", sp.last_name) AS student_name, c.name AS class_name
            FROM enrollments e JOIN student_profiles sp ON sp.id = e.student_id
            JOIN classes c ON c.id = e.class_id ORDER BY e.created_at DESC');
        echo $this->view->renderWithLayout('admin/enrollments', 'layouts/app', [
            'title' => 'Enrollments', 'content' => $this->view->render('admin/enrollments', ['enrollments' => $enrollments]),
        ]);
    }
    public function store(): void {
        $this->requireRole('admin');
        $student = (int) ($_POST['student_id'] ?? 0);
        $class = (int) ($_POST['class_id'] ?? 0);
        if (!$student || !$class) { Session::error('Student and class required.'); $this->redirect(url('admin/enrollments')); }
        try {
            $id = $this->db->insert('INSERT INTO enrollments (student_id, class_id) VALUES (?, ?)', [$student, $class]);
                $this->audit('enrollment.create', 'enrollments', $id, [], ['student_id' => $student, 'class_id' => $class]);
            Session::success('Enrolled.');
        } catch (\Throwable $e) { Session::error('Enrollment failed: ' . $e->getMessage()); }
        $this->redirect(url('admin/enrollments'));
    }
}
