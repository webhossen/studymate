<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title"><?= e($assignment['title']) ?></h1>
    <p class="sm-page-subtitle">Due <?= e(date('M j, Y', strtotime($assignment['due_date']))) ?> · <?= e($assignment['max_marks']) ?> marks</p>
  </div>
  <div class="sm-page-actions">
    <a href="<?= e(url('teacher/assignments')) ?>" class="btn btn-outline-navy">Back</a>
  </div>
</div>

<div class="sm-card mb-4">
  <div class="sm-card-header">Details</div>
  <div class="sm-card-body">
    <p class="text-muted mb-3"><?= e($assignment['description'] ?? 'No description provided.') ?></p>
    <div class="row g-3">
      <div class="col-sm-4">
        <div class="sm-detail-label">Due date</div>
        <div class="sm-detail-value"><?= e(date('M j, Y \a\t g:i A', strtotime($assignment['due_date']))) ?></div>
      </div>
      <div class="col-sm-4">
        <div class="sm-detail-label">Maximum marks</div>
        <div class="sm-detail-value"><?= e($assignment['max_marks']) ?></div>
      </div>
      <div class="col-sm-4">
        <div class="sm-detail-label">Attachment</div>
        <div class="sm-detail-value">
          <?php if (!empty($assignment['file_path'])): ?>
            <a href="<?= e(url('files/assignment/' . $assignment['id'])) ?>" class="sm-attachment-link">
              <span aria-hidden="true">📎</span> <?= e($assignment['file_original_name'] ?: 'Download') ?>
            </a>
          <?php else: ?>
            <span class="text-muted">None</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="sm-card">
  <div class="sm-card-header">Submissions</div>
  <div class="sm-card-body">
    <div class="table-wrap">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Student</th>
            <th>File</th>
            <th>Submitted</th>
            <th>Marks</th>
            <th>Published</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($submissions ?? [] as $s): ?>
          <tr>
            <td class="fw-semibold text-navy"><?= e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?></td>
            <td>
              <?php if (!empty($s['file_path'])): ?>
                <a href="<?= e(url('files/submission/' . $s['id'])) ?>" class="sm-attachment-link">
                  <span aria-hidden="true">📎</span> <?= e($s['file_original_name'] ?: 'Download') ?>
                </a>
              <?php else: ?>
                <span class="text-muted small">-</span>
              <?php endif; ?>
            </td>
            <td class="text-muted small">
              <?= e(date('M j, Y g:i A', strtotime($s['submitted_at']))) ?>
              <?php if (!empty($s['is_late'])): ?><span class="badge bg-warning ms-1">Late</span><?php endif; ?>
            </td>
            <td><?= e($s['marks'] ?? '-') ?></td>
            <td>
              <?php if ($s['is_published']): ?>
                <span class="badge bg-success">Yes</span>
              <?php else: ?>
                <span class="badge bg-secondary">No</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <a href="<?= e(url('teacher/submissions/' . $s['id'] . '/grade')) ?>" class="btn btn-sm btn-outline-navy">
                <?= $s['marks'] !== null ? 'Edit grade' : 'Grade' ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($submissions)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No submissions yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
