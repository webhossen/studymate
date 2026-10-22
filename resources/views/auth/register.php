<form method="post" action="<?= e(url('register')) ?>" novalidate>
  <?= csrf_input() ?>
  <h2 class="h5 mb-3">Create your account</h2>
  <div class="form-group mb-3">
    <label class="form-label" for="name">Full name</label>
    <input type="text" name="name" id="name" class="form-control" placeholder="Jane Doe" required value="<?= e(old('name', '')) ?>">
  </div>
  <div class="form-group mb-3">
    <label class="form-label" for="email">Email address</label>
    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required value="<?= e(old('email', '')) ?>" autocomplete="username">
  </div>
  <div class="form-group mb-3">
    <label class="form-label" for="password">Password</label>
    <input type="password" name="password" id="password" class="form-control" required autocomplete="new-password">
    <div class="form-text">Minimum 8 characters.</div>
  </div>
  <div class="form-group mb-3">
    <label class="form-label" for="role">Register as</label>
    <select name="role" id="role" class="form-select">
      <option value="student">Student</option>
    </select>
  </div>
  <button type="submit" class="btn btn-sm-navy btn-lg w-100">Create account</button>
  <div class="mt-3 text-center">
    Already have an account? <a href="<?= e(url('login')) ?>" class="text-primary text-decoration-none">Sign in</a>
  </div>
</form>
