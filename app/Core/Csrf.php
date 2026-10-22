<?php
declare(strict_types=1);
namespace App\Core;

class Csrf {
    public const TOKEN_KEY = 'studymate_csrf_token';

    public static function token(): string {
        if (empty($_SESSION[self::TOKEN_KEY])) $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
        return $_SESSION[self::TOKEN_KEY];
    }
    public static function input(): string {
        return sprintf('<input type="hidden" name="%s" value="%s">', self::TOKEN_KEY, e(self::token()));
    }
    public static function verify(string $token): bool {
        $expected = $_SESSION[self::TOKEN_KEY] ?? '';
        return $expected !== '' && hash_equals($expected, $token);
    }
    public static function verifyRequest(): bool {
        $token = $_POST[self::TOKEN_KEY] ?? $_GET[self::TOKEN_KEY] ?? null;
        if ($token === null) {
            $headers = getallheaders() ?: [];
            $token = $headers['X-CSRF-Token'] ?? $headers['X-XSRF-TOKEN'] ?? null;
        }
        if ($token === null) return false;
        return self::verify((string) $token);
    }
}
