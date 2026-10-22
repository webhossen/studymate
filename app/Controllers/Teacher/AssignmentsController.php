<?php
declare(strict_types=1);
namespace App\Controllers\Teacher;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Upload;
class AssignmentsController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $assignments = $this->db->get('SELECT a.*, c.name AS class_name, s.name AS subject_name
            FROM assignments a JOIN class_subjects cs ON cs.id = a.class_subject_id
            JOIN classes c ON c.id = cs.class_id JOIN subjects s ON s.id = cs.subject_id
            WHERE cs.teacher_id = ? ORDER BY a.due_date DESC', [$teacher['id']]);
        echo $this->view->renderWithLayout('teacher/assignments', 'layouts/app', [
            'title' => 'Assignments', 'content' => $this->view->render('teacher/assignments', ['assignments' => $assignments]),
        ]);
    }
    public function create(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $classSubjects = $this->db->get('SELECT cs.id, c.name AS class_name, s.name AS subject_name
            FROM class_subjects cs JOIN classes c ON c.id = cs.class_id JOIN subjects s ON s.id = cs.subject_id
            WHERE cs.teacher_id = ? ORDER BY c.name, s.name', [$teacher['id']]);
        echo $this->view->renderWithLayout('teacher/assignment_form', 'layouts/app', [
            'title' => 'Create Assignment', 'content' => $this->view->render('teacher/assignment_form', [
                'classSubjects' => $classSubjects, 'assignment' => null, 'action' => url('teacher/assignments')]),
        ]);
    }
    public function store(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $csId = (int) ($_POST['class_subject_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $dueDate = $_POST['due_date'] ?? '';
        $maxMarks = (float) ($_POST['max_marks'] ?? 100);
        $cs = $this->db->first('SELECT id FROM class_subjects WHERE id = ? AND teacher_id = ? LIMIT 1', [$csId, $teacher['id']]);
        if (!$cs || $title === '' || $dueDate === '') { Session::error('Invalid input.'); $this->redirect(url('teacher/assignments/create')); }
        $filePath = null; $fileOriginal = null; $fileMime = null; $fileSize = null;
        if (!empty($_FILES['assignment_file']['tmp_name'])) {
            $upload = new Upload(upload_max_size());
            $result = $upload->store($_FILES['assignment_file'], 'assignments');
            if (!$result['ok']) { Session::error($result['error']); $this->redirect(url('teacher/assignments/create')); }
            $filePath = $result['path']; $fileOriginal = $result['original_name']; $fileMime = $result['mime']; $fileSize = $result['size'];
        }
        $id = $this->db->insert('INSERT INTO assignments (class_subject_id, title, description, due_date, max_marks, file_path, file_original_name, file_mime, file_size, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$csId, $title, $description, $dueDate, $maxMarks, $filePath, $fileOriginal, $fileMime, $fileSize, $teacher['id']]);
        $this->audit('assignment.create', 'assignments', $id, [], ['title' => $title, 'class_subject_id' => $csId, 'due_date' => $dueDate]);
        Session::success('Assignment created.');
        $this->redirect(url('teacher/assignments'));
    }
    public function show(int $id): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $assignment = $this->db->first('SELECT a.* FROM assignments a JOIN class_subjects cs ON cs.id = a.class_subject_id
            WHERE a.id = ? AND cs.teacher_id = ? LIMIT 1', [$id, $teacher['id']]);
        if (!$assignment) { Session::error('Not found.'); $this->redirect(url('teacher/assignments')); }
        $submissions = $this->db->get('SELECT s.*, sp.first_name, sp.last_name, sp.student_id
            FROM submissions s JOIN student_profiles sp ON sp.id = s.student_id
            WHERE s.assignment_id = ? ORDER BY s.submitted_at DESC', [$id]);
        echo $this->view->renderWithLayout('teacher/assignment_show', 'layouts/app', [
            'title' => 'Assignment Details', 'content' => $this->view->render('teacher/assignment_show', [
                'assignment' => $assignment, 'submissions' => $submissions]),
        ]);
    }
}
