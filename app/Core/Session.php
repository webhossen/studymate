<?php
declare(strict_types=1);
namespace App\Core;

class Session {
    public static function get(string $key, $default = null) { return $_SESSION[$key] ?? $default; }
    public static function set(string $key, $value): void { $_SESSION[$key] = $value; }
    public static function remove(string $key): void { unset($_SESSION[$key]); }
    public static function flash(string $key, $value = null) {
        if ($value === null) {
            $value = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $value;
        }
        $_SESSION['flash'][$key] = $value;
    }
    public static function error(string $message): void { $_SESSION['flash']['error'] = $message; }
    public static function success(string $message): void { $_SESSION['flash']['success'] = $message; }
    public static function info(string $message): void { $_SESSION['flash']['info'] = $message; }
}
