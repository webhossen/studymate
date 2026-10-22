<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Upload;
class AssignmentsController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $userId = $this->auth->user()['id'];
        $student = $this->db->first('SELECT * FROM student_profiles WHERE user_id = ? LIMIT 1', [$userId]);
        $assignments = $this->db->get('SELECT a.*, s.name AS subject_name, c.name AS class_name,
            sub.id AS submission_id, sub.submitted_at, sub.marks, sub.grade, sub.feedback, sub.is_published,
            sub.is_late, sub.file_path AS submission_file_path, sub.file_original_name AS submission_file_name
            FROM assignments a JOIN class_subjects cs ON cs.id = a.class_subject_id
            JOIN subjects s ON s.id = cs.subject_id JOIN classes c ON c.id = cs.class_id
            JOIN enrollments en ON en.class_id = c.id AND en.student_id = ? AND en.status = "active"
            LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
            ORDER BY a.due_date DESC', [$student['id'] ?? 0, $student['id'] ?? 0]);
        echo $this->view->renderWithLayout('student/assignments', 'layouts/app', [
            'title' => 'Assignments', 'content' => $this->view->render('student/assignments', ['assignments' => $assignments]),
        ]);
    }
    public function submit(int $id): void {
        $this->requireRole('student');
        $userId = $this->auth->user()['id'];
        $student = $this->db->first('SELECT * FROM student_profiles WHERE user_id = ? LIMIT 1', [$userId]);
        $assignment = $this->db->first('SELECT a.*, cs.class_id FROM assignments a JOIN class_subjects cs ON cs.id = a.class_subject_id WHERE a.id = ? LIMIT 1', [$id]);
        if (!$assignment) { Session::error('Not found.'); $this->redirect(url('student/assignments')); }
        $enrolled = $this->db->first('SELECT id FROM enrollments WHERE student_id = ? AND class_id = ? AND status = "active" LIMIT 1',
            [$student['id'] ?? 0, $assignment['class_id']]);
        if (!$enrolled) { Session::error('Not enrolled.'); $this->redirect(url('student/assignments')); }
        $filePath = null; $fileOriginal = null; $fileMime = null; $fileSize = null;
        if (!empty($_FILES['submission_file']['tmp_name'])) {
            $upload = new Upload(upload_max_size());
            $result = $upload->store($_FILES['submission_file'], 'submissions');
            if (!$result['ok']) { Session::error($result['error']); $this->redirect(url('student/assignments')); }
            $filePath = $result['path']; $fileOriginal = $result['original_name']; $fileMime = $result['mime']; $fileSize = $result['size'];
        }
        $isLate = strtotime(date('Y-m-d H:i:s')) > strtotime($assignment['due_date']) ? 1 : 0;
        $studentId = (int) ($student['id'] ?? 0);

        /*
         * A student may only have one submission per assignment (unique key on
         * assignment_id + student_id), so a re-upload replaces the previous
         * attempt and bumps the attempt counter.
         */
        $existing = $this->db->first(
            'SELECT id, submission_attempt FROM submissions WHERE assignment_id = ? AND student_id = ? LIMIT 1',
            [$id, $studentId]
        );

        if ($existing) {
            $this->db->update(
                'UPDATE submissions SET file_path = ?, file_original_name = ?, file_mime = ?, file_size = ?,
                    submitted_at = ?, is_late = ?, submission_attempt = ?, marks = NULL, grade = NULL,
                    feedback = NULL, is_published = 0, graded_at = NULL
                 WHERE id = ?',
                [$filePath, $fileOriginal, $fileMime, $fileSize, date('Y-m-d H:i:s'), $isLate,
                    (int) $existing['submission_attempt'] + 1, (int) $existing['id']]
            );
            Session::success('Submission updated.');
            $this->redirect(url('student/assignments'));
        }

        $this->db->insert('INSERT INTO submissions (assignment_id, student_id, file_path, file_original_name, file_mime, file_size, submitted_at, is_late, submission_attempt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$id, $studentId, $filePath, $fileOriginal, $fileMime, $fileSize, date('Y-m-d H:i:s'), $isLate, 1]);
        Session::success('Submitted.');
        $this->redirect(url('student/assignments'));
    }
}
