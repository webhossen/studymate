<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Enrollments</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <form method="post" action="<?= e(url('admin/enrollments')) ?>" class="row g-3 mb-4" novalidate><?= csrf_input() ?>
    <div class="col-md-4"><label class="form-label" for="student_id">Student Profile ID</label><input type="number" name="student_id" id="student_id" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label" for="class_id">Class ID</label><input type="number" name="class_id" id="class_id" class="form-control" required></div>
    <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-sm-navy">Enroll</button></div>
  </form>
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Student</th><th>Student ID</th><th>Class</th></tr></thead>
    <tbody><?php foreach ($enrollments ?? [] as $e): ?><tr><td><?= e($e['student_name']) ?></td><td><?= e($e['student_id']) ?></td><td><?= e($e['class_name']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>