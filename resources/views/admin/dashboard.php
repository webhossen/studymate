<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Dashboard</h1>
    <p class="sm-page-subtitle">Institution overview for <?= e(date('l, j F Y')) ?>.</p>
  </div>
  <div class="sm-page-actions">
    <a href="<?= e(url('admin/users/create')) ?>" class="btn btn-sm-navy">+ Add user</a>
    <a href="<?= e(url('admin/audit-logs')) ?>" class="btn btn-outline-navy">Audit logs</a>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-6 col-lg-3">
    <div class="sm-card sm-card-body text-center text-navy">
      <div class="text-muted small fw-semibold">Total Students</div>
      <div class="h3 mb-0 fw-bold"><?= e($stats['students'] ?? 0) ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="sm-card sm-card-body text-center text-teal">
      <div class="text-muted small fw-semibold">Total Teachers</div>
      <div class="h3 mb-0 fw-bold"><?= e($stats['teachers'] ?? 0) ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="sm-card sm-card-body text-center text-primary">
      <div class="text-muted small fw-semibold">Classes</div>
      <div class="h3 mb-0 fw-bold"><?= e($stats['classes'] ?? 0) ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="sm-card sm-card-body text-center text-accent">
      <div class="text-muted small fw-semibold">Subjects</div>
      <div class="h3 mb-0 fw-bold"><?= e($stats['subjects'] ?? 0) ?></div>
    </div>
  </div>
</div>

<div class="sm-card">
  <div class="sm-card-header">Quick Actions</div>
  <div class="sm-card-body">
    <div class="d-flex flex-wrap gap-2">
      <a href="<?= e(url('admin/users/create')) ?>" class="btn btn-sm-navy">Create User</a>
      <a href="<?= e(url('admin/departments')) ?>" class="btn btn-outline-navy">Departments</a>
      <a href="<?= e(url('admin/classes')) ?>" class="btn btn-outline-navy">Classes</a>
      <a href="<?= e(url('admin/subjects')) ?>" class="btn btn-outline-navy">Subjects</a>
      <a href="<?= e(url('admin/enrollments')) ?>" class="btn btn-outline-navy">Enrollments</a>
      <a href="<?= e(url('admin/class-subjects')) ?>" class="btn btn-outline-navy">Class Subjects</a>
      <a href="<?= e(url('admin/announcements')) ?>" class="btn btn-outline-navy">Announcements</a>
      <a href="<?= e(url('admin/audit-logs')) ?>" class="btn btn-outline-navy">Audit Logs</a>
    </div>
  </div>
</div>
