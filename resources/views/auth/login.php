<form method="post" action="<?= e(url('login')) ?>" novalidate>
  <?= csrf_input() ?>
  <h2 class="h5 mb-3">Sign in to continue</h2>
  <div class="form-group mb-3">
    <label class="form-label" for="email">Email address</label>
    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required value="<?= e(old('email', '')) ?>" autocomplete="username">
  </div>
  <div class="form-group mb-3">
    <label class="form-label" for="password">Password</label>
    <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
  </div>
  <button type="submit" class="btn btn-sm-navy btn-lg w-100">Sign in</button>
  <div class="mt-3 text-center">
    <a href="<?= e(url('password/forgot')) ?>" class="text-primary text-decoration-none small">Forgot password?</a>
    <?php if (env('APP_ALLOW_REGISTRATION', 'false') === 'true'): ?>
      · <a href="<?= e(url('register')) ?>" class="text-primary text-decoration-none small">Create an account</a>
    <?php endif; ?>
  </div>
</form>
