<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Dashboard</h1>
    <p class="sm-page-subtitle">The classes and subjects assigned to you.</p>
  </div>
  <div class="sm-page-actions">
    <a href="<?= e(url('teacher/attendance')) ?>" class="btn btn-sm-navy">Mark attendance</a>
    <a href="<?= e(url('teacher/assignments/create')) ?>" class="btn btn-outline-navy">New assignment</a>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <h2 class="h6 text-navy mb-4">My Classes &amp; Subjects</h2>
  <?php if (empty($classSubjects)): ?><p class="text-muted">No classes assigned.</p>
  <?php else: ?>
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Class</th><th>Section</th><th>Subject</th><th>Actions</th></tr></thead>
    <tbody><?php foreach ($classSubjects ?? [] as $cs): ?><tr><td><?= e($cs['class_name']) ?></td><td><?= e($cs['section']) ?></td><td><?= e($cs['subject_name']) ?></td><td><a href="<?= e(url('teacher/class/' . $cs['class_id'] . '/roster')) ?>" class="btn btn-sm btn-outline-navy">Roster</a></td></tr><?php endforeach; ?></tbody></table></div>
  <?php endif; ?></div></div>