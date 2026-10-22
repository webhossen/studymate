<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Attendance</h1>
    <p class="sm-page-subtitle">Pick a class and date, then mark each student present, absent, late or excused.</p>
  </div>
</div>

<div class="sm-card mb-4">
  <div class="sm-card-header">Select session</div>
  <div class="sm-card-body">
    <form method="get" class="row g-3">
      <div class="col-md-5">
        <label class="form-label" for="class_subject_id">Class / Subject</label>
        <select name="class_subject_id" id="class_subject_id" class="form-select">
          <option value="">Select</option>
          <?php foreach ($classSubjects ?? [] as $cs): ?>
            <option value="<?= e($cs['id']) ?>" <?= ($classSubjectId ?? 0) == $cs['id'] ? 'selected' : '' ?>>
              <?= e($cs['class_name'] . ' — ' . $cs['subject_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="session_date">Date</label>
        <input type="date" name="session_date" id="session_date" class="form-control" value="<?= e($sessionDate ?? date('Y-m-d')) ?>">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-sm-navy">Load roster</button>
      </div>
    </form>
  </div>
</div>

<?php if ($classSubjectId): ?>
  <div class="sm-card">
    <div class="sm-card-header">
      Roster
      <span class="text-muted small fw-normal"><?= e($sessionDate ?? date('Y-m-d')) ?></span>
    </div>
    <div class="sm-card-body">
      <?php if (empty($students)): ?>
        <p class="text-muted small mb-0">No students are enrolled in this class yet.</p>
      <?php else: ?>
        <form method="post" action="<?= e(url('teacher/attendance')) ?>" novalidate>
          <?= csrf_input() ?>
          <input type="hidden" name="class_subject_id" value="<?= e($classSubjectId) ?>">
          <input type="hidden" name="session_date" value="<?= e($sessionDate ?? date('Y-m-d')) ?>">

          <div class="table-wrap">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Student ID</th>
                  <th style="width: 14rem;">Status</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($students as $r): ?>
                <tr>
                  <td class="fw-semibold text-navy"><?= e(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?></td>
                  <td class="text-muted small"><?= e($r['student_id']) ?></td>
                  <td>
                    <select name="status_<?= e($r['id']) ?>" class="form-select form-select-sm w-auto" aria-label="Attendance status for <?= e($r['first_name']) ?>">
                      <option value="present">Present</option>
                      <option value="absent">Absent</option>
                      <option value="late">Late</option>
                      <option value="excused">Excused</option>
                    </select>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-sm-navy">Save attendance</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
