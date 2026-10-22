<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;

class SessionsController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $sessions = $this->db->get('SELECT * FROM study_sessions WHERE student_id = ? ORDER BY started_at DESC LIMIT 50', [$this->studentProfileId()]);
        echo $this->view->renderWithLayout('student/sessions', 'layouts/app', [
            'title' => 'Study Sessions', 'content' => $this->view->render('student/sessions', ['sessions' => $sessions]),
        ]);
    }
}
