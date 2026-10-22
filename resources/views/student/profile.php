<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Profile</h1>
    <p class="sm-page-subtitle">Your account details and contact information.</p>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="sm-card text-center h-100">
      <div class="sm-card-body">
        <span class="sm-profile-avatar" aria-hidden="true">
          <?= e(strtoupper(substr((string) ($profile['first_name'] ?? $user['email'] ?? 'U'), 0, 1))) ?>
        </span>
        <h2 class="h6 text-navy mt-3 mb-1">
          <?= e(trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')) ?: 'Your account') ?>
        </h2>
        <p class="text-muted small mb-0"><?= e($user['email']) ?></p>
        <span class="badge bg-primary mt-3">Student</span>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="sm-card mb-4">
      <div class="sm-card-header">Account details</div>
      <div class="sm-card-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="sm-detail-label">Full name</div>
            <div class="sm-detail-value"><?= e(trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')) ?: '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="sm-detail-label">Student ID</div>
            <div class="sm-detail-value"><?= e($profile['student_id'] ?? '—') ?></div>
          </div>
          <div class="col-sm-6">
            <div class="sm-detail-label">Email address</div>
            <div class="sm-detail-value"><?= e($user['email']) ?></div>
          </div>
          <div class="col-sm-6">
            <div class="sm-detail-label">Role</div>
            <div class="sm-detail-value">Student</div>
          </div>
        </div>
      </div>
    </div>

    <div class="sm-card">
      <div class="sm-card-header">Contact details</div>
      <div class="sm-card-body">
        <form method="post" action="<?= e(url('student/profile')) ?>" novalidate>
          <?= csrf_input() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="phone">Phone</label>
              <input type="text" name="phone" id="phone" class="form-control" value="<?= e($profile['phone'] ?? '') ?>" placeholder="+1 555 0100">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <button type="submit" class="btn btn-sm-navy">Update profile</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
