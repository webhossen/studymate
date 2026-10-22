<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Submissions</h1>
  </div>
</div>
<div class="sm-card">
  <div class="sm-card-header">All Submissions</div>
  <div class="sm-card-body">
    <div class="table-wrap">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Student</th>
            <th>Assignment</th>
            <th>File</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Score</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($submissions ?? [] as $s): ?>
          <tr>
            <td class="fw-semibold text-navy"><?= e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?></td>
            <td><?= e($s['assignment_title'] ?? '-') ?></td>
            <td>
              <?php if (!empty($s['file_path'])): ?>
                <a href="<?= e(url('files/submission/' . $s['id'])) ?>" class="sm-attachment-link">
                  <span aria-hidden="true">📎</span> <?= e($s['file_original_name'] ?: 'Download') ?>
                </a>
              <?php else: ?>
                <span class="text-muted small">-</span>
              <?php endif; ?>
            </td>
            <td class="text-muted small"><?= e($s['submitted_at'] ? date('M j, Y H:i', strtotime($s['submitted_at'])) : '-') ?></td>
            <td>
              <?php if (!empty($s['is_late'])): ?>
                <span class="badge bg-warning">Late</span>
              <?php elseif ($s['grade'] !== null && $s['grade'] !== ''): ?>
                <span class="badge bg-success">Graded</span>
              <?php else: ?>
                <span class="badge bg-info">Pending</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($s['marks'] !== null && $s['marks'] !== ''): ?>
                <span class="fw-semibold"><?= e($s['marks']) ?></span>
                <?php if ($s['grade']): ?><span class="text-muted small">(<?= e($s['grade']) ?>)</span><?php endif; ?>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <a href="<?= e(url('teacher/submissions/' . $s['id'] . '/grade')) ?>" class="btn btn-sm btn-outline-navy">
                <?= $s['grade'] !== null && $s['grade'] !== '' ? 'Edit grade' : 'Grade' ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($submissions)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No submissions yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
