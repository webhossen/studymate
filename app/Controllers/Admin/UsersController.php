<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Core\Session;

class UsersController extends Controller {
    public function index(): void {
        $this->requireRole('admin');
        $users = $this->db->get('SELECT u.id, u.email, u.role, u.status, u.created_at,
            sp.student_id, tp.employee_id,
            COALESCE(sp.first_name, tp.first_name) AS first_name,
            COALESCE(sp.last_name, tp.last_name) AS last_name
            FROM users u LEFT JOIN student_profiles sp ON sp.user_id = u.id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id ORDER BY u.role, u.email');
        echo $this->view->renderWithLayout('admin/users', 'layouts/app', [
            'title' => 'Users', 'content' => $this->view->render('admin/users', ['users' => $users]),
        ]);
    }

    public function create(): void {
        $this->requireRole('admin');
        echo $this->view->renderWithLayout('admin/users_form', 'layouts/app', [
            'title' => 'Create User', 'content' => $this->view->render('admin/users_form', ['user' => null, 'action' => url('admin/users')]),
        ]);
    }

    public function store(): void {
        $this->requireRole('admin');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'student';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            Session::error('Invalid input.');
            $_SESSION['__old_input'] = ['name' => $name, 'email' => $email, 'role' => $role];
            $this->redirect(url('admin/users/create'));
        }
        if ($this->db->first('SELECT id FROM users WHERE email = ? LIMIT 1', [$email])) {
            Session::error('Email exists.');
            $this->redirect(url('admin/users/create'));
        }
        $uid = $this->db->insert('INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)',
            [$email, password_hash($password, PASSWORD_DEFAULT), $role, 'active']);
        $this->audit('user.create', 'users', $uid, [], ['email' => $email, 'role' => $role, 'name' => $name]);
        if ($role === 'student') {
            $this->db->insert('INSERT INTO student_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)', [$uid, $name, '']);
        } else {
            $this->db->insert('INSERT INTO teacher_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)', [$uid, $name, '']);
        }
        Session::success('User created.');
        $this->redirect(url('admin/users'));
    }

    public function edit(int $id): void {
        $this->requireRole('admin');
        $user = $this->db->first(
            'SELECT u.id, u.email, u.role, u.status, u.created_at,
                COALESCE(sp.student_id, tp.employee_id) AS identifier,
                COALESCE(sp.first_name, tp.first_name, "") AS first_name,
                COALESCE(sp.last_name, tp.last_name, "") AS last_name
             FROM users u
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
             WHERE u.id = ? LIMIT 1',
            [$id]
        );
        if (!$user) { Session::error('Not found.'); $this->redirect(url('admin/users')); }
        echo $this->view->renderWithLayout('admin/users_form', 'layouts/app', [
            'title' => 'Edit User', 'content' => $this->view->render('admin/users_form', ['user' => $user, 'action' => url('admin/users/' . $id)]),
        ]);
    }

    public function update(int $id): void {
        $this->requireRole('admin');
        $user = $this->db->first('SELECT * FROM users WHERE id = ? LIMIT 1', [$id]);
        if (!$user) { Session::error('Not found.'); $this->redirect(url('admin/users')); }

        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'student';
        $status = $_POST['status'] ?? $user['status'];
        $name = trim($_POST['name'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::error('A valid email address is required.');
            $_SESSION['__old_input'] = ['email' => $email, 'role' => $role, 'status' => $status, 'name' => $name];
            $this->redirect(url('admin/users/' . $id . '/edit'));
        }
        if (!in_array($role, ['student', 'teacher', 'admin'], true)) $role = $user['role'];
        if (!in_array($status, ['active', 'suspended'], true)) $status = $user['status'];

        $duplicate = $this->db->first('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1', [$email, $id]);
        if ($duplicate) {
            Session::error('That email is already in use.');
            $_SESSION['__old_input'] = ['email' => $email, 'role' => $role, 'status' => $status, 'name' => $name];
            $this->redirect(url('admin/users/' . $id . '/edit'));
        }

        $this->db->update('UPDATE users SET email = ?, role = ?, status = ? WHERE id = ?', [$email, $role, $status, $id]);
        $this->audit('user.update', 'users', $id,
            ['email' => $user['email'], 'role' => $user['role'], 'status' => $user['status']],
            ['email' => $email, 'role' => $role, 'status' => $status]);

        /* Keep the profile name in sync with the account. */
        [$first, $last] = $this->splitName($name);
        if ($first !== '' || $last !== '') {
            $profile = $this->db->first(
                'SELECT sp.id AS sid, tp.id AS tid FROM users u
                 LEFT JOIN student_profiles sp ON sp.user_id = u.id
                 LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
                 WHERE u.id = ? LIMIT 1',
                [$id]
            );
            if ($profile && $profile['sid']) {
                $this->db->update('UPDATE student_profiles SET first_name = ?, last_name = ? WHERE id = ?', [$first, $last, $profile['sid']]);
            } elseif ($profile && $profile['tid']) {
                $this->db->update('UPDATE teacher_profiles SET first_name = ?, last_name = ? WHERE id = ?', [$first, $last, $profile['tid']]);
            }
        }

        Session::success('User updated.');
        $this->redirect(url('admin/users'));
    }

    /** Split a full name into first and last parts. */
    private function splitName(string $name): array {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];
        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    public function status(int $id): void {
        $this->requireRole('admin');
        $status = $_POST['status'] ?? 'suspended';
        $this->db->update('UPDATE users SET status = ? WHERE id = ?', [$status, $id]);
        $this->audit('user.status', 'users', $id, [], ['status' => $status]);
        Session::success('Status updated.');
        $this->redirect(url('admin/users'));
    }
}
