<?php
declare(strict_types=1);
namespace App\Controllers\Teacher;
use App\Core\Controller;
use App\Core\Session;
class SubmissionsController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $submissions = $this->db->get('SELECT s.*, a.title AS assignment_title, sp.first_name, sp.last_name
            FROM submissions s JOIN assignments a ON a.id = s.assignment_id
            JOIN class_subjects cs ON cs.id = a.class_subject_id
            JOIN student_profiles sp ON sp.id = s.student_id
            WHERE cs.teacher_id = ? ORDER BY s.submitted_at DESC', [$teacher['id']]);
        echo $this->view->renderWithLayout('teacher/submissions', 'layouts/app', [
            'title' => 'Submissions', 'content' => $this->view->render('teacher/submissions', ['submissions' => $submissions]),
        ]);
    }
    public function gradeForm(int $id): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $submission = $this->db->first('SELECT s.*, a.title AS assignment_title, a.max_marks, sp.first_name, sp.last_name
            FROM submissions s JOIN assignments a ON a.id = s.assignment_id
            JOIN class_subjects cs ON cs.id = a.class_subject_id
            JOIN student_profiles sp ON sp.id = s.student_id
            WHERE s.id = ? AND cs.teacher_id = ? LIMIT 1', [$id, $teacher['id']]);
        if (!$submission) { Session::error('Not found.'); $this->redirect(url('teacher/submissions')); }
        echo $this->view->renderWithLayout('teacher/submission_grade', 'layouts/app', [
            'title' => 'Grade Submission', 'content' => $this->view->render('teacher/submission_grade', ['submission' => $submission]),
        ]);
    }
    public function grade(int $id): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $submission = $this->db->first('SELECT s.*, a.max_marks FROM submissions s
            JOIN assignments a ON a.id = s.assignment_id
            JOIN class_subjects cs ON cs.id = a.class_subject_id
            WHERE s.id = ? AND cs.teacher_id = ? LIMIT 1', [$id, $teacher['id']]);
        if (!$submission) { Session::error('Not found.'); $this->redirect(url('teacher/submissions')); }
        $marks = (float) ($_POST['marks'] ?? 0);
        $grade = trim($_POST['grade'] ?? '');
        $feedback = trim($_POST['feedback'] ?? '');
        $publish = isset($_POST['is_published']) ? 1 : 0;
        if ($marks < 0 || $marks > (float) $submission['max_marks']) { Session::error('Invalid marks.'); $this->redirect(url('teacher/submissions/' . $id . '/grade')); }
        $this->db->update('UPDATE submissions SET marks = ?, grade = ?, feedback = ?, graded_by = ?, graded_at = NOW(), is_published = ? WHERE id = ?',
            [$marks, $grade, $feedback, $teacher['id'], $publish]);
        $this->audit('submission.grade', 'submissions', $id, [], ['marks' => $marks, 'grade' => $grade, 'is_published' => $publish]);
        Session::success('Graded.');
        $this->redirect(url('teacher/submissions'));
    }
}
