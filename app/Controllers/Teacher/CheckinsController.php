<?php
declare(strict_types=1);
namespace App\Controllers\Teacher;
use App\Core\Controller;

class CheckinsController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $checkins = $this->db->get('SELECT sc.*, ss.subject, sp.first_name, sp.last_name
            FROM study_checkins sc JOIN study_sessions ss ON ss.id = sc.study_session_id
            JOIN student_profiles sp ON sp.id = sc.student_id
            JOIN enrollments e ON e.student_id = sc.student_id AND e.status = "active"
            WHERE e.class_id IN (SELECT class_id FROM class_subjects WHERE teacher_id = ?)
            ORDER BY sc.checked_in_at DESC LIMIT 100', [$teacher['id']]);
        echo $this->view->renderWithLayout('teacher/checkins', 'layouts/app', [
            'title' => 'Check-ins', 'content' => $this->view->render('teacher/checkins', ['checkins' => $checkins]),
        ]);
    }
}
