<?php
declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__ . '/..');
}

function load_env(string $path): void {
    if (!is_file($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key); $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
        elseif (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];
        if (!isset($_ENV[$key]) && !isset($_SERVER[$key])) {
            $_ENV[$key] = $value; $_SERVER[$key] = $value;
        }
    }
}

function env(string $key, $default = null) {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: false;
    if ($value === false || $value === '') return $default;
    return $value;
}

function app_env(): string { return env('APP_ENV', 'development'); }
function app_debug(): bool { return (string) env('APP_DEBUG', 'false') === 'true'; }
function app_url(): string { return rtrim(env('APP_URL', 'http://localhost/studymate'), '/'); }
function app_name(): string { return env('APP_NAME', 'StudyMate'); }
function app_timezone(): string { return env('APP_TIMEZONE', 'UTC'); }
function session_lifetime(): int { return (int) env('SESSION_LIFETIME', 120); }
function stale_session_threshold(): int { return (int) env('STALE_SESSION_THRESHOLD', 300); }
function upload_max_size(): int { return (int) env('UPLOAD_MAX_SIZE', 10485760); }
function is_production(): bool { return app_env() === 'production'; }
