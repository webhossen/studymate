<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;
use App\Core\Session;
class ProfileController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $user = $this->auth->user();
        $profile = $this->db->first('SELECT * FROM student_profiles WHERE user_id = ? LIMIT 1', [$user['id']]);
        echo $this->view->renderWithLayout('student/profile', 'layouts/app', [
            'title' => 'Profile', 'content' => $this->view->render('student/profile', ['profile' => $profile, 'user' => $user]),
        ]);
    }
    public function update(): void {
        $this->requireRole('student');
        $phone = trim($_POST['phone'] ?? '');
        $this->db->update('UPDATE student_profiles SET phone = ? WHERE user_id = ?', [$phone, $this->auth->user()['id']]);
        Session::success('Profile updated.');
        $this->redirect(url('student/profile'));
    }
}
