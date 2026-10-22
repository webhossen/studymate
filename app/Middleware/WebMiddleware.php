<?php
declare(strict_types=1);
namespace App\Middleware;

use App\Core\App;
use App\Core\Session;

class WebMiddleware {
    private App $app;
    public function __construct(App $app) { $this->app = $app; }

    public function handle(): void {
        \App\Core\Csrf::token();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            if (!\App\Core\Csrf::verifyRequest()) {
                $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
                if (str_contains($accept, 'application/json')) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => false, 'error' => 'CSRF token missing or invalid.']);
                    exit;
                }
                Session::error('Invalid security token. Please try again.');
                header('Location: ' . $_SERVER['REQUEST_URI'] ?? '/');
                exit;
            }
        }
    }
}
