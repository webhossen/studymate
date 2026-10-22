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
        /* Shown only in development, where there is usually no mail server. */
        $devLink = $_SESSION['__reset_link'] ?? null;
        unset($_SESSION['__reset_link']);
        echo $this->view->renderWithLayout('auth.forgot', 'layouts/auth', [
            'title' => 'Forgot Password', 'devLink' => $devLink,
        ]);
    }

    public function sendReset(): void {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::error('Enter a valid email address.');
            $_SESSION['__old_input'] = ['email' => $email];
            $this->redirect(url('password/forgot'));
        }

        $user = $this->db->first('SELECT id, email FROM users WHERE email = ? LIMIT 1', [$email]);
        $link = null;
        $mailSent = null;

        if ($user) {
            /* Any outstanding token for this address is now void. */
            $this->db->update('UPDATE password_resets SET used_at = NOW(), expires_at = NOW() WHERE email = ? AND used_at IS NULL', [$email]);

            $token = bin2hex(random_bytes(32));
            $link = url('password/reset/' . $token);

            /* Only a hash of the token is stored, so a database leak cannot be
               used to reset anybody's password. The expiry is computed by the
               database so it always lines up with NOW(). */
            $this->db->insert('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))',
                [$email, hash('sha256', $token)]);

            $mailSent = $this->sendResetMail($user['email'], $link);
        }

        /* The same message either way, so we never reveal whether the account exists. */
        Session::success('If an account exists for that address, a reset link has been sent.');

        /* If the mail could not be delivered the link would be unreachable, so
           surface it in development where the flow can still be completed. */
        if ($link !== null && app_debug()) {
            $_SESSION['__reset_link'] = $link;
        }

        $this->redirect(url('password/forgot'));
    }

    public function resetForm(string $token): void {
        $valid = $this->findResetToken($token) !== null;
        echo $this->view->renderWithLayout('auth.reset', 'layouts/auth', [
            'title' => 'Reset Password', 'token' => $token, 'valid' => $valid,
        ]);
    }

    public function resetPassword(string $token): void {
        $record = $this->findResetToken($token);
        if (!$record) {
            Session::error('This reset link is invalid or has expired. Please request a new one.');
            $this->redirect(url('password/forgot'));
        }

        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 8) {
            Session::error('Your new password must be at least 8 characters long.');
            $_SESSION['__old_input'] = ['token' => $token];
            $this->redirect(url('password/reset/' . $token));
        }
        if ($password !== $confirm) {
            Session::error('The two passwords do not match.');
            $_SESSION['__old_input'] = ['token' => $token];
            $this->redirect(url('password/reset/' . $token));
        }

        $user = $this->db->first('SELECT id FROM users WHERE email = ? LIMIT 1', [$record['email']]);
        if (!$user) {
            Session::error('That account no longer exists. Please request a new link.');
            $this->redirect(url('password/forgot'));
        }

        $this->db->update('UPDATE users SET password = ? WHERE id = ?',
            [password_hash($password, PASSWORD_DEFAULT), $user['id']]);

        /* Consume this token and every other one for the account. */
        $this->db->update('UPDATE password_resets SET used_at = NOW(), expires_at = NOW() WHERE email = ? AND used_at IS NULL',
            [$record['email']]);

        $this->audit('password.reset', 'users', (int) $user['id']);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        Session::success('Your password has been reset. You can sign in with it now.');
        $this->redirect(url('login'));
    }

    /** Find a usable reset token. Only the hash is ever compared. */
    private function findResetToken(string $token): ?array {
        if ($token === '') return null;
        return $this->db->first(
            'SELECT * FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1',
            [hash('sha256', $token)]
        );
    }

    /**
     * Deliver the reset link by email, and always record it in the log so it
     * can be retrieved when no mail server is configured.
     */
    private function sendResetMail(string $email, string $link): bool {
        $mailer = new \App\Core\Mailer();
        $sent = $mailer->sendPasswordReset($email, $link);

        foreach ($mailer->errors() as $error) {
            error_log('Password reset email failed: ' . $error);
        }

        $logDir = APP_ROOT . '/storage/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0770, true);
        @file_put_contents(
            $logDir . '/password-resets.log',
            date('Y-m-d H:i:s') . ' ' . $email . ' ' . $link . ' ' . ($sent ? 'sent' : 'failed') . "\n",
            FILE_APPEND
        );

        return $sent;
    }

    private function safeUrl(string $url): string {
        if ($url === '' || str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '//'))
            return $this->auth->roleRedirect();
        return ltrim($url, '/') !== '' ? '/' . ltrim($url, '/') : $this->auth->roleRedirect();
    }
}
