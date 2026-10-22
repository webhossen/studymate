<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title"><?= e($note['title']) ?></h1>
  </div>
  <div class="sm-page-actions"><a href="<?= e(url('student/notes')) ?>" class="btn btn-outline-navy">Back</a></div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <p class="text-muted small">Subject: <?= e($note['subject'] ?? '-') ?> · Updated: <?= e(date('M j, Y g:i A', strtotime($note['updated_at']))) ?></p>
  <div class="note-content" style="white-space: pre-wrap; line-height: 1.6;"><?= e($note['content']) ?></div></div></div>