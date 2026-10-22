<?php
/**
 * Design-system regression check.
 *
 * Verifies that the StudyMate stylesheet still exposes the tokens, shell
 * and component classes the layouts rely on, and that the layout renders
 * the expected shell markup.
 */
$base = getenv('APP_URL') ?: 'http://localhost/studymate';
$ctx = stream_context_create(['http' => ['ignore_errors' => true, 'method' => 'GET']]);

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void {
    global $pass, $fail;
    if ($ok) { $pass++; echo "  [PASS] $label\n"; }
    else { $fail++; echo "  [FAIL] $label\n"; }
}

echo "\n--- Stylesheet: design tokens ---\n";
$css = @file_get_contents($base . '/public/assets/css/app.css', false, $ctx);
check('stylesheet reachable', is_string($css) && $css !== '');

$tokens = [
    'Colour tokens'        => ['--sm-primary', '--sm-success', '--sm-warning', '--sm-danger', '--sm-accent'],
    'Surface tokens'       => ['--sm-bg', '--sm-card', '--sm-card-border', '--sm-border'],
    'Text tokens'          => ['--sm-text', '--sm-heading', '--sm-text-muted'],
    'Shape tokens'         => ['--sm-radius', '--sm-radius-lg', '--sm-shadow'],
    'Layout metrics'       => ['--sm-topbar-h', '--sm-sidebar-w', '--sm-gutter'],
];
foreach ($tokens as $group => $vars) {
    $missing = array_values(array_filter($vars, fn($v) => !str_contains((string) $css, $v . ':')));
    check($group . ' defined', $missing === []);
}

echo "\n--- Stylesheet: components ---\n";
$components = [
    'App shell'      => ['.sm-topbar', '.sm-sidebar', '.sm-main', '.sm-footer', '.sm-backdrop'],
    'Page header'    => ['.sm-page-head', '.sm-page-title', '.sm-page-subtitle', '.sm-page-actions'],
    'Cards'          => ['.sm-card', '.sm-card-header', '.sm-card-body'],
    'Buttons'        => ['.btn-sm-navy', '.btn-outline-navy', '.btn-ghost'],
    'Forms'          => ['.form-control', '.form-select', '.form-label'],
    'Tables'         => ['.table-wrap', '.table'],
    'Badges'         => ['.badge'],
    'Navigation'     => ['.nav-link', '.sm-nav-group', '.icon'],
    'Widgets'        => ['.timer-ring', '.note-content', '.text-gradient'],
    'Auth'           => ['.sm-auth-bg', '.sm-auth-card', '.sm-brand'],
    'Landing'        => ['.sm-hero-section', '.sm-stats-grid', '.sm-feature-grid', '.sm-cta-card'],
    'Theme support'  => ['light-theme', 'prefers-reduced-motion', '@media print'],
];
foreach ($components as $group => $selectors) {
    $missing = array_values(array_filter($selectors, fn($s) => !str_contains((string) $css, $s)));
    check($group . ' styled', $missing === []);
}

echo "\n--- Responsive breakpoints ---\n";
foreach (['max-width: 991.98px', 'max-width: 767.98px', 'max-width: 575.98px', 'min-width: 1400px'] as $bp) {
    check("breakpoint $bp", str_contains((string) $css, $bp));
}

echo "\n--- Layout markup ---\n";
$html = @file_get_contents($base . '/', false, $ctx);
check('landing page reachable', is_string($html) && $html !== '');
check('stylesheet linked', str_contains((string) $html, 'public/assets/css/app.css'));
check('bootstrap linked', str_contains((string) $html, 'public/assets/css/bootstrap.min.css'));
check('theme applied before paint', str_contains((string) $html, "studymate_theme"));
check('app script loaded', str_contains((string) $html, 'public/assets/js/app.js'));

echo "\n--- Notification & confirmation system ---\n";
$login = @file_get_contents($base . '/login', false, $ctx);
$hasToastStack = str_contains((string) $login, 'id="smToastStack"');
check('toast container present in the layout', $hasToastStack);
check('confirmation dialog present in the layout', str_contains((string) $login, 'id="smConfirmModal"'));
check('notification script loaded', str_contains((string) $login, 'public/assets/js/notifications.js'));
check('flash messages delivered as a payload', str_contains((string) $login, 'window.StudyMateFlash'));

$css2 = (string) $css;
foreach (['.sm-toast-stack', '.sm-toast-success', '.sm-toast-error', '.sm-toast-warning', '.sm-toast-info',
          '.sm-toast-progress', '.sm-confirm-icon', '.btn-danger-soft'] as $selector) {
    check("style $selector defined", str_contains($css2, $selector));
}
foreach (['sm-toast-in', 'sm-toast-out', 'sm-toast-progress'] as $animation) {
    check("animation $animation defined", str_contains($css2, '@keyframes ' . $animation));
}

echo "\n--- Animated logout button ---\n";
$logoutCss = @file_get_contents($base . '/public/assets/css/logout-button.css', false, $ctx);
check('logout stylesheet present', is_string($logoutCss) && $logoutCss !== '');
foreach (['.logoutButton', '.doorway', '.figure', '.door', '.bang', 'sm-logout-spin', 'sm-logout-shake'] as $selector) {
    check("logout style $selector defined", str_contains((string) $logoutCss, $selector));
}
/* The original component reset body/svg globally — that must not leak in. */
check('logout styles do not reset the body', !preg_match('/(^|\})\s*body\s*\{[^}]*overflow:\s*hidden/i', (string) $logoutCss));
check('logout styles do not restyle every svg', !preg_match('/(^|\})\s*svg\s*\{/i', (string) $logoutCss));

$logoutJs = @file_get_contents($base . '/public/assets/js/logout-button.js', false, $ctx);
check('logout script present', is_string($logoutJs) && $logoutJs !== '');
foreach (['default', 'hover', 'walking1', 'walking2', 'falling1', 'falling2', 'falling3'] as $state) {
    check("logout animation state '$state' defined", str_contains((string) $logoutJs, $state));
}

echo "\n--- Vendored assets ---\n";
$assets = [
    'css/bootstrap.min.css',
    'js/bootstrap.bundle.min.js',
    'js/chart.umd.min.js',
    'js/app.js',
    'js/notifications.js',
    'js/logout-button.js',
    'css/logout-button.css',
    'css/fullcalendar.min.css',
    'js/fullcalendar.min.js',
];
foreach ($assets as $asset) {
    $res = @file_get_contents($base . '/public/assets/' . $asset, false, $ctx);
    check($asset . ' present', is_string($res) && strlen($res) > 100);
}

echo "\n=== RESULTS: $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
