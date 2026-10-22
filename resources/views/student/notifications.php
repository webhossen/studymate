<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Notifications</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Title</th><th>Content</th><th>Status</th><th>Created</th><th class="text-end">Action</th></tr></thead>
    <tbody><?php foreach ($notifications ?? [] as $n): ?><tr><td><?= e($n['title']) ?></td><td class="text-muted small"><?= e($n['content'] ?? '-') ?></td><td><?= $n['is_read'] ? '<span class="badge bg-success">Read</span>' : '<span class="badge bg-warning">Unread</span>' ?></td><td><?= e(date('M j, Y g:i A', strtotime($n['created_at']))) ?></td><td class="text-end"><?php if (!$n['is_read']): ?><form method="post" action="<?= e(url('student/notifications/' . $n['id'] . '/read')) ?>" style="display:inline;"><?= csrf_input() ?><button type="submit" class="btn btn-sm btn-outline-navy">Mark Read</button></form><?php else: ?><span class="text-muted">-</span><?php endif; ?></td></tr><?php endforeach; ?><?php if (empty($notifications)): ?><tr><td colspan="5" class="text-center text-muted py-4">No notifications.</td></tr><?php endif; ?></tbody></table></div></div></div>