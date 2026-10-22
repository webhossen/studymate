<?php
declare(strict_types=1);
namespace App\Core;

if (!defined('APP_ROOT')) define('APP_ROOT', __DIR__ . '/../..');

class App {
    public string $root;
    public array $config = [];
    public array $routes = [];
    public ?Router $router = null;
    public ?Database $db = null;
    public ?Auth $auth = null;
    public ?Audit $audit = null;
    public string $basePath = '/';

    public function __construct(string $root) { $this->root = $root; }

    public function bootstrap(): void {
        require APP_ROOT . '/config/app.php';
        require APP_ROOT . '/app/Helpers/functions.php';
        load_env(APP_ROOT . '/.env');
        date_default_timezone_set(app_timezone());
        error_reporting(E_ALL);
        if (is_production()) { ini_set('display_errors', '0'); ini_set('log_errors', '1'); }
        else { ini_set('display_errors', '1'); }
        spl_autoload_register(function (string $class) {
            $prefix = 'App\\';
            if (strpos($class, $prefix) !== 0) return;
            $relative = substr($class, strlen($prefix));
            $file = APP_ROOT . '/app/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) require_once $file;
        });
        if (is_file(APP_ROOT . '/vendor/autoload.php')) require_once APP_ROOT . '/vendor/autoload.php';
        $this->basePath = parse_url(app_url(), PHP_URL_PATH) ?: '/';
        $this->basePath = rtrim($this->basePath, '/') . '/';
        $this->db = new Database(config('database'));
        $this->auth = new Auth($this->db, $this->basePath);
        $this->audit = new Audit($this->db);
        $this->configureSessions();
        $this->router = new Router($this, $this->basePath);
        $router = $this->router;
        require APP_ROOT . '/routes/web.php';
        require APP_ROOT . '/routes/api.php';
    }

    public function run(): void {
        if ($this->router === null) throw new \RuntimeException('Router not initialized');
        $this->router->dispatch();
    }

    public function configureSessions(): void {
        $secure = is_production();
        $lifetime = session_lifetime();
        ini_set('session.cookie_lifetime', (string) $lifetime);
        ini_set('session.gc_maxlifetime', (string) $lifetime);
        ini_set('session.cookie_secure', $secure ? '1' : '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '0');
        ini_set('session.cookie_samesite', 'Lax');
        session_name(env('SESSION_NAME', 'studymate_session'));
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['__last_regeneration'])) {
            $age = time() - (int) $_SESSION['__last_regeneration'];
            if ($age > 300) { session_regenerate_id(true); $_SESSION['__last_regeneration'] = time(); }
        } else { $_SESSION['__last_regeneration'] = time(); }
    }
}

function config(string $file): array {
    $path = APP_ROOT . '/config/' . $file . '.php';
    if (!is_file($path)) throw new \RuntimeException("Config [{$file}] not found");
    return require $path;
}
