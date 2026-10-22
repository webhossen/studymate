<?php
declare(strict_types=1);
namespace App\Core;

class Router {
    private App $app;
    private string $basePath;
    private array $routes = [];

    public function __construct(App $app, string $basePath = '/') {
        $this->app = $app;
        $this->basePath = rtrim($basePath, '/') . '/';
    }

    public function get(string $pattern, string $handler, ?string $name = null, array $middleware = []): void {
        $this->add('GET', $pattern, $handler, $name, $middleware);
    }
    public function post(string $pattern, string $handler, ?string $name = null, array $middleware = []): void {
        $this->add('POST', $pattern, $handler, $name, $middleware);
    }
    public function any(string $pattern, string $handler, ?string $name = null, array $middleware = []): void {
        $this->add('ANY', $pattern, $handler, $name, $middleware);
    }

    private function add(string $method, string $pattern, string $handler, ?string $name, array $middleware): void {
        $pattern = ltrim($pattern, '/');
        $this->routes[] = ['method' => $method, 'pattern' => $pattern, 'handler' => $handler, 'name' => $name, 'middleware' => $middleware];
    }

    public function dispatch(): void {
        $uri = $this->resolveUri();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->runMiddleware(['web']);
        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) continue;
            $params = $this->match($route['pattern'], $uri);
            if ($params === false) continue;
            $this->runMiddleware($route['middleware']);
            $this->executeHandler($route['handler'], $params);
            return;
        }
        if (str_starts_with($uri, 'api/')) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Not found.']);
            exit;
        }
        abort(404, 'Page not found.');
    }

    private function resolveUri(): string {
        $uri = explode('?', $_SERVER['REQUEST_URI'] ?? '/', 2)[0];
        $base = rtrim($this->basePath, '/');
        if ($base !== '/' && str_starts_with($uri, $base)) $uri = substr($uri, strlen($base));
        $uri = trim($uri, '/');
        return $uri === '' ? '' : $uri;
    }

    private function match(string $pattern, string $uri): array|false {
        $patternParts = $pattern === '' ? [] : explode('/', $pattern);
        $uriParts = $uri === '' ? [] : explode('/', $uri);
        if (count($patternParts) !== count($uriParts)) return false;
        $params = [];
        foreach ($patternParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[substr($part, 1, -1)] = $uriParts[$i];
                continue;
            }
            if ($part !== $uriParts[$i]) return false;
        }
        return $params;
    }

    private function runMiddleware(array $middleware): void {
        foreach ($middleware as $mw) {
            if (str_starts_with($mw, 'role:')) {
                $roles = array_map('trim', explode(',', substr($mw, 5)));
                if (!$this->app->auth->anyRole($roles)) {
                    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
                    if (str_contains($accept, 'application/json')) {
                        http_response_code(403);
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['ok' => false, 'error' => 'Insufficient permissions.']);
                        exit;
                    }
                    Session::error('You are not authorized to view this page.');
                    header('Location: ' . $this->app->auth->roleRedirect(), true, 302);
                    exit;
                }
                continue;
            }
            $className = $mw;
            if (!str_ends_with($className, 'Middleware')) $className .= 'Middleware';
            $class = 'App\\Middleware\\' . ucfirst(strtolower($className));
            $file = APP_ROOT . '/app/Middleware/' . ucfirst(strtolower($className)) . '.php';
            if (is_file($file)) require_once $file;
            if (class_exists($class)) (new $class($this->app))->handle();
        }
    }

    private function executeHandler(string $handler, array $params): void {
        [$classShort, $method] = explode('@', $handler, 2);
        $classShort = trim($classShort);
        if (!str_ends_with($classShort, 'Controller')) $classShort .= 'Controller';
        $class = 'App\\Controllers\\' . str_replace('/', '\\', $classShort);
        $file = APP_ROOT . '/app/Controllers/' . str_replace('\\', '/', $classShort) . '.php';
        if (!is_file($file)) throw new \RuntimeException("Controller [{$class}] not found");
        require_once $file;
        $controller = new $class($this->app);
        if (!method_exists($controller, $method)) throw new \RuntimeException("Method [{$method}] not found on [{$class}]");
        $reflection = new \ReflectionMethod($controller, $method);
        $args = [];
        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) { $args[] = $params[$name]; continue; }
            if (isset($_REQUEST[$name])) { $args[] = $_REQUEST[$name]; continue; }
            if ($param->isDefaultValueAvailable()) { $args[] = $param->getDefaultValue(); continue; }
            $args[] = null;
        }
        $reflection->invokeArgs($controller, $args);
    }
}
