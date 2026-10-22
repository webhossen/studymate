<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">My Submissions</h1>
    <p class="sm-page-subtitle">Every file you have uploaded, with the feedback your teacher published.</p>
  </div>
</div>

<div class="sm-card">
  <div class="sm-card-header">Submission History</div>
  <div class="sm-card-body">
    <div class="table-wrap">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Assignment</th>
            <th>Subject</th>
            <th>File</th>
            <th>Submitted</th>
            <th>Marks</th>
            <th>Feedback</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($submissions ?? [] as $s): ?>
          <tr>
            <td class="fw-semibold text-navy"><?= e($s['assignment_title'] ?? 'Assignment') ?></td>
            <td><?= e($s['subject_name'] ?? '-') ?></td>
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
            <td>
              <?php if ($s['marks'] !== null && $s['marks'] !== ''): ?>
                <span class="fw-semibold"><?= e($s['marks']) ?></span>
                <?php if (!empty($s['grade'])): ?><span class="text-muted small">(<?= e($s['grade']) ?>)</span><?php endif; ?>
              <?php else: ?>
                <span class="text-muted small">Not graded</span>
              <?php endif; ?>
            </td>
            <td class="text-muted small">
              <?= !empty($s['is_published']) ? e($s['feedback'] ?? '-') : '<span class="badge bg-secondary">Awaiting publication</span>' ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($submissions)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No submissions found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
