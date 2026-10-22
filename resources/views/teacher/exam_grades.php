<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Enter Grades — <?= e($exam['title']) ?></h1>
    <p class="sm-page-subtitle">
      <?= e(count($students ?? [])) ?> student<?= count($students ?? []) === 1 ? '' : 's' ?> ·
      out of <?= e($exam['max_marks']) ?> marks
    </p>
  </div>
  <div class="sm-page-actions">
    <a href="<?= e(url('teacher/exams')) ?>" class="btn btn-outline-navy">Back</a>
  </div>
</div>

<div class="sm-card">
  <div class="sm-card-header">Grades</div>
  <div class="sm-card-body">
    <?php if (empty($students)): ?>
      <p class="text-muted small mb-0">No students are enrolled in this class yet.</p>
    <?php else: ?>
      <form method="post" action="<?= e(url('teacher/exams/' . $exam['id'] . '/grades')) ?>" novalidate
            data-confirm="Save these grades? Published marks are shown to students immediately."
            data-confirm-title="Save grades"
            data-confirm-label="Save grades"
            data-confirm-tone="info">
        <?= csrf_input() ?>
        <div class="table-wrap">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Student</th>
                <th>Student ID</th>
                <th style="width: 8rem;">Marks</th>
                <th style="width: 7rem;">Grade</th>
                <th style="width: 6rem;">Publish</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $s): ?>
              <?php $sid = (int) $s['id']; ?>
              <tr>
                <td class="fw-semibold text-navy"><?= e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?></td>
                <td class="text-muted small"><?= e($s['student_id']) ?></td>
                <td>
                  <input type="number" name="marks_<?= $sid ?>" id="marks_<?= $sid ?>"
                         class="form-control form-control-sm w-auto"
                         value="<?= e($s['marks'] ?? '') ?>" min="0" max="<?= e($exam['max_marks']) ?>"
                         step="0.01"
                         aria-label="Marks for <?= e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?>">
                </td>
                <td>
                  <input type="text" name="grade_<?= $sid ?>" id="grade_<?= $sid ?>"
                         class="form-control form-control-sm w-auto"
                         value="<?= e($s['grade'] ?? '') ?>"
                         maxlength="3"
                         aria-label="Grade for <?= e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?>">
                </td>
                <td>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="publish_<?= $sid ?>"
                           id="publish_<?= $sid ?>" value="1"
                           <?= !empty($s['is_published']) ? 'checked' : '' ?>
                           aria-label="Publish grade to <?= e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?>">
                    <label class="form-check-label" for="publish_<?= $sid ?>">Show</label>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-end mt-4">
          <button type="submit" class="btn btn-sm-navy">Save All Grades</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
