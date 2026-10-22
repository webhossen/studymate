<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Audit Logs</h1>
    <p class="sm-page-subtitle">Every administrative and teaching action, newest first.</p>
  </div>
</div>

<div class="sm-card">
  <div class="sm-card-header">Activity</div>
  <div class="sm-card-body">
    <div class="table-wrap">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Action</th>
            <th>Table</th>
            <th>Record</th>
            <th>Details</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($logs ?? [] as $log): ?>
          <tr>
            <td class="text-muted small"><?= e($log['id']) ?></td>
            <td class="fw-semibold text-navy"><?= e($log['email'] ?? 'System') ?></td>
            <td><span class="badge bg-primary"><?= e($log['action']) ?></span></td>
            <td class="text-muted small"><?= e($log['table_name'] ?? '-') ?></td>
            <td class="text-muted small"><?= e($log['record_id'] ?? '-') ?></td>
            <td class="text-muted small">
              <?php if (!empty($log['new_values'])): ?>
                <?= e(implode(', ', array_map(fn($v) => is_scalar($v) ? (string) $v : json_encode($v), (array) json_decode($log['new_values'], true)))) ?>
              <?php else: ?>
                &mdash;
              <?php endif; ?>
            </td>
            <td class="text-muted small"><?= e(date('M j, Y g:i A', strtotime($log['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
