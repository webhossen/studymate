<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title"><?= e($user ? 'Edit User' : 'Create User') ?></h1>
    <p class="sm-page-subtitle"><?= e($user ? 'Update this account’s details and access.' : 'Add a student, teacher or administrator.') ?></p>
  </div>
  <div class="sm-page-actions">
    <a href="<?= e(url('admin/users')) ?>" class="btn btn-outline-navy">Back to users</a>
  </div>
</div>
<div class="row justify-content-center"><div class="col-md-8 col-lg-6">
<div class="sm-card"><div class="sm-card-header">Account details</div>
<div class="sm-card-body"><form method="post" action="<?= e($action) ?>" novalidate><?= csrf_input() ?>
  <?php
    $fullName = $user ? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) : '';
    $currentRole = $user['role'] ?? 'student';
    $currentStatus = $user['status'] ?? 'active';
  ?>
  <div class="form-group mb-3"><label class="form-label" for="name">Full Name</label><input type="text" name="name" id="name" class="form-control" value="<?= e(old('name', $fullName)) ?>" required></div>
  <div class="form-group mb-3"><label class="form-label" for="email">Email</label><input type="email" name="email" id="email" class="form-control" value="<?= e(old('email', $user['email'] ?? '')) ?>" required></div>
  <?php if (!$user): ?><div class="form-group mb-3"><label class="form-label" for="password">Password</label><input type="password" name="password" id="password" class="form-control" required><div class="form-text">Minimum 8 characters.</div></div><?php endif; ?>
  <div class="form-group mb-3"><label class="form-label" for="role">Role</label><select name="role" id="role" class="form-select">
    <option value="student" <?= (old('role', $currentRole) === 'student') ? 'selected' : '' ?>>Student</option>
    <option value="teacher" <?= (old('role', $currentRole) === 'teacher') ? 'selected' : '' ?>>Teacher</option>
    <option value="admin" <?= (old('role', $currentRole) === 'admin') ? 'selected' : '' ?>>Administrator</option></select></div>
  <?php if ($user): ?><div class="form-group mb-3"><label class="form-label" for="status">Status</label><select name="status" id="status" class="form-select">
    <option value="active" <?= (old('status', $currentStatus) === 'active') ? 'selected' : '' ?>>Active</option>
    <option value="suspended" <?= (old('status', $currentStatus) === 'suspended') ? 'selected' : '' ?>>Suspended</option></select></div><?php endif; ?>
  <div class="d-flex gap-2 justify-content-end">
    <a href="<?= e(url('admin/users')) ?>" class="btn btn-outline-navy">Cancel</a>
    <button type="submit" class="btn btn-sm-navy"><?= e($user ? 'Update User' : 'Create User') ?></button>
  </div></form></div></div></div></div>