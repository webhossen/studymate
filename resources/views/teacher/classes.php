<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">My Classes</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Class</th><th>Section</th><th>Subject</th><th>Code</th></tr></thead>
    <tbody><?php foreach ($classes ?? [] as $c): ?><tr><td><?= e($c['name']) ?></td><td><?= e($c['section']) ?></td><td><?= e($c['subject_name']) ?></td><td><?= e($c['subject_code'] ?? '-') ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>