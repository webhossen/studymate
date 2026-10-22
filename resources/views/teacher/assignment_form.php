<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Create Assignment</h1>
  </div>
  <div class="sm-page-actions"><a href="<?= e(url('teacher/assignments')) ?>" class="btn btn-outline-navy">Back</a></div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate><?= csrf_input() ?>
    <div class="form-group mb-3"><label class="form-label" for="class_subject_id">Class / Subject</label><select name="class_subject_id" id="class_subject_id" class="form-select" required><option value="">Select</option><?php foreach ($classSubjects ?? [] as $cs): ?><option value="<?= e($cs['id']) ?>"><?= e($cs['class_name'] . ' - ' . $cs['subject_name']) ?></option><?php endforeach; ?></select></div>
    <div class="form-group mb-3"><label class="form-label" for="title">Title</label><input type="text" name="title" id="title" class="form-control" required></div>
    <div class="form-group mb-3"><label class="form-label" for="description">Description</label><textarea name="description" id="description" class="form-control" rows="3"></textarea></div>
    <div class="row g-3 mb-3"><div class="col-md-4"><label class="form-label" for="due_date">Due Date</label><input type="datetime-local" name="due_date" id="due_date" class="form-control" required></div><div class="col-md-4"><label class="form-label" for="max_marks">Max Marks</label><input type="number" name="max_marks" id="max_marks" class="form-control" value="100" min="0" required></div><div class="col-md-4"><label class="form-label" for="assignment_file">Attachment</label><input type="file" name="assignment_file" id="assignment_file" class="form-control"></div></div>
    <div class="d-grid gap-2"><button type="submit" class="btn btn-sm-navy">Create Assignment</button></div></form></div></div>