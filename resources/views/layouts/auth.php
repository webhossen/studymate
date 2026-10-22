<?php
declare(strict_types=1);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$isLanding = ($path === '/' || $path === '/studymate/');
$pageTitle = trim((string) ($title ?? ''));
$fullTitle = ($pageTitle === '' || $pageTitle === app_name())
    ? app_name() . ' — Academic management & student productivity'
    : $pageTitle . ' | ' . app_name();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#6366f1">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($fullTitle) ?></title>
<script>
/* Apply the saved theme before first paint to avoid a flash of the wrong palette. */
(function () {
  try {
    if (localStorage.getItem('studymate_theme') === 'light') {
      document.documentElement.setAttribute('data-theme', 'light');
    }
  } catch (e) {}
})();
</script>
<link rel="stylesheet" href="<?= asset('css/bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="<?= $isLanding ? 'sm-landing-page' : '' ?>">

<button class="sm-theme-fab" type="button" data-theme-toggle title="Toggle theme" aria-label="Toggle theme">
  <span aria-hidden="true">🌙</span>
</button>

<div class="sm-auth-bg">
<?php if ($isLanding): ?>
  <?= $content ?>
<?php else: ?>
  <div class="sm-auth-card">
    <div class="sm-brand"><?= e(app_name()) ?></div>
    <div class="sm-tagline">Academic management &amp; student productivity</div>
    <?= $content ?>
  </div>
<?php endif; ?>
</div>

<?php
/* Flash messages are delivered as toasts rather than inline banners. */
$smFlashes = [];
foreach (['error', 'success', 'info', 'warning'] as $kind) {
    $msg = flash($kind);
    if ($msg) $smFlashes[] = ['type' => $kind, 'message' => $msg];
}
?>

<div class="sm-toast-stack" id="smToastStack" role="region" aria-live="polite" aria-atomic="false"></div>
<script>window.StudyMateFlash = <?= json_encode($smFlashes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>

<div class="modal fade" id="smConfirmModal" tabindex="-1" aria-labelledby="smConfirmTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <div class="d-flex gap-3 align-items-start">
          <span class="sm-confirm-icon" id="smConfirmIcon" aria-hidden="true">?</span>
          <div>
            <h5 class="sm-confirm-title" id="smConfirmTitle">Please confirm</h5>
            <p class="sm-confirm-body" id="smConfirmBody">Are you sure you want to continue?</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-navy" id="smConfirmCancel" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm-navy" id="smConfirmOk">Confirm</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/notifications.js') ?>"></script>
</body>
</html>
