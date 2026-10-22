<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Users</h1>
    <p class="sm-page-subtitle">Accounts, roles and access for your institution.</p>
  </div>
  <div class="sm-page-actions"><a href="<?= e(url('admin/users/create')) ?>" class="btn btn-sm-navy">+ Add User</a></div>
</div>

<div class="sm-card">
  <div class="sm-card-header">All Users</div>
  <div class="sm-card-body">
    <div class="table-wrap">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>ID</th>
            <th>Created</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users ?? [] as $u): ?>
          <?php $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')); ?>
          <tr>
            <td class="fw-semibold text-navy"><?= e($name !== '' ? $name : '—') ?></td>
            <td><?= e($u['email']) ?></td>
            <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'primary' : ($u['role'] === 'teacher' ? 'secondary' : 'accent') ?>"><?= e(ucfirst($u['role'])) ?></span></td>
            <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'warning' ?>"><?= e(ucfirst($u['status'])) ?></span></td>
            <td class="text-muted small"><?= e($u['student_id'] ?? $u['employee_id'] ?? '—') ?></td>
            <td class="text-muted small"><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
            <td class="text-end">
              <a href="<?= e(url('admin/users/' . $u['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-navy">Edit</a>
              <form method="post" action="<?= e(url('admin/users/' . $u['id'] . '/status')) ?>" style="display:inline;">
                <?= csrf_input() ?>
                <select name="status" class="form-select form-select-sm d-inline w-auto"
                        aria-label="Status for <?= e($u['email']) ?>"
                        data-confirm="Change the status of <strong><?= e($u['email']) ?></strong> to <strong><span data-confirm-value>…</span></strong>?"
                        data-confirm-title="Change user status"
                        data-confirm-label="Change status"
                        data-confirm-tone="warning"
                        data-confirm-previous="<?= e($u['status'] ?? 'active') ?>"
                        data-sm-autosubmit>
                  <option value="active" <?= ($u['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="suspended" <?= ($u['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
