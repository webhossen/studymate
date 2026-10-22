<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;

class SubmissionsController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $studentId = $this->studentProfileId();
        $submissions = $this->db->get('SELECT s.*, a.title AS assignment_title,
            s2.name AS subject_name, c.name AS class_name
            FROM submissions s JOIN assignments a ON a.id = s.assignment_id
            JOIN class_subjects cs ON cs.id = a.class_subject_id
            JOIN subjects s2 ON s2.id = cs.subject_id JOIN classes c ON c.id = cs.class_id
            WHERE s.student_id = ? ORDER BY s.submitted_at DESC', [$studentId]);
        echo $this->view->renderWithLayout('student/submissions', 'layouts/app', [
            'title' => 'My Submissions', 'content' => $this->view->render('student/submissions', ['submissions' => $submissions]),
        ]);
    }
}
