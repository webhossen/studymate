<?php
declare(strict_types=1);
namespace App\Controllers\Teacher;
use App\Core\Controller;

class ClassesController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $classes = $this->db->get('SELECT cs.id, c.name, c.section, s.name AS subject_name
            FROM class_subjects cs JOIN classes c ON c.id = cs.class_id JOIN subjects s ON s.id = cs.subject_id
            WHERE cs.teacher_id = ? ORDER BY c.name, s.name', [$teacher['id']]);
        echo $this->view->renderWithLayout('teacher/classes', 'layouts/app', [
            'title' => 'My Classes', 'content' => $this->view->render('teacher/classes', ['classes' => $classes]),
        ]);
    }
    public function roster(int $id): void {
        $this->requireRole('teacher');
        $students = $this->db->get('SELECT sp.student_id, sp.first_name, sp.last_name
            FROM enrollments e JOIN student_profiles sp ON sp.id = e.student_id
            WHERE e.class_id = ? AND e.status = "active" ORDER BY sp.last_name', [$id]);
        echo $this->view->renderWithLayout('teacher/roster', 'layouts/app', [
            'title' => 'Class Roster', 'content' => $this->view->render('teacher/roster', ['classId' => $id, 'students' => $students]),
        ]);
    }
}
