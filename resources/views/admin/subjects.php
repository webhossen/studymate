<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Subjects</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <form method="post" action="<?= e(url('admin/subjects')) ?>" class="row g-3 mb-4" novalidate><?= csrf_input() ?>
    <div class="col-md-4"><label class="form-label" for="name">Subject Name</label><input type="text" name="name" id="name" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label" for="code">Code</label><input type="text" name="code" id="code" class="form-control" required></div>
    <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-sm-navy">Add</button></div>
  </form>
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Code</th><th>Name</th><th>Created</th></tr></thead>
    <tbody><?php foreach ($subjects ?? [] as $s): ?><tr><td><?= e($s['code']) ?></td><td><?= e($s['name']) ?></td><td><?= e(date('M j, Y', strtotime($s['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>