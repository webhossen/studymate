<?php
declare(strict_types=1);
$user = $_SESSION['auth'] ?? null;
$role = $user['role'] ?? '';
$email = $user['email'] ?? 'User';
$initial = strtoupper(substr((string) $email, 0, 1));

/* Navigation is grouped so the sidebar reads as sections rather than a flat list. */
$nav = match ($role) {
    'admin' => [
        'Overview' => [
            ['url' => 'admin/dashboard', 'label' => 'Dashboard', 'icon' => '🏠'],
        ],
        'People' => [
            ['url' => 'admin/users', 'label' => 'Users', 'icon' => '👥'],
        ],
        'Academy' => [
            ['url' => 'admin/departments', 'label' => 'Departments', 'icon' => '🏢'],
            ['url' => 'admin/academic-years', 'label' => 'Academic Years', 'icon' => '📅'],
            ['url' => 'admin/classes', 'label' => 'Classes', 'icon' => '🏫'],
            ['url' => 'admin/subjects', 'label' => 'Subjects', 'icon' => '📚'],
        ],
        'Curriculum' => [
            ['url' => 'admin/enrollments', 'label' => 'Enrollments', 'icon' => '📋'],
            ['url' => 'admin/class-subjects', 'label' => 'Class Subjects', 'icon' => '🔗'],
        ],
        'Communication' => [
            ['url' => 'admin/announcements', 'label' => 'Announcements', 'icon' => '📢'],
        ],
        'System' => [
            ['url' => 'admin/audit-logs', 'label' => 'Audit Logs', 'icon' => '📜'],
            ['url' => 'admin/settings', 'label' => 'Settings', 'icon' => '⚙️'],
        ],
    ],
    'teacher' => [
        'Overview' => [
            ['url' => 'teacher/dashboard', 'label' => 'Dashboard', 'icon' => '🏠'],
        ],
        'Teaching' => [
            ['url' => 'teacher/classes', 'label' => 'My Classes', 'icon' => '🏫'],
            ['url' => 'teacher/attendance', 'label' => 'Attendance', 'icon' => '✅'],
            ['url' => 'teacher/assignments', 'label' => 'Assignments', 'icon' => '📝'],
            ['url' => 'teacher/submissions', 'label' => 'Submissions', 'icon' => '📥'],
            ['url' => 'teacher/exams', 'label' => 'Exams', 'icon' => '🎓'],
        ],
        'Engagement' => [
            ['url' => 'teacher/live-sessions', 'label' => 'Live Sessions', 'icon' => '🔴'],
            ['url' => 'teacher/checkins', 'label' => 'Check-ins', 'icon' => '💬'],
            ['url' => 'teacher/resources', 'label' => 'Resources', 'icon' => '📁'],
            ['url' => 'teacher/announcements', 'label' => 'Announcements', 'icon' => '📢'],
        ],
    ],
    default => [
        'Overview' => [
            ['url' => 'student/dashboard', 'label' => 'Dashboard', 'icon' => '🏠'],
        ],
        'Study' => [
            ['url' => 'student/tasks', 'label' => 'Tasks', 'icon' => '✅'],
            ['url' => 'student/planner', 'label' => 'Planner', 'icon' => '📅'],
            ['url' => 'student/notes', 'label' => 'Notes', 'icon' => '📝'],
            ['url' => 'student/focus-timer', 'label' => 'Focus Timer', 'icon' => '⏱️'],
            ['url' => 'student/sessions', 'label' => 'Study Sessions', 'icon' => '🔴'],
        ],
        'Academics' => [
            ['url' => 'student/attendance', 'label' => 'Attendance', 'icon' => '📊'],
            ['url' => 'student/assignments', 'label' => 'Assignments', 'icon' => '📚'],
            ['url' => 'student/grades', 'label' => 'Grades', 'icon' => '🎓'],
            ['url' => 'student/exams', 'label' => 'Exams', 'icon' => '🗓️'],
            ['url' => 'student/calendar', 'label' => 'Calendar', 'icon' => '📆'],
        ],
        'Account' => [
            ['url' => 'student/notifications', 'label' => 'Notifications', 'icon' => '🔔'],
            ['url' => 'student/profile', 'label' => 'Profile', 'icon' => '👤'],
        ],
    ],
};

$current = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$base = rtrim(parse_url(app_url(), PHP_URL_PATH) ?: '/', '/');

/* Builds a base-relative URL, e.g. basePath('student/dashboard'). */
$basePath = function (string $path = '') use ($base): string {
    return $base . ($path === '' ? '' : '/' . ltrim($path, '/'));
};

$isActive = function (string $url) use ($current, $base): string {
    $target = rtrim($base, '/') . '/' . ltrim($url, '/');
    $match = $current === $target || str_starts_with($current . '/', $target . '/');
    return $match ? 'active' : '';
};

/* Top-bar quick link and account menu entries depend on the role. */
$quickLink = match ($role) {
    'student' => ['url' => 'student/notifications', 'label' => 'Notifications', 'icon' => '🔔'],
    'teacher' => ['url' => 'teacher/announcements', 'label' => 'Announcements', 'icon' => '📢'],
    default   => ['url' => 'admin/announcements', 'label' => 'Announcements', 'icon' => '📢'],
};
$accountLink = match ($role) {
    'student' => ['url' => 'student/profile', 'label' => 'My Profile'],
    'teacher' => ['url' => 'teacher/classes', 'label' => 'My Classes'],
    default   => ['url' => 'admin/settings', 'label' => 'Settings'],
};
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#6366f1">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($title ?? 'Dashboard') ?> | StudyMate</title>
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
<link rel="stylesheet" href="<?= asset('css/logout-button.css') ?>">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<a class="visually-hidden-focusable" href="#smMainContent">Skip to content</a>

<nav class="sm-topbar">
  <div class="sm-topbar-inner">
    <button class="sm-menu-toggle" id="smMenuToggle" type="button"
            aria-label="Toggle navigation" aria-controls="smSidebar" aria-expanded="false">
      <span class="sm-menu-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
    </button>

    <a class="sm-brand-link" href="<?= e($basePath()) ?>">
      <span class="sm-brand-mark" aria-hidden="true">✦</span>
      <span class="sm-brand-text">StudyMate</span>
    </a>

    <div class="sm-topbar-actions">
      <a class="sm-icon-btn" id="smNotifyBtn" href="<?= e($basePath($quickLink['url'])) ?>"
         title="<?= e($quickLink['label']) ?>" aria-label="<?= e($quickLink['label']) ?>"
         data-notify-url="<?= e(base_path('api/notifications/count')) ?>">
        <span aria-hidden="true"><?= e($quickLink['icon']) ?></span>
        <span class="sm-dot" id="smNotifyDot" hidden></span>
      </a>

      <button class="sm-icon-btn" type="button" data-theme-toggle title="Toggle theme" aria-label="Toggle theme">
        <span aria-hidden="true">🌙</span>
      </button>

      <div class="dropdown">
        <button class="sm-user-btn" type="button" id="smUserMenu" data-bs-toggle="dropdown"
                aria-expanded="false" aria-label="Account menu">
          <span class="sm-avatar" aria-hidden="true"><?= e($initial) ?></span>
          <span class="sm-user-meta d-none d-sm-flex">
            <span class="sm-user-name"><?= e($email) ?></span>
            <span class="sm-user-role"><?= e(ucfirst($role ?: 'Guest')) ?></span>
          </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="smUserMenu">
          <li class="sm-dropdown-header">
            Signed in as
            <br><strong><?= e($email) ?></strong>
          </li>
          <li><a class="dropdown-item" href="<?= e($basePath($accountLink['url'])) ?>"><?= e($accountLink['label']) ?></a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="post" action="<?= e($basePath('logout')) ?>" style="margin:0;"
                  data-confirm="Sign out of StudyMate? You will need to sign in again to continue."
                  data-confirm-title="Sign out"
                  data-confirm-label="Sign out"
                  data-confirm-tone="warning">
              <?= csrf_input() ?>
              <?= $this->partial('partials/logout-button', ['label' => 'Sign out', 'id' => 'smMenuLogout']) ?>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="sm-backdrop" id="smBackdrop" aria-hidden="true"></div>

<aside class="sm-sidebar" id="smSidebar">
  <a class="brand" href="<?= e($basePath()) ?>">
    <span class="sm-brand-mark" aria-hidden="true">✦</span>
    <span>StudyMate</span>
  </a>

  <nav aria-label="Main navigation">
    <?php foreach ($nav as $group => $links): ?>
      <div class="sm-nav-group"><?= e($group) ?></div>
      <?php foreach ($links as $link): ?>
        <a class="nav-link <?= e($isActive($link['url'])) ?>"
           href="<?= e($basePath($link['url'])) ?>"
           <?= $isActive($link['url']) === 'active' ? 'aria-current="page"' : '' ?>>
          <span class="icon" aria-hidden="true"><?= e($link['icon']) ?></span>
          <span><?= e($link['label']) ?></span>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>

  <div class="divider"></div>

  <form method="post" action="<?= e($basePath('logout')) ?>" style="margin:0;"
        data-confirm="Sign out of StudyMate? You will need to sign in again to continue."
        data-confirm-title="Sign out"
        data-confirm-label="Sign out"
        data-confirm-tone="warning">
    <?= csrf_input() ?>
    <?= $this->partial('partials/logout-button', ['label' => 'Sign out', 'id' => 'smSidebarLogout']) ?>
  </form>
</aside>

<main class="sm-main" id="smMainContent">
  <div class="container-fluid">
    <?= $content ?>
  </div>

  <footer class="sm-footer">
    <div class="sm-footer-inner">
      <span class="text-gradient fw-bold">✦ StudyMate</span>
      <span>Signed in as <?= e(ucfirst($role ?: 'guest')) ?> · <a href="#" data-bs-toggle="modal" data-bs-target="#smPrivacyModal">Privacy notice</a></span>
    </div>
  </footer>
</main>

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
        <div id="smConfirmAction" class="d-none mt-4"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-navy" id="smConfirmCancel" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm-navy" id="smConfirmOk">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Template for the animated logout button shown inside the confirmation popup -->
<div id="smLogoutTemplate" class="d-none" aria-hidden="true">
  <?= $this->partial('partials/logout-button', ['label' => 'Sign out', 'id' => 'smDialogLogout', 'type' => 'button']) ?>
</div>

<div class="modal fade" id="smPrivacyModal" tabindex="-1" aria-labelledby="smPrivacyTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="smPrivacyTitle">Privacy notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">StudyMate study sessions are entirely student-controlled. Teachers of your assigned classes can see your session start time, goal, and check-ins. No screen, webcam, microphone, or browser-history data is ever collected.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm-navy" data-bs-dismiss="modal">Got it</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= asset('js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/chart.umd.min.js') ?>"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/logout-button.js') ?>"></script>
<script src="<?= asset('js/notifications.js') ?>"></script>
</body>
</html>
