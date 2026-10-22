<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\Session;
class ClassSubjectsController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $rows = $this->db->get('SELECT cs.*, c.name AS class_name, s.name AS subject_name, CONCAT(tp.first_name, " ", tp.last_name) AS teacher_name
            FROM class_subjects cs JOIN classes c ON c.id = cs.class_id
            JOIN subjects s ON s.id = cs.subject_id
            JOIN teacher_profiles tp ON tp.id = cs.teacher_id ORDER BY c.name, s.name');
        echo $this->view->renderWithLayout('admin/class_subjects', 'layouts/app', [
            'title' => 'Class Subjects', 'content' => $this->view->render('admin/class_subjects', ['assignments' => $rows]),
        ]);
    }
    public function store(): void {
        $this->requireRole('admin');
        $class = (int) ($_POST['class_id'] ?? 0);
        $subject = (int) ($_POST['subject_id'] ?? 0);
        $teacher = (int) ($_POST['teacher_id'] ?? 0);
        if (!$class || !$subject || !$teacher) { Session::error('All fields required.'); $this->redirect(url('admin/class-subjects')); }
        try {
            $id = $this->db->insert('INSERT INTO class_subjects (class_id, subject_id, teacher_id) VALUES (?, ?, ?)', [$class, $subject, $teacher]);
                $this->audit('class_subject.create', 'class_subjects', $id, [], ['class_id' => $class, 'subject_id' => $subject, 'teacher_id' => $teacher]);
            Session::success('Assigned.');
        } catch (\Throwable $e) { Session::error('Assignment failed: ' . $e->getMessage()); }
        $this->redirect(url('admin/class-subjects'));
    }
}
