<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Study Sessions</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Subject</th><th>Goal</th><th>State</th><th>Started</th><th>Duration</th></tr></thead>
    <tbody><?php foreach ($sessions ?? [] as $s): ?><tr><td><?= e($s['subject']) ?></td><td><?= e($s['goal'] ?? '-') ?></td><td><span class="badge bg-<?= $s['state'] === 'active' ? 'success' : ($s['state'] === 'completed' ? 'teal' : 'secondary') ?>"><?= e(ucfirst($s['state'])) ?></span></td><td><?= e(date('M j, Y g:i A', strtotime($s['started_at']))) ?></td><td><?= e($s['duration_minutes'] ?? 0) ?> min</td></tr><?php endforeach; ?><?php if (empty($sessions)): ?><tr><td colspan="5" class="text-center text-muted py-4">No sessions recorded yet.</td></tr><?php endif; ?></tbody></table></div></div></div>