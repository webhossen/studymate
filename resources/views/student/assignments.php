<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Assignments</h1>
    <p class="sm-page-subtitle">Upload your work before the due date. Late submissions are still accepted and flagged.</p>
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
            <th>Status</th>
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
            <td>
              <?php if ($a['submission_id']): ?>
                <span class="badge bg-success">Submitted</span>
                <?php if (!empty($a['is_late'])): ?><span class="badge bg-warning ms-1">Late</span><?php endif; ?>
              <?php else: ?>
                <span class="badge bg-warning">Pending</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <?php if ($a['submission_id']): ?>
                <a href="<?= e(url('student/submissions')) ?>" class="btn btn-sm btn-outline-navy">View</a>
                <button class="btn btn-sm btn-outline-secondary" type="button"
                        data-submit-assignment="<?= e($a['id']) ?>"
                        data-submit-title="<?= e($a['title']) ?>"
                        data-resubmit="1">Replace</button>
              <?php else: ?>
                <button class="btn btn-sm btn-sm-navy" type="button"
                        data-submit-assignment="<?= e($a['id']) ?>"
                        data-submit-title="<?= e($a['title']) ?>">Submit</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($assignments)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No assignments found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="smSubmitModal" tabindex="-1" aria-labelledby="smSubmitTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" id="smSubmitForm" enctype="multipart/form-data" novalidate>
        <?= csrf_input() ?>
        <div class="modal-header">
          <h5 class="modal-title" id="smSubmitTitle">Submit assignment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small" id="smSubmitHint">Attach your work for this assignment.</p>
          <div class="form-group mb-3">
            <label class="form-label" for="submission_file">Your file</label>
            <input type="file" name="submission_file" id="submission_file" class="form-control" required>
            <div class="form-text">PDF, Word, PowerPoint, Excel, image or ZIP — up to 10&nbsp;MB.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm-navy">Upload submission</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
/* Bootstrap's bundle is loaded at the end of the document, so wait for
   DOMContentLoaded before wiring the modal up. */
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('smSubmitForm');
  var title = document.getElementById('smSubmitTitle');
  var hint = document.getElementById('smSubmitHint');
  var input = document.getElementById('submission_file');
  var modalEl = document.getElementById('smSubmitModal');
  if (!form || !modalEl || typeof bootstrap === 'undefined') return;

  var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  document.querySelectorAll('[data-submit-assignment]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-submit-assignment');
      var name = this.getAttribute('data-submit-title') || '';
      var resubmit = this.getAttribute('data-resubmit') === '1';
      form.setAttribute('action', '<?= e(base_path('student/assignments')) ?>/' + encodeURIComponent(id) + '/submit');
      title.textContent = 'Submit “' + name + '”';
      hint.textContent = resubmit
        ? 'You have already submitted this assignment. Uploading again replaces your previous file.'
        : 'Attach your work for “' + name + '”.';

      /* Warn before overwriting an existing submission. */
      if (resubmit) {
        form.setAttribute('data-confirm',
          'Replace your previous submission for <strong>' + name + '</strong>? Your earlier file and any feedback will be cleared.');
        form.setAttribute('data-confirm-title', 'Replace submission');
        form.setAttribute('data-confirm-label', 'Replace and upload');
        form.setAttribute('data-confirm-tone', 'warning');
      } else {
        form.removeAttribute('data-confirm');
        form.removeAttribute('data-confirm-title');
        form.removeAttribute('data-confirm-label');
        form.removeAttribute('data-confirm-tone');
      }

      input.value = '';
      modal.show();
    });
  });
});
</script>
