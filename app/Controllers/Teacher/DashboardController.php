<?php
declare(strict_types=1);
namespace App\Controllers\Teacher;
use App\Core\Controller;

class DashboardController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $classSubjects = $this->db->get('SELECT cs.id, cs.class_id, c.name AS class_name, c.section, s.name AS subject_name
            FROM class_subjects cs JOIN classes c ON c.id = cs.class_id JOIN subjects s ON s.id = cs.subject_id
            WHERE cs.teacher_id = ? ORDER BY c.name, s.name', [$teacher['id']]);
        echo $this->view->renderWithLayout('teacher/dashboard', 'layouts/app', [
            'title' => 'Teacher Dashboard', 'content' => $this->view->render('teacher/dashboard', [
                'teacher' => $teacher, 'classSubjects' => $classSubjects,
            ]),
        ]);
    }
}
