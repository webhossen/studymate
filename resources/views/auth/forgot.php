<div class="sm-auth-icon" aria-hidden="true">🔑</div>

<h2 class="h5 text-center mb-2">Forgot your password?</h2>
<p class="text-muted small text-center mb-4">
  Enter the email address linked to your account and we&rsquo;ll send you a link to choose a new password.
</p>

<form method="post" action="<?= e(url('password/forgot')) ?>" novalidate>
  <?= csrf_input() ?>
  <div class="form-group mb-3">
    <label class="form-label" for="email">Email address</label>
    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com"
           required autocomplete="username" value="<?= e(old('email', '')) ?>">
  </div>
  <button type="submit" class="btn btn-sm-navy btn-lg w-100">Send reset link</button>
</form>

<?php if (!empty($devLink)): ?>
  <div class="sm-dev-note mt-4">
    <div class="sm-dev-note-title">Development mode</div>
    <p class="mb-2">No mail server is configured, so here is the reset link:</p>
    <a href="<?= e($devLink) ?>" class="sm-dev-note-link"><?= e($devLink) ?></a>
  </div>
<?php endif; ?>

<div class="text-center mt-4">
  Remembered your password?
  <a href="<?= e(url('login')) ?>" class="text-primary text-decoration-none fw-semibold">Sign in</a>
</div>
