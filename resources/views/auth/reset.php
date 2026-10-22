<form method="post" action="<?= e(url('password/reset/' . e($token ?? ''))) ?>" novalidate>
  <?= csrf_input() ?>
  <h2 class="h5 mb-3">Choose a new password</h2>
  <div class="form-group mb-3">
    <label class="form-label" for="password">New password</label>
    <input type="password" name="password" id="password" class="form-control" required autocomplete="new-password">
    <div class="form-text">Minimum 8 characters.</div>
  </div>
  <div class="form-group mb-3">
    <label class="form-label" for="password_confirm">Confirm password</label>
    <input type="password" name="password_confirm" id="password_confirm" class="form-control" required autocomplete="new-password">
  </div>
  <button type="submit" class="btn btn-sm-navy btn-lg w-100">Reset password</button>
  <div class="mt-3 text-center">
    Back to <a href="<?= e(url('login')) ?>" class="text-primary text-decoration-none">sign in</a>
  </div>
</form>
