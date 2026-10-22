<?php
declare(strict_types=1);
namespace App\Middleware;

use App\Core\App;
use App\Core\Session;

class AuthMiddleware {
    private App $app;
    public function __construct(App $app) { $this->app = $app; }
    public function handle(): void {
        if (!$this->app->auth->check()) {
            header('Location: ' . $this->app->basePath . 'login?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
            exit;
        }
    }
}
