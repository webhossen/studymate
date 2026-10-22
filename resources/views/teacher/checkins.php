<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Study Check-ins</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Student</th><th>Subject</th><th>Goal</th><th>Message</th><th>Time</th></tr></thead>
    <tbody><?php foreach ($checkins ?? [] as $c): ?><tr><td><?= e($c['first_name'] . ' ' . $c['last_name']) ?></td><td><?= e($c['subject']) ?></td><td><?= e($c['goal'] ?? '-') ?></td><td class="text-muted small"><?= e($c['message'] ?? '-') ?></td><td><?= e(date('M j, Y g:i A', strtotime($c['checked_in_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>