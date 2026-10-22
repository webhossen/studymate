<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;

class GradesController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $userId = $this->auth->user()['id'];
        $student = $this->db->first('SELECT * FROM student_profiles WHERE user_id = ? LIMIT 1', [$userId]);
        $grades = $this->db->get('SELECT g.*, e.title AS exam_title, s.name AS subject_name, e.max_marks
            FROM grades g JOIN exams e ON e.id = g.exam_id
            JOIN class_subjects cs ON cs.id = e.class_subject_id
            JOIN subjects s ON s.id = cs.subject_id
            WHERE g.student_id = ? AND g.is_published = 1 ORDER BY g.graded_at DESC', [$student['id'] ?? 0]);
        echo $this->view->renderWithLayout('student/grades', 'layouts/app', [
            'title' => 'Grades', 'content' => $this->view->render('student/grades', ['grades' => $grades]),
        ]);
    }
}
