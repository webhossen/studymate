<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Grade Submission</h1>
  </div>
  <div class="sm-page-actions"><a href="<?= e(url('teacher/submissions')) ?>" class="btn btn-outline-navy">Back</a></div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <form method="post" action="<?= e(url('teacher/submissions/' . $submission['id'] . '/grade')) ?>" novalidate
        data-confirm="Save this grade for <strong><?= e(trim(($submission['first_name'] ?? '') . ' ' . ($submission['last_name'] ?? ''))) ?></strong>? If you tick “Publish to student” the student will see it straight away."
        data-confirm-title="Save grade"
        data-confirm-label="Save grade"
        data-confirm-tone="info"><?= csrf_input() ?>
    <div class="form-group mb-3"><label class="form-label" for="marks">Marks</label><input type="number" name="marks" id="marks" class="form-control" value="<?= e($submission['marks'] ?? '') ?>" min="0" max="<?= e($submission['max_marks']) ?>" required></div>
    <div class="form-group mb-3"><label class="form-label" for="grade">Grade</label><input type="text" name="grade" id="grade" class="form-control" value="<?= e($submission['grade'] ?? '') ?>"></div>
    <div class="form-group mb-3"><label class="form-label" for="feedback">Feedback</label><textarea name="feedback" id="feedback" class="form-control" rows="4"><?= e($submission['feedback'] ?? '') ?></textarea></div>
    <div class="form-check mb-3"><input type="checkbox" name="is_published" id="is_published" class="form-check-input" <?= !empty($submission['is_published']) ? 'checked' : '' ?>><label class="form-check-label" for="is_published">Publish to student</label></div>
    <button type="submit" class="btn btn-sm-navy">Save Grade</button></form></div></div>