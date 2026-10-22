<?php
declare(strict_types=1);
namespace App\Controllers\Teacher;
use App\Core\Controller;
use App\Core\Session;
class AttendanceController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $classSubjects = $this->db->get('SELECT cs.id, c.name AS class_name, s.name AS subject_name
            FROM class_subjects cs JOIN classes c ON c.id = cs.class_id JOIN subjects s ON s.id = cs.subject_id
            WHERE cs.teacher_id = ? ORDER BY c.name, s.name', [$teacher['id']]);

        $classSubjectId = (int) ($_GET['class_subject_id'] ?? 0);
        $sessionDate = $_GET['session_date'] ?? date('Y-m-d');

        /* Roster for the selected class/subject — fetched here so the view stays free of queries. */
        $students = [];
        if ($classSubjectId) {
            $students = $this->db->get(
                'SELECT sp.id, sp.first_name, sp.last_name, sp.student_id
                 FROM enrollments e JOIN student_profiles sp ON sp.id = e.student_id
                 WHERE e.class_id = (SELECT class_id FROM class_subjects WHERE id = ?) AND e.status = "active"
                 ORDER BY sp.last_name',
                [$classSubjectId]
            );
        }

        echo $this->view->renderWithLayout('teacher/attendance', 'layouts/app', [
            'title' => 'Attendance', 'content' => $this->view->render('teacher/attendance', [
                'classSubjects' => $classSubjects, 'classSubjectId' => $classSubjectId,
                'sessionDate' => $sessionDate, 'students' => $students,
            ]),
        ]);
    }
    public function mark(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $csId = (int) ($_POST['class_subject_id'] ?? 0);
        $date = $_POST['session_date'] ?? date('Y-m-d');
        $cs = $this->db->first('SELECT id, class_id FROM class_subjects WHERE id = ? AND teacher_id = ? LIMIT 1', [$csId, $teacher['id']]);
        if (!$cs) { Session::error('Not authorized.'); $this->redirect(url('teacher/attendance')); }
        $session = $this->db->first('SELECT id FROM attendance_sessions WHERE class_subject_id = ? AND session_date = ? LIMIT 1', [$csId, $date]);
        if (!$session) {
            $sessionId = $this->db->insert('INSERT INTO attendance_sessions (class_subject_id, session_date, recorded_by) VALUES (?, ?, ?)',
                [$csId, $date, $teacher['id']]);
        } else {
            $sessionId = $session['id'];
        }
        $students = $this->db->get('SELECT sp.id FROM enrollments e JOIN student_profiles sp ON sp.id = e.student_id
            WHERE e.class_id = ? AND e.status = "active"', [$cs['class_id']]);
        $marked = 0;
        foreach ($students as $st) {
            $sid = (int) $st['id'];
            $status = $_POST['status_' . $sid] ?? 'absent';
            if (!in_array($status, ['present', 'absent', 'late', 'excused'], true)) $status = 'absent';
            $existing = $this->db->first('SELECT id FROM attendance_records WHERE attendance_session_id = ? AND student_id = ? LIMIT 1', [$sessionId, $sid]);
            if ($existing) {
                $this->db->update('UPDATE attendance_records SET status = ?, marked_by = ?, marked_at = NOW() WHERE id = ?',
                    [$status, $teacher['id'], $existing['id']]);
            } else {
                $this->db->insert('INSERT INTO attendance_records (attendance_session_id, student_id, status, marked_by) VALUES (?, ?, ?, ?)',
                    [$sessionId, $sid, $status, $teacher['id']]);
            }
            $marked++;
        }
        $this->audit('attendance.mark', 'attendance_records', $sessionId, [],
            ['class_subject_id' => $csId, 'session_date' => $date, 'students_marked' => $marked]);
        Session::success('Attendance saved.');
        $this->redirect(url('teacher/attendance') . '?class_subject_id=' . $csId . '&session_date=' . urlencode($date));
    }
}
