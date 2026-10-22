<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Assignments</h1>
    <p class="sm-page-subtitle">Set work for your classes and track what has been handed in.</p>
  </div>
  <div class="sm-page-actions">
    <a href="<?= e(url('teacher/assignments/create')) ?>" class="btn btn-sm-navy">+ Create assignment</a>
  </div>
</div>

<div class="sm-card">
  <div class="sm-card-header">All Assignments</div>
  <div class="sm-card-body">
    <div class="table-wrap">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Title</th>
            <th>Class</th>
            <th>Subject</th>
            <th>Due Date</th>
            <th>Max Marks</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($assignments ?? [] as $a): ?>
          <tr>
            <td>
              <span class="fw-semibold text-navy"><?= e($a['title']) ?></span>
              <?php if (!empty($a['file_path'])): ?>
                <a href="<?= e(url('files/assignment/' . $a['id'])) ?>" class="sm-attachment-link">
                  <span aria-hidden="true">📎</span> <?= e($a['file_original_name'] ?: 'Attachment') ?>
                </a>
              <?php endif; ?>
            </td>
            <td><?= e($a['class_name']) ?></td>
            <td><?= e($a['subject_name']) ?></td>
            <td class="text-muted small"><?= e(date('M j, Y', strtotime($a['due_date']))) ?></td>
            <td><?= e($a['max_marks']) ?></td>
            <td class="text-end">
              <a href="<?= e(url('teacher/assignments/' . $a['id'])) ?>" class="btn btn-sm btn-outline-navy">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($assignments)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No assignments yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
