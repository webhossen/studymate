<div class="sm-error-page">
  <div class="sm-error-code">403</div>
  <h1 class="h4 mb-2">Forbidden</h1>
  <p class="text-muted mb-4"><?= e($message ?? 'You are not authorized to perform this action.') ?></p>
  <div class="d-flex flex-wrap gap-2 justify-content-center">
    <a href="<?= e(url()) ?>" class="btn btn-sm-navy">Go to dashboard</a>
    <a href="<?= e(url('login')) ?>" class="btn btn-outline-navy">Sign in</a>
  </div>
</div>
