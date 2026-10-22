<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Examinations</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Exam</th><th>Subject</th><th>Class</th><th>Date</th><th>Max Marks</th><th>Your Grade</th><th>Status</th></tr></thead>
    <tbody><?php foreach ($exams ?? [] as $e): ?><tr><td><?= e($e['title']) ?></td><td><?= e($e['subject_name']) ?></td><td><?= e($e['class_name']) ?></td><td><?= e($e['exam_date'] ? date('M j, Y', strtotime($e['exam_date'])) : '-') ?></td><td><?= e($e['max_marks']) ?></td><td><?= $e['is_published'] ? (e($e['marks'] ?? '-') . ' (' . e($e['grade'] ?? '-') . ')') : '<span class="badge bg-secondary">Unpublished</span>' ?></td><td><?= $e['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-warning">Pending</span>' ?></td></tr><?php endforeach; ?><?php if (empty($exams)): ?><tr><td colspan="7" class="text-center text-muted py-4">No exams found.</td></tr><?php endif; ?></tbody></table></div></div></div>