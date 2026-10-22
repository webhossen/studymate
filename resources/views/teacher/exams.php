<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Exams &amp; Grades</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Title</th><th>Class</th><th>Subject</th><th>Date</th><th>Max Marks</th><th class="text-end">Actions</th></tr></thead>
    <tbody><?php foreach ($exams ?? [] as $e): ?><tr><td><?= e($e['title']) ?></td><td><?= e($e['class_name']) ?></td><td><?= e($e['subject_name']) ?></td><td><?= e($e['exam_date'] ? date('M j, Y', strtotime($e['exam_date'])) : '-') ?></td><td><?= e($e['max_marks']) ?></td><td class="text-end"><a href="<?= e(url('teacher/exams/' . $e['id'] . '/grades')) ?>" class="btn btn-sm btn-outline-navy">Enter Grades</a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<a href="#" class="btn btn-sm-navy">+ Create Exam</a>