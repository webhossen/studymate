<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Upload;

/**
 * Streams uploaded attachments to authorised users only.
 *
 * Files live outside the public web root (storage/private_uploads), so every
 * download has to be authorised here before the bytes are sent.
 */
class FilesController extends Controller {

    /** Teacher attachment for an assignment. */
    public function assignment(int $id): void {
        $this->requireAuth();
        $user = $this->auth->user();
        $row = $this->db->first(
            'SELECT a.file_path, a.file_original_name, a.file_mime, cs.teacher_id
             FROM assignments a JOIN class_subjects cs ON cs.id = a.class_subject_id
             WHERE a.id = ? LIMIT 1',
            [$id]
        );
        if (!$row || !$row['file_path']) { Session::error('File not found.'); $this->redirect(url('teacher/assignments')); }

        /* The owning teacher, admins, and students enrolled in the class may read it. */
        $allowed = $user['role'] === 'admin' || (int) $row['teacher_id'] === $this->teacherId();
        if (!$allowed && $user['role'] === 'student') {
            $allowed = (bool) $this->db->first(
                'SELECT 1 FROM enrollments e JOIN class_subjects cs ON cs.class_id = e.class_id
                 JOIN student_profiles sp ON sp.id = e.student_id
                 WHERE sp.user_id = ? AND cs.id = (SELECT class_subject_id FROM assignments WHERE id = ?)
                   AND e.status = "active" LIMIT 1',
                [$user['id'], $id]
            );
        }
        if (!$allowed) { Session::error('Not authorized.'); $this->redirect(url('student/assignments')); }

        $this->stream($row);
    }

    /** A student's submission for an assignment. */
    public function submission(int $id): void {
        $this->requireAuth();
        $user = $this->auth->user();
        $row = $this->db->first(
            'SELECT s.file_path, s.file_original_name, s.file_mime, s.student_id, sp.user_id AS owner_id,
                    cs.teacher_id
             FROM submissions s
             JOIN student_profiles sp ON sp.id = s.student_id
             JOIN assignments a ON a.id = s.assignment_id
             JOIN class_subjects cs ON cs.id = a.class_subject_id
             WHERE s.id = ? LIMIT 1',
            [$id]
        );
        if (!$row || !$row['file_path']) { Session::error('File not found.'); $this->redirect(url('student/submissions')); }

        $allowed = $user['role'] === 'admin'
            || (int) $row['owner_id'] === (int) $user['id']
            || (int) $row['teacher_id'] === $this->teacherId();
        if (!$allowed) { Session::error('Not authorized.'); $this->redirect(url('student/submissions')); }

        $this->stream($row);
    }

    /** A resource uploaded by a teacher. */
    public function resource(int $id): void {
        $this->requireAuth();
        $user = $this->auth->user();
        $row = $this->db->first(
            'SELECT r.file_path, r.file_original_name, r.file_mime, tp.user_id AS owner_id
             FROM resources r JOIN teacher_profiles tp ON tp.id = r.uploaded_by
             WHERE r.id = ? LIMIT 1',
            [$id]
        );
        if (!$row || !$row['file_path']) { Session::error('File not found.'); $this->redirect(url('teacher/resources')); }

        $allowed = $user['role'] === 'admin' || (int) $row['owner_id'] === (int) $user['id'];
        if (!$allowed) { Session::error('Not authorized.'); $this->redirect(url('teacher/resources')); }

        $this->stream($row);
    }

    /** The signed-in teacher's profile id, or 0 when the user is not a teacher. */
    private function teacherId(): int {
        $profile = $this->db->first('SELECT id FROM teacher_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        return (int) ($profile['id'] ?? 0);
    }

    /** Send the stored file to the browser as an attachment. */
    private function stream(array $row): void {
        $full = Upload::fullPath((string) $row['file_path']);
        if (!is_file($full)) { Session::error('The stored file is missing.'); $this->redirect(url()); }

        $name = (string) ($row['file_original_name'] ?: basename((string) $row['file_path']));
        $mime = (string) ($row['file_mime'] ?: 'application/octet-stream');

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($full));
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($full);
        exit;
    }
}
