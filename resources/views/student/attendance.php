<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Attendance Record</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <div class="row g-4"><div class="col-md-4"><div class="sm-card text-center"><div class="sm-card-body"><div class="text-muted small fw-semibold">Total Sessions</div><div class="h3 mb-0 fw-bold"><?= e($total ?? 0) ?></div></div></div></div><div class="col-md-4"><div class="sm-card text-center"><div class="sm-card-body"><div class="text-muted small fw-semibold">Present</div><div class="h3 mb-0 fw-bold text-success"><?= e($present ?? 0) ?></div></div></div></div><div class="col-md-4"><div class="sm-card text-center"><div class="sm-card-body"><div class="text-muted small fw-semibold">Attendance %</div><div class="h3 mb-0 fw-bold text-navy"><?= e($pct ?? 0) ?>%</div></div></div></div></div>
  <div class="table-wrap mt-4"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Date</th><th>Class</th><th>Subject</th><th>Status</th></tr></thead>
    <tbody><?php foreach ($records ?? [] as $r): ?><tr><td><?= e(date('M j, Y', strtotime($r['session_date']))) ?></td><td><?= e($r['class_name']) ?></td><td><?= e($r['subject_name']) ?></td><td><span class="badge bg-<?= $r['status'] === 'present' ? 'success' : ($r['status'] === 'late' ? 'warning' : 'danger') ?>"><?= e(ucfirst($r['status'])) ?></span></td></tr><?php endforeach; ?><?php if (empty($records)): ?><tr><td colspan="4" class="text-center text-muted py-4">No attendance records.</td></tr><?php endif; ?></tbody></table></div></div></div>