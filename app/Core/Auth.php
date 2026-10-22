<?php
declare(strict_types=1);
namespace App\Core;

class Auth {
    private Database $db;
    private string $basePath;
    private ?array $user = null;

    public function __construct(Database $db, string $basePath) {
        $this->db = $db;
        $this->basePath = $basePath;
    }

    public function user(): ?array {
        if ($this->user !== null) return $this->user;
        if (empty($_SESSION['auth']['user_id'])) return null;
        $this->user = $this->db->first('SELECT id, email, role, status FROM users WHERE id = ?', [(int) $_SESSION['auth']['user_id']]);
        if (!$this->user || $this->user['status'] !== 'active') { $this->logout(); return null; }
        return $this->user;
    }

    public function check(): bool { return $this->user() !== null; }
    public function guest(): bool { return !$this->check(); }

    public function attempt(string $email, string $password): array {
        $user = $this->db->first('SELECT id, email, password, role, status FROM users WHERE email = ? LIMIT 1', [$email]);
        if (!$user) return ['ok' => false, 'error' => 'Invalid email or password.'];
        if ($user['status'] !== 'active') return ['ok' => false, 'error' => 'Account not active.'];
        if (!password_verify($password, $user['password'])) return ['ok' => false, 'error' => 'Invalid email or password.'];
        return ['ok' => true, 'user' => $user];
    }

    public function login(array $user): void {
        session_regenerate_id(true);
        $_SESSION['auth'] = ['user_id' => (int) $user['id'], 'email' => $user['email'], 'role' => $user['role'], 'logged_in_at' => time()];
        $_SESSION['__last_regeneration'] = time();
        $this->user = $user;
    }

    public function logout(): void {
        $this->user = null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], true);
        }
        session_destroy();
    }

    public function hasRole(string $role): bool {
        $user = $this->user();
        return $user !== null && $user['role'] === $role;
    }
    public function anyRole(array $roles): bool {
        $user = $this->user();
        return $user !== null && in_array($user['role'], $roles, true);
    }
    public function roleRedirect(): string {
        $user = $this->user();
        if (!$user) return $this->basePath . 'login';
        return match ($user['role']) {
            'admin' => $this->basePath . 'admin/dashboard',
            'teacher' => $this->basePath . 'teacher/dashboard',
            default => $this->basePath . 'student/dashboard',
        };
    }
}
