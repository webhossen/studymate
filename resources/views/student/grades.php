<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Published Grades</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Exam</th><th>Subject</th><th>Marks</th><th>Grade</th><th>Feedback</th><th>Graded On</th></tr></thead>
    <tbody><?php foreach ($grades ?? [] as $g): ?><tr><td><?= e($g['exam_title']) ?></td><td><?= e($g['subject_name']) ?></td><td><?= e($g['marks']) ?></td><td><span class="badge bg-<?= $g['grade'] === 'F' ? 'danger' : 'success' ?>"><?= e($g['grade']) ?></span></td><td class="text-muted small"><?= e($g['feedback'] ?? '-') ?></td><td><?= e(date('M j, Y', strtotime($g['graded_at']))) ?></td></tr><?php endforeach; ?><?php if (empty($grades)): ?><tr><td colspan="6" class="text-center text-muted py-4">No published grades.</td></tr><?php endif; ?></tbody></table></div></div></div>