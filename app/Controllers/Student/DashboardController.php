<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;

class DashboardController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $userId = $this->auth->user()['id'];
        $student = $this->db->first('SELECT * FROM student_profiles WHERE user_id = ? LIMIT 1', [$userId]);
        $enrollment = $this->db->first('SELECT c.id, c.name, c.section FROM enrollments e JOIN classes c ON c.id = e.class_id
            WHERE e.student_id = ? AND e.status = "active" LIMIT 1', [$student['id'] ?? 0]);
        $pendingTasks = (int) $this->db->first('SELECT COUNT(*) AS c FROM tasks WHERE user_id = ? AND status != "completed"', [$userId])['c'];
        $completedTasks = (int) $this->db->first('SELECT COUNT(*) AS c FROM tasks WHERE user_id = ? AND status = "completed"', [$userId])['c'];
        $upcomingAssignments = (int) $this->db->first('SELECT COUNT(*) AS c FROM assignments a JOIN class_subjects cs ON cs.id = a.class_subject_id
            WHERE cs.class_id = ? AND a.due_date > NOW()', [$enrollment['id'] ?? 0])['c'];
        $upcomingExams = (int) $this->db->first('SELECT COUNT(*) AS c FROM exams e JOIN class_subjects cs ON cs.id = e.class_subject_id
            WHERE cs.class_id = ? AND (e.exam_date IS NULL OR e.exam_date > CURDATE())', [$enrollment['id'] ?? 0])['c'];
        $unreadNotifications = (int) $this->db->first('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0', [$userId])['c'];
        $recentAnnouncements = $this->db->get('SELECT * FROM announcements WHERE is_published = 1 ORDER BY published_at DESC LIMIT 5');
        $publishedGrades = $this->db->get('SELECT g.marks, g.grade, e.title AS exam_title, s.name AS subject_name
            FROM grades g JOIN exams e ON e.id = g.exam_id JOIN class_subjects cs ON cs.id = e.class_subject_id
            JOIN subjects s ON s.id = cs.subject_id WHERE g.student_id = ? AND g.is_published = 1 ORDER BY g.graded_at DESC LIMIT 5',
            [$student['id'] ?? 0]);
        echo $this->view->renderWithLayout('student/dashboard', 'layouts/app', [
            'title' => 'Student Dashboard', 'content' => $this->view->render('student/dashboard', [
                'student' => $student, 'enrollment' => $enrollment, 'pendingTasks' => $pendingTasks,
                'completedTasks' => $completedTasks, 'upcomingAssignments' => $upcomingAssignments,
                'upcomingExams' => $upcomingExams, 'unreadNotifications' => $unreadNotifications,
                'recentAnnouncements' => $recentAnnouncements, 'publishedGrades' => $publishedGrades,
            ]),
        ]);
    }
}
