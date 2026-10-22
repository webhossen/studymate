<form method="post" action="<?= e(url('password/forgot')) ?>" novalidate>
  <?= csrf_input() ?>
  <h2 class="h5 mb-3">Reset your password</h2>
  <p class="text-muted small">Enter the email address linked to your account and we&rsquo;ll send you a reset link.</p>
  <div class="form-group mb-3">
    <label class="form-label" for="email">Email address</label>
    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required value="<?= e(old('email', '')) ?>" autocomplete="username">
  </div>
  <button type="submit" class="btn btn-sm-navy btn-lg w-100">Send reset link</button>
  <div class="mt-3 text-center">
    Remembered your password? <a href="<?= e(url('login')) ?>" class="text-primary text-decoration-none">Sign in</a>
  </div>
</form>
