<?php
declare(strict_types=1);
namespace App\Middleware;

use App\Core\App;

class GuestMiddleware {
    private App $app;
    public function __construct(App $app) { $this->app = $app; }
    public function handle(): void {
        if ($this->app->auth->check()) {
            header('Location: ' . $this->app->auth->roleRedirect(), true, 302);
            exit;
        }
    }
}
