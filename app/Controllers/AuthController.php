<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Session;

class AuthController extends Controller {
    public function login(): void {
        if ($this->auth->check()) $this->redirect($this->auth->roleRedirect());
        echo $this->view->renderWithLayout('auth.login', 'layouts/auth', ['title' => 'Sign in']);
    }

    public function authenticate(): void {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $result = $this->auth->attempt($email, $password);
        if (!$result['ok']) {
            Session::error($result['error']);
            $_SESSION['__old_input'] = ['email' => $email];
            $this->redirect(url('login') . '?email=' . urlencode($email));
        }
        $this->auth->login($result['user']);
        $next = $_GET['next'] ?? $this->auth->roleRedirect();
        $this->redirect($this->safeUrl($next));
    }

    public function logout(): void {
        $this->requireAuth();
        $this->auth->logout();
        Session::success('Signed out.');
        $this->redirect(url('login'));
    }

    public function register(): void {
        if ($this->auth->check()) $this->redirect($this->auth->roleRedirect());
        echo $this->view->renderWithLayout('auth.register', 'layouts/auth', ['title' => 'Register']);
    }

    public function create(): void {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'student';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            Session::error('Invalid input.');
            $_SESSION['__old_input'] = ['name' => $name, 'email' => $email, 'role' => $role];
            $this->redirect(url('register'));
        }
        if ($this->db->first('SELECT id FROM users WHERE email = ? LIMIT 1', [$email])) {
            Session::error('Email exists.');
            $this->redirect(url('register'));
        }
        $uid = $this->db->insert('INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)',
            [$email, password_hash($password, PASSWORD_DEFAULT), $role, 'active']);
        if ($role === 'student') {
            $this->db->insert('INSERT INTO student_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)',
                [$uid, $name, '']);
        } else {
            $this->db->insert('INSERT INTO teacher_profiles (user_id, first_name, last_name) VALUES (?, ?, ?)',
                [$uid, $name, '']);
        }
        Session::success('Account created.');
        $user = $this->db->first('SELECT id, email, role FROM users WHERE id = ?', [$uid]);
        $this->auth->login($user);
        $this->redirect($this->auth->roleRedirect());
    }

    public function forgot(): void {
        echo $this->view->renderWithLayout('auth.forgot', 'layouts/auth', ['title' => 'Forgot Password']);
    }

    public function sendReset(): void {
        $email = trim($_POST['email'] ?? '');
        Session::info('If an account exists, a reset link has been logged to storage/logs/password-resets.log');
        $logDir = APP_ROOT . '/storage/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0770, true);
        $link = url('password/reset/' . bin2hex(random_bytes(16)));
        @file_put_contents($logDir . '/password-resets.log', date('Y-m-d H:i:s') . ' ' . $email . ' ' . $link . "\n", FILE_APPEND);
        $this->redirect(url('login'));
    }

    public function resetForm(string $token): void {
        echo $this->view->renderWithLayout('auth.reset', 'layouts/auth', ['title' => 'Reset Password', 'token' => $token]);
    }

    public function resetPassword(string $token): void {
        $password = $_POST['password'] ?? '';
        if (strlen($password) < 8) {
            Session::error('Password too short.');
            $this->redirect(url('password/reset/' . $token));
        }
        Session::success('Password reset.');
        $this->redirect(url('login'));
    }

    private function safeUrl(string $url): string {
        if ($url === '' || str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '//'))
            return $this->auth->roleRedirect();
        return ltrim($url, '/') !== '' ? '/' . ltrim($url, '/') : $this->auth->roleRedirect();
    }
}
