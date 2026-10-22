<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Dashboard</h1>
    <p class="sm-page-subtitle">Your study overview for <?= e(date('l, j F Y')) ?>.</p>
  </div>
  <div class="sm-page-actions">
    <a href="<?= e(url('student/tasks')) ?>" class="btn btn-sm-navy">+ Add task</a>
    <a href="<?= e(url('student/focus-timer')) ?>" class="btn btn-outline-navy">Focus timer</a>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-6 col-lg-3">
    <div class="sm-card sm-card-body text-center text-primary">
      <div class="text-muted small fw-semibold">Pending Tasks</div>
      <div class="h3 mb-0 fw-bold"><?= e($pendingTasks ?? 0) ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="sm-card sm-card-body text-center text-success">
      <div class="text-muted small fw-semibold">Completed Tasks</div>
      <div class="h3 mb-0 fw-bold"><?= e($completedTasks ?? 0) ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="sm-card sm-card-body text-center text-warning">
      <div class="text-muted small fw-semibold">Upcoming Assignments</div>
      <div class="h3 mb-0 fw-bold"><?= e($upcomingAssignments ?? 0) ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="sm-card sm-card-body text-center text-teal">
      <div class="text-muted small fw-semibold">Upcoming Exams</div>
      <div class="h3 mb-0 fw-bold"><?= e($upcomingExams ?? 0) ?></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="sm-card h-100">
      <div class="sm-card-header">Upcoming Announcements</div>
      <div class="sm-card-body">
        <?php if (!empty($recentAnnouncements)): ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($recentAnnouncements as $a): ?>
              <li class="mb-3 d-flex align-items-start gap-2">
                <span class="sm-icon-chip" aria-hidden="true">📢</span>
                <span>
                  <span class="fw-semibold text-navy d-block"><?= e($a['title']) ?></span>
                  <span class="text-muted small"><?= e(date('M j, Y', strtotime($a['published_at']))) ?></span>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted small mb-0">No recent announcements.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="sm-card h-100">
      <div class="sm-card-header">Published Grades</div>
      <div class="sm-card-body">
        <?php if (!empty($publishedGrades)): ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($publishedGrades as $g): ?>
              <li class="mb-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-navy"><?= e($g['exam_title']) ?></span>
                <span class="badge bg-<?= e($g['grade'] === 'F' ? 'danger' : 'success') ?>"><?= e($g['marks']) ?> / <?= e($g['grade']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted small mb-0">No published grades.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="sm-card mt-4">
  <div class="sm-card-header">Quick Actions</div>
  <div class="sm-card-body">
    <div class="d-flex flex-wrap gap-2">
      <a href="<?= e(url('student/tasks')) ?>" class="btn btn-sm-navy">Manage Tasks</a>
      <a href="<?= e(url('student/planner')) ?>" class="btn btn-outline-navy">Planner</a>
      <a href="<?= e(url('student/notes')) ?>" class="btn btn-outline-navy">Notes</a>
      <a href="<?= e(url('student/focus-timer')) ?>" class="btn btn-outline-navy">Focus Timer</a>
      <a href="<?= e(url('student/sessions')) ?>" class="btn btn-outline-navy">Study Sessions</a>
      <a href="<?= e(url('student/assignments')) ?>" class="btn btn-outline-navy">Assignments</a>
    </div>
  </div>
</div>
