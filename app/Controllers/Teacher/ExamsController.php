<?php
declare(strict_types=1);
namespace App\Controllers\Teacher;
use App\Core\Controller;
use App\Core\Session;
class ExamsController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $exams = $this->db->get('SELECT e.*, c.name AS class_name, s.name AS subject_name
            FROM exams e JOIN class_subjects cs ON cs.id = e.class_subject_id
            JOIN classes c ON c.id = cs.class_id JOIN subjects s ON s.id = cs.subject_id
            WHERE cs.teacher_id = ? ORDER BY e.exam_date DESC', [$teacher['id']]);
        echo $this->view->renderWithLayout('teacher/exams', 'layouts/app', [
            'title' => 'Exams & Grades', 'content' => $this->view->render('teacher/exams', ['exams' => $exams]),
        ]);
    }
    public function store(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $csId = (int) ($_POST['class_subject_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $examDate = $_POST['exam_date'] ?? null;
        $maxMarks = (float) ($_POST['max_marks'] ?? 100);
        $cs = $this->db->first('SELECT id FROM class_subjects WHERE id = ? AND teacher_id = ? LIMIT 1', [$csId, $teacher['id']]);
        if (!$cs || $title === '') { Session::error('Invalid input.'); $this->redirect(url('teacher/exams')); }
        $id = $this->db->insert('INSERT INTO exams (class_subject_id, title, exam_date, max_marks, created_by) VALUES (?, ?, ?, ?, ?)',
                [$csId, $title, $examDate, $maxMarks, $teacher['id']]);
        $this->audit('exam.create', 'exams', $id, [], ['title' => $title, 'class_subject_id' => $csId]);
        Session::success('Exam created.');
        $this->redirect(url('teacher/exams'));
    }
    public function grades(int $id): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $exam = $this->db->first('SELECT e.* FROM exams e JOIN class_subjects cs ON cs.id = e.class_subject_id
            WHERE e.id = ? AND cs.teacher_id = ? LIMIT 1', [$id, $teacher['id']]);
        if (!$exam) { Session::error('Not found.'); $this->redirect(url('teacher/exams')); }
        $students = $this->db->get('SELECT sp.id, sp.student_id, sp.first_name, sp.last_name, g.marks, g.grade, g.feedback, g.is_published
            FROM student_profiles sp JOIN enrollments e ON e.student_id = sp.id
            LEFT JOIN grades g ON g.exam_id = ? AND g.student_id = sp.id
            WHERE e.class_id = (SELECT class_id FROM class_subjects WHERE id = ?) AND e.status = "active" ORDER BY sp.last_name',
            [$id, $exam['class_subject_id']]);
        echo $this->view->renderWithLayout('teacher/exam_grades', 'layouts/app', [
            'title' => 'Enter Grades', 'content' => $this->view->render('teacher/exam_grades', ['exam' => $exam, 'students' => $students]),
        ]);
    }
    public function saveGrades(int $id): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $exam = $this->db->first('SELECT e.id, e.max_marks, e.class_subject_id FROM exams e JOIN class_subjects cs ON cs.id = e.class_subject_id
            WHERE e.id = ? AND cs.teacher_id = ? LIMIT 1', [$id, $teacher['id']]);
        if (!$exam) { Session::error('Not found.'); $this->redirect(url('teacher/exams')); }
        $maxMarks = (float) $exam['max_marks'];

        /* Only students enrolled in this class may receive a grade. */
        $eligible = [];
        foreach ($this->db->get('SELECT sp.id FROM student_profiles sp JOIN enrollments e ON e.student_id = sp.id
                WHERE e.class_id = (SELECT class_id FROM class_subjects WHERE id = ?) AND e.status = "active"',
            [$exam['class_subject_id']]) as $row) {
            $eligible[(int) $row['id']] = true;
        }

        $saved = 0;
        foreach ($_POST as $key => $value) {
            if (!str_starts_with($key, 'marks_')) continue;

            $studentId = (int) substr($key, 6);
            if ($studentId <= 0 || !isset($eligible[$studentId])) continue;

            $marks = (float) $value;
            if ($marks < 0 || $marks > $maxMarks) continue;

            $grade = trim((string) ($_POST['grade_' . $studentId] ?? ''));
            $published = isset($_POST['publish_' . $studentId]) ? 1 : 0;

            $existing = $this->db->first('SELECT id FROM grades WHERE exam_id = ? AND student_id = ? LIMIT 1', [$id, $studentId]);
            if ($existing) {
                $this->db->update('UPDATE grades SET marks = ?, grade = ?, graded_by = ?, graded_at = NOW(), is_published = ? WHERE id = ?',
                    [$marks, $grade ?: null, $teacher['id'], $published, $existing['id']]);
            } else {
                $this->db->insert('INSERT INTO grades (exam_id, student_id, marks, grade, graded_by, graded_at, is_published) VALUES (?, ?, ?, ?, ?, NOW(), ?)',
                    [$id, $studentId, $marks, $grade ?: null, $teacher['id'], $published]);
            }
            $saved++;
        }
        if ($saved > 0) {
            $this->audit('exam.grade', 'grades', $id, [], ['exam_id' => $id, 'students_graded' => $saved]);
        }
        Session::success($saved > 0 ? 'Grades saved.' : 'No grades were saved.');
        $this->redirect(url('teacher/exams/' . $id . '/grades'));
    }
}
