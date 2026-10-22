<?php
declare(strict_types=1);
namespace App\Controllers\Api;
use App\Core\Controller;

class TeacherLiveSessionsController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $classSubjectId = (int) ($_GET['class_subject_id'] ?? 0);
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        /*
         * The teacher filter is applied in the JOIN below, so it must be the
         * first bound parameter. The WHERE clause starts with the enrolment
         * status to keep the placeholder order aligned with $params.
         */
        $wheres = ['e.status = ?'];
        $params = [$teacher['id'], 'active'];
        if ($classSubjectId) {
            $cs = $this->db->first('SELECT id FROM class_subjects WHERE id = ? AND teacher_id = ? LIMIT 1', [$classSubjectId, $teacher['id']]);
            if (!$cs) { $this->json(['ok' => false, 'error' => 'Not authorized.'], 403); }
            $wheres[] = 'cs.id = ?';
            $params[] = $classSubjectId;
        }
        if ($studentId) { $wheres[] = 'ss.student_id = ?'; $params[] = $studentId; }
        $wheres[] = 'DATE(ss.started_at) BETWEEN ? AND ?';
        $params[] = $dateFrom; $params[] = $dateTo;
        $whereSql = implode(' AND ', $wheres);
        $sessions = $this->db->get(
            "SELECT ss.*, sp.student_id, sp.first_name, sp.last_name, u.email,
                    c.name AS class_name, s.name AS subject_name
             FROM study_sessions ss
             JOIN student_profiles sp ON sp.id = ss.student_id
             JOIN users u ON u.id = sp.user_id
             JOIN enrollments e ON e.student_id = sp.id
             JOIN classes c ON c.id = e.class_id
             JOIN class_subjects cs ON cs.class_id = c.id AND cs.teacher_id = ?
             JOIN subjects s ON s.id = cs.subject_id
             WHERE {$whereSql}
             GROUP BY ss.id ORDER BY ss.started_at DESC", $params);
        $threshold = stale_session_threshold();
        $now = time();
        foreach ($sessions as &$s) {
            $s['stale'] = false;
            $heartbeat = $s['last_heartbeat_at'] ? strtotime($s['last_heartbeat_at']) : ($s['started_at'] ? strtotime($s['started_at']) : 0);
            if ($s['state'] === 'active' && ($now - $heartbeat) > $threshold) {
                $s['stale'] = true; $s['display_status'] = 'No recent update';
            } else { $s['display_status'] = ucfirst($s['state']); }
            $s['duration_minutes'] = $s['recorded_duration_seconds'] ? round($s['recorded_duration_seconds'] / 60, 1) : 0;
        }
        $this->json(['ok' => true, 'sessions' => $sessions]);
    }
}
