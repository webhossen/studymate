<?php
declare(strict_types=1);
namespace App\Controllers\Student;

use App\Core\Controller;

class FocusTimerController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $studentId = $this->studentProfileId();

        /* Subjects the student is enrolled in, used to populate the picker. */
        $subjects = $this->db->get(
            'SELECT DISTINCT s.name FROM subjects s
             JOIN class_subjects cs ON cs.subject_id = s.id
             JOIN enrollments e ON e.class_id = cs.class_id
             WHERE e.student_id = ? AND e.status = "active"
             ORDER BY s.name',
            [$studentId]
        );

        /* Lightweight focus statistics for the summary strip. */
        $stats = $this->db->first(
            'SELECT COUNT(*) AS total_sessions,
                    COALESCE(SUM(recorded_duration_seconds), 0) AS total_seconds,
                    COUNT(DISTINCT DATE(started_at)) AS active_days
             FROM study_sessions
             WHERE student_id = ? AND state = "completed"',
            [$studentId]
        ) ?? ['total_sessions' => 0, 'total_seconds' => 0, 'active_days' => 0];

        echo $this->view->renderWithLayout('student/focus_timer', 'layouts/app', [
            'title' => 'Focus Timer',
            'content' => $this->view->render('student/focus_timer', [
                'subjects' => $subjects,
                'stats' => $stats,
            ]),
        ]);
    }
}
