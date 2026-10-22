<?php
declare(strict_types=1);
namespace App\Core;

abstract class Controller {
    protected View $view;
    protected App $app;
    protected Database $db;
    protected Auth $auth;

    public function __construct(App $app) {
        $this->app = $app;
        $this->db = $app->db;
        $this->auth = $app->auth;
        $this->view = new View(APP_ROOT . '/resources/views');
    }

    protected function requireAuth(): void {
        if ($this->auth->guest()) abort(401, 'Authentication required.');
    }

    /**
     * The student_profiles id of the signed-in user.
     * Student-facing tables key on student_profiles(id), not users(id).
     */
    protected function studentProfileId(): int {
        $profile = $this->db->first('SELECT id FROM student_profiles WHERE user_id = ? LIMIT 1', [$this->auth->user()['id']]);
        return (int) ($profile['id'] ?? 0);
    }

    protected function requireRole(string ...$roles): void {
        $this->requireAuth();
        $user = $this->auth->user();
        if ($user === null || !in_array($user['role'], $roles, true)) abort(403, 'Insufficient permissions.');
    }

    /**
     * Record an entry in the audit trail.
     */
    protected function audit(string $action, string $table = '', int $recordId = 0, array $old = [], array $new = []): void {
        $this->app->audit->log($action, null, $table, $recordId, $old, $new);
    }

    protected function redirect(string $url): void {
        header('Location: ' . $url, true, 302);
        exit;
    }

    protected function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data);
        exit;
    }

    protected function abort(int $code, string $message = ''): void {
        http_response_code($code);
        $view = new View(APP_ROOT . '/resources/views');
        echo $view->renderWithLayout('errors.' . $code, 'layouts/auth', [
            'code' => $code, 'message' => $message,
            'user' => !empty($_SESSION['auth']) ? $_SESSION['auth'] : null,
        ]);
        exit;
    }
}
