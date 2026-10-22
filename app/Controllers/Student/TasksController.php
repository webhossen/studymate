<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;
use App\Core\Session;
class TasksController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $userId = $this->auth->user()['id'];
        $tasks = $this->db->get('SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date IS NULL, due_date', [$userId]);
        echo $this->view->renderWithLayout('student/tasks', 'layouts/app', [
            'title' => 'Tasks', 'content' => $this->view->render('student/tasks', ['tasks' => $tasks]),
        ]);
    }
    public function store(): void {
        $this->requireRole('student');
        $title = trim($_POST['title'] ?? '');
        if ($title === '') { Session::error('Title required.'); $this->redirect(url('student/tasks')); }
        $this->db->insert('INSERT INTO tasks (user_id, title, description, subject, due_date, priority) VALUES (?, ?, ?, ?, ?, ?)',
            [$this->auth->user()['id'], $title, trim($_POST['description'] ?? ''), trim($_POST['subject'] ?? ''),
            $_POST['due_date'] ?? null, $_POST['priority'] ?? 'medium']);
        Session::success('Task created.');
        $this->redirect(url('student/tasks'));
    }
    public function update(int $id): void {
        $this->requireRole('student');
        $task = $this->db->first('SELECT id FROM tasks WHERE id = ? AND user_id = ? LIMIT 1', [$id, $this->auth->user()['id']]);
        if (!$task) { Session::error('Not found.'); $this->redirect(url('student/tasks')); }
        $status = $_POST['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'], true)) $status = 'pending';
        $data = ['title' => trim($_POST['title'] ?? ''), 'description' => trim($_POST['description'] ?? ''),
            'subject' => trim($_POST['subject'] ?? ''), 'due_date' => $_POST['due_date'] ?? null,
            'priority' => $_POST['priority'] ?? 'medium', 'status' => $status];
        if ($status === 'completed') $data['completed_at'] = date('Y-m-d H:i:s');
        $this->db->updateById($id, $data);
        Session::success('Task updated.');
        $this->redirect(url('student/tasks'));
    }
    public function delete(int $id): void {
        $this->requireRole('student');
        $task = $this->db->first('SELECT id FROM tasks WHERE id = ? AND user_id = ? LIMIT 1', [$id, $this->auth->user()['id']]);
        if (!$task) { Session::error('Not found.'); $this->redirect(url('student/tasks')); }
        $this->db->delete('DELETE FROM tasks WHERE id = ? AND user_id = ?', [$id, $this->auth->user()['id']]);
        Session::success('Task deleted.');
        $this->redirect(url('student/tasks'));
    }
}
