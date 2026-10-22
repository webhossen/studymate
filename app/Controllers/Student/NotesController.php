<?php
declare(strict_types=1);
namespace App\Controllers\Student;
use App\Core\Controller;
use App\Core\Session;
class NotesController extends Controller {
    public function index(): void {
        $this->requireRole('student');
        $userId = $this->auth->user()['id'];
        $notes = $this->db->get('SELECT * FROM notes WHERE user_id = ? ORDER BY updated_at DESC', [$userId]);
        echo $this->view->renderWithLayout('student/notes', 'layouts/app', [
            'title' => 'Notes', 'content' => $this->view->render('student/notes', ['notes' => $notes]),
        ]);
    }
    public function store(): void {
        $this->requireRole('student');
        $title = trim($_POST['title'] ?? '');
        if ($title === '') { Session::error('Title required.'); $this->redirect(url('student/notes')); }
        $this->db->insert('INSERT INTO notes (user_id, title, content, subject) VALUES (?, ?, ?, ?)',
            [$this->auth->user()['id'], $title, $_POST['content'] ?? '', trim($_POST['subject'] ?? '')]);
        Session::success('Note saved.');
        $this->redirect(url('student/notes'));
    }
    public function show(int $id): void {
        $this->requireRole('student');
        $note = $this->db->first('SELECT * FROM notes WHERE id = ? AND user_id = ? LIMIT 1', [$id, $this->auth->user()['id']]);
        if (!$note) { Session::error('Not found.'); $this->redirect(url('student/notes')); }
        echo $this->view->renderWithLayout('student/note_show', 'layouts/app', [
            'title' => 'Note', 'content' => $this->view->render('student/note_show', ['note' => $note]),
        ]);
    }
    public function update(int $id): void {
        $this->requireRole('student');
        $note = $this->db->first('SELECT id FROM notes WHERE id = ? AND user_id = ? LIMIT 1', [$id, $this->auth->user()['id']]);
        if (!$note) { Session::error('Not found.'); $this->redirect(url('student/notes')); }
        $this->db->updateById($id, ['title' => trim($_POST['title'] ?? ''), 'content' => $_POST['content'] ?? '', 'subject' => trim($_POST['subject'] ?? '')]);
        Session::success('Note updated.');
        $this->redirect(url('student/notes'));
    }
    public function delete(int $id): void {
        $this->requireRole('student');
        $note = $this->db->first('SELECT id FROM notes WHERE id = ? AND user_id = ? LIMIT 1', [$id, $this->auth->user()['id']]);
        if (!$note) { Session::error('Not found.'); $this->redirect(url('student/notes')); }
        $this->db->delete('DELETE FROM notes WHERE id = ? AND user_id = ?', [$id, $this->auth->user()['id']]);
        Session::success('Note deleted.');
        $this->redirect(url('student/notes'));
    }
}
