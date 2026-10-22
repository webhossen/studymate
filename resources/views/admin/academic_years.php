<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Academic Years</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <form method="post" action="<?= e(url('admin/academic-years')) ?>" class="row g-3 mb-4" novalidate><?= csrf_input() ?>
    <div class="col-md-3"><label class="form-label" for="name">Name</label><input type="text" name="name" id="name" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label" for="start_date">Start Date</label><input type="date" name="start_date" id="start_date" class="form-control" required></div>
    <div class="col-md-2"><label class="form-label" for="end_date">End Date</label><input type="date" name="end_date" id="end_date" class="form-control" required></div>
    <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-sm-navy">Add</button></div>
  </form>
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Name</th><th>Start</th><th>End</th><th>Created</th></tr></thead>
    <tbody><?php foreach ($years ?? [] as $y): ?><tr><td><?= e($y['name']) ?></td><td><?= e(date('M j, Y', strtotime($y['start_date']))) ?></td><td><?= e(date('M j, Y', strtotime($y['end_date']))) ?></td><td><?= e(date('M j, Y', strtotime($y['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>