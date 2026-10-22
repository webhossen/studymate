<?php
declare(strict_types=1);
namespace App\Controllers\Teacher;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Upload;
class ResourcesController extends Controller {
    public function index(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $resources = $this->db->get('SELECT * FROM resources WHERE uploaded_by = ? ORDER BY created_at DESC', [$teacher['id']]);
        echo $this->view->renderWithLayout('teacher/resources', 'layouts/app', [
            'title' => 'Resources', 'content' => $this->view->render('teacher/resources', ['resources' => $resources]),
        ]);
    }
    public function store(): void {
        $this->requireRole('teacher');
        $teacher = $this->db->first('SELECT * FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $filePath = null; $fileOriginal = null; $fileMime = null; $fileSize = null;
        if (!empty($_FILES['resource_file']['tmp_name'])) {
            $upload = new Upload(upload_max_size());
            $result = $upload->store($_FILES['resource_file'], 'resources');
            if (!$result['ok']) { Session::error($result['error']); $this->redirect(url('teacher/resources')); }
            $filePath = $result['path']; $fileOriginal = $result['original_name']; $fileMime = $result['mime']; $fileSize = $result['size'];
        }
        $id = $this->db->insert('INSERT INTO resources (title, description, file_path, file_original_name, file_mime, file_size, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$title, $description, $filePath, $fileOriginal, $fileMime, $fileSize, $teacher['id']]);
        $this->audit('resource.create', 'resources', $id, [], ['title' => $title, 'file' => $fileOriginal]);
        Session::success('Resource uploaded.');
        $this->redirect(url('teacher/resources'));
    }
}
