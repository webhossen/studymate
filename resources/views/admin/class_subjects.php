<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Class Subjects</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <form method="post" action="<?= e(url('admin/class-subjects')) ?>" class="row g-3 mb-4" novalidate><?= csrf_input() ?>
    <div class="col-md-3"><label class="form-label" for="class_id">Class ID</label><input type="number" name="class_id" id="class_id" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label" for="subject_id">Subject ID</label><input type="number" name="subject_id" id="subject_id" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label" for="teacher_id">Teacher ID</label><input type="number" name="teacher_id" id="teacher_id" class="form-control" required></div>
    <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-sm-navy">Assign</button></div>
  </form>
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Class</th><th>Subject</th><th>Teacher</th></tr></thead>
    <tbody><?php foreach ($assignments ?? [] as $a): ?><tr><td><?= e($a['class_name']) ?></td><td><?= e($a['subject_name']) ?></td><td><?= e($a['teacher_name']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>