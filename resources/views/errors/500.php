<div class="sm-error-page">
  <div class="sm-error-code">500</div>
  <h1 class="h4 mb-2">Server error</h1>
  <p class="text-muted mb-4">Something went wrong on our side. The error has been logged &mdash; please try again.</p>
  <div class="d-flex flex-wrap gap-2 justify-content-center">
    <a href="<?= e(url()) ?>" class="btn btn-sm-navy">Go to dashboard</a>
    <a href="<?= e(url('login')) ?>" class="btn btn-outline-navy">Sign in</a>
  </div>
</div>
