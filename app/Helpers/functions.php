<?php
declare(strict_types=1);

if (!function_exists('e')) {
    /**
     * Escape a value for HTML output.
     * Accepts mixed input (ints, bools, null) so views can escape any
     * expression without tripping over strict_types.
     */
    function e(mixed $value = null): string {
        if ($value === null || is_bool($value)) $value = $value ? '1' : '';
        if (is_array($value) || is_object($value)) $value = '';
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old(string $field, $default = null) {
        $old = $_SESSION['__old_input'] ?? [];
        unset($_SESSION['__old_input']);
        return $old[$field] ?? $default;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return rtrim(env('APP_URL', 'http://localhost/studymate'), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Resolve a file inside public/assets to a browser URL.
     *
     * The application can be installed two ways and the asset path differs:
     *
     *  - Document root is the project folder and the app lives in a
     *    sub-folder (APP_URL = http://host/studymate). The assets are
     *    reachable at http://host/studymate/public/assets/...
     *
     *  - Document root is the public folder (APP_URL = http://host). The
     *    assets are reachable at http://host/assets/...
     *
     * A cache-busting query string is appended so a changed stylesheet or
     * script is never served from the browser's cache.
     */
    function asset(string $path): string {
        $path = ltrim($path, '/');
        $base = rtrim((string) (parse_url(app_url(), PHP_URL_PATH) ?: ''), '/');

        /* When there is no sub-folder the public directory *is* the root, so
           the assets sit directly under it. Otherwise the public folder is
           still reachable under the application's own path. */
        $prefix = ($base === '' || $base === '/') ? '' : 'public';
        $url = rtrim(app_url(), '/') . '/' . ($prefix !== '' ? $prefix . '/' : '') . 'assets/' . $path;

        $file = defined('APP_ROOT') ? APP_ROOT . '/public/assets/' . $path : null;
        if ($file !== null && is_file($file)) {
            $url .= '?v=' . filemtime($file);
        }
        return $url;
    }
}

if (!function_exists('base_path')) {
    /**
     * Build a root-relative path from the application base path.
     * Unlike url(), this keeps the current host and port, which matters for
     * XHR calls made from a page served on a different port.
     */
    function base_path(string $path = ''): string {
        $base = rtrim(parse_url(app_url(), PHP_URL_PATH) ?: '/', '/');
        return $base . ($path === '' ? '' : '/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input(): string { return \App\Core\Csrf::input(); }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string { return \App\Core\Csrf::token(); }
}

if (!function_exists('flash')) {
    function flash(string $key = null) {
        if ($key === null) {
            $out = [];
            foreach (['error', 'success', 'info', 'warning'] as $k) {
                $out[$k] = \App\Core\Session::flash($k);
            }
            return $out;
        }
        return \App\Core\Session::flash($key);
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): void {
        http_response_code($code);
        $view = new \App\Core\View(APP_ROOT . '/resources/views');
        echo $view->renderWithLayout('errors.' . $code, 'layouts/auth', [
            'code' => $code, 'message' => $message,
            'user' => !empty($_SESSION['auth']) ? $_SESSION['auth'] : null,
        ]);
        exit;
    }
}
