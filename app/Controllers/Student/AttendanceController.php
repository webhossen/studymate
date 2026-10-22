<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;

class AttendanceController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $userId = $this->auth->user()['id'];
        $student = $this->db->first('SELECT * FROM student_profiles WHERE user_id = ? LIMIT 1', [$userId]);
        $records = $this->db->get('SELECT ar.status, ar.marked_at, asess.session_date, s.name AS subject_name, c.name AS class_name
            FROM attendance_records ar JOIN attendance_sessions asess ON asess.id = ar.attendance_session_id
            JOIN class_subjects cs ON cs.id = asess.class_subject_id
            JOIN subjects s ON s.id = cs.subject_id JOIN classes c ON c.id = cs.class_id
            WHERE ar.student_id = ? ORDER BY asess.session_date DESC', [$student['id'] ?? 0]);
        $total = count($records);
        $present = count(array_filter($records, fn ($r) => $r['status'] === 'present'));
        $pct = $total > 0 ? round(100 * $present / $total, 2) : 0;
        echo $this->view->renderWithLayout('student/attendance', 'layouts/app', [
            'title' => 'Attendance', 'content' => $this->view->render('student/attendance', [
                'records' => $records, 'total' => $total, 'present' => $present, 'pct' => $pct]),
        ]);
    }
}
