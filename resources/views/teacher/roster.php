<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Class Roster</h1>
  </div>
  <div class="sm-page-actions"><a href="<?= e(url('teacher/classes')) ?>" class="btn btn-outline-navy">Back</a></div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Student ID</th><th>Name</th></tr></thead>
    <tbody><?php foreach ($students ?? [] as $s): ?><tr><td><?= e($s['student_id']) ?></td><td><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>