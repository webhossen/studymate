<div class="sm-page-head">
  <div>
    <h1 class="sm-page-title">Announcements</h1>
  </div>
</div>
<div class="sm-card"><div class="sm-card-body">
  <form method="post" action="<?= e(url('teacher/announcements')) ?>" class="row g-3 mb-4" novalidate
        data-confirm="Publish this announcement to your classes? Students will see it straight away."
        data-confirm-title="Publish announcement"
        data-confirm-label="Publish"
        data-confirm-tone="info"><?= csrf_input() ?>
    <div class="col-md-6"><label class="form-label" for="title">Title</label><input type="text" name="title" id="title" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label" for="content">Content</label><textarea name="content" id="content" class="form-control" rows="3" required></textarea></div>
    <div class="col-12 d-flex align-items-end justify-content-end"><button type="submit" class="btn btn-sm-navy">Publish</button></div>
  </form>
  <div class="table-wrap"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>Title</th><th>Audience</th><th>Published</th></tr></thead>
    <tbody><?php foreach ($announcements ?? [] as $a): ?><tr><td><?= e($a['title']) ?></td><td><?= e(ucfirst($a['audience'])) ?></td><td><?= e(date('M j, Y', strtotime($a['published_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>