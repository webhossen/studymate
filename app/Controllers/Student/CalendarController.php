<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;

class CalendarController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $userId = $this->auth->user()['id'];
        $student = $this->db->first('SELECT * FROM student_profiles WHERE user_id = ? LIMIT 1', [$userId]);
        $enrollment = $this->db->first('SELECT c.id FROM enrollments e JOIN classes c ON c.id = e.class_id WHERE e.student_id = ? AND e.status = "active" LIMIT 1', [$student['id'] ?? 0]);
        $events = [];
        if ($enrollment) {
            $events = $this->db->get('SELECT e.title AS title, e.exam_date AS start, "exam" AS type FROM exams e
                JOIN class_subjects cs ON cs.id = e.class_subject_id WHERE cs.class_id = ? AND e.exam_date IS NOT NULL',
                [$enrollment['id']]);
            $events = array_merge($events, $this->db->get('SELECT a.title AS title, a.due_date AS start, "assignment" AS type FROM assignments a
                JOIN class_subjects cs ON cs.id = a.class_subject_id WHERE cs.class_id = ? AND a.due_date IS NOT NULL',
                [$enrollment['id']]));
            usort($events, static fn($a, $b) => strcmp((string) $a['start'], (string) $b['start']));
        }
        echo $this->view->renderWithLayout('student/calendar', 'layouts/app', [
            'title' => 'Calendar', 'content' => $this->view->render('student/calendar', ['events' => $events]),
        ]);
    }
}
