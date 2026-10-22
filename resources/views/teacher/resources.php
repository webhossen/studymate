<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Resources</h1>
    <p class="sm-page-subtitle">Share worksheets, slides and notes with your classes.</p>
  </div>
</div>

<div class="sm-card mb-4">
  <div class="sm-card-header">Upload a resource</div>
  <div class="sm-card-body">
    <form method="post" action="<?= e(url('teacher/resources')) ?>" enctype="multipart/form-data" class="row g-3" novalidate>
      <?= csrf_input() ?>
      <div class="col-md-4">
        <label class="form-label" for="title">Title</label>
        <input type="text" name="title" id="title" class="form-control" placeholder="Week 3 worksheet" required>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="description">Description</label>
        <input type="text" name="description" id="description" class="form-control" placeholder="Optional notes for students">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="resource_file">File</label>
        <input type="file" name="resource_file" id="resource_file" class="form-control" required>
        <div class="form-text">PDF, Word, slides, images or ZIP — up to 10&nbsp;MB.</div>
      </div>
      <div class="col-12 d-flex align-items-end justify-content-end">
        <button type="submit" class="btn btn-sm-navy">Upload</button>
      </div>
    </form>
  </div>
</div>

<div class="sm-card">
  <div class="sm-card-header">Your Resources</div>
  <div class="sm-card-body">
    <div class="table-wrap">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Title</th>
            <th>Description</th>
            <th>File</th>
            <th>Size</th>
            <th>Uploaded</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($resources ?? [] as $r): ?>
          <tr>
            <td class="fw-semibold text-navy"><?= e($r['title']) ?></td>
            <td class="text-muted small"><?= e($r['description'] ?? '-') ?></td>
            <td>
              <?php if (!empty($r['file_path'])): ?>
                <a href="<?= e(url('files/resource/' . $r['id'])) ?>" class="sm-attachment-link">
                  <span aria-hidden="true">📎</span> <?= e($r['file_original_name'] ?: 'Download') ?>
                </a>
              <?php else: ?>
                <span class="text-muted small">-</span>
              <?php endif; ?>
            </td>
            <td class="text-muted small"><?= $r['file_size'] ? e(round($r['file_size'] / 1024, 1) . ' KB') : '-' ?></td>
            <td class="text-muted small"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
            <td class="text-end">
              <?php if (!empty($r['file_path'])): ?>
                <a href="<?= e(url('files/resource/' . $r['id'])) ?>" class="btn btn-sm btn-outline-navy">Download</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($resources)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No resources uploaded yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
