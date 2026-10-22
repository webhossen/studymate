<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;

class ExamsController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $userId = $this->auth->user()['id'];
        $student = $this->db->first('SELECT * FROM student_profiles WHERE user_id = ? LIMIT 1', [$userId]);
        $exams = $this->db->get('SELECT e.*, s.name AS subject_name, c.name AS class_name,
            g.marks, g.grade, g.feedback, g.is_published
            FROM exams e JOIN class_subjects cs ON cs.id = e.class_subject_id
            JOIN subjects s ON s.id = cs.subject_id JOIN classes c ON c.id = cs.class_id
            JOIN enrollments en ON en.class_id = c.id AND en.student_id = ? AND en.status = "active"
            LEFT JOIN grades g ON g.exam_id = e.id AND g.student_id = ?
            ORDER BY e.exam_date DESC', [$student['id'] ?? 0, $student['id'] ?? 0]);
        echo $this->view->renderWithLayout('student/exams', 'layouts/app', [
            'title' => 'Exams', 'content' => $this->view->render('student/exams', ['exams' => $exams]),
        ]);
    }
}
